# ESPN API Integration for Player Jersey Numbers

## Overview
The `scrape-pfr-numbers.php` page has been updated to use the ESPN API instead of scraping Pro Football Reference for player jersey numbers.

## Changes Made

### 1. Template Information Updated
- Changed template name from "Scrape PFR for Two Point Conversions" to "Scrape ESPN for Player Numbers"
- Updated description to reflect ESPN API usage

### 2. New ESPN API Helper Function
Added `get_espn_player_jersey($first_name, $last_name, $year = null)` function that:
- Fetches all NFL teams from ESPN API
- Searches through each team's roster
- Matches player by full name (case-insensitive)
- Returns jersey number if found

**ESPN API Endpoints Used:**
- Teams list: `https://site.api.espn.com/apis/site/v2/sports/football/nfl/teams`
- Team roster: `https://site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/roster`

### 3. Updated Main Logic
The page now:
1. Attempts to fetch jersey number from ESPN API for the current roster
2. If found, applies that number to all years the player was active
3. If not found, prompts user for manual entry
4. Displays status messages indicating whether ESPN data was found
5. Saves data to `wp_players.numberarray` field as JSON (same format as before)

### 4. Enhanced User Interface
- Added status alerts showing ESPN API results
- Manual entry form with inputs for each year player was active
- Edit form allowing updates to saved numbers
- Bootstrap-styled forms with validation (0-99 range for jersey numbers)

## How It Works

### Automatic Mode (ESPN API Found Data)
1. Page loads with player ID in URL
2. ESPN API is queried for player's current jersey number
3. Number is applied to all years player was active
4. Data is displayed with success message
5. User can edit numbers if needed and save

### Manual Entry Mode (ESPN API No Data)
1. Page loads with player ID in URL
2. ESPN API query returns no results
3. Alert notifies user that manual entry is required
4. Form displays with input fields for each year
5. User enters jersey numbers
6. Clicking "Save Numbers" stores data as JSON in database

## Database Storage
Numbers are stored in the `wp_players` table, `numberarray` column as JSON:
```json
{
  "2020": "87",
  "2021": "87",
  "2022": "13"
}
```

## Important Notes

### ESPN API Limitations
- **No Historical Data**: ESPN API only provides current season roster data
- **Active Players Only**: Only players on current NFL rosters will be found
- **No Authentication Required**: ESPN's public API doesn't require API keys
- **Rate Limiting**: The API may have rate limits, but this isn't documented

### When Manual Entry is Required
- Retired players not on current rosters
- Players who changed jersey numbers between seasons
- Players not found due to name variations
- Historical players from before ESPN's API coverage

### Improvements from Previous Version
- No web scraping (more reliable than HTML parsing)
- Official API (less likely to break with website changes)
- Faster execution (direct API calls vs page scraping)
- Better error handling
- User-friendly manual entry interface

## Future Enhancements (Optional)

1. **Cache ESPN API Results**: Store API responses to reduce repeated calls
2. **Historical Data Source**: Integrate additional data source for retired players
3. **Bulk Import**: Add ability to import multiple players at once
4. **Number Change Detection**: Track when players change jersey numbers
5. **Team-Based Search**: If player's team is known, only search that team's roster

## Testing the Integration

1. Visit: `http://pfl-data.local/scrape-pfr-for-numbers/?id=PLAYER_ID`
2. Replace `PLAYER_ID` with actual player ID (e.g., `2025MeviPK`)
3. Check if ESPN finds the player (success alert)
4. If manual entry required, fill in jersey numbers
5. Click "Save Numbers" to store in database
6. Verify JSON data is displayed correctly

## Troubleshooting

**Player Not Found:**
- Check player name spelling in database
- Verify player is on current NFL roster
- Use manual entry for retired/historical players

**API Timeout:**
- ESPN API may be temporarily unavailable
- Refresh page to retry
- Use manual entry as fallback

**Numbers Not Saving:**
- Check database connection
- Verify `insert_player_number_array()` function exists in functions.php
- Check browser console for JavaScript errors
