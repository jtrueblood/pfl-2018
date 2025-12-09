#!/usr/bin/env python3
"""
Script to fetch MyFantasyLeague player data for a single player and optionally insert into database

This script mirrors the functionality of build-weekly-mfl.php but operates on a single player
instead of processing all teams and players in a week.

Usage:
    python3 build-single-player-mfl.py <player_identifier> <year> <week>
    
Parameters:
    player_identifier - Either:
                        - Player ID (e.g., '2018AlleQB')
                        - Player name (e.g., 'Josh Allen')
                        - MFL ID (numeric, e.g., '14477')
    year             - Season year (e.g., 2024)
    week             - Week number (1-17)
    
Examples:
    python3 build-single-player-mfl.py "2018AlleQB" 2024 13
    python3 build-single-player-mfl.py "Josh Allen" 2024 13
    python3 build-single-player-mfl.py 14477 2024 13
"""

import requests
import json
import sys
import os
import re
from datetime import datetime

# Optional MySQL support
try:
    import mysql.connector
    from mysql.connector import Error
    HAS_MYSQL = True
except Exception:
    HAS_MYSQL = False
    print("Warning: MySQL connector not available. Database operations will be disabled.")

# MFL API Configuration
MFL_LEAGUE_ID = 38954
MFL_API_KEY = "aRNp1sySvuWqx0CmO1HIZDYeFbox"
MFL_BASE_URL = "https://www58.myfantasyleague.com"

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

# MFL to PFL team mapping (from build-weekly-mfl.php)
MFL_TEAM_TO_PFL = {
    '0005': 'DST',
    '0003': 'PEP',
    '0004': 'WRZ',
    '0002': 'ETS',
    '0006': 'BST',
    '0008': 'HAT',
    '0009': 'CMN',
    '0010': 'BUL',
    '0007': 'SNR',
    '0001': 'TSG'
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


def get_mfl_to_pfl_mapping():
    """
    Get mapping of MFL player IDs to PFL player IDs from wp_players table.
    Equivalent to PHP function playerid_mfl_to_pfl()
    
    Returns:
        dict: {mfl_id: p_id} mapping
    """
    if not HAS_MYSQL:
        return {}
    
    connection = get_db_connection()
    if not connection:
        return {}
    
    try:
        cursor = connection.cursor()
        query = f"SELECT p_id, mflid FROM {DB_TABLE_PREFIX}players"
        cursor.execute(query)
        
        mapping = {}
        for p_id, mflid in cursor.fetchall():
            if mflid:
                mapping[str(mflid)] = p_id
        
        cursor.close()
        connection.close()
        return mapping
    except Error as e:
        print(f"Error getting MFL to PFL mapping: {e}")
        if connection:
            connection.close()
        return {}


def get_player_id_from_name(player_name):
    """
    Get p_id from wp_players table for a given player name.
    
    Parameters:
        player_name (str): Player's name to search for (e.g., "Josh Allen")
        
    Returns:
        str or None: p_id if found, None otherwise
    """
    if not HAS_MYSQL:
        return None
    
    connection = get_db_connection()
    if not connection:
        return None
    
    try:
        # Parse the player name into first and last
        name_parts = player_name.strip().split()
        if len(name_parts) < 2:
            return None
        
        first_name = name_parts[0]
        last_name = ' '.join(name_parts[1:])  # Handle multi-part last names
        
        cursor = connection.cursor()
        query = f"SELECT p_id FROM {DB_TABLE_PREFIX}players WHERE playerFirst = %s AND playerLast = %s LIMIT 1"
        cursor.execute(query, (first_name, last_name))
        
        result = cursor.fetchone()
        cursor.close()
        connection.close()
        
        if result:
            return result[0]
        
        return None
    except Error as e:
        print(f"Error looking up player by name: {e}")
        if connection:
            connection.close()
        return None


def get_mfl_id_from_player_id(p_id):
    """
    Get MFL ID from PFL player ID.
    
    Parameters:
        p_id (str): PFL player ID (e.g., '2018AlleQB')
        
    Returns:
        str or None: MFL ID if found, None otherwise
    """
    if not HAS_MYSQL:
        return None
    
    connection = get_db_connection()
    if not connection:
        return None
    
    try:
        cursor = connection.cursor()
        query = f"SELECT mflid FROM {DB_TABLE_PREFIX}players WHERE p_id = %s LIMIT 1"
        cursor.execute(query, (p_id,))
        
        result = cursor.fetchone()
        cursor.close()
        connection.close()
        
        if result:
            return str(result[0])
        
        return None
    except Error as e:
        print(f"Error looking up MFL ID: {e}")
        if connection:
            connection.close()
        return None


def get_player_name_from_id(p_id):
    """
    Get player name from PFL player ID.
    
    Parameters:
        p_id (str): PFL player ID (e.g., '2018AlleQB')
        
    Returns:
        str or None: Player name if found, None otherwise
    """
    if not HAS_MYSQL:
        return None
    
    connection = get_db_connection()
    if not connection:
        return None
    
    try:
        cursor = connection.cursor()
        query = f"SELECT playerFirst, playerLast FROM {DB_TABLE_PREFIX}players WHERE p_id = %s LIMIT 1"
        cursor.execute(query, (p_id,))
        
        result = cursor.fetchone()
        cursor.close()
        connection.close()
        
        if result:
            return f"{result[0]} {result[1]}"
        
        return None
    except Error as e:
        print(f"Error looking up player name: {e}")
        if connection:
            connection.close()
        return None


def resolve_player_identifier(identifier):
    """
    Resolve player identifier to (p_id, mfl_id, player_name) tuple.
    
    Parameters:
        identifier (str): Can be p_id, player name, or MFL ID
        
    Returns:
        tuple: (p_id, mfl_id, player_name) or (None, None, None) if not found
    """
    # Check if it's a numeric MFL ID
    if identifier.isdigit():
        mfl_id = identifier
        mapping = get_mfl_to_pfl_mapping()
        p_id = mapping.get(mfl_id)
        if p_id:
            player_name = get_player_name_from_id(p_id)
            return (p_id, mfl_id, player_name)
        return (None, mfl_id, None)
    
    # Check if it's a p_id format (YYYYNamePO)
    if len(identifier) >= 10 and identifier[:4].isdigit():
        p_id = identifier
        mfl_id = get_mfl_id_from_player_id(p_id)
        player_name = get_player_name_from_id(p_id)
        return (p_id, mfl_id, player_name)
    
    # Assume it's a player name
    player_name = identifier
    p_id = get_player_id_from_name(player_name)
    if p_id:
        mfl_id = get_mfl_id_from_player_id(p_id)
        return (p_id, mfl_id, player_name)
    
    return (None, None, None)


def get_mfl_player_score(mfl_id, year, week):
    """
    Get player score from MFL API.
    Equivalent to PHP function get_weekly_mfl_player_results()
    
    Parameters:
        mfl_id (str): MFL player ID
        year (int): Season year
        week (int): Week number
        
    Returns:
        dict: {week: score} or None if failed
    """
    try:
        url = f"{MFL_BASE_URL}/{year}/export"
        params = {
            'TYPE': 'playerScores',
            'L': MFL_LEAGUE_ID,
            'W': week,
            'YEAR': year,
            'PLAYERS': mfl_id,
            'JSON': 1,
            'APIKEY': MFL_API_KEY
        }
        
        headers = {
            'Cookie': 'MFL_PW_SEQ=ah9q2M6Ss%2Bis3Q29; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D'
        }
        
        response = requests.get(url, params=params, headers=headers, timeout=30)
        response.raise_for_status()
        
        data = response.json()
        
        if 'playerScores' in data and 'playerScore' in data['playerScores']:
            player_score = data['playerScores']['playerScore']
            score_week = int(player_score['week'])
            score_value = float(player_score['score'])
            return {score_week: score_value}
        
        return None
    except Exception as e:
        print(f"Error fetching MFL player score: {e}")
        return None


def get_mfl_weekly_results(year, week):
    """
    Get weekly matchup results from MFL API.
    This is used to determine team context and starter status.
    
    Parameters:
        year (int): Season year
        week (int): Week number
        
    Returns:
        dict: MFL weekly results JSON or None if failed
    """
    try:
        url = f"{MFL_BASE_URL}/{year}/export"
        params = {
            'TYPE': 'weeklyResults',
            'L': MFL_LEAGUE_ID,
            'W': week,
            'JSON': 1,
            'APIKEY': MFL_API_KEY
        }
        
        headers = {
            'Cookie': 'MFL_PW_SEQ=ah9q2M6Ss%2Bis3Q29; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D'
        }
        
        response = requests.get(url, params=params, headers=headers, timeout=30)
        response.raise_for_status()
        
        data = response.json()
        return data
    except Exception as e:
        print(f"Error fetching MFL weekly results: {e}")
        return None


def find_player_in_matchups(mfl_id, weekly_results):
    """
    Find player in weekly matchup results to determine team and opponent.
    
    Parameters:
        mfl_id (str): MFL player ID
        weekly_results (dict): MFL weekly results data
        
    Returns:
        dict: Player context (team, versus, home_away, etc.) or None
    """
    if not weekly_results or 'weeklyResults' not in weekly_results:
        return None
    
    matchups = weekly_results['weeklyResults'].get('matchup', [])
    
    for matchup in matchups:
        franchises = matchup.get('franchise', [])
        if not isinstance(franchises, list):
            franchises = [franchises]
        
        for i, franchise in enumerate(franchises):
            starters_str = franchise.get('starters', '')
            if not starters_str:
                continue
            
            # Parse starters (comma-separated MFL IDs)
            starters = [s.strip() for s in starters_str.split(',') if s.strip()]
            
            if mfl_id in starters:
                # Found the player! Get context
                team_mfl_id = franchise.get('id')
                team_pfl = MFL_TEAM_TO_PFL.get(team_mfl_id, team_mfl_id)
                
                # Get opponent (other franchise in matchup)
                opponent_idx = 1 - i  # 0->1, 1->0
                if opponent_idx < len(franchises):
                    opponent_mfl_id = franchises[opponent_idx].get('id')
                    versus_pfl = MFL_TEAM_TO_PFL.get(opponent_mfl_id, opponent_mfl_id)
                    versus_score = float(franchises[opponent_idx].get('score', 0))
                else:
                    versus_pfl = ''
                    versus_score = 0
                
                is_home = franchise.get('isHome') == '1'
                team_score = float(franchise.get('score', 0))
                result = franchise.get('result', 'L')
                
                return {
                    'team': team_pfl,
                    'versus': versus_pfl,
                    'home_away': 'H' if is_home else 'A',
                    'team_score': team_score,
                    'versus_score': versus_score,
                    'result': result,
                    'win_loss': 1 if result == 'W' else 0
                }
    
    return None


def insert_player_data_to_db(p_id, year, week, points, team_context):
    """
    Insert player weekly data into their player table.
    Mirrors the INSERT logic from build-weekly-mfl.php
    
    Parameters:
        p_id (str): PFL player ID
        year (int): Season year
        week (int): Week number
        points (float): Player's MFL points
        team_context (dict): Team and matchup context
        
    Returns:
        bool: True if successful, False otherwise
    """
    if not HAS_MYSQL:
        print("MySQL not available - cannot insert into database")
        return False
    
    connection = get_db_connection()
    if not connection:
        return False
    
    try:
        cursor = connection.cursor()
        
        # Create week_id (format: YYYYWW)
        week_id = f"{year}{week:02d}"
        
        # Extract context
        team = team_context.get('team', '')
        versus = team_context.get('versus', '')
        home_away = team_context.get('home_away', 'H')
        win_loss = team_context.get('win_loss', 0)
        
        # Get stadium based on home/away
        # Note: In real usage, you'd query wp_teams for stadium info
        # For now, leaving as empty string (location field)
        location = ''
        
        # Construct table name from player ID
        table_name = f"`{p_id}`"
        
        # Regular season insert (week <= 14)
        if week <= 14:
            # Check if record already exists
            check_query = f"SELECT week_id FROM {table_name} WHERE week_id = %s"
            cursor.execute(check_query, (week_id,))
            existing = cursor.fetchone()
            
            if existing:
                # Update existing record
                update_query = f"""
                    UPDATE {table_name}
                    SET points = %s,
                        team = %s,
                        versus = %s,
                        win_loss = %s,
                        home_away = %s
                    WHERE week_id = %s
                """
                cursor.execute(update_query, (points, team, versus, win_loss, home_away, week_id))
            else:
                # Insert new record
                insert_query = f"""
                    INSERT INTO {table_name} (
                        week_id, year, week, points, team, versus, playerid, win_loss,
                        home_away, location, game_date, nflteam, game_location, nflopp,
                        pass_yds, pass_td, pass_int, rush_yds, rush_td, rec_yds, rec_td,
                        xpm, xpa, fgm, fga, nflscore, scorediff
                    ) VALUES (
                        %s, %s, %s, %s, %s, %s, %s, %s,
                        %s, %s, %s, %s, %s, %s,
                        %s, %s, %s, %s, %s, %s, %s,
                        %s, %s, %s, %s, %s, %s
                    )
                """
                values = (
                    week_id, year, week, points, team, versus, p_id, win_loss,
                    home_away, location, '2022-00-00', 'TTT', 'S', 'ZZZ',
                    0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0
                )
                cursor.execute(insert_query, values)
        else:
            # Playoffs (week >= 15)
            # Note: Playoff insert logic is different, using wp_playoffs table
            # This would require additional logic for seed determination
            print(f"Warning: Playoff weeks (>= 15) require special handling not yet implemented")
            cursor.close()
            connection.close()
            return False
        
        connection.commit()
        cursor.close()
        connection.close()
        return True
        
    except Error as e:
        print(f"Error inserting into database: {e}")
        if connection:
            connection.close()
        return False


def display_player_data(p_id, mfl_id, player_name, year, week, score_data, team_context):
    """
    Display player data in a formatted way.
    
    Parameters:
        p_id (str): PFL player ID
        mfl_id (str): MFL player ID
        player_name (str): Player name
        year (int): Season year
        week (int): Week number
        score_data (dict): Score data from MFL
        team_context (dict): Team and matchup context
    """
    print("\n" + "="*60)
    print("PLAYER DATA RETRIEVED FROM MFL")
    print("="*60)
    print(f"Player Name:     {player_name or 'Unknown'}")
    print(f"PFL Player ID:   {p_id or 'Unknown'}")
    print(f"MFL Player ID:   {mfl_id or 'Unknown'}")
    print(f"Season:          {year}")
    print(f"Week:            {week}")
    print("-"*60)
    
    if score_data:
        score = score_data.get(week, 0)
        print(f"MFL Points:      {score}")
    else:
        print(f"MFL Points:      N/A (Not available)")
    
    if team_context:
        print(f"Team:            {team_context.get('team', 'N/A')}")
        print(f"Versus:          {team_context.get('versus', 'N/A')}")
        print(f"Home/Away:       {team_context.get('home_away', 'N/A')}")
        print(f"Team Score:      {team_context.get('team_score', 'N/A')}")
        print(f"Opponent Score:  {team_context.get('versus_score', 'N/A')}")
        print(f"Result:          {team_context.get('result', 'N/A')}")
    else:
        print("Team Context:    Not found in starters for this week")
    
    print("="*60 + "\n")


def main():
    """Main execution function."""
    if len(sys.argv) < 4:
        print(__doc__)
        sys.exit(1)
    
    player_identifier = sys.argv[1]
    try:
        year = int(sys.argv[2])
        week = int(sys.argv[3])
    except ValueError:
        print("Error: Year and week must be integers")
        sys.exit(1)
    
    # Validate week
    if week < 1 or week > 17:
        print("Error: Week must be between 1 and 17")
        sys.exit(1)
    
    print(f"\nResolving player identifier: {player_identifier}")
    
    # Resolve player identifier
    p_id, mfl_id, player_name = resolve_player_identifier(player_identifier)
    
    if not mfl_id:
        print(f"Error: Could not resolve player identifier '{player_identifier}'")
        print("Please provide a valid:")
        print("  - Player ID (e.g., '2018AlleQB')")
        print("  - Player name (e.g., 'Josh Allen')")
        print("  - MFL ID (numeric, e.g., '14477')")
        sys.exit(1)
    
    print(f"Resolved to: {player_name or 'Unknown'} (PFL ID: {p_id or 'Unknown'}, MFL ID: {mfl_id})")
    
    # Fetch player score from MFL
    print(f"\nFetching player score from MFL for {year} Week {week}...")
    score_data = get_mfl_player_score(mfl_id, year, week)
    
    if not score_data:
        print("Warning: Could not fetch player score from MFL")
    
    # Fetch weekly matchup results to get team context
    print("Fetching weekly matchup results...")
    weekly_results = get_mfl_weekly_results(year, week)
    
    team_context = None
    if weekly_results:
        team_context = find_player_in_matchups(mfl_id, weekly_results)
        if not team_context:
            print("Warning: Player not found in starters for this week")
    else:
        print("Warning: Could not fetch weekly matchup results")
    
    # Display the data
    display_player_data(p_id, mfl_id, player_name, year, week, score_data, team_context)
    
    # Only proceed with database insert if we have all required data
    if not p_id:
        print("Error: Cannot insert to database - PFL Player ID not found")
        print("This player may not exist in wp_players table")
        sys.exit(1)
    
    if not score_data:
        print("Error: Cannot insert to database - no score data available")
        sys.exit(1)
    
    if not team_context:
        print("Error: Cannot insert to database - team context not available")
        print("Player may not have been a starter this week")
        sys.exit(1)
    
    # Prompt user for confirmation
    response = input("\nInsert this data into the database? (yes/no): ").strip().lower()
    
    if response in ['yes', 'y']:
        score = score_data.get(week, 0)
        print(f"\nInserting data into player table {p_id}...")
        success = insert_player_data_to_db(p_id, year, week, score, team_context)
        
        if success:
            print("✓ Data successfully inserted into database")
        else:
            print("✗ Failed to insert data into database")
            sys.exit(1)
    else:
        print("\nDatabase insert cancelled by user")
    
    print("\nDone!")


if __name__ == "__main__":
    main()
