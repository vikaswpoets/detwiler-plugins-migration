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


def writeToFile(text):
	filename = LOG_FILE
	f = open( filename, "a", encoding='utf-8')
	f.write(text + "\n")
	f.close()

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
        
        stgmydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
        importmydb = mysql.connector.connect(host=importhost,user=importdbuser,password=importdbpass,database=importdatabase)
        
        stgcursor = stgmydb.cursor()
        importcursor = importmydb.cursor()
        
        stgquery = 'SELECT id,invoice_number, order_number,customer_number,pdf_path FROM stg_pim_gi.stg_invoices where imported is null;'    
        stgcursor.execute(stgquery)
        importquery = 'delete from import_gi_prod.stg_invoices;'
        importcursor.execute(importquery)
        
        myresult = stgcursor.fetchall()

        for row in myresult:
            importquery = "INSERT INTO import_gi_prod.stg_invoices(invoice_number, order_number,customer_number,pdf_path,importid) VALUES('"+str(row[1])+"','"+str(row[2])+"','"+str(row[3])+"','"+str(row[4])+"',"+str(maxid)+");"
            importcursor.execute(importquery)
            if(copyFileFromFTP(str(row[4]))==1): # copy file
                stgquery = "update stg_pim_gi.stg_invoices set imported=1 where id='"+str(row[0])+"';"
                stgcursor.execute(stgquery)
                stgmydb.commit()
            
            importmydb.commit()
            print("setting invoice "+str(row[0])+" as imported")
        stgcursor.close()
        
        importquery = "call sp_SyncInvoices();"
        importcursor.execute(importquery)
        importmydb.commit()
        importcursor.close()

    except mysql.connector.Error as e:
        writeToFile(str(datetime.datetime.now())+" Failed syncing invoices: "+ str(e))
        print(str(datetime.datetime.now())+" Failed syncing invoices: ", str(e))
    finally:
        if stgmydb.is_connected():
            stgmydb.close()
            writeToFile(str(datetime.datetime.now())+' "closed connection to staging db"')
        if importmydb.is_connected():
           importmydb.close()
           writeToFile(str(datetime.datetime.now())+' "closed connection to import db"')

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
    #touch LOG_FILE
    print('Integration start '+str(datetime.datetime.now()))
    writeToFile('Integration start '+str(datetime.datetime.now()))
    getNewInvoices()
    writeToFile('Integration Done '+str(datetime.datetime.now()))
    print('Integration done '+str(datetime.datetime.now()))
    
