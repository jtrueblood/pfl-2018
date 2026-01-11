#!/usr/bin/env python3
"""
Test different 1991 scoring formulas to find the best fit
"""

import mysql.connector
from mysql.connector import Error

# Database configuration
MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

DB_CONFIG = {
    'host': 'localhost',
    'database': 'local',
    'user': 'root',
    'password': 'root',
    'unix_socket': MYSQL_SOCKET
}

def calculate_score_with_formula(pass_yds, pass_td, rush_yds, rush_td, rec_yds, rec_td, pass_int, 
                                  pass_divisor, rush_divisor, rec_divisor, td_multiplier):
    """Calculate score with given formula parameters"""
    # Handle None/NULL values
    if pass_int is None or pass_int < 0:
        pass_int = 0
    if pass_yds is None or pass_yds < 0:
        pass_yds = 0
    if rush_yds is None or rush_yds < 0:
        rush_yds = 0
    if rec_yds is None or rec_yds < 0:
        rec_yds = 0
    if pass_td is None:
        pass_td = 0
    if rush_td is None:
        rush_td = 0
    if rec_td is None:
        rec_td = 0
    
    pass_get = pass_yds // pass_divisor
    if pass_get < 0:
        pass_data = 0
    else:
        pass_data = pass_get
    
    score = pass_data + (rush_yds // rush_divisor) + (rec_yds // rec_divisor) + ((pass_td + rush_td + rec_td) * td_multiplier) - pass_int
    return score

def test_formula(player_id, pass_divisor, rush_divisor, rec_divisor, td_multiplier):
    """Test a specific formula against player data"""
    connection = None
    
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        cursor = connection.cursor()
        
        # Get 1991 data for the player
        query = f"""
            SELECT week, pass_yds, pass_td, pass_int, rush_yds, rush_td, rec_yds, rec_td, points
            FROM `{player_id}`
            WHERE year = 1991
            ORDER BY week
        """
        
        cursor.execute(query)
        rows = cursor.fetchall()
        
        if not rows:
            print(f"No data found for {player_id} in 1991")
            return None
        
        results = []
        total_diff = 0
        total_abs_diff = 0
        
        for row in rows:
            week = row[0]
            pass_yds = row[1]
            pass_td = row[2]
            pass_int = row[3]
            rush_yds = row[4]
            rush_td = row[5]
            rec_yds = row[6]
            rec_td = row[7]
            pfl_points = row[8] if row[8] is not None else 0
            
            nfl_score = calculate_score_with_formula(
                pass_yds, pass_td, rush_yds, rush_td, rec_yds, rec_td, pass_int,
                pass_divisor, rush_divisor, rec_divisor, td_multiplier
            )
            
            diff = pfl_points - nfl_score
            total_diff += diff
            total_abs_diff += abs(diff)
            
            results.append({
                'week': week,
                'nfl_score': nfl_score,
                'pfl_score': pfl_points,
                'diff': diff
            })
        
        cursor.close()
        connection.close()
        
        avg_diff = total_diff / len(results)
        avg_abs_diff = total_abs_diff / len(results)
        
        return {
            'results': results,
            'avg_diff': avg_diff,
            'avg_abs_diff': avg_abs_diff,
            'total_diff': total_diff,
            'total_abs_diff': total_abs_diff,
            'num_weeks': len(results)
        }
        
    except Error as e:
        print(f"Database error: {e}")
        return None
    
    finally:
        if connection and connection.is_connected():
            connection.close()

def get_1991_players():
    """Get list of 1991 QB players from database"""
    connection = None
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        cursor = connection.cursor()
        
        query = """
            SELECT p_id, position 
            FROM wp_players 
            WHERE p_id LIKE '1991%' 
            AND position = 'QB'
            ORDER BY p_id
        """
        
        cursor.execute(query)
        players = cursor.fetchall()
        
        cursor.close()
        connection.close()
        
        return players
        
    except Error as e:
        print(f"Database error: {e}")
        return []
    
    finally:
        if connection and connection.is_connected():
            connection.close()

def main():
    # Get all 1991 RB/WR players
    players = get_1991_players()
    
    if not players:
        print("No 1991 RB/WR players found")
        return
    
    print("="*80)
    print(f"Testing 1991 Scoring Formulas for {len(players)} Players")
    print("="*80)
    print()
    
    # Test different formula combinations
    test_cases = [
        # (pass_div, rush_div, rec_div, td_mult, description)
        (50, 25, 25, 2, "Original (pass/50, rush/25, rec/25, TD*2)"),
        (50, 20, 20, 2, "Current (pass/50, rush/20, rec/20, TD*2)"),
        (50, 15, 15, 2, "Latest (pass/50, rush/15, rec/15, TD*2)"),
        (50, 25, 25, 3, "TD*3 (pass/50, rush/25, rec/25, TD*3)"),
        (50, 20, 20, 3, "TD*3 (pass/50, rush/20, rec/20, TD*3)"),
        (50, 15, 15, 3, "TD*3 (pass/50, rush/15, rec/15, TD*3)"),
        (50, 25, 25, 4, "TD*4 (pass/50, rush/25, rec/25, TD*4)"),
        (50, 20, 20, 4, "TD*4 (pass/50, rush/20, rec/20, TD*4)"),
        (50, 15, 15, 4, "TD*4 (pass/50, rush/15, rec/15, TD*4)"),
    ]
    
    # Track results for each formula
    formula_results = {}
    for test_case in test_cases:
        formula_results[test_case[4]] = {
            'total_abs_diff': 0,
            'total_players': 0,
            'params': test_case[:4]
        }
    
    # Test each player against each formula
    for player_id, position in players:
        print(f"\nTesting {player_id} ({position})...")
        
        best_for_player = None
        best_abs_diff = float('inf')
        
        for pass_div, rush_div, rec_div, td_mult, description in test_cases:
            result = test_formula(player_id, pass_div, rush_div, rec_div, td_mult)
            
            if result and result['num_weeks'] > 0:
                formula_results[description]['total_abs_diff'] += result['avg_abs_diff']
                formula_results[description]['total_players'] += 1
                
                if result['avg_abs_diff'] < best_abs_diff:
                    best_abs_diff = result['avg_abs_diff']
                    best_for_player = (description, result['avg_abs_diff'])
        
        if best_for_player:
            print(f"  Best: {best_for_player[0]} (avg abs diff: {best_for_player[1]:.2f})")
    
    # Calculate average for each formula
    print("\n" + "="*80)
    print("OVERALL RESULTS (Average across all players)")
    print("="*80)
    
    best_overall = None
    best_overall_avg = float('inf')
    
    for description, data in formula_results.items():
        if data['total_players'] > 0:
            avg = data['total_abs_diff'] / data['total_players']
            print(f"\n{description}")
            print(f"  Average absolute difference: {avg:.2f}")
            print(f"  Players tested: {data['total_players']}")
            
            if avg < best_overall_avg:
                best_overall_avg = avg
                best_overall = (description, avg, data['params'])
    
    if best_overall:
        print("\n" + "="*80)
        print("BEST OVERALL FORMULA:")
        print("="*80)
        description, avg, params = best_overall
        pass_div, rush_div, rec_div, td_mult = params
        print(f"\n{description}")
        print(f"  Pass yards: 1 point per {pass_div} yards")
        print(f"  Rush yards: 1 point per {rush_div} yards")
        print(f"  Rec yards: 1 point per {rec_div} yards")
        print(f"  Touchdowns: {td_mult} points each")
        print(f"\n  Average absolute difference: {avg:.2f}")

if __name__ == "__main__":
    main()
