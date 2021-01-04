import urllib2
from bs4 import BeautifulSoup

def get_page_soup(pageURL):
    response = urllib2.urlopen(pageURL)
    html = response.read()
    soup = BeautifulSoup(html)
    return soup
