#!/usr/bin/env python
import urllib2
from bs4 import BeautifulSoup
import json
import mysql.connector
from datetime import datetime, date

def get_copper_price(conn):
    userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.7) Gecko/2009021910 Firefox/3.0.7'
    mheaders={'User-Agent':userAgent,}

    copperUrl = "https://www.lme.com/metals/non-ferrous/copper/"
    copperDivID = "ctl01_maincontent_0_ctl00_pnlPrices"

    req = urllib2.Request(copperUrl, headers=mheaders)
    response = urllib2.urlopen(req)
    html = response.read()

    soup = BeautifulSoup(html)
    priceDiv = soup.find(id=copperDivID)
    priceElem = priceDiv.find_all('td')[1]
    copperPrice = str(priceElem.contents[0])

    now = datetime.now()
    data = (copperPrice, now)

    cursor = conn.cursor()
    updateCopperPrice = ("UPDATE dashboard SET value = %s, last_modified = %s WHERE did = 0")
    cursor.execute(updateCopperPrice, data)
    conn.commit()
    cursor.close()

def get_currency_rates(conn):
    baseURL = "https://api.fixer.io/latest?base=INR&symbols="
    sgdURL = baseURL + "SGD"
    usdURL = baseURL + "USD"

    print "Fetching SGD Rate..."
    response = urllib2.urlopen(sgdURL)
    jsonData = response.read()
    currData = json.loads(jsonData)
    sgdRate = currData["rates"]["SGD"]
    print "Done."

    print "Fetching USD Rate..."
    response = urllib2.urlopen(usdURL)
    jsonData = response.read()
    currData = json.loads(jsonData)
    usdRate = currData["rates"]["USD"]
    print "Done."

    now = datetime.now()
    sgdData = (sgdRate, now)
    usdData = (usdRate, now)

    updateSGD = ("UPDATE dashboard SET value = %s, last_modified = %s WHERE name = 'SGD'")
    updateUSD = ("UPDATE dashboard SET value = %s, last_modified = %s WHERE name = 'USD'")
    updateTime = ("UPDATE dashboard SET last_modified = %s WHERE name = 'INR'")

    cursor = conn.cursor()
    cursor.execute(updateSGD, sgdData)
    cursor.execute(updateUSD, usdData)
    cursor.execute(updateTime, (now,))
    conn.commit()
    cursor.close()

print "\n*** Running Dashboard Update Script***"
print "This script will update the LME copper rate and currency rates."
conn = mysql.connector.connect(host="localhost",user= "bhgdb",password= "bhglobalindia@123" ,database= "bhglobal")
print "\nFetching copper rate..."
get_copper_price(conn)
print "Done."
print "\nFetching currency rates...\n"
get_currency_rates(conn)
conn.close()
print "Updated successfully!"
