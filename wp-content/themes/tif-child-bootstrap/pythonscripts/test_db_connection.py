#!/usr/bin/env python3
"""
Test script to verify database connection and query wp_players table
"""

import mysql.connector
import glob
import os

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'database': 'local',
    'user': 'root',
    'password': 'root'
}

def test_connection():
    """Test database connection and show sample players"""
    try:
        # Find Local by Flywheel socket
        home = os.path.expanduser('~')
        socket_pattern = os.path.join(home, 'Library', 'Application Support', 'Local', 'run', '*', 'mysql', 'mysqld.sock')
        sockets = glob.glob(socket_pattern)
        
        if not sockets:
            print("No MySQL socket found. Is Local by Flywheel running?")
            return False
        
        print(f"Found socket: {sockets[0]}\n")
        
        # Connect using unix socket
        connection = mysql.connector.connect(
            host=DB_CONFIG['host'],
            user=DB_CONFIG['user'],
            password=DB_CONFIG['password'],
            database=DB_CONFIG['database'],
            unix_socket=sockets[0]
        )
        
        if connection.is_connected():
            print("✓ Successfully connected to database\n")
            
            cursor = connection.cursor()
            
            # Show table structure
            print("Table structure (wp_players):")
            print("-" * 60)
            cursor.execute("DESCRIBE wp_players")
            for row in cursor.fetchall():
                print(f"  {row[0]}: {row[1]}")
            
            # Count players
            print("\n" + "=" * 60)
            cursor.execute("SELECT COUNT(*) FROM wp_players")
            count = cursor.fetchone()[0]
            print(f"Total players in database: {count}")
            
            # Show first 10 players
            if count > 0:
                print("\nSample players:")
                print("-" * 60)
                cursor.execute("SELECT p_id, playerFirst, playerLast FROM wp_players LIMIT 10")
                for row in cursor.fetchall():
                    print(f"  ID: {row[0]}, Name: {row[1]} {row[2]}")
                
                # Test search for Josh Allen
                print("\n" + "=" * 60)
                print("Testing search for 'Josh Allen':")
                cursor.execute("SELECT p_id, playerFirst, playerLast FROM wp_players WHERE playerFirst = %s AND playerLast = %s", ('Josh', 'Allen'))
                result = cursor.fetchone()
                if result:
                    print(f"  ✓ Found: ID={result[0]}, Name={result[1]} {result[2]}")
                else:
                    print("  ✗ Not found in database")
            
            cursor.close()
            connection.close()
            print("\n" + "=" * 60)
            return True
            
    except Exception as e:
        print(f"✗ Error: {e}")
        return False

if __name__ == "__main__":
    print("=" * 60)
    print("Database Connection Test")
    print("=" * 60 + "\n")
    test_connection()
