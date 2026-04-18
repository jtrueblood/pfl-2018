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
            # Floor all yardage calculations at 0 (negative yards don't penalize)
            pass_pts = max(0, pass_yds // 50)
            rush_pts = max(0, rush_yds // 25)
            rec_pts = max(0, rec_yds // 25)
            nfl_score = pass_pts + rush_pts + ((pass_td + rush_td + rec_td) * 2) + rec_pts - pass_int
        else:
            # 1992+ scoring rules
            # Floor all yardage calculations at 0 (negative yards don't penalize)
            pass_pts = max(0, pass_yds // 30)
            rush_pts = max(0, rush_yds // 10)
            rec_pts = max(0, rec_yds // 10)
            nfl_score = pass_pts + rush_pts + ((pass_td + rush_td + rec_td) * 2) + rec_pts - pass_int
        
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
        # Support both single values and comma-separated arrays
        try:
            year = int(sys.argv[1])
            position = sys.argv[3].strip().upper()
            
            # Parse weeks - support single week or comma-separated list
            week_input = sys.argv[2].strip()
            if ',' in week_input:
                weeks = [int(w.strip()) for w in week_input.split(',')]
            else:
                weeks = [int(week_input)]
            
            # Parse points - support single value or comma-separated list
            points_input = sys.argv[4].strip()
            if ',' in points_input:
                expected_points_list = [int(p.strip()) for p in points_input.split(',')]
            else:
                expected_points_list = [int(points_input)]
            
            # Validate that weeks and points arrays have the same length
            if len(weeks) != len(expected_points_list):
                print(f"\n❌ Error: Number of weeks ({len(weeks)}) must match number of points ({len(expected_points_list)})")
                print("Example: python3 find_player_by_points.py 1993 \"8,9,10\" QB \"4,5,15\"")
                sys.exit(1)
            
            if len(weeks) > 1:
                print(f"Using command-line arguments: Year={year}, Weeks={weeks}, Position={position}, Points={expected_points_list}")
            else:
                print(f"Using command-line arguments: Year={year}, Week={weeks[0]}, Position={position}, Points={expected_points_list[0]}")
            print()
        except ValueError as e:
            print(f"\n❌ Invalid command-line arguments: {e}")
            print("Usage: python3 find_player_by_points.py YEAR WEEK POSITION POINTS")
            print("Single week: python3 find_player_by_points.py 1992 5 QB 14")
            print("Multiple weeks: python3 find_player_by_points.py 1993 \"8,9,10\" QB \"4,5,15\"")
            sys.exit(1)
    elif len(sys.argv) > 1:
        # Wrong number of arguments
        print("❌ Invalid number of arguments")
        print("\nUsage:")
        print("  Interactive mode: python3 find_player_by_points.py")
        print("  Command-line mode (single): python3 find_player_by_points.py YEAR WEEK POSITION POINTS")
        print("  Command-line mode (multi): python3 find_player_by_points.py YEAR \"WEEK1,WEEK2,...\" POSITION \"POINTS1,POINTS2,...\"")
        print("\nExamples:")
        print("  python3 find_player_by_points.py 1992 5 QB 14")
        print("  python3 find_player_by_points.py 1993 \"8,9,10\" QB \"4,5,15\"")
        sys.exit(1)
    else:
        # No arguments - use interactive mode (single week only)
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
            # Set up single-week arrays for consistency
            weeks = [week]
            expected_points_list = [expected_points]
        except ValueError as e:
            print(f"\n❌ Invalid input: {e}")
            sys.exit(1)
    
    # Validate inputs
    if position not in ['QB', 'RB', 'WR', 'PK']:
        print(f"\n❌ Invalid position: {position}")
        print("Valid positions: QB, RB, WR, PK")
        sys.exit(1)
    
    # Validate all weeks
    for week in weeks:
        if week < 1 or week > 17:
            print(f"\n❌ Invalid week: {week}")
            print("Week must be between 1 and 17")
            sys.exit(1)
    
    # Handle single week search
    if len(weeks) == 1:
        week = weeks[0]
        expected_points = expected_points_list[0]
        print(f"\n🔍 Searching for {position} players in {year} Week {week} with {expected_points} points...")
        print("-" * 60)
        
        # Find matching players
        matches = find_players_by_points(year, week, position, expected_points)
    else:
        # Handle multiple weeks search - find players who match ALL week/point combinations
        print(f"\n🔍 Searching for {position} players in {year} matching multiple weeks...")
        print("-" * 60)
        
        # Dictionary to track player matches: {player_name: {week: match_data}}
        player_matches = {}
        
        for i, (week, expected_points) in enumerate(zip(weeks, expected_points_list)):
            print(f"Week {week}: Looking for {expected_points} points...")
            week_matches = find_players_by_points(year, week, position, expected_points)
            
            for match in week_matches:
                player_name = match['player']
                if player_name not in player_matches:
                    player_matches[player_name] = {}
                player_matches[player_name][week] = match
        
        # Filter players who have matches for ALL specified weeks
        matches = []
        for player_name, week_data in player_matches.items():
            if len(week_data) == len(weeks):  # Player matched all weeks
                # Create a combined match entry
                # Get roster info once
                teams, p_id = get_player_roster_info(player_name, year, position)
                
                matches.append({
                    'player': player_name,
                    'p_id': p_id,
                    'teams': teams,
                    'week_data': week_data
                })
        
        print()
    
    # Display results
    if len(weeks) == 1:
        # Single week display
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
    else:
        # Multiple weeks display
        if matches:
            weeks_str = ', '.join([str(w) for w in weeks])
            points_str = ', '.join([str(p) for p in expected_points_list])
            print(f"\n✅ Found {len(matches)} player(s) matching all criteria:\n")
            print(f"   (Weeks: {weeks_str} with Points: {points_str} respectively)\n")
            
            for i, match in enumerate(matches, 1):
                print(f"{i}. {match['player']}")
                
                # Show PFL Player ID
                if match['p_id']:
                    print(f"   PFL Player ID: {match['p_id']}")
                else:
                    print(f"   PFL Player ID: (not found in wp_players)")
                
                # Show PFL roster information
                if match['teams']:
                    if len(match['teams']) == 1:
                        print(f"   PFL Team: {match['teams'][0]}")
                    else:
                        print(f"   PFL Team: {', '.join(match['teams'])} (multiple teams)")
                else:
                    print(f"   PFL Team: Not Rostered")
                
                print(f"\n   Week-by-week breakdown:")
                for week in sorted(match['week_data'].keys()):
                    week_match = match['week_data'][week]
                    stats = week_match['stats']
                    points = week_match['points']
                    
                    # Show stats based on position
                    if position == 'PK':
                        xp = stats.get('xp', 0)
                        fg = stats.get('fg', 0)
                        print(f"   Week {week}: {points} pts (XP: {xp}, FG: {fg})")
                    elif position == 'QB':
                        pass_yds = stats.get('passyards', 0)
                        pass_td = stats.get('passtd', 0)
                        pass_int = stats.get('passint', 0)
                        rush_yds = stats.get('rushyards', 0)
                        rush_td = stats.get('rushtd', 0)
                        stats_str = f"Pass: {pass_yds} yds, {pass_td} TD, {pass_int} INT"
                        if rush_yds > 0 or rush_td > 0:
                            stats_str += f" | Rush: {rush_yds} yds, {rush_td} TD"
                        print(f"   Week {week}: {points} pts ({stats_str})")
                    elif position in ['RB', 'WR']:
                        rush_yds = stats.get('rushyards', 0)
                        rush_td = stats.get('rushtd', 0)
                        rec_yds = stats.get('recyards', 0)
                        rec_td = stats.get('rectd', 0)
                        
                        stats_parts = []
                        if rush_yds > 0 or rush_td > 0:
                            stats_parts.append(f"Rush: {rush_yds} yds, {rush_td} TD")
                        if rec_yds > 0 or rec_td > 0:
                            stats_parts.append(f"Rec: {rec_yds} yds, {rec_td} TD")
                        
                        stats_str = ' | '.join(stats_parts) if stats_parts else "No stats"
                        print(f"   Week {week}: {points} pts ({stats_str})")
                print()
        else:
            weeks_str = ', '.join([str(w) for w in weeks])
            points_str = ', '.join([str(p) for p in expected_points_list])
            print(f"\n❌ No {position} players found matching all criteria")
            print(f"   (Weeks: {weeks_str} with Points: {points_str} respectively)")
    
    print("=" * 60)


if __name__ == "__main__":
    main()
