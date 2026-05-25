#!/usr/bin/env python3
"""
Correct the final score of a team's game and update both teams' records.

This script adjusts the 'points', 'result', 'vs_points' fields in team tables
and alerts the user if the game outcome changes.

Usage:
  python3 correct_game_score.py YEAR WEEK TEAM NEW_SCORE
  
Example: 
  python3 correct_game_score.py 2008 5 SNR 24
"""

import mysql.connector
import sys

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


def get_game_data(conn, team_id, year, week):
    """Get the game record for a specific team, year, and week"""
    table_name = f"wp_team_{team_id}"
    
    try:
        query = """
            SELECT season, week, team_int, points, vs, vs_points, result
            FROM {table}
            WHERE season = %s AND week = %s
        """.format(table=table_name)
        
        cursor = conn.cursor(dictionary=True)
        cursor.execute(query, (year, week))
        game = cursor.fetchone()
        cursor.close()
        
        return game
    except mysql.connector.Error as e:
        print(f"❌ Error fetching game data from {table_name}: {e}")
        return None


def update_game_score(conn, team_id, year, week, new_score, score_change):
    """Update the score and result for a team's game"""
    table_name = f"wp_team_{team_id}"
    
    try:
        update_query = """
            UPDATE {table}
            SET points = %s,
                result = result + %s
            WHERE season = %s AND week = %s
        """.format(table=table_name)
        
        cursor = conn.cursor()
        cursor.execute(update_query, (new_score, score_change, year, week))
        rows_affected = cursor.rowcount
        cursor.close()
        
        return rows_affected > 0
    except mysql.connector.Error as e:
        print(f"❌ Error updating {table_name}: {e}")
        return False


def update_opponent_score(conn, opponent_id, year, week, new_vs_points, score_change):
    """Update the opponent's vs_points and result"""
    table_name = f"wp_team_{opponent_id}"
    
    try:
        update_query = """
            UPDATE {table}
            SET vs_points = %s,
                result = result - %s
            WHERE season = %s AND week = %s
        """.format(table=table_name)
        
        cursor = conn.cursor()
        cursor.execute(update_query, (new_vs_points, score_change, year, week))
        rows_affected = cursor.rowcount
        cursor.close()
        
        return rows_affected > 0
    except mysql.connector.Error as e:
        print(f"❌ Error updating opponent {table_name}: {e}")
        return False


def determine_outcome(result):
    """Determine game outcome from result value"""
    if result > 0:
        return "WIN"
    elif result < 0:
        return "LOSS"
    else:
        return "TIE"


def correct_game_score(year, week, team_id, new_score):
    """
    Correct a team's game score and update both teams' records.
    
    Steps:
    1. Fetch the current game record
    2. Calculate the score change
    3. Update team's points and result
    4. Update opponent's vs_points and result
    5. Alert if outcome changes
    """
    try:
        conn = get_db_connection()
        
        print("\n" + "=" * 70)
        print("GAME SCORE CORRECTION")
        print("=" * 70)
        print(f"\nYear: {year}")
        print(f"Week: {week}")
        print(f"Team: {team_id}")
        print(f"New Score: {new_score}")
        print("-" * 70)
        
        # Verify team table exists
        table_name = f"wp_team_{team_id}"
        try:
            cursor = conn.cursor()
            cursor.execute(f"SELECT COUNT(*) FROM {table_name}")
            cursor.fetchone()
            cursor.close()
        except mysql.connector.Error:
            print(f"\n❌ Error: Table '{table_name}' not found")
            print(f"   Team ID should be like: SNR, ETS, ATK, etc.")
            conn.close()
            return False
        
        # Get current game data
        game = get_game_data(conn, team_id, year, week)
        
        if not game:
            print(f"\n❌ No game record found for {team_id} in {year} Week {week}")
            conn.close()
            return False
        
        # Extract current values
        old_score = game['points']
        old_result = game['result']
        opponent_id = game['vs']
        opponent_score = game['vs_points']
        
        # Calculate changes
        score_change = new_score - old_score
        new_result = old_result + score_change
        
        # Determine outcomes
        old_outcome = determine_outcome(old_result)
        new_outcome = determine_outcome(new_result)
        outcome_changed = old_outcome != new_outcome
        
        # Display current state
        print(f"\n📊 CURRENT STATE:")
        print(f"   {team_id}: {old_score} points")
        print(f"   {opponent_id}: {opponent_score} points")
        print(f"   Result: {old_result} ({old_outcome})")
        
        # Display proposed changes
        print(f"\n📝 PROPOSED CHANGES:")
        print(f"   {team_id} score: {old_score} → {new_score} (change: {score_change:+d})")
        print(f"   {team_id} result: {old_result} → {new_result} (change: {score_change:+d})")
        print(f"   {opponent_id} result: will change by {-score_change:+d}")
        
        # Alert if outcome changes
        if outcome_changed:
            print(f"\n⚠️  WARNING: GAME OUTCOME WILL CHANGE!")
            print(f"   Old outcome: {old_outcome}")
            print(f"   New outcome: {new_outcome}")
        else:
            print(f"\n✓ Game outcome remains: {new_outcome}")
        
        # Get opponent's current game data for verification
        opponent_game = get_game_data(conn, opponent_id, year, week)
        if opponent_game:
            opponent_old_result = opponent_game['result']
            opponent_new_result = opponent_old_result - score_change
            print(f"\n📊 OPPONENT ({opponent_id}) CHANGES:")
            print(f"   vs_points: {old_score} → {new_score}")
            print(f"   result: {opponent_old_result} → {opponent_new_result}")
        
        # Confirm changes
        print("\n" + "-" * 70)
        confirm = input("Apply these changes to the database? (yes/no): ").strip().lower()
        
        if confirm not in ['yes', 'y']:
            print("\n❌ Changes cancelled by user")
            conn.close()
            return False
        
        # Apply changes
        print("\n🔄 Applying changes...")
        
        # Update team's score and result
        if not update_game_score(conn, team_id, year, week, new_score, score_change):
            print(f"❌ Failed to update {team_id}")
            conn.rollback()
            conn.close()
            return False
        
        print(f"✓ Updated {team_id} score and result")
        
        # Update opponent's vs_points and result
        if not update_opponent_score(conn, opponent_id, year, week, new_score, score_change):
            print(f"❌ Failed to update opponent {opponent_id}")
            conn.rollback()
            conn.close()
            return False
        
        print(f"✓ Updated {opponent_id} vs_points and result")
        
        # Commit all changes
        conn.commit()
        
        # Verify final state
        final_game = get_game_data(conn, team_id, year, week)
        final_opponent = get_game_data(conn, opponent_id, year, week)
        
        print(f"\n✅ CHANGES APPLIED SUCCESSFULLY!")
        print(f"\n📊 FINAL STATE:")
        print(f"   {team_id}: {final_game['points']} points, result: {final_game['result']} ({determine_outcome(final_game['result'])})")
        print(f"   {opponent_id}: {final_opponent['points']} points, result: {final_opponent['result']} ({determine_outcome(final_opponent['result'])})")
        
        conn.close()
        print("\n" + "=" * 70)
        return True
        
    except mysql.connector.Error as e:
        print(f"\n❌ Database error: {e}")
        if 'conn' in locals():
            conn.rollback()
            conn.close()
        return False
    except Exception as e:
        print(f"\n❌ Unexpected error: {e}")
        if 'conn' in locals():
            conn.close()
        return False


def main():
    """Main function to handle command-line arguments"""
    if len(sys.argv) != 5:
        print("\n❌ Invalid usage")
        print("\nUsage: python3 correct_game_score.py YEAR WEEK TEAM NEW_SCORE")
        print("\nExample: python3 correct_game_score.py 2008 5 SNR 24")
        print("\nThis will:")
        print("  - Update SNR's score to 24 for 2008 Week 5")
        print("  - Adjust SNR's result by the score difference")
        print("  - Update opponent's vs_points and result accordingly")
        print("  - Alert if the game outcome changes (win/loss/tie)")
        sys.exit(1)
    
    year = int(sys.argv[1])
    week = int(sys.argv[2])
    team_id = sys.argv[3].upper()
    new_score = int(sys.argv[4])
    
    success = correct_game_score(year, week, team_id, new_score)
    sys.exit(0 if success else 1)


if __name__ == "__main__":
    main()
