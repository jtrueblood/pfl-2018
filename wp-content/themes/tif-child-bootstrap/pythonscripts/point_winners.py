#!/usr/bin/env python3
"""
Script to display the team with the most points each season (1991-2025)
and their playoff seed. Shows "Missed Playoffs" if they didn't make it.
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

def get_point_winners():
    """
    Get the team with the most points for each season from 1991-2025,
    their playoff seed, and whether they won the championship.
    Returns a list of tuples: (year, team_id, team_name, points, seed, won_championship)
    """
    conn = get_connection()
    cursor = conn.cursor()
    
    results = []
    
    # Loop through each season from 1991 to 2025
    for year in range(1991, 2026):
        try:
            # Query the standings table for this year
            # Get the team with the most points (pts column)
            query = f"""
                SELECT year, teamID, team, pts, playoff_seed 
                FROM stand{year} 
                ORDER BY pts DESC 
                LIMIT 1
            """
            cursor.execute(query)
            
            row = cursor.fetchone()
            if row:
                year_val = row[0]
                team_id = row[1]
                team_name = row[2]
                points = row[3]
                seed = row[4]
                
                # Check if seed is 0 or NULL (missed playoffs)
                if seed == 0 or seed is None:
                    seed_display = "Missed Playoffs"
                else:
                    seed_display = f"#{seed} seed"
                
                # Check if this team won the championship
                champ_query = f"SELECT winTeam FROM wp_champions WHERE year = {year}"
                cursor.execute(champ_query)
                champ_row = cursor.fetchone()
                
                won_championship = False
                if champ_row and champ_row[0] == team_id:
                    won_championship = True
                
                results.append((year_val, team_id, team_name, points, seed_display, won_championship))
            else:
                # No data found for this year (possibly future year)
                results.append((year, None, None, None, None, None))
                
        except mysql.connector.Error as e:
            # Table might not exist for future years
            results.append((year, None, None, None, None, None))
    
    cursor.close()
    conn.close()
    
    return results

def display_results(results):
    """Display the results in a formatted table."""
    print("\n" + "=" * 105)
    print("POINT WINNERS BY SEASON (1991-2025)")
    print("=" * 105)
    print(f"{'Year':<8} {'Team ID':<10} {'Team Name':<25} {'Points':<10} {'Playoff Seed':<18} {'Champion?':<12}")
    print("-" * 105)
    
    # Track stats
    missed_playoff_count = 0
    seed_1_count = 0
    seed_2_count = 0
    seed_3_count = 0
    seed_4_count = 0
    seasons_with_data = 0
    championship_count = 0
    
    for year, team_id, team_name, points, seed_display, won_championship in results:
        if team_id and team_name and points:
            champ_display = "✓ CHAMPION" if won_championship else ""
            print(f"{year:<8} {team_id:<10} {team_name:<25} {points:<10} {seed_display:<18} {champ_display:<12}")
            seasons_with_data += 1
            
            # Track championship wins
            if won_championship:
                championship_count += 1
            
            # Track seed distribution
            if seed_display == "Missed Playoffs":
                missed_playoff_count += 1
            elif seed_display == "#1 seed":
                seed_1_count += 1
            elif seed_display == "#2 seed":
                seed_2_count += 1
            elif seed_display == "#3 seed":
                seed_3_count += 1
            elif seed_display == "#4 seed":
                seed_4_count += 1
        else:
            # Future season with no data yet
            print(f"{year:<8} {'N/A':<10} {'No data yet':<25} {'N/A':<10} {'N/A':<18} {'N/A':<12}")
    
    print("-" * 105)
    
    if seasons_with_data > 0:
        print(f"\nSeasons with data: {seasons_with_data}")
        print(f"\n** CHAMPIONSHIP WINS **")
        print(f"Point winner also won championship: {championship_count} times ({championship_count/seasons_with_data*100:.1f}%)")
        print(f"Point winner did NOT win championship: {seasons_with_data - championship_count} times ({(seasons_with_data - championship_count)/seasons_with_data*100:.1f}%)")
        print(f"\nPlayoff Seed Distribution for Point Winners:")
        print(f"  #1 seed: {seed_1_count} ({seed_1_count/seasons_with_data*100:.1f}%)")
        print(f"  #2 seed: {seed_2_count} ({seed_2_count/seasons_with_data*100:.1f}%)")
        print(f"  #3 seed: {seed_3_count} ({seed_3_count/seasons_with_data*100:.1f}%)")
        print(f"  #4 seed: {seed_4_count} ({seed_4_count/seasons_with_data*100:.1f}%)")
        print(f"  Missed Playoffs: {missed_playoff_count} ({missed_playoff_count/seasons_with_data*100:.1f}%)")
    
    print("\n")

def main():
    print("\nFetching point winners data from database...\n")
    
    results = get_point_winners()
    display_results(results)

if __name__ == "__main__":
    main()
