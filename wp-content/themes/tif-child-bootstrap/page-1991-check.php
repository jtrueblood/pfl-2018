<?php
/**
 * Template Name: 1991 Check
 * Description: A page template for checking and correcting 1991 season data errors
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
                    .check-section {
                        margin: 30px 0;
                        padding: 20px;
                        background: #f9f9f9;
                        border-left: 4px solid #2271b1;
                    }
                    .check-section h2 {
                        margin-top: 0;
                        color: #333;
                    }
                    .scores-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                        background: white;
                        table-layout: fixed;
                    }
                    .scores-table th,
                    .scores-table td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: center;
                    }
                    .scores-table th {
                        background: #2271b1;
                        color: white;
                        font-weight: bold;
                    }
                    .scores-table th.team-header {
                        background: #1d5a8a;
                    }
                    .scores-table td.team-name {
                        background: #f5f5f5;
                        font-weight: bold;
                    }
                    .scores-table td.week-cell {
                        vertical-align: top;
                        font-size: 12px;
                        line-height: 1.4;
                    }
                    .scores-table td.week-cell .score {
                        font-weight: bold;
                        font-size: 14px;
                        margin-bottom: 4px;
                    }
                    .scores-table td.week-cell .players {
                        font-size: 10px;
                        color: #666;
                    }
                    .scores-table th.total-header,
                    .scores-table td.total-cell {
                        background: #f0f0f0;
                        font-weight: bold;
                        border-left: 2px solid #333;
                        border-right: 2px solid #333;
                    }
                    .scores-table th.total-header {
                        background: #444;
                        color: white;
                    }
                    .scores-table th.total-narrow,
                    .scores-table td.total-narrow {
                        max-width: 50px;
                        width: 50px;
                    }
                </style>

                <?php
                global $wpdb;
                
                $teams = array('ETS', 'PEP', 'WRZ', 'RBS', 'BUL', 'CMN', 'SNR', 'TSG');
                $weeks = range(1, 14);
                
                // Get all player names for lookup
                $playersassoc = get_players_assoc();
                
                // Get game counts for all players in 1991 to identify subs
                $player_game_counts = array();
                $all_1991_players = $wpdb->get_results(
                    "SELECT p_id FROM wp_players",
                    ARRAY_A
                );
                foreach ($all_1991_players as $p) {
                    $pid = $p['p_id'];
                    $count = $wpdb->get_var(
                        "SELECT COUNT(*) FROM `{$pid}` WHERE year LIKE '1991'"
                    );
                    if ($count !== null) {
                        $player_game_counts[$pid] = (int)$count;
                    }
                }
                
                // Expected totals by week 13 for each team
                $expected_by_13 = array(
                    'ETS' => 229,
                    'PEP' => 252,
                    'WRZ' => 259,
                    'RBS' => 221,
                    'BUL' => 331,
                    'CMN' => 225,
                    'SNR' => 259,
                    'TSG' => 260
                );
                
                // Get scores and players for each team for 1991
                $team_scores = array();
                $team_players = array();
                $team_results = array();
                foreach ($teams as $team) {
                    foreach ($weeks as $week) {
                        $result = $wpdb->get_row(
                            "SELECT points, vs_points, QB1, RB1, WR1, PK1 FROM wp_team_{$team} WHERE season LIKE '1991' AND week LIKE '{$week}'",
                            ARRAY_A
                        );
                        if ($result) {
                            $team_scores[$team][$week] = $result['points'];
                            $team_results[$team][$week] = ($result['points'] > $result['vs_points']) ? 'W' : (($result['points'] < $result['vs_points']) ? 'L' : 'T');
                            $team_diff[$team][$week] = (int)$result['points'] - (int)$result['vs_points'];
                            $team_players[$team][$week] = array(
                                'QB' => $result['QB1'],
                                'RB' => $result['RB1'],
                                'WR' => $result['WR1'],
                                'PK' => $result['PK1']
                            );
                        }
                    }
                }
                ?>
                
                <div class="check-section">
                    <h2>1991 Season Team Scores</h2>
                    
                    <table class="scores-table">
                        <thead>
                            <tr>
                                <th class="team-header">Team</th>
                                <?php for ($week = 1; $week <= 13; $week++): ?>
                                    <th>Week <?php echo $week; ?></th>
                                <?php endfor; ?>
                                <th class="total-header total-narrow">Total To 13</th>
                                <th class="total-header">Expected By 13</th>
                                <th class="total-header">Diff</th>
                                <th>Week 14</th>
                                <th class="total-header">EOS Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teams as $team): ?>
                                <tr>
                                    <td class="team-name"><?php echo $team; ?></td>
                                    <?php 
                                    $total = 0;
                                    for ($week = 1; $week <= 13; $week++): 
                                        $points = isset($team_scores[$team][$week]) ? $team_scores[$team][$week] : 0;
                                        $total += (int)$points;
                                        $players = isset($team_players[$team][$week]) ? $team_players[$team][$week] : array();
                                    ?>
                                        <?php 
                                        $game_result = isset($team_results[$team][$week]) ? $team_results[$team][$week] : '';
                                        $point_diff = isset($team_diff[$team][$week]) ? $team_diff[$team][$week] : 0;
                                        $score_color = ($game_result == 'W') ? 'color: green;' : (($game_result == 'L') ? 'color: red;' : '');
                                        $diff_display = ($point_diff > 0) ? '+' . $point_diff : $point_diff;
                                        ?>
                                        <td class="week-cell">
                                            <?php if ($points): ?>
                                                <div class="score" style="<?php echo $score_color; ?>"><?php echo $points; ?></div>
                                                <div style="font-size: 10px; <?php echo $score_color; ?>"><?php echo $diff_display; ?></div>
                                                <div class="players">
                                                    <?php 
                                                    $weekid = '1991' . sprintf('%02d', $week);
                                                    foreach (array('QB', 'RB', 'WR', 'PK') as $pos):
                                                        if ($players[$pos] && isset($playersassoc[$players[$pos]])):
                                                            $pid = $players[$pos];
                                                            $pdata = get_player_week($pid, $weekid);
                                                            $pname = $playersassoc[$pid][0].' '.$playersassoc[$pid][1];
                                                            $pscore = isset($pdata['points']) ? $pdata['points'] : '';
                                                            $sdiff = isset($pdata['scorediff']) ? $pdata['scorediff'] : '';
                                                            $sdiff_display = '';
                                                            if ($sdiff !== '' && $sdiff !== null) {
                                                                $color = ($sdiff == 0) ? '#cccccc' : '#cc0000';
                                                                $sdiff_display = ' <span style="color:'.$color.';">('. $sdiff .')</span>';
                                                            }
                                                            // Highlight subs (4 or fewer games) in blue
                                                            $games = isset($player_game_counts[$pid]) ? $player_game_counts[$pid] : 0;
                                                            $name_style = ($games > 0 && $games <= 4) ? 'color: #0066cc; font-weight: bold;' : '';
                                                            echo '<span style="'.$name_style.'">'.$pname.'</span> ' . $pscore . $sdiff_display . '<br>';
                                                        else:
                                                            echo '<br>';
                                                        endif;
                                                    endforeach;
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                    <td class="total-cell"><?php echo $total; ?></td>
                                    <td class="total-cell"><?php echo $expected_by_13[$team]; ?></td>
                                    <?php $diff = $total - $expected_by_13[$team]; ?>
                                    <td class="total-cell" style="<?php echo $diff != 0 ? 'color: red;' : 'color: green;'; ?>"><?php echo $diff; ?></td>
                                    <?php 
                                    $week14 = isset($team_scores[$team][14]) ? (int)$team_scores[$team][14] : 0;
                                    $players14 = isset($team_players[$team][14]) ? $team_players[$team][14] : array();
                                    ?>
                                    <?php 
                                    $game_result14 = isset($team_results[$team][14]) ? $team_results[$team][14] : '';
                                    $point_diff14 = isset($team_diff[$team][14]) ? $team_diff[$team][14] : 0;
                                    $score_color14 = ($game_result14 == 'W') ? 'color: green;' : (($game_result14 == 'L') ? 'color: red;' : '');
                                    $diff_display14 = ($point_diff14 > 0) ? '+' . $point_diff14 : $point_diff14;
                                    ?>
                                    <td class="week-cell">
                                        <?php if ($week14): ?>
                                            <div class="score" style="<?php echo $score_color14; ?>"><?php echo $week14; ?></div>
                                            <div style="font-size: 10px; <?php echo $score_color14; ?>"><?php echo $diff_display14; ?></div>
                                            <div class="players">
                                                <?php 
                                                $weekid14 = '199114';
                                                foreach (array('QB', 'RB', 'WR', 'PK') as $pos):
                                                    if ($players14[$pos] && isset($playersassoc[$players14[$pos]])):
                                                        $pid = $players14[$pos];
                                                        $pdata = get_player_week($pid, $weekid14);
                                                        $pname = $playersassoc[$pid][0].' '.$playersassoc[$pid][1];
                                                        $pscore = isset($pdata['points']) ? $pdata['points'] : '';
                                                        $sdiff = isset($pdata['scorediff']) ? $pdata['scorediff'] : '';
                                                        $sdiff_display = '';
                                                        if ($sdiff !== '' && $sdiff !== null) {
                                                            $color = ($sdiff == 0) ? '#cccccc' : '#cc0000';
                                                            $sdiff_display = ' <span style="color:'.$color.';">('. $sdiff .')</span>';
                                                        }
                                                        // Highlight subs (4 or fewer games) in blue
                                                        $games = isset($player_game_counts[$pid]) ? $player_game_counts[$pid] : 0;
                                                        $name_style = ($games > 0 && $games <= 4) ? 'color: #0066cc; font-weight: bold;' : '';
                                                        echo '<span style="'.$name_style.'">'.$pname.'</span> ' . $pscore . $sdiff_display . '<br>';
                                                    else:
                                                        echo '<br>';
                                                    endif;
                                                endforeach;
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="total-cell"><?php echo $total + $week14; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
        <!--End page content-->

    </div>
    <!--END CONTENT CONTAINER-->

</div>

<?php get_footer(); ?>
