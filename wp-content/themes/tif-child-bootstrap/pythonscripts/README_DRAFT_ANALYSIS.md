# PFL Draft Analysis Script

## Overview
This script analyzes PFL draft picks for any given year and determines the best/most savvy pick based on career production, immediate impact, and draft position value.

## Usage

### Basic Usage
```bash
python3 analyze_draft_year.py <year>
```

### Examples
```bash
# Analyze the 2001 draft
python3 analyze_draft_year.py 2001

# Analyze the 2000 draft
python3 analyze_draft_year.py 2000

# You can also run it directly (if executable)
./analyze_draft_year.py 2001
```

## Output

The script produces two types of output:

### 1. Console Summary
A brief summary of the analysis including:
- Best pick and their stats
- Value score calculation

### 2. HTML Output
Formatted HTML that includes:
- Why the pick was the best
- Top 5 overall picks (list format)
- Top 10 players by value score (detailed table with all stats)
- Late round steals (Round 3+)
- First round disappointments
- Conclusion paragraph

## Value Score Calculation

The script calculates a "value score" for each pick using the formula:

```
Base Score = (Career Points × 5 + Season Points × 0.5) × (Pick Number / 10)

Value Score = Base Score × HOF Multiplier × Protection Multiplier × Position Multiplier
```

### Multipliers (Applied in Order):
1. **Hall of Fame Status**: 4.0x multiplier if inducted (ultimate achievement)
2. **Protection Status**: 3.0x multiplier if player was protected at the start of the NEXT season (franchise commitment after rookie year)
3. **Position Penalty**: 0.4x multiplier for kickers (PK)

### Weighting Philosophy:
This formula **heavily favors career production and franchise commitment**:
- **Career Points**: 5x base weight (most important factor)
- **Hall of Fame**: 4x multiplier (ultimate recognition)
- **Protection Status**: 3x multiplier (shows franchise valued the player long-term)
- **Season Points**: 0.5x weight (immediate impact is less important than longevity)
- **Draft Position**: Later picks get bonus multiplier (draft savvy)
- **Position Value**: Kickers penalized at 40% of value (less valuable than skill positions)

## Analysis Criteria

### Late Round Steals
- Rounds 3 or later
- 100+ career points (for drafts 2000+)
- 200+ career points (for earlier drafts)

### First Round Disappointments
- Round 1 picks
- Less than 50 career points

## Requirements

- Python 3.x
- `curl` command (for fetching draft data)
- `mysql` command (for querying protection data)
- Access to `http://pfl-data.local/drafts/`
- Access to Local by Flywheel MySQL database via socket

## Files

- `analyze_draft_year.py` - Main script
- `analyze_draft.py` - Original 2000 draft analysis (manual data)
- `analyze_draft_2001.py` - Original 2001 draft analysis (manual data)

## Notes

- The script expects draft data in a specific HTML format
- Players who "Never Played" are treated as 0 points
- Empty career/season point values are treated as 0
- The script automatically detects rounds (Round 1 has no header in the HTML)
- **Protection Status**: The script queries the `wp_protections` database table for the NEXT year (year+1). For example, a player drafted in 2000 would be checked for protection in 2001, showing they were protected after their rookie season. The script connects to the Local by Flywheel MySQL database via socket.
