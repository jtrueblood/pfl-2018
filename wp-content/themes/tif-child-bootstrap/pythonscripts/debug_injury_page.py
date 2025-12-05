#!/usr/bin/env python3
"""
Debug script to examine the HTML structure of NFL injury page
"""
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from bs4 import BeautifulSoup

# Set up Selenium
chrome_options = Options()
chrome_options.add_argument("--disable-gpu")
chrome_options.add_argument("--no-sandbox")
chrome_options.add_argument("--disable-dev-shm-usage")
chrome_options.add_argument("--window-size=1400,1000")
chrome_options.add_argument("--user-agent=Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36")

service = Service(ChromeDriverManager().install())
driver = webdriver.Chrome(service=service, options=chrome_options)

url = "https://www.nfl.com/injuries/league/2024/reg8"
print(f"Loading {url}...")
driver.get(url)
time.sleep(5)

html = driver.page_source
soup = BeautifulSoup(html, 'html.parser')

# Save HTML to file for inspection
with open('injury_page.html', 'w', encoding='utf-8') as f:
    f.write(soup.prettify())
print("Saved HTML to injury_page.html")

# Search for Nico Collins in the page
if "Nico Collins" in html:
    print("\n✓ Found 'Nico Collins' in page HTML")
    # Find context around the name
    idx = html.find("Nico Collins")
    context = html[max(0, idx-500):idx+500]
    print(f"\nContext around 'Nico Collins':\n{context}\n")
else:
    print("\n✗ 'Nico Collins' NOT found in page HTML")

# Look for common HTML structures
print("\n=== Looking for table structures ===")
tables = soup.find_all('table')
print(f"Found {len(tables)} tables")

print("\n=== Looking for div structures with class containing 'injury' ===")
injury_divs = soup.find_all('div', class_=lambda x: x and 'injury' in x.lower() if x else False)
print(f"Found {len(injury_divs)} divs with 'injury' in class name")

print("\n=== Looking for any elements with 'nfl-o-roster' class ===")
roster_elements = soup.find_all(class_=lambda x: x and 'roster' in x.lower() if x else False)
print(f"Found {len(roster_elements)} elements with 'roster' in class name")

driver.quit()
