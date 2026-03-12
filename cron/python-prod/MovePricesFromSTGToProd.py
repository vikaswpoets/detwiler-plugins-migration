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

import datetime

LOG="./logs"
datenow=datetime.datetime.now()
DATE= datenow.strftime("%Y%m%d_%H%M%S")
LOG_FILE="./logs/import_prices_"+DATE+".log"


#stg database
host='10.169.25.226'
database='stg_pim_gi'
dbuser='pimgi'
dbpass='Adm@1234'

#import_gi database
importhost='localhost'
importdatabase='import_gi_prod'
importdbuser='importgi'
importdbpass='ZAQ123wsx'


## Counters for summary
stats = {
    "records_read": 0,
    "records_inserted": 0,
    "records_failed": 0,
    "source_import_id": 0,
    "sp_status": "not_run"
}

def writeToFile(text):
	filename = LOG_FILE
	f = open( filename, "a", encoding='utf-8')
	f.write(text + "\n")
	f.close()

def logSummary():
    writeToFile('\n' + '='*60)
    writeToFile('MOVE PRICES STG TO PROD SUMMARY')
    writeToFile('='*60)
    writeToFile(f'  Source Import ID          : {stats["source_import_id"]}')
    writeToFile(f'  Records Read from STG    : {stats["records_read"]}')
    writeToFile(f'  Records Inserted to Prod : {stats["records_inserted"]}')
    writeToFile(f'  Records Failed           : {stats["records_failed"]}')
    writeToFile(f'  SP ImportPricesFromSAP_v4: {stats["sp_status"]}')
    writeToFile('='*60 + '\n')


def runSiteSync():
    writeToFile(str(datetime.datetime.now())+' [SP] Executing sp_ImportPricesFromSAP_v4(1)...')
    try:
        mydb = mysql.connector.connect(host=importhost,user=importdbuser,password=importdbpass,database=importdatabase)
        cursor = mydb.cursor()
        query = 'call import_gi_prod.sp_ImportPricesFromSAP_v4(1);'
        cursor.execute(query)
        mydb.commit()
        cursor.close()
        stats["sp_status"] = "SUCCESS"
        writeToFile(str(datetime.datetime.now())+' [SP] sp_ImportPricesFromSAP_v4 completed successfully')
    except Exception as e:
        stats["sp_status"] = f"FAILED: {str(e)}"
        writeToFile(str(datetime.datetime.now())+f' [SP FAILED] sp_ImportPricesFromSAP_v4 Error: {str(e)}')
    
def getStgPrices():
    writeToFile(str(datetime.datetime.now())+f' [DB] Connecting to STG DB ({host}) and Import DB ({importhost})...')
    mydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)

    cursor = mydb.cursor()

    mydbimport = mysql.connector.connect(host=importhost,user=importdbuser,password=importdbpass,database=importdatabase)
    mycursorimport = mydbimport.cursor()
    importcursor = mydbimport.cursor()

    query = "SELECT stg_prices.DistributionChannel,stg_prices.PricingReferenceMaterial,stg_prices.PreviousAccountNo,stg_prices.ScaleNumber,stg_prices.Application,stg_prices.BasePrice,stg_prices.ScalePrice,stg_prices.SalesOrganization,stg_prices.Material,stg_prices.MaterialOldNumber,stg_prices.PriceListType,stg_prices.ConditionRecordSeqNo,stg_prices.ScaleTo,stg_prices.ScaleType,stg_prices.Customer,stg_prices.ConditionType,stg_prices.BasicMaterial,stg_prices.ConditionRecord,stg_prices.IsGeneric,stg_prices.ScaleFrom,stg_prices.PriceUnit,stg_prices.Price100,stg_prices.MinPrice,stg_prices.importid,stg_prices.created_at,stg_prices.updated_at,stg_prices.deleted_at FROM stg_pim_gi.stg_prices where importid=367;"

    writeToFile(str(datetime.datetime.now())+' [DB] Truncating import_gi_prod.stg_prices...')
    importquery = "truncate table import_gi_prod.stg_prices;"
    importcursor.execute(importquery)
    mydbimport.commit()
    writeToFile(str(datetime.datetime.now())+' [DB] Truncate completed')

    writeToFile(str(datetime.datetime.now())+f' [DB] Reading prices from STG (importid=367)...')
    cursor.execute(query)
    myresult = cursor.fetchall()
    stats["records_read"] = len(myresult)
    stats["source_import_id"] = 367
    writeToFile(str(datetime.datetime.now())+f' [DB] Found {len(myresult)} price records in STG')

    list=[]
    for idx, row in enumerate(myresult, 1):
        try:
            list.append(row)
            importquery = "INSERT INTO import_gi_prod.stg_prices(DistributionChannel,PricingReferenceMaterial,PreviousAccountNo,ScaleNumber,Application,BasePrice,ScalePrice,SalesOrganization,Material,MaterialOldNumber,PriceListType,ConditionRecordSeqNo,ScaleTo,ScaleType,Customer,ConditionType,BasicMaterial,ConditionRecord,IsGeneric,ScaleFrom,PriceUnit,Price100,MinPrice,created_at) VALUES('"+str(row[0])+"','"+str(row[1])+"','"+str(row[2])+"','"+str(row[3])+"','"+str(row[4])+"','"+str(row[5])+"','"+str(row[6])+"','"+str(row[7])+"','"+str(row[8])+"','"+str(row[9])+"','"+str(row[10])+"','"+str(row[11])+"','"+str(row[12])+"','"+str(row[13])+"','"+str(row[14])+"','"+str(row[15])+"','"+str(row[16])+"','"+str(row[17])+"','"+str(row[18])+"','"+str(row[19])+"','"+str(row[20])+"','"+str(row[21])+"','"+str(row[22])+"','"+str(row[24])+"');"
            importcursor.execute(importquery)
            mydbimport.commit()
            stats["records_inserted"] += 1

            if idx % 100 == 0:
                writeToFile(str(datetime.datetime.now())+f' [PROGRESS] {idx}/{len(myresult)} records moved to prod...')

        except Exception as e:
            stats["records_failed"] += 1
            writeToFile(str(datetime.datetime.now())+f' [FAILED] Row {idx} Material {str(row[8])} - Error: {str(e)}')

    writeToFile(str(datetime.datetime.now())+f' [DB] Finished moving {stats["records_inserted"]} records to prod')
    cursor.close()
    mycursorimport.close()
    importcursor.close()
    return list
    
######
# MAIN
######
if __name__ == '__main__':
    writeToFile('='*60)
    writeToFile('MOVE PRICES STG TO PROD - Integration start '+str(datetime.datetime.now()))
    writeToFile('='*60)
    writeToFile(f'  Source DB: {database} @ {host}')
    writeToFile(f'  Target DB: {importdatabase} @ {importhost}')
    writeToFile('-'*60)
    getStgPrices()
    runSiteSync()
    logSummary()
    writeToFile('Integration Done '+str(datetime.datetime.now()))
    
