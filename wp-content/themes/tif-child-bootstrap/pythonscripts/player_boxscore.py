#!/usr/bin/env python3
"""
Player Boxscore Display Script

Display detailed boxscore information for a player in a specific week,
including their team's lineup and the opponent's lineup.

Usage:
    python3 player_boxscore.py <player_id> <year> <week>
    
Example:
    python3 player_boxscore.py 1993BoniPK 1995 2
"""

import mysql.connector
import sys
import glob
import os

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

# Modifiable stat fields (raw stats only, not calculated fields)
MODIFIABLE_FIELDS = [
    ('pass_yds', 'Passing Yards'),
    ('pass_td', 'Passing Touchdowns'),
    ('pass_int', 'Passing Interceptions'),
    ('rush_yds', 'Rushing Yards'),
    ('rush_td', 'Rushing Touchdowns'),
    ('rec_yds', 'Receiving Yards'),
    ('rec_td', 'Receiving Touchdowns'),
    ('xpm', 'Extra Points Made'),
    ('xpa', 'Extra Points Attempted'),
    ('fgm', 'Field Goals Made'),
    ('fga', 'Field Goals Attempted')
]

def calculate_player_score(player_id, year, stats):
    """Calculate NFL fantasy score based on player position and year
    
    Args:
        player_id: Player ID (last 2 chars indicate position)
        year: Season year
        stats: Dictionary of stat values
    
    Returns:
        Calculated NFL score (integer)
    """
    position = player_id[-2:] if len(player_id) >= 2 else ''
    
    # Get stats with defaults
    pass_yds = int(stats.get('pass_yds', 0) or 0)
    pass_td = int(stats.get('pass_td', 0) or 0)
    pass_int = int(stats.get('pass_int', 0) or 0)
    rush_yds = int(stats.get('rush_yds', 0) or 0)
    rush_td = int(stats.get('rush_td', 0) or 0)
    rec_yds = int(stats.get('rec_yds', 0) or 0)
    rec_td = int(stats.get('rec_td', 0) or 0)
    xpm = int(stats.get('xpm', 0) or 0)
    fgm = int(stats.get('fgm', 0) or 0)
    
    # Calculate score based on position
    if position == 'PK':  # Kicker
        nfl_score = xpm + (fgm * 2)
    else:  # QB, RB, WR
        if year == 1991:
            # 1991 scoring rules
            pass_get = pass_yds // 50
            pass_data = max(0, pass_get)  # Can't be negative
            nfl_score = pass_data + (rush_yds // 25) + ((pass_td + rush_td + rec_td) * 2) + (rec_yds // 25) - pass_int
        else:
            # Standard scoring rules
            nfl_score = (pass_yds // 30) + (rush_yds // 10) + ((pass_td + rush_td + rec_td) * 2) + (rec_yds // 10) - pass_int
    
    return nfl_score

def format_section_header(title):
    """Format a section header"""
    width = 80
    return f"\n{'=' * width}\n{title.center(width)}\n{'=' * width}"

def format_subsection(title):
    """Format a subsection header"""
    return f"\n{title}\n{'-' * len(title)}"

def display_player_info(cursor, player_id):
    """Display basic player information"""
    cursor.execute(
        "SELECT playerFirst, playerLast, position, rookie FROM wp_players WHERE p_id = %s",
        (player_id,)
    )
    player = cursor.fetchone()
    
    if not player:
        print(f"Error: Player {player_id} not found")
        return None
    
    print(format_section_header("PLAYER INFORMATION"))
    print(f"Name:     {player[0]} {player[1]}")
    print(f"Player ID: {player_id}")
    print(f"Position:  {player[2]}")
    print(f"Rookie Year: {player[3]}")
    
    return player

def display_game_boxscore(cursor, player_id, year, week):
    """Display player's game boxscore"""
    week_id = f"{year:04d}{week:02d}"
    
    # Get player's game data
    cursor.execute(
        f"SELECT * FROM `{player_id}` WHERE week_id = %s",
        (week_id,)
    )
    
    game = cursor.fetchone()
    
    if not game:
        print(f"\nError: No data found for {player_id} in {year} week {week}")
        return None
    
    # Get column names
    cursor.execute(f"DESCRIBE `{player_id}`")
    columns = [row[0] for row in cursor.fetchall()]
    
    # Create dictionary for easy access
    game_data = dict(zip(columns, game))
    
    print(format_section_header("PLAYER BOXSCORE"))
    
    # Game information
    print(format_subsection("Game Details"))
    print(f"Week ID:       {game_data['week_id']}")
    print(f"Year/Week:     {game_data['year']} Week {game_data['week']}")
    print(f"PFL Team:      {game_data['team']}")
    print(f"PFL Opponent:  {game_data['versus']}")
    print(f"Location:      {'Home' if game_data['home_away'] == 'H' else 'Away'} @ {game_data['location']}")
    print(f"Result:        {'Win' if game_data['win_loss'] == 1 else 'Loss' if game_data['win_loss'] == 0 else 'N/A'}")
    print(f"Game Date:     {game_data['game_date']}")
    
    # NFL information
    print(format_subsection("NFL Game"))
    print(f"NFL Team:      {game_data['nflteam']}")
    print(f"NFL Game:      {game_data['game_location']} {game_data['nflopp']}")
    
    # Statistics
    print(format_subsection("Statistics"))
    print(f"Passing:       {game_data['pass_yds'] or 0} yards, {game_data['pass_td'] or 0} TD, {game_data['pass_int'] or 0} INT")
    print(f"Rushing:       {game_data['rush_yds'] or 0} yards, {game_data['rush_td'] or 0} TD")
    print(f"Receiving:     {game_data['rec_yds'] or 0} yards, {game_data['rec_td'] or 0} TD")
    print(f"Kicking:       {game_data['fgm'] or 0}/{game_data['fga'] or 0} FG, {game_data['xpm'] or 0}/{game_data['xpa'] or 0} XP")
    
    # Scoring
    print(format_subsection("Scoring"))
    print(f"PFL Points:    {game_data['points']}")
    print(f"NFL Expected:  {game_data['nflscore']}")
    print(f"Difference:    {game_data['scorediff']}")
    
    return game_data

def validate_team_score(cursor, team_abbr, year, week):
    """Validate that team score matches sum of player scores"""
    week_id = f"{year:04d}{week:02d}"
    team_table = f"wp_team_{team_abbr}"
    
    # Check if team table exists
    cursor.execute(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'local' AND table_name = %s",
        (team_table[3:],)
    )
    
    if cursor.fetchone()[0] == 0:
        return None
    
    # Get team data
    cursor.execute(
        f"SELECT points, result, overtime, QB1, QB2, RB1, RB2, WR1, WR2, PK1, PK2 FROM `{team_table}` WHERE season = %s AND week = %s",
        (year, week)
    )
    
    team_game = cursor.fetchone()
    if not team_game:
        return None
    
    team_points, result, overtime, qb1, qb2, rb1, rb2, wr1, wr2, pk1, pk2 = team_game
    
    # Calculate sum of starter points (QB1, RB1, WR1, PK1)
    starters = [qb1, rb1, wr1, pk1]
    starter_total = 0
    starter_details = []
    
    for pid in starters:
        if pid and pid.lower() != 'none':
            cursor.execute(
                f"SELECT points FROM `{pid}` WHERE week_id = %s",
                (week_id,)
            )
            player_result = cursor.fetchone()
            if player_result:
                points = player_result[0] or 0
                starter_total += points
                # Get player name
                cursor.execute(
                    "SELECT playerFirst, playerLast, position FROM wp_players WHERE p_id = %s",
                    (pid,)
                )
                player_info = cursor.fetchone()
                if player_info:
                    starter_details.append(f"{player_info[0]} {player_info[1]} ({player_info[2]}): {points}")
                else:
                    starter_details.append(f"{pid}: {points}")
    
    # Add overtime bonus if applicable
    ot_bonus = 0
    if overtime:
        if result > 0:  # Win (positive point differential)
            ot_bonus = 1
        # Loss (negative differential) gets 0
    
    expected_total = starter_total + ot_bonus
    difference = team_points - expected_total
    
    return {
        'team_points': team_points,
        'starter_total': starter_total,
        'ot_bonus': ot_bonus,
        'expected_total': expected_total,
        'difference': difference,
        'is_valid': difference == 0,
        'starter_details': starter_details,
        'overtime': overtime
    }

def apply_player_modification(cursor, conn, player_id, game_data, modification, impacts, year, week):
    """Apply the modification and update all dependent fields
    
    Args:
        cursor: Database cursor
        conn: Database connection
        player_id: Player ID
        game_data: Current game data dictionary
        modification: Dictionary with modification details
        impacts: Dictionary from check_modification_impacts
        year: Season year
        week: Week number
    
    Returns:
        Boolean indicating success
    """
    week_id = f"{year:04d}{week:02d}"
    team = game_data['team']
    versus = game_data['versus']
    points_delta = impacts['points_delta']
    
    try:
        # Update player table
        new_nflscore = impacts['new_nflscore']
        new_points = impacts['new_points']
        new_scorediff = new_points - new_nflscore
        
        # Check if this is an NFL score acceptance (special case)
        if modification.get('accept_nfl_score'):
            # Only update points and scorediff, not the stat field
            update_query = f"""
                UPDATE `{player_id}` 
                SET points = %s,
                    scorediff = %s
                WHERE week_id = %s
            """
            cursor.execute(update_query, (
                new_points,
                new_scorediff,
                week_id
            ))
        else:
            # Regular stat field modification
            update_query = f"""
                UPDATE `{player_id}` 
                SET {modification['field']} = %s,
                    nflscore = %s,
                    points = %s,
                    scorediff = %s
                WHERE week_id = %s
            """
            cursor.execute(update_query, (
                modification['new_value'],
                new_nflscore,
                new_points,
                new_scorediff,
                week_id
            ))
        
        # If player is a starter, update team tables
        if impacts['is_starter'] and points_delta != 0:
            # Update player's team
            cursor.execute(
                f"SELECT points, vs_points FROM wp_team_{team} WHERE season = %s AND week = %s",
                (year, week)
            )
            team_data = cursor.fetchone()
            if team_data:
                new_team_points = team_data[0] + points_delta
                vs_points = team_data[1]
                new_result = new_team_points - vs_points
                
                cursor.execute(
                    f"UPDATE wp_team_{team} SET points = %s, result = %s WHERE season = %s AND week = %s",
                    (new_team_points, new_result, year, week)
                )
            
            # Update opponent's team (their result changes)
            cursor.execute(
                f"SELECT points, vs_points FROM wp_team_{versus} WHERE season = %s AND week = %s",
                (year, week)
            )
            versus_data = cursor.fetchone()
            if versus_data:
                versus_points = versus_data[0]
                # vs_points for opponent is our team's new points
                new_vs_points = new_team_points
                new_versus_result = versus_points - new_vs_points
                
                cursor.execute(
                    f"UPDATE wp_team_{versus} SET vs_points = %s, result = %s WHERE season = %s AND week = %s",
                    (new_vs_points, new_versus_result, year, week)
                )
            
            # Update win_loss in player table if game outcome changed
            if new_result > 0:
                new_win_loss = 1
            elif new_result < 0:
                new_win_loss = 0
            else:
                new_win_loss = 0  # Ties count as losses in this system
            
            cursor.execute(
                f"UPDATE `{player_id}` SET win_loss = %s WHERE week_id = %s",
                (new_win_loss, week_id)
            )
        
        conn.commit()
        return True
        
    except Exception as e:
        print(f"\nError applying modification: {e}")
        conn.rollback()
        return False

def check_modification_impacts(cursor, player_id, game_data, modification, year, week):
    """Check and warn about impacts of the modification
    
    Args:
        cursor: Database cursor
        player_id: Player ID
        game_data: Current game data dictionary
        modification: Dictionary with modification details
        year: Season year
        week: Week number
    
    Returns:
        Dictionary with warnings and calculated changes
    """
    # Create updated stats dictionary
    updated_stats = dict(game_data)
    updated_stats[modification['field']] = modification['new_value']
    
    # Calculate new nflscore and points
    old_nflscore = game_data['nflscore'] or 0
    new_nflscore = calculate_player_score(player_id, year, updated_stats)
    
    # When modifying stats, points should equal the new nflscore
    # (The assumption is that the stats are being corrected, so NFL calculation is correct)
    old_points = game_data['points']
    new_points = new_nflscore
    points_delta = new_points - old_points
    
    warnings = []
    
    # Check if player is a starter in their team
    team = game_data['team']
    versus = game_data['versus']
    position = player_id[-2:]
    starter_field = {'QB': 'QB1', 'RB': 'RB1', 'WR': 'WR1', 'PK': 'PK1'}.get(position)
    
    is_starter = False
    if starter_field:
        cursor.execute(
            f"SELECT {starter_field} FROM wp_team_{team} WHERE season = %s AND week = %s",
            (year, week)
        )
        result = cursor.fetchone()
        if result and result[0] == player_id:
            is_starter = True
    
    # If player is a starter, check team score validation impact
    if is_starter and points_delta != 0:
        # Get current team points
        cursor.execute(
            f"SELECT points, vs_points, result FROM wp_team_{team} WHERE season = %s AND week = %s",
            (year, week)
        )
        team_result = cursor.fetchone()
        if team_result:
            current_team_points, current_vs_points, current_result = team_result
            new_team_points = current_team_points + points_delta
            
            warnings.append(
                f"Team score will change: {current_team_points} → {new_team_points} (delta: {points_delta:+d})"
            )
            
            # Check game outcome impact
            old_winner = "tie" if current_result == 0 else (team if current_result > 0 else versus)
            new_result = new_team_points - current_vs_points
            new_winner = "tie" if new_result == 0 else (team if new_result > 0 else versus)
            
            if old_winner != new_winner:
                warnings.append(
                    f"⚠️  GAME OUTCOME WILL CHANGE: {old_winner.upper()} won → {new_winner.upper()} wins"
                )
    
    return {
        'old_nflscore': old_nflscore,
        'new_nflscore': new_nflscore,
        'old_points': old_points,
        'new_points': new_points,
        'points_delta': points_delta,
        'is_starter': is_starter,
        'warnings': warnings
    }

def modify_player_data(game_data, auto_yes=False):
    """Prompt user to modify player statistics
    
    Args:
        game_data: Dictionary containing current game data
        auto_yes: Boolean to skip the initial prompt
    
    Returns:
        Dictionary with 'field', 'old_value', 'new_value' or None if cancelled
        For NFL score acceptance, returns special dict with 'accept_nfl_score': True
    """
    print("\n" + "=" * 80)
    
    if auto_yes:
        response = 'yes'
    else:
        response = input("\nDo you want to modify player data? (yes/no): ").strip().lower()
    
    if response not in ['yes', 'y']:
        return None
    
    # Display modifiable fields
    print("\n" + format_subsection("Select Field to Modify"))
    for i, (field, label) in enumerate(MODIFIABLE_FIELDS, 1):
        current_value = game_data.get(field) or 0
        print(f"{i:2d}. {label:30s} (current: {current_value})")
    
    # Add special option to accept NFL score
    nfl_option_num = len(MODIFIABLE_FIELDS) + 1
    pfl_points = game_data.get('points', 0)
    nfl_score = game_data.get('nflscore', 0)
    score_diff = game_data.get('scorediff', 0)
    print(f"{nfl_option_num:2d}. Accept NFL Score as Correct (PFL: {pfl_points}, NFL: {nfl_score}, Diff: {score_diff})")
    
    # Get field selection
    while True:
        try:
            choice = input("\nEnter field number (or 'cancel' to abort): ").strip()
            if choice.lower() == 'cancel':
                return None
            
            choice_num = int(choice)
            
            # Check if user chose to accept NFL score
            if choice_num == nfl_option_num:
                # Confirm acceptance
                print(f"\n{'-' * 80}")
                print(f"Accept NFL Score as Correct")
                print(f"  Current PFL Points: {pfl_points}")
                print(f"  NFL Expected Score: {nfl_score}")
                print(f"  Change:             {pfl_points} → {nfl_score} (delta: {nfl_score - pfl_points:+d})")
                print(f"{'-' * 80}")
                
                confirm = input("\nConfirm accepting NFL score? (yes/no): ").strip().lower()
                if confirm not in ['yes', 'y']:
                    print("Action cancelled.")
                    return None
                
                return {
                    'accept_nfl_score': True,
                    'old_value': pfl_points,
                    'new_value': nfl_score
                }
            
            if 1 <= choice_num <= len(MODIFIABLE_FIELDS):
                field_name, field_label = MODIFIABLE_FIELDS[choice_num - 1]
                break
            else:
                print(f"Invalid choice. Please enter a number between 1 and {nfl_option_num}")
        except ValueError:
            print("Invalid input. Please enter a number or 'cancel'")
    
    # Get new value
    old_value = game_data.get(field_name) or 0
    while True:
        try:
            new_value_str = input(f"\nEnter new value for {field_label} (current: {old_value}): ").strip()
            new_value = int(new_value_str)
            if new_value < 0:
                print("Value cannot be negative. Please try again.")
                continue
            break
        except ValueError:
            print("Invalid input. Please enter an integer value.")
    
    # Confirm change
    print(f"\n{'-' * 80}")
    print(f"Confirm change:")
    print(f"  Field:     {field_label}")
    print(f"  Old Value: {old_value}")
    print(f"  New Value: {new_value}")
    print(f"{'-' * 80}")
    
    confirm = input("\nConfirm this change? (yes/no): ").strip().lower()
    if confirm not in ['yes', 'y']:
        print("Change cancelled.")
        return None
    
    return {
        'field': field_name,
        'field_label': field_label,
        'old_value': old_value,
        'new_value': new_value
    }

def display_team_lineup(cursor, team_abbr, year, week, label):
    """Display team lineup for the week"""
    week_id = int(f"{year:04d}{week:02d}")
    team_table = f"wp_team_{team_abbr}"
    
    # Check if team table exists
    cursor.execute(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'local' AND table_name = %s",
        (team_table,)
    )
    
    if cursor.fetchone()[0] == 0:
        print(f"\nWarning: Team table {team_table} not found")
        return
    
    # Get team data
    cursor.execute(
        f"SELECT * FROM `{team_table}` WHERE season = %s AND week = %s",
        (year, week)
    )
    
    team_game = cursor.fetchone()
    
    if not team_game:
        print(f"\nWarning: No lineup data found for {team_abbr} in {year} week {week}")
        return
    
    # Get column names
    cursor.execute(f"DESCRIBE `{team_table}`")
    columns = [row[0] for row in cursor.fetchall()]
    
    team_data = dict(zip(columns, team_game))
    
    print(format_section_header(f"{label} - {team_abbr}"))
    
    # Game result
    print(format_subsection("Game Result"))
    print(f"Score:         {team_data['points']} - {team_data['vs_points']} vs {team_data['vs']}")
    print(f"Location:      {'Home' if team_data['home_away'] == 'H' else 'Away'} @ {team_data['stadium']}")
    # Result is point differential
    if team_data['result'] > 0:
        print(f"Result:        Win by {team_data['result']}")
    elif team_data['result'] < 0:
        print(f"Result:        Loss by {abs(team_data['result'])}")
    else:
        print(f"Result:        Regulation Tie (0)")
    if team_data['overtime']:
        print(f"Overtime:      Yes{' (Extra OT)' if team_data.get('extra_ot') else ''}")
    
    # Lineup
    print(format_subsection("Starting Lineup"))
    
    positions = [
        ('QB1', 'QB2', 'Quarterback'),
        ('RB1', 'RB2', 'Running Back'),
        ('WR1', 'WR2', 'Wide Receiver'),
        ('PK1', 'PK2', 'Placekicker')
    ]
    
    for pos1, pos2, label in positions:
        starters = []
        
        if team_data[pos1]:
            # Get player name
            cursor.execute(
                "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s",
                (team_data[pos1],)
            )
            player = cursor.fetchone()
            if player:
                starters.append(f"{player[0]} {player[1]} ({team_data[pos1]})")
            else:
                starters.append(team_data[pos1])
        
        if team_data[pos2]:
            cursor.execute(
                "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s",
                (team_data[pos2],)
            )
            player = cursor.fetchone()
            if player:
                starters.append(f"{player[0]} {player[1]} ({team_data[pos2]})")
            else:
                starters.append(team_data[pos2])
        
        if starters:
            print(f"{label:15} {', '.join(starters)}")
        else:
            print(f"{label:15} (None)")

def main():
    """Main function"""
    if len(sys.argv) < 4 or len(sys.argv) > 5:
        print("Usage: python3 player_boxscore.py <player_id> <year> <week> [modify]")
        print("\nExample:")
        print("  python3 player_boxscore.py 1993BoniPK 1995 2")
        sys.exit(1)
    
    player_id = sys.argv[1]
    try:
        year = int(sys.argv[2])
        week = int(sys.argv[3])
    except ValueError:
        print("Error: Year and week must be integers")
        sys.exit(1)
    
    auto_yes = False
    if len(sys.argv) == 5:
        if sys.argv[4].lower() in ['yes', 'y', 'modify']:
            auto_yes = True
    
    # Connect to database
    conn = get_db_connection()
    cursor = conn.cursor(buffered=True)
    
    try:
        # Display player information
        player_info = display_player_info(cursor, player_id)
        if not player_info:
            sys.exit(1)
        
        # Display player's game boxscore
        game_data = display_game_boxscore(cursor, player_id, year, week)
        if not game_data:
            sys.exit(1)
        
        # Validate player's team score
        player_team_validation = validate_team_score(cursor, game_data['team'], year, week)
        
        # Validate opponent's team score
        opponent_team_validation = validate_team_score(cursor, game_data['versus'], year, week)
        
        # Display player's team lineup
        display_team_lineup(cursor, game_data['team'], year, week, "PLAYER'S TEAM LINEUP")
        
        # Display score validation for player's team
        if player_team_validation:
            print(format_subsection("Score Validation"))
            print(f"Team Score:    {player_team_validation['team_points']}")
            print(f"Starters Total: {player_team_validation['starter_total']}")
            if player_team_validation['overtime']:
                print(f"OT Bonus:      {player_team_validation['ot_bonus']} (Winner gets +1)")
            print(f"Expected:      {player_team_validation['expected_total']}")
            print(f"Difference:    {player_team_validation['difference']}")
            
            if not player_team_validation['is_valid']:
                print("\n" + "!" * 80)
                print("WARNING: TEAM SCORE MISMATCH DETECTED!".center(80))
                print("!" * 80)
                ot_text = f" + {player_team_validation['ot_bonus']} (OT)" if player_team_validation['ot_bonus'] else ""
                print(f"\nThe team score ({player_team_validation['team_points']}) does not match the expected total ({player_team_validation['expected_total']}):")
                print(f"  Starters: {player_team_validation['starter_total']}{ot_text}")
                print(f"  Difference: {player_team_validation['difference']} points\n")
                print("Starter breakdown:")
                for detail in player_team_validation['starter_details']:
                    print(f"  {detail}")
                print("\n" + "!" * 80)
        
        # Display opponent's team lineup
        display_team_lineup(cursor, game_data['versus'], year, week, "OPPONENT'S TEAM LINEUP")
        
        # Display score validation for opponent's team
        if opponent_team_validation:
            print(format_subsection("Score Validation"))
            print(f"Team Score:    {opponent_team_validation['team_points']}")
            print(f"Starters Total: {opponent_team_validation['starter_total']}")
            if opponent_team_validation['overtime']:
                print(f"OT Bonus:      {opponent_team_validation['ot_bonus']} (Winner gets +1)")
            print(f"Expected:      {opponent_team_validation['expected_total']}")
            print(f"Difference:    {opponent_team_validation['difference']}")
            
            if not opponent_team_validation['is_valid']:
                print("\n" + "!" * 80)
                print("WARNING: TEAM SCORE MISMATCH DETECTED!".center(80))
                print("!" * 80)
                ot_text = f" + {opponent_team_validation['ot_bonus']} (OT)" if opponent_team_validation['ot_bonus'] else ""
                print(f"\nThe team score ({opponent_team_validation['team_points']}) does not match the expected total ({opponent_team_validation['expected_total']}):")
                print(f"  Starters: {opponent_team_validation['starter_total']}{ot_text}")
                print(f"  Difference: {opponent_team_validation['difference']} points\n")
                print("Starter breakdown:")
                for detail in opponent_team_validation['starter_details']:
                    print(f"  {detail}")
                print("\n" + "!" * 80)
        
        print("\n" + "=" * 80 + "\n")
        
        # Prompt for data modification
        modification = modify_player_data(game_data, auto_yes=auto_yes)
        
        if modification:
            # Check if user is accepting NFL score as correct
            if modification.get('accept_nfl_score'):
                # Special handling for NFL score acceptance
                points_delta = modification['new_value'] - modification['old_value']
                
                # Build a simplified impacts dict
                impacts = {
                    'old_nflscore': game_data['nflscore'],
                    'new_nflscore': game_data['nflscore'],  # Stays the same
                    'old_points': modification['old_value'],
                    'new_points': modification['new_value'],
                    'points_delta': points_delta,
                    'is_starter': False,  # Will be recalculated below
                    'warnings': []
                }
                
                # Check if player is a starter
                team = game_data['team']
                versus = game_data['versus']
                position = player_id[-2:]
                starter_field = {'QB': 'QB1', 'RB': 'RB1', 'WR': 'WR1', 'PK': 'PK1'}.get(position)
                
                if starter_field:
                    cursor.execute(
                        f"SELECT {starter_field} FROM wp_team_{team} WHERE season = %s AND week = %s",
                        (year, week)
                    )
                    result = cursor.fetchone()
                    if result and result[0] == player_id:
                        impacts['is_starter'] = True
                
                # Check team impact if starter
                if impacts['is_starter'] and points_delta != 0:
                    cursor.execute(
                        f"SELECT points, vs_points, result FROM wp_team_{team} WHERE season = %s AND week = %s",
                        (year, week)
                    )
                    team_result = cursor.fetchone()
                    if team_result:
                        current_team_points, current_vs_points, current_result = team_result
                        new_team_points = current_team_points + points_delta
                        
                        impacts['warnings'].append(
                            f"Team score will change: {current_team_points} → {new_team_points} (delta: {points_delta:+d})"
                        )
                        
                        # Check game outcome impact
                        old_winner = "tie" if current_result == 0 else (team if current_result > 0 else versus)
                        new_result = new_team_points - current_vs_points
                        new_winner = "tie" if new_result == 0 else (team if new_result > 0 else versus)
                        
                        if old_winner != new_winner:
                            impacts['warnings'].append(
                                f"⚠️  GAME OUTCOME WILL CHANGE: {old_winner.upper()} won → {new_winner.upper()} wins"
                            )
                
                # Display impact summary
                print("\n" + "=" * 80)
                print("NFL SCORE ACCEPTANCE IMPACT SUMMARY".center(80))
                print("=" * 80)
                print(f"\nAction:        Accept NFL Score as Correct")
                print(f"Change:        PFL Points {modification['old_value']} → {modification['new_value']} (delta: {points_delta:+d})")
                print(f"NFL Score:     {impacts['new_nflscore']} (unchanged)")
                print(f"New Scorediff: 0 (PFL will match NFL)")
                
                if impacts['is_starter']:
                    print(f"\nPlayer is a starter - team scores will be affected.")
                
                if impacts['warnings']:
                    print("\n" + "-" * 80)
                    print("WARNINGS:")
                    for warning in impacts['warnings']:
                        print(f"  {warning}")
                    print("-" * 80)
                
                # Final confirmation
                final_confirm = input("\nProceed with accepting NFL score? (yes/no): ").strip().lower()
                
                if final_confirm in ['yes', 'y']:
                    # Apply the change using a special modification dict
                    nfl_modification = {
                        'field': 'points',
                        'field_label': 'PFL Points (accepting NFL score)',
                        'old_value': modification['old_value'],
                        'new_value': modification['new_value'],
                        'accept_nfl_score': True
                    }
                    
                    if apply_player_modification(cursor, conn, player_id, game_data, nfl_modification, impacts, year, week):
                        print("\n✓ NFL score accepted and applied successfully!")
                        print("\nUpdated boxscore:")
                        print("=" * 80)
                        
                        # Re-display updated information
                        game_data = display_game_boxscore(cursor, player_id, year, week)
                        
                        # Re-validate scores
                        player_team_validation = validate_team_score(cursor, game_data['team'], year, week)
                        display_team_lineup(cursor, game_data['team'], year, week, "PLAYER'S TEAM LINEUP")
                        
                        if player_team_validation:
                            print(format_subsection("Score Validation"))
                            print(f"Team Score:    {player_team_validation['team_points']}")
                            print(f"Starters Total: {player_team_validation['starter_total']}")
                            if player_team_validation['overtime']:
                                print(f"OT Bonus:      {player_team_validation['ot_bonus']} (Winner gets +1)")
                            print(f"Expected:      {player_team_validation['expected_total']}")
                            print(f"Difference:    {player_team_validation['difference']}")
                            
                            if not player_team_validation['is_valid']:
                                print("\n" + "!" * 80)
                                print("WARNING: TEAM SCORE MISMATCH DETECTED!".center(80))
                                print("!" * 80)
                                ot_text = f" + {player_team_validation['ot_bonus']} (OT)" if player_team_validation['ot_bonus'] else ""
                                print(f"\nThe team score ({player_team_validation['team_points']}) does not match the expected total ({player_team_validation['expected_total']}):")
                                print(f"  Starters: {player_team_validation['starter_total']}{ot_text}")
                                print(f"  Difference: {player_team_validation['difference']} points\n")
                                print("Starter breakdown:")
                                for detail in player_team_validation['starter_details']:
                                    print(f"  {detail}")
                                print("\n" + "!" * 80)
                        
                        print("\n" + "=" * 80 + "\n")
                    else:
                        print("\n✗ Failed to accept NFL score.")
                else:
                    print("\nAction cancelled.")
                
                # Exit early since we handled the NFL score acceptance
                return
            
            # Regular field modification handling
            # Check impacts of the modification
            impacts = check_modification_impacts(cursor, player_id, game_data, modification, year, week)
            
            # Display impact summary
            print("\n" + "=" * 80)
            print("MODIFICATION IMPACT SUMMARY".center(80))
            print("=" * 80)
            print(f"\nField:         {modification['field_label']}")
            print(f"Change:        {modification['old_value']} → {modification['new_value']}")
            print(f"\nPlayer Score:  {impacts['old_points']} → {impacts['new_points']} (delta: {impacts['points_delta']:+d})")
            print(f"NFL Score:     {impacts['old_nflscore']} → {impacts['new_nflscore']}")
            
            if impacts['is_starter']:
                print(f"\nPlayer is a starter - team scores will be affected.")
            
            if impacts['warnings']:
                print("\n" + "-" * 80)
                print("WARNINGS:")
                for warning in impacts['warnings']:
                    print(f"  {warning}")
                print("-" * 80)
            
            # Final confirmation
            final_confirm = input("\nProceed with modification? (yes/no): ").strip().lower()
            
            if final_confirm in ['yes', 'y']:
                if apply_player_modification(cursor, conn, player_id, game_data, modification, impacts, year, week):
                    print("\n✓ Modification applied successfully!")
                    print("\nUpdated boxscore:")
                    print("=" * 80)
                    
                    # Re-display updated information
                    game_data = display_game_boxscore(cursor, player_id, year, week)
                    
                    # Re-validate scores
                    player_team_validation = validate_team_score(cursor, game_data['team'], year, week)
                    display_team_lineup(cursor, game_data['team'], year, week, "PLAYER'S TEAM LINEUP")
                    
                    if player_team_validation:
                        print(format_subsection("Score Validation"))
                        print(f"Team Score:    {player_team_validation['team_points']}")
                        print(f"Starters Total: {player_team_validation['starter_total']}")
                        if player_team_validation['overtime']:
                            print(f"OT Bonus:      {player_team_validation['ot_bonus']} (Winner gets +1)")
                        print(f"Expected:      {player_team_validation['expected_total']}")
                        print(f"Difference:    {player_team_validation['difference']}")
                        
                        if not player_team_validation['is_valid']:
                            print("\n" + "!" * 80)
                            print("WARNING: TEAM SCORE MISMATCH DETECTED!".center(80))
                            print("!" * 80)
                            ot_text = f" + {player_team_validation['ot_bonus']} (OT)" if player_team_validation['ot_bonus'] else ""
                            print(f"\nThe team score ({player_team_validation['team_points']}) does not match the expected total ({player_team_validation['expected_total']}):")
                            print(f"  Starters: {player_team_validation['starter_total']}{ot_text}")
                            print(f"  Difference: {player_team_validation['difference']} points\n")
                            print("Starter breakdown:")
                            for detail in player_team_validation['starter_details']:
                                print(f"  {detail}")
                            print("\n" + "!" * 80)
                    
                    print("\n" + "=" * 80 + "\n")
                else:
                    print("\n✗ Failed to apply modification.")
            else:
                print("\nModification cancelled.")
        
    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    main()
