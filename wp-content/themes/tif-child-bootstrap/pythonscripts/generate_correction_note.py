#!/usr/bin/env python3
"""
Generate a correction note for a player's score update.
This note can be copied and pasted into the ACF Options area.
"""

import sys

def main():
    print("=" * 60)
    print("GENERATE CORRECTION NOTE")
    print("=" * 60)
    print()
    
    # Check if command-line arguments were provided
    if len(sys.argv) == 5:
        # Parse command-line arguments: PLAYER_NAME TEAM_ABBR YEAR WEEK
        player_name = sys.argv[1]
        team_abbr = sys.argv[2].upper()
        year = sys.argv[3]
        week = sys.argv[4]
        print(f"Player: {player_name}")
        print(f"Team: {team_abbr}")
        print(f"Year: {year}")
        print(f"Week: {week}")
    elif len(sys.argv) > 1:
        print("❌ Invalid number of arguments")
        print("\nUsage:")
        print('  Interactive mode: python3 generate_correction_note.py')
        print('  Command-line mode: python3 generate_correction_note.py "PLAYER_NAME" TEAM_ABBR YEAR WEEK')
        print('\nExample: python3 generate_correction_note.py "Boomer Esiason" PEP 1993 8')
        sys.exit(1)
    else:
        # Interactive mode
        print("Enter the following information:")
        print()
        player_name = input("Player Name (e.g., Boomer Esiason): ").strip()
        team_abbr = input("Team Abbreviation (e.g., PEP): ").strip().upper()
        year = input("Year (e.g., 1993): ").strip()
        week = input("Week (e.g., 8): ").strip()
    
    # Validate inputs
    if not player_name or not team_abbr or not year or not week:
        print("\n❌ All fields are required")
        sys.exit(1)
    
    # Generate Week ID (format: YYYYWW)
    week_id = f"{year}{week.zfill(2)}"
    
    # Generate the correction note
    note = f"{player_name} ({team_abbr}) had a correction to his update boxscore data. Player score and game score were corrected but outcome remains the same."
    
    # Display results
    print()
    print("=" * 60)
    print("CORRECTION NOTE GENERATED")
    print("=" * 60)
    print()
    print(f"Week ID: {week_id}")
    print(f"Team: {team_abbr}")
    print()
    print("Note:")
    print("-" * 60)
    print(note)
    print("-" * 60)
    print()
    print("Instructions:")
    print("1. Go to: http://pfl-data.local/wp-admin/admin.php?page=acf-options-options")
    print("2. Click 'Add Row' in the Weekly Update section")
    print(f"3. Enter Week ID: {week_id}")
    print(f"4. Enter Team: {team_abbr}")
    print("5. Paste the note above into the 'Update Notes' field")
    print("6. Click 'Update' to save")
    print()
    print("=" * 60)


if __name__ == "__main__":
    main()
