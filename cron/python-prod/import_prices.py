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
LOG_FILE="./logs/import_prices_"+DATE+".log"


#database
host='localhost'
database='stg_pim_gi'
dbuser='importgi'
dbpass='ZAQ123wsx'

#link="https://e2515-iflmap.hcisbt.eu1.hana.ondemand.com/http/GICHANNELS/GET_DATA_PRICE_CDS"
#tokenlink="https://oauthasservices-a4b9bd800.hana.ondemand.com/oauth2/api/v1/token"
link="https://l2515-iflmap.hcisbp.eu1.hana.ondemand.com/http/GICHANNELS/GET_DATA_PRICE_CDS"
tokenlink="https://oauthasservices-a3c9ce896.hana.ondemand.com/oauth2/api/v1/token"

clientid="e27dfb2c-9961-3756-9720-32c99ec819ac"
clientsecret="9ad9a0c8-02ef-3253-993b-8faa20d6965b"
granttype="client_credentials"
tokenname="SAP - Client Credentials"

#body={"Filters": {"Filter": [{"Field": "Material","Sign": "eq","Value": "44003071","Operator": ""}]}}
body={"Filters": {"Filter": [{"Field": "SalesOrganization","Sign": "eq","Value": "2141","Operator": ""}]}}
#body={"Filters": {"Filter": [{"Field": "SalesOrganization","Sign": "eq","Value": "2141","Operator": "and"},{"Field": "Customer","Sign": "eq","Value": "1014919","Operator": ""}]}}

## Counters for summary
stats = {
    "records_total": 0,
    "records_success": 0,
    "records_failed": 0,
    "import_id": 0,
    "failed_materials": []
}

def writeToFile(text):
	filename = LOG_FILE
	f = open( filename, "a", encoding='utf-8')
	f.write(text + "\n")
	f.close()

def logSummary():
    writeToFile('\n' + '='*60)
    writeToFile('PRICE IMPORT SUMMARY')
    writeToFile('='*60)
    writeToFile(f'  Import ID              : {stats["import_id"]}')
    writeToFile(f'  Total Records Received : {stats["records_total"]}')
    writeToFile(f'  Records Inserted OK    : {stats["records_success"]}')
    writeToFile(f'  Records Failed         : {stats["records_failed"]}')
    if stats["failed_materials"]:
        writeToFile(f'  Failed Materials: {", ".join(stats["failed_materials"][:20])}')
        if len(stats["failed_materials"]) > 20:
            writeToFile(f'  ... and {len(stats["failed_materials"])-20} more')
    writeToFile('='*60 + '\n')

def get_access_token(url):
    response = requests.post(
        url,
        data={"grant_type": granttype},
        auth=(clientid, clientsecret),
    )
    return response.json()["access_token"]




def getSAPPrices():
    writeToFile(str(datetime.datetime.now())+' [API] Requesting OAuth token...')
    token=get_access_token(tokenlink)
    writeToFile(str(datetime.datetime.now())+' [API] Token obtained successfully')

    headers = {"Authorization": f"Bearer {token}","Content-Type":"application/json","Accept":"*/*","Connection":"keep-alive","Accept-Encoding":"gzip, deflate, br"}
    writeToFile(str(datetime.datetime.now())+' [API] Calling SAP Price API...')
    response = requests.get(
        link,
        data=json.dumps(body, sort_keys=True, indent=4),
        timeout=(2,3600),
        headers=headers,
    )
    writeToFile(str(datetime.datetime.now())+f' [API] Response status: {response.status_code}')
    if response.status_code == 200:
        insertPricesIntoStaging (response.json())
    else:
        writeToFile(str(datetime.datetime.now())+f' [API FAILED] SAP returned status {response.status_code}')
    
def parseJSON(response):
    data=response
    print(data["ZDD_I_SD_PIM_MaterialPrice"]["ZDD_I_SD_PIM_MaterialPriceType"])
    for ord in data["ZDD_I_SD_PIM_MaterialPrice"]["ZDD_I_SD_PIM_MaterialPriceType"]:
        print("BasePrice:", ord["BasePrice"])
        print("BasicMaterial:", ord["BasicMaterial"])
        print('---')

    
def insertPricesIntoStaging(data):
    mydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
    mycursor = mydb.cursor()

    cursor = mydb.cursor()

    query = 'SELECT ifnull(max(importid),0) as id from stg_prices'
    cursor.execute(query)
    maxid = cursor.fetchone()
    maxid=int(maxid[0])+1
    stats["import_id"] = maxid
    writeToFile(str(datetime.datetime.now())+f' [DB] New import batch ID: {maxid}')

    records = data["ZDD_I_SD_PIM_MaterialPrice"]["ZDD_I_SD_PIM_MaterialPriceType"]
    stats["records_total"] = len(records)
    writeToFile(str(datetime.datetime.now())+f' [DB] Inserting {len(records)} price records into stg_prices...')

    for idx, ord in enumerate(records, 1):
        try:
            BasePrice=ord["BasePrice"]
            BasicMaterial=ord["BasicMaterial"]
            Customer=ord["Customer"]
            ConditionRecord=ord["ConditionRecord"]
            MaterialOldNumber=ord["MaterialOldNumber"]
            ScaleType=ord["ScaleType"]
            ScaleFrom=ord["ScaleFrom"]
            DistributionChannel=ord["DistributionChannel"]
            SalesOrganization=ord["SalesOrganization"]
            Material=ord["Material"]
            PricingReferenceMaterial=ord["PricingReferenceMaterial"]
            MinPrice=ord["MinPrice"]
            ScalePrice=ord["ScalePrice"]
            PriceListType=ord["PriceListType"]
            IsGeneric=ord["IsGeneric"]
            PriceUnit=ord["PriceUnit"]
            ConditionRecordSeqNo=ord["ConditionRecordSeqNo"]
            ConditionType=ord["ConditionType"]
            ScaleTo=ord["ScaleTo"]
            Price100=ord["Price100"]
            Application=ord["Application"]
            PreviousAccountNo=ord["PreviousAccountNo"]
            ScaleNumber=ord["ScaleNumber"]

            cursor.execute("INSERT INTO stg_prices(ImportDate,DistributionChannel,PricingReferenceMaterial,PreviousAccountNo,ScaleNumber,Application,BasePrice,ScalePrice,SalesOrganization,Material,MaterialOldNumber,PriceListType,ConditionRecordSeqNo,ScaleTo,ScaleType,Customer,ConditionType,BasicMaterial,ConditionRecord,IsGeneric,ScaleFrom,PriceUnit,Price100,MinPrice,importid) values(now(),'"+DistributionChannel+"','"+PricingReferenceMaterial+"','"+PreviousAccountNo+"','"+ScaleNumber+"','"+Application+"','"+BasePrice+"','"+ScalePrice+"','"+SalesOrganization+"','"+Material+"','"+MaterialOldNumber+"','"+PriceListType+"','"+ConditionRecordSeqNo+"','"+ScaleTo+"','"+ScaleType+"','"+Customer+"','"+ConditionType+"','"+BasicMaterial+"','"+ConditionRecord+"','"+IsGeneric+"','"+ScaleFrom+"','"+PriceUnit+"','"+Price100+"','"+MinPrice+"',"+str(maxid)+");")
            mydb.commit()
            stats["records_success"] += 1

            # Log progress every 100 records
            if idx % 100 == 0:
                writeToFile(str(datetime.datetime.now())+f' [PROGRESS] {idx}/{len(records)} price records inserted...')

        except Exception as e:
            stats["records_failed"] += 1
            stats["failed_materials"].append(Material)
            writeToFile(str(datetime.datetime.now())+f' [FAILED] Material {Material} (Customer: {Customer}) - Error: {str(e)}')

    writeToFile(str(datetime.datetime.now())+f' [DB] Finished inserting all price records')

    #close the connection to the database.
    mycursor.close()
    cursor.close()
    
######
# MAIN
######
if __name__ == '__main__':
    writeToFile('='*60)
    writeToFile('SAP PRICE IMPORT - Integration start '+str(datetime.datetime.now()))
    writeToFile('='*60)
    writeToFile(f'  Database: {database} @ {host}')
    writeToFile(f'  SAP Endpoint: {link}')
    writeToFile(f'  Filter: {json.dumps(body)}')
    writeToFile('-'*60)
    getSAPPrices()
    logSummary()
    writeToFile('Integration Done '+str(datetime.datetime.now()))
    
