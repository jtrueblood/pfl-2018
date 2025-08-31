<?php
/*
 * Template Name: Build Postseason Player Scores
 * Description: Get player scores from MFL for weeks 15 and 16 to insert into wp_playoffs
 */
 ?>

<?php get_header(); ?>

<?php
$year = $_GET['Y'];
$week = $_GET['W'];
$weekform = sprintf('%02d', $week);
$weekid = $year.$weekform;
$run = $_GET['SQL'];
$oneseed = $_GET['S1'];
$twoseed = $_GET['S2'];
$threeseed = $_GET['S3'];
$fourseed = $_GET['S4'];

$mflteams = teams_for_mfl_history(); // make sure to update the teams list array in this function each season
$getyearteams = $mflteams[$year];

foreach ($getyearteams as $key => $value):
    $string = strval($key);
    $flipkeys[$value] = $string;
endforeach;

//printr($flipkeys, 0);
$playoffteams = array(
    1 => $oneseed,
    2 => $twoseed,
    3 => $threeseed,
    4 => $fourseed
);
$flipseed = array_flip($playoffteams);

function get_score_for_playoffs($year, $week, $teams){
    $weekform = sprintf('%02d', $week);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www48.myfantasyleague.com/$year/export?TYPE=weeklyResults&L=38954&APIKEY=aRNp1sySvuWsx0amO1HIZDYeFbox&W=$week&MISSING_AS_BYE=&JSON=1",
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

    $file =  $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/mfl-weekly-gamelogs/202415.json';

    $my = json_decode($file, false);
    $franchise = $my['weeklyResults']['matchup'];
    var_dump($franchise);

    if($week == 15):
        $teama = $franchise[0]['franchise'][0];
        $teamb = $franchise[0]['franchise'][1];
        $teamc = $franchise[1]['franchise'][0];
        $teamd = $franchise[1]['franchise'][1];

        $newArray = array(
            $teama['id'] => $teama,
            $teamb['id'] => $teamb,
            $teamc['id'] => $teamc,
            $teamd['id'] => $teamd
        );
    endif;

    if($week == 16):
        $teama = $franchise['franchise'][0];
        $teamb = $franchise['franchise'][1];

        $newArray = array(
            $teama['id'] => $teama,
            $teamb['id'] => $teamb
        );
    endif;

    //return $franchise;
    return $newArray[$teams];
}

function set_pos_num($position){
    if($position == 'QB'):
        $pos = 1;
    else :
        if($position == 'RB'):
            $pos = 2;
        else:
            if($position == 'WR'):
                $pos = 3;
            else:
                $pos = 4;
            endif;
        endif;
    endif;
    return $pos;
}

if($week == 15):
    $oneseed_mfl_data = get_score_for_playoffs($year, $week, $flipkeys[$oneseed]);
    $twoseed_mfl_data = get_score_for_playoffs($year, $week, $flipkeys[$twoseed]);
    $threeseed_mfl_data = get_score_for_playoffs($year, $week, $flipkeys[$threeseed]);
    $fourseed_mfl_data = get_score_for_playoffs($year, $week, $flipkeys[$fourseed]);

    if($oneseed_mfl_data['player']):
    foreach ($oneseed_mfl_data['player'] as $key => $value):
        if($value['status'] == 'starter'):
            $one_starters[one_player_mfl_to_pfl($value['id'])] = $value['score'];
        endif;
    endforeach;
    endif;
    $one_result = $oneseed_mfl_data['result'];

    if($twoseed_mfl_data['player']):
        foreach ($twoseed_mfl_data['player'] as $key => $value):
            if($value['status'] == 'starter'):
                $two_starters[one_player_mfl_to_pfl($value['id'])] = $value['score'];
            endif;
        endforeach;
    endif;
    $two_result = $twoseed_mfl_data['result'];

    if($threeseed_mfl_data['player']):
        foreach ($threeseed_mfl_data['player'] as $key => $value):
            if($value['status'] == 'starter'):
                $three_starters[one_player_mfl_to_pfl($value['id'])] = $value['score'];
            endif;
        endforeach;
    endif;
    $three_result = $threeseed_mfl_data['result'];

    if($fourseed_mfl_data['player']):
        foreach ($fourseed_mfl_data['player'] as $key => $value):
            if($value['status'] == 'starter'):
                $four_starters[one_player_mfl_to_pfl($value['id'])] = $value['score'];
            endif;
        endforeach;
    endif;
    $four_result = $fourseed_mfl_data['result'];

    // year playoffs seed position ot
    // 2020 0 1 1 0
    if($one_result == 'W' ? $one_r = 1 : $one_r = 0);
    if($one_starters):
        foreach ($one_starters as $key => $value):
            $position  = substr($key, strlen($key)-2);
            $p = set_pos_num($position);
            $one_seed[] = array(
                'id' => $year.'01'.$p.'0',
                'year' => $year,
                'week' => $week,
                'playerid' => $key,
                'points' => $value,
                'team' => $oneseed,
                'versus' => $fourseed,
                'overtime' => 0,
                'result' => $one_r
            );
        endforeach;
    endif;

    if($two_result == 'W' ? $two_r = 1 : $two_r = 0);
    if($two_starters):
        foreach ($two_starters as $key => $value):
            $position  = substr($key, strlen($key)-2);
            $p = set_pos_num($position);
            $two_seed[] = array(
                'id' => $year.'02'.$p.'0',
                'year' => $year,
                'week' => $week,
                'playerid' => $key,
                'points' => $value,
                'team' => $twoseed,
                'versus' => $threeseed,
                'overtime' => 0,
                'result' => $two_r
            );
        endforeach;
    endif;

    if($three_result == 'W' ? $three_r = 1 : $three_r = 0);
    if($three_starters):
        foreach ($three_starters as $key => $value):
            $position  = substr($key, strlen($key)-2);
            $p = set_pos_num($position);
            $three_seed[] = array(
                'id' => $year.'03'.$p.'0',
                'year' => $year,
                'week' => $week,
                'playerid' => $key,
                'points' => $value,
                'team' => $threeseed,
                'versus' => $twoseed,
                'overtime' => 0,
                'result' => $three_r
            );
        endforeach;
    endif;

    if($four_result == 'W' ? $four_r = 1 : $four_r = 0);
    if($four_starters):
        foreach ($four_starters as $key => $value):
            $position  = substr($key, strlen($key)-2);
            $p = set_pos_num($position);
            $four_seed[] = array(
                'id' => $year.'04'.$p.'0',
                'year' => $year,
                'week' => $week,
                'playerid' => $key,
                'points' => $value,
                'team' => $fourseed,
                'versus' => $oneseed,
                'overtime' => 0,
                'result' => $four_r
            );
        endforeach;
    endif;
    if($one_seed):
        $insert = array_merge($one_seed, $two_seed, $three_seed, $four_seed);
    endif;
endif;

if($week == 16):

    // Get the data from the previous week to use for determining the seeds of the winning teams
    global $wpdb;
    $all_playoff_results = $wpdb->get_results("select * from wp_playoffs", ARRAY_N);
    foreach ($all_playoff_results as $key => $value):
        if($value[1] == $year):
            if($value[2] == 15):
                if($value[8] == 1):
                    $playoff_winners[] = $value[5];
                endif;
            endif;
        endif;
    endforeach;

    $weekplayoffwinners = array_unique($playoff_winners);
    foreach($weekplayoffwinners as $key => $value):
        $winningseeds[$value] = $flipseed[$value];
    endforeach;

    $teama_pb_mfl_data = get_score_for_playoffs($year, $week, $flipkeys[$oneseed]);
    $teamb_pb_mfl_data = get_score_for_playoffs($year, $week, $flipkeys[$twoseed]);

    $team_a_pb = $teama_pb_mfl_data['id'];
    $score_a_pb = $teama_pb_mfl_data['score'];
    $result_a_pb = $teama_pb_mfl_data['result'];
    $players_a_pb = $teama_pb_mfl_data['player'];

    foreach ($players_a_pb as $key => $value):
        if($value['status'] == 'starter'):
            $team_a_pb_starters[one_player_mfl_to_pfl($value['id'])] = $value['score'];
        endif;
    endforeach;

    $team_b_pb = $teamb_pb_mfl_data['id'];
    $score_b_pb = $teamb_pb_mfl_data['score'];
    $result_b_pb = $teamb_pb_mfl_data['result'];
    $players_b_pb = $teamb_pb_mfl_data['player'];

    foreach ($players_b_pb as $key => $value):
        if($value['status'] == 'starter'):
            $team_b_pb_starters[one_player_mfl_to_pfl($value['id'])] = $value['score'];
        endif;
    endforeach;

    if($result_a_pb == 'W' ? $team_a_r = 1 : $team_a_r = 0);
    if($team_a_pb_starters):
        foreach ($team_a_pb_starters as $key => $value):
            $pb_team_a_seed = $winningseeds[$getyearteams[$team_a_pb]];
            $position  = substr($key, strlen($key)-2);
            $p = set_pos_num($position);
            $team_a_pb_formatted[] = array(
                'id' => $year.'1'.$pb_team_a_seed.$p.'0',
                'year' => $year,
                'week' => $week,
                'playerid' => $key,
                'points' => $value,
                'team' => $getyearteams[$team_a_pb],
                'versus' => $getyearteams[$team_b_pb],
                'overtime' => 0,
                'result' => $team_a_r
            );
        endforeach;
    endif;

    if($result_b_pb == 'W' ? $team_b_r = 1 : $team_b_r = 0);
    if($team_b_pb_starters):
        foreach ($team_b_pb_starters as $key => $value):
            $pb_team_b_seed = $winningseeds[$getyearteams[$team_b_pb]];
            $position  = substr($key, strlen($key)-2);
            $p = set_pos_num($position);
            $team_b_pb_formatted[] = array(
                'id' => $year.'1'.$pb_team_b_seed.$p.'0',
                'year' => $year,
                'week' => $week,
                'playerid' => $key,
                'points' => $value,
                'team' => $getyearteams[$team_b_pb],
                'versus' => $getyearteams[$team_a_pb],
                'overtime' => 0,
                'result' => $team_b_r
            );
        endforeach;
    endif;
    if($team_a_pb_formatted):
        $insert = array_merge($team_a_pb_formatted, $team_b_pb_formatted);
    endif;
endif;


//printr($winningseeds, 0);
//printr($insert, 1);
//printr($team_b_pb_formatted, 0);
//printr($team_a_pb_starters, 0);
//printr($mflteams, 0);
//printr($three_seed, 0);
//printr($four_seed, 0);
//echo $teama_pb.'<br>';
//echo $teamb_pb.'<br>';

if($run == 1):
    // insert into wp_playoffs table
    foreach($insert as $key => $value):
    $wpdb->insert(
        'wp_playoffs',
        array(
            'id' => $value['id'],
            'year' => $value['year'],
            'week' => $value['week'],
            'playerid' => $value['playerid'],
            'points' => $value['points'],
            'team' => $value['team'],
            'versus' => $value['versus'],
            'overtime' => $value['overtime'],
            'result' => $value['result']
        )
    );
    endforeach;
endif;



 ?>

<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <div id="page-content">
                <div class="col-xs-8">
                        <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title">Get Playoff Scores from MFL (Week 15 & 16)</h3>
                                <p>Intructions:</p>
                                <p>First make sure the teams_for_mfl_history function is updated in functions.php to include the list of teams and their MFL IDs.</p>
                                <p>Week 15:  Enter year and week value and the four team IDs of the playoff teams by seed in the URL.  Keep SQL = 0.  Reload.  If everything looks good change SQL=1 and reload again to insert data into wp_playoffs table.</p>
                                <p>Week 16:  Change W=16.  Keep all teams in the same seed positions.  Follow same steps as above.</p>
                                <?php echo 'Week:'.$week.' - '.$year.'<br>'; ?>
                                <?php echo $oneseed.' - '.$fourseed.'<br>'; ?>
                                <?php echo $twoseed.' - '.$threeseed.'<br>';?>
                                <?php printr($insert, 0);?>
                            </div>
                        </div>
                </div>
            </div>

        </div><!--End page content-->

</div><!--END CONTENT CONTAINER-->


<?php include_once('main-nav.php'); ?>
<?php include_once('aside.php'); ?>

    </div>
    </div>

<?php session_destroy(); ?>

    </div>
    </div>




<?php get_footer(); ?>