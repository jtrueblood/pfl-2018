#!/usr/bin/env python3
"""
Script to replace a player ID across the entire database.
This will search all tables and columns for the old player ID and replace it with the new one.
"""

import mysql.connector
import sys

# Database connection settings
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'root',
    'database': 'local',
    'unix_socket': '/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock'
}

def get_connection():
    """Create and return a database connection."""
    return mysql.connector.connect(**DB_CONFIG)

def find_player_id_occurrences(old_player_id):
    """
    Search the entire database for occurrences of the player ID.
    Returns a list of (table_name, column_name, count) tuples.
    """
    conn = get_connection()
    cursor = conn.cursor()
    
    occurrences = []
    
    # Define key tables and columns to check
    key_checks = [
        ('wp_players', 'p_id'),
        ('wp_drafts', 'playerid'),
        ('wp_protections', 'playerid'),
        ('wp_playoffs', 'playerid'),
        ('wp_season_leaders', 'playerid'),
    ]
    
    print(f"\nSearching for player ID: {old_player_id}\n")
    print("-" * 60)
    
    # Check key tables
    for table, column in key_checks:
        query = f"SELECT COUNT(*) FROM {table} WHERE {column} = %s"
        try:
            cursor.execute(query, (old_player_id,))
            count = cursor.fetchone()[0]
            if count > 0:
                occurrences.append((table, column, count))
                print(f"Found {count} occurrence(s) in {table}.{column}")
        except mysql.connector.Error as e:
            # Table might not exist, skip it
            pass
    
    # Check for player-specific table (e.g., 1991CarnPK)
    try:
        cursor.execute(f"DESCRIBE `{old_player_id}`")
        cursor.execute(f"SELECT COUNT(*) FROM `{old_player_id}`")
        count = cursor.fetchone()[0]
        if count > 0:
            occurrences.append((old_player_id, 'playerid', count))
            print(f"Found player table: {old_player_id} with {count} records")
    except mysql.connector.Error:
        # Player table doesn't exist
        pass
    
    # Check team tables for pk1 and pk2 columns
    cursor.execute("SHOW TABLES LIKE 'wp_team_%'")
    team_tables = [row[0] for row in cursor.fetchall()]
    
    for table in team_tables:
        for col in ['pk1', 'pk2']:
            query = f"SELECT COUNT(*) FROM {table} WHERE {col} = %s"
            try:
                cursor.execute(query, (old_player_id,))
                count = cursor.fetchone()[0]
                if count > 0:
                    occurrences.append((table, col, count))
                    print(f"Found {count} occurrence(s) in {table}.{col}")
            except mysql.connector.Error:
                pass
    
    cursor.close()
    conn.close()
    
    if not occurrences:
        print(f"No occurrences of '{old_player_id}' found in the database.")
    
    print("-" * 60)
    return occurrences

def replace_player_id(old_player_id, new_player_id, occurrences):
    """
    Replace the old player ID with the new one in all found locations.
    """
    conn = get_connection()
    cursor = conn.cursor()
    
    total_updated = 0
    
    print(f"\nReplacing '{old_player_id}' with '{new_player_id}'...\n")
    print("-" * 60)
    
    for table, column, count in occurrences:
        # Special handling for player-specific table
        if table == old_player_id and column == 'playerid':
            # Update the player table itself
            query = f"UPDATE `{table}` SET {column} = %s"
            cursor.execute(query, (new_player_id,))
            updated = cursor.rowcount
            print(f"✓ Updated {updated} records in player table {table}.{column}")
            total_updated += updated
            
            # Rename the table if it's a player-specific table
            try:
                cursor.execute(f"RENAME TABLE `{old_player_id}` TO `{new_player_id}`")
                print(f"✓ Renamed table '{old_player_id}' to '{new_player_id}'")
            except mysql.connector.Error as e:
                print(f"⚠ Could not rename table: {e}")
        else:
            # Update regular tables
            query = f"UPDATE {table} SET {column} = %s WHERE {column} = %s"
            cursor.execute(query, (new_player_id, old_player_id))
            updated = cursor.rowcount
            print(f"✓ Updated {updated} records in {table}.{column}")
            total_updated += updated
    
    conn.commit()
    cursor.close()
    conn.close()
    
    print("-" * 60)
    print(f"\nTotal records updated: {total_updated}")
    print("✓ All replacements completed successfully!\n")

def main():
    print("=" * 60)
    print("Player ID Replacement Tool")
    print("=" * 60)
    
    # Get user input
    if len(sys.argv) == 3:
        old_player_id = sys.argv[1]
        new_player_id = sys.argv[2]
    else:
        old_player_id = input("\nEnter the OLD player ID to replace: ").strip()
        new_player_id = input("Enter the NEW player ID: ").strip()
    
    if not old_player_id or not new_player_id:
        print("Error: Both player IDs must be provided.")
        sys.exit(1)
    
    # Validate format (should be like 1991CarnPK)
    if len(old_player_id) != 10 or len(new_player_id) != 10:
        print("Warning: Player IDs are typically 10 characters (e.g., 1991CarnPK)")
        confirm = input("Continue anyway? (y/n): ").lower()
        if confirm != 'y':
            print("Aborted.")
            sys.exit(0)
    
    # Find all occurrences
    occurrences = find_player_id_occurrences(old_player_id)
    
    if not occurrences:
        sys.exit(0)
    
    # Confirm before replacing
    print(f"\nTotal occurrences found: {sum(count for _, _, count in occurrences)}")
    confirm = input("\nProceed with replacement? (y/n): ").lower()
    
    if confirm == 'y':
        replace_player_id(old_player_id, new_player_id, occurrences)
    else:
        print("Aborted.")
        sys.exit(0)

if __name__ == "__main__":
    main()
