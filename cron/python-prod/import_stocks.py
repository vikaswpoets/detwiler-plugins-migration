#!/bin/bash
import shutil
import mysql.connector
import os
import datetime
import pysftp
import requests
import html
import json
from io import open

import fnmatch
from ftplib import FTP

import ftplib

import pysftp
from io import open


LOG="./logs"
datenow=datetime.datetime.now()
DATE= datenow.strftime("%Y%m%d_%H%M%S")
LOG_FILE="./logs/import_stocks_"+DATE+".log"


#database
host='localhost'
#database='stg_pim_gi'
#dbuser='jmartins'
#dbpass='ZAQ123wsx'
database='import_gi_prod'
dbuser='importgi'
dbpass='ZAQ123wsx'


#link="https://e2515-iflmap.hcisbt.eu1.hana.ondemand.com/http/GICHANNELS/GET_DATA_STOCK_CDS"
#tokenlink="https://oauthasservices-a4b9bd800.hana.ondemand.com/oauth2/api/v1/token"
link="https://l2515-iflmap.hcisbp.eu1.hana.ondemand.com/http/GICHANNELS/GET_DATA_STOCK_CDS"
tokenlink="https://oauthasservices-a3c9ce896.hana.ondemand.com/oauth2/api/v1/token"
clientid="e27dfb2c-9961-3756-9720-32c99ec819ac"
clientsecret="9ad9a0c8-02ef-3253-993b-8faa20d6965b"
granttype="client_credentials"
tokenname="SAP - Client Credentials"

#body={"Filters": {"Filter": [{"Field": "Material","Sign": "eq","Value": "44003071","Operator": ""}]}}
#body={"Filters": {"Filter": [{"Field": "SalesOrganization","Sign": "eq","Value": "2141","Operator": ""}]}}
#body={"Filters": {"Filter": [{"Field": "Plant","Sign": "eq","Value": "2130","Operator": ""}]}}
body={"Filters": {"Filter": [{"Field": "Plant","Sign": "eq","Value": "2130","Operator": "and"},{"Field": "SalesOrganization","Sign": "eq","Value": "2130","Operator": ""}]}}
#body={"Filters": {"Filter": [{"Field": "SalesOrganization","Sign": "eq","Value": "1110","Operator": ""}]}}
#body={"Filters": {"Filter": [{"Field": "SalesOrganization","Sign": "eq","Value": "2141","Operator": "and"},{"Field": "Customer","Sign": "eq","Value": "1014919","Operator": ""}]}}

## Counters for summary
stats = {
    "records_total": 0,
    "records_success": 0,
    "records_failed": 0,
    "import_id": 0,
    "sp_status": "not_run",
    "failed_materials": []
}

def writeToFile(text):
	filename = LOG_FILE
	f = open( filename, "a", encoding='utf-8')
	f.write(text + "\n")
	f.close()

def logSummary():
    writeToFile('\n' + '='*60)
    writeToFile('STOCK IMPORT SUMMARY')
    writeToFile('='*60)
    writeToFile(f'  Import ID              : {stats["import_id"]}')
    writeToFile(f'  Total Records Received : {stats["records_total"]}')
    writeToFile(f'  Records Inserted OK    : {stats["records_success"]}')
    writeToFile(f'  Records Failed         : {stats["records_failed"]}')
    writeToFile(f'  SP insertStocksIntoPortal: {stats["sp_status"]}')
    if stats["failed_materials"]:
        writeToFile(f'  Failed Materials: {", ".join(stats["failed_materials"][:20])}')
    writeToFile('='*60 + '\n')

def get_access_token(url):
    response = requests.post(
        url,
        data={"grant_type": granttype},
        auth=(clientid, clientsecret),
    )
    return response.json()["access_token"]



def getSAPStocks():
    writeToFile(str(datetime.datetime.now())+' [API] Requesting OAuth token...')
    token=get_access_token(tokenlink)
    writeToFile(str(datetime.datetime.now())+' [API] Token obtained successfully')

    headers = {"Authorization": f"Bearer {token}","Connection": "keep-alive"}
    writeToFile(str(datetime.datetime.now())+' [API] Calling SAP Stock API...')
    response = requests.get(
        link,
        data=json.dumps(body, sort_keys=True, indent=4),
        headers=headers
    )
    writeToFile(str(datetime.datetime.now())+f' [API] Response status: {response.status_code}')
    if response.status_code == 200:
        insertStocksIntoStaging (response.json())
    else:
        writeToFile(str(datetime.datetime.now())+f' [API FAILED] SAP returned status {response.status_code}')

def parseJSON(response):
    data=response
    print(data)
    print(data["ZDD_I_SD_PIM_MaterialStock"]["ZDD_I_SD_PIM_MaterialStockType"])
    for ord in data["ZDD_I_SD_PIM_MaterialStock"]["ZDD_I_SD_PIM_MaterialStockType"]:
        print("BasePrice:", ord["BasePrice"])
        print("BasicMaterial:", ord["BasicMaterial"])
        print('---')

    
def insertStocksIntoStaging(data):
    mydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
    mycursor = mydb.cursor()

    cursor = mydb.cursor()

    query = 'SELECT ifnull(max(importid),0) as id from stg_stocks'
    cursor.execute(query)
    maxid = cursor.fetchone()
    maxid=int(maxid[0])+1
    stats["import_id"] = maxid
    writeToFile(str(datetime.datetime.now())+f' [DB] New import batch ID: {maxid}')

    records = data["ZDD_I_SD_PIM_MaterialStock"]["ZDD_I_SD_PIM_MaterialStockType"]
    stats["records_total"] = len(records)
    writeToFile(str(datetime.datetime.now())+f' [DB] Inserting {len(records)} stock records into stg_stocks...')

    for idx, ord in enumerate(records, 1):
        try:
            StockQuantity=ord["StockQuantity"]
            StorageLocation=ord["StorageLocation"]
            Plant=ord["Plant"]
            BasicMaterial=ord["BasicMaterial"]
            StockQuantityUnit=ord["StockQuantityUnit"]
            EstimatedShipDate=ord["EstimatedShipDate"]
            Material=ord["Material"]
            Batch=ord["Batch"]
            DistributionChannel=ord["DistributionChannel"]
            ManufactureDate=ord["ManufactureDate"]
            LeadTimeInWeeks=ord["LeadTimeInWeeks"]
            PricingReferenceProduct=ord["PricingReferenceProduct"]
            TotalStockQuantity=ord["TotalStockQuantity"]
            OldMaterialNumber=ord["OldMaterialNumber"]
            CureDate=ord["CureDate"]
            SalesOrganization=ord["SalesOrganization"]
            TotalStockQuantityUnit=ord["TotalStockQuantityUnit"]

            cursor.execute("INSERT INTO stg_stocks(importdate,StockQuantity,StorageLocation,Plant,BasicMaterial,StockQuantityUnit,EstimatedShipDate,Material,Batch,DistributionChannel,ManufactureDate,LeadTimeInWeeks,PricingReferenceProduct,TotalStockQuantity,OldMaterialNumber,CureDate,SalesOrganization,TotalStockQuantityUnit,importid) values(now(),'"+StockQuantity+"','"+StorageLocation+"','"+Plant+"','"+BasicMaterial+"','"+StockQuantityUnit+"','"+EstimatedShipDate+"','"+Material+"','"+Batch+"','"+DistributionChannel+"','"+ManufactureDate+"','"+LeadTimeInWeeks+"','"+PricingReferenceProduct+"','"+TotalStockQuantity+"','"+OldMaterialNumber+"','"+CureDate+"','"+SalesOrganization+"','"+TotalStockQuantityUnit+"','"+str(maxid)+"');")
            stats["records_success"] += 1

            # Log progress every 100 records
            if idx % 100 == 0:
                writeToFile(str(datetime.datetime.now())+f' [PROGRESS] {idx}/{len(records)} stock records inserted...')

        except Exception as e:
            stats["records_failed"] += 1
            stats["failed_materials"].append(Material)
            writeToFile(str(datetime.datetime.now())+f' [FAILED] Material {Material} (Plant: {Plant}) - Error: {str(e)}')

    cursor.execute("commit;")
    writeToFile(str(datetime.datetime.now())+f' [DB] All stock records committed')

    writeToFile(str(datetime.datetime.now())+' [SP] Executing sp_insertStocksIntoPortal...')
    try:
        cursor.execute("call sp_insertStocksIntoPortal;")
        mydb.commit()
        stats["sp_status"] = "SUCCESS"
        writeToFile(str(datetime.datetime.now())+' [SP] sp_insertStocksIntoPortal completed successfully')
    except Exception as e:
        stats["sp_status"] = f"FAILED: {str(e)}"
        writeToFile(str(datetime.datetime.now())+f' [SP FAILED] sp_insertStocksIntoPortal Error: {str(e)}')

    mycursor.close()
    cursor.close()
    
######
# MAIN
######
if __name__ == '__main__':
    writeToFile('='*60)
    writeToFile('SAP STOCK IMPORT - Integration start '+str(datetime.datetime.now()))
    writeToFile('='*60)
    writeToFile(f'  Database: {database} @ {host}')
    writeToFile(f'  SAP Endpoint: {link}')
    writeToFile(f'  Filter: {json.dumps(body)}')
    writeToFile('-'*60)
    getSAPStocks()
    logSummary()
    writeToFile('Integration Done '+str(datetime.datetime.now()))
    
