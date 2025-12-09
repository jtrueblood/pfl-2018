#!/usr/bin/env python3
"""
Script to fetch NFL player data using ESPN's unofficial API

Usage:
    python3 getplayernfldata.py "Player Name" YEAR WEEK [INSERT]
    
Parameters:
    Player Name - Full name of the player (e.g., "Josh Allen")
    YEAR        - Season year (e.g., 2024)
    WEEK        - Week number(s): single week (13) or comma-separated weeks (11,12,13)
    INSERT      - Optional: 'Yes' to insert into database, 'No' to just display (default: 'No')
    
Examples:
    python3 getplayernfldata.py "Josh Allen" 2024 13
    python3 getplayernfldata.py "Josh Allen" 2024 13 Yes
    python3 getplayernfldata.py "Josh Allen" 2024 "11,12,13" Yes
    python3 getplayernfldata.py "Josh Allen" 2024 "1,2,3,4,5" No
"""

import requests
import json
import sys
from datetime import datetime
import os
import re

# Optional MySQL support
try:
    import mysql.connector
    from mysql.connector import Error
    HAS_MYSQL = True
except Exception:
    HAS_MYSQL = False


# Defaults; will be overridden by wp-config.php if found
DB_CONFIG = {
    'host': 'localhost',
    'database': 'local',
    'user': 'root',
    'password': 'root',
    'port': 3306,
}
DB_TABLE_PREFIX = 'wp_'

# Specific socket for this Local by Flywheel site (from getpflimage.py)
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

# NFL Team name to abbreviation mapping (from functions.php all_nfl_teams_flipped)
NFL_TEAM_ABBREVIATIONS = {
    'Indianapolis Colts': 'IND',
    'Colts': 'IND',
    'Green Bay Packers': 'GNB',
    'Packers': 'GNB',
    'Philadelphia Eagles': 'PHI',
    'Eagles': 'PHI',
    'New England Patriots': 'NWE',
    'Patriots': 'NWE',
    'Minnesota Vikings': 'MIN',
    'Vikings': 'MIN',
    'Atlanta Falcons': 'ATL',
    'Falcons': 'ATL',
    'Dallas Cowboys': 'DAL',
    'Cowboys': 'DAL',
    'Denver Broncos': 'DEN',
    'Broncos': 'DEN',
    'Buffalo Bills': 'BUF',
    'Bills': 'BUF',
    'San Francisco 49ers': 'SFO',
    '49ers': 'SFO',
    'New Orleans Saints': 'NOR',
    'Saints': 'NOR',
    'Cincinnati Bengals': 'CIN',
    'Bengals': 'CIN',
    'Kansas City Chiefs': 'KAN',
    'Chiefs': 'KAN',
    'Seattle Seahawks': 'SEA',
    'Seahawks': 'SEA',
    'Detroit Lions': 'DET',
    'Lions': 'DET',
    'Pittsburgh Steelers': 'PIT',
    'Steelers': 'PIT',
    'Arizona Cardinals': 'ARI',
    'Cardinals': 'ARI',
    'St. Louis Rams': 'STL',
    'San Diego Chargers': 'SDG',
    'Houston Texans': 'HOU',
    'Houston Oilers': 'HOU',
    'Texans': 'HOU',
    'Oilers': 'HOU',
    'Miami Dolphins': 'MIA',
    'Dolphins': 'MIA',
    'New York Giants': 'NYG',
    'Giants': 'NYG',
    'Baltimore Ravens': 'BAL',
    'Ravens': 'BAL',
    'Washington Commanders': 'WAS',
    'Washington Redskins': 'WAS',
    'Commanders': 'WAS',
    'Redskins': 'WAS',
    'Chicago Bears': 'CHI',
    'Bears': 'CHI',
    'Carolina Panthers': 'CAR',
    'Panthers': 'CAR',
    'Oakland Raiders': 'OAK',
    'Las Vegas Raiders': 'LVR',
    'Raiders': 'LVR',
    'Tennessee Titans': 'TEN',
    'Titans': 'TEN',
    'Jacksonville Jaguars': 'JAX',
    'Jaguars': 'JAX',
    'Tampa Bay Buccaneers': 'TAM',
    'Buccaneers': 'TAM',
    'New York Jets': 'NYJ',
    'Jets': 'NYJ',
    'Cleveland Browns': 'CLE',
    'Browns': 'CLE',
    'Los Angeles Rams': 'LAR',
    'Rams': 'LAR',
    'LA Rams': 'LAR',
    'Los Angeles Chargers': 'LAC',
    'Chargers': 'LAC',
    'LA Chargers': 'LAC'
}


def get_team_abbreviation(team_name):
    """
    Convert full team name to 3-letter abbreviation
    
    Parameters
    ----------
    team_name : str
        Full team name (e.g., 'Buffalo Bills')
        
    Returns
    -------
    str
        3-letter abbreviation or original name if not found
    """
    return NFL_TEAM_ABBREVIATIONS.get(team_name, team_name)


def _load_wp_db_config():
    """Attempt to load DB settings and table prefix from wp-config.php."""
    global DB_CONFIG, DB_TABLE_PREFIX
    try:
        # Walk up from this script to find app/public/wp-config.php
        here = os.path.abspath(os.path.dirname(__file__))
        # project root appears to be .../app/public/wp-content/themes/.../pythonscripts
        # ascend to app/public
        parts = here.split(os.sep)
        if 'wp-content' in parts:
            wp_index = parts.index('wp-content')
            wp_root = os.sep.join(parts[:wp_index])
        else:
            # fallback: two levels up
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
    except Exception:
        # best-effort only
        pass


# Load config from wp-config.php when module is imported
_load_wp_db_config()


def calculate_nfl_score(p_id, year, stats):
    """
    Calculate NFL fantasy score based on player position
    
    Parameters
    ----------
    p_id : str
        Player ID (e.g., '2018AlleQB')
    year : int
        Season year
    stats : dict
        Player statistics dictionary
        
    Returns
    -------
    int
        NFL fantasy score
    """
    # Determine player position from p_id (last 2 characters)
    position = p_id[-2:] if len(p_id) >= 2 else ''
    
    # Get stats with defaults
    pass_yds = int(stats.get('Pass Yds', 0) or 0)
    pass_td = int(stats.get('Pass TD', 0) or 0)
    pass_int = int(stats.get('Pass Int', 0) or 0)
    rush_yds = int(stats.get('Rush Yds', 0) or 0)
    rush_td = int(stats.get('Rush TD', 0) or 0)
    rec_yds = int(stats.get('Rec Yds', 0) or 0)
    rec_td = int(stats.get('Rec TD', 0) or 0)
    xpm = int(stats.get('XP Made', 0) or 0)
    fgm = int(stats.get('FG Made', 0) or 0)
    
    # Ensure no negative values (except interceptions)
    if pass_int < 0:
        pass_int = 0
    if pass_yds < 0:
        pass_yds = 0
    if rush_yds < 0:
        rush_yds = 0
    if rec_yds < 0:
        rec_yds = 0
    
    # Calculate score based on position
    if position == 'PK':  # Kicker
        # pk_score_converter: $xpm + ($fgm * 2)
        nfl_score = xpm + (fgm * 2)
    else:  # QB, RB, WR (use pos_score_converter)
        if year == 1991:
            # 1991 scoring rules
            pass_get = pass_yds // 50
            if pass_get < 0:
                pass_data = 0
            else:
                pass_data = pass_get
            nfl_score = pass_data + (rush_yds // 25) + ((pass_td + rush_td + rec_td) * 2) + (rec_yds // 25) - pass_int
        else:
            # Standard scoring rules
            nfl_score = (pass_yds // 30) + (rush_yds // 10) + ((pass_td + rush_td + rec_td) * 2) + (rec_yds // 10) - pass_int
    
    return nfl_score


def insert_player_stats_to_db(p_id, year, week, stats):
    """
    Insert player stats into their player table
    
    Parameters
    ----------
    p_id : str
        Player ID (e.g., '2018AlleQB')
    year : int
        Season year
    week : int
        Week number
    stats : dict
        Player statistics dictionary
        
    Returns
    -------
    bool
        True if successful, False otherwise
    """
    if not HAS_MYSQL:
        print("MySQL not available - cannot insert into database")
        return False
    
    connection = None
    cursor = None
    try:
        # Connect to database
        connection = mysql.connector.connect(
            host=DB_CONFIG['host'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password'],
            database=DB_CONFIG['database'],
            unix_socket=MYSQL_SOCKET
        )
        
        if connection.is_connected():
            cursor = connection.cursor()
            
            # Construct table name from player ID
            table_name = f"`{p_id}`"
            
            # Create week_id (format: YYYYWW)
            week_id = f"{year}{week:02d}"
            
            # Extract team abbreviations and home/away status
            team = stats.get('Team', '')
            versus = stats.get('Versus Team', '')
            home_away_value = stats.get('Home or Away', '')
            home_away = 'H' if home_away_value == 'vs' else 'A'
            game_location = home_away_value  # Store 'vs' or '@' in game_location
            
            # Format date
            game_date = stats.get('Date', '')[:10]  # Get YYYY-MM-DD part
            
            # Calculate NFL fantasy score
            nfl_score = calculate_nfl_score(p_id, year, stats)
            
            # Get existing PFL points from database to calculate diff
            # First try to get existing points value
            pfl_points = 0
            try:
                check_query = f"SELECT points FROM {table_name} WHERE week_id = %s"
                cursor.execute(check_query, (week_id,))
                existing_row = cursor.fetchone()
                if existing_row and existing_row[0] is not None:
                    pfl_points = int(existing_row[0])
            except:
                pass  # pfl_points stays 0 if query fails or no record exists
            
            # Calculate score difference: points - nflscore
            score_diff = pfl_points - nfl_score
            
            # Prepare SQL for INSERT or UPDATE
            # Note: ON DUPLICATE KEY UPDATE does NOT update 'points' - that's managed manually in PFL
            query = f"""
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
                ON DUPLICATE KEY UPDATE
                    nflteam = VALUES(nflteam),
                    nflopp = VALUES(nflopp),
                    home_away = VALUES(home_away),
                    game_location = VALUES(game_location),
                    game_date = VALUES(game_date),
                    pass_yds = VALUES(pass_yds),
                    pass_td = VALUES(pass_td),
                    pass_int = VALUES(pass_int),
                    rush_yds = VALUES(rush_yds),
                    rush_td = VALUES(rush_td),
                    rec_yds = VALUES(rec_yds),
                    rec_td = VALUES(rec_td),
                    xpm = VALUES(xpm),
                    xpa = VALUES(xpa),
                    fgm = VALUES(fgm),
                    fga = VALUES(fga),
                    nflscore = VALUES(nflscore),
                    scorediff = points - VALUES(nflscore)
            """
            
            values = (
                week_id, year, week, pfl_points, '', '', p_id, 0,
                home_away, '', game_date, team, game_location, versus,
                stats.get('Pass Yds', 0),
                stats.get('Pass TD', 0),
                stats.get('Pass Int', 0),
                stats.get('Rush Yds', 0),
                stats.get('Rush TD', 0),
                stats.get('Rec Yds', 0),
                stats.get('Rec TD', 0),
                stats.get('XP Made', 0),
                stats.get('XP Att', 0),
                stats.get('FG Made', 0),
                stats.get('FG Att', 0),
                nfl_score, score_diff
            )
            
            cursor.execute(query, values)
            connection.commit()
            
            cursor.close()
            connection.close()
            return True
            
    except Exception as e:
        print(f"Error inserting into database: {e}")
        return False
        
    finally:
        if cursor:
            cursor.close()
        if connection and connection.is_connected():
            connection.close()


def get_player_id_from_db(player_name):
    """
    Get p_id from wp_players table for a given player name
    
    Parameters
    ----------
    player_name : str
        Player's name to search for (e.g., "Josh Allen")
        
    Returns
    -------
    int or None
        p_id if found, None otherwise
    """
    if not HAS_MYSQL:
        return None
    
    connection = None
    cursor = None
    try:
        # Parse the player name into first and last
        name_parts = player_name.strip().split()
        if len(name_parts) < 2:
            return None
        
        first_name = name_parts[0]
        last_name = ' '.join(name_parts[1:])  # Handle multi-part last names
        
        # Connect using unix socket (same as getpflimage.py)
        connection = mysql.connector.connect(
            host=DB_CONFIG['host'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password'],
            database=DB_CONFIG['database'],
            unix_socket=MYSQL_SOCKET
        )
        
        if connection.is_connected():
            cursor = connection.cursor()
            
            # Query using playerFirst and playerLast columns (same as getpflimage.py)
            table_name = f"{DB_TABLE_PREFIX}players"
            query = f"SELECT p_id FROM {table_name} WHERE playerFirst = %s AND playerLast = %s LIMIT 1"
            cursor.execute(query, (first_name, last_name))
            
            result = cursor.fetchone()
            
            if result:
                return result[0]
            
            return None
            
    except Exception as e:
        # Silently fail if DB is unavailable
        return None
        
    finally:
        if cursor:
            cursor.close()
        if connection and connection.is_connected():
            connection.close()


def get_player_weeks_played_in_season(p_id, year):
    """
    Get weeks played by a player in a specific season
    
    Converted from PHP function get_player_points_team_season() and modified
    to return only the weeks played.
    
    Parameters
    ----------
    p_id : str
        Player ID table name (e.g., '2018AlleQB')
    year : int
        Season year
        
    Returns
    -------
    list or None
        List of week numbers (as integers) that the player played in the season,
        or None if database is unavailable or query fails
    """
    if not HAS_MYSQL:
        return None
    
    connection = None
    cursor = None
    try:
        # Connect using unix socket
        connection = mysql.connector.connect(
            host=DB_CONFIG['host'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password'],
            database=DB_CONFIG['database'],
            unix_socket=MYSQL_SOCKET
        )
        
        if connection.is_connected():
            cursor = connection.cursor()
            
            # Query player table for all records in the given year
            # Using backticks for table name since it contains special characters
            query = f"SELECT week FROM `{p_id}` WHERE year = %s ORDER BY week ASC"
            cursor.execute(query, (year,))
            
            results = cursor.fetchall()
            
            if results:
                # Extract week numbers from results
                weeks = [int(row[0]) for row in results]
                return weeks
            
            return []
            
    except Exception as e:
        # Silently fail if DB is unavailable or table doesn't exist
        return None
        
    finally:
        if cursor:
            cursor.close()
        if connection and connection.is_connected():
            connection.close()


class ESPNNFLAPI:
    """Wrapper for ESPN's unofficial NFL API"""
    
    BASE_URL = "http://site.api.espn.com/apis/site/v2/sports/football/nfl"
    
    @staticmethod
    def get_scoreboard(week=None, year=None):
        """
        Get scoreboard data for a specific week
        
        Parameters
        ----------
        week : int, optional
            Week number (1-18 for regular season)
        year : int, optional
            Season year
            
        Returns
        -------
        dict
            JSON response with scoreboard data
        """
        url = f"{ESPNNFLAPI.BASE_URL}/scoreboard"
        params = {}
        
        if week:
            params['week'] = week
        if year:
            params['seasontype'] = 2  # Regular season
            params['dates'] = year
            
        response = requests.get(url, params=params)
        response.raise_for_status()
        return response.json()
    
    @staticmethod
    def get_teams():
        """
        Get all NFL teams
        
        Returns
        -------
        dict
            JSON response with team data
        """
        url = f"{ESPNNFLAPI.BASE_URL}/teams"
        response = requests.get(url)
        response.raise_for_status()
        return response.json()
    
    @staticmethod
    def get_team_roster(team_id):
        """
        Get roster for a specific team
        
        Parameters
        ----------
        team_id : str or int
            ESPN team ID (e.g., '2' for Buffalo Bills)
            
        Returns
        -------
        dict
            JSON response with roster data
        """
        url = f"{ESPNNFLAPI.BASE_URL}/teams/{team_id}/roster"
        response = requests.get(url)
        response.raise_for_status()
        return response.json()
    
    @staticmethod
    def get_game_summary(game_id):
        """
        Get detailed game summary including player statistics
        
        Parameters
        ----------
        game_id : str or int
            ESPN game ID
            
        Returns
        -------
        dict
            JSON response with game summary and player stats
        """
        url = f"{ESPNNFLAPI.BASE_URL}/summary"
        params = {'event': game_id}
        response = requests.get(url, params=params)
        response.raise_for_status()
        return response.json()


def get_player_stats_from_game(player_name, game_id):
    """
    Get detailed player statistics from a specific game
    
    Parameters
    ----------
    player_name : str
        Player's name (e.g., 'Josh Allen')
    game_id : str or int
        ESPN game ID
        
    Returns
    -------
    dict or None
        Player statistics if found, None otherwise
    """
    api = ESPNNFLAPI()
    
    try:
        game_summary = api.get_game_summary(game_id)
        
        # Check if box score exists
        if 'boxscore' not in game_summary:
            print("No box score available for this game")
            return None
        
        boxscore = game_summary['boxscore']
        players = boxscore.get('players', [])
        
        # Search through each team's players
        for team_data in players:
            team_name = team_data.get('team', {}).get('displayName', '')
            
            # Look through different stat categories (passing, rushing, receiving, etc.)
            for stat_category in team_data.get('statistics', []):
                category_name = stat_category.get('name', '')
                
                for athlete in stat_category.get('athletes', []):
                    if player_name.lower() in athlete.get('athlete', {}).get('displayName', '').lower():
                        # Format the statistics
                        stats = {}
                        for stat in athlete.get('stats', []):
                            stats[stat] = athlete.get('stats', [])[athlete.get('stats', []).index(stat)]
                        
                        return {
                            'player': athlete.get('athlete', {}).get('displayName'),
                            'team': team_name,
                            'category': category_name,
                            'labels': stat_category.get('labels', []),
                            'stats': athlete.get('stats', [])
                        }
        
        print(f"Player {player_name} not found in game {game_id}")
        return None
        
    except requests.exceptions.RequestException as e:
        print(f"API Error: {e}")
        return None
    except Exception as e:
        print(f"Error: {e}")
        return None


def find_player_game_in_week(player_name, team_abbr, week, year=2024):
    """
    Find the game ID for a player's team in a specific week
    
    Parameters
    ----------
    player_name : str
        Player's name (e.g., 'Josh Allen')
    team_abbr : str
        Team abbreviation (e.g., 'BUF')
    week : int
        Week number
    year : int
        Season year
        
    Returns
    -------
    str or None
        Game ID if found, None otherwise
    """
    api = ESPNNFLAPI()
    
    try:
        scoreboard = api.get_scoreboard(week=week, year=year)
        
        if 'events' not in scoreboard:
            print("No games found for this week")
            return None
        
        # Look for team's game
        for game in scoreboard['events']:
            if 'competitions' not in game:
                continue
                
            for competition in game['competitions']:
                for competitor in competition.get('competitors', []):
                    team = competitor.get('team', {})
                    if team.get('abbreviation', '').upper() == team_abbr.upper():
                        return game.get('id')
        
        print(f"No game found for {team_abbr} in week {week}")
        return None
        
    except Exception as e:
        print(f"Error: {e}")
        return None


def get_player_stats_by_week(player_name, team_abbr, week, year=2024):
    """
    Get a player's statistics for a specific week
    
    Parameters
    ----------
    player_name : str
        Player's name (e.g., 'Josh Allen')
    team_abbr : str
        Team abbreviation (e.g., 'BUF')
    week : int
        Week number
    year : int
        Season year
        
    Returns
    -------
    dict or None
        Player statistics if found, None otherwise
    """
    # First, find the game
    game_id = find_player_game_in_week(player_name, team_abbr, week, year)
    
    if not game_id:
        return None
    
    # Then get the player's stats from that game
    return get_player_stats_from_game(player_name, game_id)


def find_player_team(player_name, year=2024):
    """
    Find which team a player is on
    
    Parameters
    ----------
    player_name : str
        Player's name
    year : int
        Season year
        
    Returns
    -------
    str or None
        Team abbreviation if found, None otherwise
    """
    api = ESPNNFLAPI()
    teams_data = api.get_teams()
    
    if 'sports' not in teams_data or not teams_data['sports']:
        return None
    
    leagues = teams_data['sports'][0].get('leagues', [])
    if not leagues:
        return None
    
    # Check each team's roster
    for team in leagues[0].get('teams', []):
        team_info = team.get('team', {})
        team_id = team_info.get('id')
        team_abbr = team_info.get('abbreviation')
        
        if not team_id:
            continue
        
        try:
            roster_data = api.get_team_roster(team_id)
            
            # Check if player is on this team
            if 'athletes' in roster_data:
                for athlete in roster_data['athletes']:
                    if player_name.lower() in athlete.get('fullName', '').lower():
                        return team_abbr
        except:
            continue
    
    return None


def get_comprehensive_player_stats(player_name, week, year=2024):
    """
    Get comprehensive player statistics for a specific week
    
    Parameters
    ----------
    player_name : str
        Player's name (e.g., 'Josh Allen')
    week : int
        Week number
    year : int
        Season year
        
    Returns
    -------
    dict or None
        Comprehensive player statistics including:
        - Date of game
        - Team
        - Versus Team
        - Home or Away
        - Pass Yds, Pass TD, Pass Int
        - Rush Yds, Rush TD
        - Rec Yds, Rec TD
        - 2pt conversions
    """
    api = ESPNNFLAPI()
    
    try:
        # Get scoreboard for the week
        scoreboard = api.get_scoreboard(week=week, year=year)
        
        if 'events' not in scoreboard:
            return None
        
        # Search through all games to find the player
        for game in scoreboard['events']:
            if 'competitions' not in game:
                continue
            
            game_id = game.get('id')
            competition = game['competitions'][0]
            game_date = game.get('date', '')
            
            # Get team information
            competitors = competition.get('competitors', [])
            if len(competitors) < 2:
                continue
            
            home_team = None
            away_team = None
            
            for comp in competitors:
                if comp.get('homeAway') == 'home':
                    home_team = comp.get('team', {})
                else:
                    away_team = comp.get('team', {})
            
            # Get game summary with box score
            game_summary = api.get_game_summary(game_id)
            
            if 'boxscore' not in game_summary:
                continue
            
            boxscore = game_summary['boxscore']
            players = boxscore.get('players', [])
            
            # Search for the player
            for team_data in players:
                team_info = team_data.get('team', {})
                team_name = team_info.get('displayName', '')
                team_abbr = team_info.get('abbreviation', '')
                
                # Determine if this is home or away
                is_home = team_abbr == home_team.get('abbreviation', '')
                home_away = 'vs' if is_home else '@'
                versus_team_name = away_team.get('displayName', '') if is_home else home_team.get('displayName', '')
                
                # Convert team names to abbreviations
                team_abbr_display = get_team_abbreviation(team_name)
                versus_team_abbr = get_team_abbreviation(versus_team_name)
                
                # Initialize stats
                player_stats = {
                    'Date': game_date,
                    'Team': team_abbr_display,
                    'Versus Team': versus_team_abbr,
                    'Home or Away': home_away,
                    'Pass Yds': 0,
                    'Pass TD': 0,
                    'Pass Int': 0,
                    'Rush Yds': 0,
                    'Rush TD': 0,
                    'Rec Yds': 0,
                    'Rec TD': 0,
                    '2pt conversions': 0,
                    'XP Made': 0,
                    'XP Att': 0,
                    'FG Made': 0,
                    'FG Att': 0
                }
                
                found_player = False
                
                # Look through stat categories
                for stat_category in team_data.get('statistics', []):
                    category_name = stat_category.get('name', '').lower()
                    labels = stat_category.get('labels', [])
                    
                    for athlete in stat_category.get('athletes', []):
                        athlete_name = athlete.get('athlete', {}).get('displayName', '')
                        
                        if player_name.lower() not in athlete_name.lower():
                            continue
                        
                        found_player = True
                        stats = athlete.get('stats', [])
                        
                        # Parse stats based on category
                        if 'passing' in category_name:
                            # Labels typically: ['C/ATT', 'YDS', 'AVG', 'TD', 'INT', ...]
                            for i, label in enumerate(labels):
                                if i >= len(stats):
                                    break
                                if label == 'YDS':
                                    player_stats['Pass Yds'] = stats[i]
                                elif label == 'TD':
                                    player_stats['Pass TD'] = stats[i]
                                elif label == 'INT':
                                    player_stats['Pass Int'] = stats[i]
                        
                        elif 'rushing' in category_name:
                            # Labels typically: ['CAR', 'YDS', 'AVG', 'TD', 'LONG']
                            for i, label in enumerate(labels):
                                if i >= len(stats):
                                    break
                                if label == 'YDS':
                                    player_stats['Rush Yds'] = stats[i]
                                elif label == 'TD':
                                    player_stats['Rush TD'] = stats[i]
                        
                        elif 'receiving' in category_name:
                            # Labels typically: ['REC', 'YDS', 'AVG', 'TD', 'LONG', 'TGTS']
                            for i, label in enumerate(labels):
                                if i >= len(stats):
                                    break
                                if label == 'YDS':
                                    player_stats['Rec Yds'] = stats[i]
                                elif label == 'TD':
                                    player_stats['Rec TD'] = stats[i]
                        
                        elif 'kicking' in category_name:
                            # Labels typically: ['FG', 'PCT', 'LONG', 'XP']
                            # Stats format: ['FG_MADE/FG_ATT', 'PCT', 'LONG', 'XP_MADE/XP_ATT']
                            for i, label in enumerate(labels):
                                if i >= len(stats):
                                    break
                                if label == 'FG':
                                    # Parse 'MADE/ATT' format
                                    fg_stat = str(stats[i])
                                    if '/' in fg_stat:
                                        parts = fg_stat.split('/')
                                        player_stats['FG Made'] = parts[0]
                                        player_stats['FG Att'] = parts[1]
                                    else:
                                        player_stats['FG Made'] = fg_stat
                                elif label == 'XP':
                                    # Parse 'MADE/ATT' format
                                    xp_stat = str(stats[i])
                                    if '/' in xp_stat:
                                        parts = xp_stat.split('/')
                                        player_stats['XP Made'] = parts[0]
                                        player_stats['XP Att'] = parts[1]
                                    else:
                                        player_stats['XP Made'] = xp_stat
                
                if found_player:
                    player_stats['Player'] = player_name
                    return player_stats
        
        return None
        
    except Exception as e:
        print(f"Error: {e}")
        return None


def get_team_list():
    """
    Get list of all NFL teams
    
    Returns
    -------
    list
        List of team dictionaries with id, name, abbreviation
    """
    api = ESPNNFLAPI()
    
    try:
        teams_data = api.get_teams()
        teams = []
        
        if 'sports' in teams_data and len(teams_data['sports']) > 0:
            leagues = teams_data['sports'][0].get('leagues', [])
            if leagues:
                for team in leagues[0].get('teams', []):
                    team_info = team.get('team', {})
                    teams.append({
                        'id': team_info.get('id'),
                        'name': team_info.get('displayName'),
                        'abbreviation': team_info.get('abbreviation'),
                        'location': team_info.get('location')
                    })
        
        return teams
        
    except Exception as e:
        print(f"Error fetching teams: {e}")
        return []


def process_single_week(player_name, year, week, insert_to_db, p_id=None):
    """Process stats for a single week
    
    Parameters
    ----------
    player_name : str
        Player's name
    year : int
        Season year
    week : int
        Week number
    insert_to_db : bool
        Whether to insert into database
    p_id : str, optional
        Player ID (will be fetched if not provided)
        
    Returns
    -------
    tuple
        (success: bool, p_id: str)
    """
    print(f"\nFetching stats for {player_name} - Week {week}, {year}...")
    
    # Get player ID from database if not provided
    if not p_id:
        p_id = get_player_id_from_db(player_name)
    
    # Get comprehensive player stats
    stats = get_comprehensive_player_stats(player_name, week, year)
    
    if stats:
        # Format date
        try:
            date_obj = datetime.fromisoformat(stats['Date'].replace('Z', '+00:00'))
            formatted_date = date_obj.strftime('%Y-%m-%d')
        except:
            formatted_date = stats['Date']
        
        # Print results
        print("=" * 60)
        print(f"Player: {player_name}")
        if p_id:
            print(f"Player ID (p_id): {p_id}")
        else:
            print(f"Player ID (p_id): Not found in database")
        print("=" * 60)
        print(f"Date of game:      {formatted_date}")
        print(f"Team:              {stats['Team']}")
        print(f"Versus Team:       {stats['Versus Team']}")
        print(f"Home or Away:      {stats['Home or Away']}")
        print("\nPassing Stats:")
        print(f"  Pass Yds:        {stats['Pass Yds']}")
        print(f"  Pass TD:         {stats['Pass TD']}")
        print(f"  Pass Int:        {stats['Pass Int']}")
        print("\nRushing Stats:")
        print(f"  Rush Yds:        {stats['Rush Yds']}")
        print(f"  Rush TD:         {stats['Rush TD']}")
        print("\nReceiving Stats:")
        print(f"  Rec Yds:         {stats['Rec Yds']}")
        print(f"  Rec TD:          {stats['Rec TD']}")
        print("\nKicking Stats:")
        print(f"  XP Made:         {stats['XP Made']}")
        print(f"  XP Att:          {stats['XP Att']}")
        print(f"  FG Made:         {stats['FG Made']}")
        print(f"  FG Att:          {stats['FG Att']}")
        print("\nOther:")
        print(f"  2pt conversions: {stats['2pt conversions']}")
        print("=" * 60)
        
        # Insert into database if requested
        if insert_to_db:
            if not p_id:
                print("\n⚠ Cannot insert to database: Player ID (p_id) not found")
                print("  Player must exist in wp_players table to insert stats")
            else:
                print(f"\nInserting stats into table {p_id}...")
                success = insert_player_stats_to_db(p_id, year, week, stats)
                if success:
                    print(f"✓ Successfully inserted/updated stats in {p_id} table")
                    print(f"  Week ID: {year}{week:02d}")
                else:
                    print(f"✗ Failed to insert stats into database")
        
        return (True, p_id)
    else:
        print("✗ Could not retrieve player stats")
        print("\nPossible reasons:")
        print("  - Player name is incorrect or not found")
        print("  - Player didn't play in that week")
        print("  - Game hasn't been played yet")
        print("  - Week or year is invalid")
        return (False, p_id)


def main():
    """Main function to handle command-line arguments"""
    
    # Check if arguments are provided
    if len(sys.argv) < 4 or len(sys.argv) > 5:
        print("Usage: python3 getplayernfldata.py \"Player Name\" YEAR WEEK [INSERT]")
        print("\nExamples:")
        print('  python3 getplayernfldata.py "Josh Allen" 2024 13')
        print('  python3 getplayernfldata.py "Josh Allen" 2024 13 Yes')
        print('  python3 getplayernfldata.py "Josh Allen" 2024 "11,12,13" Yes')
        sys.exit(1)
    
    # Parse arguments
    player_name = sys.argv[1]
    
    try:
        year = int(sys.argv[2])
    except ValueError:
        print("Error: YEAR must be an integer")
        sys.exit(1)
    
    # Parse week parameter - can be single or comma-separated
    week_param = sys.argv[3]
    try:
        if ',' in week_param:
            # Multiple weeks
            weeks = [int(w.strip()) for w in week_param.split(',')]
        else:
            # Single week
            weeks = [int(week_param)]
    except ValueError:
        print("Error: WEEK must be an integer or comma-separated integers")
        sys.exit(1)
    
    # Parse optional INSERT parameter (default: 'No')
    insert_to_db = False
    if len(sys.argv) == 5:
        insert_param = sys.argv[4].lower()
        if insert_param in ['yes', 'y', 'true', '1']:
            insert_to_db = True
        elif insert_param in ['no', 'n', 'false', '0']:
            insert_to_db = False
        else:
            print(f"Invalid INSERT parameter: {sys.argv[4]}")
            print("Use 'Yes' or 'No'")
            sys.exit(1)
    
    # Validate inputs
    if year < 2000 or year > 2100:
        print("Error: Invalid year")
        sys.exit(1)
    
    for week in weeks:
        if week < 1 or week > 18:
            print(f"Error: Week {week} must be between 1 and 18")
            sys.exit(1)
    
    # Print summary
    if len(weeks) == 1:
        print(f"Processing {player_name} for Week {weeks[0]}, {year}")
    else:
        print(f"Processing {player_name} for {len(weeks)} weeks: {', '.join(map(str, weeks))}")
        print(f"Year: {year}")
    
    # Get player ID once (reuse for all weeks)
    p_id = get_player_id_from_db(player_name)
    if p_id:
        print(f"Player ID: {p_id}")
        
        # Get weeks played in the season
        weeks_played = get_player_weeks_played_in_season(p_id, year)
        if weeks_played:
            weeks_csv = ','.join(map(str, weeks_played))
            print(f"Weeks Played in {year}: {weeks_csv}")
        else:
            print(f"Weeks Played in {year}: No data found")
    else:
        print("Player ID: Not found in database")
        if insert_to_db:
            print("\n⚠ Warning: Player ID not found automatically")
        
        # Prompt user to manually enter the Player ID
        print("\nWould you like to manually enter the Player ID?")
        print("Enter the Player ID (e.g., '2018AlleQB') or press Enter to skip:")
        try:
            manual_p_id = input("> ").strip()
            if manual_p_id:
                p_id = manual_p_id
                print(f"Using manually entered Player ID: {p_id}")
                
                # Try to get weeks played with the manual ID
                weeks_played = get_player_weeks_played_in_season(p_id, year)
                if weeks_played:
                    weeks_csv = ','.join(map(str, weeks_played))
                    print(f"Weeks Played in {year}: {weeks_csv}")
                else:
                    print(f"Weeks Played in {year}: No data found (table may not exist)")
            else:
                print("No Player ID entered - continuing without database insertion")
        except (EOFError, KeyboardInterrupt):
            print("\nNo Player ID entered - continuing without database insertion")
    
    # Process each week
    results = []
    for i, week in enumerate(weeks):
        if len(weeks) > 1:
            print(f"\n{'='*60}")
            print(f"Processing Week {i+1} of {len(weeks)}: Week {week}")
            print(f"{'='*60}")
        
        success, p_id = process_single_week(player_name, year, week, insert_to_db, p_id)
        results.append((week, success))
    
    # Print summary if multiple weeks
    if len(weeks) > 1:
        print(f"\n\n{'='*60}")
        print("SUMMARY")
        print(f"{'='*60}")
        print(f"Player: {player_name}")
        print(f"Year: {year}")
        print(f"Weeks processed: {len(weeks)}")
        print(f"\nResults:")
        successful = 0
        failed = 0
        for week, success in results:
            status = "✓" if success else "✗"
            print(f"  Week {week:2d}: {status}")
            if success:
                successful += 1
            else:
                failed += 1
        print(f"\nSuccessful: {successful}")
        print(f"Failed: {failed}")
        print(f"{'='*60}")
    
    # Exit with error if any week failed
    if any(not success for week, success in results):
        sys.exit(1)


if __name__ == "__main__":
    main()
