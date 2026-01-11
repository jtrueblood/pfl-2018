#!/usr/bin/env python3
"""
Verify PFL scores from historical PDF newsletters against database

This script:
1. Reads a PDF newsletter (e.g., 2000_01.pdf)
2. Extracts game scores and individual player scores
3. Compares against database values
4. Identifies discrepancies and verifies team totals
"""

import sys
import re
import mysql.connector
from mysql.connector import Error
import PyPDF2
from pathlib import Path

# Database configuration
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

DB_CONFIG = {
    'host': 'localhost',
    'database': 'local',
    'user': 'root',
    'password': 'root',
    'unix_socket': MYSQL_SOCKET
}

# Team abbreviation mapping (from PDF to database)
TEAM_ABBREV = {
    'WRZ': 'Warriorz',
    'ETS': 'Euro-Trashers',
    'PEP': 'Peppers',
    'BST': 'Bustas',
    'SON': 'Sons',
    'SNR': 'Sixty Niners',
    'BUL': 'Bulls',
    'TSG': 'Tsongas',
    'CMN': 'C-Men',
    'PHR': 'Pherns'
}

def extract_pdf_text(pdf_path):
    """Extract text from PDF file"""
    try:
        with open(pdf_path, 'rb') as file:
            reader = PyPDF2.PdfReader(file)
            text = ''
            for page in reader.pages:
                text += page.extract_text()
            return text
    except Exception as e:
        print(f"Error reading PDF: {e}")
        return None

def parse_game_scores(text):
    """
    Parse game scores from PDF text
    
    Returns list of games with format:
    {
        'team1': 'Euro-Trashers', 'score1': 40, 'team2': 'Sons', 'score2': 29,
        'players1': [{'name': 'B.Johnson', 'score': 9}, ...],
        'players2': [{'name': 'Brunell', 'score': 12}, ...]
    }
    """
    games = []
    
    # First find the SCORES section
    scores_start = text.find('SCORES:')
    if scores_start == -1:
        return games
    
    standings_start = text.find('STANDINGS:')
    if standings_start == -1:
        standings_start = len(text)
    
    # Extract just the scores section
    scores_text = text[scores_start:standings_start]
    
    # Pattern: Team1 Score1, Team2 Score2 (on its own line, followed by player line)
    # Team names can have spaces and hyphens (Euro-Trashers, Sixty Niners, etc)
    # Looking for pattern like "Euro-Trashers 40, Sons 29"
    lines = scores_text.split('\n')
    
    i = 0
    while i < len(lines):
        line = lines[i].strip()
        
        # Match game score line: "Team1 Score1, Team2 Score2"
        # Team names are multi-word, score is 2 digits typically
        # Handle optional asterisks or special characters at start (e.g. "**Warriorz 52")
        game_match = re.match(r'^[\*\s]*([A-Z][\w\s\-]+?)\s+(\d+),\s+([A-Z][\w\s\-]+?)\s+(\d+)\s*$', line)
        
        if game_match:
            team1_name = game_match.group(1).strip()
            score1 = int(game_match.group(2))
            team2_name = game_match.group(3).strip()
            score2 = int(game_match.group(4))
            
            # Next line(s) should have player scores
            # Player scores can be on one line: "Name1 X, Name2 Y / Name3 Z, Name4 W"
            # OR split across two lines:
            # Line 1: "Name1 X, Name2 Y / "
            # Line 2: "Name3 Z, Name4 W."
            if i + 1 < len(lines):
                player_line1 = lines[i + 1].strip()
                
                # Check if '/' is present
                if '/' in player_line1:
                    # Check if team2 players are on the same line or next line
                    team1_players_str, team2_players_str = player_line1.split('/', 1)
                    
                    # If team2 part is empty or very short, check next line
                    if len(team2_players_str.strip()) < 5 and i + 2 < len(lines):
                        team2_players_str = lines[i + 2].strip()
                    
                    team1_players = parse_player_scores(team1_players_str)
                    team2_players = parse_player_scores(team2_players_str)
                    
                    games.append({
                        'team1': team1_name,
                        'score1': score1,
                        'team2': team2_name,
                        'score2': score2,
                        'players1': team1_players,
                        'players2': team2_players
                    })
        
        i += 1
    
    return games

def parse_player_scores(player_str):
    """Parse player scores from string like 'Warner 17, James 20, Moss 8'"""
    players = []
    
    # Pattern: Name Score
    # Name can be:
    # - B.Johnson (initial + dot + name)
    # - S.Davis
    # - T.D. (initials with dots)
    # - Mrs.Robinson (prefix + dot + name)
    # - Simple names like Warner, James, etc
    # Match pattern: word characters, dots, hyphens, followed by space and number
    pattern = r'([A-Z][\w\.\-]*(?:\.[A-Z][\w\.\-]*)?)\s+(\d+)'
    
    matches = re.finditer(pattern, player_str)
    for match in matches:
        name = match.group(1)
        # Remove trailing dots
        name = name.rstrip('.')
        players.append({
            'name': name,
            'score': int(match.group(2))
        })
    
    return players

def find_player_in_db(cursor, last_name, year):
    """
    Find player ID in database by last name
    Returns list of possible matches (in case of multiple players with same last name)
    """
    # Try exact match first
    query = """
        SELECT p_id, playerFirst, playerLast, position 
        FROM wp_players 
        WHERE playerLast = %s
        AND CAST(SUBSTRING(p_id, 1, 4) AS UNSIGNED) <= %s
        ORDER BY p_id DESC
    """
    
    cursor.execute(query, (last_name, year))
    results = cursor.fetchall()
    
    return results

def get_player_score_from_db(cursor, player_id, year, week):
    """Get player's score from database for specific week"""
    try:
        query = f"SELECT points FROM `{player_id}` WHERE year = %s AND week = %s"
        cursor.execute(query, (year, week))
        result = cursor.fetchone()
        return result[0] if result else None
    except Error:
        return None

def verify_pdf_scores(pdf_path):
    """Main verification function"""
    
    # Extract filename to get year and week
    filename = Path(pdf_path).stem  # e.g., "2000_01"
    match = re.match(r'(\d{4})_(\d{2})', filename)
    
    if not match:
        print(f"Error: Filename must be in format YYYY_WW.pdf (e.g., 2000_01.pdf)")
        return
    
    year = int(match.group(1))
    week = int(match.group(2))
    
    print("=" * 80)
    print(f"VERIFYING SCORES: Year {year}, Week {week}")
    print(f"Source: {pdf_path}")
    print("=" * 80)
    print()
    
    # Extract PDF text
    text = extract_pdf_text(pdf_path)
    if not text:
        return
    
    # Parse games
    games = parse_game_scores(text)
    
    if not games:
        print("No games found in PDF. Check PDF format.")
        return
    
    print(f"Found {len(games)} games in PDF\n")
    
    # Connect to database
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        cursor = connection.cursor()
        
        # Process each game
        for i, game in enumerate(games, 1):
            print(f"{'=' * 80}")
            print(f"GAME {i}: {game['team1']} {game['score1']} vs {game['team2']} {game['score2']}")
            print(f"{'=' * 80}")
            
            # Verify Team 1 players
            verify_team_players(cursor, game['team1'], game['players1'], game['score1'], year, week)
            
            print()
            
            # Verify Team 2 players
            verify_team_players(cursor, game['team2'], game['players2'], game['score2'], year, week)
            
            print()
        
        cursor.close()
        connection.close()
        
    except Error as e:
        print(f"Database error: {e}")

def verify_team_players(cursor, team_name, players, team_score, year, week):
    """Verify all players for a team"""
    
    print(f"\n{team_name} (Team Score: {team_score})")
    print("-" * 80)
    
    results = []
    pdf_total = 0
    db_total = 0
    mismatches = []
    
    for player_data in players:
        player_name = player_data['name']
        pdf_score = player_data['score']
        pdf_total += pdf_score
        
        # Find player in database
        matches = find_player_in_db(cursor, player_name, year)
        
        if not matches:
            results.append({
                'name': player_name,
                'pdf': pdf_score,
                'db': 'NOT FOUND',
                'match': '❌',
                'player_id': None
            })
            continue
        
        # Use the first match (most recent player with that last name)
        player_id, first_name, last_name, position = matches[0]
        
        # Get score from database
        db_score = get_player_score_from_db(cursor, player_id, year, week)
        
        if db_score is None:
            results.append({
                'name': f"{first_name[0]}.{last_name}",
                'position': position,
                'pdf': pdf_score,
                'db': 'NO DATA',
                'match': '❌',
                'player_id': player_id
            })
        else:
            db_total += db_score
            match = '✅' if pdf_score == db_score else '❌'
            
            results.append({
                'name': f"{first_name[0]}.{last_name}",
                'position': position,
                'pdf': pdf_score,
                'db': db_score,
                'match': match,
                'player_id': player_id
            })
            
            if pdf_score != db_score:
                mismatches.append({
                    'name': f"{first_name} {last_name}",
                    'player_id': player_id,
                    'pdf': pdf_score,
                    'db': db_score,
                    'diff': db_score - pdf_score
                })
    
    # Print results table
    print(f"{'Player':<20} {'Pos':<4} {'PDF':<6} {'DB':<6} {'Match':<6}")
    print("-" * 80)
    
    for r in results:
        pos = r.get('position', '???')
        print(f"{r['name']:<20} {pos:<4} {r['pdf']:<6} {str(r['db']):<6} {r['match']:<6}")
    
    print("-" * 80)
    print(f"{'TOTALS':<20} {'':4} {pdf_total:<6} {db_total:<6}")
    print()
    
    # Check if totals match team score
    pdf_matches_team = pdf_total == team_score
    db_matches_team = db_total == team_score
    
    print(f"PDF player total ({pdf_total}) vs Team score ({team_score}): {'✅ MATCH' if pdf_matches_team else '❌ MISMATCH'}")
    print(f"DB player total ({db_total}) vs Team score ({team_score}): {'✅ MATCH' if db_matches_team else '❌ MISMATCH'}")
    
    # Report mismatches
    if mismatches:
        print(f"\n⚠️  DISCREPANCIES FOUND ({len(mismatches)}):")
        for m in mismatches:
            print(f"  • {m['name']} ({m['player_id']}): PDF={m['pdf']}, DB={m['db']}, Diff={m['diff']:+d}")
    
    # Summary analysis
    if not pdf_matches_team:
        print(f"\n⚠️  WARNING: PDF individual scores don't add up to team total!")
        print(f"   Difference: {pdf_total - team_score:+d}")
    
    if not db_matches_team and pdf_matches_team:
        print(f"\n⚠️  WARNING: Database has errors - PDF scores are internally consistent but DB is not")
    
    if db_matches_team and not pdf_matches_team:
        print(f"\n⚠️  WARNING: Database is consistent but PDF has transcription errors")

def main():
    if len(sys.argv) != 2:
        print("Usage: python3 verify_pdf_scores.py <pdf_file>")
        print()
        print("Example:")
        print("  python3 verify_pdf_scores.py /path/to/2000_01.pdf")
        print("  python3 verify_pdf_scores.py http://pfl-data.local/wp-content/uploads/2000_01.pdf")
        sys.exit(1)
    
    pdf_path = sys.argv[1]
    
    # Handle URLs
    if pdf_path.startswith('http://') or pdf_path.startswith('https://'):
        import subprocess
        import tempfile
        import os
        
        # Extract filename from URL
        url_filename = os.path.basename(pdf_path)
        
        # Download to temp file with original filename
        temp_dir = tempfile.gettempdir()
        temp_path = os.path.join(temp_dir, url_filename)
        
        print(f"Downloading PDF from URL...")
        result = subprocess.run(['curl', '-s', pdf_path, '-o', temp_path], capture_output=True)
        
        if result.returncode != 0:
            print(f"Error downloading PDF: {result.stderr.decode()}")
            sys.exit(1)
        
        pdf_path = temp_path
    
    if not Path(pdf_path).exists():
        print(f"Error: File not found: {pdf_path}")
        sys.exit(1)
    
    verify_pdf_scores(pdf_path)

if __name__ == "__main__":
    main()
