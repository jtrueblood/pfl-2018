#!/usr/bin/env python3
"""
Script to identify NFL bye weeks using multiple data sources.

Usage: python getbyeweeks.py <year>
Example: python getbyeweeks.py 2024

IMPORTANT: This script works for years 1991-2025.
- Years 1991-1998: Parsed from Google Sheets data
- Years 1999-2025: Retrieved from nfl_data_py library

Note: Team abbreviations are automatically converted to match the custom 
3-letter codes used in functions.php all_nfl_teams().
"""

import sys
import json
import os
import nfl_data_py as nfl
from typing import Set, Dict

# Mapping from nfl_data_py abbreviations to functions.php abbreviations
TEAM_ABBREVIATION_MAP = {
    'GB': 'GNB',     # Packers
    'KC': 'KAN',     # Chiefs
    'LA': 'LAR',     # Rams (current)
    'LV': 'LVR',     # Raiders (Las Vegas)
    'NE': 'NWE',     # Patriots
    'NO': 'NOR',     # Saints
    'SF': 'SFO',     # 49ers
    'TB': 'TAM',     # Buccaneers
    'SD': 'SDG',     # Chargers (historical San Diego)
    # Keep all other abbreviations as-is
}

# Mapping from team names to functions.php abbreviations
TEAM_NAME_TO_ABBR = {
    # Full names
    'Arizona': 'ARI',
    'Atlanta': 'ATL',
    'Baltimore': 'BAL',
    'Buffalo': 'BUF',
    'Carolina': 'CAR',
    'Chicago': 'CHI',
    'Cincinnati': 'CIN',
    'Cleveland': 'CLE',
    'Dallas': 'DAL',
    'Denver': 'DEN',
    'Detroit': 'DET',
    'Green Bay': 'GNB',
    'Houston': 'HOU',
    'Indianapolis': 'IND',
    'Jacksonville': 'JAX',
    'Kansas City': 'KAN',
    'Miami': 'MIA',
    'Minnesota': 'MIN',
    'New England': 'NWE',
    'New Orleans': 'NOR',
    'New York Giants': 'NYG',
    'New York Jets': 'NYJ',
    'Oakland': 'OAK',
    'Philadelphia': 'PHI',
    'Pittsburgh': 'PIT',
    'St. Louis': 'STL',
    'San Diego': 'SDG',
    'San Francisco': 'SFO',
    'Seattle': 'SEA',
    'Tampa Bay': 'TAM',
    'Tennessee': 'TEN',
    'Washington': 'WAS',
    # Shortened/nickname versions (from Google Sheet)
    'Cardinals': 'ARI',
    'Falcons': 'ATL',
    'Ravens': 'BAL',
    'Bills': 'BUF',
    'Panthers': 'CAR',
    'Bears': 'CHI',
    'Bengals': 'CIN',
    'Browns': 'CLE',
    'Cowboys': 'DAL',
    'Broncos': 'DEN',
    'Lions': 'DET',
    'Packers': 'GNB',
    'Oilers': 'HOU',  # Houston Oilers (became Titans)
    'Colts': 'IND',
    'Jaguars': 'JAX',
    'Chiefs': 'KAN',
    'Dolphins': 'MIA',
    'Vikings': 'MIN',
    'Patriots': 'NWE',
    'Saints': 'NOR',
    'Giants': 'NYG',
    'Jets': 'NYJ',
    'Raiders': 'OAK',
    'Eagles': 'PHI',
    'Steelers': 'PIT',
    'Rams': 'STL',  # St. Louis Rams
    'Chargers': 'SDG',
    '49ers': 'SFO',
    'Seahawks': 'SEA',
    'Buccaneers': 'TAM',
    'Titans': 'TEN',
    'Redskins': 'WAS',
}


def parse_google_sheet_data_1991_1998(year: int) -> tuple[Dict, Dict]:
    """
    Parse bye weeks from hardcoded Google Sheet data for years 1991-1998.
    
    Args:
        year: The NFL season year (1991-1998)
    
    Returns:
        Tuple of (Dictionary with season info and bye weeks, Dictionary of conversions made)
    """
    conversions = {}
    
    # Hardcoded data from Google Sheet
    # Format: {year: {week: [team_names]}}
    sheet_data = {
        1991: {
            5: ['Bengals', 'Browns', 'Oilers', 'Steelers'],
            6: ['Falcons', 'Rams', 'Saints', '49ers'],
            7: ['Bears', 'Broncos', 'Lions', 'Packers', 'Patriots', 'Buccaneers'],
            8: ['Cowboys', 'Giants', 'Eagles', 'Redskins'],
            9: ['Bills', 'Colts', 'Dolphins', 'Jets'],
            10: ['Chiefs', 'Raiders', 'Chargers', 'Seahawks'],
            14: ['Cardinals', 'Vikings']
        },
        1992: {
            1: ['Dolphins', 'Patriots'],
            4: ['Cowboys', 'Colts', 'Giants', 'Eagles', 'Cardinals', 'Redskins'],
            5: ['Bengals', 'Browns', 'Oilers', 'Steelers'],
            6: ['Bears', 'Lions', 'Packers', 'Vikings', 'Chargers', 'Buccaneers'],
            7: ['Bills', 'Jets'],
            8: ['Falcons', 'Rams', 'Saints', '49ers'],
            9: ['Broncos', 'Chiefs', 'Raiders', 'Seahawks']
        },
        1993: {
            3: ['Bills', 'Bears', 'Packers', 'Colts', 'Dolphins', 'Vikings', 'Jets', 'Buccaneers'],
            4: ['Cowboys', 'Broncos', 'Chiefs', 'Raiders', 'Giants', 'Eagles', 'Chargers', 'Redskins'],
            5: ['Bengals', 'Browns', 'Oilers', 'Cardinals', 'Steelers', 'Patriots'],
            6: ['Falcons', 'Lions', 'Rams', 'Saints', '49ers', 'Seahawks'],
            7: ['Bills', 'Bears', 'Packers', 'Colts', 'Dolphins', 'Vikings', 'Jets', 'Buccaneers'],
            8: ['Cowboys', 'Broncos', 'Chiefs', 'Raiders', 'Giants', 'Eagles', 'Chargers', 'Redskins'],
            9: ['Bengals', 'Browns', 'Oilers', 'Steelers'],
            10: ['Falcons', 'Rams', 'Saints', '49ers'],
            11: ['Lions', 'Patriots'],
            12: ['Cardinals', 'Seahawks']
        },
        1994: {
            4: ['Cardinals', 'Cowboys', 'Giants', 'Eagles'],
            5: ['Broncos', 'Chiefs', 'Raiders', 'Chargers'],
            6: ['Bengals', 'Browns', 'Oilers', 'Steelers'],
            7: ['Bears', 'Lions', 'Packers', 'Vikings', 'Seahawks', 'Buccaneers'],
            8: ['Bills', 'Dolphins', 'Patriots', 'Jets'],
            9: ['Falcons', 'Rams', 'Saints', '49ers'],
            11: ['Colts', 'Redskins']
        },
        1996: {
            3: ['Falcons', 'Panthers', 'Rams', '49ers'],
            4: ['Ravens', 'Bengals', 'Oilers', 'Steelers'],
            5: ['Bills', 'Colts', 'Dolphins', 'Patriots'],
            6: ['Cardinals', 'Cowboys', 'Giants', 'Eagles', 'Buccaneers', 'Redskins'],
            7: ['Broncos', 'Chiefs', 'Chargers', 'Seahawks'],
            8: ['Bears', 'Lions', 'Packers', 'Vikings'],
            9: ['Saints', 'Raiders'],
            10: ['Jaguars', 'Jets']
        },
        1997: {
            3: ['Bengals', 'Jaguars', 'Steelers', 'Titans'],
            4: ['Cardinals', 'Cowboys', 'Eagles', 'Redskins'],
            5: ['Bills', 'Colts', 'Dolphins', 'Patriots'],
            6: ['Falcons', 'Panthers', 'Rams', '49ers'],
            7: ['Ravens', 'Broncos', 'Chiefs', 'Raiders', 'Chargers', 'Seahawks'],
            8: ['Bears', 'Packers', 'Vikings', 'Buccaneers'],
            9: ['Lions', 'Jets'],
            10: ['Saints', 'Giants']
        },
        1998: {
            3: ['Falcons', 'Panthers', 'Saints', '49ers'],
            4: ['Bills', 'Dolphins', 'Patriots', 'Jets'],
            5: ['Ravens', 'Bengals', 'Jaguars', 'Steelers', 'Rams', 'Titans'],
            6: ['Lions', 'Packers', 'Vikings', 'Buccaneers'],
            7: ['Broncos', 'Chiefs', 'Raiders', 'Seahawks'],
            8: ['Cardinals', 'Cowboys', 'Colts', 'Giants', 'Eagles', 'Redskins'],
            9: ['Bears', 'Chargers']
        }
    }
    
    if year not in sheet_data:
        print(f"Error: No data available for year {year}", file=sys.stderr)
        sys.exit(1)
    
    print(f"Loading data from Google Sheet for {year}...")
    
    bye_weeks = []
    year_data = sheet_data[year]
    
    for week_num in sorted(year_data.keys()):
        team_names = year_data[week_num]
        team_abbrs = []
        
        for team_name in team_names:
            # Try to find matching abbreviation
            abbr = None
            for full_name, code in TEAM_NAME_TO_ABBR.items():
                if team_name in full_name or full_name in team_name:
                    abbr = code
                    conversions[team_name] = abbr
                    break
            
            if not abbr:
                print(f"Warning: Unknown team name '{team_name}'", file=sys.stderr)
            else:
                team_abbrs.append(abbr)
        
        if team_abbrs:
            bye_weeks.append({
                "week": week_num,
                "teams": sorted(team_abbrs)
            })
    
    result = {
        "season": year,
        "bye_weeks": bye_weeks
    }
    
    return result, conversions


def convert_team_abbreviation(team: str, conversions: Dict) -> str:
    """
    Convert team abbreviation from NFL standard to PHP format.
    
    Args:
        team: Original team abbreviation
        conversions: Dictionary to track conversions made
    
    Returns:
        Converted team abbreviation
    """
    if team in TEAM_ABBREVIATION_MAP:
        converted = TEAM_ABBREVIATION_MAP[team]
        conversions[team] = converted
        return converted
    return team


def get_teams_for_week(df, week: int, conversions: Dict) -> Set[str]:
    """
    Get all teams that played in a given week from the schedule dataframe.
    
    Args:
        df: Schedule dataframe
        week: The week number
        conversions: Dictionary to track conversions made
    
    Returns:
        Set of team abbreviations that played in that week (converted to PHP format)
    """
    week_df = df[df['week'] == week]
    teams = set(week_df['home_team'].tolist() + week_df['away_team'].tolist())
    # Convert all team abbreviations
    converted_teams = {convert_team_abbreviation(team, conversions) for team in teams}
    return converted_teams


def identify_bye_weeks(year: int) -> tuple[Dict, Dict]:
    """
    Identify all bye weeks for an NFL season.
    
    Args:
        year: The NFL season year (1999 or later)
    
    Returns:
        Tuple of (Dictionary with season info and bye weeks, Dictionary of conversions made)
    """
    # Track all team abbreviation conversions
    conversions = {}
    
    try:
        print(f"Loading schedule data for {year}...")
        df = nfl.import_schedules([year])
    except ValueError as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)
    
    # Filter to regular season only
    df = df[df['game_type'] == 'REG']
    
    print(f"Establishing master team list from Week 1...")
    master_team_list = get_teams_for_week(df, 1, conversions)
    
    if not master_team_list:
        print("Error: Could not retrieve teams from Week 1", file=sys.stderr)
        sys.exit(1)
    
    print(f"Found {len(master_team_list)} teams in Week 1")
    
    bye_weeks = []
    
    # Get the maximum week number in the schedule
    max_week = int(df['week'].max())
    
    # Check weeks 2 through max_week
    for week in range(2, max_week + 1):
        print(f"Checking Week {week}...")
        teams_playing = get_teams_for_week(df, week, conversions)
        
        # If we got no teams, we've likely reached the end of the season
        if not teams_playing:
            print(f"No data found for Week {week}, stopping.")
            break
        
        # Find teams on bye (in master list but not playing this week)
        teams_on_bye = master_team_list - teams_playing
        
        if teams_on_bye:
            bye_week_data = {
                "week": week,
                "teams": sorted(list(teams_on_bye))
            }
            bye_weeks.append(bye_week_data)
            print(f"  Week {week}: {len(teams_on_bye)} team(s) on bye")
    
    result = {
        "season": year,
        "bye_weeks": bye_weeks
    }
    
    return result, conversions


def main():
    if len(sys.argv) != 2:
        print("Usage: python getbyeweeks.py <year>")
        print("Example: python getbyeweeks.py 2024")
        sys.exit(1)
    
    try:
        year = int(sys.argv[1])
    except ValueError:
        print("Error: Year must be a valid integer", file=sys.stderr)
        sys.exit(1)
    
    print(f"Identifying bye weeks for {year} NFL season...")
    print("-" * 50)
    
    # Route to appropriate data source based on year
    if 1991 <= year <= 1998:
        result, conversions = parse_google_sheet_data_1991_1998(year)
    elif year >= 1999:
        result, conversions = identify_bye_weeks(year)
    else:
        print(f"Error: Data not available for year {year}", file=sys.stderr)
        print(f"Supported years: 1991-1998, 1999-2025", file=sys.stderr)
        sys.exit(1)
    
    # Define output directory
    output_dir = "/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-content/themes/tif-child-bootstrap/nfl-bye-weeks"
    
    # Create directory if it doesn't exist
    os.makedirs(output_dir, exist_ok=True)
    
    # Save to JSON file
    output_file = os.path.join(output_dir, f"bye_weeks_{year}.json")
    with open(output_file, 'w') as f:
        json.dump(result, f, indent=2)
    
    print("-" * 50)
    print(f"Results saved to {output_file}")
    print(f"\nFound {len(result['bye_weeks'])} week(s) with byes")
    
    # Count how many bye weeks each team has
    team_bye_count = {}
    for bye_week in result['bye_weeks']:
        for team in bye_week['teams']:
            team_bye_count[team] = team_bye_count.get(team, 0) + 1
    
    # Get total unique teams
    total_teams = len(team_bye_count)
    
    # Validation check
    print(f"\nValidation:")
    print(f"  Total teams with bye weeks: {total_teams}")
    
    # Check if all teams have the expected number of bye weeks
    byes_per_team = set(team_bye_count.values())
    if len(byes_per_team) == 1:
        num_byes = list(byes_per_team)[0]
        print(f"  Each team has {num_byes} bye week(s)")
        
        # Special case for 1993
        if year == 1993:
            if num_byes == 2:
                print(f"  ✓ Expected: 2 bye weeks per team (1993 special case)")
            else:
                print(f"  ⚠️  WARNING: Expected 2 bye weeks per team in 1993, found {num_byes}")
        else:
            if num_byes == 1:
                print(f"  ✓ Expected: 1 bye week per team")
            else:
                print(f"  ⚠️  WARNING: Expected 1 bye week per team, found {num_byes}")
    else:
        print(f"  ⚠️  WARNING: Teams have inconsistent bye weeks: {sorted(byes_per_team)}")
        # Show which teams have unusual bye counts
        for team, count in sorted(team_bye_count.items()):
            if count != 1 and year != 1993:
                print(f"     {team}: {count} bye week(s)")
            elif count != 2 and year == 1993:
                print(f"     {team}: {count} bye week(s)")
    
    # Print team abbreviation conversions
    if conversions:
        print("\nTeam Abbreviation Conversions (NFL → PHP format):")
        for nfl_code, php_code in sorted(conversions.items()):
            print(f"  {nfl_code} → {php_code}")
    
    # Print summary
    if result['bye_weeks']:
        print("\nSummary:")
        for bye_week in result['bye_weeks']:
            print(f"  Week {bye_week['week']}: {len(bye_week['teams'])} team(s)")


if __name__ == "__main__":
    main()
