# Draft Events Injection into MFL Transactions Table

## Overview
This implementation adds draft events from the `wp_drafts` table into the MFL Player Transactions table displayed on player pages.

## Changes Made

### 1. Python Script (inject_draft_events.py)
Created a helper script to:
- Query draft records for any player from `wp_drafts` table
- Display draft events with proper formatting
- Demonstrate the expected output format

**Usage:**
```bash
python3 inject_draft_events.py <player_id>
# Example: python3 inject_draft_events.py 2018AlleQB
```

### 2. PHP Code Changes (players.php)
Modified the MFL transactions section around line 1788-1900 to:

#### A. Query Draft Data
After fetching MFL transactions, the code now queries `wp_drafts` for the player:
```php
$draft_events = get_drafts_player($playerid);
```

#### B. Define Draft Dates
Added a mapping of draft years to dates (August 1st by default):
```php
$draft_dates = array(
    1991 => '1991-08-01', 
    1992 => '1992-08-01',
    // ... etc
);
```

> **Note:** You can update these dates based on actual draft dates from http://pfl-data.local/drafts/

#### C. Inject Draft Events
For each draft event, the code:
1. Formats the pick as `R{round}-{pick}` (e.g., R1-02, R3-15)
2. Creates a draft event array matching MFL transaction format
3. Adds it to the appropriate year in the transactions array

```php
$draft_event = array(
    'type' => 'DRAFT',
    'realtime' => $draft_date . ' 00:00:00',
    'franchise' => $draft['acteam'],
    'action' => 'Drafted ' . $pick_format,
    'is_draft' => true
);
```

#### D. Display Draft Events
Added handling for the 'DRAFT' type in the table display loop:
```php
if($type == 'DRAFT'):
    // Display draft row with proper formatting
```

## Output Format
Draft events appear in the transactions table with:
- **Type:** DRAFT
- **Player:** Player name
- **Year:** Draft year (e.g., 2018)
- **Date:** Month/Day from MFL timestamp or fallback date
- **Time:** Actual time from MFL timestamp (HH:MM:SS) or "-" if not available
- **Team:** Team that drafted the player (from wp_drafts.acteam)
- **Action:** "Drafted R{round}-{pick}" (e.g., "Drafted R1-02")

## Example Output

### With MFL Timestamp (2011+):
```
Type    Player         Year    Date      Time        Team    Action
DRAFT   Josh Allen     2018    08/15     14:27:59    BST     Drafted R1-02
```

### Without MFL Timestamp (pre-2011):
```
Type    Player         Year    Date      Time    Team    Action
DRAFT   Tom Brady      2005    08/01     -       ETS     Drafted R3-15
```

## Database Structure
The implementation uses the existing `wp_drafts` table with columns:
- `playerid` - Player ID (e.g., '2018AlleQB')
- `year` - Draft year
- `round` - Round number (e.g., '01', '02')
- `picknum` - Pick number within round
- `acteam` - Team abbreviation that made the pick

## Future Enhancements
1. **Actual Draft Dates:** Update the `$draft_dates` array with real draft dates by scraping or manually entering data from http://pfl-data.local/drafts/
2. **Draft Time:** If draft times are available, replace the '-' with actual times
3. **Sorting:** Consider sorting all transactions (including drafts) chronologically by date
4. **Additional Info:** Could add draft notes or original team vs. trading team information

## Testing
To test the implementation:
1. Visit a player page on the site
2. Scroll to the "MFL Player Transactions" section
3. Look for DRAFT rows appearing in the appropriate years
4. Verify the pick format is correct (e.g., R1-02, not R01-02)

## Troubleshooting
If draft events don't appear:
1. Check that the player has a record in `wp_drafts` table
2. Verify `get_drafts_player()` function exists in functions.php
3. Check PHP error logs for any issues
4. Use the Python script to verify draft data exists for the player
