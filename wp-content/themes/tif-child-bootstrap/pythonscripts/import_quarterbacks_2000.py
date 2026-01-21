#!/usr/bin/env python3
"""
Import Quarterback CSV data from Pro Football Reference into wp_stathead_QB MySQL table.
"""

import csv
import mysql.connector
import sys
import os
from datetime import datetime

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

def parse_date(date_str):
    """Parse date string from CSV into MySQL date format"""
    if not date_str or date_str.strip() == '':
        return None
    
    try:
        return datetime.strptime(date_str, '%Y-%m-%d').date()
    except ValueError:
        return None

def extract_year_from_filename(filepath):
    """Extract year from filename like '1991-QB.csv'"""
    basename = os.path.basename(filepath)
    parts = basename.split('-')
    if len(parts) >= 2 and parts[0].isdigit():
        return int(parts[0])
    return None

def extract_player_id(pos_id_str):
    """Extract player ID from last column which contains player ID"""
    if not pos_id_str or pos_id_str.strip() == '' or pos_id_str == '-9999':
        return ''
    return pos_id_str.strip()

def safe_int(value, default=0):
    """Safely convert a value to int, return default if conversion fails"""
    if not value or value.strip() == '':
        return default
    try:
        return int(float(value))
    except ValueError:
        return default

def import_csv_to_db(csv_file_path):
    """Import CSV file into wp_stathead_QB table"""
    
    if not os.path.exists(csv_file_path):
        print(f"❌ Error: File not found: {csv_file_path}")
        return False
    
    # Extract year from filename
    year = extract_year_from_filename(csv_file_path)
    if not year:
        print(f"❌ Error: Could not extract year from filename: {csv_file_path}")
        print("Expected format: YYYY-QB.csv (e.g., 1991-QB.csv)")
        return False
    
    print(f"\n📄 Reading CSV file: {csv_file_path}")
    print(f"📅 Year: {year}")
    
    # Read CSV file
    try:
        with open(csv_file_path, 'r') as f:
            # Skip first row (category headers like "Passing", "Rushing", etc.)
            next(f)
            
            # Read header line to get column names
            header_line = f.readline().strip()
            headers = [h.strip() for h in header_line.split(',')]
            
            # Now read data rows
            reader = csv.reader(f)
            
            rows_to_insert = []
            skipped_rows = 0
            
            for values in reader:
                if len(values) < len(headers):
                    skipped_rows += 1
                    continue
                
                # Create dict from headers and values
                row = dict(zip(headers, values))
                
                # Skip empty rows or header-like rows
                if not row.get('Player') or row.get('Player') == 'Player':
                    skipped_rows += 1
                    continue
                
                # Extract data
                player_name = row.get('Player', '').strip()
                week = row.get('Week', '').strip()
                date_str = row.get('Date', '').strip()
                # Team/Opp/HomeAway: use direct column indices due to blank header column
                team = values[12].strip() if len(values) > 12 else ''
                location_marker = values[13].strip() if len(values) > 13 else ''
                opp = values[14].strip() if len(values) > 14 else ''
                homeaway = '@' if location_marker == '@' else 'vs'
                
                # QB CSV column positions (0-based indexing):
                # Team(12), Location(13), Opp(14), Result(15)
                # Passing: Cmp(16), Att(17), Inc(18), Cmp%(19), Yds(20), TD(21), Int(22)
                # Rushing: Att(35), Yds(36), Y/A(37), TD(38)
                
                # Use column indices for stats
                pass_yds = safe_int(values[20]) if len(values) > 20 else 0
                pass_td = safe_int(values[21]) if len(values) > 21 else 0
                pass_int = safe_int(values[22]) if len(values) > 22 else 0
                
                rush_yds = safe_int(values[36]) if len(values) > 36 else 0
                rush_td = safe_int(values[38]) if len(values) > 38 else 0
                
                player_id_raw = row.get('-additional', '').strip()
                
                # Validate required fields
                if not player_name or not week:
                    skipped_rows += 1
                    continue
                
                try:
                    week_int = int(week)
                except ValueError:
                    skipped_rows += 1
                    continue
                
                # Parse date
                game_date = parse_date(date_str)
                
                # Extract player ID
                player_id = extract_player_id(player_id_raw)
                
                # Create row for database
                db_row = {
                    'playerid': player_id,
                    'playername': player_name,
                    'year': year,
                    'week': week_int,
                    'game_date': game_date,
                    'team': team,
                    'versusteam': opp,
                    'homeaway': homeaway,
                    'passyards': pass_yds,
                    'passtd': pass_td,
                    'passint': pass_int,
                    'rushyards': rush_yds,
                    'rushtd': rush_td,
                    'recyards': 0,
                    'rectd': 0,
                    'xp': 0,
                    'fg': 0,
                    'twopt': 0
                }
                
                rows_to_insert.append(db_row)
            
            print(f"✅ Parsed {len(rows_to_insert)} valid rows from CSV")
            if skipped_rows > 0:
                print(f"ℹ️  Skipped {skipped_rows} invalid/empty rows")
            
            if len(rows_to_insert) == 0:
                print("❌ No valid data to import")
                return False
            
            # Show sample data
            print(f"\n📋 Sample data (first 3 rows):")
            for i, row in enumerate(rows_to_insert[:3], 1):
                print(f"\n  Row {i}:")
                print(f"    Player: {row['playername']} (ID: {row['playerid']})")
                print(f"    Year: {row['year']}, Week: {row['week']}, Date: {row['game_date']}")
                print(f"    Team: {row['team']} {row['homeaway']} {row['versusteam']}")
                print(f"    Pass: {row['passyards']} yds, {row['passtd']} TD, {row['passint']} INT")
                print(f"    Rush: {row['rushyards']} yds, {row['rushtd']} TD")
            
            # Confirm import
            response = input(f"\n⚠️  Import {len(rows_to_insert)} rows into wp_stathead_QB? (yes/no): ").strip().lower()
            if response not in ['yes', 'y']:
                print("Import cancelled")
                return False
            
            # Connect to database and insert
            conn = get_db_connection()
            cursor = conn.cursor()
            
            insert_query = """
                INSERT INTO wp_stathead_QB 
                (playerid, playername, year, week, game_date, team, versusteam, homeaway,
                 passyards, passtd, passint, rushyards, rushtd, recyards, rectd, xp, fg, twopt)
                VALUES (%(playerid)s, %(playername)s, %(year)s, %(week)s, %(game_date)s,
                        %(team)s, %(versusteam)s, %(homeaway)s,
                        %(passyards)s, %(passtd)s, %(passint)s, %(rushyards)s, %(rushtd)s,
                        %(recyards)s, %(rectd)s, %(xp)s, %(fg)s, %(twopt)s)
            """
            
            try:
                cursor.executemany(insert_query, rows_to_insert)
                conn.commit()
                print(f"\n✅ Successfully imported {cursor.rowcount} rows!")
                return True
            except Exception as e:
                conn.rollback()
                print(f"\n❌ Error inserting data: {e}")
                return False
            finally:
                cursor.close()
                conn.close()
                
    except Exception as e:
        print(f"\n❌ Error reading CSV file: {e}")
        return False

def main():
    if len(sys.argv) != 2:
        print("Usage: python3 import_quarterbacks.py <path_to_csv>")
        print("Example: python3 import_quarterbacks.py ../pfr-raw-season/1991-QB.csv")
        sys.exit(1)
    
    csv_file = sys.argv[1]
    
    print("=" * 60)
    print("QUARTERBACK CSV IMPORTER")
    print("=" * 60)
    
    # Import the file
    success = import_csv_to_db(csv_file)
    
    if success:
        print("\n✅ Import completed successfully!")
    else:
        print("\n❌ Import failed")
    
    print("=" * 60)

if __name__ == "__main__":
    main()
