#!/usr/bin/env python3
"""
Bulk-fill missing linescore data for playoff (week 15) and Posse Bowl
(week 16) rows in wp_playoffs.

Walks wp_playoffs once, picks every row whose linescore columns are still
NULL (pass_yds IS NULL), looks up the player's first/last name from
wp_players, and runs the same per-player scrape logic that the "Add
Linescore" button in the UI fires one row at a time.

Usage:
    python3 bulk_playoff_linescores.py                      # everything missing
    python3 bulk_playoff_linescores.py --year 2024          # single year
    python3 bulk_playoff_linescores.py --year 2024 --week 15
    python3 bulk_playoff_linescores.py --since 2010         # 2010 onward
    python3 bulk_playoff_linescores.py --dry-run            # show plan, do nothing
    python3 bulk_playoff_linescores.py --limit 5            # cap rows processed
    python3 bulk_playoff_linescores.py --delay 0.5          # seconds between calls
    python3 bulk_playoff_linescores.py --mode twopt         # rescrape suspected
                                                            # missed 2pt rows
    python3 bulk_playoff_linescores.py --mode all           # both buckets

Notes:
    - Pre-2001 rows scrape from wp_stathead_*; 2001+ from ESPN.
    - 1.0 s default delay between ESPN calls so we don't get rate-limited.
    - Each successful write also clears the matching weekly-results
      transient, same as the single-player path.
"""

import argparse
import sys
import time
from pathlib import Path

# Re-use everything from the single-player script
SCRIPT_DIR = Path(__file__).resolve().parent
sys.path.insert(0, str(SCRIPT_DIR))
import getplayernfldata as gpd  # type: ignore

import mysql.connector  # noqa: E402


def _pfl_expected_from_row(r):
    """Re-implement the PFL expected-score formula for an existing wp_playoffs
    row. Mirrors calcExpectedScore() in src/components/results/GamePanel.tsx
    so what we compute here matches what the page renders."""
    pos = (r.get('pid') or '')[-2:].upper()
    yr = int(r.get('year') or 0)

    def floor_yds(yds, per):
        return max(0, int(yds or 0)) // per

    twopt = int(r.get('twopt') or 0)
    if pos == 'PK':
        return int(r.get('xpm') or 0) + int(r.get('fgm') or 0) * 2 + twopt

    tds = (int(r.get('pass_td') or 0) +
           int(r.get('rush_td') or 0) +
           int(r.get('rec_td')  or 0))
    if yr == 1991:
        return (floor_yds(r.get('pass_yds'), 50) +
                floor_yds(r.get('rush_yds'), 25) +
                tds * 2 +
                floor_yds(r.get('rec_yds'), 25) -
                int(r.get('pass_int') or 0) +
                twopt)
    return (floor_yds(r.get('pass_yds'), 30) +
            floor_yds(r.get('rush_yds'), 10) +
            tds * 2 +
            floor_yds(r.get('rec_yds'), 10) -
            int(r.get('pass_int') or 0) +
            twopt)


def fetch_missing(year=None, week=None, since=None, limit=None, mode='missing'):
    """Return list of candidate rows.

    mode='missing'  → wp_playoffs rows whose linescore was never scraped
    mode='twopt'    → rows that ALREADY have a linescore but where
                      actual - expected == 1 and twopt == 0 (the
                      classic "missed 2pt conversion" pattern)
    mode='all'      → both categories
    """
    conn = mysql.connector.connect(
        host=gpd.DB_CONFIG['host'],
        user=gpd.DB_CONFIG['user'],
        password=gpd.DB_CONFIG['password'],
        database=gpd.DB_CONFIG['database'],
        unix_socket=gpd.MYSQL_SOCKET,
    )
    try:
        cur = conn.cursor(dictionary=True)

        where = [
            "p.playerid IS NOT NULL",
            "p.playerid <> ''",
            "p.playerid <> 'None'",
            "p.week IN (15, 16)",
        ]
        params = []
        if year is not None:
            where.append("p.year = %s")
            params.append(year)
        if week is not None:
            where.append("p.week = %s")
            params.append(week)
        if since is not None:
            where.append("p.year >= %s")
            params.append(since)

        # Stage 1 — totally missing linescores
        results = []
        if mode in ('missing', 'all'):
            stage1_where = where + ["p.pass_yds IS NULL"]
            sql = f"""
                SELECT p.id, p.year, p.week, p.playerid AS pid, p.team,
                       p.points, p.pass_yds, p.pass_td, p.pass_int,
                       p.rush_yds, p.rush_td, p.rec_yds, p.rec_td,
                       p.xpm, p.fgm, p.twopt,
                       pl.playerFirst AS first, pl.playerLast AS last,
                       'missing' AS reason
                FROM wp_playoffs p
                LEFT JOIN wp_players pl ON pl.p_id = p.playerid
                WHERE {' AND '.join(stage1_where)}
                ORDER BY p.year ASC, p.week ASC, p.team, p.playerid
            """
            cur.execute(sql, params)
            results.extend(cur.fetchall())

        # Stage 2 — already has linescore, but actual − expected == 1
        # and twopt = 0. Computed in Python because the formula is
        # year-dependent (1991 uses different yardage divisors).
        if mode in ('twopt', 'all'):
            stage2_where = where + [
                "p.pass_yds IS NOT NULL",
                "p.points IS NOT NULL",
                "COALESCE(p.twopt, 0) = 0",
            ]
            sql = f"""
                SELECT p.id, p.year, p.week, p.playerid AS pid, p.team,
                       p.points, p.pass_yds, p.pass_td, p.pass_int,
                       p.rush_yds, p.rush_td, p.rec_yds, p.rec_td,
                       p.xpm, p.fgm, p.twopt,
                       pl.playerFirst AS first, pl.playerLast AS last,
                       'twopt-suspect' AS reason
                FROM wp_playoffs p
                LEFT JOIN wp_players pl ON pl.p_id = p.playerid
                WHERE {' AND '.join(stage2_where)}
                ORDER BY p.year ASC, p.week ASC, p.team, p.playerid
            """
            cur.execute(sql, params)
            for r in cur.fetchall():
                expected = _pfl_expected_from_row(r)
                actual = int(r.get('points') or 0)
                if actual - expected == 1:
                    results.append(r)

        # Final ordering + optional cap
        results.sort(key=lambda r: (int(r['year']), int(r['week']),
                                    r.get('team') or '', r.get('pid') or ''))
        if limit is not None:
            results = results[:int(limit)]
        return results
    finally:
        try:
            cur.close()
        except Exception:
            pass
        conn.close()


def process_row(row, dry_run=False):
    """Drive process_single_week() for one missing row.

    Returns 'ok' / 'dnp' / 'skip' / 'fail'.
    """
    name = f"{row.get('first') or ''} {row.get('last') or ''}".strip()
    if not name:
        return 'skip'
    pid = row['pid']
    year = int(row['year'])
    week = int(row['week'])
    team = row.get('team') or None
    reason = row.get('reason') or 'missing'

    tag = 'MISSING' if reason == 'missing' else '+1 DIFF '
    label = f"[{year}-W{week}] {tag} {pid:<12} {name:<28} ({team})"
    if dry_run:
        print(f"  DRY  {label}")
        return 'ok'

    print(f"  >>  {label}")
    try:
        success, _, status = gpd.process_single_week(
            player_name=name,
            year=year,
            week=week,
            insert_to_db=True,
            p_id=pid,
            team_abbr=team,
            compact_output=True,
            allow_dnp=True,
        )
    except Exception as e:
        print(f"      ✗ exception: {e}")
        return 'fail'

    if status == 'dnp':
        return 'dnp'
    return 'ok' if success else 'fail'


def main():
    ap = argparse.ArgumentParser(
        description="Bulk-fill missing linescores in wp_playoffs.",
    )
    ap.add_argument('--year', type=int, help="Only this year")
    ap.add_argument('--week', type=int, choices=[15, 16],
                    help="Only this week (15 = Playoffs, 16 = Posse Bowl)")
    ap.add_argument('--since', type=int, help="Year >= SINCE")
    ap.add_argument('--limit', type=int, help="Cap rows processed")
    ap.add_argument('--delay', type=float, default=1.0,
                    help="Seconds to sleep between rows (default 1.0)")
    ap.add_argument('--mode', choices=['missing', 'twopt', 'all'],
                    default='missing',
                    help="missing=rows without a linescore (default); "
                         "twopt=rows already scraped where actual−expected=1 "
                         "and twopt=0; all=both")
    ap.add_argument('--dry-run', action='store_true',
                    help="Print the plan but write nothing")
    args = ap.parse_args()

    rows = fetch_missing(year=args.year, week=args.week, since=args.since,
                         limit=args.limit, mode=args.mode)
    if not rows:
        print("Nothing missing — wp_playoffs is fully populated.")
        return 0

    # Quick summary
    by_yr = {}
    for r in rows:
        by_yr.setdefault(int(r['year']), 0)
        by_yr[int(r['year'])] += 1
    print(f"Found {len(rows)} rows missing linescore data:")
    for yr in sorted(by_yr):
        print(f"   {yr}: {by_yr[yr]}")
    if args.dry_run:
        print("\n(dry run — no DB writes)")
    print()

    tally = {'ok': 0, 'dnp': 0, 'skip': 0, 'fail': 0}
    for i, r in enumerate(rows, 1):
        status = process_row(r, dry_run=args.dry_run)
        tally[status] = tally.get(status, 0) + 1
        if not args.dry_run and i < len(rows):
            time.sleep(args.delay)

    print()
    print("Done.")
    for k in ('ok', 'dnp', 'skip', 'fail'):
        print(f"   {k:<5} {tally.get(k, 0)}")
    return 0 if tally.get('fail', 0) == 0 else 1


if __name__ == '__main__':
    sys.exit(main())
