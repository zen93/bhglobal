import page
import alphadate
import re

def get_urls():
    urls = []
    urls.append("http://www.eprocuregsl.gov.in/nicgep/app")
    return urls

def get_tenders(tenders):
    tenderURL = get_urls()[0]
    # testURL = "http://bhi.local/tender_test/gsl.html"
    soup = page.get_page_soup(tenderURL)
    table = soup.table(id='activeTenders')[0]

    rows = table.find_all("tr")
    temp = []
    l = {}

    for row in rows:
        td = row.find_all("td")
        current = td[0]
        #regex to remove serial nums from tenders
        descWithSerial = str(current.a.contents[0])
        desc = re.sub(r'^[0-9]{1,2}\.[\s]', '', descWithSerial)
        l["description"] = desc
        current = td[1]
        l["enquiry_number"] = (str(current.contents[0]))
        current = td[2]
        l["deadline"] = alphadate.get_date(str(current.contents[0]))
        # print l["deadline"]
        current = td[3]
        l["posted_on"] = alphadate.get_date((str(current.contents[0])))
        # print l["posted_on"]

        temp.append(l.copy())
    tenders.append(temp)
