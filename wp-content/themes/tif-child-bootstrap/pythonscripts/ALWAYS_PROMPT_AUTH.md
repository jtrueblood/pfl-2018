# Always Prompt Authentication Mode

## Overview
The `fetch_mfl_drafts.py` script now **always prompts** for authentication method, giving you full control every time you fetch draft data.

## How It Works

### Every Fetch Prompts You
No matter if an API key is stored or not, you'll always see the authentication menu:

#### When API Key Is Stored:
```
============================================================
AUTHENTICATION FOR YEAR 2024
============================================================
Choose authentication method:
  1. Use stored API Key
  2. Enter new API Key
  3. Username/Password
  4. Skip this year
============================================================
Enter choice (1, 2, 3, or 4):
```

#### When No API Key Is Stored:
```
============================================================
AUTHENTICATION FOR YEAR 2024
============================================================
Choose authentication method:
  1. API Key (recommended)
  2. Username/Password
  3. Skip this year
============================================================
Enter choice (1, 2, or 3):
```

## Options Explained

### When API Key Is Stored (4 Options):

**Option 1: Use stored API Key**
- Uses the API key already saved in database
- Fastest option - no need to enter anything
- Recommended for regular use

**Option 2: Enter new API Key**
- Prompts for a new API key
- Replaces the stored key with new one
- Use when API key has changed

**Option 3: Username/Password**
- Prompts for MFL username and password
- One-time use (not saved)
- Use when you prefer not to use API key

**Option 4: Skip this year**
- Skips fetching this year
- Continues to next year (in batch mode)

### When No API Key Is Stored (3 Options):

**Option 1: API Key (recommended)**
- Prompts for API key
- Saves to database for future use
- Best for regular use

**Option 2: Username/Password**
- Prompts for MFL username and password
- One-time use (not saved)
- Good for testing or one-off fetches

**Option 3: Skip this year**
- Skips fetching this year
- Continues to next year (in batch mode)

## Usage Examples

### Example 1: Using Stored API Key
```bash
$ python3 fetch_mfl_drafts.py 2024

============================================================
AUTHENTICATION FOR YEAR 2024
============================================================
Choose authentication method:
  1. Use stored API Key
  2. Enter new API Key
  3. Username/Password
  4. Skip this year
============================================================
Enter choice (1, 2, 3, or 4): 1
  Using stored API key for 2024
Fetching draft results for 2024 (using API key)... ✓ Found 70 picks
  Saved to: .../2024_draft_results.json (15,960 bytes)

✓ Draft results fetched successfully!
```

### Example 2: Updating API Key
```bash
$ python3 fetch_mfl_drafts.py 2024

============================================================
AUTHENTICATION FOR YEAR 2024
============================================================
Choose authentication method:
  1. Use stored API Key
  2. Enter new API Key
  3. Username/Password
  4. Skip this year
============================================================
Enter choice (1, 2, 3, or 4): 2

To find the API key:
Visit: https://www48.myfantasyleague.com/2024/options?L=38954&O=26
Look for the 'Export/API' section and copy the key

Enter API key for 2024: aRNp1sySvuWrx0GmO1HIZDYeFbox
  ✓ API key saved for 2024
Fetching draft results for 2024 (using API key)... ✓ Found 70 picks

✓ Draft results fetched successfully!
```

### Example 3: Using Username/Password Instead
```bash
$ python3 fetch_mfl_drafts.py 2024

============================================================
AUTHENTICATION FOR YEAR 2024
============================================================
Choose authentication method:
  1. Use stored API Key
  2. Enter new API Key
  3. Username/Password
  4. Skip this year
============================================================
Enter choice (1, 2, 3, or 4): 3

Enter your MFL credentials:
Username: myusername
Password: [hidden]
  Note: Username/password not saved (only API keys are stored)
Fetching draft results for 2024 (using username/password)... ✓ Found 70 picks

✓ Draft results fetched successfully!
```

### Example 4: First Time (No Stored Key)
```bash
$ python3 fetch_mfl_drafts.py 2023

============================================================
AUTHENTICATION FOR YEAR 2023
============================================================
Choose authentication method:
  1. API Key (recommended)
  2. Username/Password
  3. Skip this year
============================================================
Enter choice (1, 2, or 3): 1

To find the API key:
Visit: https://www48.myfantasyleague.com/2023/options?L=38954&O=26
Look for the 'Export/API' section and copy the key

Enter API key for 2023: aRNp1sySvuWqx0CmO1HIZDYeFbox
  ✓ API key saved for 2023
Fetching draft results for 2023 (using API key)... ✓ Found 85 picks

✓ Draft results fetched successfully!
```

## Benefits of Always Prompting

### 1. Full Control
- Choose authentication method every time
- Switch between methods easily
- No surprises or automatic behavior

### 2. Flexibility
- Use stored key when convenient
- Use username/password when needed
- Update keys on the fly

### 3. Testing Different Methods
- Easy to compare API key vs username/password
- Test new keys before committing
- Verify credentials work

### 4. Security
- Explicit choice every time
- No automatic use of credentials
- Clear about what authentication is being used

### 5. Error Recovery
- If stored key doesn't work, immediately try another method
- Don't need to delete key from database to use password
- Quick fallback options

## Batch Operations

When using `--all` or `--range`, you'll be prompted for each year:

```bash
$ python3 fetch_mfl_drafts.py --range 2023 2025

Fetching draft results for years 2023-2025
================================================================================

[Prompts for 2023]
Choose authentication method:
  1. Use stored API Key
  ...

[Prompts for 2024]
Choose authentication method:
  1. Use stored API Key
  ...

[Prompts for 2025]
Choose authentication method:
  1. API Key (recommended)
  ...
```

You can use different methods for each year!

## Common Workflows

### Workflow 1: Regular Use (Stored Keys)
For years you fetch regularly, just press `1` each time:
```
Enter choice: 1
[Uses stored key - fast and easy]
```

### Workflow 2: Update Key
When a key changes, press `2` to update:
```
Enter choice: 2
[Enter new key - replaces old one]
```

### Workflow 3: One-Off Fetch
For rarely-fetched years, use username/password:
```
Enter choice: 3 (or 2 if no stored key)
[Enter credentials - not saved]
```

### Workflow 4: Mixed Approach
Use whatever makes sense for that specific fetch:
- Stored key for known-good keys
- Username/password for testing
- New key when updating

## Tips

### Quick Stored Key Use
Just press `1` + Enter for fastest fetch with stored key

### Testing New Keys
Use option 2/3 (username/password) to test without affecting stored key

### Updating Multiple Years
In batch mode, you can update keys for multiple years in one run

### Skip and Come Back
Use skip option if you don't have credentials handy

## Comparison to Old Behavior

### Old Behavior:
- Automatically used stored key (no prompt)
- Only prompted if key didn't exist
- Less flexibility

### New Behavior:
- Always prompts (full control)
- Can choose to use stored key or override
- Maximum flexibility

## Why This Is Better

1. **Transparency**: Always know what authentication is being used
2. **Control**: Choose method each time based on current needs
3. **Flexibility**: Easy to test different methods or update keys
4. **Recovery**: If stored key fails, immediately try another method
5. **No Surprises**: Never confused about which key was used

## Keyboard Shortcuts

For fastest operation:
- **Use stored key**: Just type `1` + Enter
- **Skip year**: Type `4` + Enter (or `3` if no stored key)

This makes it fast while still giving you control!
