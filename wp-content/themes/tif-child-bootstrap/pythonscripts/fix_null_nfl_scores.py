#!/usr/bin/env python3
"""
Fix NULL NFL scores in player tables.

This script:
1. Finds all player tables with NULL nflscore values
2. Recalculates the expected NFL score based on game stats
3. Updates the nflscore column
4. Recalculates and updates the scorediff column
"""

import mysql.connector
from mysql.connector import Error
import sys

# Database configuration
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

DB_CONFIG = {
    'host': 'localhost',
    'database': 'local',
    'user': 'root',
    'password': 'root',
    'unix_socket': MYSQL_SOCKET
}

def pos_score_converter(year, pass_yds, pass_td, rush_yds, rush_td, pass_int, rec_yds, rec_td):
    """Calculate NFL expected score for QB/RB/WR positions."""
    # Handle None/NULL values
    if pass_int is None or pass_int < 0:
        pass_int = 0
    if pass_yds is None or pass_yds < 0:
        pass_yds = 0
    if rush_yds is None or rush_yds < 0:
        rush_yds = 0
    if rec_yds is None or rec_yds < 0:
        rec_yds = 0
    if pass_td is None:
        pass_td = 0
    if rush_td is None:
        rush_td = 0
    if rec_td is None:
        rec_td = 0
    
    if year == 1991:
        # 1991 scoring rules
        pass_get = pass_yds // 50
        if pass_get < 0:
            pass_data = 0
        else:
            pass_data = pass_get
        
        posscore = pass_data + (rush_yds // 20) + ((pass_td + rush_td + rec_td) * 2) + (rec_yds // 20) - pass_int
        return posscore
    else:
        # Post-1991 scoring rules
        posscore = (pass_yds // 30) + (rush_yds // 10) + ((pass_td + rush_td + rec_td) * 2) + (rec_yds // 10) - pass_int
        return posscore

def pk_score_converter(year, xpm, fgm):
    """Calculate NFL expected score for PK position."""
    if xpm is None:
        xpm = 0
    if fgm is None:
        fgm = 0
    
    pkscore = xpm + (fgm * 2)
    return pkscore

def get_all_player_tables(cursor):
    """Get list of all player table names from wp_players."""
    cursor.execute("SELECT p_id FROM wp_players")
    return [row[0] for row in cursor.fetchall()]

def get_score_corrections(cursor, player_id):
    """Get score corrections for a player from wp_score_correct."""
    query = """
        SELECT weekid, score 
        FROM wp_score_correct 
        WHERE playerid = %s
    """
    cursor.execute(query, (player_id,))
    corrections = {}
    for row in cursor.fetchall():
        weekid = row[0]
        score = row[1] if row[1] is not None else 0
        corrections[weekid] = score
    return corrections

def fix_player_null_scores(cursor, player_id, player_position, dry_run=True):
    """Fix NULL nflscore values for a single player."""
    table_name = player_id
    
    # Check if table exists
    cursor.execute(f"SHOW TABLES LIKE '{table_name}'")
    if not cursor.fetchone():
        return 0, []
    
    # Get score corrections for this player
    corrections = get_score_corrections(cursor, player_id)
    
    # Find rows with NULL nflscore
    query = f"""
        SELECT week_id, year, week, pass_yds, pass_td, pass_int, 
               rush_yds, rush_td, rec_yds, rec_td, xpm, fgm, points
        FROM `{table_name}`
        WHERE nflscore IS NULL OR nflscore = ''
    """
    
    try:
        cursor.execute(query)
        null_rows = cursor.fetchall()
    except Error as e:
        print(f"Error querying {table_name}: {e}")
        return 0, []
    
    if not null_rows:
        return 0, []
    
    updates = []
    for row in null_rows:
        weekid = row[0]
        year = row[1]
        week = row[2]
        pass_yds = row[3]
        pass_td = row[4]
        pass_int = row[5]
        rush_yds = row[6]
        rush_td = row[7]
        rec_yds = row[8]
        rec_td = row[9]
        xpm = row[10]
        fgm = row[11]
        pfl_points = row[12] if row[12] is not None else 0
        
        # Calculate NFL expected score based on position
        if player_position == 'PK':
            nfl_score = pk_score_converter(year, xpm, fgm)
        else:
            nfl_score = pos_score_converter(year, pass_yds, pass_td, rush_yds, rush_td, 
                                           pass_int, rec_yds, rec_td)
        
        # Add any score corrections
        correction = corrections.get(weekid, 0)
        nfl_score += correction
        
        # Calculate difference
        score_diff = pfl_points - nfl_score
        
        updates.append({
            'weekid': weekid,
            'year': year,
            'week': week,
            'nfl_score': nfl_score,
            'pfl_score': pfl_points,
            'diff': score_diff
        })
        
        if not dry_run:
            # Update the database
            update_query = f"""
                UPDATE `{table_name}`
                SET nflscore = %s, scorediff = %s
                WHERE week_id = %s
            """
            cursor.execute(update_query, (nfl_score, score_diff, weekid))
    
    return len(updates), updates

def main():
    dry_run = True
    specific_player = None
    connection = None
    
    # Parse command line arguments
    for i, arg in enumerate(sys.argv[1:], 1):
        if arg == '--execute':
            dry_run = False
        elif arg.startswith('--player='):
            specific_player = arg.split('=', 1)[1]
        elif not arg.startswith('--'):
            # Assume it's a player ID if it doesn't start with --
            specific_player = arg
    
    if dry_run:
        print("DRY RUN MODE: No changes will be made. Use --execute to apply changes.")
    else:
        print("EXECUTE MODE: Changes will be written to database")
    
    if specific_player:
        print(f"Target: Single player ({specific_player})")
    else:
        print("Target: All players")
    
    print("-" * 80)
    
    try:
        # Connect to database
        connection = mysql.connector.connect(**DB_CONFIG)
        cursor = connection.cursor()
        
        # Get players to process
        if specific_player:
            cursor.execute("SELECT p_id, position FROM wp_players WHERE p_id = %s", (specific_player,))
            players = cursor.fetchall()
            if not players:
                print(f"Error: Player '{specific_player}' not found in wp_players table.")
                return 1
        else:
            cursor.execute("SELECT p_id, position FROM wp_players")
            players = cursor.fetchall()
        
        total_fixed = 0
        players_with_issues = []
        
        for player_id, position in players:
            count, updates = fix_player_null_scores(cursor, player_id, position, dry_run)
            
            if count > 0:
                total_fixed += count
                players_with_issues.append({
                    'player_id': player_id,
                    'position': position,
                    'count': count,
                    'updates': updates
                })
                
                print(f"\n{player_id} ({position}) - {count} NULL scores found:")
                for update in updates:
                    print(f"  Year: {update['year']}, Week: {update['week']} | "
                          f"NFL Expected: {update['nfl_score']} | "
                          f"PFL Actual: {update['pfl_score']} | "
                          f"Difference: {update['diff']}")
        
        if not dry_run:
            connection.commit()
            print(f"\n✓ Successfully updated {total_fixed} records across {len(players_with_issues)} players")
        else:
            print(f"\n✓ Found {total_fixed} NULL records across {len(players_with_issues)} players")
            print("\nRun with --execute flag to apply these changes to the database.")
        
    except Error as e:
        print(f"Database error: {e}")
        return 1
    
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
    
    return 0

if __name__ == "__main__":
    sys.exit(main())
