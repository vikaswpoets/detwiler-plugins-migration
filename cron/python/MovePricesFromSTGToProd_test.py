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


def writeToFile(text):
	filename = LOG_FILE
	f = open( filename, "a", encoding='utf-8')
	f.write(text + "\n")
	f.close()


def runSiteSync():
    mydb = mysql.connector.connect(host=importhost,user=importdbuser,password=importdbpass,database=importdatabase)
            
    #writeToFile(str(datetime.datetime.now())+' "Inserting pim data into staging area"')
    cursor = mydb.cursor()
    print('Executing Site Sync')
    query = 'call import_gi_prod.sp_ImportPricesFromSAP(1);'
    cursor.execute(query)
    mydb.commit()
    cursor.close()
    print('Done')
    
def getStgPrices():
    mydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
            
    cursor = mydb.cursor()
    
    mydbimport = mysql.connector.connect(host=importhost,user=importdbuser,password=importdbpass,database=importdatabase)
    mycursorimport = mydbimport.cursor()
    importcursor = mydbimport.cursor()
    
    
    query = "SELECT stg_prices.DistributionChannel,stg_prices.PricingReferenceMaterial,stg_prices.PreviousAccountNo,stg_prices.ScaleNumber,stg_prices.Application,stg_prices.BasePrice,stg_prices.ScalePrice,stg_prices.SalesOrganization,stg_prices.Material,stg_prices.MaterialOldNumber,stg_prices.PriceListType,stg_prices.ConditionRecordSeqNo,stg_prices.ScaleTo,stg_prices.ScaleType,stg_prices.Customer,stg_prices.ConditionType,stg_prices.BasicMaterial,stg_prices.ConditionRecord,stg_prices.IsGeneric,stg_prices.ScaleFrom,stg_prices.PriceUnit,stg_prices.Price100,stg_prices.MinPrice,stg_prices.importid,stg_prices.created_at,stg_prices.updated_at,stg_prices.deleted_at FROM stg_pim_gi.stg_prices where importid=(select max(importid) from stg_pim_gi.stg_prices where salesorganization=2142);"
    
    importquery = "truncate table import_gi_prod.stg_prices;"
    importcursor.execute(importquery)
    mydbimport.commit()
    
    cursor.execute(query)
    myresult = cursor.fetchall()
    list=[]
    for row in myresult:
        list.append(row)
        importquery = "INSERT INTO import_gi_prod.stg_prices(DistributionChannel,PricingReferenceMaterial,PreviousAccountNo,ScaleNumber,Application,BasePrice,ScalePrice,SalesOrganization,Material,MaterialOldNumber,PriceListType,ConditionRecordSeqNo,ScaleTo,ScaleType,Customer,ConditionType,BasicMaterial,ConditionRecord,IsGeneric,ScaleFrom,PriceUnit,Price100,MinPrice,created_at) VALUES('"+str(row[0])+"','"+str(row[1])+"','"+str(row[2])+"','"+str(row[3])+"','"+str(row[4])+"','"+str(row[5])+"','"+str(row[6])+"','"+str(row[7])+"','"+str(row[8])+"','"+str(row[9])+"','"+str(row[10])+"','"+str(row[11])+"','"+str(row[12])+"','"+str(row[13])+"','"+str(row[14])+"','"+str(row[15])+"','"+str(row[16])+"','"+str(row[17])+"','"+str(row[18])+"','"+str(row[19])+"','"+str(row[20])+"','"+str(row[21])+"','"+str(row[22])+"','"+str(row[24])+"');"
        #print(importquery)
        importcursor.execute(importquery)
        mydbimport.commit()
    cursor.close()
    importcursor.close()
    #print(list[0])
    return list
    
######
# MAIN
######
if __name__ == '__main__':
    #touch LOG_FILE
    writeToFile('Integration start '+str(datetime.datetime.now()))
    getStgPrices()
#    runSiteSync()
    writeToFile('Integration Done '+str(datetime.datetime.now()))
    
