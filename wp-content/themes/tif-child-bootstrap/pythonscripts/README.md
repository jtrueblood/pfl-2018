# PFL Python Scripts

Collection of Python scripts for managing Posse Football League data.

## Scripts

### 1. getplayernfldata.py - NFL Player Data Fetcher

Fetches NFL player statistics from ESPN's unofficial API and optionally retrieves the player ID from a WordPress database.

### 2. build-single-player-mfl.py - MyFantasyLeague Single Player Data

Fetches MyFantasyLeague player data for a single player and optionally inserts it into the database. This mirrors the functionality of the `build-weekly-mfl.php` page but operates on a single player instead of processing all teams and players in a week.

### 3. update-standings-table.py - End of Season Standings Update

Updates the end-of-season standings table by copying the current year's table to next year and updating the current year with Week 14 results data.

### 4. insert_probowl_leaders.py - Pro Bowl Leaders Insertion

Inserts the top 2 players from each position (QB, RB, WR, PK) for each division (EGAD, DGAS) into the wp_probowlbox table based on season points leaders.

---

## Requirements

```bash
pip install requests mysql-connector-python beautifulsoup4
```

---

## build-single-player-mfl.py Usage

This script fetches MFL (MyFantasyLeague) player scores and matchup data for a single player in a specific week.

```bash
python3 build-single-player-mfl.py <player_identifier> <year> <week>
```

### Parameters

- **player_identifier**: Can be one of three formats:
  - PFL Player ID (e.g., `2018AlleQB`)
  - Player name (e.g., `"Josh Allen"`)
  - MFL ID (numeric, e.g., `14477`)
- **year**: Season year (e.g., `2024`)
- **week**: Week number (1-17)

### Examples

```bash
# Using PFL Player ID
python3 build-single-player-mfl.py 2018AlleQB 2024 13

# Using player name (must be in quotes if it contains spaces)
python3 build-single-player-mfl.py "Josh Allen" 2024 13

# Using MFL ID
python3 build-single-player-mfl.py 14477 2024 13
```

### What the Script Does

1. **Resolves player identifier** - Looks up the player in your wp_players database table
2. **Fetches MFL player score** - Gets the fantasy points from MyFantasyLeague API
3. **Fetches weekly matchup results** - Gets team context (team, opponent, home/away, scores)
4. **Displays data** - Shows all retrieved information in a formatted table
5. **Prompts for confirmation** - Asks if you want to insert the data into the database
6. **Inserts data** - If confirmed, inserts/updates the player's weekly data in their player table

### Output Format

The script displays:
- Player Name
- PFL Player ID
- MFL Player ID
- Season and Week
- MFL Fantasy Points
- Team information (team, opponent, home/away status)
- Game result (scores and win/loss)

### Database Operations

**Regular Season (Weeks 1-14):**
- Inserts data into the player's individual table (e.g., `2018AlleQB`)
- Updates fields: `week_id`, `year`, `week`, `points`, `team`, `versus`, `win_loss`, `home_away`
- If record exists, it updates; otherwise, it inserts a new record

**Playoffs (Weeks 15+):**
- Not yet implemented (requires special seed-based logic)

### Important Notes

- The player must be a **starter** in the MFL league for that week to have team context
- The player must exist in the `wp_players` table
- The script uses the same MFL API credentials as the PHP build script
- Data is only inserted after user confirmation (yes/no prompt)

---

## update-standings-table.py Usage

This script automates the end-of-season standings table update process.

```bash
python3 update-standings-table.py <year>
```

### Parameters

- **year**: Season year (e.g., `2025`)

### Examples

```bash
python3 update-standings-table.py 2025
```

### What the Script Does

**Step 1: Copy Table to Next Year**
- Makes a copy of `stand{year}` table (e.g., `stand2025`)
- Creates new table `stand{year+1}` (e.g., `stand2026`)
- Copies all data and structure to prepare for next season
- Prompts before overwriting if destination table exists

**Step 2: Fetch Week 14 Standings**
- Scrapes the results page: `http://pfl-data.local/results/?Y={year}&W=14`
- Parses both EGAD and DGAS standings tables
- Extracts: Wins, Losses, Win%, Points, PPG, Points Against, +/-, Division Record, Games Back

**Step 3: Display & Confirm**
- Shows formatted standings summary for both divisions
- Prompts for confirmation before updating database

**Step 4: Update Database**
- Updates `stand{year}` table with final Week 14 standings
- Updates fields: division, win, loss, winper, gb, pts, ppg, pts_agst, plus_min, div_win, div_loss
- Shows progress for each team updated

### Output Format

The script displays:
```
======================================================================
STANDINGS DATA RETRIEVED FROM RESULTS PAGE
======================================================================

EGAD Standings:
----------------------------------------------------------------------
Team  W-L     Win%    Pts   PPG    Vs    +/-    Div    GB  
----------------------------------------------------------------------
PEP   9-5     0.643   551   39.4   550   1      5-3    -   
HAT   8-6     0.571   558   39.9   504   54     4-4    1   
...
```

### Database Schema

The script updates the following columns in the `stand{year}` table:
- `division` - Division name (EGAD or DGAS)
- `win` - Total wins
- `loss` - Total losses
- `winper` - Win percentage
- `gb` - Games behind division leader
- `pts` - Total points
- `ppg` - Points per game
- `pts_agst` - Points against
- `plus_min` - Point differential (+/-)
- `div_win` - Division wins
- `div_loss` - Division losses

### Important Notes

- Always run at the end of Week 14 (regular season)
- Requires BeautifulSoup4 for HTML parsing: `pip install beautifulsoup4`
- The script prompts for confirmation before:
  - Overwriting existing next year's table
  - Updating current year's standings
- Team name mapping is hardcoded (update if team names change)
- Does NOT update: playoff_seed, home_win, home_loss (must be set manually)

---

## insert_probowl_leaders.py Usage

This script populates the Pro Bowl roster for a given season by selecting the top 2 players from each of the 4 position categories (QB, RB, WR, PK) for each division (EGAD and DGAS).

```bash
python3 insert_probowl_leaders.py <year>
```

### Parameters

- **year**: Season year (e.g., `2025`)

### Examples

```bash
# Insert Pro Bowl leaders for 2025
python3 insert_probowl_leaders.py 2025

# Insert Pro Bowl leaders for 2026
python3 insert_probowl_leaders.py 2026
```

### What the Script Does

1. **Retrieves team divisions** - Gets division assignments from the standings table
2. **Clears existing Pro Bowl data** - Removes any existing Pro Bowl entries for the current year
3. **Gets top players by position and division** - Queries wp_season_leaders for top scorers
4. **Determines team and division** - Looks up each player's team from their individual player table
5. **Inserts into wp_probowlbox** - Adds players with correct starter/backup designation

### Output Format

The script displays a detailed summary:
```
================================================================================
INSERTING PRO BOWL LEADERS FOR 2025
================================================================================

Getting team divisions...
Found 10 teams

Cleared existing Pro Bowl data for 2025

--------------------------------------------------------------------------------
Processing EGAD Division
--------------------------------------------------------------------------------

QB Leaders:
  #1 (STARTER): 2018AlleQB      | Team: ETS | 200 pts | ID: prb202501
  #2 (BACKUP): 2017GoffQB      | Team: HAT | 129 pts | ID: prb202502
...
```

### Database Operations

**Reads from:**
- `stand{year}` - Team divisions
- `wp_season_leaders` - Season point totals by player
- `{playerid}` - Individual player tables for team assignments

**Writes to:**
- `wp_probowlbox` - Pro Bowl roster with the following fields:
  - `id` - Auto-generated as `prb{year}{##}`
  - `playerid` - Player identifier
  - `pos` - Position (QB, RB, WR, PK)
  - `team` - Player's team
  - `league` - Division (EGAD or DGAS)
  - `year` - Season year
  - `points` - Set to 0 (filled in after Pro Bowl in Week 17)
  - `starter` - 0 for #1 ranked player, 1 for #2 ranked player
  - `ptsused` - Set to 0 (filled in after Pro Bowl in Week 17)

### Starter Logic

- `starter = 0`: The #1 ranked player at that position for the division (the starter)
- `starter = 1`: The #2 ranked player at that position for the division (the backup)
- `starter = 2`: Replacement player (not relevant to this script)

### Important Notes

- Clears ALL existing Pro Bowl data for the specified year before inserting
- Requires that `wp_season_leaders` table is up to date for the specified year
- Player must have played for a team in the league during that season
- Points and ptsused are initialized to 0 and should be updated after the Pro Bowl is played in Week 17
- Year must be between 1991 and 2100

---

## getplayernfldata.py Usage

```bash
python3 getplayernfldata.py "Player Name" YEAR WEEK [INSERT]
```

### Parameters

- **Player Name**: Full name of the player (e.g., "Josh Allen")
- **YEAR**: Season year (e.g., 2024)
- **WEEK**: Week number(s) - can be:
  - Single week: `13`
  - Comma-separated weeks: `"11,12,13"`
  - All weeks player has data for: `all`
- **INSERT**: Optional - `Yes` to insert into database, `No` to just display (default: `No`)

### Examples

```bash
# Get Josh Allen's stats for Week 13, 2024
python3 getplayernfldata.py "Josh Allen" 2024 13

# Get Patrick Mahomes' stats for Week 12, 2024 and insert to database
python3 getplayernfldata.py "Patrick Mahomes" 2024 12 Yes

# Get Justin Tucker's stats for multiple weeks
python3 getplayernfldata.py "Justin Tucker" 2024 "11,12,13" Yes

# Get all weeks of data for Josh Allen in 2024 (uses existing week data from database)
python3 getplayernfldata.py "Josh Allen" 2024 all Yes
```

### 'all' Parameter

When using `all` for the WEEK parameter:
- The script retrieves all weeks the player has in their player table for the specified year
- Only processes weeks where the player has existing data in the database
- Requires the player to exist in the `wp_players` table
- Useful for updating NFL stats for all weeks a player has already played

## Output

The script returns the following statistics for a given player and week:

- **Player ID (p_id)**: From wp_players database table (if database is available)
- **Date of game**: When the game was played
- **Team**: Player's team
- **Versus Team**: Opponent team
- **Home or Away**: Location of game
- **Passing Stats**: Pass Yds, Pass TD, Pass Int
- **Rushing Stats**: Rush Yds, Rush TD
- **Receiving Stats**: Rec Yds, Rec TD
- **Kicking Stats**: XP Made, XP Att, FG Made, FG Att
- **Other**: 2pt conversions
- **NFL Score**: Calculated fantasy score based on NFL stats
- **DIFF**: Difference between PFL score and NFL score (PFL - NFL)

## Database Configuration

The script automatically attempts to:
1. Load database credentials from `wp-config.php`
2. Connect using standard MySQL connection
3. Fall back to Local by Flywheel's Unix socket if standard connection fails

### Database Table Requirements

The script expects a `wp_players` table (or with custom prefix) with at least:
- `p_id` - Player ID (integer)
- `p_name` - Player name (string)

### Manual Database Configuration

If automatic detection doesn't work, you can manually edit the `DB_CONFIG` in the script:

```python
DB_CONFIG = {
    'host': 'localhost',
    'database': 'your_database',
    'user': 'your_username',
    'password': 'your_password',
    'port': 3306,
}
```

## Troubleshooting

### "Not found in database"
- The Local by Flywheel site may not be running
- The wp_players table may not exist or may be empty
- The player name in the database may not match the ESPN name

### "Could not retrieve player stats"
- Check that the player name is spelled correctly
- Verify the week and year are valid
- Ensure the game has been played
- Check that the player participated in that game

## Notes

- The script works without database connectivity - it will simply show "Not found in database" for the p_id
- 2pt conversions data may not be available from the ESPN API
- The script searches all games in the specified week to find the player
