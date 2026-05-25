#!/usr/bin/env python3
"""
Script to find the best player for each letter of the alphabet (A-Z)
based on total points scored across all seasons.
"""

import mysql.connector
import sys
import string

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

def get_player_full_name(conn, playerid):
    """
    Get the full name of a player from wp_players table.
    Returns the full name or None if not found.
    """
    cursor = conn.cursor()
    
    query = "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s"
    cursor.execute(query, (playerid,))
    result = cursor.fetchone()
    cursor.close()
    
    if result:
        first_name, last_name = result
        return f"{first_name} {last_name}"
    return None

def get_best_player_by_lastname(conn, letter):
    """
    Get the best player whose last name starts with the given letter.
    Returns a tuple: (playerid, total_points, full_name) or None if no player found.
    """
    cursor = conn.cursor()
    
    # Query wp_season_leaders and sum all points for each player
    # Player IDs typically follow format like "2018AlleQB" where "Alle" is part of last name
    query = f"""
        SELECT playerid, SUM(points) as total_points
        FROM wp_season_leaders
        WHERE playerid REGEXP '^[0-9]{{4}}{letter}'
        GROUP BY playerid
        ORDER BY total_points DESC
        LIMIT 1
    """
    
    cursor.execute(query)
    result = cursor.fetchone()
    cursor.close()
    
    if result:
        playerid, total_points = result
        full_name = get_player_full_name(conn, playerid)
        return (playerid, total_points, full_name, 'last')
    return None

def get_best_player_by_firstname(conn, letter):
    """
    Get the best player whose first name starts with the given letter.
    Returns a tuple: (playerid, total_points, full_name) or None if no player found.
    """
    cursor = conn.cursor()
    
    # First, get all players whose first name starts with the letter
    query = f"""
        SELECT p.p_id, p.playerFirst, p.playerLast
        FROM wp_players p
        WHERE p.playerFirst LIKE '{letter}%'
    """
    
    cursor.execute(query)
    matching_players = cursor.fetchall()
    
    if not matching_players:
        cursor.close()
        return None
    
    # Now find which of these players has the most total points
    best_player = None
    best_points = 0
    
    for p_id, first_name, last_name in matching_players:
        points_query = """
            SELECT SUM(points) as total_points
            FROM wp_season_leaders
            WHERE playerid = %s
        """
        cursor.execute(points_query, (p_id,))
        result = cursor.fetchone()
        
        if result and result[0] and result[0] > best_points:
            best_points = result[0]
            best_player = (p_id, result[0], f"{first_name} {last_name}", 'first')
    
    cursor.close()
    return best_player


def main():
    """Main function to find the best player for each letter A-Z."""
    print("\n" + "="*100)
    print("BEST PLAYER FOR EACH LETTER OF THE ALPHABET (A-Z)")
    print("="*100 + "\n")
    
    conn = get_connection()
    
    print("Querying database for best player for each letter...\n")
    
    # Collect results for all letters
    results = []
    
    for letter in string.ascii_uppercase:
        # First try to find by last name
        player = get_best_player_by_lastname(conn, letter)
        
        # If no player found by last name, try first name
        if not player:
            player = get_best_player_by_firstname(conn, letter)
        
        if player:
            playerid, total_points, full_name, name_type = player
            results.append((letter, playerid, full_name, total_points, name_type))
        else:
            results.append((letter, None, None, None, None))
    
    # Display the table
    print("-"*100)
    print(f"{'Letter':<8} {'Player ID':<20} {'Full Name':<35} {'Total Points':<15} {'Match'}")
    print("-"*100)
    
    for letter, playerid, full_name, total_points, name_type in results:
        if playerid:
            match_type = "Last Name" if name_type == 'last' else "First Name"
            print(f"{letter:<8} {playerid:<20} {full_name:<35} {total_points:<15.1f} {match_type}")
        else:
            print(f"{letter:<8} {'N/A':<20} {'N/A':<35} {'N/A':<15} {'N/A'}")
    
    print("-"*100)
    
    # Find the overall best player
    valid_results = [(letter, playerid, name, points) for letter, playerid, name, points, _ in results if playerid]
    if valid_results:
        best = max(valid_results, key=lambda x: x[3])
        best_letter, best_playerid, best_name, best_points = best
        
        print(f"\n{'='*100}")
        print(f"OVERALL BEST PLAYER:")
        print(f"  Letter: {best_letter}")
        print(f"  Player ID: {best_playerid}")
        print(f"  Full Name: {best_name}")
        print(f"  Total Points: {best_points:.1f}")
        print(f"{'='*100}\n")
    
    conn.close()

if __name__ == "__main__":
    main()
