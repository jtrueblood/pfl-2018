# Fix NULL NFL Scores Script

## Purpose
This script identifies and fixes player table rows where the `nflscore` column is NULL, even though NFL game data exists. This commonly happens when:
- Data was imported before the scoring calculation was implemented
- The scoring function had bugs (like the 1991 bug that was just fixed)
- Database updates were incomplete

## What It Does
1. Scans all player tables for rows with NULL `nflscore` values
2. Recalculates the expected NFL score using the same logic as the PHP functions
3. Applies any score corrections from `wp_score_correct` table
4. Updates both `nflscore` and `scorediff` columns
5. Shows before/after comparison for each fix

## Usage

### Dry Run - All Players (Preview Only)
```bash
python3 fix_null_nfl_scores.py
```
This will show you what would be changed for ALL players WITHOUT making any changes to the database.

### Dry Run - Single Player (Preview Only)
```bash
python3 fix_null_nfl_scores.py 1991ClakWR
```
or
```bash
python3 fix_null_nfl_scores.py --player=1991ClakWR
```
This will show you what would be changed for a specific player WITHOUT making any changes.

### Execute Mode - All Players (Apply Changes)
```bash
python3 fix_null_nfl_scores.py --execute
```
This will actually update the database with the calculated values for ALL players.

### Execute Mode - Single Player (Apply Changes)
```bash
python3 fix_null_nfl_scores.py 1991ClakWR --execute
```
or
```bash
python3 fix_null_nfl_scores.py --execute --player=1991ClakWR
```
This will actually update the database for the specified player only.

## Example Output

### Single Player Mode
```
$ python3 fix_null_nfl_scores.py 1991ClakWR

DRY RUN MODE: No changes will be made. Use --execute to apply changes.
Target: Single player (1991ClakWR)
--------------------------------------------------------------------------------

1991ClakWR (WR) - 14 NULL scores found:
  Year: 1991, Week: 1 | NFL Expected: 8 | PFL Actual: 8 | Difference: 0
  Year: 1991, Week: 2 | NFL Expected: 6 | PFL Actual: 6 | Difference: 0
  Year: 1991, Week: 3 | NFL Expected: 10 | PFL Actual: 10 | Difference: 0
  Year: 1991, Week: 4 | NFL Expected: 4 | PFL Actual: 4 | Difference: 0
  ...

✓ Found 14 NULL records across 1 players

Run with --execute flag to apply these changes to the database.
```

### All Players Mode
```
$ python3 fix_null_nfl_scores.py

DRY RUN MODE: No changes will be made. Use --execute to apply changes.
Target: All players
--------------------------------------------------------------------------------

1991ClakWR (WR) - 14 NULL scores found:
  Year: 1991, Week: 1 | NFL Expected: 8 | PFL Actual: 8 | Difference: 0
  ...

1991AikmQB (QB) - 16 NULL scores found:
  Year: 1991, Week: 1 | NFL Expected: 12 | PFL Actual: 12 | Difference: 0
  ...

✓ Found 247 NULL records across 23 players

Run with --execute flag to apply these changes to the database.
```

## How It Works

### Scoring Calculation
The script uses the same formulas as the PHP `pos_score_converter()` and `pk_score_converter()` functions:

**For 1991:**
- Passing: yards / 50 (floor)
- Rushing: yards / 25 (floor)
- Receiving: yards / 25 (floor)
- All TDs: 2 points each
- Interceptions: -1 point each

**For 1992+:**
- Passing: yards / 30 (floor)
- Rushing: yards / 10 (floor)
- Receiving: yards / 10 (floor)
- All TDs: 2 points each
- Interceptions: -1 point each

**For Kickers (all years):**
- Extra Points: 1 point each
- Field Goals: 2 points each

### Score Corrections
The script automatically applies any corrections from the `wp_score_correct` table (for 2-point conversions, special teams TDs, etc.)

## Requirements
- Python 3.6+
- mysql-connector-python package
```bash
pip3 install mysql-connector-python
```

## Database Configuration
Edit the `DB_CONFIG` dictionary in the script if your database settings differ:
```python
DB_CONFIG = {
    'host': 'localhost',
    'database': 'pfl_data',
    'user': 'root',
    'password': 'root'
}
```

## Integration with Error Check Page
After running this script with `--execute`, refresh the Error Check page to see:
- Reduced number of errors in "Other Point Difference Issues"
- NULL indicators removed from fixed entries
- Remaining issues that need manual review

## Safety Features
- **Dry run by default**: Must explicitly use `--execute` to make changes
- **Table validation**: Checks if player tables exist before querying
- **NULL handling**: Safely handles missing/NULL values in stat columns
- **Transaction support**: Uses MySQL commits for data integrity
