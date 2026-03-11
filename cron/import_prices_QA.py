#!/bin/bash
import shutil
import mysql.connector
import os
import datetime
import pysftp
import requests
import urllib.request as requesturllib
import html
import json_stream.requests
import json
from io import open

import fnmatch
from ftplib import FTP

import ftplib

import pysftp
from io import open

import urllib3, socket
from urllib3.connection import HTTPConnection
HTTPConnection.default_socket_options = ( 
    HTTPConnection.default_socket_options + [
    (socket.SOL_SOCKET, socket.SO_SNDBUF, 10000000),
    (socket.SOL_SOCKET, socket.SO_RCVBUF, 10000000)])

LOG="./logs"
datenow=datetime.datetime.now()
DATE= datenow.strftime("%Y%m%d_%H%M%S")
LOG_FILE="./logs/import_prices_"+DATE+".log"


#database
host='localhost'
database='stg_pim_gi'
dbuser='jmartins'
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
    #print(response.json())
    return response.json()["access_token"]

def connectViaStream(headers):
    with requests.get(        
        link,
        data=json.dumps(body, sort_keys=True, indent=4),stream=True, timeout=(100,100),
        headers=headers,) as response:
        data = json_stream.requests.load(response)
    print(response)

def connectViaRegular(headers):
    response = requesturllib.Request(
    #response = requests.get(
        link,
        data=bytes(json.dumps(body, sort_keys=True, indent=4),'utf-8'),
        headers=headers,
        method='GET'
    )
    webURL = requesturllib.urlopen(response)
    data = webURL.read()
    print(data)
    encoding = webURL.info().get_content_charset('utf-8')
    JSON_object = json.loads(data.decode(encoding))
    parseJSON(JSON_object)

#    with requesturllib.urlopen(response) as f:
#        pass
#    print(f.status)
    
    
    #print(response.json())


def connectViaRegularChunks(headers):
    response = requests.get(
        link,
        data=json.dumps(body, sort_keys=True, indent=4), timeout=(100,300), stream=True,
        auth=(clientid, clientsecret),
        headers=headers,
    )
    
    response_content = []
    
    for chunk in (response.raw.read_chunked()):
        print(chunk)    

    print(response.status)
#    if(response.ok):
#        for chunk in response.iter_content(chunk_size=128):
#            response_content.append(chunk)
#            print(Chunk)
#        response.close()
#    else:
#        response.raise_for_status() 
        
    print("CHUNK end")
                    
def getSAPPrices():
    token=get_access_token(tokenlink)
    
    headers = {"Authorization": f"Bearer {token}","Content-Type":"application/json","Accept":"*/*","Connection":"keep-alive","Accept-Encoding":"gzip, deflate, br",'User-Agent': 'PostmanRuntime/7.39.0'}

    #connectViaStream(headers)
    #connectViaRegular(headers)
    connectViaRegularChunks(headers)

    
    #my_json = response_content.replace("'", '"')
    #print(json.loads(response_content[0].decode("utf-8")))
    #print(json.dumps(json.loads(response_content) ))
    #print(response.json())
    #insertPricesIntoStaging (response.json())
    #parseJSON (response.json())
    
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
            
    #writeToFile(str(datetime.datetime.now())+' "Inserting pim data into staging area"')
    cursor = mydb.cursor()
    
    query = 'SELECT ifnull(max(importid),0) as id from stg_prices'
    cursor.execute(query)
    maxid = cursor.fetchone()
    maxid=int(maxid[0])+1    
    print("MAXID:",maxid)

    for ord in data["ZDD_I_SD_PIM_MaterialPrice"]["ZDD_I_SD_PIM_MaterialPriceType"]:
        #mycursor.execute("insert into stg_import.stg_prices values(now(),'"+mydb._cmysql.escape_string(productjson).decode("utf-8")+"')");
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
        print("INSERT INTO stg_prices(ImportDate,DistributionChannel,PricingReferenceMaterial,PreviousAccountNo,ScaleNumber,Application,BasePrice,ScalePrice,SalesOrganization,Material,MaterialOldNumber,PriceListType,ConditionRecordSeqNo,ScaleTo,ScaleType,Customer,ConditionType,BasicMaterial,ConditionRecord,IsGeneric,ScaleFrom,PriceUnit,Price100,MinPrice,importid) values(now(),'"+DistributionChannel+"','"+PricingReferenceMaterial+"','"+PreviousAccountNo+"','"+ScaleNumber+"','"+Application+"','"+BasePrice+"','"+ScalePrice+"','"+SalesOrganization+"','"+Material+"','"+MaterialOldNumber+"','"+PriceListType+"','"+ConditionRecordSeqNo+"','"+ScaleTo+"','"+ScaleType+"','"+Customer+"','"+ConditionType+"','"+BasicMaterial+"','"+ConditionRecord+"','"+IsGeneric+"','"+ScaleFrom+"','"+PriceUnit+"','"+Price100+"','"+MinPrice+"',"+str(maxid)+");")
        mydb.commit()

    #close the connection to the database.
    
    cursor.close()
    
######
# MAIN
######
if __name__ == '__main__':
    #touch LOG_FILE
    writeToFile('Integration start '+str(datetime.datetime.now()))
    getSAPPrices()
    writeToFile('Integration Done '+str(datetime.datetime.now()))
    
