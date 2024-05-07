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
                <H3>Trade Analyizer</H3>

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
                echo $tradeid;
                $nexttrade = $tradeid + 1;
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
                    $getyears = the_seasons();
                    foreach($getyears as $year):
                        if($startseason <= $year):
                            $poten_seasons[] = $year;
                        endif;
                    endforeach;
                    foreach ($poten_seasons as $season):
                        $player_tenure[$season] = get_player_season_stats($playerid, $season);
                    endforeach;
                    foreach ($player_tenure as $key => $year):
                        if(is_array($year)):
                        foreach ($year as $week => $value):
                                if(is_array($value)):
                                    $checkteam = $value['team'];
                                    $theseason = $value['season'];
                                    $theweek = $value['week'];
                                    $zeroweek = str_pad($theweek, 2, '0', STR_PAD_LEFT);;
                                if($checkteam == $teamid):
                                    $playeronteam[$theseason.$zeroweek] = $value;
                                endif;
                            endif;
                        endforeach;
                        endif;
                    endforeach;

                    return $playeronteam;
                }
                //$grade = grade_acquisition('2018JackQB', 2021, 'BUL');
                //printr($grade, 0);

                ?>
                <a href="?TRADE=<?php echo $nexttrade?>">Next Trade</a>
                <div class="row">
                    <div class="col-xs-24 col-sm-12">
                        <?php echo '<h3>'.$thetrade['year'].' / '.$thetrade['when'].' - Trade ID:'.$tradeid.'</h3>'; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-24 col-sm-8">
                        <?php echo '<h2>'.$teamlist[$thetrade['team1']]['team'].'</h2>'; ?>
                        <hr>
                        <h5>Players:</h5>
                        <?php
                        foreach($expplayers1 as $key => $value):
                            $trimplay = ltrim($value);
                            if($trimplay):
                                echo '<h3>'.pid_to_name($trimplay, 2).'</h3>';
                                $grade_traded_player1 = grade_acquisition($trimplay, $thetrade['year'], $thetrade['team1'] );
                                $printplayer1 = convert_stats_array($grade_traded_player1);
                                echo $printplayer1['points'].' points / '.$printplayer1['games'].' games';
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
                                echo '<h3>No Players</h3>';
                            endif;
                        endforeach;
                        if($alsogot1):
                            //printr($otherplayers1, 0);
                            // get the extra players from the player later traded away + his team points
                            foreach ($otherplayers1[0] as $key => $value):
                                if($value):
                                    echo '<h5>Player assets were later traded away for: '.pid_to_name($value, 2).'</h5>';
                                    $playerextra1 = get_player_by_pick($value);
                                    echo '<h4>'.pid_to_name($playerextra1, 2).'</h4>';
                                    $grade_printplayerextra1 = grade_acquisition($playerextra1, $thetrade['year'], $thetrade['team1'] );
                                    $printplayerextra1 = convert_stats_array($grade_printplayerextra1);
                                    echo $printplayerextra1['points'].' points / '.$printplayerextra1['games'].' games';
                                    $tradesum1[] = $printplayerextra1['points'];
                                endif;
                            endforeach;

                            // get the extra picks from the traded player and convert them to a player + his team points
                            foreach($otherpicks1[0] as $key => $value):
                                if($value):
                                    echo '<h5>Player assets were later traded away for pick: '.$value.' that beacme</h5>';
                                    $playerpickextra1 = get_player_by_pick($value);
                                    echo '<h4>'.pid_to_name($playerpickextra1, 2).'</h4>';
                                    $grade_printplayerpickextra1 = grade_acquisition($playerpickextra1, $thetrade['year'], $thetrade['team1'] );
                                    $printplayerpickextra1 = convert_stats_array($grade_printplayerpickextra1);
                                    echo $printplayerpickextra1['points'].' points / '.$printplayerpickextra1['games'].' games';
                                    $tradesum1[] = $printplayerpickextra1['points'];
                                endif;
                            endforeach;
                            //printr($printplayerpickextra1, 0);
                        endif;
                        ?>
                        <hr>
                        <h5>Picks:</h5>
                        <?php
                        //printr($exppicks1, 0);
                        foreach($exppicks1 as $key => $value):
                            $trimmed = ltrim($value);
                            $pickprint1 = format_draft_pick_return($trimmed);
                            echo '<h3>'.$pickprint1.'</h3>';
                            if($trimmed):
                                $playerpp1 = get_player_by_pick($trimmed);
                                if($playerpp1):
                                    echo '<h3>Became: '.pid_to_name($playerpp1, 2).'</h3>';
                                    $grade_pick_player1 = grade_acquisition($playerpp1, $thetrade['year'], $thetrade['team2'] );
                                    $printpick1 = convert_stats_array($grade_pick_player1);
                                    echo $printpick1['points'].' points / '.$printpick1['games'].' games';
                                    $tradesum1[] = $printpick1['points'];
                                else:
                                    echo 'Used for a player that never played.';
                                endif;

                            else:
                                echo '<h3>No Picks</h3>';
                            endif;
                        endforeach;
                        ?>
                        <hr>
                        <h5>Protections:</h5>
                        <?php echo '<h3>'.pid_to_name($protections1, 2).'</h3>';
                        $grade_protection_1 = grade_acquisition($protections1, $thetrade['year'], $thetrade['team1'] );
                        $printprotection1 = convert_stats_array($grade_protection_1);
                        echo $printprotection1['points'].' points / '.$printprotection1['games'].' games';
                        $tradesum1[] = $printprotection1['points'];
                        ?>
                        <hr>
                        <h5>Trade Value</h5>
                        <?php
                            //printr($tradesum1, 0);
                            $addsum1 = array_sum($tradesum1);
                            echo '<h3>'.$addsum1.'</h3>';
                        ?>
                    </div>

                    // SECOND TEAM

                    <div class="col-xs-24 col-sm-8">
                        <?php echo '<h2>'.$teamlist[$thetrade['team2']]['team'].'</h2>'; ?>
                        <hr>
                        <h5>Players:</h5>
                        <?php
                        foreach($expplayers2 as $key => $value):
                            $trimplay = ltrim($value);
                            if($trimplay):
                                echo '<h3>'.pid_to_name($trimplay, 2).'</h3>';
                                $grade_traded_player2 = grade_acquisition($trimplay, $thetrade['year'], $thetrade['team2'] );
                                $printplayer2 = convert_stats_array($grade_traded_player2);
                                echo $printplayer2['points'].' points / '.$printplayer2['games'].' games';
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
                                echo '<h3>No Players</h3>';
                            endif;
                        endforeach;
                        if($alsogot2):
                            // get the extra players from the player later traded away + his team points
                            foreach ($otherplayers2[0] as $key => $value):
                                if($value):
                                    echo '<h5>Player assets were later traded away for: '.pid_to_name($value, 2).'</h5>';
                                    $playerextra2 = get_player_by_pick($value);
                                    echo '<h4>'.pid_to_name($playerextra2, 2).'</h4>';
                                    $grade_printplayerextra2 = grade_acquisition($playerextra2, $thetrade['year'], $thetrade['team2'] );
                                    $printplayerextra2 = convert_stats_array($grade_printplayerextra2);
                                    echo $printplayerextra2['points'].' points / '.$printplayerextra2['games'].' games';
                                    $tradesum2[] = $printplayerextra2['points'];
                                endif;
                            endforeach;

                            // get the extra picks from the traded player and convert them to a player + his team points
                            //printr($otherpicks2[0], 0);
                            foreach($otherpicks2[0] as $key => $value):
                                if($value):
                                    echo '<h5>Player assets were later traded away for pick: '.$value.' that beacme</h5>';
                                    $playerpickextra2 = get_player_by_pick($value);
                                    echo '<h4>'.pid_to_name($playerpickextra2, 2).'</h4>';
                                    $grade_printplayerpickextra2 = grade_acquisition($playerpickextra2, $thetrade['year'], $thetrade['team2'] );
                                    $printplayerpickextra2 = convert_stats_array($grade_printplayerpickextra2);
                                    echo $printplayerpickextra2['points'].' points / '.$printplayerpickextra2['games'].' games';
                                    $tradesum2[] = $printplayerpickextra2['points'];
                                endif;
                            endforeach;
                        endif;
                        ?>
                        <hr>
                        <h5>Picks:</h5>
                        <?php
                            //printr($exppicks2, 0);
                            foreach($exppicks2 as $key => $value):
                                $trimmed = ltrim($value);
                                $pickprint2 = format_draft_pick_return($trimmed);
                                echo '<h3>'.$pickprint2.'</h3>';
                                if($trimmed):
                                    $playerpp2 = get_player_by_pick($trimmed);
                                    if($playerpp2):
                                        echo '<h3>Became: '.pid_to_name($playerpp2, 2).'</h3>';
                                        $grade_pick_player2 = grade_acquisition($playerpp2, $thetrade['year'], $thetrade['team2'] );
                                        $printpick2 = convert_stats_array($grade_pick_player2);
                                        echo $printpick2['points'].' points / '.$printpick2['games'].' games';
                                        $tradesum2[] = $printpick2['points'];
                                    else:
                                        echo 'Used for a player that never played.';
                                    endif;
                                else:
                                    echo '<h3>No Picks</h3>';
                                endif;
                            endforeach;
                        ?>
                        <hr>
                        <h5>Protections:</h5>
                        <?php echo '<h3>'.pid_to_name($protections2, 2).'</h3>';
                        $grade_protection_2 = grade_acquisition($protections2, $thetrade['year'], $thetrade['team2'] );
                        $printprotection2 = convert_stats_array($grade_protection_2);
                        echo $printprotection2['points'].' points / '.$printprotection2['games'].' games';
                        $tradesum2[] = $printprotection2['points'];
                        ?>
                        <hr>
                        <h5>Trade Value</h5>
                        <?php
                            //printr($tradesum2, 0);
                            $addsum2 = array_sum($tradesum2);
                            echo '<h3>'.$addsum2.'</h3>';
                            $difference1 = $addsum1 - $addsum2;
                            $difference2 = $addsum2 - $addsum1;
                            if($difference2 > 0):
                                echo '<h3>'.$teamlist[$thetrade['team2']]['team'].' won trade by '.$difference2.' points</h3>';
                                insert_trade_winner($tradeid, $thetrade['team2'], $thetrade['team1'], $difference2);
                            else:
                                echo '<h3>'.$teamlist[$thetrade['team1']]['team'].' won trade by '.$difference1.' points</h3>';
                                insert_trade_winner($tradeid, $thetrade['team1'], $thetrade['team2'], $difference1);
                            endif;
                        ?>
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