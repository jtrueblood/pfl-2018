#!/usr/bin/env python3
import mysql.connector

# Connect to database
conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='root',
    database='local',
    unix_socket='/Users/jamietrueblood/Library/Application Support/Local/run/JYl9oL2fW/mysql/mysqld.sock'
)
cursor = conn.cursor()

# Get all first-round kickers
cursor.execute("""
    SELECT year, playerid 
    FROM wp_drafts 
    WHERE pos = 'PK' AND round = '01' 
    ORDER BY year
""")
first_round_kickers = cursor.fetchall()

# Group by year
years_with_first_round_pk = {}
for year, playerid in first_round_kickers:
    if year not in years_with_first_round_pk:
        years_with_first_round_pk[year] = []
    years_with_first_round_pk[year].append(playerid)

# Analyze each year
results = []
for year, kickers in sorted(years_with_first_round_pk.items()):
    # Get top 5 kickers for that season
    cursor.execute("""
        SELECT playerid, points 
        FROM wp_season_leaders 
        WHERE season = %s AND playerid LIKE '%%PK' 
        ORDER BY points DESC 
        LIMIT 5
    """, (year,))
    top5 = [row[0] for row in cursor.fetchall()]
    
    # Check if any first-round picks are in top 5
    top5_count = sum(1 for k in kickers if k in top5)
    
    results.append({
        'year': year,
        'num_first_round': len(kickers),
        'in_top5': top5_count,
        'kickers': kickers,
        'top5': top5
    })

# Print results
print("First-Round Kicker Performance Analysis")
print("=" * 80)
print()

success_count = 0
total_kickers = 0

for r in results:
    total_kickers += r['num_first_round']
    success_count += r['in_top5']
    
    if r['in_top5'] > 0:
        print(f"✓ {r['year']}: {r['in_top5']}/{r['num_first_round']} first-round kickers in top 5")
        for k in r['kickers']:
            if k in r['top5']:
                rank = r['top5'].index(k) + 1
                print(f"   - {k} finished #{rank}")
    else:
        print(f"✗ {r['year']}: 0/{r['num_first_round']} first-round kickers in top 5")

print()
print("=" * 80)
print(f"SUMMARY:")
print(f"Total first-round kickers: {total_kickers}")
print(f"Finished in top 5: {success_count}")
print(f"Success rate: {success_count}/{total_kickers} = {100*success_count/total_kickers:.1f}%")
print()
print(f"Years with first-round kickers: {len(results)}")
print(f"Years where at least one finished top 5: {sum(1 for r in results if r['in_top5'] > 0)}")

conn.close()
