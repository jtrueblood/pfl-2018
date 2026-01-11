#!/usr/bin/env python3
"""
Transfer a game row from one player's table to another.
This handles cases where stats were attributed to the wrong player.
"""

import pymysql
import sys

# Database connection details
DB_CONFIG = {
    'unix_socket': '/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock',
    'user': 'root',
    'password': 'root',
    'database': 'local',
    'charset': 'utf8mb4'
}

def get_player_game_data(cursor, player_id, year, week):
    """Fetch the game data for a specific player, year, and week."""
    table_name = player_id
    week_id = f"{year}{week:02d}"
    
    query = f"SELECT * FROM `{table_name}` WHERE week_id = %s"
    cursor.execute(query, (week_id,))
    return cursor.fetchone()

def get_table_columns(cursor, table_name):
    """Get all column names for a table."""
    query = f"SHOW COLUMNS FROM `{table_name}`"
    cursor.execute(query)
    return [row['Field'] for row in cursor.fetchall()]

def delete_game_row(cursor, player_id, year, week):
    """Delete the game row from the incorrect player's table."""
    table_name = player_id
    week_id = f"{year}{week:02d}"
    
    query = f"DELETE FROM `{table_name}` WHERE week_id = %s"
    cursor.execute(query, (week_id,))
    return cursor.rowcount

def insert_game_row(cursor, player_id, game_data, columns):
    """Insert the game row into the correct player's table."""
    table_name = player_id
    
    # Build the INSERT query
    placeholders = ', '.join(['%s'] * len(columns))
    columns_str = ', '.join([f'`{col}`' for col in columns])
    query = f"INSERT INTO `{table_name}` ({columns_str}) VALUES ({placeholders})"
    
    # Get values in the correct order
    values = [game_data[col] for col in columns]
    
    cursor.execute(query, values)
    return cursor.rowcount

def transfer_player_game():
    """Main function to transfer a game from one player to another."""
    
    # Get input from user
    print("Transfer Game from Wrong Player to Correct Player")
    print("=" * 50)
    
    incorrect_player_id = input("Enter incorrect player ID (e.g., 1992SharWR): ").strip()
    correct_player_id = input("Enter correct player ID (e.g., 1992SharWR): ").strip()
    year = int(input("Enter year (e.g., 1992): ").strip())
    week = int(input("Enter week (e.g., 11): ").strip())
    
    week_id = f"{year}{week:02d}"
    
    print(f"\n{'='*50}")
    print(f"Transferring game from {incorrect_player_id} to {correct_player_id}")
    print(f"Year: {year}, Week: {week} (week_id: {week_id})")
    print(f"{'='*50}\n")
    
    try:
        # Connect to database
        connection = pymysql.connect(**DB_CONFIG, cursorclass=pymysql.cursors.DictCursor)
        cursor = connection.cursor()
        
        # Fetch the game data from incorrect player's table
        print(f"Fetching game data from {incorrect_player_id}...")
        game_data = get_player_game_data(cursor, incorrect_player_id, year, week)
        
        if not game_data:
            print(f"ERROR: No game found for {incorrect_player_id} in week {week_id}")
            return
        
        print(f"Found game: {game_data.get('game_date')} - {game_data.get('nflteam')} vs {game_data.get('nflopp')}")
        
        # Get column structure of the correct player's table
        print(f"\nChecking table structure of {correct_player_id}...")
        columns = get_table_columns(cursor, correct_player_id)
        
        # Update the player_id field in the game data
        game_data['player_id'] = correct_player_id
        
        # Show what will be transferred
        print("\nGame data to transfer:")
        print(f"  Date: {game_data.get('game_date')}")
        print(f"  Opponent: {game_data.get('nflteam')} vs {game_data.get('nflopp')}")
        print(f"  Points: {game_data.get('points')}")
        print(f"  Pass Yards: {game_data.get('pass_yds')}")
        print(f"  Rush Yards: {game_data.get('rush_yds')}")
        print(f"  Rec Yards: {game_data.get('rec_yds')}")
        print(f"  TDs: {game_data.get('td')}")
        
        # Confirm before proceeding
        confirm = input("\nProceed with transfer? (yes/no): ").strip().lower()
        
        if confirm != 'yes':
            print("Transfer cancelled.")
            return
        
        # Insert into correct player's table
        print(f"\nInserting game into {correct_player_id}...")
        inserted = insert_game_row(cursor, correct_player_id, game_data, columns)
        
        if inserted == 0:
            print("ERROR: Failed to insert game into correct player's table")
            connection.rollback()
            return
        
        print(f"Successfully inserted game into {correct_player_id}")
        
        # Delete from incorrect player's table
        print(f"\nDeleting game from {incorrect_player_id}...")
        deleted = delete_game_row(cursor, incorrect_player_id, year, week)
        
        if deleted == 0:
            print("ERROR: Failed to delete game from incorrect player's table")
            connection.rollback()
            return
        
        print(f"Successfully deleted game from {incorrect_player_id}")
        
        # Commit the transaction
        connection.commit()
        
        print("\n" + "="*50)
        print("✓ Transfer completed successfully!")
        print("="*50)
        
    except pymysql.Error as e:
        print(f"\nDatabase Error: {e}")
        if 'connection' in locals():
            connection.rollback()
    except Exception as e:
        print(f"\nError: {e}")
        if 'connection' in locals():
            connection.rollback()
    finally:
        if 'connection' in locals():
            cursor.close()
            connection.close()

if __name__ == "__main__":
    transfer_player_game()
