#!/usr/bin/env python3
"""
Add two-point conversion records to player game statistics.
This script manages the 'twopt' column in player tables.

Usage:
  Interactive mode: python3 confirm_two_pts.py
  Command-line mode: python3 confirm_two_pts.py PLAYER_ID YEAR WEEK
  
Example: python3 confirm_two_pts.py 2015CoopWR 2015 5
"""

import mysql.connector
import sys

# Database configuration
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

def get_db_connection():
    """Get database connection using Local by Flywheel socket"""
    return mysql.connector.connect(
        host='localhost',
        user='root',
        password='root',
        database='local',
        unix_socket=MYSQL_SOCKET
    )


def check_column_exists(conn, table_name, column_name):
    """Check if a column exists in a table"""
    try:
        cursor = conn.cursor()
        cursor.execute(f"SELECT {column_name} FROM {table_name} LIMIT 1")
        cursor.fetchall()
        cursor.close()
        return True
    except mysql.connector.Error:
        return False


def get_column_position(conn, table_name, after_column):
    """Get the position of a column to insert after"""
    try:
        cursor = conn.cursor()
        cursor.execute(f"SHOW COLUMNS FROM {table_name}")
        columns = cursor.fetchall()
        cursor.close()
        for i, col in enumerate(columns):
            if col[0] == after_column:
                return i + 1
        return None
    except mysql.connector.Error:
        return None


def create_twopt_column(conn, table_name):
    """
    Create the 'twopt' column if it doesn't exist.
    Adds it at the end of the table to avoid disrupting existing column indices.
    """
    if check_column_exists(conn, table_name, 'twopt'):
        print(f"✓ Column 'twopt' already exists in {table_name}")
        return True
    
    try:
        # Add the column at the end
        alter_query = f"ALTER TABLE {table_name} ADD COLUMN twopt INT DEFAULT 0"
        cursor = conn.cursor()
        cursor.execute(alter_query)
        cursor.close()
        conn.commit()
        print(f"✓ Created column 'twopt' in {table_name}")
        return True
        
    except mysql.connector.Error as e:
        print(f"❌ Error creating column: {e}")
        return False


def auto_fill_twopt_column(conn, table_name):
    """Auto-fill the twopt column with 0 for all rows"""
    try:
        # Set all existing NULL or empty values to 0
        query = f"UPDATE {table_name} SET twopt = 0 WHERE twopt IS NULL"
        cursor = conn.cursor()
        cursor.execute(query)
        rows_affected = cursor.rowcount
        cursor.close()
        conn.commit()
        
        if rows_affected > 0:
            print(f"✓ Auto-filled {rows_affected} rows with twopt = 0")
        
        return True
        
    except mysql.connector.Error as e:
        print(f"❌ Error auto-filling column: {e}")
        return False


def add_two_point_conversion(player_id, year, week):
    """
    Add a two-point conversion for a player in a specific year/week.
    
    Steps:
    1. Identify the player table based on player_id
    2. Ensure twopt column exists
    3. Find the game record for the given year/week
    4. Increment twopt by 1
    5. Increment nflscore by 1
    6. Decrement scorediff by 1
    """
    try:
        conn = get_db_connection()
        
        print("\n" + "=" * 60)
        print("TWO-POINT CONVERSION CONFIRMATION")
        print("=" * 60)
        print(f"\nPlayer ID: {player_id}")
        print(f"Year: {year}")
        print(f"Week: {week}")
        print("-" * 60)
        
        # Verify the table exists
        table_name = player_id.lower()
        try:
            cursor = conn.cursor()
            cursor.execute(f"SELECT COUNT(*) as count FROM {table_name}")
            cursor.fetchone()
            cursor.close()
        except mysql.connector.Error:
            print(f"\n❌ Error: Table '{table_name}' not found")
            print(f"   Player ID format should be like: 2015CoopWR")
            conn.close()
            return False
        
        # Create twopt column if needed
        if not create_twopt_column(conn, table_name):
            conn.close()
            return False
        
        # Auto-fill twopt column with 0
        if not auto_fill_twopt_column(conn, table_name):
            conn.close()
            return False
        
        # Find the game record
        query = f"""
            SELECT * FROM {table_name}
            WHERE year = %s AND week = %s
        """
        cursor = conn.cursor(dictionary=True)
        cursor.execute(query, (year, week))
        game_record = cursor.fetchone()
        cursor.close()
        
        if not game_record:
            print(f"\n❌ No game record found for year {year}, week {week}")
            conn.close()
            return False
        
        print(f"\n✓ Found game record:")
        print(f"   Current nflscore: {game_record['nflscore']}")
        print(f"   Current scorediff: {game_record['scorediff']}")
        print(f"   Current twopt: {game_record['twopt']} (type: {type(game_record['twopt'])})")
        
        # Update the record
        # Increment twopt, increment nflscore, decrement scorediff
        # Use COALESCE to handle NULL values just in case
        update_query = f"""
            UPDATE {table_name}
            SET 
                twopt = COALESCE(twopt, 0) + 1,
                nflscore = nflscore + 1,
                scorediff = scorediff - 1
            WHERE year = %s AND week = %s
        """
        cursor = conn.cursor()
        cursor.execute(update_query, (year, week))
        cursor.close()
        conn.commit()
        
        # Verify the update
        cursor = conn.cursor(dictionary=True)
        cursor.execute(query, (year, week))
        updated_record = cursor.fetchone()
        cursor.close()
        
        print(f"\n✓ Update successful:")
        print(f"   New nflscore: {updated_record['nflscore']} (was {game_record['nflscore']})")
        print(f"   New scorediff: {updated_record['scorediff']} (was {game_record['scorediff']})")
        print(f"   New twopt: {updated_record['twopt']} (was {game_record['twopt']})")
        
        conn.close()
        
        print("\n" + "=" * 60)
        return True
        
    except mysql.connector.Error as e:
        print(f"\n❌ Database error: {e}")
        return False
    except Exception as e:
        print(f"\n❌ Error: {e}")
        return False


def main():
    print("\n" + "=" * 60)
    print("TWO-POINT CONVERSION MANAGER")
    print("=" * 60)
    
    # Check for command-line arguments
    if len(sys.argv) == 4:
        # Command-line mode
        try:
            player_id = sys.argv[1].strip()
            year = int(sys.argv[2])
            week = int(sys.argv[3])
            print(f"\nUsing command-line arguments:")
            print(f"  Player ID: {player_id}")
            print(f"  Year: {year}")
            print(f"  Week: {week}")
        except ValueError as e:
            print(f"\n❌ Invalid arguments: {e}")
            print("\nUsage:")
            print("  Interactive: python3 confirm_two_pts.py")
            print("  Command-line: python3 confirm_two_pts.py PLAYER_ID YEAR WEEK")
            print("\nExample: python3 confirm_two_pts.py 2015CoopWR 2015 5")
            sys.exit(1)
    elif len(sys.argv) > 1:
        # Wrong number of arguments
        print("\n❌ Invalid number of arguments")
        print("\nUsage:")
        print("  Interactive: python3 confirm_two_pts.py")
        print("  Command-line: python3 confirm_two_pts.py PLAYER_ID YEAR WEEK")
        print("\nExample: python3 confirm_two_pts.py 2015CoopWR 2015 5")
        sys.exit(1)
    else:
        # Interactive mode
        print("\nINTERACTIVE MODE")
        print("=" * 60)
        print("\nNOTE: This script will:")
        print("  1. Create 'twopt' column if it doesn't exist")
        print("  2. Auto-fill all 'twopt' values with 0")
        print("  3. Increment twopt by 1 for the specified game")
        print("  4. Increment nflscore by 1")
        print("  5. Decrement scorediff by 1")
        print("\n" + "=" * 60)
        
        try:
            player_id = input("\nEnter Player ID (e.g., 2015CoopWR): ").strip()
            year = int(input("Enter Year (e.g., 2015): ").strip())
            week = int(input("Enter Week (1-17): ").strip())
            
            if not player_id:
                print("\n❌ Player ID cannot be empty")
                sys.exit(1)
            
            if week < 1 or week > 17:
                print(f"\n❌ Invalid week: {week}")
                print("Week must be between 1 and 17")
                sys.exit(1)
                
        except ValueError as e:
            print(f"\n❌ Invalid input: {e}")
            sys.exit(1)
    
    # Execute the two-point conversion addition
    success = add_two_point_conversion(player_id, year, week)
    
    if success:
        print("\n✅ Two-point conversion confirmed successfully!")
    else:
        print("\n❌ Failed to confirm two-point conversion")
        sys.exit(1)


if __name__ == "__main__":
    main()
