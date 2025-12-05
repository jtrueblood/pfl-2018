from sportsipy.nfl.teams import Teams
import pandas as pd
import sys

# Try multiple years
years_to_try = [2024, 2023, 2022, 2021]
teams = None

for year in years_to_try:
    print(f"Trying {year} season...")
    try:
        teams = Teams(year=year)
        # Try to iterate to see if data exists
        team_count = len(list(teams))
        if team_count > 0:
            print(f"Found data for {year} season with {team_count} teams!")
            teams = Teams(year=year)  # Re-initialize after counting
            break
    except Exception as e:
        print(f"Error with {year}: {e}")
        continue

if not teams:
    print("Could not fetch data for any year. Exiting.")
    sys.exit(1)

# Create a list to store team data
teams_data = []

for team in teams:
    team_info = {
        'name': team.name,
        'abbreviation': team.abbreviation,
        'wins': team.wins,
        'losses': team.losses,
        'win_percentage': team.win_percentage,
        'points_for': team.points_for,
        'points_against': team.points_against,
        'point_difference': team.point_difference,
        'strength_of_schedule': team.strength_of_schedule,
        'simple_rating_system': team.simple_rating_system,
        'offensive_simple_rating_system': team.offensive_simple_rating_system,
        'defensive_simple_rating_system': team.defensive_simple_rating_system
    }
    teams_data.append(team_info)

# Convert to DataFrame
df = pd.DataFrame(teams_data)

# Sort by wins descending
df = df.sort_values('wins', ascending=False)

# Save to CSV
df.to_csv('nfl_teams_data.csv', index=False)

print(f"\nNFL team data saved to nfl_teams_data.csv ({len(teams_data)} teams)")
print("\nPreview:")
print(df.to_string())
