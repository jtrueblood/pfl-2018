#!/usr/bin/env python3
"""
Fetch NFL game kickoff times from nfl_data_py and store in wp_nfl_game_times.

Covers 1999–present. For years before 1999, nfl_data_py lacks schedule data
so those rows are left without times (they can be filled manually if needed).

Run once to backfill all history, then once each new season to add that year.

Usage:
  python3 fetch_nfl_gametimes.py           # fetch all years 1999–current
  python3 fetch_nfl_gametimes.py 2026      # fetch a single year (new season)

Table created if missing:
  wp_nfl_game_times (season, week, game_date, home_team, away_team, gametime, weekday)

gametime is stored as "HH:MM" in US Eastern time (e.g. "13:00", "20:20").
home_team / away_team use PFR-style abbreviations (GNB, NWE, SFO …).
"""

import sys
import datetime
import mysql.connector
import nfl_data_py as nfl
import pandas as pd

# ── DB connection ─────────────────────────────────────────────────────────────

MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"

def get_db():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="root",
        database="local",
        unix_socket=MYSQL_SOCKET,
    )

# ── Team abbreviation normalisation (nflverse → PFR style) ───────────────────

ABBR_MAP = {
    "GB":  "GNB",
    "KC":  "KAN",
    "LA":  "LAR",
    "LAC": "LAC",
    "LV":  "LVR",
    "NE":  "NWE",
    "NO":  "NOR",
    "SF":  "SFO",
    "TB":  "TAM",
    "SD":  "SDG",
    "OAK": "OAK",
    "STL": "STL",
    "JAC": "JAX",
    "ARI": "ARI",
    "ATL": "ATL",
    "BAL": "BAL",
    "BUF": "BUF",
    "CAR": "CAR",
    "CHI": "CHI",
    "CIN": "CIN",
    "CLE": "CLE",
    "DAL": "DAL",
    "DEN": "DEN",
    "DET": "DET",
    "HOU": "HOU",
    "IND": "IND",
    "JAX": "JAX",
    "MIN": "MIN",
    "NYG": "NYG",
    "NYJ": "NYJ",
    "PHI": "PHI",
    "PIT": "PIT",
    "SEA": "SEA",
    "TEN": "TEN",
    "WAS": "WAS",
    "WSH": "WAS",
}

def norm(abbr: str) -> str:
    if not abbr or (isinstance(abbr, float)):
        return ""
    return ABBR_MAP.get(str(abbr).strip(), str(abbr).strip())

# ── DB setup ──────────────────────────────────────────────────────────────────

CREATE_TABLE = """
CREATE TABLE IF NOT EXISTS wp_nfl_game_times (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    season     SMALLINT     NOT NULL,
    week       TINYINT      NOT NULL,
    game_date  DATE         NOT NULL,
    home_team  VARCHAR(10)  NOT NULL,
    away_team  VARCHAR(10)  NOT NULL,
    gametime   VARCHAR(10)  DEFAULT NULL COMMENT 'HH:MM ET, e.g. 13:00',
    weekday    VARCHAR(10)  DEFAULT NULL,
    temp       SMALLINT     DEFAULT NULL COMMENT 'degrees F at kickoff; NULL for dome games',
    wind       SMALLINT     DEFAULT NULL COMMENT 'mph; NULL for dome games',
    roof       VARCHAR(20)  DEFAULT NULL COMMENT 'outdoors, dome, retractable, open',
    surface    VARCHAR(30)  DEFAULT NULL COMMENT 'grass, fieldturf, astroturf, etc.',
    UNIQUE KEY uq_game (season, week, home_team)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
"""

ALTER_COLUMNS = [
    "ALTER TABLE wp_nfl_game_times ADD COLUMN IF NOT EXISTS temp    SMALLINT    DEFAULT NULL COMMENT 'degrees F at kickoff; NULL for dome games'",
    "ALTER TABLE wp_nfl_game_times ADD COLUMN IF NOT EXISTS wind    SMALLINT    DEFAULT NULL COMMENT 'mph; NULL for dome games'",
    "ALTER TABLE wp_nfl_game_times ADD COLUMN IF NOT EXISTS roof    VARCHAR(20) DEFAULT NULL COMMENT 'outdoors, dome, retractable, open'",
    "ALTER TABLE wp_nfl_game_times ADD COLUMN IF NOT EXISTS surface VARCHAR(30) DEFAULT NULL COMMENT 'grass, fieldturf, astroturf, etc.'",
]

# ── Core logic ────────────────────────────────────────────────────────────────

def fetch_year(year: int) -> pd.DataFrame:
    print(f"  Fetching {year} schedule from nfl_data_py …", end=" ", flush=True)
    df = nfl.import_schedules([year])
    # Regular season + postseason (we only care about regular season weeks 1–18)
    df = df[df["game_type"] == "REG"].copy()
    print(f"{len(df)} games")
    return df

def upsert_year(cursor, year: int, df: pd.DataFrame) -> tuple[int, int]:
    inserted = updated = 0
    for _, row in df.iterrows():
        game_date_raw = row.get("gameday") or row.get("game_date")
        if pd.isna(game_date_raw):
            continue
        game_date = str(game_date_raw)[:10]  # ensure YYYY-MM-DD

        week = int(row["week"]) if not pd.isna(row["week"]) else None
        if week is None:
            continue

        home = norm(row.get("home_team", ""))
        away = norm(row.get("away_team", ""))
        if not home or not away:
            continue

        def optstr(key):
            v = row.get(key)
            return str(v).strip() if v and not pd.isna(v) else None

        def optint(key):
            v = row.get(key)
            try:
                return int(round(float(v))) if v and not pd.isna(v) else None
            except (ValueError, TypeError):
                return None

        gametime = optstr("gametime")
        weekday  = optstr("weekday")
        temp     = optint("temp")
        wind     = optint("wind")
        roof     = optstr("roof")
        surface  = optstr("surface")

        cursor.execute("""
            INSERT INTO wp_nfl_game_times
                (season, week, game_date, home_team, away_team, gametime, weekday, temp, wind, roof, surface)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE
                game_date = VALUES(game_date),
                away_team = VALUES(away_team),
                gametime  = VALUES(gametime),
                weekday   = VALUES(weekday),
                temp      = VALUES(temp),
                wind      = VALUES(wind),
                roof      = VALUES(roof),
                surface   = VALUES(surface)
        """, (year, week, game_date, home, away, gametime, weekday, temp, wind, roof, surface))

        if cursor.rowcount == 1:
            inserted += 1
        elif cursor.rowcount == 2:
            updated += 1

    return inserted, updated

# ── Entry point ───────────────────────────────────────────────────────────────

def main():
    current_year = datetime.date.today().year

    if len(sys.argv) == 2:
        try:
            years = [int(sys.argv[1])]
        except ValueError:
            print("Usage: python3 fetch_nfl_gametimes.py [year]")
            sys.exit(1)
    elif len(sys.argv) == 1:
        years = list(range(1999, current_year + 1))
    else:
        print("Usage: python3 fetch_nfl_gametimes.py [year]")
        sys.exit(1)

    conn = get_db()
    cursor = conn.cursor()

    print("Creating/updating wp_nfl_game_times table …")
    cursor.execute(CREATE_TABLE)
    for stmt in ALTER_COLUMNS:
        try:
            cursor.execute(stmt)
        except Exception:
            pass  # column already exists on older MySQL without IF NOT EXISTS support
    conn.commit()

    total_inserted = total_updated = 0

    for year in years:
        try:
            df = fetch_year(year)
            ins, upd = upsert_year(cursor, year, df)
            conn.commit()
            total_inserted += ins
            total_updated  += upd
            print(f"  {year}: {ins} inserted, {upd} updated")
        except Exception as e:
            print(f"  {year}: ERROR — {e}")
            conn.rollback()

    cursor.close()
    conn.close()

    print(f"\nDone. Total: {total_inserted} inserted, {total_updated} updated.")

if __name__ == "__main__":
    main()
