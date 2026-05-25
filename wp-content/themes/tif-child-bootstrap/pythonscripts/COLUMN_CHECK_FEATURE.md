# Column Checking Feature

## Overview

Both `getplayernfldata.py` and `find_ttt_in_players.py` have been updated to automatically check for required database columns before performing operations. If required columns are missing, the scripts will prompt the user to add them.

## Required Columns

The following columns are required in all player tables:
- `nflteam` (VARCHAR(3)) - NFL team abbreviation
- `game_location` (VARCHAR(3)) - Home/Away indicator
- `nflopp` (VARCHAR(3)) - Opponent team abbreviation

## How It Works

### getplayernfldata.py

When you run the script with the INSERT parameter set to "Yes", it will:

1. Connect to the database
2. Check if the player's table has the required columns
3. If columns are missing:
   - Display which columns are missing
   - Prompt: "Do you want to add these columns to '[table_name]'? (yes/no):"
   - If you answer "yes":
     - Automatically add the missing columns
     - Continue with data insertion
   - If you answer "no":
     - Stop execution and display an error message

**Example:**
```bash
python3 getplayernfldata.py "Josh Allen" 2024 13 Yes

# If columns are missing, you'll see:
# ⚠️  Table '2018AlleQB' is missing columns: nflteam, game_location, nflopp
# Do you want to add these columns to '2018AlleQB'? (yes/no): yes
# 
# Adding missing columns to '2018AlleQB'...
#   ✓ Added column 'nflteam' to table '2018AlleQB'
#   ✓ Added column 'game_location' to table '2018AlleQB'
#   ✓ Added column 'nflopp' to table '2018AlleQB'
# ✓ Columns added successfully
```

### find_ttt_in_players.py

When you run the script, it will:

1. Scan all player tables (ending in QB, RB, WR, or PK)
2. Check each table for the required columns
3. If any tables are missing columns:
   - Display a summary of tables with missing columns
   - Prompt: "Do you want to add missing columns to these [N] table(s)? (yes/no):"
   - If you answer "yes":
     - Automatically add missing columns to all affected tables
     - Continue with TTT search
   - If you answer "no":
     - Skip column additions
     - Continue with TTT search (skipping tables without required columns)

**Example:**
```bash
python3 find_ttt_in_players.py

# If columns are missing, you'll see:
# Checking for missing columns...
# 
# ⚠️  Found 3 table(s) with missing columns:
#   - 2008RoseQB: missing nflteam, game_location, nflopp
#   - 2020MurrQB: missing nflteam, game_location, nflopp
#   - 2021MattRB: missing nflteam
# 
# Do you want to add missing columns to these 3 table(s)? (yes/no): yes
# 
# Adding missing columns...
#   Processing 2008RoseQB...
#     ✓ Added column 'nflteam' to table '2008RoseQB'
#     ✓ Added column 'game_location' to table '2008RoseQB'
#     ✓ Added column 'nflopp' to table '2008RoseQB'
#   ...
# ✓ Column additions completed
```

## Functions Added

Both scripts now include these helper functions:

### `check_required_columns(cursor, table_name)`
- Checks if a table has all required columns
- Returns a list of missing column names

### `add_missing_columns(cursor, connection, table_name, missing_columns)`
- Adds missing columns to a table
- Each column is added as VARCHAR(3) NULL
- Returns True on success, False on failure

## Benefits

1. **Automatic Detection** - No need to manually check which tables need updates
2. **Safe Operations** - Always prompts before making database changes
3. **Batch Processing** - `find_ttt_in_players.py` can update multiple tables at once
4. **Graceful Handling** - Scripts continue to work even if you decline to add columns

## Notes

- Column additions are committed to the database immediately after being added
- If column addition fails, the script will display an error and stop
- All added columns are nullable (NULL allowed) to avoid issues with existing data
- The column check happens before any data insertion attempts
