#!/usr/bin/env python3
"""
Script to find 'TTT' values in the nflteam column across all player tables
(tables ending in QB, RB, WR, or PK)
"""

import mysql.connector
from mysql.connector import Error

# Database configuration
DB_CONFIG = {
    'host': '127.0.0.1',
    'port': 10014,
    'user': 'root',
    'password': 'root',
    'database': 'local',
    'unix_socket': '/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock'
}

def get_player_tables(cursor):
    """Get all tables ending in QB, RB, WR, or PK"""
    suffixes = ['QB', 'RB', 'WR', 'PK']
    tables = []
    
    for suffix in suffixes:
        cursor.execute(f"SHOW TABLES LIKE '%{suffix}'")
        results = cursor.fetchall()
        tables.extend([row[0] for row in results])
    
    return tables

def check_required_columns(cursor, table_name):
    """Check if table has the required columns (nflteam, game_location, nflopp)"""
    required_columns = ['nflteam', 'game_location', 'nflopp']
    
    cursor.execute(f"SHOW COLUMNS FROM `{table_name}`")
    existing_columns = [col[0] for col in cursor.fetchall()]
    
    missing_columns = [col for col in required_columns if col not in existing_columns]
    
    return missing_columns

def add_missing_columns(cursor, table_name, missing_columns):
    """Add missing columns to the table"""
    try:
        for column in missing_columns:
            alter_query = f"ALTER TABLE `{table_name}` ADD COLUMN `{column}` VARCHAR(3) NULL"
            cursor.execute(alter_query)
            print(f"      ✓ Added column '{column}' to table '{table_name}'")
        return True
    except Error as e:
        print(f"      ❌ Error adding columns to '{table_name}': {e}")
        return False

def check_table_for_ttt(cursor, table_name):
    """Check a specific table for 'TTT' in the nflteam column"""
    try:
        # First, check if the nflteam column exists
        cursor.execute(f"SHOW COLUMNS FROM `{table_name}` LIKE 'nflteam'")
        if not cursor.fetchone():
            # Skip silently - we already warned about missing columns
            return [], []
        
        # Get column names first
        cursor.execute(f"SHOW COLUMNS FROM `{table_name}`")
        columns = [col[0] for col in cursor.fetchall()]
        
        # Query for 'TTT' in nflteam column
        query = f"SELECT * FROM `{table_name}` WHERE nflteam = 'TTT'"
        cursor.execute(query)
        results = cursor.fetchall()
        
        return results, columns
    except Error as e:
        print(f"  ❌ Error checking table '{table_name}': {e}")
        return [], []

def main():
    connection = None
    cursor = None
    try:
        # Connect to database
        print("Connecting to database...")
        connection = mysql.connector.connect(**DB_CONFIG)
        cursor = connection.cursor()
        
        print(f"✓ Connected to database '{DB_CONFIG['database']}'\n")
        
        # Get all player tables
        print("Finding player tables (ending in QB, RB, WR, or PK)...")
        player_tables = get_player_tables(cursor)
        
        if not player_tables:
            print("No player tables found!")
            return
        
        print(f"✓ Found {len(player_tables)} player tables\n")
        print("=" * 80)
        
        # First pass: Check for missing columns across all tables
        print("\nChecking for missing columns...")
        tables_with_missing_columns = {}
        
        for table in player_tables:
            missing_columns = check_required_columns(cursor, table)
            if missing_columns:
                tables_with_missing_columns[table] = missing_columns
        
        if tables_with_missing_columns:
            print(f"\n⚠️  Found {len(tables_with_missing_columns)} table(s) with missing columns:")
            for table, missing in list(tables_with_missing_columns.items())[:5]:  # Show first 5
                print(f"  - {table}: missing {', '.join(missing)}")
            if len(tables_with_missing_columns) > 5:
                print(f"  ... and {len(tables_with_missing_columns) - 5} more")
            
            # Prompt user
            response = input(f"\nDo you want to add missing columns to these {len(tables_with_missing_columns)} table(s)? (yes/no): ").strip().lower()
            
            if response in ['yes', 'y']:
                print("\nAdding missing columns...")
                for table, missing in tables_with_missing_columns.items():
                    print(f"  Processing {table}...")
                    success = add_missing_columns(cursor, table, missing)
                    if success:
                        connection.commit()
                print("\n✓ Column additions completed")
            else:
                print("\n⚠️  Skipping column additions. Tables without required columns will be skipped.")
        else:
            print("✓ All tables have the required columns\n")
        
        print("\n" + "=" * 80)
        
        # Check each table for 'TTT'
        total_ttt_count = 0
        tables_with_ttt = []
        
        for table in player_tables:
            print(f"\nChecking table: {table}")
            results, columns = check_table_for_ttt(cursor, table)
            
            if results:
                total_ttt_count += len(results)
                tables_with_ttt.append(table)
                print(f"  ✓ Found {len(results)} row(s) with 'TTT' in nflteam column")
                
                # Display results with player name and ID
                for row in results:
                    row_dict = dict(zip(columns, row))
                    
                    # Extract player ID from the playerid column
                    player_id = row_dict.get('playerid', 'N/A')
                    
                    # The table name itself is the player ID (e.g., 2024McCaQB)
                    player_name = table
                    
                    print(f"    Player Table: {player_name}, Player ID: {player_id}")
            else:
                print(f"  ✓ No 'TTT' values found")
        
        # Summary
        print("\n" + "=" * 80)
        print("\nSUMMARY:")
        print(f"  Total tables checked: {len(player_tables)}")
        print(f"  Tables with 'TTT': {len(tables_with_ttt)}")
        print(f"  Total rows with 'TTT': {total_ttt_count}")
        
        if tables_with_ttt:
            print(f"\n  Tables containing 'TTT':")
            for table in tables_with_ttt:
                print(f"    - {table}")
        
    except Error as e:
        print(f"Database error: {e}")
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
            print("\n✓ Database connection closed")

if __name__ == "__main__":
    main()
