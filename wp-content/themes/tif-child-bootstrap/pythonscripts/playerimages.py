# playerimages.py

import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
import time
from collections import deque

class WebCrawler:
    def __init__(self, base_url, target_text):
        self.base_url = base_url
        self.target_text = target_text
        self.visited_urls = set()
        self.matching_urls = []
        self.session = requests.Session()
        
    def is_valid_url(self, url):
        """Check if URL is valid and belongs to the same domain and is a player URL"""
        parsed = urlparse(url)
        base_parsed = urlparse(self.base_url)
        # Only accept URLs from same domain that start with /player/
        return (parsed.netloc == base_parsed.netloc and 
                url.startswith('http://pfl-data.local/player/'))
    
    def get_all_links(self, url):
        """Extract all links from a webpage"""
        try:
            response = self.session.get(url, timeout=10)
            response.raise_for_status()
            soup = BeautifulSoup(response.content, 'html.parser')
            
            links = set()
            # Get all anchor tags with href attributes
            for link in soup.find_all('a', href=True):
                absolute_url = urljoin(url, link['href'])
                # Accept all URLs from the same domain to discover player URLs
                parsed = urlparse(absolute_url)
                base_parsed = urlparse(self.base_url)
                if parsed.netloc == base_parsed.netloc:
                    links.add(absolute_url)
            
            return links, soup
            
        except requests.RequestException as e:
            print(f"Error fetching {url}: {e}")
            return set(), None
    
    def check_for_target_text(self, soup, current_url):
        """Check if the target text exists in the current page (only for player pages)"""
        if not soup:
            return
            
        # Only check for target text on player pages
        if current_url.startswith('http://pfl-data.local/player/'):
            # Check if the target text appears anywhere in the page content
            page_text = soup.get_text()
            if self.target_text in page_text:
                self.matching_urls.append(current_url)
                print(f"Found '{self.target_text}' on: {current_url}")
                return
    
    def crawl(self, max_pages=100):
        """Crawl the website looking for the target text"""
        print(f"Starting crawl of {self.base_url} looking for '{self.target_text}'")
        
        urls_to_visit = deque([self.base_url])
        pages_crawled = 0
        
        while urls_to_visit and pages_crawled < max_pages:
            current_url = urls_to_visit.popleft()
            
            if current_url in self.visited_urls:
                continue
                
            print(f"Crawling: {current_url}")
            self.visited_urls.add(current_url)
            
            # Get links and page content
            links, soup = self.get_all_links(current_url)
            
            # Check if target text is found on this page
            self.check_for_target_text(soup, current_url)
            
            # Add new links to queue
            for link in links:
                if link not in self.visited_urls:
                    urls_to_visit.append(link)
            
            pages_crawled += 1
            
            # Be polite to the server
            time.sleep(0.5)
        
        print(f"\nCrawl completed. Visited {pages_crawled} pages.")
        return self.matching_urls

def parse_player_id(player_id):
    """
    Parse a player ID into year, name, and position components.
    
    Args:
        player_id (str): Player ID in format YYYYNAMEPOS
    
    Returns:
        dict: Dictionary with 'player_id', 'name', 'position', 'year'
    """
    import re
    
    # Pattern to extract year (4 digits), name (letters), and position (2-3 letters at end)
    pattern = r'^(\d{4})([A-Za-z]+?)([A-Z]{2,3})$'
    match = re.match(pattern, player_id)
    
    if match:
        year = match.group(1)
        name = match.group(2)
        position = match.group(3)
        
        # Capitalize the name properly
        formatted_name = name.capitalize()
        
        return {
            'player_id': player_id,
            'name': formatted_name,
            'position': position,
            'year': year
        }
    else:
        # If pattern doesn't match, return basic info
        return {
            'player_id': player_id,
            'name': 'Unknown',
            'position': 'Unknown',
            'year': 'Unknown'
        }

def extract_player_info_from_no_image(url="http://pfl-data.local/supercards/"):
    """
    Extract player information for IDs that appear directly after 'No Image -' text.
    
    Args:
        url (str): The URL to check
    
    Returns:
        list: List of dictionaries with player info (player_id, name, position, year)
    """
    import re
    
    try:
        session = requests.Session()
        print(f"Checking URL: {url}")
        print(f"Looking for player IDs after 'No Image -' text")
        
        response = session.get(url, timeout=30)
        response.raise_for_status()
        
        # Get the raw text content
        page_content = response.text
        
        # Use regex to find player IDs that come after 'No Image -'
        # Pattern looks for 'No Image -' followed by whitespace and then captures the player ID
        pattern = r'No Image\s*-\s*([^\s<]+)'
        
        matches = re.findall(pattern, page_content, re.IGNORECASE)
        
        print(f"\nFound {len(matches)} player IDs after 'No Image -':")
        
        # Parse each player ID and create structured data
        players_info = []
        for i, player_id in enumerate(matches, 1):
            # Remove any trailing punctuation or HTML artifacts
            clean_id = re.sub(r'[<>"\',;]+$', '', player_id)
            
            # Parse the player ID
            player_info = parse_player_id(clean_id)
            players_info.append(player_info)
            
            print(f"  {i}. {player_info['name']} ({player_info['position']}) - {player_info['player_id']}")
        
        return players_info
        
    except requests.RequestException as e:
        print(f"Error fetching {url}: {e}")
        return []
    except Exception as e:
        print(f"Error processing page: {e}")
        return []

def main():
    """
    Main function to extract structured player information from 'No Image -' text.
    """
    try:
        players_info = extract_player_info_from_no_image()
        
        print(f"\nSummary:")
        print(f"Total players with missing images: {len(players_info)}")
        
        if players_info:
            print(f"\nStructured player data:")
            import json
            print(json.dumps(players_info, indent=2))
        
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    main()
