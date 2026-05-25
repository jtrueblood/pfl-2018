#!/usr/bin/env python3
"""
Script to find players who were drafted/rostered before 2025 but scored their first PFL points in 2025.

This identifies "late bloomers" like Drake Maye who were on rosters but didn't play until 2025.
"""

import mysql.connector
from mysql.connector import Error
import glob
import os
import sys

# Database Configuration
DB_CONFIG = {
    'host': 'localhost',
    'database': 'local',
    'user': 'root',
    'password': 'root'
}

# Specific socket for Local by Flywheel site (matching build-single-player-mfl.py)
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

def get_db_connection():
    """Establish database connection using Local by Flywheel socket."""
    try:
        # Connect using unix socket
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


def get_player_first_points_year(cursor, p_id):
    """
    Get the year a player first scored points in PFL.
    
    Parameters:
        cursor: Database cursor
        p_id (str): Player ID
        
    Returns:
        int or None: Year of first points, or None if no points
    """
    try:
        # Query the player's individual table for their first game with points > 0
        query = f"""
            SELECT year 
            FROM `{p_id}` 
            WHERE points > 0 
            ORDER BY year ASC, week ASC 
            LIMIT 1
        """
        cursor.execute(query)
        result = cursor.fetchone()
        
        if result:
            return int(result[0])
        return None
    except Error as e:
        # Table might not exist for some players
        return None


def get_player_draft_year(p_id):
    """
    Extract the draft year from player ID.
    Player IDs are formatted as: YYYYNamePO (e.g., 2024MayeQB)
    
    Parameters:
        p_id (str): Player ID
        
    Returns:
        int or None: Draft year
    """
    if len(p_id) >= 4 and p_id[:4].isdigit():
        return int(p_id[:4])
    return None


def find_late_bloomers():
    """
    Find all players who were drafted/rostered before 2025 but scored their first points in 2025.
    """
    connection = get_db_connection()
    if not connection:
        sys.exit(1)
    
    try:
        cursor = connection.cursor()
        
        # Get all players from wp_players table
        print("Fetching all players from database...")
        cursor.execute("SELECT p_id, playerFirst, playerLast FROM wp_players")
        all_players = cursor.fetchall()
        
        print(f"Found {len(all_players)} total players in database\n")
        print("Analyzing players...")
        print("="*80)
        
        late_bloomers = []
        
        for p_id, first_name, last_name in all_players:
            # Get draft year from player ID
            draft_year = get_player_draft_year(p_id)
            
            # Skip if drafted in 2025 or later (or can't determine draft year)
            if not draft_year or draft_year >= 2025:
                continue
            
            # Get year of first points
            first_points_year = get_player_first_points_year(cursor, p_id)
            
            # Check if first points were in 2025
            if first_points_year == 2025:
                player_name = f"{first_name} {last_name}"
                late_bloomers.append({
                    'name': player_name,
                    'p_id': p_id,
                    'draft_year': draft_year,
                    'first_points_year': first_points_year
                })
                print(f"✓ {player_name:30} | ID: {p_id:15} | Drafted: {draft_year} | First Points: {first_points_year}")
        
        print("="*80)
        print(f"\nFound {len(late_bloomers)} players who fit the criteria:\n")
        
        # Display results in a clean list
        if late_bloomers:
            print("PLAYERS DRAFTED BEFORE 2025 WHO SCORED FIRST POINTS IN 2025:")
            print("-"*80)
            for i, player in enumerate(late_bloomers, 1):
                print(f"{i:2}. {player['name']} (Drafted {player['draft_year']})")
        else:
            print("No players found matching the criteria.")
        
        cursor.close()
        connection.close()
        
        return late_bloomers
        
    except Error as e:
        print(f"Error during analysis: {e}")
        if connection:
            connection.close()
        sys.exit(1)


if __name__ == "__main__":
    find_late_bloomers()
