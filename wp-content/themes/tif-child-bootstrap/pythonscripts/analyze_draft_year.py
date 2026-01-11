#!/usr/bin/env python3
"""
Draft Analysis Script
Analyzes PFL draft picks for a given year and outputs HTML formatted results.

Usage: python3 analyze_draft_year.py <year>
Example: python3 analyze_draft_year.py 2001
"""

import sys
import subprocess
import re
from collections import namedtuple

DraftPick = namedtuple('DraftPick', ['pick_num', 'round', 'team', 'orig_team', 'name', 'position', 'season_pts', 'career_pts', 'protected', 'hall_of_fame'])


def fetch_draft_data(year):
    """Fetch draft data from the URL"""
    url = f"http://pfl-data.local/drafts/?id={year}"
    try:
        result = subprocess.run(
            ['curl', '-s', url],
            capture_output=True,
            text=True,
            check=True
        )
        return result.stdout
    except subprocess.CalledProcessError as e:
        print(f"Error fetching data: {e}")
        return None


def fetch_protected_players(year):
    """Fetch protected players from database for the season after draft year"""
    next_year = year + 1
    
    # Query the wp_protections table for players protected in next season
    # Use mysql with Local by Flywheel socket
    socket_path = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"
    
    try:
        # Query to get first and last names concatenated
        query = f"SELECT CONCAT(playerFirst, ' ', playerLast) FROM wp_protections WHERE year = {next_year}"
        
        result = subprocess.run(
            [
                'mysql',
                '-S', socket_path,
                '-u', 'root',
                '-proot',
                'local',
                '-e', query,
                '--skip-column-names'
            ],
            capture_output=True,
            text=True,
            check=True
        )
        
        protected_players = set()
        for line in result.stdout.strip().split('\n'):
            if line.strip():
                protected_players.add(line.strip())
        
        return protected_players
    except subprocess.CalledProcessError as e:
        # Database query failed or table doesn't exist
        return set()
    except Exception as e:
        return set()


def fetch_hall_of_fame_members():
    """Fetch Hall of Fame members from the URL"""
    url = "http://pfl-data.local/hall-of-fame/"
    try:
        result = subprocess.run(
            ['curl', '-s', url],
            capture_output=True,
            text=True,
            check=True
        )
        # Extract player names from the Hall of Fame page
        # Format: <a href="/player?id=....">Name</a>
        hof_members = set()
        matches = re.findall(r'<a href="/player\?id=[^"]+">([^<]+)</a>', result.stdout)
        for name in matches:
            hof_members.add(name.strip())
        return hof_members
    except subprocess.CalledProcessError as e:
        print(f"Error fetching Hall of Fame data: {e}")
        return set()


    
def parse_draft_html(html_content, hof_members, protected_players_next_year):
    """Parse draft HTML content and extract draft picks."""
    picks = []
    current_round = 1
    pick_in_round = 0
    
    # Split content into sections by round headers
    sections = re.split(r'<tr[^>]*class="[^"]*bg-dark[^"]*"[^>]*>.*?Round\s+(\d+).*?</tr>', html_content)
    
    for i, section in enumerate(sections):
        # Every other match is a round number
        if i > 0 and i % 2 == 1:
            # This is a round number from the split
            current_round = int(sections[i])
            pick_in_round = 0
            continue
        
        # Find all picks in this section
        picks_in_section = re.findall(
            r'<td class="min-width hidden-xs">([A-Z]{3})</td>.*?' +  # Team
            r'<td class="min-width hidden-xs">(?:&nbsp;|([A-Z]{3}))</td>.*?' +  # Orig team (may be empty)
            r'class="player-link">([^<]+)</a>.*?' +  # Player name
            r'<td class="text-center"><span[^>]*>([A-Z]{2,3})</span></td>.*?' +  # Position
            r'<td class="text-center"><span[^>]*>([^<]*)</span></td>.*?' +  # Season points
            r'<td class="text-center"><span[^>]*>([^<]*)</span></td>',  # Career points
            section,
            re.DOTALL
        )
        
        for match in picks_in_section:
            pick_in_round += 1
            
            team = match[0]
            orig_team = match[1] if match[1] else team
            name = match[2]
            position = match[3]
            
            # Parse season points
            season_pts = 0
            if match[4] and match[4] != 'Never Played' and match[4].isdigit():
                season_pts = int(match[4])
            
            # Parse career points
            career_pts = 0
            if match[5] and match[5] != 'Never Played' and match[5].isdigit():
                career_pts = int(match[5])
            
            # Check if player was protected in NEXT year's draft (year+1)
            # This shows the player was so good they were protected after their rookie season
            protected = name in protected_players_next_year
            
            # Check if player is in Hall of Fame
            hall_of_fame = name in hof_members
            
            # Calculate overall pick number
            pick_num = (current_round - 1) * 10 + pick_in_round
            
            pick = DraftPick(
                pick_num=pick_num,
                round=current_round,
                team=team,
                orig_team=orig_team,
                name=name,
                position=position,
                season_pts=season_pts,
                career_pts=career_pts,
                protected=protected,
                hall_of_fame=hall_of_fame
            )
            picks.append(pick)
    
    return picks


def calculate_value_score(pick):
    """
    Calculate a value score based on:
    - Career points with franchise (HEAVILY weighted - 5x)
    - Protection status (3x multiplier - shows franchise commitment)
    - Hall of Fame status (4x multiplier - ultimate recognition)
    - Draft position (later picks get bonus)
    - Season points (minor factor - 0.5x)
    - Position (kickers penalized)
    """
    # HEAVILY weight career points (5x base multiplier)
    career_value = pick.career_pts * 5
    
    # Season points are less important (0.5x)
    season_value = pick.season_pts * 0.5
    
    # Calculate raw value
    raw_value = career_value + season_value
    
    # Apply draft position bonus (later picks get higher multiplier)
    value_score = raw_value * (pick.pick_num / 10)
    
    # MAJOR BONUS: Hall of Fame status - ultimate achievement
    # HOF players get a 4x multiplier
    if pick.hall_of_fame:
        value_score *= 4.0
    
    # MAJOR BONUS: Protection status - franchise commitment
    # Protected players get a 3x multiplier
    if pick.protected:
        value_score *= 3.0
    
    # PENALTY: Kickers are less valuable
    # Apply 0.4x multiplier to kickers (60% reduction)
    if pick.position == 'PK':
        value_score *= 0.4
    
    return value_score


def analyze_draft(picks, year):
    """Analyze draft picks and generate results"""
    
    # Add value scores to picks
    analyzed_picks = []
    for pick in picks:
        value_score = calculate_value_score(pick)
        analyzed_picks.append((pick, value_score))
    
    # Sort by value score
    analyzed_picks.sort(key=lambda x: x[1], reverse=True)
    
    # Get best pick
    if not analyzed_picks:
        return None
        
    best_pick, best_value = analyzed_picks[0]
    
    # Find late round steals (round 3+, high career value)
    career_threshold = 100 if year >= 2000 else 200
    late_round_steals = [p for p in picks if p.round >= 3 and p.career_pts >= career_threshold]
    late_round_steals.sort(key=lambda p: p.career_pts, reverse=True)
    
    # Find first round busts (less than 50 pts)
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


def generate_html_output(analysis):
    """Generate HTML formatted output"""
    
    if not analysis:
        return "<p>No draft data found.</p>"
    
    year = analysis['year']
    best = analysis['best_pick']
    best_value = analysis['best_value']
    
    html = f'''<div class="draft-analysis">
  <p class="lead">Based on my analysis of the {year} draft, <strong>{best.name} (Pick #{best.pick_num}, Round {best.round}) by {best.team} was the best pick in the draft</strong>.</p>
  
  <h3>Why {best.name} Was the Best Pick:</h3>
  <ol>
    <li><strong>Outstanding Production:</strong> Scored {best.career_pts} career points for the franchise'''
    
    # Add context about ranking
    if best.career_pts > 500:
        html += ' - an exceptional total'
    elif best.career_pts > 250:
        html += ' - one of the highest totals in the draft'
    elif best.career_pts > 100:
        html += ' - a solid contribution'
    
    html += '</li>\n'
    
    if best.season_pts > 0:
        html += f'    <li><strong>Immediate Impact:</strong> Put up {best.season_pts} points in the first season ({year})'
        
        # Check if it was highest in draft
        max_season_pts = max([p[0].season_pts for p in analysis['analyzed_picks']])
        if best.season_pts == max_season_pts:
            html += ', the highest season total in the entire draft'
        
        html += '</li>\n'
    
    html += f'''    <li><strong>Draft Position Value:</strong> Selected at pick #{best.pick_num} in the {best.round}'''
    
    if best.round == 1:
        html += 'st'
    elif best.round == 2:
        html += 'nd'
    elif best.round == 3:
        html += 'rd'
    else:
        html += 'th'
    
    html += ' round, which represents '
    
    if best.round >= 4:
        html += 'tremendous value for a player who became a franchise contributor'
    elif best.round >= 3:
        html += 'excellent value for this level of production'
    else:
        html += 'solid value at this draft position'
    
    html += '</li>\n'
    
    # Add Hall of Fame status if applicable
    if best.hall_of_fame:
        html += '    <li><strong>Hall of Fame:</strong> This player was inducted into the PFL Hall of Fame, the ultimate recognition of greatness</li>\n'
    
    # Add protection status if applicable
    if best.protected:
        html += '    <li><strong>Protected Player:</strong> The franchise protected this player, showing strong commitment and confidence in their value</li>\n'
    
    if best.round >= 3:
        html += f'''    <li><strong>Late-Round Gem:</strong> Getting {best.career_pts} career points from a {best.round}'''
        if best.round == 3:
            html += 'rd'
        else:
            html += 'th'
        html += ' round pick demonstrates excellent draft savvy</li>\n'
    
    html += '''  </ol>
  
  <h3>Other Notable Aspects:</h3>
  
  <h4>Top 10 Players by Value Score:</h4>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Rank</th>
        <th>Player</th>
        <th>Team</th>
        <th>Pick #</th>
        <th>Round</th>
        <th>Position</th>
        <th>Season PTS</th>
        <th>Career PTS</th>
        <th>Protected</th>
        <th>HOF</th>
        <th>Value Score</th>
      </tr>
    </thead>
    <tbody>
'''
    
    for i, (pick, value) in enumerate(analysis['analyzed_picks'][:10], 1):
        protected_icon = '<i class="fa fa-lock"></i>' if pick.protected else ''
        hof_icon = '<i class="fa fa-star"></i>' if pick.hall_of_fame else ''
        html += f'''      <tr>
        <td>{i}</td>
        <td><strong>{pick.name}</strong></td>
        <td>{pick.team}</td>
        <td>#{pick.pick_num}</td>
        <td>{pick.round}</td>
        <td>{pick.position}</td>
        <td>{pick.season_pts}</td>
        <td>{pick.career_pts}</td>
        <td>{protected_icon}</td>
        <td>{hof_icon}</td>
        <td>{value:.1f}</td>
      </tr>
'''
    
    html += '''    </tbody>
  </table>
  
  <h4>Late Round Steals:</h4>
  <ul>
'''
    
    if analysis['late_round_steals']:
        # Summary statement
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
        
        # List notable steals
        for pick in analysis['late_round_steals'][:5]:  # Top 5 steals
            html += f'    <li>{pick.name} at pick #{pick.pick_num} (Round {pick.round}) scored {pick.career_pts} career points</li>\n'
    else:
        html += f'    <li>No significant late-round steals (rounds 3+) in the {year} draft</li>\n'
    
    html += '''  </ul>
  
  <h4>First Round Disappointments:</h4>
  <ul>
'''
    
    if analysis['first_round_busts']:
        html += f'    <li>{len(analysis["first_round_busts"])} of the first-round picks scored fewer than 50 career points'
        
        # List worst busts
        worst_busts = analysis['first_round_busts'][:3]
        bust_names = ', '.join([f'{p.name} (#{p.pick_num} - {p.career_pts} pts)' for p in worst_busts])
        html += f', including {bust_names}</li>\n'
    else:
        html += f'    <li>The first round of the {year} draft was solid with no major disappointments</li>\n'
    
    html += '  </ul>\n'
    html += '  \n'
    html += f'  <p class="conclusion">The combination of {best.name}\'s '
    
    if best.career_pts > 300:
        html += 'massive '
    elif best.career_pts > 150:
        html += 'strong '
    else:
        html += ''
    
    html += f'career production ({best.career_pts} points)'
    
    if best.season_pts > 50:
        html += f', immediate impact ({best.season_pts} points in year one)'
    
    # Choose pronoun based on position
    pronoun = 'him' if best.position in ['QB', 'RB', 'PK'] else 'them'
    html += f', and the value of getting {pronoun} at pick #{best.pick_num} makes this the most savvy pick in the {year} draft.</p>\n'
    html += '</div>'
    
    return html


def main():
    if len(sys.argv) != 2:
        print("Usage: python3 analyze_draft_year.py <year>")
        print("Example: python3 analyze_draft_year.py 2001")
        sys.exit(1)
    
    try:
        year = int(sys.argv[1])
    except ValueError:
        print(f"Error: '{sys.argv[1]}' is not a valid year")
        sys.exit(1)
    
    print(f"Fetching draft data for {year}...")
    html_content = fetch_draft_data(year)
    
    if not html_content:
        print("Failed to fetch draft data")
        sys.exit(1)
    
    print("Fetching Hall of Fame members...")
    hof_members = fetch_hall_of_fame_members()
    print(f"Found {len(hof_members)} Hall of Fame members")
    
    print(f"Fetching protected players from {year + 1} draft...")
    protected_players_next_year = fetch_protected_players(year)
    print(f"Found {len(protected_players_next_year)} protected players from next season")
    
    print("Parsing draft data...")
    picks = parse_draft_html(html_content, hof_members, protected_players_next_year)
    
    if not picks:
        print(f"No draft picks found for {year}")
        sys.exit(1)
    
    print(f"Found {len(picks)} picks. Analyzing...")
    analysis = analyze_draft(picks, year)
    
    if not analysis:
        print("Analysis failed")
        sys.exit(1)
    
    print("\n" + "=" * 80)
    print(f"{year} DRAFT ANALYSIS")
    print("=" * 80)
    print(f"\nBest Pick: {analysis['best_pick'].name}")
    print(f"  Pick #{analysis['best_pick'].pick_num} (Round {analysis['best_pick'].round})")
    print(f"  Team: {analysis['best_pick'].team}")
    print(f"  Career Points: {analysis['best_pick'].career_pts}")
    print(f"  Value Score: {analysis['best_value']:.1f}")
    
    print("\n" + "=" * 80)
    print("HTML OUTPUT")
    print("=" * 80 + "\n")
    
    html_output = generate_html_output(analysis)
    print(html_output)


if __name__ == '__main__':
    main()
