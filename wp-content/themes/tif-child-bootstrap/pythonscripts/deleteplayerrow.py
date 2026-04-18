#!/usr/bin/env python3
"""
Script to delete a single row from a player's table

Usage:
    python3 deleteplayerrow.py PLAYER_ID YEAR WEEK
    
Parameters:
    PLAYER_ID - Player ID (e.g., "2024AlleQB")
    YEAR      - Season year (e.g., 2024)
    WEEK      - Week number (e.g., 5)
    
Example:
    python3 deleteplayerrow.py "2024AlleQB" 2024 5
    
This will delete the row for 2024 Week 5 from the 2024AlleQB table.
"""

import sys
import mysql.connector
from mysql.connector import Error

# Database configuration for Local by Flywheel
DB_CONFIG = {
    'host': 'localhost',
    'database': 'local',
    'user': 'root',
    'password': 'root',
    'unix_socket': '/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock'
}


def delete_player_row(player_id, year, week):
    """
    Delete a single row from a player's table
    
    Parameters
    ----------
    player_id : str
        Player ID (table name)
    year : int
        Season year
    week : int
        Week number
        
    Returns
    -------
    bool
        True if successful, False otherwise
    """
    try:
        # Connect to database
        connection = mysql.connector.connect(**DB_CONFIG)
        cursor = connection.cursor()
        
        # Calculate week_id
        week_id = f"{year}{week:02d}"
        
        # First, check if the row exists
        check_query = f"SELECT * FROM `{player_id}` WHERE year = %s AND week = %s"
        cursor.execute(check_query, (year, week))
        row = cursor.fetchone()
        
        if not row:
            print(f"\n❌ ERROR: No row found for {player_id} in {year} Week {week}")
            return False
        
        # Show the row that will be deleted
        print(f"\n📋 Row to be deleted:")
        print(f"   Player: {player_id}")
        print(f"   Year: {year}, Week: {week}")
        print(f"   Week ID: {week_id}")
        print(f"   Points: {row[3] if len(row) > 3 else 'N/A'}")
        print(f"   Team: {row[4] if len(row) > 4 else 'N/A'}")
        
        # Ask for confirmation
        confirm = input(f"\n⚠️  Are you sure you want to DELETE this row? (yes/no): ").strip().lower()
        
        if confirm not in ('yes', 'y'):
            print("❌ Deletion cancelled.")
            return False
        
        # Delete the row
        delete_query = f"DELETE FROM `{player_id}` WHERE year = %s AND week = %s"
        cursor.execute(delete_query, (year, week))
        connection.commit()
        
        rows_deleted = cursor.rowcount
        
        if rows_deleted > 0:
            print(f"\n✅ Successfully deleted {rows_deleted} row(s) from {player_id}")
            print(f"   Deleted: {year} Week {week} (week_id: {week_id})")
            return True
        else:
            print(f"\n❌ No rows were deleted from {player_id}")
            return False
            
    except Error as e:
        print(f"\n❌ Database error: {e}")
        return False
        
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()


def main():
    """Main function to handle command line arguments"""
    
    if len(sys.argv) != 4:
        print("Usage: python3 deleteplayerrow.py PLAYER_ID YEAR WEEK")
        print("\nExample:")
        print('  python3 deleteplayerrow.py "2024AlleQB" 2024 5')
        sys.exit(1)
    
    player_id = sys.argv[1]
    
    try:
        year = int(sys.argv[2])
        week = int(sys.argv[3])
    except ValueError:
        print("❌ ERROR: YEAR and WEEK must be valid integers")
        sys.exit(1)
    
    # Validate inputs
    if year < 1991 or year > 2100:
        print("❌ ERROR: Year must be between 1991 and 2100")
        sys.exit(1)
    
    if week < 1 or week > 18:
        print("❌ ERROR: Week must be between 1 and 18")
        sys.exit(1)
    
    # Execute deletion
    success = delete_player_row(player_id, year, week)
    
    if success:
        sys.exit(0)
    else:
        sys.exit(1)


if __name__ == "__main__":
    main()
