import page
import numericdate
from bs4.element import NavigableString
from bs4.element import Tag
import re

def clean_string(myString):
    myString = myString.replace(u'\xa0', u' ')
    myString = myString.replace('\n', ' ')
    myString = myString.split()
    myString = filter(None, myString)
    myString = ' '.join(myString)
    return myString

def extract_tenders(tenderURL):
    soup = page.get_page_soup(tenderURL)
    # [s.extract() for s in soup('a')]
    span = soup(text='Sr. No.')[0]

    table = span.find_parent("table")
    rows = table.find_all("tr")
    rows = rows[1:]
    tenders = []
    l = {}

    for row in rows:
        td = row.find_all("td")
        text = ""

        current = td[1]
        text = current.get_text()
        text = clean_string(text)
        l["enquiry_number"] = text

        current = td[2]
        [s.extract() for s in current('a')]
        text = current.get_text()
        text = clean_string(text)
        l["description"] = text

        current = td[3]
        text = current.get_text()
        text = clean_string(text)
        postedDate = numericdate.get_date(text)
        # print postedDate
        l["posted_on"] = postedDate

        current = td[4]
        text = current.get_text()
        text = clean_string(text)
        l["deadline"] = numericdate.get_date(text)
        # print l["deadline"]

        tenders.append(l.copy())

    return tenders

def get_urls():
    urls = []
    urls.append("http://www.mazagondock.gov.in/newsite2010/tend_sb_mp.htm")
    urls.append("http://www.mazagondock.gov.in/newsite2010/tenders_ey.htm")
    return urls

def get_tenders(tenders):
    urls = get_urls()
    tenders.append(get_tenders_shipbuilding(urls[0]))
    tenders.append(get_tenders_submarine(urls[1]))

def get_tenders_shipbuilding(tenderURL):
    # tenderURL = "http://www.mazagondock.gov.in/newsite2010/tend_sb_mp.htm"
    # tenderURL = "http://bhi.local/tender_test/mdl1.html"
    return extract_tenders(tenderURL)

def get_tenders_submarine(tenderURL):
    # tenderURL = "http://www.mazagondock.gov.in/newsite2010/tenders_ey.htm"
    # tenderURL = "http://bhi.local/tender_test/mdl2.html"
    return extract_tenders(tenderURL)
