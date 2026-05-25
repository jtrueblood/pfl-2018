import requests
import pandas as pd
from datetime import datetime

# ESPN API endpoint for NFL standings
url = "https://site.api.espn.com/apis/v2/sports/football/nfl/standings"

print("Fetching NFL data from ESPN API...")

try:
    response = requests.get(url)
    response.raise_for_status()
    data = response.json()
    
    teams_data = []
    
    # Parse the standings data
    if 'children' in data:
        for conference in data['children']:
            conference_name = conference.get('name', 'Unknown')
            if 'standings' in conference and 'entries' in conference['standings']:
                for team_entry in conference['standings']['entries']:
                    team = team_entry.get('team', {})
                    stats = team_entry.get('stats', [])
                    
                    # Extract stats
                    stats_dict = {}
                    for stat in stats:
                        stats_dict[stat.get('name')] = stat.get('value')
                    
                    team_info = {
                        'name': team.get('displayName', 'Unknown'),
                        'abbreviation': team.get('abbreviation', 'UNK'),
                        'conference': conference_name,
                        'wins': stats_dict.get('wins', 0),
                        'losses': stats_dict.get('losses', 0),
                        'ties': stats_dict.get('ties', 0),
                        'win_percentage': stats_dict.get('winPercent', 0),
                        'points_for': stats_dict.get('pointsFor', 0),
                        'points_against': stats_dict.get('pointsAgainst', 0),
                        'point_differential': stats_dict.get('pointDifferential', 0),
                        'streak': stats_dict.get('streak', 'N/A')
                    }
                    teams_data.append(team_info)
    
    if teams_data:
        # Convert to DataFrame
        df = pd.DataFrame(teams_data)
        
        # Sort by wins descending
        df = df.sort_values('wins', ascending=False)
        
        # Save to CSV
        df.to_csv('nfl_teams_data.csv', index=False)
        
        print(f"\nNFL team data saved to nfl_teams_data.csv ({len(teams_data)} teams)")
        print(f"Data retrieved on: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        print("\nPreview:")
        print(df.to_string(index=False))
    else:
        print("No team data found in the response")
        
except requests.exceptions.RequestException as e:
    print(f"Error fetching data: {e}")
except Exception as e:
    print(f"Error processing data: {e}")
