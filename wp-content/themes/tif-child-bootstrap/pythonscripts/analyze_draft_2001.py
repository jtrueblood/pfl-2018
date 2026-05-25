import re
from collections import namedtuple

DraftPick = namedtuple('DraftPick', ['pick_num', 'round', 'team', 'orig_team', 'name', 'position', 'season_pts', 'career_pts'])

# Manually extracted picks from the 2001 draft HTML
picks = [
    # Round 1
    DraftPick(1, 1, 'TSG', 'TSG', 'Michael Vick', 'QB', 1, 585),
    DraftPick(2, 1, 'CMN', 'CMN', 'Rich Gannon', 'QB', 21, 21),
    DraftPick(3, 1, 'PEP', 'PEP', 'Jeff Wilkins', 'PK', 85, 85),
    DraftPick(4, 1, 'WRZ', 'WRZ', 'Donnell Bennett', 'RB', 0, 0),
    DraftPick(5, 1, 'SON', 'SON', 'Tony Gonzalez', 'TE', 27, 27),
    DraftPick(6, 1, 'PHR', 'PHR', 'Morton Anderson', 'PK', 9, 0),
    DraftPick(7, 1, 'BST', 'BST', 'Mike Vanderjagt', 'PK', 75, 416),
    DraftPick(8, 1, 'BUL', 'BUL', 'Joe Horn', 'WR', 7, 9),
    DraftPick(9, 1, 'ETS', 'ETS', 'Olindo Mare', 'PK', 61, 61),
    DraftPick(10, 1, 'SNR', 'SNR', 'Warrick Dunn', 'RB', 11, 15),
    
    # Round 2
    DraftPick(11, 2, 'TSG', 'TSG', 'Ed McCaffrey', 'WR', 11, 35),
    DraftPick(12, 2, 'CMN', 'CMN', 'Emmitt Smith', 'RB', 23, 0),
    DraftPick(13, 2, 'BST', 'PEP', 'Tim Brown', 'WR', 26, 549),
    DraftPick(14, 2, 'WRZ', 'WRZ', 'Kerry Collins', 'QB', 31, 50),
    DraftPick(15, 2, 'SON', 'SON', 'Brad Johnson', 'QB', 27, 27),
    DraftPick(16, 2, 'PHR', 'PHR', 'Elvis Grbac', 'QB', 10, 10),
    DraftPick(17, 2, 'BST', 'BST', 'Ladanian Tomlinson', 'RB', 34, 34),
    DraftPick(18, 2, 'TSG', 'BUL', 'Lamar Smith', 'RB', 18, 18),
    DraftPick(19, 2, 'ETS', 'ETS', 'Mushin Muhammed', 'WR', 0, 18),
    DraftPick(20, 2, 'SNR', 'SNR', 'Ryan Longwell', 'PK', 66, 70),
    
    # Round 3
    DraftPick(21, 3, 'TSG', 'TSG', 'Koren Robinson', 'WR', 0, 23),
    DraftPick(22, 3, 'ETS', 'CMN', 'Rob Johnson', 'QB', 0, 0),
    DraftPick(23, 3, 'BST', 'PEP', 'Derrick Mason', 'WR', 0, 0),
    DraftPick(24, 3, 'WRZ', 'WRZ', 'Matt Stover', 'PK', 31, 50),
    DraftPick(25, 3, 'SON', 'SON', 'Tim Seder', 'PK', 0, 0),
    DraftPick(26, 3, 'PHR', 'PHR', 'Mike Anderson', 'RB', 11, 11),
    DraftPick(27, 3, 'PEP', 'BST', 'Ricky Watters', 'RB', 0, 0),
    DraftPick(28, 3, 'BUL', 'BUL', 'Martin Gramatica', 'PK', 62, 269),
    DraftPick(29, 3, 'CMN', 'ETS', 'Jason Elam', 'PK', 81, 295),
    DraftPick(30, 3, 'SNR', 'SNR', 'Antonio Freeman', 'WR', 8, 318),
    
    # Round 4
    DraftPick(31, 4, 'SNR', 'TSG', 'Jamal Lewis', 'RB', 0, 92),
    DraftPick(32, 4, 'CMN', 'CMN', 'Keyshawn Johnson', 'WR', 14, 0),
    DraftPick(33, 4, 'SNR', 'PEP', 'Matt Hasselbeck', 'QB', 0, 9),
    DraftPick(34, 4, 'CMN', 'WRZ', 'Jamal Anderson', 'RB', 0, 43),
    DraftPick(35, 4, 'SON', 'SON', 'Jimmy Smith', 'WR', 0, 0),
    DraftPick(36, 4, 'PHR', 'PHR', 'Amani Toomer', 'WR', 45, 45),
    DraftPick(37, 4, 'PEP', 'BST', 'Drew Bledsoe', 'QB', 0, 225),
    DraftPick(38, 4, 'BUL', 'BUL', 'James Stewart', 'RB', 0, 0),
    DraftPick(39, 4, 'ETS', 'ETS', 'Charlie Garner', 'RB', 0, 0),
    DraftPick(40, 4, 'SNR', 'SNR', 'Mike Hollis', 'PK', 4, 4),
    
    # Round 5
    DraftPick(41, 5, 'TSG', 'TSG', 'Jose Cortez', 'PK', 68, 122),
    DraftPick(42, 5, 'CMN', 'CMN', 'Michael Westbrook', 'WR', 0, 0),
    DraftPick(43, 5, 'PEP', 'PEP', 'David Boston', 'WR', 97, 0),
    DraftPick(44, 5, 'CMN', 'WRZ', 'Jeff George', 'QB', 0, 0),
    DraftPick(45, 5, 'SON', 'SON', 'David Akers', 'PK', 59, 126),
    DraftPick(46, 5, 'PHR', 'PHR', 'Vinny Testaverde', 'QB', 17, 0),
    DraftPick(47, 5, 'BST', 'BST', 'Joe Nedney', 'PK', 6, 77),
    DraftPick(48, 5, 'BUL', 'BUL', 'Tim Couch', 'QB', 5, 5),
    DraftPick(49, 5, 'ETS', 'ETS', 'Chris Wenkie', 'QB', 0, 0),
    DraftPick(50, 5, 'SNR', 'SNR', 'Michael Pittman', 'RB', 7, 7),
    
    # Round 6
    DraftPick(51, 6, 'BUL', 'BUL', 'Sebastian Janikowski', 'PK', 6, 6),
    DraftPick(52, 6, 'PEP', 'PEP', 'James Thrash', 'WR', 0, 0),
    DraftPick(53, 6, 'WRZ', 'WRZ', 'Gary Anderson', 'PK', 28, 0),
]

# Calculate value score for each pick
def calculate_value_score(pick):
    """
    Calculate a value score based on:
    - Career points (most important)
    - How late in the draft (draft position penalty - later = better value)
    - Season points (immediate impact)
    """
    # Normalize by pick number (later picks get bonus multiplier)
    draft_position_multiplier = pick.pick_num / 10  # Ranges from 0.1 to 5.3
    
    # Weight career points heavily
    career_value = pick.career_pts * 2
    season_value = pick.season_pts * 1.5
    
    # Calculate raw value
    raw_value = career_value + season_value
    
    # Apply draft position bonus (inverse relationship)
    # Later picks get higher multiplier
    value_score = raw_value * (pick.pick_num / 10)
    
    return value_score

# Analyze the picks
print("=" * 80)
print("2001 DRAFT ANALYSIS")
print("=" * 80)
print()

# Add value scores to picks
analyzed_picks = []
for pick in picks:
    value_score = calculate_value_score(pick)
    analyzed_picks.append((pick, value_score))

# Sort by value score
analyzed_picks.sort(key=lambda x: x[1], reverse=True)

# Print top 10 picks
print("TOP 10 PICKS BY VALUE (considering draft position, career pts, season pts):")
print("-" * 80)
print(f"{'Rank':<5} {'Pick#':<6} {'Rd':<4} {'Team':<5} {'Name':<20} {'Pos':<4} {'Seas':<6} {'Career':<7} {'Value':<8}")
print("-" * 80)

for i, (pick, value) in enumerate(analyzed_picks[:10], 1):
    print(f"{i:<5} {pick.pick_num:<6} {pick.round:<4} {pick.team:<5} {pick.name:<20} {pick.position:<4} {pick.season_pts:<6} {pick.career_pts:<7} {value:<8.1f}")

print()
print("=" * 80)
print("KEY INSIGHTS:")
print("=" * 80)

# Find the best pick
best_pick = analyzed_picks[0][0]
print(f"\nBEST PICK: {best_pick.name}")
print(f"  - Pick #{best_pick.pick_num} (Round {best_pick.round})")
print(f"  - Team: {best_pick.team}")
print(f"  - Position: {best_pick.position}")
print(f"  - Season Points: {best_pick.season_pts}")
print(f"  - Career Points: {best_pick.career_pts}")
print(f"  - Value Score: {analyzed_picks[0][1]:.1f}")

# Additional analysis
print("\n" + "-" * 80)
print("OTHER NOTABLE PICKS:")
print("-" * 80)

# Find late round steals (round 3+, high career value)
late_round_steals = [p for p in picks if p.round >= 3 and p.career_pts >= 100]
if late_round_steals:
    print("\nLATE ROUND STEALS (Round 3+, 100+ career pts):")
    for pick in late_round_steals:
        print(f"  - {pick.name}: Pick #{pick.pick_num} (Round {pick.round}), {pick.career_pts} career pts")

# Find first round busts (less than 50 pts)
first_round_busts = [p for p in picks if p.round == 1 and p.career_pts < 50]
if first_round_busts:
    print("\nFIRST ROUND DISAPPOINTMENTS (< 50 career pts):")
    for pick in first_round_busts:
        print(f"  - {pick.name}: Pick #{pick.pick_num}, only {pick.career_pts} career pts")

# Best value by position
print("\nBEST VALUE BY POSITION:")
for pos in ['QB', 'RB', 'WR', 'PK', 'TE']:
    pos_picks = [(p, v) for p, v in analyzed_picks if p.position == pos and p.career_pts > 0]
    if pos_picks:
        best = pos_picks[0]
        print(f"  {pos}: {best[0].name} (Pick #{best[0].pick_num}, {best[0].career_pts} career pts)")
