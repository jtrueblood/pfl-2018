#!/usr/bin/env python3
"""
Script to add a new player to the PFL database.

Creates a new player record in wp_players table and a corresponding
player stats table by cloning the structure from a template table.
"""

import mysql.connector
from mysql.connector import Error
import json

# Database configuration
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'root',
    'database': 'local',
}

# Template table to clone structure from
TEMPLATE_TABLE = '1991AikmQB'


def get_db_connection():
    """Get database connection using Local by Flywheel socket"""
    return mysql.connector.connect(
        host=DB_CONFIG['host'],
        user=DB_CONFIG['user'],
        password=DB_CONFIG['password'],
        database=DB_CONFIG['database'],
        unix_socket=MYSQL_SOCKET
    )


def prompt_user():
    """Prompt user for player information"""
    print("=" * 60)
    print("ADD NEW PLAYER TO DATABASE")
    print("=" * 60)
    print()
    
    player_data = {}
    
    # Get player information
    player_data['playerFirst'] = input("Enter player first name: ").strip()
    while not player_data['playerFirst']:
        print("First name cannot be empty")
        player_data['playerFirst'] = input("Enter player first name: ").strip()
    
    player_data['playerLast'] = input("Enter player last name: ").strip()
    while not player_data['playerLast']:
        print("Last name cannot be empty")
        player_data['playerLast'] = input("Enter player last name: ").strip()
    
    player_data['position'] = input("Enter position (QB, RB, WR, PK): ").strip().upper()
    while player_data['position'] not in ['QB', 'RB', 'WR', 'PK']:
        print("Invalid position. Must be QB, RB, WR, or PK")
        player_data['position'] = input("Enter position (QB, RB, WR, PK): ").strip().upper()
    
    player_data['rookie'] = input("Enter rookie year (e.g., 1991): ").strip()
    while not player_data['rookie'].isdigit() or len(player_data['rookie']) != 4:
        print("Rookie year must be a 4-digit year")
        player_data['rookie'] = input("Enter rookie year (e.g., 1991): ").strip()
    
    player_data['height'] = input("Enter height (optional, e.g., 6-2): ").strip()
    player_data['weight'] = input("Enter weight (optional, e.g., 205): ").strip()
    player_data['college'] = input("Enter college (optional): ").strip()
    
    player_data['number'] = input("Enter jersey number (optional): ").strip()
    player_data['pfruri'] = input("Enter PFR URI (optional): ").strip()
    
    return player_data


def create_p_id(player_data):
    """Create player ID from rookie year + first 4 letters of last name + position"""
    first_four_last = player_data['playerLast'][:4]
    p_id = f"{player_data['rookie']}{first_four_last}{player_data['position']}"
    return p_id


def create_numberarray(rookie_year, number):
    """Create numberarray JSON from rookie year and number"""
    if not number:
        return json.dumps({})
    return json.dumps({rookie_year: number})


def clone_table(cursor, connection, template_table, new_table):
    """Clone table structure from template without data"""
    try:
        # Get the CREATE TABLE statement
        cursor.execute(f"SHOW CREATE TABLE `{template_table}`")
        create_table_result = cursor.fetchone()
        create_table_stmt = create_table_result[1]
        
        # Modify the statement for the new table
        create_table_stmt = create_table_stmt.replace(
            f"`{template_table}`",
            f"`{new_table}`"
        )
        
        # Execute the create table statement
        cursor.execute(create_table_stmt)
        connection.commit()
        
        print(f"✓ Created table `{new_table}` based on `{template_table}`")
        return True
        
    except Error as e:
        print(f"✗ Error creating table: {e}")
        return False


def insert_player(cursor, connection, p_id, player_data):
    """Insert player record into wp_players table"""
    try:
        # Create numberarray JSON
        numberarray = create_numberarray(player_data['rookie'], player_data['number'])
        
        query = """
            INSERT INTO wp_players (
                p_id, playerFirst, playerLast, position, rookie,
                height, weight, college, number, numberarray, pfruri
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        values = (
            p_id,
            player_data['playerFirst'],
            player_data['playerLast'],
            player_data['position'],
            player_data['rookie'],
            player_data['height'] or None,
            player_data['weight'] or None,
            player_data['college'] or None,
            player_data['number'] or None,
            numberarray,
            player_data['pfruri'] or None
        )
        
        cursor.execute(query, values)
        connection.commit()
        
        print(f"✓ Inserted player record into wp_players")
        return True
        
    except Error as e:
        print(f"✗ Error inserting player: {e}")
        return False


def player_exists(cursor, p_id):
    """Check if player already exists"""
    try:
        cursor.execute("SELECT p_id FROM wp_players WHERE p_id = %s", (p_id,))
        result = cursor.fetchone()
        return result is not None
    except:
        return False


def table_exists(cursor, table_name):
    """Check if table already exists"""
    try:
        cursor.execute(f"SHOW TABLES LIKE %s", (table_name,))
        result = cursor.fetchone()
        return result is not None
    except:
        return False


def main():
    """Main function"""
    connection = None
    cursor = None
    
    try:
        # Get player information from user
        player_data = prompt_user()
        
        # Create p_id
        p_id = create_p_id(player_data)
        
        print(f"\n{'='*60}")
        print(f"Player ID: {p_id}")
        print(f"{'='*60}\n")
        
        # Connect to database
        connection = get_db_connection()
        if not connection.is_connected():
            print("✗ Failed to connect to database")
            return
        
        cursor = connection.cursor()
        
        # Check if player already exists
        if player_exists(cursor, p_id):
            print(f"✗ Player with ID {p_id} already exists in wp_players")
            return
        
        # Check if table already exists
        if table_exists(cursor, p_id):
            print(f"✗ Table `{p_id}` already exists")
            return
        
        # Create new player table
        print(f"\nCreating new player table `{p_id}`...")
        if not clone_table(cursor, connection, TEMPLATE_TABLE, p_id):
            return
        
        # Insert player into wp_players
        print(f"Inserting player into wp_players table...")
        if not insert_player(cursor, connection, p_id, player_data):
            return
        
        # Success summary
        print(f"\n{'='*60}")
        print("✓ PLAYER ADDED SUCCESSFULLY")
        print(f"{'='*60}")
        print(f"\nPlayer Details:")
        print(f"  Name: {player_data['playerFirst']} {player_data['playerLast']}")
        print(f"  Position: {player_data['position']}")
        print(f"  Rookie Year: {player_data['rookie']}")
        print(f"  Player ID: {p_id}")
        print(f"  Table: `{p_id}`")
        if player_data['number']:
            print(f"  Number: {player_data['number']}")
        if player_data['height']:
            print(f"  Height: {player_data['height']}")
        if player_data['weight']:
            print(f"  Weight: {player_data['weight']}")
        if player_data['college']:
            print(f"  College: {player_data['college']}")
        print(f"  Numberarray: {create_numberarray(player_data['rookie'], player_data['number'])}")
        
    except KeyboardInterrupt:
        print("\n\n✗ Operation cancelled by user")
    except Exception as e:
        print(f"✗ Error: {e}")
    finally:
        if cursor:
            cursor.close()
        if connection and connection.is_connected():
            connection.close()


if __name__ == "__main__":
    main()
