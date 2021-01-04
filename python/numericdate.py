#This is for numeric addresses
# numeric: cochin, mdl, sci
# Alpha: grse, gsl, hsl, hsl
import re
from datetime import datetime
import calendar

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
    rSearch = re.search(r'(\d{1,2})([\W])+(\d{1,2})([\W])+(20\d\d)', rawDate)
    if(rSearch is None):
        add2000 = True
        rSearch = re.search(r'(\d{1,2})([\W])+(\d{1,2})([\W])+(\d\d)', rawDate)
    extractedDate = rSearch.group(0)
    rSearch = re.findall(r'(\d+)', extractedDate)
    day = add_zero_padding(rSearch[0])
    month = add_zero_padding(rSearch[1])
    if add2000:
        year = add_2000(rSearch[2])
    else:
        year = rSearch[2]

    return [day, month, year]

def get_date(rawDate):
    dateList = extract_date(rawDate)
    dateString = ' '.join(dateList)
    try:
        dateObj = datetime.strptime(dateString, "%d %m %Y")
    except ValueError:
        legaldays = calendar.monthrange(int(dateList[2]), int(dateList[1]))[0]
        if legaldays < dateList[0]:
            dateObj = datetime(int(dateList[2]), int(dateList[1]), legaldays)
    return dateObj


# print get_date("11:00 PM 06. 2/ 16. ")
