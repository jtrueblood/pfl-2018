#!/usr/bin/env python3
"""
Script to insert Pro Bowl leaders into wp_probowlbox table.
Gets top 2 players from each position (QB, RB, WR, PK) for each division (EGAD, DGAS)
based on points scored in the season.

Usage:
    python3 insert_probowl_leaders.py <year>
    
Example:
    python3 insert_probowl_leaders.py 2025
"""

import mysql.connector
import sys

def get_connection():
    """Establish connection to MySQL database."""
    try:
        conn = mysql.connector.connect(
            host='localhost',
            user='root',
            password='root',
            database='local',
            unix_socket='/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock'
        )
        return conn
    except mysql.connector.Error as e:
        print(f"Error connecting to database: {e}")
        sys.exit(1)

def get_team_divisions(conn, year):
    """
    Get mapping of teams to divisions for the given year.
    Returns dict: {teamID: division}
    """
    cursor = conn.cursor()
    query = f"SELECT teamID, division FROM stand{year}"
    cursor.execute(query)
    
    team_divisions = {}
    for row in cursor.fetchall():
        team_divisions[row[0]] = row[1]
    
    cursor.close()
    return team_divisions

def get_player_team(conn, playerid, year):
    """
    Get the team(s) a player played for in the given year.
    Returns the first team found (most common case).
    """
    cursor = conn.cursor()
    
    try:
        # Query the player's individual table
        query = f"SELECT DISTINCT team FROM {playerid} WHERE year = {year} LIMIT 1"
        cursor.execute(query)
        row = cursor.fetchone()
        
        if row:
            return row[0]
        return None
    except mysql.connector.Error:
        return None
    finally:
        cursor.close()

def get_top_players_by_division(conn, year, position, division, team_divisions, limit=2):
    """
    Get top N players for a given position and division based on points.
    Returns list of tuples: (playerid, points, team)
    """
    cursor = conn.cursor()
    
    # Get all players for this position sorted by points
    query = f"""
        SELECT playerid, points 
        FROM wp_season_leaders 
        WHERE season = {year} AND playerid LIKE '%{position}'
        ORDER BY points DESC
    """
    cursor.execute(query)
    
    results = []
    for row in cursor.fetchall():
        playerid = row[0]
        points = row[1]
        
        # Get the player's team
        team = get_player_team(conn, playerid, year)
        
        if team and team in team_divisions:
            player_division = team_divisions[team]
            
            # Only include players from the specified division
            if player_division == division:
                results.append((playerid, points, team))
                
                # Stop once we have enough players
                if len(results) >= limit:
                    break
    
    cursor.close()
    return results

def clear_existing_probowl_data(conn, year):
    """Remove existing pro bowl data for the given year."""
    cursor = conn.cursor()
    query = f"DELETE FROM wp_probowlbox WHERE year = {year}"
    cursor.execute(query)
    conn.commit()
    cursor.close()
    print(f"Cleared existing Pro Bowl data for {year}")

def insert_probowl_player(conn, playerid, team, division, year, starter):
    """
    Insert a player into the wp_probowlbox table.
    """
    cursor = conn.cursor()
    
    # Extract position from playerid (last 2 characters)
    pos = playerid[-2:]
    
    # Generate the next ID
    # Get the highest current ID for this year
    query = f"SELECT id FROM wp_probowlbox WHERE year = {year} ORDER BY id DESC LIMIT 1"
    cursor.execute(query)
    row = cursor.fetchone()
    
    if row:
        # Extract the number from the last ID and increment
        last_id = row[0]
        last_num = int(last_id.replace(f'prb{year}', ''))
        next_num = last_num + 1
    else:
        # First entry for this year
        next_num = 1
    
    new_id = f'prb{year}{next_num:02d}'
    
    # Insert the player
    insert_query = """
        INSERT INTO wp_probowlbox 
        (id, playerid, pos, team, league, year, points, starter, ptsused)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
    """
    
    values = (new_id, playerid, pos, team, division, year, 0, starter, 0)
    
    cursor.execute(insert_query, values)
    conn.commit()
    cursor.close()
    
    return new_id

def main():
    """Main function to populate Pro Bowl leaders."""
    # Check for year argument
    if len(sys.argv) != 2:
        print("Usage: python3 insert_probowl_leaders.py <year>")
        print("Example: python3 insert_probowl_leaders.py 2025")
        sys.exit(1)
    
    try:
        year = int(sys.argv[1])
    except ValueError:
        print(f"Error: Invalid year '{sys.argv[1]}'. Year must be a number.")
        sys.exit(1)
    
    # Validate year range
    if year < 1991 or year > 2100:
        print(f"Error: Year {year} is out of valid range (1991-2100)")
        sys.exit(1)
    
    print(f"\n{'='*80}")
    print(f"INSERTING PRO BOWL LEADERS FOR {year}")
    print(f"{'='*80}\n")
    
    conn = get_connection()
    
    # Get team-to-division mapping
    print("Getting team divisions...")
    team_divisions = get_team_divisions(conn, year)
    print(f"Found {len(team_divisions)} teams\n")
    
    # Clear existing data for this year
    clear_existing_probowl_data(conn, year)
    
    # Positions to process
    positions = ['QB', 'RB', 'WR', 'PK']
    divisions = ['EGAD', 'DGAS']
    
    total_inserted = 0
    
    # Process each division and position
    for division in divisions:
        print(f"\n{'-'*80}")
        print(f"Processing {division} Division")
        print(f"{'-'*80}")
        
        for position in positions:
            print(f"\n{position} Leaders:")
            
            # Get top 2 players for this position/division
            top_players = get_top_players_by_division(
                conn, year, position, division, team_divisions, limit=2
            )
            
            if not top_players:
                print(f"  No {position} players found for {division}")
                continue
            
            # Insert each player
            for idx, (playerid, points, team) in enumerate(top_players):
                # starter: 0 for #1 ranked, 1 for #2 ranked
                starter = idx
                
                new_id = insert_probowl_player(
                    conn, playerid, team, division, year, starter
                )
                
                rank = idx + 1
                starter_label = "STARTER" if starter == 0 else "BACKUP"
                print(f"  #{rank} ({starter_label}): {playerid:15s} | Team: {team} | {points} pts | ID: {new_id}")
                
                total_inserted += 1
    
    print(f"\n{'='*80}")
    print(f"SUCCESS: Inserted {total_inserted} players into wp_probowlbox")
    print(f"{'='*80}\n")
    
    conn.close()

if __name__ == "__main__":
    main()
