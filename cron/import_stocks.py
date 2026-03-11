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
database='stg_pim_gi'
dbuser='jmartins'
dbpass='ZAQ123wsx'

#link="https://e2515-iflmap.hcisbt.eu1.hana.ondemand.com/http/GICHANNELS/GET_DATA_STOCK_CDS"
#tokenlink="https://oauthasservices-a4b9bd800.hana.ondemand.com/oauth2/api/v1/token"
#link="https://l2515-iflmap.hcisbp.eu1.hana.ondemand.com/http/GICHANNELS/GET_DATA_STOCK_CDS"
link="https://l2515-iflmap.hcisbp.eu1.hana.ondemand.com/http/GICHANNELS/GET_DATA_MaterialStockReqr"
tokenlink="https://oauthasservices-a3c9ce896.hana.ondemand.com/oauth2/api/v1/token"
clientid="e27dfb2c-9961-3756-9720-32c99ec819ac"
clientsecret="9ad9a0c8-02ef-3253-993b-8faa20d6965b"
granttype="client_credentials"
tokenname="SAP - Client Credentials"

#body={"Filters": {"Filter": [{"Field": "Material","Sign": "eq","Value": "44003071","Operator": ""}]}}
#body={"Filters": {"Filter": [{"Field": "SalesOrganization","Sign": "eq","Value": "2141","Operator": ""}]}}
body={"Filters": {"Filter": [{"Field": "Plant","Sign": "eq","Value": "2130","Operator": ""}]}}
#body={"Filters": {"Filter": [{"Field": "SalesOrganization","Sign": "eq","Value": "1110","Operator": ""}]}}
#body={"Filters": {"Filter": [{"Field": "SalesOrganization","Sign": "eq","Value": "2141","Operator": "and"},{"Field": "Customer","Sign": "eq","Value": "1014919","Operator": ""}]}}

def writeToFile(text):
	filename = LOG_FILE
	f = open( filename, "a", encoding='utf-8')
	f.write(text + "\n")
	f.close()

def get_access_token(url):
    response = requests.post(
        url,
        data={"grant_type": granttype},
        auth=(clientid, clientsecret),
    )
    return response.json()["access_token"]



def getSAPStocks():
    token=get_access_token(tokenlink)
    
    headers = {"Authorization": f"Bearer {token}","Connection": "keep-alive"}
    response = requests.get(
        link,
        data=json.dumps(body, sort_keys=True, indent=4),
        headers=headers
    )
    #print(response)
    insertStocksIntoStaging (response.json())
    #parseJSON (response.json())

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
            
    #writeToFile(str(datetime.datetime.now())+' "Inserting pim data into staging area"')
    cursor = mydb.cursor()
    
    query = 'SELECT ifnull(max(importid),0) as id from stg_stocks'
    cursor.execute(query)
    maxid = cursor.fetchone()
    maxid=int(maxid[0])+1    

    for ord in data["ZDD_I_SD_PIM_MaterialStock"]["ZDD_I_SD_PIM_MaterialStockType"]:                 
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
    cursor.execute("commit;")
    cursor.execute("call sp_insertStocksIntoPortal;")
    #close the connection to the database.
    mydb.commit()
    cursor.close()
    
######
# MAIN
######
if __name__ == '__main__':
    #touch LOG_FILE
    writeToFile('Integration start '+str(datetime.datetime.now()))
    getSAPStocks()
    writeToFile('Integration Done '+str(datetime.datetime.now()))
    
