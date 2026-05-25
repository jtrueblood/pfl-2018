# MFL Draft Results Integration - Complete Documentation

## Overview
This implementation integrates draft events into the MFL Player Transactions table on player pages, using actual draft timestamps from the MFL API (2011-present) and fallback dates for earlier years.

## Components

### 1. Python Script: `fetch_mfl_drafts.py`
**Purpose:** Fetch draft results from MFL API and save as JSON files.

**Location:** `pythonscripts/fetch_mfl_drafts.py`

**Usage:**
```bash
# Fetch a specific year
python3 fetch_mfl_drafts.py 2025

# Fetch all years (2011-2025)
python3 fetch_mfl_drafts.py --all

# Fetch a range of years
python3 fetch_mfl_drafts.py --range 2020 2025

# List existing files
python3 fetch_mfl_drafts.py --list
```

**Features:**
- Fetches draft results from MFL API
- Saves JSON files to `mfl-drafts/` directory
- Handles errors gracefully
- Shows progress and summary
- Validates year range (2011-present only)

**Output Format:**
Files are saved as: `{year}_draft_results.json`
Example: `2025_draft_results.json`

**JSON Structure:**
```json
{
  "encoding": "utf-8",
  "version": "1.0",
  "draftResults": {
    "draftUnit": {
      "draftType": "SAME",
      "draftPick": [
        {
          "round": "01",
          "player": "17042",
          "franchise": "0001",
          "pick": "01",
          "timestamp": "1755972079",
          "comments": "..."
        }
      ]
    }
  }
}
```

### 2. PHP Functions in `functions.php`
**Location:** Lines 6735-6818 in `functions.php`

#### Function: `get_mfl_draft_timestamp($year, $player_mfl_id)`
Gets the actual draft timestamp from MFL JSON files.

**Parameters:**
- `$year` (int): Draft year
- `$player_mfl_id` (string): MFL player ID

**Returns:** 
- `string`: Draft timestamp in 'YYYY-MM-DD HH:MM:SS' format
- `null`: If file doesn't exist or player not found

**Example:**
```php
$timestamp = get_mfl_draft_timestamp(2025, '17042');
// Returns: "2025-08-15 14:27:59"
```

#### Function: `get_draft_date_for_player($year, $player_mfl_id = null)`
Gets the draft date with fallback to defaults.

**Parameters:**
- `$year` (int): Draft year
- `$player_mfl_id` (string, optional): MFL player ID

**Returns:** 
- `string`: Draft date in 'YYYY-MM-DD' format

**Logic:**
1. If MFL ID provided, tries to get actual timestamp from JSON
2. Falls back to default dates (August 1st) for all years

### 3. Player Page Integration (`players.php`)
**Location:** Lines 1791-1825 in `players.php`

**Implementation:**
1. Queries `wp_drafts` table for player
2. For each draft record:
   - Tries to get actual timestamp from MFL JSON (if year >= 2011)
   - Falls back to default date if not found
   - Formats pick as "R{round}-{pick}" (e.g., "R1-02")
   - Creates draft event object
   - Adds to transactions array for display

**Draft Event Structure:**
```php
array(
    'type' => 'DRAFT',
    'realtime' => '2025-08-15 14:27:59',  // Actual or default
    'franchise' => 'BST',
    'action' => 'Drafted R1-02',
    'is_draft' => true
)
```

**Display in Table:**
- **Type:** DRAFT
- **Player:** Player name
- **Year:** Draft year
- **Date:** Month/Day from timestamp
- **Time:** Actual time from MFL timestamp (HH:MM:SS) or "-" if no timestamp available
- **Team:** Team that drafted player
- **Action:** "Drafted R{round}-{pick}"

## Setup Instructions

### Step 1: Run the Python Script
Fetch draft results from MFL API:

```bash
cd pythonscripts
python3 fetch_mfl_drafts.py --all
```

This will create JSON files in the `mfl-drafts/` directory.

### Step 2: Verify Files
Check that files were created:

```bash
python3 fetch_mfl_drafts.py --list
```

You should see files like:
```
2011_draft_results.json
2012_draft_results.json
...
2025_draft_results.json
```

### Step 3: Test on Player Page
1. Visit a player page (e.g., a player drafted in 2018 or later)
2. Scroll to "MFL Player Transactions" section
3. Look for DRAFT rows with actual timestamps

## Directory Structure

```
theme-root/
├── mfl-drafts/                    # Created by fetch script
│   ├── 2011_draft_results.json
│   ├── 2012_draft_results.json
│   └── ...
├── pythonscripts/
│   ├── fetch_mfl_drafts.py       # Main fetch script
│   ├── inject_draft_events.py    # Helper/test script
│   └── draft_timestamp_helper.php # Reference PHP code
├── functions.php                  # Contains helper functions
└── players.php                    # Player page template

```

## Data Flow

```
MFL API
   ↓
fetch_mfl_drafts.py
   ↓
mfl-drafts/{year}_draft_results.json
   ↓
get_mfl_draft_timestamp()  ←  players.php
   ↓
Draft event in transactions table
```

## Example Output

### For Player Drafted in 2025 (with MFL data):
```
Type    Player         Year    Date      Time        Team    Action
DRAFT   Josh Allen     2025    08/15     14:27:59    BST     Drafted R1-02
```

### For Player Drafted in 2005 (without MFL data):
```
Type    Player         Year    Date      Time    Team    Action
DRAFT   Tom Brady      2005    08/01     -       ETS     Drafted R3-15
```

## Updating Draft Data

To update with new draft data (e.g., after 2026 draft):

```bash
cd pythonscripts
python3 fetch_mfl_drafts.py 2026
```

Or update all years:
```bash
python3 fetch_mfl_drafts.py --all
```

## Troubleshooting

### Draft events not showing
1. Check that player has record in `wp_drafts` table
2. Verify `get_drafts_player()` function exists in functions.php
3. Check PHP error logs

### Timestamps showing as defaults (08/01)
1. Verify MFL JSON file exists for that year in `mfl-drafts/`
2. Check that player's MFL ID is set in `wp_players` table
3. Verify MFL ID in JSON matches player's MFL ID

### Script fails to fetch data
1. Check internet connection
2. Verify MFL API credentials are correct
3. Check year is within valid range (2011-present)
4. Try fetching a single year to test

### JSON files not found
1. Ensure `mfl-drafts/` directory exists
2. Check file permissions
3. Verify script ran successfully
4. Check the output directory path in script

## Technical Notes

### Timestamp Format
- MFL API provides Unix timestamps
- Converted to MySQL datetime format: 'YYYY-MM-DD HH:MM:SS'
- Displayed in table as Month/Day only

### Pick Number Format
- Stored in DB as: round='01', pick='02'
- Displayed as: R1-02
- Leading zeros removed from round, maintained for pick

### Performance
- JSON files are read on-demand per player
- Files are small (<100KB typically)
- No caching implemented (could be added if needed)

### Fallback Dates
All pre-2011 drafts use August 1st as default:
- 1991-08-01
- 1992-08-01
- ... etc

These can be updated with actual dates if known.

## Future Enhancements

1. **Actual Historical Dates**: Research and add real draft dates for pre-2011 years
2. **Caching**: Add transient caching for JSON file reads
3. **Bulk Update**: Create admin interface to fetch all draft years at once
4. **Draft Comments**: Display any draft pick comments from MFL
5. **Trade Information**: Link draft picks to trades if pick was traded
6. **Time Formatting**: Format time display (e.g., "2:27 PM" instead of "14:27:59")

## API Reference

### MFL Draft Results API
**Endpoint:** `https://www48.myfantasyleague.com/{year}/export`

**Parameters:**
- `TYPE`: 'draftResults'
- `L`: League ID (38954)
- `APIKEY`: API key
- `JSON`: 1

**Example:**
```
https://www48.myfantasyleague.com/2025/export?TYPE=draftResults&L=38954&APIKEY=aRNp1sySvuWqx0CmO1HIZDYeFbox&JSON=1
```

## Credits
Created: December 2025
Integration of MFL draft data with player transaction history.
