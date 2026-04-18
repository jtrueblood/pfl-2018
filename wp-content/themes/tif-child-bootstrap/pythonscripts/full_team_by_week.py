#!/usr/bin/env python3
"""
Get expected scores for all players on a team's 1991 roster for a specific week.

Usage:
    python3 full_team_by_week.py WEEK TEAM_ID
    
Parameters:
    WEEK    - Week number (1-14)
    TEAM_ID - Team abbreviation (ETS, PEP, WRZ, RBS, BUL, CMN, SNR, TSG)
    
Example:
    python3 full_team_by_week.py 4 ETS
    
This will show all players rostered by ETS in 1991 and their expected scores for Week 4.
"""

import mysql.connector
import sys

# Database configuration
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

# Valid teams for 1991
VALID_TEAMS = ['ETS', 'PEP', 'WRZ', 'RBS', 'BUL', 'CMN', 'SNR', 'TSG']

TEAM_NAMES = {
    'ETS': 'Euro-Trashers',
    'PEP': 'Peppers',
    'WRZ': 'Space Warriorz',
    'RBS': 'Red Barons',
    'BUL': 'Raging Bulls',
    'CMN': 'C-Men',
    'SNR': 'Sixty Niners',
    'TSG': 'Tsongas'
}


def get_db_connection():
    """Get database connection using Local by Flywheel socket"""
    return mysql.connector.connect(
        host='localhost',
        user='root',
        password='root',
        database='local',
        unix_socket=MYSQL_SOCKET
    )


def get_team_roster_1991(team_id):
    """
    Get all players rostered for a team in 1991.
    Returns dict with positions as keys and list of player IDs as values.
    """
    roster = {'QB': [], 'RB': [], 'WR': [], 'PK': []}
    
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        query = """
            SELECT r.pid, p.playerFirst, p.playerLast, p.position
            FROM wp_rosters r
            JOIN wp_players p ON r.pid = p.p_id
            WHERE r.year = 1991 AND r.team = %s
            ORDER BY p.position, p.playerLast
        """
        
        cursor.execute(query, (team_id,))
        rows = cursor.fetchall()
        
        for row in rows:
            pos = row['position']
            if pos in roster:
                roster[pos].append({
                    'pid': row['pid'],
                    'first': row['playerFirst'],
                    'last': row['playerLast']
                })
        
        cursor.close()
        conn.close()
        
    except mysql.connector.Error as e:
        print(f"❌ Database error getting roster: {e}")
    
    return roster


def get_player_name(pid):
    """Get player's full name from wp_players table"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        query = "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s"
        cursor.execute(query, (pid,))
        row = cursor.fetchone()
        
        cursor.close()
        conn.close()
        
        if row:
            return f"{row['playerFirst']} {row['playerLast']}"
        return pid
        
    except:
        return pid


def calculate_points_1991(position, stats):
    """
    Calculate fantasy points using 1991 scoring rules.
    
    1991 Scoring (QB/RB/WR):
      - Passing yards: 1 point per 50 yards (rounded down)
      - Rushing yards: 1 point per 25 yards (rounded down)
      - Receiving yards: 1 point per 25 yards (rounded down)
      - All TDs: 2 points each
      - Interceptions: -1 point
    
    Kickers:
      - XP: 1 point each
      - FG: 2 points each
    """
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
        return xp + (fg * 2)
    else:
        # 1991 scoring rules
        pass_pts = max(0, pass_yds // 50)
        rush_pts = max(0, rush_yds // 25)
        rec_pts = max(0, rec_yds // 25)
        return pass_pts + rush_pts + ((pass_td + rush_td + rec_td) * 2) + rec_pts - pass_int


def get_player_expected_score(player_name, week, position):
    """
    Get expected score for a player from wp_stathead_* tables.
    Returns dict with score and stats, or None if not found.
    """
    conn = None
    cursor = None
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        table_name = f"wp_stathead_{position}"
        query = f"""
            SELECT * FROM {table_name}
            WHERE year = 1991 AND week = %s AND playername = %s
        """
        
        cursor.execute(query, (week, player_name))
        row = cursor.fetchone()
        
        if row:
            points = calculate_points_1991(position, row)
            return {
                'points': points,
                'stats': row,
                'found': True
            }
        
        return None
        
    except mysql.connector.Error as e:
        return None
    except Exception as e:
        return None
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()


def get_player_pfl_score(pid, week):
    """
    Get the PFL recorded score for a player from their player table.
    Returns dict with points and scorediff, or None if not found.
    """
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        week_id = f"1991{week:02d}"
        query = f"SELECT points, nflscore, scorediff FROM `{pid}` WHERE week_id = %s"
        
        cursor.execute(query, (week_id,))
        row = cursor.fetchone()
        
        cursor.close()
        conn.close()
        
        if row:
            return {
                'pfl_points': row['points'],
                'nfl_score': row['nflscore'],
                'scorediff': row['scorediff']
            }
        
        return None
        
    except:
        return None


def get_team_lineup(team_id, week):
    """
    Get the lineup (starters) for a team in a specific week.
    Returns set of player IDs who were in the lineup.
    """
    lineup = set()
    
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        table_name = f"wp_team_{team_id}"
        query = f"SELECT QB1, RB1, WR1, PK1 FROM `{table_name}` WHERE season = 1991 AND week = %s"
        
        cursor.execute(query, (week,))
        row = cursor.fetchone()
        
        cursor.close()
        conn.close()
        
        if row:
            for pos in ['QB1', 'RB1', 'WR1', 'PK1']:
                if row[pos] and row[pos] not in ['None', '', 'null']:
                    lineup.add(row[pos])
        
    except mysql.connector.Error as e:
        print(f"❌ Error getting lineup: {e}")
    
    return lineup


def display_roster_scores(team_id, week):
    """Display all roster players with their expected and actual scores"""
    
    print(f"\n{'='*70}")
    print(f"  {TEAM_NAMES.get(team_id, team_id)} - 1991 Week {week} Roster Scores")
    print(f"{'='*70}\n")
    
    roster = get_team_roster_1991(team_id)
    lineup = get_team_lineup(team_id, week)
    
    if not any(roster.values()):
        print(f"❌ No players found on {team_id} roster for 1991")
        return
    
    total_expected = 0
    total_pfl = 0
    
    for position in ['QB', 'RB', 'WR', 'PK']:
        players = roster[position]
        if not players:
            continue
            
        print(f"\n{position}s:")
        print(f"{'-'*80}")
        print(f"{'Player':<28} {'Expected':<10} {'PFL':<10} {'Diff':<10} {'vs Start':<10} {'Status'}")
        print(f"{'-'*80}")
        
        # First pass: find starter's expected score for this position
        starter_expected = None
        for player in players:
            pid = player['pid']
            if pid in lineup:
                player_name = f"{player['first']} {player['last']}"
                expected_data = get_player_expected_score(player_name, week, position)
                if expected_data:
                    starter_expected = expected_data['points']
                break
        
        for player in players:
            pid = player['pid']
            in_lineup = '✓ ' if pid in lineup else '  '
            player_name = f"{player['first']} {player['last']}"
            display_name = f"{in_lineup}{player_name}"
            
            # Get expected score from stathead
            expected_data = get_player_expected_score(player_name, week, position)
            
            # Get PFL recorded score
            pfl_data = get_player_pfl_score(pid, week)
            
            expected_pts = expected_data['points'] if expected_data else '--'
            pfl_pts = pfl_data['pfl_points'] if pfl_data else '--'
            scorediff = pfl_data['scorediff'] if pfl_data else '--'
            
            # Calculate vs starter difference
            vs_starter = ''
            if pid in lineup:
                vs_starter = '--'  # This is the starter
            elif expected_data and starter_expected is not None:
                diff_vs_start = expected_data['points'] - starter_expected
                if diff_vs_start > 0:
                    vs_starter = f"\033[92m+{diff_vs_start}\033[0m"  # Green for better
                elif diff_vs_start < 0:
                    vs_starter = f"\033[91m{diff_vs_start}\033[0m"  # Red for worse
                else:
                    vs_starter = '0'
            
            # Determine status
            status = ''
            if expected_data and pfl_data:
                if expected_pts == pfl_data['pfl_points']:
                    status = '✓'
                else:
                    status = f"⚠️  (PFL={pfl_pts})"
                total_expected += expected_pts
                total_pfl += pfl_data['pfl_points'] if pfl_data['pfl_points'] else 0
            elif not expected_data:
                status = '(no NFL data)'
            elif not pfl_data:
                status = '(not played)'
            
            # Format scorediff display
            diff_display = ''
            if scorediff not in [None, '', '--']:
                if scorediff == 0:
                    diff_display = f"({scorediff})"
                else:
                    diff_display = f"\033[91m({scorediff})\033[0m"  # Red color
            
            print(f"{display_name:<28} {str(expected_pts):<10} {str(pfl_pts):<10} {diff_display:<10} {vs_starter:<10} {status}")
    
    print(f"\n{'='*70}")
    print(f"  Summary: Expected Total = {total_expected}, PFL Total = {total_pfl}")
    if total_expected != total_pfl:
        print(f"  ⚠️  Difference: {total_pfl - total_expected}")
    print(f"{'='*70}\n")


def main():
    """Main function"""
    
    if len(sys.argv) == 3:
        try:
            week = int(sys.argv[1])
            team_id = sys.argv[2].upper()
        except ValueError:
            print("❌ Week must be a number")
            sys.exit(1)
    else:
        print("=" * 60)
        print("1991 FULL TEAM ROSTER SCORES BY WEEK")
        print("=" * 60)
        print()
        print(f"Valid teams: {', '.join(VALID_TEAMS)}")
        print()
        
        try:
            week = int(input("Enter Week (1-14): ").strip())
            team_id = input("Enter Team ID (e.g., ETS): ").strip().upper()
        except ValueError:
            print("❌ Invalid input")
            sys.exit(1)
    
    # Validate inputs
    if week < 1 or week > 14:
        print(f"❌ Invalid week: {week}")
        print("Week must be between 1 and 14 for 1991 season")
        sys.exit(1)
    
    if team_id not in VALID_TEAMS:
        print(f"❌ Invalid team: {team_id}")
        print(f"Valid teams: {', '.join(VALID_TEAMS)}")
        sys.exit(1)
    
    # Display roster scores
    display_roster_scores(team_id, week)


if __name__ == "__main__":
    main()
