#!/usr/bin/env python3
"""
Script to fetch MFL draft results from the MFL API and save them as JSON files.

This script downloads draft results for specified years (2011-present) from the MFL API
and saves them to the mfl-drafts directory. API keys are stored in the database for reuse.

Authentication:
    - API Key (recommended): Stored in database for reuse
    - Username/Password: Can be used if API key not available (not stored)

Usage:
    python3 fetch_mfl_drafts.py [year]           # Fetch specific year
    python3 fetch_mfl_drafts.py --all            # Fetch all years (2011-2025)
    python3 fetch_mfl_drafts.py --range 2020 2025  # Fetch range of years
    
Examples:
    python3 fetch_mfl_drafts.py 2025
    python3 fetch_mfl_drafts.py --all
    python3 fetch_mfl_drafts.py --range 2020 2025
"""

import requests
import json
import sys
import os
from datetime import datetime
from pathlib import Path

try:
    import mysql.connector
    from mysql.connector import Error
    HAS_MYSQL = True
except ImportError:
    HAS_MYSQL = False
    print("Warning: MySQL connector not available. API keys will not be stored.")

# MFL API Configuration
MFL_LEAGUE_ID = 38954
MFL_BASE_URL = "https://www48.myfantasyleague.com"

# Database Configuration
DB_CONFIG = {
    'host': 'localhost',
    'database': 'local',
    'user': 'root',
    'password': 'root'
}
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

# Output directory
OUTPUT_DIR = "/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-content/themes/tif-child-bootstrap/mfl-drafts"

# Valid year range (MFL API only has data from 2011 onwards)
MIN_YEAR = 2011
MAX_YEAR = datetime.now().year


def get_db_connection():
    """Establish database connection."""
    if not HAS_MYSQL:
        return None
    
    try:
        connection = mysql.connector.connect(
            host=DB_CONFIG['host'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password'],
            database=DB_CONFIG['database'],
            unix_socket=MYSQL_SOCKET
        )
        return connection
    except Error as e:
        print(f"Warning: Could not connect to database: {e}")
        return None


def ensure_api_keys_table():
    """Create the wp_mfl_api_keys table if it doesn't exist."""
    if not HAS_MYSQL:
        return False
    
    connection = get_db_connection()
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        create_table_query = """
            CREATE TABLE IF NOT EXISTS wp_mfl_api_keys (
                year INT PRIMARY KEY,
                api_key VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        """
        cursor.execute(create_table_query)
        connection.commit()
        cursor.close()
        connection.close()
        return True
    except Error as e:
        print(f"Warning: Could not create API keys table: {e}")
        if connection:
            connection.close()
        return False


def get_api_key_for_year(year):
    """Get stored API key for a specific year from database."""
    if not HAS_MYSQL:
        return None
    
    connection = get_db_connection()
    if not connection:
        return None
    
    try:
        cursor = connection.cursor()
        query = "SELECT api_key FROM wp_mfl_api_keys WHERE year = %s"
        cursor.execute(query, (year,))
        result = cursor.fetchone()
        cursor.close()
        connection.close()
        
        if result:
            return result[0]
        return None
    except Error as e:
        if connection:
            connection.close()
        return None


def save_api_key_for_year(year, api_key):
    """Save API key for a specific year to database."""
    if not HAS_MYSQL:
        return False
    
    connection = get_db_connection()
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        query = """
            INSERT INTO wp_mfl_api_keys (year, api_key) 
            VALUES (%s, %s)
            ON DUPLICATE KEY UPDATE api_key = %s, updated_at = CURRENT_TIMESTAMP
        """
        cursor.execute(query, (year, api_key, api_key))
        connection.commit()
        cursor.close()
        connection.close()
        return True
    except Error as e:
        print(f"Warning: Could not save API key: {e}")
        if connection:
            connection.close()
        return False


def prompt_for_credentials(year, has_stored_key=False):
    """Prompt user to enter authentication credentials for a specific year."""
    print(f"\n{'='*60}")
    print(f"AUTHENTICATION FOR YEAR {year}")
    print(f"{'='*60}")
    print("Choose authentication method:")
    if has_stored_key:
        print("  1. Use stored API Key")
        print("  2. Enter new API Key")
        print("  3. Username/Password")
        print("  4. Skip this year")
    else:
        print("  1. API Key (recommended)")
        print("  2. Username/Password")
        print("  3. Skip this year")
    print(f"{'='*60}")
    
    if has_stored_key:
        choice = input("Enter choice (1, 2, 3, or 4): ").strip()
    else:
        choice = input("Enter choice (1, 2, or 3): ").strip()
    
    # Handle skip option (adjusted based on whether stored key exists)
    if (has_stored_key and choice == '4') or (not has_stored_key and choice == '3') or choice.lower() == 'skip':
        return None, None
    
    # Option 1: Use stored key (if available) or enter new key
    if choice == '1':
        if has_stored_key:
            # Return special marker to indicate using stored key
            return 'USE_STORED', None
        else:
            # Prompt for new API key
            print(f"\nTo find the API key:")
            print(f"Visit: https://www48.myfantasyleague.com/{year}/options?L=38954&O=26")
            print("Look for the 'Export/API' section and copy the key")
            api_key = input(f"\nEnter API key for {year}: ").strip()
            
            if not api_key:
                print("Error: API key cannot be empty")
                return None, None
            
            return api_key, None
    
    # Option 2: Enter new API key (if stored key exists) or username/password
    elif choice == '2':
        if has_stored_key:
            # Prompt for new API key to replace stored one
            print(f"\nTo find the API key:")
            print(f"Visit: https://www48.myfantasyleague.com/{year}/options?L=38954&O=26")
            print("Look for the 'Export/API' section and copy the key")
            api_key = input(f"\nEnter API key for {year}: ").strip()
            
            if not api_key:
                print("Error: API key cannot be empty")
                return None, None
            
            return api_key, None
        else:
            # Username/password
            print(f"\nEnter your MFL credentials:")
            username = input("Username: ").strip()
            
            if not username:
                print("Error: Username cannot be empty")
                return None, None
            
            # Use getpass for secure password input
            import getpass
            password = getpass.getpass("Password: ")
            
            if not password:
                print("Error: Password cannot be empty")
                return None, None
            
            return None, {'username': username, 'password': password}
    
    # Option 3: Username/password (when stored key exists)
    elif choice == '3' and has_stored_key:
        print(f"\nEnter your MFL credentials:")
        username = input("Username: ").strip()
        
        if not username:
            print("Error: Username cannot be empty")
            return None, None
        
        # Use getpass for secure password input
        import getpass
        password = getpass.getpass("Password: ")
        
        if not password:
            print("Error: Password cannot be empty")
            return None, None
        
        return None, {'username': username, 'password': password}
    
    else:
        print("Invalid choice")
        return None, None


def prompt_for_api_key(year):
    """Prompt user to enter API key for a specific year (legacy function)."""
    api_key, _ = prompt_for_credentials(year)
    return api_key


def ensure_output_directory():
    """Create the output directory if it doesn't exist."""
    Path(OUTPUT_DIR).mkdir(parents=True, exist_ok=True)
    print(f"Output directory: {OUTPUT_DIR}")


def fetch_draft_results(year, api_key=None, credentials=None):
    """
    Fetch draft results from MFL API for a specific year.
    
    Parameters:
        year (int): The year to fetch draft results for
        api_key (str, optional): API key for MFL API
        credentials (dict, optional): Dict with 'username' and 'password' keys
        
    Returns:
        dict: Draft results JSON data, or None if failed
    """
    try:
        url = f"{MFL_BASE_URL}/{year}/export"
        params = {
            'TYPE': 'draftResults',
            'L': MFL_LEAGUE_ID,
            'JSON': 1
        }
        
        # Add authentication - either API key or username/password
        if api_key:
            params['APIKEY'] = api_key
        elif credentials:
            params['USERNAME'] = credentials['username']
            params['PASSWORD'] = credentials['password']
        else:
            print(f"✗ No authentication provided")
            return None
        
        auth_method = "API key" if api_key else "username/password"
        print(f"Fetching draft results for {year} (using {auth_method})...", end=' ')
        
        response = requests.get(url, params=params, timeout=30)
        response.raise_for_status()
        
        data = response.json()
        
        # Check if we got valid draft results
        if 'draftResults' in data and 'draftUnit' in data['draftResults']:
            draft_picks = data['draftResults']['draftUnit'].get('draftPick', [])
            
            # Handle case where draftPick might be a single dict instead of list
            if isinstance(draft_picks, dict):
                draft_picks = [draft_picks]
            
            num_picks = len(draft_picks) if draft_picks else 0
            print(f"✓ Found {num_picks} picks")
            return data
        else:
            print(f"⚠ No draft data found (check authentication)")
            return None
            
    except requests.exceptions.RequestException as e:
        print(f"✗ Error fetching data: {e}")
        return None
    except json.JSONDecodeError as e:
        print(f"✗ Error parsing JSON: {e}")
        return None
    except Exception as e:
        print(f"✗ Unexpected error: {e}")
        return None


def save_draft_results(year, data):
    """
    Save draft results to a JSON file.
    
    Parameters:
        year (int): The year of the draft
        data (dict): Draft results data
        
    Returns:
        bool: True if successful, False otherwise
    """
    try:
        output_file = os.path.join(OUTPUT_DIR, f"{year}_draft_results.json")
        
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=2, ensure_ascii=False)
        
        file_size = os.path.getsize(output_file)
        print(f"  Saved to: {output_file} ({file_size:,} bytes)")
        return True
        
    except Exception as e:
        print(f"  ✗ Error saving file: {e}")
        return False


def process_year(year, prompt_for_key=True):
    """
    Fetch and save draft results for a specific year.
    
    Parameters:
        year (int): The year to process
        prompt_for_key (bool): Whether to prompt for authentication (always True now)
        
    Returns:
        bool: True if successful, False otherwise
    """
    if year < MIN_YEAR or year > MAX_YEAR:
        print(f"⚠ Year {year} is outside valid range ({MIN_YEAR}-{MAX_YEAR})")
        return False
    
    # Check if stored API key exists
    stored_api_key = get_api_key_for_year(year)
    has_stored_key = stored_api_key is not None
    
    # Always prompt user for authentication method
    api_key, credentials = prompt_for_credentials(year, has_stored_key=has_stored_key)
    
    # Handle special 'USE_STORED' marker
    if api_key == 'USE_STORED':
        api_key = stored_api_key
        print(f"  Using stored API key for {year}")
    
    if not api_key and not credentials:
        print(f"  Skipping year {year} - no authentication provided")
        return False
    
    # Save the new API key if provided (don't save passwords)
    if api_key and api_key != stored_api_key:
        if save_api_key_for_year(year, api_key):
            print(f"  ✓ API key saved for {year}")
    elif credentials:
        print(f"  Note: Username/password not saved (only API keys are stored)")
    
    data = fetch_draft_results(year, api_key=api_key, credentials=credentials)
    
    if data:
        return save_draft_results(year, data)
    
    return False


def process_all_years():
    """
    Fetch and save draft results for all years from 2011 to present.
    
    Returns:
        tuple: (successful_count, failed_count)
    """
    successful = 0
    failed = 0
    
    print(f"\nFetching draft results for years {MIN_YEAR}-{MAX_YEAR}")
    print("=" * 80)
    
    for year in range(MIN_YEAR, MAX_YEAR + 1):
        if process_year(year):
            successful += 1
        else:
            failed += 1
        print()  # Empty line between years
    
    return successful, failed


def process_year_range(start_year, end_year):
    """
    Fetch and save draft results for a range of years.
    
    Parameters:
        start_year (int): Starting year (inclusive)
        end_year (int): Ending year (inclusive)
        
    Returns:
        tuple: (successful_count, failed_count)
    """
    if start_year > end_year:
        start_year, end_year = end_year, start_year
    
    start_year = max(start_year, MIN_YEAR)
    end_year = min(end_year, MAX_YEAR)
    
    successful = 0
    failed = 0
    
    print(f"\nFetching draft results for years {start_year}-{end_year}")
    print("=" * 80)
    
    for year in range(start_year, end_year + 1):
        if process_year(year):
            successful += 1
        else:
            failed += 1
        print()  # Empty line between years
    
    return successful, failed


def list_existing_files():
    """List all existing draft result files in the output directory."""
    try:
        files = sorted([f for f in os.listdir(OUTPUT_DIR) if f.endswith('_draft_results.json')])
        
        if files:
            print("\nExisting draft result files:")
            print("-" * 80)
            for filename in files:
                filepath = os.path.join(OUTPUT_DIR, filename)
                file_size = os.path.getsize(filepath)
                file_time = datetime.fromtimestamp(os.path.getmtime(filepath))
                print(f"  {filename:<30} {file_size:>10,} bytes   {file_time.strftime('%Y-%m-%d %H:%M:%S')}")
        else:
            print("\nNo existing draft result files found.")
            
    except FileNotFoundError:
        print("\nOutput directory does not exist yet.")
    except Exception as e:
        print(f"\nError listing files: {e}")


def main():
    """Main execution function."""
    print("\n" + "=" * 80)
    print("MFL DRAFT RESULTS FETCHER")
    print("=" * 80)
    
    # Ensure output directory exists
    ensure_output_directory()
    
    # Ensure API keys table exists
    if HAS_MYSQL:
        ensure_api_keys_table()
    
    # Parse command line arguments
    if len(sys.argv) == 1:
        # No arguments - show usage and list existing files
        print(__doc__)
        list_existing_files()
        sys.exit(0)
    
    if sys.argv[1] == '--list':
        list_existing_files()
        sys.exit(0)
    
    if sys.argv[1] == '--all':
        # Fetch all years
        successful, failed = process_all_years()
        
        print("=" * 80)
        print(f"SUMMARY: {successful} successful, {failed} failed")
        print("=" * 80)
        
        if failed > 0:
            sys.exit(1)
        
    elif sys.argv[1] == '--range':
        # Fetch range of years
        if len(sys.argv) != 4:
            print("Error: --range requires two year arguments")
            print("Usage: python3 fetch_mfl_drafts.py --range START_YEAR END_YEAR")
            sys.exit(1)
        
        try:
            start_year = int(sys.argv[2])
            end_year = int(sys.argv[3])
        except ValueError:
            print("Error: Year arguments must be integers")
            sys.exit(1)
        
        successful, failed = process_year_range(start_year, end_year)
        
        print("=" * 80)
        print(f"SUMMARY: {successful} successful, {failed} failed")
        print("=" * 80)
        
        if failed > 0:
            sys.exit(1)
    
    else:
        # Fetch specific year
        try:
            year = int(sys.argv[1])
        except ValueError:
            print(f"Error: Invalid year '{sys.argv[1]}'")
            sys.exit(1)
        
        print()
        if process_year(year):
            print("\n✓ Draft results fetched successfully!")
        else:
            print("\n✗ Failed to fetch draft results")
            sys.exit(1)


if __name__ == "__main__":
    main()
