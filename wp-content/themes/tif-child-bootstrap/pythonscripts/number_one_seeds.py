#!/usr/bin/env python3
"""
Script to display all #1 seeds from 1991-2025 and their total points scored.
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

def get_number_one_seeds():
    """
    Get all #1 seeds from 1991-2025 with their points.
    Returns a list of tuples: (year, team_id, team_name, points)
    """
    conn = get_connection()
    cursor = conn.cursor()
    
    results = []
    
    # Loop through each season from 1991 to 2025
    for year in range(1991, 2026):
        try:
            # Query the standings table for this year
            # playoff_seed, teamID, team, pts are the actual column names
            query = f"SELECT year, playoff_seed, teamID, team, pts FROM stand{year} WHERE playoff_seed = 1"
            cursor.execute(query)
            
            row = cursor.fetchone()
            if row:
                year_val = row[0]
                team_id = row[2]
                team_name = row[3]
                points = row[4]
                results.append((year_val, team_id, team_name, points))
            else:
                # No #1 seed found for this year (possibly future year)
                results.append((year, None, None, None))
                
        except mysql.connector.Error as e:
            # Table might not exist for future years
            results.append((year, None, None, None))
    
    cursor.close()
    conn.close()
    
    return results

def display_results(results):
    """Display the results in a formatted table."""
    print("\n" + "=" * 70)
    print("NUMBER 1 SEEDS BY SEASON (1991-2025)")
    print("=" * 70)
    print(f"{'Year':<8} {'Team ID':<10} {'Team Name':<25} {'Points':<10}")
    print("-" * 70)
    
    total_points = 0
    count = 0
    
    for year, team_id, team_name, points in results:
        if team_id and team_name and points:
            print(f"{year:<8} {team_id:<10} {team_name:<25} {points:<10.2f}")
            total_points += float(points)
            count += 1
        else:
            # Future season with no data yet
            print(f"{year:<8} {'N/A':<10} {'No data yet':<25} {'N/A':<10}")
    
    print("-" * 70)
    
    if count > 0:
        avg_points = total_points / count
        print(f"\nTotal seasons with data: {count}")
        print(f"Average points by #1 seed: {avg_points:.2f}")
        print(f"Total points by all #1 seeds: {total_points:.2f}")
    
    print("\n")

def main():
    print("\nFetching #1 seeds data from database...\n")
    
    results = get_number_one_seeds()
    display_results(results)

if __name__ == "__main__":
    main()
