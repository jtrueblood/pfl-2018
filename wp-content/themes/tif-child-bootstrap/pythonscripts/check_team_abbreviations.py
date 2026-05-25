#!/usr/bin/env python3
"""
Script to cross-check team abbreviations between functions.php all_nfl_teams() 
and the bye week JSON files.
"""

import json
import os
import glob

# Team abbreviations from functions.php all_nfl_teams()
php_teams = {
    'IND', 'GNB', 'PHI', 'NWE', 'MIN', 'ATL', 'DAL', 'DEN', 'BUF', 'SFO',
    'NOR', 'CIN', 'KAN', 'SEA', 'DET', 'PIT', 'ARI', 'STL', 'SDG', 'HOU',
    'MIA', 'NYG', 'BAL', 'WAS', 'CHI', 'CAR', 'OAK', 'TEN', 'JAX', 'TAM',
    'NYJ', 'CLE', 'LAR', 'LAC', 'LVR', 'RAM', 'RAI', 'PHO'
}

# Get all teams from JSON files
json_teams = set()
json_dir = "/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-content/themes/tif-child-bootstrap/nfl-bye-weeks"

json_files = glob.glob(os.path.join(json_dir, "bye_weeks_*.json"))

for json_file in json_files:
    with open(json_file, 'r') as f:
        data = json.load(f)
        for bye_week in data['bye_weeks']:
            for team in bye_week['teams']:
                json_teams.add(team)

print("=" * 70)
print("TEAM ABBREVIATION CROSS-CHECK")
print("=" * 70)

# Teams in JSON but not in PHP array
json_only = json_teams - php_teams
if json_only:
    print("\n⚠️  Teams in JSON files but NOT in functions.php all_nfl_teams():")
    for team in sorted(json_only):
        print(f"   - {team}")
else:
    print("\n✓ All JSON teams found in functions.php")

# Teams in PHP but not in JSON (this is expected for historical teams)
php_only = php_teams - json_teams
if php_only:
    print("\n📝 Teams in functions.php but NOT in JSON files:")
    print("   (This is normal for historical teams or different abbreviations)")
    for team in sorted(php_only):
        print(f"   - {team}")

# Common teams
common = php_teams & json_teams
print(f"\n✓ Common teams: {len(common)} abbreviations match")

# Print mapping suggestions for mismatches
print("\n" + "=" * 70)
print("MAPPING SUGGESTIONS")
print("=" * 70)

# Common mismatches we can detect
mappings = {
    'GB': 'GNB',
    'NE': 'NWE',
    'SF': 'SFO',
    'NO': 'NOR',
    'KC': 'KAN',
    'TB': 'TAM',
    'LA': 'LAR'  # or could be RAM
}

needs_mapping = []
for json_team in sorted(json_only):
    if json_team in mappings:
        print(f"   {json_team} (JSON) → {mappings[json_team]} (PHP)")
        needs_mapping.append(json_team)

if not needs_mapping:
    print("   No obvious mappings needed")

print("\n" + "=" * 70)
print(f"Total unique teams in JSON: {len(json_teams)}")
print(f"Total teams in PHP array: {len(php_teams)}")
print("=" * 70)
