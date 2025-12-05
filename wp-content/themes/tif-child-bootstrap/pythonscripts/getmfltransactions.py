#!/usr/bin/env python3
"""
Fetch and display MyFantasyLeague transactions for a given year.
"""

import argparse
import json
import sys
import os
import requests


def get_mfl_transactions(year):
    """
    Fetch transactions from MyFantasyLeague API for the specified year.
    
    Args:
        year (str): The year to fetch transactions for
        
    Returns:
        dict: JSON response from the API
    """
    url = f"https://www48.myfantasyleague.com/{year}/export"
    params = {
        "TYPE": "transactions",
        "L": "38954",
        "APIKEY": "aRNp1sySvuWqx1KmO1HIZDYeF7ox",
        "W": "",
        "TRANS_TYPE": "",
        "FRANCHISE": "",
        "DAYS": "",
        "COUNT": "",
        "JSON": "1"
    }
    
    try:
        response = requests.get(url, params=params)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"Error fetching data: {e}", file=sys.stderr)
        sys.exit(1)
    except json.JSONDecodeError as e:
        print(f"Error parsing JSON response: {e}", file=sys.stderr)
        sys.exit(1)


def main():
    parser = argparse.ArgumentParser(
        description="Fetch and display MyFantasyLeague transactions"
    )
    parser.add_argument(
        "year",
        type=str,
        help="The year to fetch transactions for (e.g., 2025)"
    )
    
    args = parser.parse_args()
    
    # Fetch transactions
    data = get_mfl_transactions(args.year)
    
    # Define output directory and filename
    output_dir = "/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-content/themes/tif-child-bootstrap/mfl-transactions"
    filename = f"{args.year}-trans.json"
    filepath = os.path.join(output_dir, filename)
    
    # Create directory if it doesn't exist
    os.makedirs(output_dir, exist_ok=True)
    
    # Save JSON to file (overwriting if exists)
    try:
        with open(filepath, 'w') as f:
            json.dump(data, f, indent=2)
        print(f"Successfully saved transactions to {filepath}")
    except IOError as e:
        print(f"Error saving file: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
