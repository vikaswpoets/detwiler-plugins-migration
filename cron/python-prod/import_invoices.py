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
LOG_FILE="./logs/import_invoices_"+DATE+".log"


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

# path variables
localpath = "/home/ext.infolabix/ftp_files/"
remotepath = "/"
logspath = "/home/ext.infolabix/logs/"

# server configuration
ftp_host = "10.169.25.226"
ft_user = "giuser"
ft_pass="2024#Abril%10"

#ftp_host = "168.63.37.239"
#ft_user = "giuser"
#ft_pass = "Labix#2020#serial"

SourcePath = '/var/www/pim-gi/storage/app/public/invoices/'
#SourcePath= 'C:/xampp8.0/htdocs/pim-gi/public/storage/invoices/'
#Targetpath= '/var/www/gi-prod/wp-content/uploads/'+datenow.strftime("%Y")+'/'+datenow.strftime("%m")+'/'
Targetpath= '/var/www/gi-prod/wp-content/uploads/woocommerce_uploads/'
#Targetpath= '/home/adminfolabix/testfolder/'


## Counters for summary
stats = {
    "invoices_total": 0,
    "invoices_success": 0,
    "invoices_failed": 0,
    "files_copied": 0,
    "files_failed": 0,
    "import_id": 0,
    "sp_status": "not_run",
    "failed_invoices": [],
    "failed_files": []
}

def writeToFile(text):
	filename = LOG_FILE
	f = open( filename, "a", encoding='utf-8')
	f.write(text + "\n")
	f.close()

def logSummary():
    writeToFile('\n' + '='*60)
    writeToFile('INVOICE IMPORT SUMMARY')
    writeToFile('='*60)
    writeToFile(f'  Import ID              : {stats["import_id"]}')
    writeToFile(f'  Total Invoices Found   : {stats["invoices_total"]}')
    writeToFile(f'  Invoices Imported OK   : {stats["invoices_success"]}')
    writeToFile(f'  Invoices Failed        : {stats["invoices_failed"]}')
    writeToFile(f'  PDF Files Copied OK    : {stats["files_copied"]}')
    writeToFile(f'  PDF Files Failed       : {stats["files_failed"]}')
    writeToFile(f'  SP SyncInvoices        : {stats["sp_status"]}')
    if stats["failed_invoices"]:
        writeToFile(f'  Failed Invoice IDs: {", ".join(str(i) for i in stats["failed_invoices"])}')
    if stats["failed_files"]:
        writeToFile(f'  Failed Files: {", ".join(stats["failed_files"])}')
    writeToFile('='*60 + '\n')

def getImportId():
    importmydb2 = mysql.connector.connect(host=importhost,user=importdbuser,password=importdbpass,database=importdatabase)
            
    cursor = importmydb2.cursor()
    
    query = 'SELECT ifnull(max(importid),0) as id from stg_invoices'
    cursor.execute(query)
    maxid = cursor.fetchone()
    maxid=int(maxid[0])+1 
    #close the connection to the database.
    cursor.close()
    if importmydb2.is_connected():
        importmydb2.close()
        #print("Closed connection to import getting maxid")
    return maxid
    
def getNewInvoices():
    try:
        maxid=getImportId()
        stats["import_id"] = maxid
        writeToFile(str(datetime.datetime.now())+f' [DB] New import batch ID: {maxid}')

        writeToFile(str(datetime.datetime.now())+f' [DB] Connecting to STG ({host}) and Import ({importhost})...')
        stgmydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
        importmydb = mysql.connector.connect(host=importhost,user=importdbuser,password=importdbpass,database=importdatabase)

        stgcursor = stgmydb.cursor()
        importcursor = importmydb.cursor()

        stgquery = 'SELECT id,invoice_number, order_number,customer_number,pdf_path FROM stg_pim_gi.stg_invoices where imported is null;'
        stgcursor.execute(stgquery)
        importquery = 'delete from import_gi_prod.stg_invoices;'
        importcursor.execute(importquery)

        myresult = stgcursor.fetchall()
        stats["invoices_total"] = len(myresult)
        writeToFile(str(datetime.datetime.now())+f' [DB] Found {len(myresult)} new invoices to process')

        if len(myresult) == 0:
            writeToFile(str(datetime.datetime.now())+' [INFO] No new invoices to import')

        for idx, row in enumerate(myresult, 1):
            invoice_id = str(row[0])
            invoice_num = str(row[1])
            order_num = str(row[2])
            customer_num = str(row[3])
            pdf_file = str(row[4])

            writeToFile(str(datetime.datetime.now())+f' [{idx}/{len(myresult)}] Processing Invoice #{invoice_num} (Order: {order_num}, Customer: {customer_num})')

            try:
                importquery = "INSERT INTO import_gi_prod.stg_invoices(invoice_number, order_number,customer_number,pdf_path,importid) VALUES('"+invoice_num+"','"+order_num+"','"+customer_num+"','"+pdf_file+"',"+str(maxid)+");"
                importcursor.execute(importquery)

                if(copyFileFromFTP(pdf_file)==1):
                    stgquery = "update stg_pim_gi.stg_invoices set imported=1 where id='"+invoice_id+"';"
                    stgcursor.execute(stgquery)
                    stgmydb.commit()
                    stats["invoices_success"] += 1
                    stats["files_copied"] += 1
                    writeToFile(str(datetime.datetime.now())+f' [SUCCESS] Invoice #{invoice_num} imported + PDF copied')
                else:
                    stats["invoices_success"] += 1
                    stats["files_failed"] += 1
                    stats["failed_files"].append(pdf_file)
                    writeToFile(str(datetime.datetime.now())+f' [PARTIAL] Invoice #{invoice_num} imported but PDF copy failed: {pdf_file}')

                importmydb.commit()

            except Exception as e:
                stats["invoices_failed"] += 1
                stats["failed_invoices"].append(invoice_num)
                writeToFile(str(datetime.datetime.now())+f' [FAILED] Invoice #{invoice_num} - Error: {str(e)}')

        stgcursor.close()

        writeToFile(str(datetime.datetime.now())+' [SP] Executing sp_SyncInvoices()...')
        try:
            importquery = "call sp_SyncInvoices();"
            importcursor.execute(importquery)
            importmydb.commit()
            stats["sp_status"] = "SUCCESS"
            writeToFile(str(datetime.datetime.now())+' [SP] sp_SyncInvoices completed successfully')
        except Exception as e:
            stats["sp_status"] = f"FAILED: {str(e)}"
            writeToFile(str(datetime.datetime.now())+f' [SP FAILED] sp_SyncInvoices Error: {str(e)}')
        importcursor.close()

    except mysql.connector.Error as e:
        writeToFile(str(datetime.datetime.now())+f' [CRITICAL] Failed syncing invoices: {str(e)}')
        print(str(datetime.datetime.now())+" Failed syncing invoices: ", str(e))
    finally:
        if stgmydb.is_connected():
            stgmydb.close()
        if importmydb.is_connected():
           importmydb.close()

def getFile(file,sourcepath,destiny):
    with sftp.cd(sourcepath) :
        sftp.get(file,destiny)   
    

def copyFileFromFTP(file):  #copyFileFromFTP
    try:
        print(str(datetime.datetime.now())+" Copying file: ", file)
        cnopts = pysftp.CnOpts()
        cnopts.hostkeys = None
        
        global sftp
        sftp = pysftp.Connection(ftp_host , username=ft_user, password=ft_pass, cnopts=cnopts)
        print(file)
        print(SourcePath)
        print(Targetpath+file)

        getFile(file,SourcePath,Targetpath+file)
        return 1
    except Exception  as e:    
        print (str(datetime.datetime.now())+" Error Copying file: ", file," : ", str(e)) 
        return 0
    finally:        
        sftp.close()
######
# MAIN
######
if __name__ == '__main__':
    writeToFile('='*60)
    writeToFile('INVOICE IMPORT - Integration start '+str(datetime.datetime.now()))
    writeToFile('='*60)
    writeToFile(f'  STG Database: {database} @ {host}')
    writeToFile(f'  Import Database: {importdatabase} @ {importhost}')
    writeToFile(f'  SFTP: {ftp_host}')
    writeToFile(f'  Source: {SourcePath}')
    writeToFile(f'  Target: {Targetpath}')
    writeToFile('-'*60)
    getNewInvoices()
    logSummary()
    writeToFile('Integration Done '+str(datetime.datetime.now()))
    print('Integration done '+str(datetime.datetime.now()))
    
