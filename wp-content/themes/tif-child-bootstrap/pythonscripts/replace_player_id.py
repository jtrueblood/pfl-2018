#!/usr/bin/env python3
"""
Script to replace a player ID across the entire database and in files.
This will search all tables and columns for the old player ID and replace it with the new one.
It will also search through files in the theme directory and replace occurrences.
"""

import mysql.connector
import sys
import os
import re
from pathlib import Path

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
        ('wp_player_pvqs', 'playerid'),
        ('wp_player_of_week', 'playerid'),
        ('wp_awards', 'pid'),
        ('wp_rosters', 'pid'),
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
        cursor.fetchall()  # Clear the result
        cursor.execute(f"SELECT COUNT(*) FROM `{old_player_id}`")
        count = cursor.fetchone()[0]
        if count > 0:
            occurrences.append((old_player_id, 'playerid', count))
            print(f"Found player table: {old_player_id} with {count} records")
    except mysql.connector.Error:
        # Player table doesn't exist
        pass
    
    # Check team tables for all player position columns
    cursor.execute("SHOW TABLES LIKE 'wp_team_%'")
    team_tables = [row[0] for row in cursor.fetchall()]
    
    # Team tables have QB1, RB1, WR1, PK1, QB2, RB2, WR2, PK2 columns
    player_columns = ['QB1', 'RB1', 'WR1', 'PK1', 'QB2', 'RB2', 'WR2', 'PK2']
    
    for table in team_tables:
        for col in player_columns:
            query = f"SELECT COUNT(*) FROM {table} WHERE {col} = %s"
            try:
                cursor.execute(query, (old_player_id,))
                count = cursor.fetchone()[0]
                if count > 0:
                    occurrences.append((table, col, count))
                    print(f"Found {count} occurrence(s) in {table}.{col}")
            except mysql.connector.Error:
                pass
    
    # Check wp_trades table (players stored in comma-separated text fields)
    for col in ['players1', 'players2', 'protection1', 'protection2']:
        query = f"SELECT COUNT(*) FROM wp_trades WHERE {col} LIKE %s"
        try:
            cursor.execute(query, (f'%{old_player_id}%',))
            count = cursor.fetchone()[0]
            if count > 0:
                occurrences.append(('wp_trades', col, count))
                print(f"Found {count} occurrence(s) in wp_trades.{col} (text field)")
        except mysql.connector.Error:
            pass
    
    cursor.close()
    conn.close()
    
    if not occurrences:
        print(f"No occurrences of '{old_player_id}' found in the database.")
    
    print("-" * 60)
    return occurrences

def find_player_id_in_files(old_player_id, search_path):
    """
    Search for occurrences of the player ID in files.
    Returns a list of (file_path, line_number, line_content) tuples.
    """
    print(f"\nSearching for player ID in files: {old_player_id}\n")
    print("-" * 60)
    
    occurrences = []
    
    # File extensions to search
    extensions = ['.php', '.txt', '.json', '.js', '.css', '.html', '.md']
    
    # Directories to skip
    skip_dirs = {'node_modules', '.git', 'vendor', 'cache', 'mfl-weekly-rosters', 
                 'mfl-weekly-gamelogs', 'nfl-injuries', 'pfr-gamelogs', 'data'}
    
    search_path = Path(search_path)
    file_count = 0
    matched_files = 0
    
    for file_path in search_path.rglob('*'):
        # Skip directories we don't want to search
        if any(skip in file_path.parts for skip in skip_dirs):
            continue
            
        # Only search text files
        if file_path.is_file() and file_path.suffix in extensions:
            file_count += 1
            try:
                with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                    for line_num, line in enumerate(f, 1):
                        if old_player_id in line:
                            occurrences.append((str(file_path), line_num, line.strip()))
                            if file_path not in [occ[0] for occ in occurrences[:-1]]:
                                matched_files += 1
            except Exception as e:
                # Skip files that can't be read
                pass
    
    if occurrences:
        # Group by file
        files_with_occurrences = {}
        for file_path, line_num, line_content in occurrences:
            if file_path not in files_with_occurrences:
                files_with_occurrences[file_path] = []
            files_with_occurrences[file_path].append((line_num, line_content))
        
        print(f"Found {len(occurrences)} occurrence(s) in {len(files_with_occurrences)} file(s):\n")
        for file_path, lines in files_with_occurrences.items():
            print(f"  {file_path}: {len(lines)} occurrence(s)")
            # Show first 3 occurrences
            for line_num, line_content in lines[:3]:
                preview = line_content[:80] + '...' if len(line_content) > 80 else line_content
                print(f"    Line {line_num}: {preview}")
            if len(lines) > 3:
                print(f"    ... and {len(lines) - 3} more")
    else:
        print(f"No occurrences of '{old_player_id}' found in {file_count} files.")
    
    print("-" * 60)
    return occurrences

def replace_player_id_in_files(old_player_id, new_player_id, file_occurrences):
    """
    Replace the old player ID with the new one in all found files.
    """
    print(f"\nReplacing '{old_player_id}' with '{new_player_id}' in files...\n")
    print("-" * 60)
    
    # Group occurrences by file
    files_to_update = {}
    for file_path, line_num, line_content in file_occurrences:
        if file_path not in files_to_update:
            files_to_update[file_path] = []
        files_to_update[file_path].append((line_num, line_content))
    
    total_files_updated = 0
    total_replacements = 0
    
    for file_path, occurrences in files_to_update.items():
        try:
            # Read the entire file
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            
            # Count occurrences before replacement
            count_before = content.count(old_player_id)
            
            # Replace all occurrences
            updated_content = content.replace(old_player_id, new_player_id)
            
            # Write back to file
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(updated_content)
            
            total_files_updated += 1
            total_replacements += count_before
            print(f"✓ Updated {count_before} occurrence(s) in {file_path}")
            
        except Exception as e:
            print(f"⚠ Could not update {file_path}: {e}")
    
    print("-" * 60)
    print(f"\nFiles updated: {total_files_updated}")
    print(f"Total replacements: {total_replacements}")
    print("✓ File replacements completed!\n")

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
        elif table == 'wp_trades' and column in ['players1', 'players2', 'protection1', 'protection2']:
            # Special handling for text fields in wp_trades - use REPLACE function
            query = f"UPDATE {table} SET {column} = REPLACE({column}, %s, %s) WHERE {column} LIKE %s"
            cursor.execute(query, (old_player_id, new_player_id, f'%{old_player_id}%'))
            updated = cursor.rowcount
            print(f"✓ Updated {updated} records in {table}.{column} (text field)")
            total_updated += updated
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
    
    print("\n" + "=" * 60)
    print("PHASE 1: DATABASE SEARCH")
    print("=" * 60)
    
    # Find all database occurrences
    db_occurrences = find_player_id_occurrences(old_player_id)
    
    print("\n" + "=" * 60)
    print("PHASE 2: FILE SYSTEM SEARCH")
    print("=" * 60)
    
    # Find all file occurrences
    # Get the theme directory (parent of pythonscripts directory)
    script_dir = os.path.dirname(os.path.abspath(__file__))
    theme_dir = os.path.dirname(script_dir)
    
    file_occurrences = find_player_id_in_files(old_player_id, theme_dir)
    
    # Summary
    print("\n" + "=" * 60)
    print("SUMMARY")
    print("=" * 60)
    db_count = sum(count for _, _, count in db_occurrences) if db_occurrences else 0
    file_count = len(file_occurrences)
    print(f"Database occurrences: {db_count}")
    print(f"File occurrences: {file_count}")
    print(f"Total: {db_count + file_count}")
    
    if not db_occurrences and not file_occurrences:
        print("\nNo occurrences found. Nothing to replace.")
        sys.exit(0)
    
    # Confirm before replacing
    print("\n" + "=" * 60)
    confirm = input("\nProceed with replacement in BOTH database and files? (y/n): ").lower()
    
    if confirm != 'y':
        print("Aborted.")
        sys.exit(0)
    
    print("\n" + "=" * 60)
    print("EXECUTING REPLACEMENTS")
    print("=" * 60)
    
    # Perform database replacements
    if db_occurrences:
        replace_player_id(old_player_id, new_player_id, db_occurrences)
    
    # Perform file replacements
    if file_occurrences:
        replace_player_id_in_files(old_player_id, new_player_id, file_occurrences)
    
    print("\n" + "=" * 60)
    print("ALL REPLACEMENTS COMPLETED!")
    print("=" * 60)
    print(f"\nSuccessfully replaced all occurrences of '{old_player_id}' with '{new_player_id}'.")
    print("Database and file system updates complete.\n")

if __name__ == "__main__":
    main()
