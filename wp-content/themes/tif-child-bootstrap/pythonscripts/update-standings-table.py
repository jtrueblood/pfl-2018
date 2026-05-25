#!/usr/bin/env python3
"""
Script to update end-of-season standings table

This script:
1. Copies the current year's standings table (e.g., stand2025) to next year (e.g., stand2026)
2. Fetches standings data from the Week 14 results page
3. Updates the current year's standings table with the latest data

Usage:
    python3 update-standings-table.py <year>
    
Parameters:
    year - Season year (e.g., 2025)
    
Examples:
    python3 update-standings-table.py 2025
"""

import requests
import sys
import os
import re
from bs4 import BeautifulSoup

# Optional MySQL support
try:
    import mysql.connector
    from mysql.connector import Error
    HAS_MYSQL = True
except Exception:
    HAS_MYSQL = False
    print("Warning: MySQL connector not available. Database operations will be disabled.")

# Database Configuration (will be overridden by wp-config.php if found)
DB_CONFIG = {
    'host': 'localhost',
    'database': 'local',
    'user': 'root',
    'password': 'root',
    'port': 3306,
}
DB_TABLE_PREFIX = 'wp_'

# Specific socket for Local by Flywheel site
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

# Team abbreviation to full name mapping
TEAM_NAMES = {
    'Peppers': 'PEP',
    'Euro-Trashers': 'ETS',
    'Warriorz': 'WRZ',
    'Space Warriorz': 'WRZ',
    'Jimmys Hats': 'HAT',
    'Booty Bustas': 'BST',
    'Sixty Niners': 'SNR',
    'Destruction': 'DST',
    'C-Men': 'CMN',
    'Raging Bulls': 'BUL',
    'Tsongas': 'TSG'
}

# Fallback division mapping if the page doesn't separate by division
DIVISION_BY_TEAM = {
    'EGAD': {'PEP', 'HAT', 'ETS', 'WRZ', 'BST'},
    'DGAS': {'SNR', 'DST', 'CMN', 'BUL', 'TSG'},
}


def _load_wp_db_config():
    """Attempt to load DB settings and table prefix from wp-config.php."""
    global DB_CONFIG, DB_TABLE_PREFIX
    try:
        # Walk up from this script to find app/public/wp-config.php
        here = os.path.abspath(os.path.dirname(__file__))
        parts = here.split(os.sep)
        if 'wp-content' in parts:
            wp_index = parts.index('wp-content')
            wp_root = os.sep.join(parts[:wp_index])
        else:
            # fallback: ascend to app/public
            wp_root = os.path.abspath(os.path.join(here, '..', '..', '..', '..'))
        
        wp_config_path = os.path.join(wp_root, 'wp-config.php')
        if not os.path.isfile(wp_config_path):
            return
        
        with open(wp_config_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        def _const(name):
            m = re.search(r"define\(\s*'" + re.escape(name) + r"'\s*,\s*'([^']*)'\s*\)", content)
            return m.group(1) if m else None
        
        db_name = _const('DB_NAME')
        db_user = _const('DB_USER')
        db_password = _const('DB_PASSWORD')
        db_host = _const('DB_HOST')
        
        # table prefix
        m = re.search(r"\$table_prefix\s*=\s*'([^']+)'\s*;", content)
        if m:
            DB_TABLE_PREFIX = m.group(1)
        
        if db_name:
            DB_CONFIG['database'] = db_name
        if db_user:
            DB_CONFIG['user'] = db_user
        if db_password:
            DB_CONFIG['password'] = db_password
        if db_host:
            # DB_HOST may include host:port
            if ':' in db_host and not db_host.startswith('/'):
                host, port = db_host.split(':', 1)
                DB_CONFIG['host'] = host
                try:
                    DB_CONFIG['port'] = int(port)
                except ValueError:
                    pass
            else:
                DB_CONFIG['host'] = db_host
    except Exception as e:
        # best-effort only
        pass


# Load config from wp-config.php when module is imported
_load_wp_db_config()


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
        print(f"Error connecting to database: {e}")
        return None


def _get_division_map(year):
    """
    Get the current division assignments from the database.
    
    Parameters:
        year (int): Season year
        
    Returns:
        dict: Mapping of team abbreviation to division
    """
    if not HAS_MYSQL:
        return {}
    
    connection = get_db_connection()
    if not connection:
        return {}
    
    try:
        cursor = connection.cursor()
        table_name = f"stand{year}"
        
        # Check if table exists
        cursor.execute(f"SHOW TABLES LIKE '{table_name}'")
        if not cursor.fetchone():
            cursor.close()
            connection.close()
            return {}
        
        cursor.execute(f"SELECT teamID, division FROM {table_name} WHERE year = %s", (year,))
        results = cursor.fetchall()
        
        division_map = {row[0]: row[1] for row in results}
        
        cursor.close()
        connection.close()
        
        return division_map
        
    except Error as e:
        print(f"Warning: Could not fetch division info from database: {e}")
        if connection:
            connection.close()
        return {}


def copy_standings_table(source_year):
    """
    Copy standings table from source_year to source_year+1.
    
    Parameters:
        source_year (int): Source year (e.g., 2025)
        
    Returns:
        bool: True if successful, False otherwise
    """
    if not HAS_MYSQL:
        print("MySQL not available - cannot copy table")
        return False
    
    connection = get_db_connection()
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        
        source_table = f"stand{source_year}"
        dest_table = f"stand{source_year + 1}"
        
        # Check if source table exists
        cursor.execute(f"SHOW TABLES LIKE '{source_table}'")
        if not cursor.fetchone():
            print(f"Error: Source table {source_table} does not exist")
            cursor.close()
            connection.close()
            return False
        
        # Check if destination table already exists
        cursor.execute(f"SHOW TABLES LIKE '{dest_table}'")
        if cursor.fetchone():
            print(f"Warning: Destination table {dest_table} already exists")
            response = input(f"Do you want to drop and recreate {dest_table}? (yes/no): ").strip().lower()
            if response not in ['yes', 'y']:
                print("Operation cancelled")
                cursor.close()
                connection.close()
                return False
            cursor.execute(f"DROP TABLE {dest_table}")
            print(f"Dropped existing table {dest_table}")
        
        # Copy table structure
        cursor.execute(f"CREATE TABLE {dest_table} LIKE {source_table}")
        print(f"Created table {dest_table} with same structure as {source_table}")
        
        # Copy data
        cursor.execute(f"INSERT INTO {dest_table} SELECT * FROM {source_table}")
        rows_copied = cursor.rowcount
        print(f"Copied {rows_copied} rows from {source_table} to {dest_table}")
        
        connection.commit()
        cursor.close()
        connection.close()
        return True
        
    except Error as e:
        print(f"Error copying table: {e}")
        if connection:
            connection.close()
        return False


def fetch_standings_from_results_page(year, week=14):
    """
    Fetch standings data from the results page.
    
    Parameters:
        year (int): Season year
        week (int): Week number (default: 14)
        
    Returns:
        dict: Standings data by team abbreviation, or None if failed
    """
    try:
        url = f"http://pfl-data.local/results/?Y={year}&W={week}"
        response = requests.get(url, timeout=30)
        response.raise_for_status()
        
        soup = BeautifulSoup(response.content, 'html.parser')
        
        standings_data = {}
        
        # Try to find all division headings first
        headings = soup.find_all('h4', string=lambda t: t and 'Standings' in t)
        
        if headings:
            # Check if this is a combined table (only one heading) or separate tables
            is_combined_table = len(headings) == 1
            
            for heading in headings:
                text = heading.get_text(strip=True)
                m = re.search(r'^(EGAD|DGAS)\s+Standings', text)
                division = m.group(1) if m else None
                
                # Prefer the table that contains this heading (thead->table)
                table = heading.find_parent('table')
                if table is None:
                    # Fallback to next table in DOM
                    table = heading.find_next('table')
                if table is None:
                    print(f"Warning: Could not find table associated with heading '{text}'")
                    continue
                
                tbody = table.find('tbody')
                if not tbody:
                    print(f"Warning: No tbody under table for '{text}'")
                    continue
                
                for row in tbody.find_all('tr'):
                    cols = row.find_all('td')
                    if len(cols) < 11:
                        continue
                    team_full_name = cols[0].get_text(strip=True)
                    team_abbr = TEAM_NAMES.get(team_full_name)
                    if not team_abbr:
                        print(f"Warning: Unknown team name '{team_full_name}'")
                        continue
                    
                    # If this is a combined table, always use fallback mapping
                    # Otherwise use the division from the heading
                    if is_combined_table:
                        use_division = 'EGAD' if team_abbr in DIVISION_BY_TEAM['EGAD'] else 'DGAS'
                    else:
                        # If division heading wasn't parsed, infer from fallback mapping
                        inferred_div = None
                        if not division:
                            for div, teams in DIVISION_BY_TEAM.items():
                                if team_abbr in teams:
                                    inferred_div = div
                                    break
                        use_division = division or inferred_div or 'EGAD'
                    
                    wins = int(cols[1].get_text(strip=True))
                    losses = int(cols[2].get_text(strip=True))
                    win_pct = float(cols[3].get_text(strip=True))
                    pts = int(cols[4].get_text(strip=True))
                    ppg = float(cols[5].get_text(strip=True))
                    pts_vs = int(cols[6].get_text(strip=True))
                    plus_minus = int(cols[7].get_text(strip=True))
                    div_w = int(cols[8].get_text(strip=True))
                    div_l = int(cols[9].get_text(strip=True))
                    gb_text = cols[10].get_text(strip=True)
                    gb = 0 if gb_text == '-' else float(gb_text)
                    
                    standings_data[team_abbr] = {
                        'division': use_division,
                        'win': wins,
                        'loss': losses,
                        'winper': win_pct,
                        'pts': pts,
                        'ppg': ppg,
                        'pts_agst': pts_vs,
                        'plus_min': plus_minus,
                        'div_win': div_w,
                        'div_loss': div_l,
                        'gb': gb
                    }
        else:
            # Fallback: parse any standings tables by class
            tables = soup.select('table.week-standings-table')
            if not tables:
                print("Warning: Could not find any standings table")
                return None
            for table in tables:
                tbody = table.find('tbody')
                if not tbody:
                    continue
                for row in tbody.find_all('tr'):
                    cols = row.find_all('td')
                    if len(cols) < 11:
                        continue
                    team_full_name = cols[0].get_text(strip=True)
                    team_abbr = TEAM_NAMES.get(team_full_name)
                    if not team_abbr:
                        print(f"Warning: Unknown team name '{team_full_name}'")
                        continue
                    # Infer division from fallback mapping
                    use_division = 'EGAD' if team_abbr in DIVISION_BY_TEAM['EGAD'] else 'DGAS'
                    wins = int(cols[1].get_text(strip=True))
                    losses = int(cols[2].get_text(strip=True))
                    win_pct = float(cols[3].get_text(strip=True))
                    pts = int(cols[4].get_text(strip=True))
                    ppg = float(cols[5].get_text(strip=True))
                    pts_vs = int(cols[6].get_text(strip=True))
                    plus_minus = int(cols[7].get_text(strip=True))
                    div_w = int(cols[8].get_text(strip=True))
                    div_l = int(cols[9].get_text(strip=True))
                    gb_text = cols[10].get_text(strip=True)
                    gb = 0 if gb_text == '-' else float(gb_text)
                    standings_data[team_abbr] = {
                        'division': use_division,
                        'win': wins,
                        'loss': losses,
                        'winper': win_pct,
                        'pts': pts,
                        'ppg': ppg,
                        'pts_agst': pts_vs,
                        'plus_min': plus_minus,
                        'div_win': div_w,
                        'div_loss': div_l,
                        'gb': gb
                    }
        
        return standings_data
        
    except Exception as e:
        print(f"Error fetching standings from results page: {e}")
        return None


def update_standings_table(year, standings_data):
    """
    Update standings table with new data.
    
    Parameters:
        year (int): Season year
        standings_data (dict): Standings data by team abbreviation
        
    Returns:
        bool: True if successful, False otherwise
    """
    if not HAS_MYSQL:
        print("MySQL not available - cannot update table")
        return False
    
    connection = get_db_connection()
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        
        table_name = f"stand{year}"
        
        # Check if table exists
        cursor.execute(f"SHOW TABLES LIKE '{table_name}'")
        if not cursor.fetchone():
            print(f"Error: Table {table_name} does not exist")
            cursor.close()
            connection.close()
            return False
        
        updated_count = 0
        
        for team_abbr, data in standings_data.items():
            # Update the row for this team
            update_query = f"""
                UPDATE {table_name}
                SET division = %s,
                    win = %s,
                    loss = %s,
                    winper = %s,
                    gb = %s,
                    pts = %s,
                    ppg = %s,
                    pts_agst = %s,
                    plus_min = %s,
                    div_win = %s,
                    div_loss = %s
                WHERE teamID = %s AND year = %s
            """
            
            values = (
                data['division'],
                data['win'],
                data['loss'],
                data['winper'],
                data['gb'],
                data['pts'],
                data['ppg'],
                data['pts_agst'],
                data['plus_min'],
                data['div_win'],
                data['div_loss'],
                team_abbr,
                year
            )
            
            cursor.execute(update_query, values)
            
            if cursor.rowcount > 0:
                updated_count += 1
                print(f"✓ Updated {team_abbr}: {data['win']}-{data['loss']}, {data['pts']} pts")
            else:
                print(f"⚠ No matching row found for team {team_abbr}")
        
        connection.commit()
        cursor.close()
        connection.close()
        
        print(f"\nSuccessfully updated {updated_count} teams in {table_name}")
        return True
        
    except Error as e:
        print(f"Error updating standings table: {e}")
        if connection:
            connection.close()
        return False


def display_standings_summary(standings_data):
    """
    Display a summary of the standings data.
    
    Parameters:
        standings_data (dict): Standings data by team abbreviation
    """
    print("\n" + "="*70)
    print("STANDINGS DATA RETRIEVED FROM RESULTS PAGE")
    print("="*70)
    
    for division in ['EGAD', 'DGAS']:
        print(f"\n{division} Standings:")
        print("-"*70)
        print(f"{'Team':<5} {'W-L':<7} {'Win%':<7} {'Pts':<5} {'PPG':<6} {'Vs':<5} {'+/-':<6} {'Div':<6} {'GB':<4}")
        print("-"*70)
        
        # Filter and sort teams by division
        div_teams = {k: v for k, v in standings_data.items() if v['division'] == division}
        sorted_teams = sorted(div_teams.items(), key=lambda x: x[1]['gb'])
        
        for team_abbr, data in sorted_teams:
            print(f"{team_abbr:<5} {data['win']}-{data['loss']:<5} "
                  f"{data['winper']:<7.3f} {data['pts']:<5} {data['ppg']:<6.1f} "
                  f"{data['pts_agst']:<5} {data['plus_min']:<6} "
                  f"{data['div_win']}-{data['div_loss']:<4} "
                  f"{'-' if data['gb'] == 0 else str(data['gb']):<4}")
    
    print("="*70 + "\n")


def main():
    """Main execution function."""
    if len(sys.argv) < 2:
        print(__doc__)
        sys.exit(1)
    
    try:
        year = int(sys.argv[1])
    except ValueError:
        print("Error: Year must be an integer")
        sys.exit(1)
    
    print(f"\n{'='*70}")
    print(f"END OF SEASON STANDINGS UPDATE - {year}")
    print(f"{'='*70}\n")
    
    # Step 1: Copy current year's table to next year
    print(f"Step 1: Copying stand{year} table to stand{year + 1}...")
    copy_success = copy_standings_table(year)
    
    if copy_success:
        print(f"✓ Successfully copied stand{year} to stand{year + 1}\n")
    else:
        print(f"⚠ Failed to copy table, but continuing with update...\n")
    
    # Step 2: Fetch standings from results page
    print(f"Step 2: Fetching standings data from results page for {year} Week 14...")
    standings_data = fetch_standings_from_results_page(year, week=14)
    
    if not standings_data:
        print("Error: Could not fetch standings data from results page")
        sys.exit(1)
    
    print(f"✓ Successfully fetched standings for {len(standings_data)} teams\n")
    
    # Display the data
    display_standings_summary(standings_data)
    
    # Step 3: Prompt for confirmation
    response = input(f"\nUpdate stand{year} table with this data? (yes/no): ").strip().lower()
    
    if response in ['yes', 'y']:
        print(f"\nStep 3: Updating stand{year} table...")
        success = update_standings_table(year, standings_data)
        
        if success:
            print("\n✓ Standings table updated successfully!")
        else:
            print("\n✗ Failed to update standings table")
            sys.exit(1)
    else:
        print("\nUpdate cancelled by user")
    
    print("\nDone!")


if __name__ == "__main__":
    main()
