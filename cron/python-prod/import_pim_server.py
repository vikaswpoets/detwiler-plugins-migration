#!/bin/bash
import traceback
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
#DATE=(date +'%Y%m%d_%H%M%S')
datenow=datetime.datetime.now()
DATE= datenow.strftime("%Y%m%d_%H%M%S")
#DATE=str(datetime.datetime.now())
#LOG_FILE="./logs/import_pim_"+DATE+".log"
LOG_FILE="/home/datext_infola_jos/python/logs/import_pim_"+DATE+".log"
#/home/datext_infola_jos/python


SourcePath = '/var/www/pim-gi/storage/app/public/productthumbs/' 
SourcePathImages = '/var/www/pim-gi/storage/app/public/productimages/' 
SourcePathFiles = '/var/www/pim-gi/storage/app/public/productfiles/' 
Targetpath= '/var/www/gi-prod/wp-content/uploads/'+datenow.strftime("%Y")+'/'+datenow.strftime("%m")+'/'
#print(Targetpath)
#Backuppath= '/home/ext.infolabix/xml_files/backup/'

#database
host='localhost'
database='import_gi_prod'
dbuser='importgi'
dbpass='ZAQ123wsx'

# path variables
localpath = "/home/datext.infola_jos/python/"
remotepath = "/"
logspath = "/home/datext.infola_jos/python/logs/"

# server configuration
ftp_host = "10.169.25.226"
ft_user = "giuser"
ft_pass="2024#Abril%10"

ftp_path_thumb="/var/www/pim-gi/storage/app/public/productthumbs/"
ftp_path_files="/var/www/pim-gi/storage/app/public/productimages/"
ftp_path_images="/var/www/pim-gi/storage/app/public/productfiles/"


##
#       Write to file
##
## Counters for summary
stats = {
    "products_total": 0,
    "products_success": 0,
    "products_failed": 0,
    "files_copied": 0,
    "files_failed": 0,
    "sp_success": 0,
    "sp_failed": 0,
    "failed_products": [],
    "failed_files": []
}

def writeToFile(text):
	filename = LOG_FILE
	f = open( filename, "a", encoding='utf-8')
	f.write(text + "\n")
	f.close()

def logSummary():
    writeToFile('\n' + '='*60)
    writeToFile('INTEGRATION SUMMARY')
    writeToFile('='*60)
    writeToFile(f'  Total Products Processed : {stats["products_total"]}')
    writeToFile(f'  Products Imported OK     : {stats["products_success"]}')
    writeToFile(f'  Products Failed          : {stats["products_failed"]}')
    writeToFile(f'  Files Copied OK          : {stats["files_copied"]}')
    writeToFile(f'  Files Copy Failed        : {stats["files_failed"]}')
    writeToFile(f'  SP Executions OK         : {stats["sp_success"]}')
    writeToFile(f'  SP Executions Failed     : {stats["sp_failed"]}')
    if stats["failed_products"]:
        writeToFile(f'\n  Failed Product IDs: {", ".join(str(p) for p in stats["failed_products"])}')
    if stats["failed_files"]:
        writeToFile(f'  Failed Files: {", ".join(stats["failed_files"])}')
    writeToFile('='*60 + '\n')

def getLastUpdateDate():
    mydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
    mycursor = mydb.cursor()
            
    writeToFile(str(datetime.datetime.now())+' "retrieving last update date"')
    mycursor.execute("select ifnull(max(dt),date_add(now(),interval -1 day)) as dt from import_gi.import;");
    record = mycursor.fetchone()[0]
    mydb.commit()
    print("last date: ", record)
    return record.strftime("%Y-%m-%d %H:%M:%S")


#pimaddresslist="http://10.169.28.244/api/getProductListToJson/"+getLastUpdateDate()+"/?api_token=OTrUdp7VCLTSKdqS3Mh2gHKJkCHtZznynDfGp7fgBaPXc1ZEmdEed1U4xwmt"
pimaddresslist="http://10.169.25.226/api/getProductListToJsonFromSyncOnDemand/?api_token=OTrUdp7VCLTSKdqS3Mh2gHKJkCHtZznynDfGp7fgBaPXc1ZEmdEed1U4xwmt"
pimaddressfinish="http://10.169.25.226/api/setsyncTableDone?api_token=OTrUdp7VCLTSKdqS3Mh2gHKJkCHtZznynDfGp7fgBaPXc1ZEmdEed1U4xwmt"
pimproductddress="http://10.169.25.226/api/getProductToJson/"
pimaddress="http://10.169.25.226/api/exporttojson?api_token=OTrUdp7VCLTSKdqS3Mh2gHKJkCHtZznynDfGp7fgBaPXc1ZEmdEed1U4xwmt"
regionsaddress="http://10.169.25.226/api/getRegionsToJson?api_token=OTrUdp7VCLTSKdqS3Mh2gHKJkCHtZznynDfGp7fgBaPXc1ZEmdEed1U4xwmt"
token="?api_token=OTrUdp7VCLTSKdqS3Mh2gHKJkCHtZznynDfGp7fgBaPXc1ZEmdEed1U4xwmt"

def getJsonFomPim(address):
    writeToFile(str(datetime.datetime.now())+' "reading pim data from service"')
    api_response = requests.get(address)
    writeToFile(str(datetime.datetime.now())+' "finish reading pim data from service"')
    api_responsetext=api_response.text.replace("'","''")
    return api_responsetext;
    
def importRegions():
    writeToFile(str(datetime.datetime.now())+' "reading region data from pim service"')
    try:
        list=getJsonFomPim(regionsaddress)
        #print(list)
    except:
        writeToFile(str(datetime.datetime.now())+' "Failed loading list: "')
        list=getJsonFomPim(regionsaddress)

    try:    
        mydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
        mycursor = mydb.cursor()
        writeToFile(str(datetime.datetime.now())+' "Inserting pim data into staging area"')
        mycursor.execute("insert into importregions values(now(),'"+list+"')");
        mydb.commit()
        writeToFile(str(datetime.datetime.now())+' "Finished pim data into staging area"')
    except mysql.connector.Error as e:
        writeToFile(str(datetime.datetime.now())+" Inserting pim data into staging area: "+ e)
        print(str(datetime.datetime.now())+"Inserting pim data into staging area: ", e)
    finally:
        if mydb.is_connected():
            mydb.close()
            mycursor.close()
            writeToFile(str(datetime.datetime.now())+' "closed connection for inserting data into staging"')        


def importPim():
    writeToFile(str(datetime.datetime.now())+' "reading pim data from service"')
    #print (getJsonFomPim(pimaddresslist))
    try:
        print(pimaddresslist)
        list=json.loads(getJsonFomPim(pimaddresslist))
        print(pimaddresslist)
        print(list)
    except:
        writeToFile(str(datetime.datetime.now())+' "Failed loading list: "')
        list=json.loads(getJsonFomPim(pimaddresslist))
        
    writeToFile(str(datetime.datetime.now())+f' Found {len(list)} products to process')
    stats["products_total"] = len(list)

    for idx, prod in enumerate(list, 1):
        writeToFile(str(datetime.datetime.now())+f' [{idx}/{len(list)}] Processing product ID: {prod}')
        #get product json
        print("%s%s%s"%(pimproductddress, prod,token))

        #write product json to database
        try:
            productjson= getJsonFomPim("%s%s%s"%(pimproductddress, prod,token))
            # try again in case it failed
            try:
                json.loads(productjson)
            except:
                writeToFile(str(datetime.datetime.now())+f' [RETRY] Product {prod} - JSON parse failed, retrying API call')
                productjson= getJsonFomPim("%s%s%s"%(pimproductddress, prod,token))


            mydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
            mycursor = mydb.cursor()

            mycursor.execute("insert into import values(now(),'"+mydb._cmysql.escape_string(productjson).decode("utf-8")+"')");
            mydb.commit()
            stats["products_success"] += 1
            writeToFile(str(datetime.datetime.now())+f' [SUCCESS] Product {prod} imported into staging')

        except mysql.connector.Error as e:
            stats["products_failed"] += 1
            stats["failed_products"].append(prod)
            writeToFile(str(datetime.datetime.now())+f' [FAILED] Product {prod} - DB Error: {str(e)}')
            print(str(datetime.datetime.now())+" Error Inserting pim data into staging area: ", e)
        except Exception as e:
            stats["products_failed"] += 1
            stats["failed_products"].append(prod)
            writeToFile(str(datetime.datetime.now())+f' [FAILED] Product {prod} - Error: {str(e)}')
        finally:
            if mydb.is_connected():
                mydb.close()
                mycursor.close()
        importData()
        copyFiles()

#---------------------------------------------------------------------------------------------------




def getNewDataFomPim():
    writeToFile(str(datetime.datetime.now())+' "reading pim data from service"')
    api_response = requests.get(pimaddress)
    writeToFile(str(datetime.datetime.now())+' "finish reading pim data from service"')
    api_responsetext=api_response.text.replace("'","''")

    try:
        mydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
        mycursor = mydb.cursor()
        
        writeToFile(str(datetime.datetime.now())+' "Inserting pim data into staging area"')
        mycursor.execute("insert into import values(now(),'"+api_responsetext+"')");
        mydb.commit()
        writeToFile(str(datetime.datetime.now())+' "Finished pim data into staging area"')
        
    except mysql.connector.Error as e:
        writeToFile(str(datetime.datetime.now())+" Inserting pim data into staging area: "+ e)
        print(str(datetime.datetime.now())+"Inserting pim data into staging area: ", e)
    finally:
        if mydb.is_connected():
            mydb.close()
            mycursor.close()
            writeToFile(str(datetime.datetime.now())+' "closed connection for inserting data into staging"')    

        
def importData():
    try:
        mydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)

        mycursor = mydb.cursor()

        writeToFile(str(datetime.datetime.now())+' [SP] Executing sp_migrate_data_from_pim_v11...')
        result_args = mycursor.callproc("sp_migrate_data_from_pim_v11", [0,])
        mydb.commit()
        stats["sp_success"] += 1
        writeToFile(str(datetime.datetime.now())+' [SP] sp_migrate_data_from_pim_v11 completed successfully')

    except mysql.connector.Error as e:
        stats["sp_failed"] += 1
        writeToFile(str(datetime.datetime.now())+f' [SP FAILED] sp_migrate_data_from_pim_v11 Error: {str(e)}')
    finally:
        if mydb.is_connected():
            mydb.close()
            mycursor.close()






# connection
sftp = None


def getFile(file,sourcepath,destiny):
    with sftp.cd(sourcepath) :
        sftp.get(file,destiny)   
    

def copyFileFromFTP(file):  #copyFileFromFTP

    try:
        cnopts = pysftp.CnOpts()
        cnopts.hostkeys = None

        global sftp
        sftp = pysftp.Connection(ftp_host , username=ft_user, password=ft_pass, cnopts=cnopts)

        getFile(file,ftp_path_thumb,Targetpath+file)
        stats["files_copied"] += 1
        writeToFile(str(datetime.datetime.now())+f' [FILE OK] Copied thumbnail: {file}')
    except Exception  as e:
        try:
            getFile(file,ftp_path_images,Targetpath+file)
            stats["files_copied"] += 1
            writeToFile(str(datetime.datetime.now())+f' [FILE OK] Copied image: {file}')
        except :
            try:
                getFile(file,ftp_path_files,Targetpath+file)
                stats["files_copied"] += 1
                writeToFile(str(datetime.datetime.now())+f' [FILE OK] Copied document: {file}')
            except:
                stats["files_failed"] += 1
                stats["failed_files"].append(file)
                writeToFile(str(datetime.datetime.now())+f' [FILE FAILED] Could not copy: {file} - {str(e)}')
                traceback.print_exc()

        sftp.close()
       

def copyFile(file): #copyFileFromFilesystem
    try:
        print(str(datetime.datetime.now())+" Copying file: ", file)
        shutil.copyfile(SourcePath+file, Targetpath+file)        
        print (str(datetime.datetime.now())+" Copied Thumbnail file: ", file)
    except IOError as e:
        #print ("Unable to copy file. %s" % e)
        #print ("Trying image folder")
        try:
            shutil.copyfile(SourcePathImages+file, Targetpath+file)
            print (str(datetime.datetime.now())+" Copied image file: ", file)
        except:
            try:
                #print ("Trying Files folder")
                shutil.copyfile(SourcePathFiles+file, Targetpath+file)
                print (str(datetime.datetime.now())+" Copied file: ", file)
            except:
                writeToFile(str(datetime.datetime.now())+' "Error copying file: "'+file)

    
##
#	Get all files
##
def getFiles():

	# specify additional connection options using the pysftp
	cnopts = pysftp.CnOpts()
	cnopts.hostkeys = None

	global sftp
	sftp = pysftp.Connection(ftp_host , username=ft_user, password=ft_pass, cnopts=cnopts)
	sftp.walktree(remotepath, fileHandler, emptyHandler, emptyHandler)
	sftp.close()
    
def copyFiles():
    try:
        print("Copy files!!")
        connection = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
        sql_select_Query = "SELECT distinct filename FROM files_to_transfer where nullif(filename, 'null') is not null  and integration_id=(select max(integration_id) from files_to_transfer);"
        cursor = connection.cursor()
        cursor.execute(sql_select_Query)
        # get all records
        records = cursor.fetchall()
        for row in records:
            writeToFile(str(datetime.datetime.now())+' copying file: '+row[0])
            copyFileFromFTP(row[0])

    except mysql.connector.Error as e:
        writeToFile(str(datetime.datetime.now())+' "Error reading data from MySQL table"')
        print("Error reading data from MySQL table", e)
    finally:
        if connection.is_connected():
            connection.close()
            cursor.close()
            print("MySQL connection is closed")

def setFinishProcessPim():
    writeToFile(str(datetime.datetime.now())+' "Finishing pim process"')
    api_response = requests.get(pimaddressfinish)
    return;
    
######
# MAIN
######
if __name__ == '__main__':
    writeToFile('='*60)
    writeToFile('PIM PRODUCT IMPORT - Integration start '+str(datetime.datetime.now()))
    writeToFile('='*60)
    writeToFile(f'  Database: {database} @ {host}')
    writeToFile(f'  Target Path: {Targetpath}')
    writeToFile(f'  PIM Server: {ftp_host}')
    writeToFile('-'*60)
    importPim()
    setFinishProcessPim()
    writeToFile(str(datetime.datetime.now())+' PIM sync finish endpoint called')
    logSummary()
    writeToFile('Integration Done '+str(datetime.datetime.now()))
    
