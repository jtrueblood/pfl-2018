<?php
/*
 * Template Name: Build MFL Player OT Score
 * Description: Get score for a player in a given week as part of an OT game  */
?>


<?php get_header(); ?>

<?php

// THIS IS THE URL FORMAT
// http://pfl-data.local/player-ot-score/?SQL=2&GID=01&Y=2022&W=12&HOME=HAT&QBH=2016PresQB&RBH=2022GainRB&WRH=2022AndrWR&PKH=2015SantPK&AWAY=CMN&RBA=2022ForeRB&PKA=2007CrosPK

$team_all_ids = get_teams();
$playersassoc = get_players_assoc();
$theseasons = the_seasons();
$theweeks = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14);

$year = $_GET['Y'];
$week = $_GET['W'];
$weekform = sprintf('%02d', $week);
$weekid = $year.$weekform;
$run = $_GET['SQL'];
$gid = $_GET['GID'];

// HOME TEAM
$hometeam = $_GET['HOME'];

$qb_h_pfl = $_GET['QBH'];
$qb_h_info = get_player_basic_info($qb_h_pfl);
$qb_h_mfl = $qb_h_info[0]['mflid'];

$rb_h_pfl = $_GET['RBH'];
$rb_h_info = get_player_basic_info($rb_h_pfl);
$rb_h_mfl = $rb_h_info[0]['mflid'];

$wr_h_pfl = $_GET['WRH'];
$wr_h_info = get_player_basic_info($wr_h_pfl);
$wr_h_mfl = $wr_h_info[0]['mflid'];

$pk_h_pfl = $_GET['PKH'];
$pk_h_info = get_player_basic_info($pk_h_pfl);
$pk_h_mfl = $pk_h_info[0]['mflid'];

// AWAY TEAM
$awayteam = $_GET['AWAY'];

$qb_a_pfl = $_GET['QBA'];
$qb_a_info = get_player_basic_info($qb_a_pfl);
$qb_a_mfl = $qb_a_info[0]['mflid'];

$rb_a_pfl = $_GET['RBA'];
$rb_a_info = get_player_basic_info($rb_a_pfl);
$rb_a_mfl = $rb_a_info[0]['mflid'];

$wr_a_pfl = $_GET['WRA'];
$wr_a_info = get_player_basic_info($wr_a_pfl);
$wr_a_mfl = $wr_a_info[0]['mflid'];

$pk_a_pfl = $_GET['PKA'];
$pk_a_info = get_player_basic_info($pk_a_pfl);
$pk_a_mfl = $pk_a_info[0]['mflid'];

$teamarray = array(
    $qb_h_pfl => $hometeam,
    $rb_h_pfl => $hometeam,
    $wr_h_pfl => $hometeam,
    $pk_h_pfl => $hometeam,
    $qb_a_pfl => $awayteam,
    $rb_a_pfl => $awayteam,
    $wr_a_pfl => $awayteam,
    $pk_a_pfl => $awayteam
);
//printr($teamarray, 0);

function get_score_for_ot($mflpid, $pflpid, $year, $week, $teams){
    $hometeam = $_GET['HOME'];
    $awayteam = $_GET['AWAY'];
    $weekform = sprintf('%02d', $week);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www48.myfantasyleague.com/$year/export?TYPE=playerScores&L=38954&W=$week&YEAR=$year&PLAYERS=$mflpid&JSON=1",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Cookie: MFL_PW_SEQ=ah9q2M6Ss%2Bis3Q29; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $weekresults = json_decode($response, true);
    $points = $weekresults['playerScores']['playerScore']['score'];
    $location = get_stadium_by_team($hometeam);
    $playerteam = $teams[$pflpid];
    if($playerteam == $awayteam):
        $theteam = $awayteam;
        $otherteam = $hometeam;
        $where = 'A';
    else:
        $theteam = $hometeam;
        $otherteam = $awayteam;
        $where = 'H';
    endif;

    $insert_array = array(
        'week_id' => $year.$weekform,
        'year' => $year,
        'week' => $week,
        'points' => $points,
        'team' => $theteam,
        'versus' => $otherteam,
        'playerid' => $pflpid,
        'win_loss' => '',
        'home_away' => $where,
        'location' => $location,
    );
    return $insert_array;
}

$insert_qb_h = get_score_for_ot($qb_h_mfl, $qb_h_pfl, $year, $week, $teamarray);
$insert_rb_h = get_score_for_ot($rb_h_mfl, $rb_h_pfl, $year, $week, $teamarray);
$insert_wr_h = get_score_for_ot($wr_h_mfl, $wr_h_pfl, $year, $week, $teamarray);
$insert_pk_h = get_score_for_ot($pk_h_mfl, $pk_h_pfl, $year, $week, $teamarray);

$insert_qb_a = get_score_for_ot($qb_a_mfl, $qb_a_pfl, $year, $week, $teamarray);
$insert_rb_a = get_score_for_ot($rb_a_mfl, $rb_a_pfl, $year, $week, $teamarray);
$insert_wr_a = get_score_for_ot($wr_a_mfl, $wr_a_pfl, $year, $week, $teamarray);
$insert_pk_a = get_score_for_ot($pk_a_mfl, $pk_a_pfl, $year, $week, $teamarray);

$homescore = $insert_qb_h['points'] + $insert_rb_h['points'] + $insert_wr_h['points'] + $insert_pk_h['points'];
$awayscore = $insert_qb_a['points'] + $insert_rb_a['points'] + $insert_wr_a['points'] + $insert_pk_a['points'];

if($homescore > $awayscore):
    $insert_qb_h['win_loss'] = 1;
    $insert_rb_h['win_loss'] = 1;
    $insert_wr_h['win_loss'] = 1;
    $insert_pk_h['win_loss'] = 1;
    $insert_qb_a['win_loss'] = 0;
    $insert_rb_a['win_loss'] = 0;
    $insert_wr_a['win_loss'] = 0;
    $insert_pk_a['win_loss'] = 0;

    $winqb = $insert_qb_h['playerid'];
    $winrb = $insert_rb_h['playerid'];
    $winwr = $insert_wr_h['playerid'];
    $winpk = $insert_pk_h['playerid'];
    $loseqb = $insert_qb_a['playerid'];
    $loserb = $insert_rb_a['playerid'];
    $losewr = $insert_wr_a['playerid'];
    $losepk = $insert_pk_a['playerid'];
else:
    $insert_qb_h['win_loss'] = 0;
    $insert_rb_h['win_loss'] = 0;
    $insert_wr_h['win_loss'] = 0;
    $insert_pk_h['win_loss'] = 0;
    $insert_qb_a['win_loss'] = 1;
    $insert_rb_a['win_loss'] = 1;
    $insert_wr_a['win_loss'] = 1;
    $insert_pk_a['win_loss'] = 1;

    $loseqb = $insert_qb_h['playerid'];
    $loserb = $insert_rb_h['playerid'];
    $losewr = $insert_wr_h['playerid'];
    $losepk = $insert_pk_h['playerid'];
    $winqb = $insert_qb_a['playerid'];
    $winrb = $insert_rb_a['playerid'];
    $winwr = $insert_wr_a['playerid'];
    $winpk = $insert_pk_a['playerid'];
endif;

$insert_player = array(
    $qb_h_pfl => $insert_qb_h,
    $rb_h_pfl => $insert_rb_h,
    $wr_h_pfl => $insert_wr_h,
    $pk_h_pfl => $insert_pk_h,
    $qb_a_pfl => $insert_qb_a,
    $rb_a_pfl => $insert_rb_a,
    $wr_a_pfl => $insert_wr_a,
    $pk_a_pfl => $insert_pk_a,
);

if($homescore > $awayscore):
    $chickendinner = $hometeam;
    $bigloser = $awayteam;
else:
    $chickendinner = $awayteam;
    $bigloser = $hometeam;
endif;

//printr($insert_player, 0);

//Insert formatted data into wp_overtime table
global $wpdb;
if($run == 1):
    foreach ($insert_player as $key => $value){
        $winningqb = isset($winqb) ?  $winqb : 'None';
        $winningrb = isset($winrb) ?  $winrb : 'None';
        $winningwr = isset($winwr) ?  $winwr : 'None';
        $winningpk = isset($winpk) ?  $winpk : 'None';
        $loseingqb = isset($loseqb) ?  $loseqb : 'None';
        $loseingrb = isset($loserb) ?  $loserb : 'None';
        $loseingwr = isset($losewr) ?  $losewr : 'None';
        $loseingpk = isset($losepk) ?  $losepk : 'None';

        $wpdb->insert(
            'wp_overtime',
            array(
                'id' => $year.$weekform.$gid,
                'winteam' => $chickendinner,
                'loseteam' => $bigloser,
                'winQB' => $winningqb,
                'winRB' => $winningrb,
                'winWR' => $winningwr,
                'winPK' => $winningpk,
                'loseQB' => $loseingqb,
                'loseRB' => $loseingrb,
                'loseWR' => $loseingwr,
                'losePK' => $loseingpk,
                'hometeam' => $hometeam,
                'extraot' => 0
            ),
            array (
                '%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d'
            )
        );
    }
endif;



//Insert formatted data into winning team wp_team_XXX table
if($run == 2):
    $winningqb = isset($winqb) ?  $winqb : 'None';
    $winningrb = isset($winrb) ?  $winrb : 'None';
    $winningwr = isset($winwr) ?  $winwr : 'None';
    $winningpk = isset($winpk) ?  $winpk : 'None';
    $loseingqb = isset($loseqb) ?  $loseqb : 'None';
    $loseingrb = isset($loserb) ?  $loserb : 'None';
    $loseingwr = isset($losewr) ?  $losewr : 'None';
    $loseingpk = isset($losepk) ?  $losepk : 'None';

    $wpdb->update(
        'wp_team_'.$chickendinner,
        array(
            'overtime' 	=> 1,
            'QB2' => $winningqb,
            'RB2' => $winningrb,
            'WR2' => $winningwr,
            'PK2' => $winningpk,
            'extra_ot' => 0
        ),
        array (
            'id' => $weekid,
        )
    );

    $wpdb->update(
        'wp_team_'.$bigloser,
        array(
            'overtime' 	=> 1,
            'QB2' => $loseingqb,
            'RB2' => $loseingrb,
            'WR2' => $loseingwr,
            'PK2' => $loseingpk,
            'extra_ot' => 0
        ),
        array (
            'id' => $weekid,
        )
    );
endif;

//Insert formatted data into players tables
if($run == 3):
    foreach ($insert_player as $key => $pi){
        $wpdb->insert(
            $key,
            array(
                'week_id' 	=> $pi['week_id'],
                'year'		=> $pi['year'],
                'week'		=> $pi['week'],
                'points'	=> $pi['points'],
                'team'		=> $pi['team'],
                'versus'	=> $pi['versus'],
                'playerid'	=> $pi['playerid'],
                'win_loss'	=> $pi['win_loss'],
                'home_away'	=> $pi['home_away'],
                'location'	=> $pi['location'],
                // Change made in 2022 after player tables were expanded to include NFL Game stats.
                // Set values to empty.  Add weekly NFL Data using the scrape-pfr.php file
                'game_date' => '2022-00-00',
                'nflteam' => 'TTT',
                'game_location' => 'S',
                'nflopp' => 'ZZZ',
                'pass_yds' => 0,
                'pass_td' => 0,
                'pass_int' => 0,
                'rush_yds' => 0,
                'rush_td' => 0,
                'rec_yds' => 0,
                'rec_td' => 0,
                'xpm' => 0,
                'xpa' => 0,
                'fgm' => 0,
                'fga' => 0,
                'nflscore' => 0,
                'scorediff' => 0
            ),
            array (
                '%d','%d','%d','%d','%s','%s','%s','%d','%s','%s','%s','%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d'
            )
        );
    }
endif;

//$seasonstrans = get_or_set($seasons, 'seasons', 1200);
//$teamstrans = get_or_set($teams, 'teams');
//$playersassoc = get_or_set($playersas, 'playersassoc');

//$count = count($unique);
//echo $count;
?>

    <div style="min-height: 2000px;">

        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <div id="page-content">

                <div class="col-xs-24" style="margin-bottom: 20px;">
                <?php
                echo '<h2>Get OT Scores from MFL</h2>';
                echo '<strong>Instructions:</strong> 1. Select the Year, Week, Team, and Player values.  
                Win / Loss is calculated. Leave NONES blank
                Run the page once to build URL string and set variables.
                If everything looks correct, change SQL=1 to insert into wp_overtime table - reload,
                change SQL=2 to update both wp_team_XXX tables - reload,
                change SQL=3 to insert game scores into players tables - reload.
                If the game is one of many OT games that week, run process for first game, then go into wp_overtime table
                and change that game id to "...02".  Then run again for second game.  
                If Game is extra ot, so into wp_overtime and change the value from 0 to 1.  
                You may need to adjust who the winning and losing team is.';
                ?>
                </div>


                <div class="col-xs-12">
                    <select data-placeholder="Select Year..." class="chzn-select" style="width:100%;" id="pickYEAR">
                        <option value=""></option>

                        <?php
                        foreach ($theseasons as $key => $value){
                            $printyear .= '<option value="&Y='.$value.'">'.$value.'</option>';
                        }
                        echo $printyear;
                        ?>
                    </select>
                    <select data-placeholder="Select A Team..." class="chzn-select" style="width:100%;" id="pickteamOTH">
                        <option value=""></option>

                        <?php
                        foreach ($team_all_ids as $key => $value){
                            $printselect .= '<option value="&HOME='.$key.'">'.$value['team'].'</option>';
                        }
                        echo $printselect;
                        ?>
                    </select>
                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="pickplayerQBH">
                        <option value=""></option>

                        <?php
                        foreach ($playersassoc as $key => $selectplayer){
                            $firsto = $selectplayer[0];
                            $lasto = $selectplayer[1];
                            $playerselect_qbh .= '<option value="&QBH='.$key.'">'.$firsto.' '.$lasto.'</option>';
                        }
                        echo $playerselect_qbh;
                        ?>
                    </select>
                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="pickplayerRBH">
                        <option value=""></option>

                        <?php
                        foreach ($playersassoc as $key => $selectplayer){
                            $firsto = $selectplayer[0];
                            $lasto = $selectplayer[1];
                            $playerselect_rbh .= '<option value="&RBH='.$key.'">'.$firsto.' '.$lasto.'</option>';
                        }
                        echo $playerselect_rbh;
                        ?>
                    </select>
                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="pickplayerWRH">
                        <option value=""></option>

                        <?php
                        foreach ($playersassoc as $key => $selectplayer){
                            $firsto = $selectplayer[0];
                            $lasto = $selectplayer[1];
                            $playerselect_wrh .= '<option value="&WRH='.$key.'">'.$firsto.' '.$lasto.'</option>';
                        }
                        echo $playerselect_wrh;
                        ?>
                    </select>
                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="pickplayerPKH">
                        <option value=""></option>

                        <?php
                        foreach ($playersassoc as $key => $selectplayer){
                            $firsto = $selectplayer[0];
                            $lasto = $selectplayer[1];
                            $playerselect_pkh .= '<option value="&PKH='.$key.'">'.$firsto.' '.$lasto.'</option>';
                        }
                        echo $playerselect_pkh;
                        ?>
                    </select>


                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Home Team</h3>

                            <h4><?php echo $homescore;?></h4>

                            <?php printr($insert_qb_h, 0);?>
                            <?php printr($insert_rb_h, 0);?>
                            <?php printr($insert_wr_h, 0);?>
                            <?php printr($insert_pk_h, 0);?>
                        </div>
                    </div>


                </div>

                <div class="col-xs-12">
                    <select data-placeholder="Select Week..." class="chzn-select" style="width:100%;" id="pickWEEK">
                        <option value=""></option>

                        <?php
                        foreach ($theweeks as $key => $value){
                            $printweek .= '<option value="&W='.$value.'">'.$value.'</option>';
                        }
                        echo $printweek;
                        ?>
                    </select>
                    <select data-placeholder="Select A Team..." class="chzn-select" style="width:100%;" tabindex="2" id="pickteamOTA">
                        <option value=""></option>

                        <?php
                        foreach ($team_all_ids as $key => $value){
                            $printselecta .= '<option value="&AWAY='.$key.'">'.$value['team'].'</option>';
                        }
                        echo $printselecta;
                        ?>
                    </select>
                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="pickplayerQBA">
                        <option value=""></option>

                        <?php
                        foreach ($playersassoc as $key => $selectplayer){
                            $firsto = $selectplayer[0];
                            $lasto = $selectplayer[1];
                            $playerselect_qba .= '<option value="&QBA='.$key.'">'.$firsto.' '.$lasto.'</option>';
                        }
                        echo $playerselect_qba;
                        ?>
                    </select>
                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="pickplayerRBA">
                        <option value=""></option>

                        <?php
                        foreach ($playersassoc as $key => $selectplayer){
                            $firsto = $selectplayer[0];
                            $lasto = $selectplayer[1];
                            $playerselect_rba .= '<option value="&RBA='.$key.'">'.$firsto.' '.$lasto.'</option>';
                        }
                        echo $playerselect_rba;
                        ?>
                    </select>
                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="pickplayerWRA">
                        <option value=""></option>

                        <?php
                        foreach ($playersassoc as $key => $selectplayer){
                            $firsto = $selectplayer[0];
                            $lasto = $selectplayer[1];
                            $playerselect_wra .= '<option value="&WRA='.$key.'">'.$firsto.' '.$lasto.'</option>';
                        }
                        echo $playerselect_wra;
                        ?>
                    </select>
                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="pickplayerPKA">
                        <option value=""></option>

                        <?php
                        foreach ($playersassoc as $key => $selectplayer){
                            $firsto = $selectplayer[0];
                            $lasto = $selectplayer[1];
                            $playerselect_pka .= '<option value="&PKA='.$key.'">'.$firsto.' '.$lasto.'</option>';
                        }
                        echo $playerselect_pka;
                        ?>
                    </select>

                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Road Team</h3>
                            <div class="col-xs-24 col-sm-6">
                                <button class="btn btn-warning" id="teamDropOTH">Select</button>
                            </div>
                            <h4><?php echo $awayscore; ?></h4>
                            <?php printr($insert_qb_a, 0);?>
                            <?php printr($insert_rb_a, 0);?>
                            <?php printr($insert_wr_a, 0);?>
                            <?php printr($insert_pk_a, 0);?>
                        </div>
                    </div>
                </div>
            </div>

        </div><!--End page content-->

    </div><!--END CONTENT CONTAINER-->


<?php get_footer(); ?>