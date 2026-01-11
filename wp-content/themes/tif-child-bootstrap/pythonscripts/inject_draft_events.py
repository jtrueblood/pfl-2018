#!/usr/bin/env python3
"""
Script to inject draft events into the MFL transactions table display on player pages.

This script modifies the player transaction display to include draft events from wp_drafts table.
For each player drafted, it adds a row with:
- Type: DRAFT
- Action: Drafted R{round}-{pick}
- Date: from drafts page data or default based on year

Note: This modifies the display logic in players.php, not the actual MFL transactions data.
"""

import mysql.connector
from mysql.connector import Error
import sys

# Database Configuration
DB_CONFIG = {
    'host': 'localhost',
    'database': 'local',
    'user': 'root',
    'password': 'root'
}

# Specific socket for Local by Flywheel site
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

# Draft dates by year (you can expand this based on http://pfl-data.local/drafts/)
DRAFT_DATES = {
    1991: '1991-08-01',
    1992: '1992-08-01',
    1993: '1993-08-01',
    1994: '1994-08-01',
    1995: '1995-08-01',
    1996: '1996-08-01',
    1997: '1997-08-01',
    1998: '1998-08-01',
    1999: '1999-08-01',
    2000: '2000-08-01',
    2001: '2001-08-01',
    2002: '2002-08-01',
    2003: '2003-08-01',
    2004: '2004-08-01',
    2005: '2005-08-01',
    2006: '2006-08-01',
    2007: '2007-08-01',
    2008: '2008-08-01',
    2009: '2009-08-01',
    2010: '2010-08-01',
    2011: '2011-08-01',
    2012: '2012-08-01',
    2013: '2013-08-01',
    2014: '2014-08-01',
    2015: '2015-08-01',
    2016: '2016-08-01',
    2017: '2017-08-01',
    2018: '2018-08-01',
    2019: '2019-08-01',
    2020: '2020-08-01',
    2021: '2021-08-01',
    2022: '2022-08-01',
    2023: '2023-08-01',
    2024: '2024-08-01',
    2025: '2025-08-01',
}


def get_db_connection():
    """Establish database connection using Local by Flywheel socket."""
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


def get_draft_records_for_player(player_id):
    """
    Get all draft records for a specific player.
    
    Parameters:
        player_id (str): Player ID (e.g., '2018AlleQB')
        
    Returns:
        list: List of draft records
    """
    connection = get_db_connection()
    if not connection:
        return []
    
    try:
        cursor = connection.cursor(dictionary=True)
        query = "SELECT * FROM wp_drafts WHERE playerid = %s ORDER BY year ASC"
        cursor.execute(query, (player_id,))
        results = cursor.fetchall()
        
        cursor.close()
        connection.close()
        
        return results
    except Error as e:
        print(f"Error getting draft records: {e}")
        if connection:
            connection.close()
        return []


def format_pick(round_num, pick_num):
    """
    Format pick number in R{round}-{pick} format.
    
    Parameters:
        round_num (str): Round number (e.g., '01', '02')
        pick_num (str): Pick number (e.g., '01', '15')
        
    Returns:
        str: Formatted pick (e.g., 'R1-02')
    """
    # Remove leading zeros
    round_int = int(round_num) if round_num else 0
    pick_int = int(pick_num) if pick_num else 0
    
    return f"R{round_int}-{pick_int:02d}"


def get_draft_date(year):
    """
    Get the draft date for a given year.
    
    Parameters:
        year (int): Draft year
        
    Returns:
        str: Date string in YYYY-MM-DD format
    """
    return DRAFT_DATES.get(year, f"{year}-08-01")


def display_draft_events_for_player(player_id):
    """
    Display all draft events for a player.
    
    Parameters:
        player_id (str): Player ID
    """
    drafts = get_draft_records_for_player(player_id)
    
    if not drafts:
        print(f"No draft records found for player {player_id}")
        return
    
    print(f"\n{'='*80}")
    print(f"DRAFT EVENTS FOR PLAYER: {player_id}")
    print(f"{'='*80}")
    print(f"{'Year':<8} {'Date':<12} {'Team':<6} {'Pick':<10} {'Action':<30}")
    print(f"{'-'*80}")
    
    for draft in drafts:
        year = draft['year']
        team = draft['acteam']
        round_num = draft['round']
        pick_num = draft['picknum']
        draft_date = get_draft_date(year)
        pick_format = format_pick(round_num, pick_num)
        action = f"Drafted {pick_format}"
        
        print(f"{year:<8} {draft_date:<12} {team:<6} {pick_format:<10} {action:<30}")
    
    print(f"{'='*80}\n")


def main():
    """Main execution function."""
    if len(sys.argv) < 2:
        print(__doc__)
        print("\nUsage: python3 inject_draft_events.py <player_id>")
        print("Example: python3 inject_draft_events.py 2018AlleQB")
        sys.exit(1)
    
    player_id = sys.argv[1]
    
    print(f"\nLooking up draft events for player: {player_id}")
    display_draft_events_for_player(player_id)
    
    print("\nNOTE: To integrate this into the player page, you need to modify players.php")
    print("to query wp_drafts and merge the draft events into the transactions display.")
    print("\nThe draft events should be inserted with:")
    print("  - Type: 'DRAFT'")
    print("  - Action: 'Drafted R{round}-{pick}'")
    print("  - Date: from DRAFT_DATES mapping")
    print("  - Team: from wp_drafts.acteam")


if __name__ == "__main__":
    main()
