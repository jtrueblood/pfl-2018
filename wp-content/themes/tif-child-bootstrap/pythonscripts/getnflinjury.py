#!/usr/bin/env python3
"""
NFL Injury Report Scraper
Scrapes injury reports from NFL.com for specified weeks and years.
Filters for QB, RB, WR, TE, and K positions only.
"""

import argparse
import json
import os
import sys
import time
from datetime import datetime
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from bs4 import BeautifulSoup
from webdriver_manager.chrome import ChromeDriverManager


def scrape_injury_report(driver, year, week):
    """
    Scrape injury report for a given year and week using Selenium.
    
    Args:
        driver: Selenium WebDriver instance
        year: Year (e.g., 2024)
        week: Week number (e.g., 8)
    
    Returns:
        List of injury records
    """
    url = f"https://www.nfl.com/injuries/league/{year}/reg{week}"
    
    try:
        print(f"  Loading {url}...")
        driver.get(url)
        
        # Wait for the injury data to load
        time.sleep(5)  # Give extra time for dynamic content
        
        # Get the page source after JavaScript has rendered
        html = driver.page_source
        soup = BeautifulSoup(html, 'html.parser')
        
    except Exception as e:
        print(f"Error fetching {url}: {e}", file=sys.stderr)
        return []
    
    injuries = []
    target_positions = {'QB', 'RB', 'WR', 'TE', 'K'}
    
    # Find all team sections
    team_sections = soup.find_all('div', class_='d3-o-section-sub-title')
    
    for team_section in team_sections:
        # Get team name
        team_name_elem = team_section.find('span')
        if not team_name_elem:
            continue
        team_name = team_name_elem.get_text(strip=True)
        
        # Find the table that follows this team section
        # The table is a sibling of the team section's parent
        parent = team_section.parent
        if not parent:
            continue
        
        table = parent.find_next_sibling('div', class_='d3-o-table--horizontal-scroll')
        if not table:
            continue
        
        table_elem = table.find('table')
        if not table_elem:
            continue
        
        # Parse table rows
        tbody = table_elem.find('tbody')
        if not tbody:
            continue
        
        rows = tbody.find_all('tr')
        for row in rows:
            cells = row.find_all('td')
            if len(cells) < 5:
                continue
            
            # Extract data from cells
            player_name = cells[0].get_text(strip=True)
            position = cells[1].get_text(strip=True)
            injury = cells[2].get_text(strip=True)
            practice_status = cells[3].get_text(strip=True)
            game_status = cells[4].get_text(strip=True)
            
            # Only include target positions
            if position not in target_positions:
                continue
            
            # Only include 'Out' or 'Doubtful' status
            if game_status not in ['Out', 'Doubtful']:
                continue
            
            injury_record = {
                "year": year,
                "week": week,
                "team": team_name,
                "player_name": player_name,
                "position": position,
                "injury": injury,
                "game_status": game_status
            }
            
            injuries.append(injury_record)
    
    return injuries


def save_to_json(injuries, output_dir, year, week):
    """
    Save injury data to JSON file.
    
    Args:
        injuries: List of injury records
        output_dir: Directory to save the JSON file
        year: Year for the filename
        week: Week for the filename
    """
    os.makedirs(output_dir, exist_ok=True)
    
    # Generate filename with year and week
    filename = f"nfl_injuries_{year}_{week}.json"
    filepath = os.path.join(output_dir, filename)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        json.dump(injuries, f, indent=2, ensure_ascii=False)
    
    print(f"Saved {len(injuries)} injury records to {filepath}")
    return filepath


def main():
    parser = argparse.ArgumentParser(
        description='Scrape NFL injury reports from NFL.com'
    )
    parser.add_argument(
        '--weeks',
        type=str,
        required=True,
        help='Comma-separated list of weeks (e.g., "1,2,3" or "1-17" for range)'
    )
    parser.add_argument(
        '--years',
        type=str,
        required=True,
        help='Comma-separated list of years (e.g., "2011,2012" or "2011-2015" for range)'
    )
    parser.add_argument(
        '--output-dir',
        type=str,
        default='/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-content/themes/tif-child-bootstrap/nfl-injuries',
        help='Output directory for JSON files'
    )
    parser.add_argument(
        '--headless',
        action='store_true',
        help='Run Chrome in headless mode'
    )
    parser.add_argument(
        '--chromedriver-path',
        type=str,
        default=None,
        help='Path to chromedriver if not on PATH'
    )
    
    args = parser.parse_args()
    
    # Parse weeks
    weeks = []
    for part in args.weeks.split(','):
        if '-' in part:
            start, end = map(int, part.split('-'))
            weeks.extend(range(start, end + 1))
        else:
            weeks.append(int(part))
    
    # Parse years
    years = []
    for part in args.years.split(','):
        if '-' in part:
            start, end = map(int, part.split('-'))
            years.extend(range(start, end + 1))
        else:
            years.append(int(part))
    
    print(f"Scraping injury reports for years {years} and weeks {weeks}")

    # Set up Selenium Chrome options
    chrome_options = Options()
    chrome_options.add_argument("--disable-gpu")
    chrome_options.add_argument("--no-sandbox")
    chrome_options.add_argument("--disable-dev-shm-usage")
    chrome_options.add_argument("--window-size=1400,1000")
    chrome_options.add_argument("--user-agent=Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36")
    if args.headless:
        chrome_options.add_argument("--headless=new")

    # Use ChromeDriverManager to automatically handle ChromeDriver version
    if args.chromedriver_path:
        service = Service(args.chromedriver_path)
    else:
        service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=chrome_options)

    try:
        for year in years:
            for week in weeks:
                print(f"Fetching {year} Week {week}...")
                injuries = scrape_injury_report(driver, year, week)
                print(f"  Found {len(injuries)} relevant injuries")
                
                if injuries:
                    save_to_json(injuries, args.output_dir, year, week)
                else:
                    print(f"  No injury data found for {year} Week {week}")
    finally:
        driver.quit()


if __name__ == '__main__':
    main()
