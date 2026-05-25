#!/usr/bin/env python3
"""
Finds discrepancies between wp_drafts (pick ownership changes) and wp_trades (recorded trades).

wp_drafts columns used:
  - pickord  = original team that owned the pick slot
  - team     = team that actually used the pick to draft
  - tradeid  = 0 means no trade; non-zero links to wp_trades.id

Pick string format in wp_trades.picks1/picks2: "YYYY.RR.NN"
  where RR = round, NN = roundnum (position within the round)

Three types of discrepancies:
  A. pickord != team AND tradeid = 0  — pick changed hands with no trade linked
  B. tradeid != 0 AND pickord != team, but pick string not found in that trade record
  C. pick listed in a trade but pickord == team in wp_drafts (didn't change hands)
"""

import sys
import mysql.connector
import datetime

MYSQL_SOCKET = "/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock"
MYSQL_USER   = "root"
MYSQL_PASS   = "root"
MYSQL_DB     = "local"

def connect():
    return mysql.connector.connect(
        unix_socket=MYSQL_SOCKET,
        user=MYSQL_USER,
        password=MYSQL_PASS,
        database=MYSQL_DB,
    )

def fmt_pick(year, rnd, num):
    return f"{year}.{str(rnd).zfill(2)}.{str(num).zfill(2)}"

def ordinal(n):
    n = int(n)
    suffix = {1:"st",2:"nd",3:"rd"}.get(n % 10 if n % 100 not in [11,12,13] else 0, "th")
    return f"{n}{suffix}"

def pick_label(year, rnd, num=None):
    label = f"{year} {ordinal(rnd)} Rd"
    if num is not None:
        label += f" (pick {int(num)})"
    return label

def main():
    conn = connect()
    cur = conn.cursor()

    # ── Load all draft picks ──────────────────────────────────────────────────
    cur.execute("""
        SELECT id, year, round, roundnum, picknum, pickord, team,
               playerfirst, playerlast, pos, tradeid
        FROM wp_drafts
        ORDER BY year, round, roundnum
    """)
    drafts = cur.fetchall()

    # ── Load all trades ───────────────────────────────────────────────────────
    cur.execute("""
        SELECT id, year, team1, picks1, team2, picks2, notes
        FROM wp_trades
        ORDER BY year, id
    """)
    trades_raw = cur.fetchall()

    trades = {}
    for row in trades_raw:
        tid, yr, t1, p1, t2, p2, notes = row
        picks1 = [p.strip() for p in (p1 or "").split(",") if p.strip()]
        picks2 = [p.strip() for p in (p2 or "").split(",") if p.strip()]
        trades[tid] = {
            "year": yr, "team1": t1, "picks1": set(picks1),
            "team2": t2, "picks2": set(picks2),
            "notes": notes or "",
            "all_picks": set(picks1) | set(picks2),
        }

    # pick_str → [(trade_id, receiving_team)]
    all_traded_picks = {}
    for tid, t in trades.items():
        for p in t["picks1"]:
            all_traded_picks.setdefault(p, []).append((tid, t["team1"]))
        for p in t["picks2"]:
            all_traded_picks.setdefault(p, []).append((tid, t["team2"]))

    print("=" * 70)
    print("PFL TRADE / DRAFT DISCREPANCY REPORT")
    print("=" * 70)

    # ── TYPE A: pick changed hands but no tradeid ─────────────────────────────
    type_a = [d for d in drafts if d[5] != d[6] and (not d[10] or d[10] == 0)]
    print(f"\n{'─'*70}")
    print(f"TYPE A — Pick changed hands with NO trade linked ({len(type_a)} found)")
    print(f"  (wp_drafts.pickord != team AND tradeid = 0)")
    print(f"{'─'*70}")
    if type_a:
        for d in type_a:
            did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, tradeid = d
            player = f"{first} {last} ({pos})" if first else "—"
            pick_str = fmt_pick(yr, rnd, rnum)
            in_trade = all_traded_picks.get(pick_str, [])
            hint = ""
            if in_trade:
                hint = f"  ← in trade(s) {[t[0] for t in in_trade]} (just missing tradeid link)"
            print(f"  {pick_label(yr, rnd, pnum):30s}  {pickord} → {team}  |  {player}{hint}")
    else:
        print("  ✓ None found.")

    # ── TYPE B: tradeid set but pick not in that trade record ─────────────────
    type_b = []
    for d in drafts:
        did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, tradeid = d
        if not tradeid or tradeid == 0:
            continue
        if pickord == team:
            continue  # pick didn't change hands despite having tradeid
        trade = trades.get(tradeid)
        if not trade:
            type_b.append((d, f"tradeid {tradeid} points to non-existent trade"))
            continue
        pick_str = fmt_pick(yr, rnd, rnum)
        if pick_str not in trade["all_picks"]:
            type_b.append((d, f"trade #{tradeid} ({trade['team1']}↔{trade['team2']}, {trade['year']}) "
                               f"doesn't list pick {pick_str}"))

    print(f"\n{'─'*70}")
    print(f"TYPE B — tradeid set but pick not found in trade record ({len(type_b)} found)")
    print(f"{'─'*70}")
    if type_b:
        for d, reason in type_b:
            did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, tradeid = d
            player = f"{first} {last} ({pos})" if first else "—"
            print(f"  {pick_label(yr, rnd, pnum):30s}  {pickord} → {team}  |  {player}")
            print(f"    ↳ {reason}")
    else:
        print("  ✓ None found.")

    # ── TYPE C: pick in trade but pickord == team in wp_drafts ───────────────
    draft_by_pick = {}
    for d in drafts:
        did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, tradeid = d
        key = fmt_pick(yr, rnd, rnum)
        draft_by_pick[key] = d

    current_year = datetime.datetime.now().year
    type_c_past = []
    type_c_future = []
    for pick_str, recipients in all_traded_picks.items():
        yr = int(pick_str.split(".")[0])
        d = draft_by_pick.get(pick_str)
        if d is None:
            entry = (pick_str, recipients, None, "pick not found in wp_drafts")
        else:
            did, yr2, rnd, rnum, pnum, pickord, team, first, last, pos, tradeid = d
            if pickord == team:
                entry = (pick_str, recipients, d, "pickord == team (never changed hands in draft)")
            else:
                continue  # correctly traded, not a discrepancy
        if yr <= current_year:
            type_c_past.append(entry)
        else:
            type_c_future.append(entry)

    print(f"\n{'─'*70}")
    print(f"TYPE C — Pick in trade but didn't change hands in draft ({len(type_c_past)} past, {len(type_c_future)} future/pending)")
    print(f"{'─'*70}")
    if type_c_past:
        for pick_str, recipients, d, reason in type_c_past:
            parts = pick_str.split(".")
            yr, rnd, rnum = parts[0], parts[1], parts[2]
            trade_ids = [r[0] for r in recipients]
            recv_teams = [r[1] for r in recipients]
            label = pick_label(yr, rnd)
            if d:
                _, _, _, _, pnum, pickord, team, first, last, pos, tradeid = d
                player = f"{first} {last} ({pos})" if first else "—"
                print(f"  {label:30s}  drafted by {team} (same as original)  |  {player}")
            else:
                print(f"  {label:30s}  pick not in wp_drafts")
            print(f"    ↳ Trade(s) #{trade_ids} sent pick to {recv_teams} — {reason}")
    else:
        print("  ✓ None found.")

    if type_c_future:
        print(f"\n  Future picks in trades (not yet drafted — expected):")
        for pick_str, recipients, d, reason in type_c_future:
            parts = pick_str.split(".")
            yr, rnd = parts[0], parts[1]
            recv_teams = [r[1] for r in recipients]
            trade_ids = [r[0] for r in recipients]
            print(f"    {pick_label(yr, rnd):28s}  → {recv_teams}  (trade #{trade_ids})")

    # ── Summary ───────────────────────────────────────────────────────────────
    print(f"\n{'='*70}")
    print("SUMMARY")
    print(f"{'='*70}")
    print(f"  Type A (pick moved, no trade linked):        {len(type_a)}")
    print(f"  Type B (trade linked but pick not in trade): {len(type_b)}")
    print(f"  Type C past (in trade but not in draft):     {len(type_c_past)}")
    print(f"  Type C future (pending picks in trades):     {len(type_c_future)}")
    total = len(type_a) + len(type_b) + len(type_c_past)
    print(f"  ─────────────────────────────────────────")
    print(f"  Total actionable discrepancies:             {total}")

    cur.close()
    conn.close()

if __name__ == "__main__":
    main()
