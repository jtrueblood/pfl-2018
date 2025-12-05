# NFL Player Data Fetcher

This script fetches NFL player statistics from ESPN's unofficial API and optionally retrieves the player ID from a WordPress database.

## Requirements

```bash
pip install requests mysql-connector-python
```

## Usage

```bash
python3 getplayernfldata.py "Player Name" YEAR WEEK
```

### Examples

```bash
# Get Josh Allen's stats for Week 13, 2024
python3 getplayernfldata.py "Josh Allen" 2024 13

# Get Patrick Mahomes' stats for Week 12, 2024
python3 getplayernfldata.py "Patrick Mahomes" 2024 12

# Get Justin Tucker's (kicker) stats
python3 getplayernfldata.py "Justin Tucker" 2024 13
```

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
