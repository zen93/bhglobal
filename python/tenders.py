#!/usr/bin/env python
from itertools import izip
import sys
import mysql.connector
import pprint

import cochin, goa, grse, sci, hsl, mdl

def add_urls(urls, urllist):
    for url in urllist:
        urls.append(url)

def get_url_id(conn, url):
    urlID = None
    cursor = conn.cursor()
    url = (url,)
    query = ("SELECT id FROM `tender_url` WHERE url = %s")
    cursor.execute(query, url)
    for (id) in cursor:
        urlID = id[0]
    return urlID

def update_tenders(conn, tenders, urlIDs):
    cursor = conn.cursor()
    for tenderPage, urlID in izip(tenders, urlIDs):
        tenderPage.reverse()
        for tender in tenderPage:
            query = ("INSERT IGNORE INTO tenders(url_id, enquiry_number, description, posted_on, deadline) VALUES (%s, %s, %s, %s, %s)")
            # data = (urlID, tender["enquiry_number"], tender["description"], tender["posted_on"].strftime('%Y-%m-%d %H:%M:%S'), tender["deadline"])
            data = (urlID, tender["enquiry_number"], tender["description"], tender["posted_on"].strftime('%Y-%m-%d'), tender["deadline"])
            cursor.execute(query, data)
    conn.commit()
    cursor.close()
    print "Tenders updated successfully."

print "\n*** Running Tenders Update system ***\n"
print "This script will now check corresponding websites for tenders.\n"
tenders = []
urls = []
try:
    hsl.get_tenders(tenders)
    add_urls(urls, hsl.get_urls())
    print "HSL Done. (1/6)"
except:
    print "HSL module contains errors"

try:
    sci.get_tenders(tenders)
    add_urls(urls, sci.get_urls())
    print "SCI Done. (2/6)"
except:
    print "SCI module contains errors"

try:
    mdl.get_tenders(tenders)
    add_urls(urls, mdl.get_urls())
    print "MDL Done. (3/6)"
except:
    print "MDL module contains errors"

try:
    grse.get_tenders(tenders)
    add_urls(urls, grse.get_urls())
    print "GRSE Done. (4/6)"
except:
    print "GRSE module contains errors"

try:
    goa.get_tenders(tenders)
    add_urls(urls, goa.get_urls())
    print "Goa Done. (5/6)"
except:
    print "Goa module contains errors"

try:
    cochin.get_tenders(tenders)
    add_urls(urls, cochin.get_urls())
    print "Cochin Done. (6/6)"
except:
    print "Cochin module contains errors"

print "\nWebsites have been checked for updates."
print "\nUpdates will now be added to database.\n"

try:
    print "Connecting to Database..."
    conn = mysql.connector.connect(host="localhost",user= "bhgdb",password= "bhglobalindia@123" ,database= "bhglobal")
    urlIDs = []
    for url in urls:
        urlIDs.append(get_url_id(conn, url))
    print "Updating tenders..."

    # pprint.pprint(tenders)
    update_tenders(conn, tenders, urlIDs)
except:
    # pprint.pprint(tenders)
    print "\n\n*** ERROR ***: Something went wrong..."
    print sys.exc_info()[0]
    raise
finally:
    conn.close()
    print "Connection closed."
