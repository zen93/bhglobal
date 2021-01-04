#Cochin Shipyard
import page
import numericdate

def get_urls():
    urls = []
    urls.append("http://cochinshipyard.com/tenders.html")
    return urls

def get_tenders(tenders):
    tenderURL = get_urls()[0]
    soup = page.get_page_soup(tenderURL)

    #find b tag with purchase text and its parent
    for tag in soup.find_all("b"):
        if (str(tag.contents[0])).strip().lower() == "purchase":
            ptag = tag.find_parent("table")

    #find all rows in parent
    rows = ptag.find_all("tr")
    rows = rows[2:] #discard first 2 rows
    temp = []
    l = {}

    for row in rows:
        td = row.find_all("td")
        current = td[0].find("span")
        l["enquiry_number"] = (str(current.contents[0]))
        current = td[1].find("span").a
        # l["link"] = (str(current['href']))
        l["description"] = (str(current.contents[0]))
        current = td[2].find("span")
        l["deadline"] = numericdate.get_date(str(current.contents[0]))
        # print l["deadline"]
        current = td[3].find("span")
        l["posted_on"] = numericdate.get_date(str(current.contents[0]))
        # print l["posted_on"]
        temp.append(l.copy())
    tenders.append(temp)
