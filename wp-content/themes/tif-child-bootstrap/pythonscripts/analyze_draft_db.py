#!/usr/bin/env python3
"""
Draft Analysis Script (Database Version)
Analyzes PFL draft picks for a given year using direct database queries.

Usage: python3 analyze_draft_db.py <year>
Example: python3 analyze_draft_db.py 2001
"""

import sys
import subprocess
import re
from collections import namedtuple

# MySQL connection settings
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"
MYSQL_USER = "root"
MYSQL_PASS = "root"
MYSQL_DB = "local"

DraftPick = namedtuple('DraftPick', ['pick_num', 'round', 'team', 'name', 'position', 'season_pts', 'career_pts', 'protected', 'hall_of_fame', 'playerid'])


def run_mysql_query(query):
    """Execute a MySQL query and return results"""
    try:
        result = subprocess.run(
            [
                'mysql',
                '-S', MYSQL_SOCKET,
                '-u', MYSQL_USER,
                f'-p{MYSQL_PASS}',
                MYSQL_DB,
                '-e', query,
                '--skip-column-names',
                '--batch'
            ],
            capture_output=True,
            text=True,
            check=True
        )
        return result.stdout
    except subprocess.CalledProcessError as e:
        print(f"Database query failed: {e}")
        return None


def fetch_protected_players(year):
    """Fetch protected players from next season"""
    next_year = year + 1
    query = f"SELECT CONCAT(playerFirst, ' ', playerLast) FROM wp_protections WHERE year = {next_year}"
    result = run_mysql_query(query)
    
    if not result:
        return set()
    
    protected_players = set()
    for line in result.strip().split('\n'):
        if line.strip():
            protected_players.add(line.strip())
    
    return protected_players


def fetch_hall_of_fame_members():
    """Fetch Hall of Fame members from website"""
    url = "http://pfl-data.local/hall-of-fame/"
    try:
        result = subprocess.run(
            ['curl', '-s', url],
            capture_output=True,
            text=True,
            check=True
        )
        hof_members = set()
        matches = re.findall(r'<a href="/player\?id=[^"]+">([^<]+)</a>', result.stdout)
        for name in matches:
            hof_members.add(name.strip())
        return hof_members
    except subprocess.CalledProcessError as e:
        return set()


def fetch_player_points(playerid, team, draft_year):
    """Fetch season and career points for a player with a specific team from draft year forward
    
    Rules:
    1. If player was NOT protected the following season, only count that season's points (not career)
    2. If player was re-drafted by same team later, only count until the next draft year
    3. Otherwise, count all career points from draft year forward
    """
    # Check if player table exists
    check_table_query = f"SHOW TABLES LIKE '{playerid}'"
    table_exists = run_mysql_query(check_table_query)
    
    if not table_exists or not table_exists.strip():
        return 0, 0
    
    # Check if this player was re-drafted by the same team in a later year
    redraft_query = f"""
    SELECT MIN(year) as next_draft_year
    FROM wp_drafts
    WHERE playerid = '{playerid}'
    AND team = '{team}'
    AND year > {draft_year}
    """
    
    redraft_result = run_mysql_query(redraft_query)
    next_draft_year = None
    if redraft_result and redraft_result.strip() and redraft_result.strip() != 'NULL':
        try:
            next_draft_year = int(redraft_result.strip())
        except (ValueError, TypeError):
            next_draft_year = None
    
    # Check if player was protected in the season immediately following the draft
    # If NOT protected following season, career points should be limited to just that season
    next_season = draft_year + 1
    protection_query = f"""
    SELECT COUNT(*) FROM wp_protections
    WHERE playerid = '{playerid}'
    AND team = '{team}'
    AND year = {next_season}
    """
    
    protection_result = run_mysql_query(protection_query)
    was_protected_next_season = False
    if protection_result and protection_result.strip():
        try:
            count = int(protection_result.strip())
            was_protected_next_season = count > 0
        except (ValueError, TypeError):
            was_protected_next_season = False
    
    # Get season points (first year with team after being drafted)
    season_query = f"""
    SELECT COALESCE(SUM(points), 0)
    FROM {playerid}
    WHERE team = '{team}'
    AND year = {draft_year}
    """
    
    season_result = run_mysql_query(season_query)
    season_pts = 0
    if season_result and season_result.strip():
        try:
            season_pts = int(float(season_result.strip()))
        except (ValueError, TypeError):
            season_pts = 0
    
    # Get career points with appropriate rules:
    # 1. If NOT protected next season, only count the draft year (career resets)
    # 2. If re-drafted later by same team, only count until next draft year
    # 3. Otherwise, count all points from draft year forward
    if not was_protected_next_season:
        # Player wasn't protected next season - only count draft year points
        career_pts = season_pts
    elif next_draft_year:
        career_query = f"""
        SELECT COALESCE(SUM(points), 0)
        FROM {playerid}
        WHERE team = '{team}'
        AND year >= {draft_year}
        AND year < {next_draft_year}
        """
        career_result = run_mysql_query(career_query)
        career_pts = 0
        if career_result and career_result.strip():
            try:
                career_pts = int(float(career_result.strip()))
            except (ValueError, TypeError):
                career_pts = 0
    else:
        career_query = f"""
        SELECT COALESCE(SUM(points), 0)
        FROM {playerid}
        WHERE team = '{team}'
        AND year >= {draft_year}
        """
        career_result = run_mysql_query(career_query)
        career_pts = 0
        if career_result and career_result.strip():
            try:
                career_pts = int(float(career_result.strip()))
            except (ValueError, TypeError):
                career_pts = 0
    
    return season_pts, career_pts


def fetch_draft_picks(year, hof_members, protected_players):
    """Fetch draft picks from database"""
    query = f"""
    SELECT 
        CAST(picknum AS UNSIGNED) as pick_num,
        CAST(round AS UNSIGNED) as round,
        team,
        TRIM(CONCAT(playerfirst, ' ', playerlast)) as name,
        pos as position,
        playerid
    FROM wp_drafts
    WHERE year = {year}
    ORDER BY CAST(picknum AS UNSIGNED)
    """
    
    result = run_mysql_query(query)
    
    if not result:
        return []
    
    picks = []
    for line in result.strip().split('\n'):
        if not line.strip():
            continue
        
        parts = line.split('\t')
        if len(parts) < 6:
            continue
        
        pick_num = int(parts[0]) if parts[0].isdigit() else 0
        round_num = int(parts[1]) if parts[1].isdigit() else 0
        team = parts[2]
        name = parts[3]
        position = parts[4]
        playerid = parts[5] if len(parts) > 5 else None
        
        # Get points if we have a playerid
        season_pts = 0
        career_pts = 0
        if playerid and playerid != 'NULL':
            season_pts, career_pts = fetch_player_points(playerid, team, year)
        
        # Check protection and HOF status
        protected = name in protected_players
        hall_of_fame = name in hof_members
        
        pick = DraftPick(
            pick_num=pick_num,
            round=round_num,
            team=team,
            name=name,
            position=position,
            season_pts=season_pts,
            career_pts=career_pts,
            protected=protected,
            hall_of_fame=hall_of_fame,
            playerid=playerid
        )
        picks.append(pick)
    
    return picks


def calculate_value_score(pick):
    """Calculate value score for a pick"""
    # HEAVILY weight career points (5x base multiplier)
    career_value = pick.career_pts * 5
    
    # Season points are less important (0.5x)
    season_value = pick.season_pts * 0.5
    
    # Calculate raw value
    raw_value = career_value + season_value
    
    # Apply draft position bonus (later picks get moderate bonus)
    # Formula: 1 + (pick_num / 50) gives gradual increase
    # Pick #5: 1.1x, Pick #50: 2.0x, Pick #70: 2.4x
    value_score = raw_value * (1 + (pick.pick_num / 50)) if pick.pick_num > 0 else raw_value
    
    # BONUS: Hall of Fame status (reduced to 2x to not overpower production)
    if pick.hall_of_fame:
        value_score *= 2.0
    
    # MAJOR BONUS: Protection status
    if pick.protected:
        value_score *= 3.0
    
    # PENALTY: Kickers are less valuable
    if pick.position == 'PK':
        value_score *= 0.4
    
    return value_score


def analyze_draft(picks, year):
    """Analyze draft picks and generate results"""
    analyzed_picks = []
    for pick in picks:
        value_score = calculate_value_score(pick)
        analyzed_picks.append((pick, value_score))
    
    analyzed_picks.sort(key=lambda x: x[1], reverse=True)
    
    if not analyzed_picks:
        return None
    
    best_pick, best_value = analyzed_picks[0]
    
    # Find late round steals (round 3+, high career value)
    career_threshold = 100 if year >= 2000 else 200
    late_round_steals = [p for p in picks if p.round >= 3 and p.career_pts >= career_threshold]
    late_round_steals.sort(key=lambda p: p.career_pts, reverse=True)
    
    # Find first round busts
    first_round_busts = [p for p in picks if p.round == 1 and p.career_pts < 50]
    first_round_busts.sort(key=lambda p: p.career_pts)
    
    # Get top 5 overall
    top_5 = analyzed_picks[:5]
    
    # Best value by position
    best_by_position = {}
    for pos in ['QB', 'RB', 'WR', 'PK', 'TE']:
        pos_picks = [(p, v) for p, v in analyzed_picks if p.position == pos and p.career_pts > 0]
        if pos_picks:
            best_by_position[pos] = pos_picks[0]
    
    return {
        'year': year,
        'best_pick': best_pick,
        'best_value': best_value,
        'top_5': top_5,
        'late_round_steals': late_round_steals,
        'first_round_busts': first_round_busts,
        'best_by_position': best_by_position,
        'analyzed_picks': analyzed_picks
    }


def create_pick_value_table():
    """Create the wp_drafts_pick_value table if it doesn't exist"""
    query = """
    CREATE TABLE IF NOT EXISTS wp_drafts_pick_value (
        id INT AUTO_INCREMENT PRIMARY KEY,
        year INT NOT NULL,
        playername VARCHAR(100) NOT NULL,
        playerid VARCHAR(20),
        team VARCHAR(10) NOT NULL,
        picknum INT NOT NULL,
        round INT NOT NULL,
        valuescore DECIMAL(10,2) NOT NULL,
        INDEX idx_year (year),
        INDEX idx_playerid (playerid),
        INDEX idx_team (team),
        UNIQUE KEY unique_pick (year, playerid, team)
    )
    """
    result = run_mysql_query(query)
    return result is not None


def save_pick_values_to_db(analysis, year):
    """Save all pick values to the database"""
    print(f"\nSaving pick values to database...")
    
    # First, delete existing records for this year
    delete_query = f"DELETE FROM wp_drafts_pick_value WHERE year = {year}"
    run_mysql_query(delete_query)
    
    # Insert all picks
    inserted = 0
    for pick, value_score in analysis['analyzed_picks']:
        # Escape single quotes in player names
        safe_name = pick.name.replace("'", "\\'").replace('"', '\\"')
        safe_playerid = pick.playerid if pick.playerid and pick.playerid != 'NULL' else 'NULL'
        
        if safe_playerid == 'NULL':
            insert_query = f"""
            INSERT INTO wp_drafts_pick_value 
            (year, playername, playerid, team, picknum, round, valuescore)
            VALUES ({year}, '{safe_name}', NULL, '{pick.team}', {pick.pick_num}, {pick.round}, {value_score:.2f})
            """
        else:
            insert_query = f"""
            INSERT INTO wp_drafts_pick_value 
            (year, playername, playerid, team, picknum, round, valuescore)
            VALUES ({year}, '{safe_name}', '{safe_playerid}', '{pick.team}', {pick.pick_num}, {pick.round}, {value_score:.2f})
            """
        
        result = run_mysql_query(insert_query)
        if result is not None:
            inserted += 1
    
    print(f"Saved {inserted} pick values to database")
    return inserted


def generate_html_output(analysis):
    """Generate HTML formatted output"""
    if not analysis:
        return "<p>No draft data found.</p>"
    
    year = analysis['year']
    best = analysis['best_pick']
    best_value = analysis['best_value']
    
    html = f'<div class="draft-analysis">\n'
    html += f'  <style>\n'
    html += f'    .draft-analysis h3 {{ margin-top: 40px; margin-bottom: 20px; }}\n'
    html += f'    .draft-analysis h4 {{ margin-top: 35px; margin-bottom: 18px; }}\n'
    html += f'    .draft-analysis .lead {{ margin-bottom: 35px; }}\n'
    html += f'    .draft-analysis ol, .draft-analysis ul {{ margin-bottom: 30px; }}\n'
    html += f'    .draft-analysis table {{ margin-bottom: 30px; }}\n'
    html += f'  </style>\n'
    html += f'  <p class="lead">Based on my analysis of the {year} draft, <strong>{best.name} (Pick #{best.pick_num}, Round {best.round}) by {best.team} was the best pick in the draft</strong>.</p>\n'
    html += f'  \n'
    html += f'  <h3>Why {best.name} Was the Best Pick:</h3>\n'
    html += f'  <ol>\n'
    
    # Production
    html += f'    <li><strong>Outstanding Production:</strong> Scored {best.career_pts} career points for the franchise'
    if best.career_pts > 500:
        html += ' - an exceptional total'
    elif best.career_pts > 250:
        html += ' - one of the highest totals in the draft'
    elif best.career_pts > 100:
        html += ' - a solid contribution'
    html += '</li>\n'
    
    # Season points
    if best.season_pts > 0:
        html += f'    <li><strong>Immediate Impact:</strong> Put up {best.season_pts} points in the first season ({year})'
        max_season_pts = max([p[0].season_pts for p in analysis['analyzed_picks']])
        if best.season_pts == max_season_pts:
            html += ', the highest season total in the entire draft'
        html += '</li>\n'
    
    # Draft position
    round_suffix = {1: 'st', 2: 'nd', 3: 'rd'}.get(best.round, 'th')
    html += f'    <li><strong>Draft Position Value:</strong> Selected at pick #{best.pick_num} in the {best.round}{round_suffix} round, which represents '
    if best.round >= 4:
        html += 'tremendous value for a player who became a franchise contributor'
    elif best.round >= 3:
        html += 'excellent value for this level of production'
    else:
        html += 'solid value at this draft position'
    html += '</li>\n'
    
    # HOF status
    if best.hall_of_fame:
        html += '    <li><strong>Hall of Fame:</strong> This player was inducted into the PFL Hall of Fame, the ultimate recognition of greatness</li>\n'
    
    # Protection status
    if best.protected:
        html += '    <li><strong>Protected Player:</strong> The franchise protected this player, showing strong commitment and confidence in their value</li>\n'
    
    # Late round gem
    if best.round >= 3:
        round_suffix = 'rd' if best.round == 3 else 'th'
        html += f'    <li><strong>Late-Round Gem:</strong> Getting {best.career_pts} career points from a {best.round}{round_suffix} round pick demonstrates excellent draft savvy</li>\n'
    
    html += '  </ol>\n'
    html += '  \n'
    html += '  <h3>Other Notable Aspects:</h3>\n'
    html += '  \n'
    
    # Top 10 table
    html += '  <h4>Top 10 Players by Value Score:</h4>\n'
    html += '  <table class="table table-striped">\n'
    html += '    <thead>\n'
    html += '      <tr>\n'
    html += '        <th>Rank</th>\n'
    html += '        <th>Player</th>\n'
    html += '        <th>Team</th>\n'
    html += '        <th>Pick #</th>\n'
    html += '        <th>Round</th>\n'
    html += '        <th>Position</th>\n'
    html += '        <th>Season PTS</th>\n'
    html += '        <th>Career PTS</th>\n'
    html += '        <th>Protected</th>\n'
    html += '        <th>HOF</th>\n'
    html += '        <th>Value Score</th>\n'
    html += '      </tr>\n'
    html += '    </thead>\n'
    html += '    <tbody>\n'
    
    for i, (pick, value) in enumerate(analysis['analyzed_picks'][:10], 1):
        protected_icon = '<i class="fa fa-lock"></i>' if pick.protected else ''
        hof_icon = '<i class="fa fa-star"></i>' if pick.hall_of_fame else ''
        html += f'      <tr>\n'
        html += f'        <td>{i}</td>\n'
        html += f'        <td><strong>{pick.name}</strong></td>\n'
        html += f'        <td>{pick.team}</td>\n'
        html += f'        <td>#{pick.pick_num}</td>\n'
        html += f'        <td>{pick.round}</td>\n'
        html += f'        <td>{pick.position}</td>\n'
        html += f'        <td>{pick.season_pts}</td>\n'
        html += f'        <td>{pick.career_pts}</td>\n'
        html += f'        <td>{protected_icon}</td>\n'
        html += f'        <td>{hof_icon}</td>\n'
        html += f'        <td>{value:.1f}</td>\n'
        html += f'      </tr>\n'
    
    html += '    </tbody>\n'
    html += '  </table>\n'
    html += '  \n'
    
    # Late round steals
    html += '  <h4>Late Round Steals:</h4>\n'
    html += '  <ul>\n'
    if analysis['late_round_steals']:
        threshold = 100 if analysis['year'] >= 2000 else 200
        html += f'    <li>The {year} draft had '
        if len(analysis['late_round_steals']) >= 5:
            html += 'exceptional'
        elif len(analysis['late_round_steals']) >= 3:
            html += 'solid'
        else:
            html += 'limited'
        html += f' value in rounds 3+, with {len(analysis["late_round_steals"])} player'
        if len(analysis['late_round_steals']) != 1:
            html += 's'
        html += f' scoring {threshold}+ career points</li>\n'
        
        for pick in analysis['late_round_steals'][:5]:
            html += f'    <li>{pick.name} at pick #{pick.pick_num} (Round {pick.round}) scored {pick.career_pts} career points</li>\n'
    else:
        html += f'    <li>No significant late-round steals (rounds 3+) in the {year} draft</li>\n'
    html += '  </ul>\n'
    html += '  \n'
    
    # First round busts
    html += '  <h4>First Round Disappointments:</h4>\n'
    html += '  <ul>\n'
    if analysis['first_round_busts']:
        html += f'    <li>{len(analysis["first_round_busts"])} of the first-round picks scored fewer than 50 career points'
        worst_busts = analysis['first_round_busts'][:3]
        bust_names = ', '.join([f'{p.name} (#{p.pick_num} - {p.career_pts} pts)' for p in worst_busts])
        html += f', including {bust_names}</li>\n'
    else:
        html += f'    <li>The first round of the {year} draft was solid with no major disappointments</li>\n'
    html += '  </ul>\n'
    html += '  \n'
    
    # Team rankings by average value
    html += '  <h4>Who Won This Draft:</h4>\n'
    html += '  <table class="table table-striped">\n'
    html += '    <thead>\n'
    html += '      <tr>\n'
    html += '        <th>Rank</th>\n'
    html += '        <th>Team</th>\n'
    html += '        <th>Avg Value</th>\n'
    html += '        <th>Total Value</th>\n'
    html += '        <th># of Picks</th>\n'
    html += '        <th>Best Pick</th>\n'
    html += '        <th>Worst Pick</th>\n'
    html += '      </tr>\n'
    html += '    </thead>\n'
    html += '    <tbody>\n'
    
    # Calculate team totals with full names and best/worst picks, sorted by average
    team_query = f"""
    SELECT 
        dpv.team,
        t.team as team_name,
        SUM(dpv.valuescore) as total_value,
        COUNT(*) as num_picks,
        AVG(dpv.valuescore) as avg_value
    FROM wp_drafts_pick_value dpv
    LEFT JOIN wp_teams t ON dpv.team = t.team_int
    WHERE dpv.year = {year}
    GROUP BY dpv.team, t.team
    ORDER BY avg_value DESC
    """
    
    team_result = run_mysql_query(team_query)
    if team_result and team_result.strip():
        rank = 1
        for line in team_result.strip().split('\n'):
            if not line.strip():
                continue
            parts = line.split('\t')
            if len(parts) >= 5:
                team_abbr = parts[0]
                team_name = parts[1] if parts[1] != 'NULL' else team_abbr
                total_value = float(parts[2])
                num_picks = int(parts[3])
                avg_value = float(parts[4])
                
                # Get best and worst picks for this team
                best_query = f"""
                SELECT playername, picknum, valuescore
                FROM wp_drafts_pick_value
                WHERE year = {year} AND team = '{team_abbr}'
                ORDER BY valuescore DESC
                LIMIT 1
                """
                
                worst_query = f"""
                SELECT playername, picknum, valuescore
                FROM wp_drafts_pick_value
                WHERE year = {year} AND team = '{team_abbr}'
                ORDER BY valuescore ASC
                LIMIT 1
                """
                
                best_result = run_mysql_query(best_query)
                worst_result = run_mysql_query(worst_query)
                
                best_pick = 'N/A'
                worst_pick = 'N/A'
                
                if best_result and best_result.strip():
                    best_parts = best_result.strip().split('\t')
                    if len(best_parts) >= 2:
                        best_pick = f'{best_parts[0]} (#{best_parts[1]})'
                
                if worst_result and worst_result.strip():
                    worst_parts = worst_result.strip().split('\t')
                    if len(worst_parts) >= 2:
                        worst_pick = f'{worst_parts[0]} (#{worst_parts[1]})'
                
                html += f'      <tr>\n'
                html += f'        <td>{rank}</td>\n'
                html += f'        <td><strong>{team_name}</strong></td>\n'
                html += f'        <td>{avg_value:.1f}</td>\n'
                html += f'        <td>{total_value:.1f}</td>\n'
                html += f'        <td>{num_picks}</td>\n'
                html += f'        <td>{best_pick}</td>\n'
                html += f'        <td>{worst_pick}</td>\n'
                html += f'      </tr>\n'
                rank += 1
    
    html += '    </tbody>\n'
    html += '  </table>\n'
    html += '</div>'
    
    return html


def process_year(year, hof_members):
    """Process a single year's draft analysis"""
    import os
    
    print(f"\n{'='*80}")
    print(f"Processing {year} draft...")
    print(f"{'='*80}")
    
    print(f"Fetching protected players from {year + 1}...")
    protected_players = fetch_protected_players(year)
    print(f"Found {len(protected_players)} protected players from next season")
    
    print("Fetching draft picks from database...")
    picks = fetch_draft_picks(year, hof_members, protected_players)
    
    if not picks:
        print(f"No draft picks found for {year}")
        return False
    
    print(f"Found {len(picks)} picks. Analyzing...")
    analysis = analyze_draft(picks, year)
    
    if not analysis:
        print(f"Analysis failed for {year}")
        return False
    
    # Save to database
    print("Saving to database...")
    save_pick_values_to_db(analysis, year)
    
    print(f"\nBest Pick: {analysis['best_pick'].name}")
    print(f"  Pick #{analysis['best_pick'].pick_num} (Round {analysis['best_pick'].round})")
    print(f"  Team: {analysis['best_pick'].team}")
    print(f"  Career Points: {analysis['best_pick'].career_pts}")
    print(f"  Value Score: {analysis['best_value']:.1f}")
    
    # Generate and save HTML output
    html_output = generate_html_output(analysis)
    
    script_dir = os.path.dirname(os.path.abspath(__file__))
    output_dir = os.path.join(os.path.dirname(script_dir), 'draft-analysis')
    
    # Create directory if it doesn't exist
    os.makedirs(output_dir, exist_ok=True)
    
    output_file = os.path.join(output_dir, f"draft_analysis_{year}.html")
    
    try:
        with open(output_file, 'w') as f:
            f.write(html_output)
        print(f"HTML saved to: {output_file}")
        return True
    except Exception as e:
        print(f"Warning: Could not save HTML to file: {e}")
        return False


def main():
    if len(sys.argv) < 2 or len(sys.argv) > 3:
        print("Usage: python3 analyze_draft_db.py <year> [end_year]")
        print("Examples:")
        print("  python3 analyze_draft_db.py 2001          # Analyze single year")
        print("  python3 analyze_draft_db.py 1991 1994     # Analyze range 1991-1994")
        sys.exit(1)
    
    try:
        start_year = int(sys.argv[1])
        end_year = int(sys.argv[2]) if len(sys.argv) == 3 else start_year
    except ValueError:
        print(f"Error: Invalid year value")
        sys.exit(1)
    
    # Validate year range
    if end_year < start_year:
        print(f"Error: End year ({end_year}) must be greater than or equal to start year ({start_year})")
        sys.exit(1)
    
    # Fetch Hall of Fame members once (shared across all years)
    print("Fetching Hall of Fame members...")
    hof_members = fetch_hall_of_fame_members()
    print(f"Found {len(hof_members)} Hall of Fame members")
    
    # Create table once
    print("\nCreating/verifying database table...")
    if not create_pick_value_table():
        print("Warning: Could not create database table")
    
    # Process each year in the range
    years = list(range(start_year, end_year + 1))
    successful = 0
    failed = 0
    
    for year in years:
        if process_year(year, hof_members):
            successful += 1
        else:
            failed += 1
    
    # Summary
    print(f"\n{'='*80}")
    print(f"SUMMARY")
    print(f"{'='*80}")
    print(f"Years processed: {len(years)}")
    print(f"Successful: {successful}")
    print(f"Failed: {failed}")
    print(f"\nAnalysis complete!\n")


if __name__ == '__main__':
    main()
