# Username/Password Authentication for MFL Draft Fetcher

## Overview
The `fetch_mfl_drafts.py` script now supports two authentication methods:
1. **API Key** (recommended) - Stored in database for reuse
2. **Username/Password** - Used when API key not available (not stored for security)

## When to Use Username/Password

Use username/password authentication when:
- You don't have or can't find the API key for a specific year
- You prefer not to manage API keys
- The API key has expired or changed
- You want a quick one-time fetch without storing credentials

## How It Works

### Authentication Flow
1. Script checks for stored API key in database
2. If not found, prompts user to choose authentication method:
   ```
   ============================================================
   AUTHENTICATION REQUIRED FOR YEAR 2024
   ============================================================
   Choose authentication method:
     1. API Key (recommended)
     2. Username/Password
     3. Skip this year
   ============================================================
   ```

3. Based on choice:
   - **Option 1**: Prompts for API key → Saves to database → Uses for all future requests
   - **Option 2**: Prompts for username/password → Uses immediately → NOT saved (security)
   - **Option 3**: Skips the year

### Security Features
- **Passwords are never stored** - Only API keys are saved to database
- **Secure password entry** - Uses `getpass` module (password not visible on screen)
- **No password logging** - Credentials never written to logs or files
- **Session-only** - Username/password only used for that specific fetch operation

## Usage Examples

### Example 1: Using Username/Password (First Time)
```bash
$ python3 fetch_mfl_drafts.py 2023

============================================================
AUTHENTICATION REQUIRED FOR YEAR 2023
============================================================
Choose authentication method:
  1. API Key (recommended)
  2. Username/Password
  3. Skip this year
============================================================
Enter choice (1, 2, or 3): 2

Enter your MFL credentials:
Username: your_mfl_username
Password: [hidden input]
  Note: Username/password not saved (only API keys are stored)
Fetching draft results for 2023 (using username/password)... ✓ Found 85 picks
  Saved to: .../2023_draft_results.json (18,456 bytes)

✓ Draft results fetched successfully!
```

### Example 2: Using API Key (First Time)
```bash
$ python3 fetch_mfl_drafts.py 2023

============================================================
AUTHENTICATION REQUIRED FOR YEAR 2023
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
  Saved to: .../2023_draft_results.json (18,456 bytes)

✓ Draft results fetched successfully!
```

### Example 3: Subsequent Fetch (API Key Stored)
```bash
$ python3 fetch_mfl_drafts.py 2023

Fetching draft results for 2023 (using API key)... ✓ Found 85 picks
  Saved to: .../2023_draft_results.json (18,456 bytes)

✓ Draft results fetched successfully!
```

## MFL API Parameters

The script uses different parameters based on authentication method:

### With API Key:
```
https://www48.myfantasyleague.com/2024/export?TYPE=draftResults&L=38954&APIKEY=yourkey&JSON=1
```

### With Username/Password:
```
https://www48.myfantasyleague.com/2024/export?TYPE=draftResults&L=38954&USERNAME=youruser&PASSWORD=yourpass&JSON=1
```

Both methods access the same data and produce identical results.

## Comparison: API Key vs Username/Password

| Feature | API Key | Username/Password |
|---------|---------|-------------------|
| **Stored for reuse** | ✅ Yes | ❌ No |
| **Secure password entry** | N/A | ✅ Yes (getpass) |
| **Requires finding key** | ✅ Yes | ❌ No |
| **One-time setup** | ✅ Yes | ❌ No (re-enter each time) |
| **Works offline** | ✅ Yes (once stored) | ❌ No |
| **Recommended for** | Regular use | One-time/testing |

## Best Practices

### Recommended: Use API Keys
1. More secure (stored locally, never transmitted in prompts)
2. Faster for repeated fetches (no re-entry)
3. More convenient for batch operations
4. Can be managed (update/delete) via database

### When to Use Username/Password
1. Quick one-time fetches
2. Testing without committing to storing keys
3. When API key is unavailable or difficult to find
4. For years you rarely fetch

### Security Tips
1. **Never share your credentials** - API keys or passwords
2. **Use strong passwords** - If using password authentication
3. **Change passwords regularly** - Standard security practice
4. **Store API keys** - More secure than repeatedly entering passwords

## Troubleshooting

### Problem: "No draft data found (check authentication)"
**Possible Causes:**
- Incorrect username or password
- Incorrect API key
- Account doesn't have access to that league
- API endpoint changed

**Solutions:**
1. Verify username/password by logging into MFL website
2. Check API key is correct for that specific year
3. Ensure you have access to league 38954

### Problem: "Username cannot be empty"
**Solution:** 
Enter your MFL username (the one you use to log in to MyFantasyLeague.com)

### Problem: "Password cannot be empty"
**Solution:**
Enter your MFL password. Note: Password won't be visible as you type (security feature)

### Problem: Password not working
**Possible Causes:**
- Caps Lock is on
- Special characters in password
- Password recently changed

**Solutions:**
1. Check Caps Lock
2. Copy/paste password if it contains special characters
3. Reset password on MFL website if needed

## Advanced Usage

### Mixing Authentication Methods
You can use different methods for different years:

```bash
# Year 2024: Use API key
$ python3 fetch_mfl_drafts.py 2024
[Choose option 1, enter API key]

# Year 2023: Use username/password (don't have key handy)
$ python3 fetch_mfl_drafts.py 2023
[Choose option 2, enter username/password]

# Year 2024 again: Uses stored API key automatically
$ python3 fetch_mfl_drafts.py 2024
[No prompt - uses stored key]

# Year 2023 again: Prompts for authentication again
$ python3 fetch_mfl_drafts.py 2023
[Prompts again - password not stored]
```

### Batch Operations with Mixed Auth
When using `--all` or `--range`:
- Years with stored API keys: Skip prompt (use stored key)
- Years without keys: Prompt for authentication (choose method)

```bash
$ python3 fetch_mfl_drafts.py --range 2020 2024

# If 2020-2022 have stored keys, only prompts for 2023-2024
Fetching draft results for 2020 (using API key)... ✓
Fetching draft results for 2021 (using API key)... ✓
Fetching draft results for 2022 (using API key)... ✓

[Prompts for 2023 authentication]
[Prompts for 2024 authentication]
```

## Technical Notes

### Password Security
- Uses Python's `getpass` module for secure password input
- Passwords never echoed to terminal
- Passwords not stored in variables longer than necessary
- Passwords not logged or written to files

### API Key vs Password Storage
- **API Keys**: Stored in `wp_mfl_api_keys` table
- **Passwords**: Never stored anywhere (memory only during request)

### Why Not Store Passwords?
1. **Security**: Passwords grant full account access
2. **Best Practice**: Never store passwords in plain text
3. **MFL Policy**: API keys are intended for automation
4. **Limited Scope**: API keys can be revoked without changing password

## FAQ

**Q: Can I use username/password for all years?**
A: Yes, but you'll need to re-enter credentials each time you fetch that year.

**Q: If I enter username/password once, will it remember it?**
A: No, passwords are never stored. Only API keys are stored.

**Q: Which is more secure?**
A: API keys are more secure for automation because they:
- Have limited scope (export data only)
- Can be revoked independently of your password
- Don't grant full account access

**Q: Can I switch from username/password to API key later?**
A: Yes! Just run the script again for that year and choose option 1 (API key).

**Q: What if my password has special characters?**
A: Type it normally - getpass handles all characters. If issues persist, try copy/paste.

**Q: Does username/password work for all years?**
A: Yes, as long as you have a valid MFL account with access to the league.

**Q: Why is API key recommended?**
A: One-time setup, faster repeated use, no password re-entry, more secure for automation.

## Getting Your MFL Credentials

### Username
Your MFL username is what you use to log in at https://www48.myfantasyleague.com/

### Password
Your MFL password. If forgotten:
1. Go to https://www48.myfantasyleague.com/
2. Click "Forgot Password"
3. Follow reset instructions

### API Key
1. Visit: `https://www48.myfantasyleague.com/{YEAR}/options?L=38954&O=26`
2. Look for "Export/API" section
3. Copy the displayed key
