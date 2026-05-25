#!/usr/bin/env python3
"""
Migrate twopt column to the end of all player tables.
This script:
1. Identifies all player tables
2. For tables with twopt column, moves it to the end
3. Verifies the column order after migration
"""

import mysql.connector
import sys

MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

def get_db_connection():
    """Get database connection"""
    return mysql.connector.connect(
        host='localhost',
        user='root',
        password='root',
        database='local',
        unix_socket=MYSQL_SOCKET
    )

def get_all_player_tables(conn):
    """Get list of all player tables"""
    cursor = conn.cursor()
    cursor.execute("SHOW TABLES LIKE '%'")
    tables = [row[0] for row in cursor.fetchall()]
    cursor.close()
    
    # Filter for player tables (exclude wp_ prefixed tables)
    player_tables = [t for t in tables if not t.startswith('wp_')]
    return player_tables

def has_twopt_column(conn, table_name):
    """Check if table has twopt column"""
    try:
        cursor = conn.cursor()
        cursor.execute(f"SELECT twopt FROM {table_name} LIMIT 1")
        cursor.fetchall()
        cursor.close()
        return True
    except mysql.connector.Error:
        return False

def get_column_order(conn, table_name):
    """Get the current column order"""
    cursor = conn.cursor()
    cursor.execute(f"SHOW COLUMNS FROM {table_name}")
    columns = [row[0] for row in cursor.fetchall()]
    cursor.close()
    return columns

def migrate_twopt_column(table_name):
    """Move twopt column to the end of the table"""
    try:
        conn = get_db_connection()
        columns = get_column_order(conn, table_name)
        
        if 'twopt' not in columns:
            conn.close()
            print(f"⚠️  Table {table_name} doesn't have twopt column")
            return False
        
        # Check if twopt is already at the end
        if columns[-1] == 'twopt':
            conn.close()
            print(f"✓ Table {table_name}: twopt already at the end")
            return True
        
        # Need to migrate: drop and recreate twopt at the end
        cursor = conn.cursor()
        cursor.execute(f"ALTER TABLE {table_name} DROP COLUMN twopt")
        cursor.close()
        
        cursor = conn.cursor()
        cursor.execute(f"ALTER TABLE {table_name} ADD COLUMN twopt INT DEFAULT 0")
        cursor.close()
        conn.commit()
        
        # Verify
        new_columns = get_column_order(conn, table_name)
        conn.close()
        
        if new_columns[-1] == 'twopt':
            print(f"✓ Migrated {table_name}: twopt moved to end (index {len(new_columns) - 1})")
            return True
        else:
            print(f"❌ Failed to migrate {table_name}: twopt not at end")
            return False
            
    except mysql.connector.Error as e:
        print(f"❌ Error migrating {table_name}: {e}")
        return False

def main():
    print("\n" + "=" * 60)
    print("TWOPT COLUMN MIGRATION")
    print("=" * 60)
    
    conn = get_db_connection()
    
    # Get all player tables
    print("\nFinding player tables...")
    player_tables = get_all_player_tables(conn)
    print(f"Found {len(player_tables)} player tables")
    
    # Find tables with twopt
    print("\nChecking for twopt column...")
    tables_with_twopt = []
    for table in player_tables:
        if has_twopt_column(conn, table):
            tables_with_twopt.append(table)
    
    print(f"Found {len(tables_with_twopt)} tables with twopt column")
    
    if len(tables_with_twopt) == 0:
        print("No tables with twopt column found. Nothing to migrate.")
        conn.close()
        return
    
    # Migrate each table
    print("\n" + "-" * 60)
    print("MIGRATING TABLES")
    print("-" * 60)
    
    success_count = 0
    for table in tables_with_twopt:
        if migrate_twopt_column(table):
            success_count += 1
    
    print("\n" + "=" * 60)
    print(f"MIGRATION COMPLETE: {success_count}/{len(tables_with_twopt)} successful")
    print("=" * 60)
    
    # Sample verification
    if tables_with_twopt:
        print(f"\nVerifying first table: {tables_with_twopt[0]}")
        conn = get_db_connection()
        columns = get_column_order(conn, tables_with_twopt[0])
        for i, col in enumerate(columns[-5:]):
            idx = len(columns) - 5 + i
            print(f"  Index {idx}: {col}")
        conn.close()

if __name__ == "__main__":
    main()
