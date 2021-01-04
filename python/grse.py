#Garden Reach Shipbuilders & Engineers
import page
import alphadate
from bs4.element import NavigableString
from bs4.element import Tag
import re

def get_urls():
    urls = []
    urls.append("http://grse.nic.in/tender.php?yr=&status=2")
    urls.append("http://grse.nic.in/tender.php?yr=&status=0")
    urls.append("http://grse.nic.in/tender.php?yr=&status=1")
    return urls

def clean_string(elem):
    myString = ''.join(elem.strip().split('\n'))
    myString = myString.split()
    myString = filter(None, myString)
    myString = ' '.join(myString)
    return myString

def get_element_text(element):
    for elem in element:
        if type(elem) is NavigableString or type(elem) is unicode:
            text = clean_string(elem)
            break
        elif type(elem) is Tag and elem.contents:
            element = elem.contents[0]
    return text

def extract_tenders(tenderURL):
    soup = page.get_page_soup(tenderURL)
    span = soup(text='Sl. No.')[0]

    table = span.find_parent("table")
    # print table
    rows = table.find_all("tr")
    rows = rows[1:]
    tenders = []
    l = {}

    for row in rows:
        td = row.find_all("td")

        text = clean_string(td[2].get_text().encode("ascii", "ignore"))
        # text = get_element_text(current)
        l["enquiry_number"] = text

        current = td[3].contents
        text = get_element_text(current)
        postedDate = alphadate.get_date(text)
        # print postedDate
        l["posted_on"] = postedDate

        current = td[4].contents
        text = get_element_text(current)
        l["description"] = text

        current = td[6].contents
        text = get_element_text(current)
        deadlineDate = alphadate.get_date(text)
        l["deadline"] = deadlineDate

        tenders.append(l.copy())
    return tenders

def get_tenders(tenders):
    urls = get_urls()
    tenders.append(get_tenders_single(urls[0]))
    tenders.append(get_tenders_open(urls[1]))
    tenders.append(get_tenders_limited(urls[2]))

def get_tenders_single(tenderURL):
    # tenderURL = "http://grse.nic.in/tender.php?yr=&status=2"
    # testURL = "http://bhi.local/tender_test/grse-single.html"
    return extract_tenders(tenderURL)

def get_tenders_open(tenderURL):
    # tenderURL = "http://grse.nic.in/tender.php?yr=&status=0"
    return extract_tenders(tenderURL)

def get_tenders_limited(tenderURL):
    # tenderURL = "http://grse.nic.in/tender.php?yr=&status=1"
    # testURL = "http://bhi.local/tender_test/grse-limited.html"
    return extract_tenders(tenderURL)
