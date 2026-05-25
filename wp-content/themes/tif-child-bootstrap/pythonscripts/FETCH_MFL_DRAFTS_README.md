# MFL Draft Results Fetcher - API Key Management

## Overview
The `fetch_mfl_drafts.py` script now manages API keys per year, storing them in a MySQL database table for reuse.

## How It Works

### API Key Storage
- **Database Table:** `wp_mfl_api_keys`
- **Structure:**
  ```sql
  CREATE TABLE wp_mfl_api_keys (
      year INT PRIMARY KEY,
      api_key VARCHAR(255) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )
  ```

### API Key Flow
1. When fetching a year, the script first checks if an API key exists in the database
2. If not found, prompts the user to enter the API key
3. Saves the API key to the database for future use
4. Uses the API key to fetch draft results from MFL

## Finding API Keys

### Method 1: Through MFL Website
1. Go to: `https://www48.myfantasyleague.com/{YEAR}/options?L=38954&O=26`
   - Example: https://www48.myfantasyleague.com/2024/options?L=38954&O=26
2. Look for the "Export/API" section
3. Copy the API key shown

### Method 2: From Previous Exports
If you've previously exported data from MFL, the API key is in the URL parameters.

### Method 3: League Settings
1. Log into your MFL league
2. Go to "Setup" → "Export/API"
3. Copy the API key

## Usage

### First Time Setup
When you run the script for a year without a stored API key:

```bash
python3 fetch_mfl_drafts.py 2024
```

You'll see:
```
============================================================
API KEY REQUIRED FOR YEAR 2024
============================================================
To find the API key:
1. Visit: https://www48.myfantasyleague.com/2024/options?L=38954&O=26
2. Look for the 'Export/API' section
3. Copy the API key shown there
============================================================
Enter API key for 2024 (or 'skip' to skip this year): 
```

**Enter the API key** and press Enter. The key will be saved and used immediately.

### Subsequent Runs
Once an API key is saved, the script will use it automatically:

```bash
python3 fetch_mfl_drafts.py 2024
# Uses stored API key - no prompt needed!
```

### Skipping a Year
If you don't have the API key handy, type `skip`:

```
Enter API key for 2024 (or 'skip' to skip this year): skip
  Skipping year 2024 - no API key provided
```

### Fetching Multiple Years
When using `--all` or `--range`, the script will:
1. Use stored keys for years that have them
2. Prompt for keys for years that don't
3. Continue to the next year if you skip one

```bash
python3 fetch_mfl_drafts.py --all
# Prompts only for years without stored keys

python3 fetch_mfl_drafts.py --range 2020 2024
# Prompts only for years without stored keys in that range
```

## Managing API Keys

### View Stored Keys
Query the database directly:

```sql
SELECT * FROM wp_mfl_api_keys ORDER BY year DESC;
```

### Update a Key
If an API key changes or was entered incorrectly, just run the script again.
The script will prompt for the key and update it:

```bash
python3 fetch_mfl_drafts.py 2024
# Enter new key when prompted
# Key will be updated in database
```

Or update directly in database:

```sql
UPDATE wp_mfl_api_keys 
SET api_key = 'newKeyHere' 
WHERE year = 2024;
```

### Delete a Key
Remove from database to force re-prompt:

```sql
DELETE FROM wp_mfl_api_keys WHERE year = 2024;
```

## Important Notes

### API Keys Change Per Year
- **Each MFL season has its own API key**
- You'll need to find and enter the key for each year you want to fetch
- Once entered, the key is stored permanently

### Why Keys Change
MFL generates new API keys for:
- New seasons
- League security updates
- Password resets
- League migrations

### Key Reuse
The script will **automatically reuse** stored keys, so you only need to enter each key once.

### Error Messages

#### "No draft data found (check API key)"
- The API key is incorrect or has expired
- Solution: Delete the key from database and re-run with correct key

#### "Could not connect to database"
- MySQL is not running or connection settings are wrong
- Script will still work but won't save keys (you'll need to enter them every time)

#### "Warning: MySQL connector not available"
- `mysql-connector-python` package not installed
- Install: `pip3 install mysql-connector-python`
- Script will still work but won't save keys

## Examples

### Example 1: Fetch Single Year (First Time)
```bash
$ python3 fetch_mfl_drafts.py 2024

============================================================
API KEY REQUIRED FOR YEAR 2024
============================================================
...
Enter API key for 2024 (or 'skip' to skip this year): aRNp1sySvuWqx0CmO1HIZDYeFbox
  ✓ API key saved for 2024
Fetching draft results for 2024... ✓ Found 120 picks
  Saved to: .../mfl-drafts/2024_draft_results.json (45,123 bytes)

✓ Draft results fetched successfully!
```

### Example 2: Fetch Single Year (Key Already Stored)
```bash
$ python3 fetch_mfl_drafts.py 2024

Fetching draft results for 2024... ✓ Found 120 picks
  Saved to: .../mfl-drafts/2024_draft_results.json (45,123 bytes)

✓ Draft results fetched successfully!
```

### Example 3: Fetch All Years
```bash
$ python3 fetch_mfl_drafts.py --all

Fetching draft results for years 2011-2025
================================================================================
Fetching draft results for 2011... ✓ Found 96 picks
  Saved to: .../2011_draft_results.json

============================================================
API KEY REQUIRED FOR YEAR 2012
============================================================
...
Enter API key for 2012 (or 'skip' to skip this year): [enter key]
  ✓ API key saved for 2012
Fetching draft results for 2012... ✓ Found 108 picks
...

================================================================================
SUMMARY: 14 successful, 1 failed
================================================================================
```

### Example 4: Skip a Year
```bash
$ python3 fetch_mfl_drafts.py 2023

============================================================
API KEY REQUIRED FOR YEAR 2023
============================================================
...
Enter API key for 2023 (or 'skip' to skip this year): skip
  Skipping year 2023 - no API key provided

✗ Failed to fetch draft results
```

## Troubleshooting

### Problem: API key doesn't work
**Solution:** 
1. Verify you copied the complete key
2. Check you're using the key for the correct year
3. Try getting a fresh key from MFL website

### Problem: Keys not being saved
**Check:**
1. MySQL is running: `mysql.server status`
2. Database exists: `SHOW DATABASES LIKE 'local';`
3. Python MySQL connector installed: `pip3 list | grep mysql`

### Problem: Table doesn't exist
**Solution:**
The script automatically creates the table, but you can create it manually:

```sql
CREATE TABLE IF NOT EXISTS wp_mfl_api_keys (
    year INT PRIMARY KEY,
    api_key VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Technical Details

### Database Connection
Uses the same connection settings as other PFL scripts:
- Host: localhost
- Database: local
- User: root
- Password: root
- Socket: Local by Flywheel socket path

### Security
- API keys are stored in the local database
- Keys are not transmitted anywhere except to MFL API
- Keys are specific to the league (38954)

### Performance
- Database lookups are fast (indexed by year)
- No performance impact compared to hardcoded keys
- Reduces manual work for multi-year fetches

## Benefits

1. **No more hardcoded keys** - Each year uses its own key
2. **Automatic reuse** - Enter once, use forever
3. **Easy updates** - Just re-run to update a key
4. **Batch friendly** - Fetch all years without manual key entry
5. **Persistent** - Keys survive script updates and system reboots
