import page
import alphadate
import re

def get_urls():
    urls = []
    urls.append("http://hsl.gov.in/currentTender.aspx")
    urls.append("http://www.eprocurehsl.gov.in/nicgep/app")
    return urls

def get_tenders(tenders):
    urls = get_urls()
    tenders.append(get_tenders_current(urls[0]))
    tenders.append(get_tenders_eprocure(urls[1]))

def get_tenders_current(tenderURL):
    # tenderURL = "http://hsl.gov.in/currentTender.aspx"
    # tenderURL = "http://bhi.local/tender_test/gsl.html"
    soup = page.get_page_soup(tenderURL)
    table = soup.table

    rows = table.find_all("tr")
    rows = rows[1:]
    tenders = []
    l = {}

    for row in rows:
        td = row.find_all("td")
        current = td[1]
        l["enquiry_number"] = str(current.get_text()).strip()
        current = td[2]
        l["description"] = str(current.get_text()).strip()
        current = td[3]
        l["posted_on"] = alphadate.get_date(str(current.get_text()).strip())
        current = td[4]
        l["deadline"] = alphadate.get_date(str(current.get_text()).strip())

        tenders.append(l.copy())
    return tenders

def get_tenders_eprocure(tenderURL):
    # tenderURL = "http://www.eprocurehsl.gov.in/nicgep/app"
    # tenderURL = "http://bhi.local/tender_test/gsl.html"
    soup = page.get_page_soup(tenderURL)
    table = soup.table(id='activeTenders')[0]

    rows = table.find_all("tr")
    tenders = []
    l = {}

    for row in rows:
        td = row.find_all("td")
        current = td[0]
        descWithSerial = str(current.a.contents[0])
        desc = re.sub(r'^[0-9]{1,2}\.[\s]', '', descWithSerial)
        l["description"] = desc
        current = td[1]
        l["enquiry_number"] = (str(current.contents[0]))
        current = td[2]
        l["deadline"] = alphadate.get_date(str(current.contents[0]))
        # print l["deadline"]
        current = td[3]
        l["posted_on"] = alphadate.get_date(str(current.contents[0]))
        # print l["posted_on"]

        tenders.append(l.copy())
    return tenders
