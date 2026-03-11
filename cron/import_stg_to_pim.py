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
#DATE=(date +'%Y%m%d_%H%M%S')
datenow=datetime.datetime.now()
DATE= datenow.strftime("%Y%m%d_%H%M%S")
#DATE=str(datetime.datetime.now())
LOG_FILE="./logs/import_pim_"+DATE+".log"

#database
host='localhost'
database='stg_pim_gi'
dbuser='jmartins'
dbpass='ZAQ123wsx'


# server configuration
ftp_host = "168.63.37.239"
ft_user = "adminfolabix"
ft_pass = "Labix#2020#serial"


def execute():
    writeToFile(str(datetime.datetime.now())+' "processing data from stg to pim"')

    try:
        mydb = mysql.connector.connect(host=host,user=dbuser,password=dbpass,database=database)
        mycursor = mydb.cursor()
        
        mycursor.execute("call sp_UPSERT_Product_to_PIM");
        mydb.commit()
        writeToFile(str(datetime.datetime.now())+' "Finished processing data!"')
        
    except mysql.connector.Error as e:
        writeToFile(str(datetime.datetime.now())+" Error Processing stg data: "+ str(e))
        print(str(datetime.datetime.now())+" Error Processing stg data:", str(e))
    finally:
        if mydb.is_connected():
            mydb.close()
            mycursor.close()
            writeToFile(str(datetime.datetime.now())+' "closed connection for Processing stg data"')    

##
#       Write to file
##
def writeToFile(text):
	filename = LOG_FILE
	f = open( filename, "a", encoding='utf-8')
	f.write(text + "\n")
	f.close()
        

######
# MAIN
######
if __name__ == '__main__':
    #touch LOG_FILE
    writeToFile('Integration from stg to pim start '+str(datetime.datetime.now()))
    execute()
    writeToFile('Integration from stg to pim Done '+str(datetime.datetime.now()))
    
