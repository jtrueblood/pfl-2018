#!/usr/bin/env python3
"""
Analyzes trade/draft discrepancies and proposes SQL fixes.

For each pick where pickord != team (changed hands), tries to find the
matching trade record in wp_trades. Outputs:

  1. CONFIRMED matches  — pick string found in exactly one trade → safe to UPDATE
  2. AMBIGUOUS matches  — pick string found in multiple trades → needs manual review
  3. GAPS              — pick changed hands but no trade record exists for it

Also handles Type B (tradeid=1 placeholder) separately.
"""

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

def pick_label(year, rnd, pnum=None):
    label = f"{year} {ordinal(rnd)} Rd"
    if pnum is not None:
        label += f" (#{int(pnum)})"
    return label

def main():
    conn = connect()
    cur  = conn.cursor()

    cur.execute("""
        SELECT id, year, round, roundnum, picknum, pickord, team,
               playerfirst, playerlast, pos, tradeid
        FROM wp_drafts
        ORDER BY year, round, roundnum
    """)
    drafts = cur.fetchall()

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

    # pick_str → [(trade_id, side, receiving_team)]
    pick_to_trades = {}
    for tid, t in trades.items():
        for p in t["picks1"]:
            pick_to_trades.setdefault(p, []).append((tid, "picks1", t["team1"]))
        for p in t["picks2"]:
            pick_to_trades.setdefault(p, []).append((tid, "picks2", t["team2"]))

    # ── Collect all picks that changed hands ──────────────────────────────────
    # Separate Type A (tradeid=0) and Type B (tradeid=1 placeholder)
    type_a = []  # tradeid = 0, pickord != team
    type_b = []  # tradeid = 1 (placeholder), pickord != team

    for d in drafts:
        did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, tradeid = d
        if pickord == team:
            continue
        if not tradeid or tradeid == 0:
            type_a.append(d)
        elif tradeid == 1:
            type_b.append(d)

    def classify(picks_list, label):
        confirmed  = []  # (draft_row, trade_id, trade_info)
        ambiguous  = []  # (draft_row, [(trade_id, trade_info)])
        gaps       = []  # draft_row — no matching trade exists
        wrong_side = []  # found in trade but receiving team doesn't match acteam

        for d in picks_list:
            did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, tradeid = d
            pick_str = fmt_pick(yr, rnd, rnum)
            matches  = pick_to_trades.get(pick_str, [])

            if not matches:
                gaps.append(d)
            elif len(matches) == 1:
                tid, side, recv_team = matches[0]
                t = trades[tid]
                confirmed.append((d, tid, t, recv_team))
            else:
                # Multiple trades contain this pick — flag as ambiguous
                ambiguous.append((d, [(tid, trades[tid], recv) for tid, _, recv in matches]))

        return confirmed, ambiguous, gaps

    # ── Process Type B first (tradeid=1 placeholder) ──────────────────────────
    b_confirmed, b_ambiguous, b_gaps = classify(type_b, "B")

    print("=" * 72)
    print("PFL DRAFT ↔ TRADE LINK REPAIR REPORT")
    print("=" * 72)

    # ─── TYPE B: tradeid=1 placeholder ────────────────────────────────────────
    print(f"\n{'─'*72}")
    print(f"TYPE B — Picks with tradeid=1 (placeholder) that need correct trade ID")
    print(f"  ({len(type_b)} total: {len(b_confirmed)} confirmed, {len(b_ambiguous)} ambiguous, {len(b_gaps)} gaps)")
    print(f"{'─'*72}")

    if b_confirmed:
        print(f"\n  CONFIRMED MATCHES ({len(b_confirmed)}) — safe to UPDATE:")
        for d, tid, t, recv_team in b_confirmed:
            did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, _ = d
            player = f"{first} {last} ({pos})" if first else "—"
            recv_note = f" [receiving={recv_team}]" if recv_team != team else ""
            print(f"    {pick_label(yr, rnd, pnum):28s}  {pickord}→{team}  |  {player}")
            print(f"      trade #{tid} ({t['team1']}↔{t['team2']}, {t['year']}){recv_note}")
            if t['notes']:
                print(f"      notes: {t['notes'][:80]}")

    if b_ambiguous:
        print(f"\n  AMBIGUOUS ({len(b_ambiguous)}) — multiple trades claim this pick:")
        for d, trade_opts in b_ambiguous:
            did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, _ = d
            player = f"{first} {last} ({pos})" if first else "—"
            print(f"    {pick_label(yr, rnd, pnum):28s}  {pickord}→{team}  |  {player}")
            for tid, t, recv in trade_opts:
                print(f"      → trade #{tid} ({t['team1']}↔{t['team2']}, {t['year']}) recv={recv}")

    if b_gaps:
        print(f"\n  GAPS ({len(b_gaps)}) — pick changed hands but no trade record lists it:")
        for d in b_gaps:
            did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, _ = d
            player = f"{first} {last} ({pos})" if first else "—"
            print(f"    {pick_label(yr, rnd, pnum):28s}  {pickord}→{team}  |  {player}")

    # ─── TYPE A: tradeid=0, no link at all ────────────────────────────────────
    a_confirmed, a_ambiguous, a_gaps = classify(type_a, "A")

    print(f"\n{'─'*72}")
    print(f"TYPE A — Picks with tradeid=0 that changed hands (no trade linked)")
    print(f"  ({len(type_a)} total: {len(a_confirmed)} confirmed, {len(a_ambiguous)} ambiguous, {len(a_gaps)} gaps)")
    print(f"{'─'*72}")

    if a_confirmed:
        print(f"\n  CONFIRMED MATCHES ({len(a_confirmed)}) — can add tradeid link:")
        for d, tid, t, recv_team in a_confirmed:
            did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, _ = d
            player = f"{first} {last} ({pos})" if first else "—"
            recv_note = f" [recv={recv_team}]" if recv_team != team else ""
            print(f"    {pick_label(yr, rnd, pnum):28s}  {pickord}→{team}  |  {player}")
            print(f"      trade #{tid} ({t['team1']}↔{t['team2']}, {t['year']}){recv_note}")

    if a_ambiguous:
        print(f"\n  AMBIGUOUS ({len(a_ambiguous)}) — multiple trades claim this pick:")
        for d, trade_opts in a_ambiguous:
            did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, _ = d
            player = f"{first} {last} ({pos})" if first else "—"
            print(f"    {pick_label(yr, rnd, pnum):28s}  {pickord}→{team}  |  {player}")
            for tid, t, recv in trade_opts:
                print(f"      → trade #{tid} ({t['team1']}↔{t['team2']}, {t['year']}) recv={recv}")

    if a_gaps:
        print(f"\n  GAPS ({len(a_gaps)}) — no trade record; need new wp_trades entry:")
        # Group by year for easier reading
        by_year = {}
        for d in a_gaps:
            yr = d[1]
            by_year.setdefault(yr, []).append(d)
        for yr in sorted(by_year):
            print(f"\n    ── {yr} ──")
            for d in by_year[yr]:
                did, yr2, rnd, rnum, pnum, pickord, team, first, last, pos, _ = d
                player = f"{first} {last} ({pos})" if first else "—"
                pick_str = fmt_pick(yr2, rnd, rnum)
                print(f"    {pick_str}  {pickord}→{team}  |  {player}")

    # ─── SQL UPDATE PREVIEW ───────────────────────────────────────────────────
    all_confirmed = [(d, tid) for d, tid, t, recv in b_confirmed + a_confirmed]

    print(f"\n{'='*72}")
    print(f"PROPOSED SQL UPDATES ({len(all_confirmed)} statements)")
    print(f"{'='*72}")
    print(f"-- Review carefully before running. Back up wp_drafts first.")
    print(f"-- These set tradeid for picks where the match is unambiguous.\n")

    for d, tid in all_confirmed:
        did, yr, rnd, rnum, pnum, pickord, team, first, last, pos, _ = d
        print(f"UPDATE wp_drafts SET tradeid = {tid} WHERE id = {did};  -- {pick_label(yr, rnd, pnum)} {pickord}→{team} | {first} {last}")

    # ─── Summary ──────────────────────────────────────────────────────────────
    total_gaps    = len(b_gaps) + len(a_gaps)
    total_ambig   = len(b_ambiguous) + len(a_ambiguous)
    total_confirm = len(all_confirmed)

    print(f"\n{'='*72}")
    print(f"SUMMARY")
    print(f"{'='*72}")
    print(f"  Type B picks (tradeid=1 placeholder):  {len(type_b)}")
    print(f"  Type A picks (tradeid=0, no link):     {len(type_a)}")
    print(f"  ─────────────────────────────────────")
    print(f"  Confirmed → auto-fixable with UPDATE:  {total_confirm}")
    print(f"  Ambiguous → manual review needed:      {total_ambig}")
    print(f"  Gaps → need new wp_trades record:      {total_gaps}")

    cur.close()
    conn.close()

if __name__ == "__main__":
    main()
