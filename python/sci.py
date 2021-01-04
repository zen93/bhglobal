#Shipping Corporation of India
import page
import numericdate

def clean_string(elem):
    myString = ''.join(elem.strip().split('\n'))
    myString = myString.split()
    myString = filter(None, myString)
    myString = ' '.join(myString)
    return myString

def get_urls():
    urls = []
    urls.append("http://www.shipindia.com/procurement/tenders-floated.aspx")
    return urls

def get_tenders(tenders):
    tenderURL = get_urls()[0]
    # testURL = "http://bhi.local/tender_test/sci.html"
    soup = page.get_page_soup(tenderURL)
    table = soup.table

    #find all rows in parent
    rows = table.find_all("tr")
    rows = rows[1:]
    temp = []
    l = {}

    for row in rows:
        td = row.find_all("td")
        current = td[0]
        l["description"] = (str(current.contents[0]))
        current = td[1]
        l["enquiry_number"] = (str(current.contents[0]))
        current = td[2]
        l["posted_on"] = numericdate.get_date(str(current.contents[0]))
        # print l["posted_on"]
        current = td[3]
        l["deadline"] = numericdate.get_date(str(current.contents[0]))
        # print l["deadline"]

        temp.append(l.copy())
    tenders.append(temp)
