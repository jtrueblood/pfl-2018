<?php
/*
 * Template Name: Trade Analyzer
 * Description: Stuff Goes Here
 */
 ?>

<?php get_header();
function convert_stats_array($array){
    if($array):
        foreach ($array as $value):
            $pointspulled[] = $value['points'];
        endforeach;
        $points = array_sum($pointspulled);
        $games = count($pointspulled);
        if($games > 0):
            $value = array(
                'points' => $points,
                'games' => $games
            );
        endif;
    else:
        $value = array(
            'points' => 0,
            'games' => 0
        );
    endif;
    return $value;
}

function insert_trade_winner($id, $teamid, $loser, $pointsdif){
    global $wpdb;
    $wpdb->update(
        'wp_trades',
        array(
            'tradewinner' => $teamid,
            'tradeloser' => $loser,
            'tradewinpoints' => $pointsdif
            // string
        ),
        array(
            'id' => $id
        )
    );
}

?>
<?php

 ?>
<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <div id="page-content">
                <?php
                $drafts = get_drafts();
                $getyears = the_seasons();
                $trades = get_trades();
                $teamlist = get_teams();
                foreach($teamlist as $key => $value):
                    $teams[] = $key;
                endforeach;
                foreach($trades as $key => $value):
                    $trades[$value['id']] = $value;
                endforeach;

                //printr($trades, 0 );

                $tradeid = $_GET['TRADE'];
                $nexttrade = $tradeid + 1;
                $prevtrade = $tradeid - 1;
                //$tradeid = 140;
                //printr($trades[$tradeid], 0);
                $thetrade = $trades[$tradeid];

                $players1 = $thetrade['players1'];
                $expplayers1 = explode(',', $players1);
                $picks1 = $thetrade['picks1'];
                $exppicks1 = explode(',', $picks1);
                $protections1 = $thetrade['protections1'];
                $expprotections1 = explode(',', $protections1);

                $players2 = $thetrade['players2'];
                $expplayers2 = explode(',', $players2);
                $picks2 = $thetrade['picks2'];
                $exppicks2 = explode(',', $picks2);
                $protections2 = $thetrade['protections2'];
                $expprotections2 = explode(',', $protections2);

                //$picktest = get_player_by_pick('2000.02.08'); // RON DANE Issue not in DB but looks like he was drafted and PLayed
                //printr($trades, 0);
                //$picktest1 = get_player_by_pick('2022.03.01');
                //printr($picktest1, 0);

                function grade_acquisition($playerid, $startseason, $teamid){
                    // Determine the last season to count for this player on this team
                    $end_season = null;
                    
                    // Check if player was traded away from this team after the start season
                    $all_trades = get_trade_by_player($playerid);
                    if($all_trades):
                        foreach($all_trades as $trade_year => $trade_data):
                            if($trade_year > $startseason):
                                // Check if this team traded the player away
                                if($trade_data[0]['traded_from_team'] == $teamid):
                                    // Player was traded away in this year, stop counting here
                                    $end_season = $trade_year;
                                    break;
                                endif;
                            endif;
                        endforeach;
                    endif;
                    
                    // Get all protections for this player
                    $protections = get_protections_player($playerid);
                    $protected_years = array();
                    if($protections):
                        foreach($protections as $prot):
                            if($prot['team'] == $teamid && $prot['year'] >= $startseason):
                                $protected_years[$prot['year']] = true;
                            endif;
                        endforeach;
                    endif;
                    
                    // Build list of seasons to check
                    $getyears = the_seasons();
                    foreach($getyears as $year):
                        if($startseason <= $year):
                            // If we have an end season from a trade, don't go past it
                            if($end_season !== null && $year > $end_season):
                                break;
                            endif;
                            $poten_seasons[] = $year;
                        endif;
                    endforeach;
                    
                    // Get player stats for each season and check team ownership
                    foreach ($poten_seasons as $season):
                        $player_tenure[$season] = get_player_season_stats($playerid, $season);
                    endforeach;
                    
                    $last_counted_season = $startseason;
                    foreach ($player_tenure as $key => $year):
                        if(is_array($year)):
                            $season_has_games = false;
                            foreach ($year as $week => $value):
                                if(is_array($value)):
                                    $checkteam = $value['team'];
                                    $theseason = $value['season'];
                                    $theweek = $value['week'];
                                    $zeroweek = str_pad($theweek, 2, '0', STR_PAD_LEFT);
                                    if($checkteam == $teamid):
                                        $playeronteam[$theseason.$zeroweek] = $value;
                                        $season_has_games = true;
                                        $last_counted_season = $theseason;
                                    endif;
                                endif;
                            endforeach;
                            
                            // If player played games this season for this team,
                            // check if they were protected into next season
                            if($season_has_games):
                                $next_season = $key + 1;
                                // If not protected into next season and didn't play games, stop counting
                                if(!isset($protected_years[$next_season])):
                                    // Check if player has any games next season for this team
                                    $next_season_stats = get_player_season_stats($playerid, $next_season);
                                    $has_games_next_season = false;
                                    if(is_array($next_season_stats)):
                                        foreach($next_season_stats as $w => $v):
                                            if(is_array($v) && $v['team'] == $teamid):
                                                $has_games_next_season = true;
                                                break;
                                            endif;
                                        endforeach;
                                    endif;
                                    // If no games next season and not protected, this was the last season
                                    if(!$has_games_next_season):
                                        break;
                                    endif;
                                endif;
                            endif;
                        endif;
                    endforeach;

                    return $playeronteam;
                }
                
                function format_week_range($games_array){
                    if(empty($games_array)):
                        return '';
                    endif;
                    
                    $weeks = array_keys($games_array);
                    sort($weeks);
                    $first_week = reset($weeks);
                    $last_week = end($weeks);
                    
                    // Format: YYYYWW -> YYYY Week W
                    $first_year = substr($first_week, 0, 4);
                    $first_wk = ltrim(substr($first_week, 4, 2), '0');
                    $last_year = substr($last_week, 0, 4);
                    $last_wk = ltrim(substr($last_week, 4, 2), '0');
                    
                    if($first_year == $last_year):
                        return $first_year.' Week '.$first_wk.' - Week '.$last_wk;
                    else:
                        return $first_year.' Week '.$first_wk.' - '.$last_year.' Week '.$last_wk;
                    endif;
                }
                //$grade = grade_acquisition('2018JackQB', 2021, 'BUL');
                //printr($grade, 0);

                ?>
                
                <div id="page-title">
                    <h1 class="page-header text-bold">Trade Analyzer</h1>
                    <h3><?php echo $thetrade['year'].' / '.$thetrade['when'].' - Trade ID: '.$tradeid; ?></h3>
                </div>
                
                <div class="row mar-btm">
                    <div class="col-xs-24">
                        <?php if($prevtrade > 0): ?>
                            <a href="?TRADE=<?php echo $prevtrade; ?>" class="btn btn-default">← Previous Trade</a>
                        <?php endif; ?>
                        <a href="?TRADE=<?php echo $nexttrade; ?>" class="btn btn-default">Next Trade →</a>
                        <a href="/trades/" class="btn btn-default">All Trades</a>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-xs-24 col-sm-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title"><?php echo $teamlist[$thetrade['team1']]['team']; ?></h3>
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <h5 class="text-bold mar-btm">Players</h5>
                        <?php
                        foreach($expplayers1 as $key => $value):
                            $trimplay = ltrim($value);
                            if($trimplay):
                                echo '<div class="mar-btm">';
                                echo '<strong>'.pid_to_name($trimplay, 2).'</strong><br>';
                                $grade_traded_player1 = grade_acquisition($trimplay, $thetrade['year'], $thetrade['team1'] );
                                $printplayer1 = convert_stats_array($grade_traded_player1);
                                echo $printplayer1['points'].' points / '.$printplayer1['games'].' games';
                                $week_range1 = format_week_range($grade_traded_player1);
                                if($week_range1):
                                    echo '<br><small class="text-muted">'.$week_range1.'</small>';
                                endif;
                                echo '</div>';
                                $tradesum1[] = $printplayer1['points'];

                                // check to see if player was traded away by that team later.  If so, include that next deal in the value.
                                $istraded_1 = get_trade_by_player($trimplay);
                                foreach ($istraded_1 as $year => $value):
                                    if($year > $thetrade['year']):
                                        if($value[0]['traded_from_team'] == $thetrade['team1']):
                                            $newtrades1[$year] = $value;
                                        endif;
                                    endif;
                                endforeach;
                                if($newtrades1):
                                    echo '<h4>Who was later traded away for:</h4>';
                                    //printr($newtrades1, 0);
                                    foreach ($newtrades1 as $key => $value):
                                        $alsogot1[] = array(
                                            'players' => $newtrades1[$key][0]['sent_players'],
                                            'picks' => $newtrades1[$key][0]['sent_picks'],
                                        );
                                    endforeach;
                                    $otherplayers1[] = $alsogot1[0]['players'];
                                    $otherpicks1[] = $alsogot1[0]['picks'];
                                endif;
                            else:
                                echo '<p class="text-muted">No Players</p>';
                            endif;
                        endforeach;
                        if($alsogot1):
                            echo '<h6 class="text-thin text-muted mar-top">Later Traded Away For:</h6>';
                            //printr($otherplayers1, 0);
                            // get the extra players from the player later traded away + his team points
                            foreach ($otherplayers1[0] as $key => $value):
                                if($value):
                                    // $value is already a player ID
                                    echo '<div class="mar-btm pad-lft">';
                                    echo '<strong>'.pid_to_name($value, 2).'</strong><br>';
                                    $grade_printplayerextra1 = grade_acquisition($value, $thetrade['year'], $thetrade['team1'] );
                                    $printplayerextra1 = convert_stats_array($grade_printplayerextra1);
                                    echo $printplayerextra1['points'].' points / '.$printplayerextra1['games'].' games';
                                    $week_range_extra1 = format_week_range($grade_printplayerextra1);
                                    if($week_range_extra1):
                                        echo '<br><small class="text-muted">'.$week_range_extra1.'</small>';
                                    endif;
                                    echo '</div>';
                                    $tradesum1[] = $printplayerextra1['points'];
                                endif;
                            endforeach;

                            // get the extra picks from the traded player and convert them to a player + his team points
                            foreach($otherpicks1[0] as $key => $value):
                                if($value):
                                    echo '<div class="mar-btm pad-lft">';
                                    echo '<small class="text-muted">Pick '.$value.' became:</small><br>';
                                    $playerpickextra1 = get_player_by_pick($value);
                                    echo '<strong>'.pid_to_name($playerpickextra1, 2).'</strong><br>';
                                    $grade_printplayerpickextra1 = grade_acquisition($playerpickextra1, $thetrade['year'], $thetrade['team1'] );
                                    $printplayerpickextra1 = convert_stats_array($grade_printplayerpickextra1);
                                    echo $printplayerpickextra1['points'].' points / '.$printplayerpickextra1['games'].' games';
                                    $week_range_pickextra1 = format_week_range($grade_printplayerpickextra1);
                                    if($week_range_pickextra1):
                                        echo '<br><small class="text-muted">'.$week_range_pickextra1.'</small>';
                                    endif;
                                    echo '</div>';
                                    $tradesum1[] = $printplayerpickextra1['points'];
                                endif;
                            endforeach;
                            //printr($printplayerpickextra1, 0);
                        endif;
                        ?>
                        
                        <h5 class="text-bold mar-top mar-btm">Draft Picks</h5>
                        <?php
                        //printr($exppicks1, 0);
                        foreach($exppicks1 as $key => $value):
                            $trimmed = ltrim($value);
                            $pickprint1 = format_draft_pick_return($trimmed);
                            if($trimmed):
                                echo '<div class="mar-btm">';
                                echo '<strong>'.$pickprint1.'</strong><br>';
                                $playerpp1 = get_player_by_pick($trimmed);
                                if($playerpp1):
                                    echo '<small class="text-muted">Became: '.pid_to_name($playerpp1, 2).'</small><br>';
                                    // Team 1 owns picks1, so grade for team1
                                    $grade_pick_player1 = grade_acquisition($playerpp1, $thetrade['year'], $thetrade['team1'] );
                                    $printpick1 = convert_stats_array($grade_pick_player1);
                                    echo $printpick1['points'].' points / '.$printpick1['games'].' games';
                                    $week_range_pick1 = format_week_range($grade_pick_player1);
                                    if($week_range_pick1):
                                        echo '<br><small class="text-muted">'.$week_range_pick1.'</small>';
                                    endif;
                                else:
                                    echo '<small class="text-muted">Used for a player that never played.</small>';
                                endif;
                                echo '</div>';
                                $tradesum1[] = $printpick1['points'];
                            else:
                                echo '<p class="text-muted">No Picks</p>';
                            endif;
                        endforeach;
                        ?>
                        
                        <?php if($protections1): ?>
                        <h5 class="text-bold mar-top mar-btm">Protections</h5>
                        <div class="mar-btm">
                        <?php 
                        echo '<strong>'.pid_to_name($protections1, 2).'</strong><br>';
                        echo '<small class="text-muted">Protection traded (points counted in player stats)</small>';
                        // Don't add protection points to trade sum - they're already counted in player stats
                        // $grade_protection_1 = grade_acquisition($protections1, $thetrade['year'], $thetrade['team1'] );
                        // $printprotection1 = convert_stats_array($grade_protection_1);
                        // echo $printprotection1['points'].' points / '.$printprotection1['games'].' games';
                        // $tradesum1[] = $printprotection1['points'];
                        ?>
                        </div>
                        <?php endif; ?>
                            </div>
                            <div class="panel-footer">
                                <h4 class="text-bold mar-no">Total Trade Value: 
                                    <?php
                                        //printr($tradesum1, 0);
                                        $addsum1 = array_sum($tradesum1);
                                        echo '<span class="text-primary">'.$addsum1.' points</span>';
                                    ?>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <!-- SECOND TEAM -->

                    <div class="col-xs-24 col-sm-12">
                        <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title"><?php echo $teamlist[$thetrade['team2']]['team']; ?></h3>
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <h5 class="text-bold mar-btm">Players</h5>
                        <?php
                        foreach($expplayers2 as $key => $value):
                            $trimplay = ltrim($value);
                            if($trimplay):
                                echo '<div class="mar-btm">';
                                echo '<strong>'.pid_to_name($trimplay, 2).'</strong><br>';
                                $grade_traded_player2 = grade_acquisition($trimplay, $thetrade['year'], $thetrade['team2'] );
                                $printplayer2 = convert_stats_array($grade_traded_player2);
                                echo $printplayer2['points'].' points / '.$printplayer2['games'].' games';
                                $week_range2 = format_week_range($grade_traded_player2);
                                if($week_range2):
                                    echo '<br><small class="text-muted">'.$week_range2.'</small>';
                                endif;
                                echo '</div>';
                                $tradesum2[] = $printplayer2['points'];

                                // check to see if player was traded away by that team later.  If so, include that next deal in the value.
                                $istraded_2 = get_trade_by_player($trimplay);
                                foreach ($istraded_2 as $year => $value):
                                    if($year > $thetrade['year']):
                                        if($value[0]['traded_from_team'] == $thetrade['team2']):
                                            $newtrades2[$year] = $value;
                                        endif;
                                    endif;
                                endforeach;
                                if($newtrades2):
                                    //printr($newtrades1, 0);
                                    foreach ($newtrades2 as $key => $value):
                                        if($key):
                                        $alsogot2[] = array(
                                            'players' => $newtrades2[$key][0]['sent_players'],
                                            'picks' => $newtrades2[$key][0]['sent_picks']
                                        );
                                        endif;
                                    endforeach;
                                    $otherplayers2[] = $alsogot2[0]['players'];
                                    $otherpicks2[] = $alsogot2[0]['picks'];
                                endif;
                            else:
                                echo '<p class="text-muted">No Players</p>';
                            endif;
                        endforeach;
                        if($alsogot2):
                            echo '<h6 class="text-thin text-muted mar-top">Later Traded Away For:</h6>';
                            // get the extra players from the player later traded away + his team points
                            foreach ($otherplayers2[0] as $key => $value):
                                if($value):
                                    // $value is already a player ID
                                    echo '<div class="mar-btm pad-lft">';
                                    echo '<strong>'.pid_to_name($value, 2).'</strong><br>';
                                    $grade_printplayerextra2 = grade_acquisition($value, $thetrade['year'], $thetrade['team2'] );
                                    $printplayerextra2 = convert_stats_array($grade_printplayerextra2);
                                    echo $printplayerextra2['points'].' points / '.$printplayerextra2['games'].' games';
                                    $week_range_extra2 = format_week_range($grade_printplayerextra2);
                                    if($week_range_extra2):
                                        echo '<br><small class="text-muted">'.$week_range_extra2.'</small>';
                                    endif;
                                    echo '</div>';
                                    $tradesum2[] = $printplayerextra2['points'];
                                endif;
                            endforeach;

                            // get the extra picks from the traded player and convert them to a player + his team points
                            //printr($otherpicks2[0], 0);
                            foreach($otherpicks2[0] as $key => $value):
                                if($value):
                                    echo '<div class="mar-btm pad-lft">';
                                    echo '<small class="text-muted">Pick '.$value.' became:</small><br>';
                                    $playerpickextra2 = get_player_by_pick($value);
                                    echo '<strong>'.pid_to_name($playerpickextra2, 2).'</strong><br>';
                                    $grade_printplayerpickextra2 = grade_acquisition($playerpickextra2, $thetrade['year'], $thetrade['team2'] );
                                    $printplayerpickextra2 = convert_stats_array($grade_printplayerpickextra2);
                                    echo $printplayerpickextra2['points'].' points / '.$printplayerpickextra2['games'].' games';
                                    $week_range_pickextra2 = format_week_range($grade_printplayerpickextra2);
                                    if($week_range_pickextra2):
                                        echo '<br><small class="text-muted">'.$week_range_pickextra2.'</small>';
                                    endif;
                                    echo '</div>';
                                    $tradesum2[] = $printplayerpickextra2['points'];
                                endif;
                            endforeach;
                        endif;
                        ?>
                        
                        <h5 class="text-bold mar-top mar-btm">Draft Picks</h5>
                        <?php
                            //printr($exppicks2, 0);
                            foreach($exppicks2 as $key => $value):
                                $trimmed = ltrim($value);
                                $pickprint2 = format_draft_pick_return($trimmed);
                                if($trimmed):
                                    echo '<div class="mar-btm">';
                                    echo '<strong>'.$pickprint2.'</strong><br>';
                                    $playerpp2 = get_player_by_pick($trimmed);
                                    if($playerpp2):
                                        echo '<small class="text-muted">Became: '.pid_to_name($playerpp2, 2).'</small><br>';
                                        $grade_pick_player2 = grade_acquisition($playerpp2, $thetrade['year'], $thetrade['team2'] );
                                        $printpick2 = convert_stats_array($grade_pick_player2);
                                        echo $printpick2['points'].' points / '.$printpick2['games'].' games';
                                        $week_range_pick2 = format_week_range($grade_pick_player2);
                                        if($week_range_pick2):
                                            echo '<br><small class="text-muted">'.$week_range_pick2.'</small>';
                                        endif;
                                    else:
                                        echo '<small class="text-muted">Used for a player that never played.</small>';
                                    endif;
                                    echo '</div>';
                                    $tradesum2[] = $printpick2['points'];
                                else:
                                    echo '<p class="text-muted">No Picks</p>';
                                endif;
                            endforeach;
                        ?>
                        
                        <?php if($protections2): ?>
                        <h5 class="text-bold mar-top mar-btm">Protections</h5>
                        <div class="mar-btm">
                        <?php 
                        echo '<strong>'.pid_to_name($protections2, 2).'</strong><br>';
                        echo '<small class="text-muted">Protection traded (points counted in player stats)</small>';
                        // Don't add protection points to trade sum - they're already counted in player stats
                        // $grade_protection_2 = grade_acquisition($protections2, $thetrade['year'], $thetrade['team2'] );
                        // $printprotection2 = convert_stats_array($grade_protection_2);
                        // echo $printprotection2['points'].' points / '.$printprotection2['games'].' games';
                        // $tradesum2[] = $printprotection2['points'];
                        ?>
                        </div>
                        <?php endif; ?>
                            </div>
                            <div class="panel-footer">
                                <h4 class="text-bold mar-no">Total Trade Value: 
                                    <?php
                                        //printr($tradesum2, 0);
                                        $addsum2 = array_sum($tradesum2);
                                        echo '<span class="text-primary">'.$addsum2.' points</span>';
                                    ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Trade Winner Summary -->
                <div class="row mar-top">
                    <div class="col-xs-24">
                        <div class="panel panel-dark">
                            <div class="panel-body text-center">
                                <?php
                                    $difference1 = $addsum1 - $addsum2;
                                    $difference2 = $addsum2 - $addsum1;
                                    if($difference2 > 0):
                                        echo '<h3 class="text-bold mar-no">'.$teamlist[$thetrade['team2']]['team'].' won trade by <span class="text-success">'.$difference2.' points</span></h3>';
                                        insert_trade_winner($tradeid, $thetrade['team2'], $thetrade['team1'], $difference2);
                                    elseif($difference1 > 0):
                                        echo '<h3 class="text-bold mar-no">'.$teamlist[$thetrade['team1']]['team'].' won trade by <span class="text-success">'.$difference1.' points</span></h3>';
                                        insert_trade_winner($tradeid, $thetrade['team1'], $thetrade['team2'], $difference1);
                                    else:
                                        echo '<h3 class="text-bold mar-no">Even Trade - <span class="text-muted">0 point difference</span></h3>';
                                    endif;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
</div>



    </div><!--End page content-->

</div><!--END CONTENT CONTAINER-->


<?php include_once('main-nav.php'); ?>
<?php include_once('aside.php'); ?>

</div>

<?php get_footer(); ?>