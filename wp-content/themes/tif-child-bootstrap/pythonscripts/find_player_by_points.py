#!/usr/bin/env python3
"""
Find players who scored specific points in a given year, week, and position.
Queries MySQL wp_stathead_*POS* tables for player statistics.
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


def get_player_roster_info(player_name, year, position):
    """
    Get roster information for a player in a specific year.
    First looks up the player's PFL Player ID (p_id) from wp_players,
    then queries wp_rosters to find their PFL team(s).
    Returns tuple: (list of teams, p_id or None)
    """
    if not player_name or player_name == '':
        return ([], None)
    
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Split player name into first and last
        name_parts = player_name.strip().split()
        if len(name_parts) < 2:
            return ([], None)
        
        first_name = name_parts[0]
        last_name = ' '.join(name_parts[1:])  # Handle multi-part last names
        
        # Get p_id from wp_players table
        query = """
            SELECT p_id FROM wp_players
            WHERE playerFirst = %s AND playerLast = %s AND position = %s
            LIMIT 1
        """
        
        cursor.execute(query, (first_name, last_name, position))
        player_row = cursor.fetchone()
        
        if not player_row:
            cursor.close()
            conn.close()
            return ([], None)
        
        p_id = player_row['p_id']
        
        # Now query wp_rosters with the p_id
        query = """
            SELECT DISTINCT team FROM wp_rosters
            WHERE pid = %s AND year = %s
            ORDER BY team
        """
        
        cursor.execute(query, (p_id, year))
        rows = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        teams = [row['team'] for row in rows]
        return (teams, p_id)
        
    except Exception as e:
        # Silently fail and return empty list
        return ([], None)


def calculate_points(year, position, stats):
    """
    Calculate fantasy points based on year and position.
    Returns points as integer.
    
    Scoring rules from player_boxscore.py:
    - 1991: pass_yds//50 + rush_yds//25 + (all TDs * 2) + rec_yds//25 - pass_int
    - 1992+: pass_yds//30 + rush_yds//10 + (all TDs * 2) + rec_yds//10 - pass_int
    - Kickers: xp + (fg * 2)
    """
    # Get stats with defaults (from MySQL columns)
    pass_yds = int(stats.get('passyards', 0) or 0)
    pass_td = int(stats.get('passtd', 0) or 0)
    pass_int = int(stats.get('passint', 0) or 0)
    rush_yds = int(stats.get('rushyards', 0) or 0)
    rush_td = int(stats.get('rushtd', 0) or 0)
    rec_yds = int(stats.get('recyards', 0) or 0)
    rec_td = int(stats.get('rectd', 0) or 0)
    xp = int(stats.get('xp', 0) or 0)
    fg = int(stats.get('fg', 0) or 0)
    
    if position == 'PK':
        # Kickers: xp + (fg * 2)
        return xp + (fg * 2)
    else:
        # QB, RB, WR: position-agnostic scoring
        if year == 1991:
            # 1991 scoring rules
            pass_get = pass_yds // 50
            pass_data = max(0, pass_get)  # Can't be negative
            nfl_score = pass_data + (rush_yds // 25) + ((pass_td + rush_td + rec_td) * 2) + (rec_yds // 25) - pass_int
        else:
            # 1992+ scoring rules
            nfl_score = (pass_yds // 30) + (rush_yds // 10) + ((pass_td + rush_td + rec_td) * 2) + (rec_yds // 10) - pass_int
        
        return nfl_score


def find_players_by_points(year, week, position, expected_points):
    """
    Find all players who scored the expected points in given year/week/position.
    Queries wp_stathead_*POS* tables in MySQL.
    """
    matching_players = []
    
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Query the appropriate table based on position
        table_name = f"wp_stathead_{position}"
        query = f"""
            SELECT * FROM {table_name}
            WHERE year = %s AND week = %s
        """
        
        cursor.execute(query, (year, week))
        rows = cursor.fetchall()
        
        if not rows:
            print(f"\n⚠️  No data found in {table_name} for year {year}, week {week}")
            return []
        
        # Calculate points for each player and filter matches
        for row in rows:
            calculated_points = calculate_points(year, position, row)
            
            if calculated_points == expected_points:
                matching_players.append({
                    'player': row['playername'],
                    'playerid': row['playerid'],
                    'points': calculated_points,
                    'stats': row
                })
        
        cursor.close()
        conn.close()
        
    except mysql.connector.Error as e:
        print(f"\n❌ Database error: {e}")
        return []
    except Exception as e:
        print(f"\n❌ Error querying database: {e}")
        return []
    
    return matching_players


def main():
    print("=" * 60)
    print("PLAYER FINDER BY POINTS")
    print("=" * 60)
    print()
    
    # Check if command-line arguments were provided
    if len(sys.argv) == 5:
        # Parse command-line arguments: YEAR WEEK POSITION POINTS
        try:
            year = int(sys.argv[1])
            week = int(sys.argv[2])
            position = sys.argv[3].strip().upper()
            expected_points = int(sys.argv[4])
            print(f"Using command-line arguments: Year={year}, Week={week}, Position={position}, Points={expected_points}")
            print()
        except ValueError as e:
            print(f"\n❌ Invalid command-line arguments: {e}")
            print("Usage: python3 find_player_by_points.py YEAR WEEK POSITION POINTS")
            print("Example: python3 find_player_by_points.py 1992 5 QB 14")
            sys.exit(1)
    elif len(sys.argv) > 1:
        # Wrong number of arguments
        print("❌ Invalid number of arguments")
        print("\nUsage:")
        print("  Interactive mode: python3 find_player_by_points.py")
        print("  Command-line mode: python3 find_player_by_points.py YEAR WEEK POSITION POINTS")
        print("\nExample: python3 find_player_by_points.py 1992 5 QB 14")
        sys.exit(1)
    else:
        # No arguments - use interactive mode
        print("SCORING SYSTEM:")
        print("-" * 60)
        print("1991 Scoring (QB/RB/WR):")
        print("  - Passing yards: 1 point per 50 yards (rounded down)")
        print("  - Rushing yards: 1 point per 25 yards (rounded down)")
        print("  - Receiving yards: 1 point per 25 yards (rounded down)")
        print("  - All TDs: 2 points each")
        print("  - Interceptions: -1 point")
        print()
        print("1992+ Scoring (QB/RB/WR):")
        print("  - Passing yards: 1 point per 30 yards (rounded down)")
        print("  - Rushing yards: 1 point per 10 yards (rounded down)")
        print("  - Receiving yards: 1 point per 10 yards (rounded down)")
        print("  - All TDs: 2 points each")
        print("  - Interceptions: -1 point")
        print()
        print("Kickers (All Years):")
        print("  - XP: 1 point each")
        print("  - FG: 2 points each")
        print("=" * 60)
        print()
        
        # Get user input interactively
        try:
            year = int(input("Enter Year (e.g., 1992): ").strip())
            week = int(input("Enter Week (1-17): ").strip())
            position = input("Enter Position (QB, RB, WR, PK): ").strip().upper()
            expected_points = int(input("Enter Expected Points: ").strip())
        except ValueError as e:
            print(f"\n❌ Invalid input: {e}")
            sys.exit(1)
    
    # Validate inputs
    if position not in ['QB', 'RB', 'WR', 'PK']:
        print(f"\n❌ Invalid position: {position}")
        print("Valid positions: QB, RB, WR, PK")
        sys.exit(1)
    
    if week < 1 or week > 17:
        print(f"\n❌ Invalid week: {week}")
        print("Week must be between 1 and 17")
        sys.exit(1)
    
    print(f"\n🔍 Searching for {position} players in {year} Week {week} with {expected_points} points...")
    print("-" * 60)
    
    # Find matching players
    matches = find_players_by_points(year, week, position, expected_points)
    
    if matches:
        print(f"\n✅ Found {len(matches)} player(s) with {expected_points} points:\n")
        for i, match in enumerate(matches, 1):
            print(f"{i}. {match['player']} - {match['points']} points")
            
            # Get PFL roster information and p_id
            player_name = match['player']
            teams, p_id = get_player_roster_info(player_name, year, position)
            
            # Show PFL Player ID
            if p_id:
                print(f"   PFL Player ID: {p_id}")
            else:
                print(f"   PFL Player ID: (not found in wp_players)")
            
            # Show PFL roster information
            if teams:
                if len(teams) == 1:
                    print(f"   PFL Team: {teams[0]}")
                else:
                    print(f"   PFL Team: {', '.join(teams)} (multiple teams)")
            else:
                print(f"   PFL Team: Not Rostered")
            
            # Show relevant stats based on position
            stats = match['stats']
            if position == 'PK':
                xp = stats.get('xp', 0)
                fg = stats.get('fg', 0)
                print(f"   Stats: XP: {xp}, FG: {fg}")
            elif position == 'QB':
                pass_yds = stats.get('passyards', 0)
                pass_td = stats.get('passtd', 0)
                pass_int = stats.get('passint', 0)
                rush_yds = stats.get('rushyards', 0)
                rush_td = stats.get('rushtd', 0)
                print(f"   Stats: Pass: {pass_yds} yds, {pass_td} TD, {pass_int} INT")
                if rush_yds > 0 or rush_td > 0:
                    print(f"          Rush: {rush_yds} yds, {rush_td} TD")
            elif position in ['RB', 'WR']:
                rush_yds = stats.get('rushyards', 0)
                rush_td = stats.get('rushtd', 0)
                rec_yds = stats.get('recyards', 0)
                rec_td = stats.get('rectd', 0)
                
                stats_str = []
                if rush_yds > 0 or rush_td > 0:
                    stats_str.append(f"Rush: {rush_yds} yds, {rush_td} TD")
                if rec_yds > 0 or rec_td > 0:
                    stats_str.append(f"Rec: {rec_yds} yds, {rec_td} TD")
                
                if stats_str:
                    print(f"   Stats: {' | '.join(stats_str)}")
            print()
    else:
        print(f"\n❌ No {position} players found with {expected_points} points in {year} Week {week}")
    
    print("=" * 60)


if __name__ == "__main__":
    main()
