#This is for numeric addresses
# numeric: cochin, mdl, sci
# Alpha: grse, gsl, hsl, hsl
import re
from datetime import datetime

def add_zero_padding(date):
    if len(date) < 2:
        date = '0' + date
    return date

def add_2000(year):
    if len(year) == 2:
        year = '20' + year
    elif len(year) == 1:
        year = '200' + year
    return year

def extract_date(rawDate):
    add2000 = False
    # rawDate = "1 3 . 4 - 20 13.".replace(' ', '')
    rawDate = rawDate.replace(' ', '').replace('\n', ' ')
    rSearch = re.search(r'(\d{1,2})([\W])*([a-z]+)([\W])*(20\d\d)', rawDate, re.I)
    if(rSearch is None):
        add2000 = True
        rSearch = re.search(r'(\d{1,2})([\W])*([a-z]+)([\W])*(\d\d)', rawDate, re.I)
    extractedDate = rSearch.group(0)
    rSearch = re.findall(r'(\d+)', extractedDate)
    day = add_zero_padding(rSearch[0])
    if add2000:
        year = add_2000(rSearch[1])
    else:
        year = rSearch[1]

    rSearch = re.findall(r'[a-z]+', extractedDate, re.I)
    month = rSearch[0].lower()

    return [day, month, year]

def get_date(rawDate):
    dateList = extract_date(rawDate)
    dateString = ' '.join(dateList)
    try:
        dateObj = datetime.strptime(dateString, "%d %b %Y")
    except ValueError:
        legaldays = calendar.monthrange(int(dateList[2]), int(dateList[1]))[0]
        if legaldays < dateList[0]:
            dateObj = datetime(int(dateList[2]), int(dateList[1]), legaldays)
    return dateObj

# print get_date('''11 Apr 2016	''')
