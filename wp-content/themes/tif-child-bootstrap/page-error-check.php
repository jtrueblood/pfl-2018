<?php
/**
 * Template Name: Error Check
 * Description: A page template to identify inconsistencies in player and team data
 */
?>

<?php get_header(); ?>

<div class="boxed">
    
    <!--CONTENT CONTAINER-->
    <div id="content-container">
        
        <div id="page-title">
            <?php while (have_posts()) : the_post(); ?>
                <h1 class="page-header text-bold"><?php the_title();?></h1>
            <?php endwhile; wp_reset_query(); ?>    
        </div>
        
        <!--Page content-->
        <div id="page-content">

            <?php the_post(); ?>
            
            <div class="entry-content">
                <?php the_content(); ?>

                <style>
                    .error-check-section {
                        margin: 30px 0;
                        padding: 20px;
                        background: #f9f9f9;
                        border-left: 4px solid #dc3232;
                    }
                    .error-check-section.no-errors {
                        border-left-color: #46b450;
                    }
                    .error-check-section h2 {
                        margin-top: 0;
                        color: #333;
                        cursor: pointer;
                        user-select: none;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                    }
                    .error-check-section h2:hover {
                        opacity: 0.8;
                    }
                    .collapse-toggle {
                        font-size: 0.8em;
                        margin-left: 10px;
                        transition: transform 0.3s;
                    }
                    .collapse-toggle.collapsed {
                        transform: rotate(-90deg);
                    }
                    .section-content {
                        overflow: hidden;
                        transition: max-height 0.3s ease-out;
                    }
                    .section-content.collapsed {
                        max-height: 0 !important;
                    }
                    .error-list {
                        list-style: none;
                        padding: 0;
                    }
                    .error-list li {
                        padding: 10px;
                        margin: 5px 0;
                        background: white;
                        border-left: 3px solid #dc3232;
                    }
                    .error-list.two-point-conversions li {
                        border-left-color: #2271b1;
                    }
                    .error-list li a {
                        color: #0073aa;
                        text-decoration: none;
                    }
                    .error-list li a:hover {
                        text-decoration: underline;
                    }
                    .success-message {
                        color: #46b450;
                        font-weight: bold;
                    }
                    .error-count {
                        background: #dc3232;
                        color: white;
                        padding: 2px 8px;
                        border-radius: 3px;
                        font-size: 14px;
                        margin-left: 10px;
                    }
                    .error-count.two-point {
                        background: #2271b1;
                    }
                    .stats-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 15px;
                        margin: 20px 0;
                    }
                    .stat-box {
                        padding: 15px;
                        background: white;
                        border: 1px solid #ddd;
                        text-align: center;
                    }
                    .stat-number {
                        font-size: 32px;
                        font-weight: bold;
                        color: #0073aa;
                    }
                    .stat-label {
                        color: #666;
                        margin-top: 5px;
                    }
                    .copy-script-btn {
                        background: #2271b1;
                        color: white;
                        border: none;
                        padding: 2px 8px;
                        border-radius: 3px;
                        cursor: pointer;
                        font-size: 11px;
                        margin-left: 10px;
                        transition: background 0.2s;
                    }
                    .copy-script-btn:hover {
                        background: #135e96;
                    }
                    .copy-script-btn.copied {
                        background: #46b450;
                    }
                    .copy-transfer-btn {
                        background: #d63638;
                        color: white;
                        border: none;
                        padding: 2px 8px;
                        border-radius: 3px;
                        cursor: pointer;
                        font-size: 11px;
                        margin-left: 5px;
                        transition: background 0.2s;
                    }
                    .copy-transfer-btn:hover {
                        background: #a02020;
                    }
                    .copy-transfer-btn.copied {
                        background: #46b450;
                    }
                    .error-game-row {
                        display: flex;
                        align-items: center;
                        gap: 15px;
                        margin-top: 5px;
                        padding: 5px 0;
                    }
                    .error-game-info {
                        flex: 0 0 auto;
                    }
                    .error-game-actions {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        flex: 0 0 auto;
                    }
                    .error-game-cause {
                        flex: 0 0 auto;
                        margin-left: 15px;
                    }
                    .resources-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 30px 0;
                        background: white;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                    }
                    .resources-table th,
                    .resources-table td {
                        padding: 12px;
                        text-align: left;
                        border: 1px solid #ddd;
                    }
                    .resources-table th {
                        background: #f9f9f9;
                        font-weight: bold;
                        color: #333;
                        position: sticky;
                        top: 0;
                        z-index: 10;
                    }
                    .resources-table tr:hover {
                        background: #f5f5f5;
                    }
                    .resources-table-container {
                        margin: 30px 0;
                        border: 1px solid #ddd;
                    }
                    .resources-section {
                        margin: 40px 0;
                    }
                    .resources-section h2 {
                        color: #333;
                        border-bottom: 3px solid #2271b1;
                        padding-bottom: 10px;
                        margin-bottom: 20px;
                    }
                    .helper-scripts {
                        background: #f0f6fc;
                        border: 1px solid #2271b1;
                        border-radius: 5px;
                        padding: 20px;
                        margin: 20px 0;
                    }
                    .helper-scripts h3 {
                        margin-top: 0;
                        color: #2271b1;
                        font-size: 18px;
                    }
                    .helper-scripts ul {
                        margin: 10px 0;
                        padding-left: 20px;
                    }
                    .helper-scripts li {
                        margin: 8px 0;
                    }
                    .helper-scripts code {
                        background: #fff;
                        padding: 2px 6px;
                        border-radius: 3px;
                        font-family: monospace;
                        color: #d63638;
                    }
                    .helper-scripts .script-desc {
                        color: #666;
                        font-size: 14px;
                        margin-left: 10px;
                    }
                </style>

                <!-- Helper Scripts Documentation -->
                <div class="helper-scripts">
                    <h3>🐍 Python Helper Scripts</h3>
                    <p style="margin: 0 0 15px 0; color: #666;">The following scripts are available in the <code>pythonscripts/</code> directory to help address data issues:</p>
                    <ul>
                        <li>
                            <strong><code>getplayernfldata.py</code></strong>
                            <span class="script-desc">- Fetch NFL stats from ESPN API (2001+) or wp_stathead tables (1991-2000) and insert into player tables</span>
                            <br><code style="font-size: 12px;">python3 getplayernfldata.py "Player Name" YEAR WEEK [Yes]</code>
                        </li>
                        <li>
                            <strong><code>player_boxscore.py</code></strong>
                            <span class="script-desc">- Display detailed player boxscore and calculate expected PFL points for a specific game</span>
                            <br><code style="font-size: 12px;">python3 player_boxscore.py PLAYER_ID YEAR WEEK</code>
                        </li>
                        <li>
                            <strong><code>find_player_by_points.py</code></strong>
                            <span class="script-desc">- Search for players who scored specific points in a given week (useful for identifying wrong player entries)</span>
                            <br><code style="font-size: 12px;">python3 find_player_by_points.py YEAR WEEK POSITION POINTS</code>
                        </li>
                        <li>
                            <strong><code>transfer_player_game.py</code></strong>
                            <span class="script-desc">- Transfer a player from one team to another for a specific game (useful for fixing wrong player roster assignments)</span>
                            <br><code style="font-size: 12px;">python3 transfer_player_game.py PLAYER_ID FROM_TEAM TO_TEAM YEAR WEEK</code>
                        </li>
                        <li>
                            <strong><code>add_player.py</code></strong>
                            <span class="script-desc">- Add a new player to the database with interactive prompts (creates player record in wp_players and new player stats table)</span>
                            <br><code style="font-size: 12px;">python3 add_player.py</code>
                        </li>
                    </ul>
                </div>

                <?php
                // Get all players and teams from database tables
                global $wpdb;
                
                // Get all players from wp_players table
                $players = $wpdb->get_results("SELECT * FROM wp_players", ARRAY_A);
                
                // Get all teams from wp_teams table  
                $teams = $wpdb->get_results("SELECT * FROM wp_teams", ARRAY_A);
                
                // Load weekly update PDFs from ACF options
                $weekly_pdfs = array();
                $pdf_count = get_option('options_update_pdfs', 0);
                for ($i = 0; $i < $pdf_count; $i++) {
                    $week_id = get_option("options_update_pdfs_{$i}_week_id");
                    $pdf_id = get_option("options_update_pdfs_{$i}_pdf_file");
                    if ($week_id && $pdf_id) {
                        $pdf_url = wp_get_attachment_url($pdf_id);
                        if ($pdf_url) {
                            $weekly_pdfs[$week_id] = $pdf_url;
                        }
                    }
                }

                // Initialize error tracking
                $errors = array(
                    'players_missing_team' => array(),
                    'players_with_invalid_team' => array(),
                    'players_missing_position' => array(),
                    'players_missing_number' => array(),
                    'players_missing_college' => array(),
                    'players_missing_picture' => array(),
                    'duplicate_numbers_per_team' => array(),
                    'missing_player_stats' => array(),
                    'invalid_stat_values' => array(),
                    'orphaned_relationships' => array(),
                    'likely_two_point_conversions' => array(),
                    'scoring_entry_errors_1991_1993' => array(),
                    'other_scoring_discrepancies_1991' => array(),
                    'other_scoring_discrepancies_1992plus' => array()
                );

                // Get all team IDs for validation (using 'int' field from wp_teams)
                $valid_team_ids = array_column($teams, 'int');

                // Check player data
                foreach ($players as $player) {
                    $player_id = $player['p_id'];
                    $player_name = $player['first'] . ' ' . $player['last'];
                    // Link to player page instead of edit link
                    $player_link = home_url('/player/?id=' . $player_id);

                    // Check for team assignment using wp_rosters
                    $player_rosters = $wpdb->get_results(
                        $wpdb->prepare("SELECT DISTINCT team FROM wp_rosters WHERE pid = %s", $player_id),
                        ARRAY_A
                    );
                    
                    if (empty($player_rosters)) {
                        $errors['players_missing_team'][] = array(
                            'name' => $player_name,
                            'link' => $player_link,
                            'id' => $player_id
                        );
                    }

                    // Check for position (field index 3 in wp_players)
                    $position = $player['position'];
                    if (empty($position)) {
                        $errors['players_missing_position'][] = array(
                            'name' => $player_name,
                            'link' => $player_link,
                            'id' => $player_id
                        );
                    }

                    // Check for player number - compare number column with numberarray
                    // Note: 0 is a valid jersey number, so check for null/empty string instead of using empty()
                    $number = $player['number'];
                    $number_array = json_decode($player['numberarray'], true);
                    
                    $has_number = ($number !== null && $number !== '');
                    $has_number_array = !empty($number_array);
                    
                    $issue_note = '';
                    if (!$has_number && !$has_number_array) {
                        $issue_note = 'Both number and numberarray are blank';
                    } elseif (!$has_number_array && $has_number) {
                        $issue_note = 'Number in "number" column (' . $number . ') but not in numberarray';
                    } elseif (!$has_number && $has_number_array) {
                        $issue_note = 'Number in numberarray but "number" column is blank';
                    }
                    
                    if ($issue_note) {
                        $errors['players_missing_number'][] = array(
                            'name' => $player_name,
                            'link' => $player_link,
                            'id' => $player_id,
                            'note' => $issue_note
                        );
                    }

                    // Check for college (field index 8 in wp_players)
                    $college = $player['college'];
                    if (empty($college)) {
                        $errors['players_missing_college'][] = array(
                            'name' => $player_name,
                            'link' => $player_link,
                            'id' => $player_id
                        );
                    }

                    // Check for profile picture
                    $player_image = get_attachment_url_by_slug($player_id);
                    if (empty($player_image)) {
                        $errors['players_missing_picture'][] = array(
                            'name' => $player_name,
                            'link' => $player_link,
                            'id' => $player_id
                        );
                    }

                    // Check for basic stats (height index 6, weight index 7)
                    $height = $player['height'];
                    $weight = $player['weight'];
                    
                    if (empty($height) || empty($weight)) {
                        $errors['missing_player_stats'][] = array(
                            'name' => $player_name,
                            'link' => $player_link,
                            'id' => $player_id
                        );
                    }
                }

                // Check for duplicate numbers per team using wp_rosters
                $duplicate_check = $wpdb->get_results(
                    "SELECT r1.pid as pid1, r1.team, r1.number, p1.first as first1, p1.last as last1,
                            r2.pid as pid2, p2.first as first2, p2.last as last2
                     FROM wp_rosters r1
                     JOIN wp_rosters r2 ON r1.team = r2.team AND r1.year = r2.year AND r1.number = r2.number AND r1.pid < r2.pid
                     JOIN wp_players p1 ON r1.pid = p1.p_id
                     JOIN wp_players p2 ON r2.pid = p2.p_id
                     WHERE r1.number IS NOT NULL AND r1.number != ''
                     ORDER BY r1.team, r1.number",
                    ARRAY_A
                );
                
                $duplicate_numbers = array();
                foreach ($duplicate_check as $dup) {
                    $team_name = $dup['team'];
                    $number = $dup['number'];
                    $key = $team_name . '-' . $number;
                    
                    if (!isset($duplicate_numbers[$key])) {
                        $duplicate_numbers[$key] = array(
                            'team' => $team_name,
                            'number' => $number,
                            'players' => array()
                        );
                    }
                    
                    // Add both players if not already added
                    $found1 = false;
                    $found2 = false;
                    foreach ($duplicate_numbers[$key]['players'] as $p) {
                        if ($p['id'] == $dup['pid1']) $found1 = true;
                        if ($p['id'] == $dup['pid2']) $found2 = true;
                    }
                    
                    if (!$found1) {
                        $duplicate_numbers[$key]['players'][] = array(
                            'id' => $dup['pid1'],
                            'name' => $dup['first1'] . ' ' . $dup['last1'],
                            'link' => home_url('/player/?id=' . $dup['pid1'])
                        );
                    }
                    if (!$found2) {
                        $duplicate_numbers[$key]['players'][] = array(
                            'id' => $dup['pid2'],
                            'name' => $dup['first2'] . ' ' . $dup['last2'],
                            'link' => home_url('/player/?id=' . $dup['pid2'])
                        );
                    }
                }
                
                $errors['duplicate_numbers_per_team'] = array_values($duplicate_numbers);

                // Check for scoring discrepancies (NFL expected score vs PFL actual score)
                // Get all player IDs
                $all_player_ids = array_column($players, 'p_id');
                
                foreach ($all_player_ids as $pid) {
                    // Get player info
                    $player_info = $wpdb->get_row(
                        $wpdb->prepare("SELECT playerFirst, playerLast, position FROM wp_players WHERE p_id = %s", $pid),
                        ARRAY_A
                    );
                    
                    if (!$player_info) continue;
                    
                    $player_name = $player_info['playerFirst'] . ' ' . $player_info['playerLast'];
                    $player_link = home_url('/player/?id=' . $pid);
                    
                    // Check if player table exists
                    $table_name = $pid;
                    $table_exists = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                        DB_NAME,
                        $table_name
                    ));
                    
                    if (!$table_exists) continue;
                    
                    // Get all game data for this player
                    // Exclude cases where NFL score is negative and PFL is 0 (impossible to have negative PFL scores)
                    $game_data = $wpdb->get_results(
                        "SELECT * FROM `{$table_name}` WHERE scorediff != 0 AND NOT (nflscore < 0 AND points = 0)",
                        ARRAY_A
                    );
                    
                    // Get player's PFL team for this year
                    $player_team = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT team FROM wp_rosters WHERE pid = %s AND year = %d",
                            $pid,
                            $game_data[0]['year'] ?? 0
                        )
                    );
                    
                    // Add any discrepancies to errors array
                    // Separate into likely 2-point conversions vs other issues
                    foreach ($game_data as $game) {
                        // Get player's PFL team for this specific year/week
                        $player_team_for_game = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT team FROM wp_rosters WHERE pid = %s AND year = %d",
                                $pid,
                                $game['year']
                            )
                        );
                        
                        // DIAGNOSTIC FLAGS
                        // Check if NFL data is missing/incomplete
                        $missing_nfl_data = (
                            ($game['pass_yds'] === null || $game['pass_yds'] === '') &&
                            ($game['rush_yds'] === null || $game['rush_yds'] === '') &&
                            ($game['rec_yds'] === null || $game['rec_yds'] === '') &&
                            ($game['xpm'] === null || $game['xpm'] === '')
                        );
                        
                        // Check if all NFL stats are zero (likely didn't play)
                        $all_stats_zero = (
                            ($game['pass_yds'] == 0 || $game['pass_yds'] === null) &&
                            ($game['pass_td'] == 0 || $game['pass_td'] === null) &&
                            ($game['rush_yds'] == 0 || $game['rush_yds'] === null) &&
                            ($game['rush_td'] == 0 || $game['rush_td'] === null) &&
                            ($game['rec_yds'] == 0 || $game['rec_yds'] === null) &&
                            ($game['rec_td'] == 0 || $game['rec_td'] === null) &&
                            ($game['xpm'] == 0 || $game['xpm'] === null) &&
                            ($game['fgm'] == 0 || $game['fgm'] === null)
                        );
                        
                        // Check if player is in PFL lineup for this game (if all stats zero and has points)
                        $in_pfl_lineup = false;
                        $lineup_player_info = null;
                        if ($all_stats_zero && $game['points'] > 0) {
                            // Get player's team and position
                            $player_team = $wpdb->get_var(
                                $wpdb->prepare(
                                    "SELECT team FROM wp_rosters WHERE pid = %s AND year = %d",
                                    $pid,
                                    $game['year']
                                )
                            );
                            
                            if ($player_team) {
                                // Check if this player is in the team's lineup for this week
                                $team_table = 'wp_team_' . $player_team;
                                $position = $player_info['position'];
                                
                                // Check if team table exists
                                $table_exists = $wpdb->get_var(
                                    $wpdb->prepare(
                                        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                                        DB_NAME,
                                        $team_table
                                    )
                                );
                                
                                if ($table_exists) {
                                    // Get the lineup for this week (QB1, QB2, RB1, RB2, WR1, WR2, PK1, PK2)
                                    $lineup_query = "SELECT QB1, QB2, RB1, RB2, WR1, WR2, PK1, PK2 FROM `{$team_table}` WHERE season = %d AND week = %d";
                                    $lineup = $wpdb->get_row(
                                        $wpdb->prepare($lineup_query, $game['year'], $game['week']),
                                        ARRAY_A
                                    );
                                    
                                    if ($lineup) {
                                        // Check if current player is in the lineup
                                        $position_slots = array();
                                        if ($position == 'QB') $position_slots = array('QB1', 'QB2');
                                        elseif ($position == 'RB') $position_slots = array('RB1', 'RB2');
                                        elseif ($position == 'WR') $position_slots = array('WR1', 'WR2');
                                        elseif ($position == 'PK') $position_slots = array('PK1', 'PK2');
                                        
                                        foreach ($position_slots as $slot) {
                                            if ($lineup[$slot] == $pid) {
                                                $in_pfl_lineup = true;
                                                break;
                                            }
                                        }
                                        
                                        // If not in lineup, collect who IS in those position slots
                                        if (!$in_pfl_lineup) {
                                            $lineup_player_info = array();
                                            foreach ($position_slots as $slot) {
                                                if (!empty($lineup[$slot])) {
                                                    $lineup_player = $wpdb->get_row(
                                                        $wpdb->prepare(
                                                            "SELECT playerFirst, playerLast FROM wp_players WHERE p_id = %s",
                                                            $lineup[$slot]
                                                        ),
                                                        ARRAY_A
                                                    );
                                                    if ($lineup_player) {
                                                        $lineup_player_info[] = $slot . ': ' . $lineup_player['playerFirst'] . ' ' . $lineup_player['playerLast'] . ' (' . $lineup[$slot] . ')';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Determine likely root cause
                        $likely_cause = '';
                        if ($missing_nfl_data) {
                            $likely_cause = 'Missing NFL Data';
                        } else if ($all_stats_zero && $game['points'] > 0) {
                            // If player is not in team lineup, the line score was entered in error
                            if (!$in_pfl_lineup) {
                                $likely_cause = 'Not in PFL Lineup';
                            } else {
                                $likely_cause = 'Wrong Player/Wrong Week';
                            }
                        } else if ($game['nflscore'] === null || $game['nflscore'] === '') {
                            $likely_cause = 'NFL Score Not Calculated';
                        } else {
                            $likely_cause = 'Scoring/Entry Error';
                        }
                        
                        // Build Pro Football Reference URL
                        $pfr_url = '';
                        if (!empty($game['game_date']) && !empty($game['nflteam']) && !empty($game['nflopp']) && !empty($game['game_location'])) {
                            // Format: YYYYMMDD0{hometeam}.htm (the 0 is typically used for single games)
                            $date_formatted = str_replace('-', '', $game['game_date']);
                            // Home team is the one with 'vs' (home), away is '@'
                            $home_team = ($game['game_location'] == 'vs' || $game['game_location'] == 'V') ? strtolower($game['nflteam']) : strtolower($game['nflopp']);
                            $pfr_url = 'https://www.pro-football-reference.com/boxscores/' . $date_formatted . '0' . $home_team . '.htm';
                        }
                        
                        // Check for weekly update PDF
                        $week_id = sprintf('%04d%02d', $game['year'], $game['week']);
                        $pdf_url = isset($weekly_pdfs[$week_id]) ? $weekly_pdfs[$week_id] : '';
                        
                        $error_data = array(
                            'player_name' => $player_name,
                            'player_id' => $pid,
                            'player_link' => $player_link,
                            'player_team' => $player_team_for_game,
                            'year' => $game['year'],
                            'week' => $game['week'],
                            'nfl_score' => $game['nflscore'],
                            'pfl_score' => $game['points'],
                            'difference' => $game['scorediff'],
                            'nflscore_is_null' => ($game['nflscore'] === null || $game['nflscore'] === ''),
                            'missing_nfl_data' => $missing_nfl_data,
                            'all_stats_zero' => $all_stats_zero,
                            'likely_cause' => $likely_cause,
                            'lineup_player_info' => $lineup_player_info,
                            'pfr_url' => $pfr_url,
                            'pdf_url' => $pdf_url
                        );
                        
                        // Two point conversions started in 1994 and are worth 1 point
                        // If difference is exactly +1 (not -1) and year >= 1994, likely a 2-point conversion
                        // Negative differences are likely scoring errors, not 2-point conversions
                        if ($game['scorediff'] == 1 && $game['year'] >= 1994) {
                            $errors['likely_two_point_conversions'][] = $error_data;
                        } elseif (abs($game['scorediff']) == 1 && $game['year'] >= 1991 && $game['year'] <= 1993) {
                            // ±1 point differences in 1991-1993 are likely scoring/entry errors
                            $errors['scoring_entry_errors_1991_1993'][] = $error_data;
                        } else {
                            // Separate 1991 vs 1992+ scoring discrepancies
                            if ($game['year'] == 1991) {
                                $errors['other_scoring_discrepancies_1991'][] = $error_data;
                            } else {
                                $errors['other_scoring_discrepancies_1992plus'][] = $error_data;
                            }
                        }
                    }
                }

                // Calculate total errors
                $total_errors = count($errors['players_missing_team']) +
                                count($errors['players_with_invalid_team']) +
                                count($errors['players_missing_position']) +
                                count($errors['players_missing_number']) +
                                count($errors['players_missing_college']) +
                                count($errors['duplicate_numbers_per_team']) +
                                count($errors['missing_player_stats']) +
                                count($errors['likely_two_point_conversions']) +
                                count($errors['scoring_entry_errors_1991_1993']) +
                                count($errors['other_scoring_discrepancies_1991']) +
                                count($errors['other_scoring_discrepancies_1992plus']);
                ?>

                <!-- Summary Statistics -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($players); ?></div>
                        <div class="stat-label">Total Players</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($teams); ?></div>
                        <div class="stat-label">Total Teams</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" style="color: <?php echo $total_errors > 0 ? '#dc3232' : '#46b450'; ?>">
                            <?php echo $total_errors; ?>
                        </div>
                        <div class="stat-label">Total Errors</div>
                    </div>
                </div>

                <!-- Research Resources Table -->
                <div class="error-check-section no-errors" style="border-left-color: #2271b1;" data-section-id="research-resources">
                    <h2 onclick="toggleSection(this)">
                        <span>Research Resources by Week</span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <div class="resources-table-container">
                        <table class="resources-table">
                            <thead>
                                <tr>
                                    <th>Year & Week</th>
                                    <th>Results Page</th>
                                    <th>Pro Football Reference</th>
                                    <th>NFL Dataset</th>
                                    <th>MFL Results</th>
                                    <th>PFL Update</th>
                                    <th>PFL Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Generate rows for 1991 through current year, weeks 1-17
                                $current_year = date('Y');
                                for ($year = 1991; $year <= $current_year; $year++) {
                                    for ($week = 1; $week <= 17; $week++) {
                                        $results_url = home_url('/results/?Y=' . $year . '&W=' . sprintf('%02d', $week));
                                        
                                        // Determine MFL League ID based on year (only 2012+)
                                        $mfl_league_id = '';
                                        if ($year == 2012) {
                                            $mfl_league_id = '47001';
                                        } elseif ($year == 2013) {
                                            $mfl_league_id = '23875';
                                        } elseif ($year == 2014) {
                                            $mfl_league_id = '11521';
                                        } elseif ($year == 2015) {
                                            $mfl_league_id = '47099';
                                        } elseif ($year >= 2016) {
                                            $mfl_league_id = '38954';
                                        }
                                        
                                        $mfl_url = '';
                                        $mfl_link = '<span style="color: #999;">Not Available</span>';
                                        if (!empty($mfl_league_id)) {
                                            $mfl_url = 'https://www47.myfantasyleague.com/' . $year . '/weekly?L=' . $mfl_league_id . '&W=' . $week;
                                            $mfl_link = '<a href="' . $mfl_url . '" target="_blank" style="color: #0073aa; text-decoration: underline;">MFL Weekly</a>';
                                        }
                                        
                                        // Build Pro Football Reference weekly schedule link
                                        // Format: https://www.pro-football-reference.com/years/YYYY/week_W.htm
                                        $pfr_url = 'https://www.pro-football-reference.com/years/' . $year . '/week_' . $week . '.htm';
                                        $pfr_link = '<a href="' . $pfr_url . '" target="_blank" style="color: #0073aa; text-decoration: underline;">PFR Week ' . $week . '</a>';
                                        
                                        // Check for PFL Update PDF from ACF options
                                        $week_id = sprintf('%04d%02d', $year, $week);
                                        $pfl_update_link = '<span style="color: #999;">Not Available</span>';
                                        if (isset($weekly_pdfs[$week_id])) {
                                            $pfl_update_link = '<a href="' . $weekly_pdfs[$week_id] . '" target="_blank" style="color: #0073aa; text-decoration: underline;">Week ' . $week . ', ' . $year . ' Update</a>';
                                        }
                                        
                                        // NFL Dataset: ESPN API for 2001+, CSV fallback for 1991-2000
                                        $espn_dataset = '<span style="color: #999;">Not Available</span>';
                                        
                                        if ($year >= 2001) {
                                            // 2001 onwards: ESPN API is the source of truth
                                            $espn_dataset = '<span style="color: #46b450; font-weight: bold;">ESPN API Dataset</span>';
                                        } else {
                                            // 1991-2000: Check for CSV files as fallback
                                            $theme_path = get_stylesheet_directory();
                                            $csv_base_path = $theme_path . '/pfr-raw-season/';
                                            $positions = ['QB', 'RB', 'WR', 'PK'];
                                            $available_positions = [];
                                            
                                            foreach ($positions as $pos) {
                                                $file_path = $csv_base_path . $year . '-' . $pos . '.csv';
                                                if (file_exists($file_path)) {
                                                    $available_positions[] = $pos;
                                                }
                                            }
                                            
                                            if (!empty($available_positions)) {
                                                $positions_list = implode(', ', $available_positions);
                                                $espn_dataset = '<span style="color: #46b450; font-weight: bold;">PFR Dataset - ' . $positions_list . '</span>';
                                            }
                                        }
                                        
                                        echo '<tr>';
                                        echo '<td><strong>' . $year . ' Week ' . sprintf('%02d', $week) . '</strong></td>';
                                        echo '<td><a href="' . $results_url . '" target="_blank" style="color: #0073aa; text-decoration: underline;">View Results</a></td>'; // Results Page
                                        echo '<td>' . $pfr_link . '</td>'; // Pro Football Reference
                                        echo '<td>' . $espn_dataset . '</td>'; // NFL Dataset
                                        echo '<td>' . $mfl_link . '</td>'; // MFL Results
                                        echo '<td>' . $pfl_update_link . '</td>'; // PFL Update
                                        echo '<td></td>'; // PFL Notes
                                        echo '</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>

                <!-- Players Missing Team Assignment -->
                <div class="error-check-section <?php echo empty($errors['players_missing_team']) ? 'no-errors' : ''; ?>" data-section-id="missing-team">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Players Missing Team Assignment
                            <?php if (!empty($errors['players_missing_team'])): ?>
                                <span class="error-count"><?php echo count($errors['players_missing_team']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <?php if (empty($errors['players_missing_team'])): ?>
                            <p class="success-message">✓ All players have team assignments</p>
                        <?php else: ?>
                            <ul class="error-list">
                                <?php foreach ($errors['players_missing_team'] as $error): ?>
                                    <li>
                                        <a href="<?php echo $error['link']; ?>" target="_blank">
                                            <?php echo esc_html($error['name']); ?> <span style="color: #666; font-size: 0.9em;"><?php echo $error['id']; ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Players with Invalid Team References -->
                <?php if (!empty($errors['players_with_invalid_team'])): ?>
                    <div class="error-check-section">
                        <h2>
                            Players with Invalid Team References
                            <span class="error-count"><?php echo count($errors['players_with_invalid_team']); ?></span>
                        </h2>
                        <ul class="error-list">
                            <?php foreach ($errors['players_with_invalid_team'] as $error): ?>
                                <li>
                                    <a href="<?php echo $error['link']; ?>">
                                        <?php echo esc_html($error['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Players Missing Position -->
                <div class="error-check-section <?php echo empty($errors['players_missing_position']) ? 'no-errors' : ''; ?>" data-section-id="missing-position">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Players Missing Position
                            <?php if (!empty($errors['players_missing_position'])): ?>
                                <span class="error-count"><?php echo count($errors['players_missing_position']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <?php if (empty($errors['players_missing_position'])): ?>
                            <p class="success-message">✓ All players have positions assigned</p>
                        <?php else: ?>
                            <ul class="error-list">
                                <?php foreach ($errors['players_missing_position'] as $error): ?>
                                    <li>
                                        <a href="<?php echo $error['link']; ?>">
                                            <?php echo esc_html($error['name']); ?> <span style="color: #666; font-size: 0.9em;"><?php echo $error['id']; ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Players Missing Jersey Number -->
                <div class="error-check-section <?php echo empty($errors['players_missing_number']) ? 'no-errors' : ''; ?>" data-section-id="missing-number">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Players Missing Jersey Number
                            <?php if (!empty($errors['players_missing_number'])): ?>
                                <span class="error-count"><?php echo count($errors['players_missing_number']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <?php if (empty($errors['players_missing_number'])): ?>
                            <p class="success-message">✓ All players have jersey numbers</p>
                        <?php else: ?>
                            <ul class="error-list">
                                <?php foreach ($errors['players_missing_number'] as $error): ?>
                                    <li>
                                        <a href="<?php echo $error['link']; ?>" target="_blank">
                                            <?php echo esc_html($error['name']); ?> <span style="color: #666; font-size: 0.9em;"><?php echo $error['id']; ?></span>
                                        </a>
                                        <?php if (!empty($error['note'])): ?>
                                            <br><span style="color: #d63638; font-size: 0.9em; font-style: italic;">→ <?php echo esc_html($error['note']); ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Players Missing College -->
                <div class="error-check-section <?php echo empty($errors['players_missing_college']) ? 'no-errors' : ''; ?>" data-section-id="missing-college">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Players Missing College
                            <?php if (!empty($errors['players_missing_college'])): ?>
                                <span class="error-count"><?php echo count($errors['players_missing_college']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <?php if (empty($errors['players_missing_college'])): ?>
                            <p class="success-message">✓ All players have college listed</p>
                        <?php else: ?>
                            <ul class="error-list">
                                <?php foreach ($errors['players_missing_college'] as $error): ?>
                                    <li>
                                        <a href="<?php echo $error['link']; ?>" target="_blank">
                                            <?php echo esc_html($error['name']); ?> <span style="color: #666; font-size: 0.9em;"><?php echo $error['id']; ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Players Missing Profile Picture -->
                <div class="error-check-section <?php echo empty($errors['players_missing_picture']) ? 'no-errors' : ''; ?>" data-section-id="missing-picture">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Players Missing Profile Picture
                            <?php if (!empty($errors['players_missing_picture'])): ?>
                                <span class="error-count"><?php echo count($errors['players_missing_picture']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <?php if (empty($errors['players_missing_picture'])): ?>
                            <p class="success-message">✓ All players have profile pictures</p>
                        <?php else: ?>
                            <ul class="error-list">
                                <?php foreach ($errors['players_missing_picture'] as $error): ?>
                                    <li>
                                        <a href="<?php echo $error['link']; ?>" target="_blank">
                                            <?php echo esc_html($error['name']); ?> <span style="color: #666; font-size: 0.9em;"><?php echo $error['id']; ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Duplicate Jersey Numbers Per Team -->
                <div class="error-check-section <?php echo empty($errors['duplicate_numbers_per_team']) ? 'no-errors' : ''; ?>" data-section-id="duplicate-numbers">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Duplicate Jersey Numbers Per Team
                            <?php if (!empty($errors['duplicate_numbers_per_team'])): ?>
                                <span class="error-count"><?php echo count($errors['duplicate_numbers_per_team']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <?php if (empty($errors['duplicate_numbers_per_team'])): ?>
                            <p class="success-message">✓ No duplicate jersey numbers found</p>
                        <?php else: ?>
                            <ul class="error-list">
                                <?php foreach ($errors['duplicate_numbers_per_team'] as $duplicate): ?>
                                    <li>
                                        <strong><?php echo esc_html($duplicate['team']); ?> - #<?php echo $duplicate['number']; ?></strong>
                                        <ul style="margin-top: 5px;">
                                            <?php foreach ($duplicate['players'] as $player): ?>
                                                <li>
                                                    <a href="<?php echo $player['link']; ?>" target="_blank">
                                                        <?php echo esc_html($player['name']); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Players Missing Basic Stats -->
                <div class="error-check-section <?php echo empty($errors['missing_player_stats']) ? 'no-errors' : ''; ?>" data-section-id="missing-stats">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Players Missing Basic Stats (Height/Weight/Year)
                            <?php if (!empty($errors['missing_player_stats'])): ?>
                                <span class="error-count"><?php echo count($errors['missing_player_stats']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <?php if (empty($errors['missing_player_stats'])): ?>
                            <p class="success-message">✓ All players have basic stats</p>
                        <?php else: ?>
                            <ul class="error-list">
                                <?php foreach ($errors['missing_player_stats'] as $error): ?>
                                    <li>
                                        <a href="<?php echo $error['link']; ?>" target="_blank">
                                            <?php echo esc_html($error['name']); ?> <span style="color: #666; font-size: 0.9em;"><?php echo $error['id']; ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Likely Two Point Conversions -->
                <div class="error-check-section <?php echo empty($errors['likely_two_point_conversions']) ? 'no-errors' : ''; ?>" style="border-left-color: <?php echo empty($errors['likely_two_point_conversions']) ? '#46b450' : '#2271b1'; ?>;" data-section-id="two-point-conversions">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Likely Two Point Conversions (Difference of 1, 1994+)
                            <?php if (!empty($errors['likely_two_point_conversions'])): ?>
                                <span class="error-count two-point"><?php echo count($errors['likely_two_point_conversions']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <p style="color: #666; font-size: 0.9em; margin-top: 0;">Two point conversions are worth 1 point and started in 1994.</p>
                    <?php if (empty($errors['likely_two_point_conversions'])): ?>
                        <p class="success-message">✓ No likely two point conversions found</p>
                    <?php else: ?>
                        <?php
                        // Group by player
                        $grouped_2pt = array();
                        foreach ($errors['likely_two_point_conversions'] as $error) {
                            $pid = $error['player_id'];
                            if (!isset($grouped_2pt[$pid])) {
                                $grouped_2pt[$pid] = array(
                                    'player_name' => $error['player_name'],
                                    'player_id' => $error['player_id'],
                                    'player_link' => $error['player_link'],
                                    'games' => array()
                                );
                            }
                            $grouped_2pt[$pid]['games'][] = $error;
                        }
                        ?>
                        <ul class="error-list two-point-conversions">
                            <?php foreach ($grouped_2pt as $player_data): ?>
                                <li>
                                    <a href="<?php echo $player_data['player_link']; ?>" target="_blank">
                                        <?php echo esc_html($player_data['player_name']); ?> <span style="color: #666; font-size: 0.9em;"><?php echo $player_data['player_id']; ?></span>
                                    </a>
                                    <?php foreach ($player_data['games'] as $game): ?>
                                        <div class="error-game-row">
                                            <div class="error-game-info" style="color: #2271b1; font-size: 0.9em;">
                                                → <a href="<?php echo home_url('/results/?Y=' . $game['year'] . '&W=' . sprintf('%02d', $game['week'])); ?>" target="_blank" style="color: #2271b1; text-decoration: underline;"><?php echo $game['year']; ?>, Week: <?php echo $game['week']; ?></a>
                                                <?php if (!empty($game['player_team'])): ?>
                                                    | <strong>Team: <?php echo esc_html($game['player_team']); ?></strong>
                                                <?php endif; ?>
                                                | NFL Expected: <?php echo $game['nfl_score']; ?><?php if ($game['nflscore_is_null']): ?> <strong style="color: #2271b1;">(NULL)</strong><?php endif; ?> 
                                                | PFL Actual: <?php echo $game['pfl_score']; ?> 
                                                | Difference: <?php echo $game['difference']; ?>
                                            </div>
                                            <div class="error-game-actions">
                                                <?php if (!empty($game['pfr_url'])): ?>
                                                    <a href="<?php echo $game['pfr_url']; ?>" target="_blank" style="color: #2271b1; text-decoration: underline;" title="View on Pro Football Reference">PFR ↗</a>
                                                <?php endif; ?>
                                                <?php if (!empty($game['pdf_url'])): ?>
                                                    <a href="<?php echo $game['pdf_url']; ?>" target="_blank" style="color: #2271b1; text-decoration: underline;" title="View Weekly Update PDF">📝 PDF</a>
                                                <?php endif; ?>
                                                <button class="copy-script-btn" onclick="copyScript('<?php echo $game['player_id']; ?>', <?php echo $game['year']; ?>, <?php echo $game['week']; ?>, this)" title="Copy python script to clipboard">📋 Copy Script</button>
                                                <button class="copy-transfer-btn" onclick="copyTransferScript('<?php echo $game['player_id']; ?>', <?php echo $game['year']; ?>, <?php echo $game['week']; ?>, this)" title="Copy transfer script to clipboard">🔄 Transfer</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    </div>
                </div>

                <!-- Scoring/Entry Errors (1991-1993) -->
                <div class="error-check-section <?php echo empty($errors['scoring_entry_errors_1991_1993']) ? 'no-errors' : ''; ?>" data-section-id="scoring-entry-1991-1993">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Scoring / Entry Errors (±1 point, 1991-1993)
                            <?php if (!empty($errors['scoring_entry_errors_1991_1993'])): ?>
                                <span class="error-count"><?php echo count($errors['scoring_entry_errors_1991_1993']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <p style="color: #666; font-size: 0.9em; margin-top: 0;">Difference of exactly +1 or -1 point in years 1991, 1992, or 1993.</p>
                    <?php if (empty($errors['scoring_entry_errors_1991_1993'])): ?>
                        <p class="success-message">✓ No scoring/entry errors found for 1991-1993</p>
                    <?php else: ?>
                        <?php
                        // Group by player
                        $grouped_scoring = array();
                        foreach ($errors['scoring_entry_errors_1991_1993'] as $error) {
                            $pid = $error['player_id'];
                            if (!isset($grouped_scoring[$pid])) {
                                $grouped_scoring[$pid] = array(
                                    'player_name' => $error['player_name'],
                                    'player_id' => $error['player_id'],
                                    'player_link' => $error['player_link'],
                                    'games' => array()
                                );
                            }
                            $grouped_scoring[$pid]['games'][] = $error;
                        }
                        ?>
                        <ul class="error-list">
                            <?php foreach ($grouped_scoring as $player_data): ?>
                                <li>
                                    <a href="<?php echo $player_data['player_link']; ?>" target="_blank">
                                        <?php echo esc_html($player_data['player_name']); ?> <span style="color: #666; font-size: 0.9em;"><?php echo $player_data['player_id']; ?></span>
                                    </a>
                                    <?php foreach ($player_data['games'] as $game): ?>
                                        <div class="error-game-row">
                                            <div class="error-game-info" style="color: #d63638; font-size: 0.9em;">
                                                → <a href="<?php echo home_url('/results/?Y=' . $game['year'] . '&W=' . sprintf('%02d', $game['week'])); ?>" target="_blank" style="color: #d63638; text-decoration: underline;"><?php echo $game['year']; ?>, Week: <?php echo $game['week']; ?></a>
                                                <?php if (!empty($game['player_team'])): ?>
                                                    | <strong>Team: <?php echo esc_html($game['player_team']); ?></strong>
                                                <?php endif; ?>
                                                | NFL Expected: <?php echo $game['nfl_score']; ?><?php if ($game['nflscore_is_null']): ?> <strong style="color: #dc3232;">(NULL)</strong><?php endif; ?> 
                                                | PFL Actual: <?php echo $game['pfl_score']; ?> 
                                                | Difference: <?php echo $game['difference']; ?>
                                            </div>
                                            <div class="error-game-actions">
                                                <?php if (!empty($game['pfr_url'])): ?>
                                                    <a href="<?php echo $game['pfr_url']; ?>" target="_blank" style="color: #d63638; text-decoration: underline;" title="View on Pro Football Reference">PFR ↗</a>
                                                <?php endif; ?>
                                                <?php if (!empty($game['pdf_url'])): ?>
                                                    <a href="<?php echo $game['pdf_url']; ?>" target="_blank" style="color: #d63638; text-decoration: underline;" title="View Weekly Update PDF">📝 PDF</a>
                                                <?php endif; ?>
                                                <button class="copy-script-btn" onclick="copyScript('<?php echo $game['player_id']; ?>', <?php echo $game['year']; ?>, <?php echo $game['week']; ?>, this)" title="Copy python script to clipboard">📋 Copy Script</button>
                                                <button class="copy-transfer-btn" onclick="copyTransferScript('<?php echo $game['player_id']; ?>', <?php echo $game['year']; ?>, <?php echo $game['week']; ?>, this)" title="Copy transfer script to clipboard">🔄 Transfer</button>
                                            </div>
                                            <?php if (!empty($game['likely_cause'])): ?>
                                                <?php 
                                                // Set color to purple for "Wrong Player/Wrong Week", otherwise use blue
                                                $cause_color = ($game['likely_cause'] == 'Wrong Player/Wrong Week') ? '#9b59b6' : '#2271b1';
                                                ?>
                                                <span class="error-game-cause" style="color: <?php echo $cause_color; ?>; font-weight: bold; font-size: 0.9em;">
                                                    🔍 Likely Cause: <?php echo $game['likely_cause']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    </div>
                </div>

                <!-- Other Scoring Discrepancies - 1991 -->
                <div class="error-check-section <?php echo empty($errors['other_scoring_discrepancies_1991']) ? 'no-errors' : ''; ?>" data-section-id="scoring-1991">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Other Point Difference Issues - 1991
                            <?php if (!empty($errors['other_scoring_discrepancies_1991'])): ?>
                                <span class="error-count"><?php echo count($errors['other_scoring_discrepancies_1991']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <p style="color: #666; font-size: 0.9em; margin-top: 0;">1991 scoring: Pass yards/50, Rush yards/20, Rec yards/20, TDs worth 2 points.</p>
                    <?php if (empty($errors['other_scoring_discrepancies_1991'])): ?>
                        <p class="success-message">✓ No scoring discrepancies found for 1991</p>
                    <?php else: ?>
                        <?php
                        // Group by player
                        $grouped_1991 = array();
                        foreach ($errors['other_scoring_discrepancies_1991'] as $error) {
                            $pid = $error['player_id'];
                            if (!isset($grouped_1991[$pid])) {
                                $grouped_1991[$pid] = array(
                                    'player_name' => $error['player_name'],
                                    'player_id' => $error['player_id'],
                                    'player_link' => $error['player_link'],
                                    'games' => array()
                                );
                            }
                            $grouped_1991[$pid]['games'][] = $error;
                        }
                        ?>
                        <ul class="error-list">
                            <?php foreach ($grouped_1991 as $player_data): ?>
                                <li>
                                    <a href="<?php echo $player_data['player_link']; ?>" target="_blank">
                                        <?php echo esc_html($player_data['player_name']); ?> <span style="color: #666; font-size: 0.9em;"><?php echo $player_data['player_id']; ?></span>
                                    </a>
                                    <?php foreach ($player_data['games'] as $game): ?>
                                        <div class="error-game-row">
                                            <div class="error-game-info" style="color: #d63638; font-size: 0.9em;">
                                                → <a href="<?php echo home_url('/results/?Y=' . $game['year'] . '&W=' . sprintf('%02d', $game['week'])); ?>" target="_blank" style="color: #d63638; text-decoration: underline;"><?php echo $game['year']; ?>, Week: <?php echo $game['week']; ?></a>
                                                <?php if (!empty($game['player_team'])): ?>
                                                    | <strong>Team: <?php echo esc_html($game['player_team']); ?></strong>
                                                <?php endif; ?>
                                                | NFL Expected: <?php echo $game['nfl_score']; ?><?php if ($game['nflscore_is_null']): ?> <strong style="color: #dc3232;">(NULL)</strong><?php endif; ?> 
                                                | PFL Actual: <?php echo $game['pfl_score']; ?> 
                                                | Difference: <?php echo $game['difference']; ?>
                                            </div>
                                            <div class="error-game-actions">
                                                <?php if (!empty($game['pfr_url'])): ?>
                                                    <a href="<?php echo $game['pfr_url']; ?>" target="_blank" style="color: #d63638; text-decoration: underline;" title="View on Pro Football Reference">PFR ↗</a>
                                                <?php endif; ?>
                                                <?php if (!empty($game['pdf_url'])): ?>
                                                    <a href="<?php echo $game['pdf_url']; ?>" target="_blank" style="color: #d63638; text-decoration: underline;" title="View Weekly Update PDF">📝 PDF</a>
                                                <?php endif; ?>
                                                <button class="copy-script-btn" onclick="copyScript('<?php echo $game['player_id']; ?>', <?php echo $game['year']; ?>, <?php echo $game['week']; ?>, this)" title="Copy python script to clipboard">📋 Copy Script</button>
                                                <button class="copy-transfer-btn" onclick="copyTransferScript('<?php echo $game['player_id']; ?>', <?php echo $game['year']; ?>, <?php echo $game['week']; ?>, this)" title="Copy transfer script to clipboard">🔄 Transfer</button>
                                            </div>
                                            <?php if (!empty($game['likely_cause'])): ?>
                                                <?php 
                                                // Set color to purple for "Wrong Player/Wrong Week", otherwise use blue
                                                $cause_color = ($game['likely_cause'] == 'Wrong Player/Wrong Week') ? '#9b59b6' : '#2271b1';
                                                ?>
                                                <span class="error-game-cause" style="color: <?php echo $cause_color; ?>; font-weight: bold; font-size: 0.9em;">
                                                    🔍 Likely Cause: <?php echo $game['likely_cause']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    </div>
                </div>

                <!-- Other Scoring Discrepancies - 1992+ -->
                <div class="error-check-section <?php echo empty($errors['other_scoring_discrepancies_1992plus']) ? 'no-errors' : ''; ?>" data-section-id="scoring-1992plus">
                    <h2 onclick="toggleSection(this)">
                        <span>
                            Other Point Difference Issues - 1992 and Later
                            <?php if (!empty($errors['other_scoring_discrepancies_1992plus'])): ?>
                                <span class="error-count"><?php echo count($errors['other_scoring_discrepancies_1992plus']); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="collapse-toggle collapsed">▼</span>
                    </h2>
                    <div class="section-content collapsed">
                        <p style="color: #666; font-size: 0.9em; margin-top: 0;">1992+ scoring: Pass yards/30, Rush yards/10, Rec yards/10, TDs worth 2 points.</p>
                    <?php if (empty($errors['other_scoring_discrepancies_1992plus'])): ?>
                        <p class="success-message">✓ No scoring discrepancies found for 1992 and later</p>
                    <?php else: ?>
                        <?php
                        // Count by likely cause
                        $cause_summary = array();
                        foreach ($errors['other_scoring_discrepancies_1992plus'] as $error) {
                            $cause = $error['likely_cause'];
                            if (!isset($cause_summary[$cause])) {
                                $cause_summary[$cause] = 0;
                            }
                            $cause_summary[$cause]++;
                        }
                        arsort($cause_summary);
                        ?>
                        
                        <?php if (!empty($cause_summary)): ?>
                            <div style="background: #f0f6fc; padding: 15px; margin: 15px 0; border-left: 4px solid #2271b1;">
                                <strong>Breakdown by Likely Cause:</strong>
                                <ul style="margin: 10px 0; padding-left: 20px;">
                                    <?php foreach ($cause_summary as $cause => $count): ?>
                                        <li><strong><?php echo $cause; ?>:</strong> <?php echo $count; ?> occurrences</li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php
                        // Group by player
                        $grouped_1992plus = array();
                        foreach ($errors['other_scoring_discrepancies_1992plus'] as $error) {
                            $pid = $error['player_id'];
                            if (!isset($grouped_1992plus[$pid])) {
                                $grouped_1992plus[$pid] = array(
                                    'player_name' => $error['player_name'],
                                    'player_id' => $error['player_id'],
                                    'player_link' => $error['player_link'],
                                    'games' => array()
                                );
                            }
                            $grouped_1992plus[$pid]['games'][] = $error;
                        }
                        ?>
                        <ul class="error-list">
                            <?php foreach ($grouped_1992plus as $player_data): ?>
                                <li>
                                    <a href="<?php echo $player_data['player_link']; ?>" target="_blank">
                                        <?php echo esc_html($player_data['player_name']); ?> <span style="color: #666; font-size: 0.9em;"><?php echo $player_data['player_id']; ?></span>
                                    </a>
                                    <?php foreach ($player_data['games'] as $game): ?>
                                        <div class="error-game-row">
                                            <div class="error-game-info" style="color: #d63638; font-size: 0.9em;">
                                                → <a href="<?php echo home_url('/results/?Y=' . $game['year'] . '&W=' . sprintf('%02d', $game['week'])); ?>" target="_blank" style="color: #d63638; text-decoration: underline;"><?php echo $game['year']; ?>, Week: <?php echo $game['week']; ?></a>
                                                <?php if (!empty($game['player_team'])): ?>
                                                    | <strong>Team: <?php echo esc_html($game['player_team']); ?></strong>
                                                <?php endif; ?>
                                                | NFL Expected: <?php echo $game['nfl_score']; ?><?php if ($game['nflscore_is_null']): ?> <strong style="color: #dc3232;">(NULL)</strong><?php endif; ?> 
                                                | PFL Actual: <?php echo $game['pfl_score']; ?> 
                                                | Difference: <?php echo $game['difference']; ?>
                                            </div>
                                            <div class="error-game-actions">
                                                <?php if (!empty($game['pfr_url'])): ?>
                                                    <a href="<?php echo $game['pfr_url']; ?>" target="_blank" style="color: #d63638; text-decoration: underline;" title="View on Pro Football Reference">PFR ↗</a>
                                                <?php endif; ?>
                                                <?php if (!empty($game['pdf_url'])): ?>
                                                    <a href="<?php echo $game['pdf_url']; ?>" target="_blank" style="color: #d63638; text-decoration: underline;" title="View Weekly Update PDF">📝 PDF</a>
                                                <?php endif; ?>
                                                <button class="copy-script-btn" onclick="copyScript('<?php echo $game['player_id']; ?>', <?php echo $game['year']; ?>, <?php echo $game['week']; ?>, this)" title="Copy python script to clipboard">📋 Copy Script</button>
                                                <button class="copy-transfer-btn" onclick="copyTransferScript('<?php echo $game['player_id']; ?>', <?php echo $game['year']; ?>, <?php echo $game['week']; ?>, this)" title="Copy transfer script to clipboard">🔄 Transfer</button>
                                            </div>
                                            <?php if (!empty($game['likely_cause'])): ?>
                                                <?php 
                                                // Set color to purple for "Wrong Player/Wrong Week", otherwise use blue
                                                $cause_color = ($game['likely_cause'] == 'Wrong Player/Wrong Week') ? '#9b59b6' : '#2271b1';
                                                ?>
                                                <span class="error-game-cause" style="color: <?php echo $cause_color; ?>; font-weight: bold; font-size: 0.9em;">
                                                    🔍 Likely Cause: <?php echo $game['likely_cause']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    </div>
                </div>

            </div><!-- .entry-content -->
            
        </div>
        <!--End page content-->

    </div><!--END CONTENT CONTAINER-->

    <?php include_once('main-nav.php'); ?>
    <?php include_once('aside.php'); ?>

</div>

<script>
function toggleSection(header) {
    const content = header.nextElementSibling;
    const toggle = header.querySelector('.collapse-toggle');
    const section = header.closest('.error-check-section');
    const sectionId = section ? section.getAttribute('data-section-id') : null;
    
    if (content.classList.contains('collapsed')) {
        // Expand
        content.style.maxHeight = content.scrollHeight + 'px';
        content.classList.remove('collapsed');
        toggle.classList.remove('collapsed');
        
        // Save state to localStorage
        if (sectionId) {
            localStorage.setItem('error-check-section-' + sectionId, 'open');
        }
    } else {
        // Collapse
        content.style.maxHeight = '0';
        content.classList.add('collapsed');
        toggle.classList.add('collapsed');
        
        // Save state to localStorage
        if (sectionId) {
            localStorage.setItem('error-check-section-' + sectionId, 'closed');
        }
    }
}

function copyScript(playerId, year, week, button) {
    const script = `python3 player_boxscore.py ${playerId} ${year} ${week}`;
    
    // Create a temporary textarea element
    const textarea = document.createElement('textarea');
    textarea.value = script;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    
    // Select and copy the text
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            // Change button appearance to show success
            const originalText = button.innerHTML;
            button.innerHTML = '✓ Copied!';
            button.classList.add('copied');
            
            // Reset button after 2 seconds
            setTimeout(function() {
                button.innerHTML = originalText;
                button.classList.remove('copied');
            }, 2000);
        } else {
            alert('Failed to copy to clipboard');
        }
    } catch (err) {
        console.error('Failed to copy: ', err);
        alert('Failed to copy to clipboard');
    } finally {
        // Remove the textarea
        document.body.removeChild(textarea);
    }
}

function copyTransferScript(playerId, year, week, button) {
    const script = `python3 transfer_player_game.py
# When prompted, enter:
# Incorrect player ID: ${playerId}
# Correct player ID: [ENTER CORRECT ID]
# Year: ${year}
# Week: ${week}`;
    
    // Create a temporary textarea element
    const textarea = document.createElement('textarea');
    textarea.value = script;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    
    // Select and copy the text
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            // Change button appearance to show success
            const originalText = button.innerHTML;
            button.innerHTML = '✓ Copied!';
            button.classList.add('copied');
            
            // Reset button after 2 seconds
            setTimeout(function() {
                button.innerHTML = originalText;
                button.classList.remove('copied');
            }, 2000);
        } else {
            alert('Failed to copy to clipboard');
        }
    } catch (err) {
        console.error('Failed to copy: ', err);
        alert('Failed to copy to clipboard');
    } finally {
        // Remove the textarea
        document.body.removeChild(textarea);
    }
}

// Restore panel states from localStorage on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.error-check-section[data-section-id]').forEach(function(section) {
        const sectionId = section.getAttribute('data-section-id');
        const savedState = localStorage.getItem('error-check-section-' + sectionId);
        const content = section.querySelector('.section-content');
        const toggle = section.querySelector('.collapse-toggle');
        
        if (savedState === 'open') {
            // Expand this section
            content.classList.remove('collapsed');
            content.style.maxHeight = content.scrollHeight + 'px';
            if (toggle) toggle.classList.remove('collapsed');
        } else if (savedState === 'closed') {
            // Keep it collapsed (already default state)
            content.classList.add('collapsed');
            content.style.maxHeight = '0';
            if (toggle) toggle.classList.add('collapsed');
        }
        // If no saved state, keep default (collapsed)
    });
    
    // Set initial max-height for any sections that are open but not explicitly handled above
    document.querySelectorAll('.section-content:not(.collapsed)').forEach(function(content) {
        if (!content.style.maxHeight || content.style.maxHeight === '0px') {
            content.style.maxHeight = content.scrollHeight + 'px';
        }
    });
});
</script>

<?php get_footer(); ?>
