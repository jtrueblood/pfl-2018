<?php
/*
 * Template Name: Build Weekly MFL
 * Description: Extract season player data for individual players from MFL  */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 

/*
get_cache('mfl/thestarters', 0);	
$thestarters = $_SESSION['mfl/thestarters'];
*/
//$year = 2017;
//$week = 1; change this to 1 to 14 and reload page to build all of the week needed starters
$lid = 38954;

$set = $_GET['SET'];
$year = $_GET['Y'];
$week = $_GET['W'];	
$run = $_GET['SQL'];
$touch = $_GET['TOUCH'];
$curl = $_GET['CURL'];

if($set == 0){
	die();
}

if(empty($year)){
	die();
}

$teamdetails = get_teams();
$covertids = playerid_mfl_to_pfl();

//$mflid = $thestarters[$id];

$allteams = array('DST' => $dst, 'PEP' => $pep, 'WRZ' => $wrz, 'ETS' => $ets, 'BST' => $bst, 'HAT' => $hat, 'CMN' => $cmn, 'BUL' => $bul, 'SNR' => $snr, 'TSG' => $tsg);

$mflteamids = array('0005' => 'DST', '0003' => 'PEP', '0004' => 'WRZ', '0002' => 'ETS', '0006' => 'BST', '0008' => 'HAT', '0009' => 'CMN', '0010' => 'BUL', '0007' => 'SNR', '0001' => 'TSG');

$weeks = array('1','2','3','4','5','6','7','8','9','10','11','12','13','14');
$weeks_2dig = array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14');

//$testweek = get_weekly_mfl_player_results(10703, $year, $week);
//printr($testweek, 1);

// get json about player from MFL scores for that player, for each week (reguardless of starter or not) 	

// this pulls all of the 'starters' out of the matchups so we can identify team and if player was a starter that week	

// THIS IS NOW COMMENTED OUT BECAUSE THE TRANSIENTS HAVE BEEN BUILT.  NEXT SEASON UNCOMMENT, CHANGE THE $YEAR and BUILD THE ARRAY EACH WEEK

//$jsonweekresults = file_get_contents('https://www58.myfantasyleague.com/2021/export?TYPE=weeklyResults&L=38954&APIKEY=aRNp1sySvuWux0CmO1HIZDYeF7ox&W=1&JSON=1');

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://www58.myfantasyleague.com/$year/export?TYPE=weeklyResults&L=38954&W=$week&JSON=1",
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
//echo $response;

$weekresults = json_decode($response, true);
$json_store = json_encode($playerstats);

//printr($weekresults, 1);

$destination_folder = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/mfl-weekly-gamelogs';

if (file_exists($destination_folder.'/'.$year.$week.'.json')):
	$report_message = $year.$week.' -- file exsists || ';
	echo '<script>console.log("'.$year.$week.' - file exsists");</script>';
	echo $report_message;
	$get_weekresults = file_get_contents($destination_folder.'/'.$year.$week.'.json');
	$weekresults = json_decode($get_weekresults, true);
	$matchups = $weekresults['weeklyResults'];
	//printr($matchups, 0);
else:
	$curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www58.myfantasyleague.com/$year/export?TYPE=weeklyResults&L=38954&W=$week&JSON=1",
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
	echo $response;

	file_put_contents("$destination_folder/$year$week.json", $response);
	$report_message =  ' -- Added to mfl-gamelogs-- || ';
	echo '<script>console.log("'.$year.$week.'.json - added");</script>';
	echo $report_message;
	$matchups = $weekresults['weeklyResults'];
	//printr($matchups, 0);
	die(); // STOP PAGE LOAD HERE AND RELOAD AGAIN WITH LOCAL JSON DATA
endif;

echo 'starters';

$get1 = rtrim($matchups['matchup'][0]['franchise'][0]['starters'],",");
$get2 = rtrim($matchups['matchup'][0]['franchise'][1]['starters'],",");
$get3 = rtrim($matchups['matchup'][1]['franchise'][0]['starters'],",");
$get4 = rtrim($matchups['matchup'][1]['franchise'][1]['starters'],",");
$get5 = rtrim($matchups['matchup'][2]['franchise'][0]['starters'],",");
$get6 = rtrim($matchups['matchup'][2]['franchise'][1]['starters'],",");
$get7 = rtrim($matchups['matchup'][3]['franchise'][0]['starters'],",");
$get8 = rtrim($matchups['matchup'][3]['franchise'][1]['starters'],",");
$get9 = rtrim($matchups['matchup'][4]['franchise'][0]['starters'],",");
$get10 = rtrim($matchups['matchup'][4]['franchise'][1]['starters'],",");

$starter1 = str_getcsv($get1);
$starter2 = str_getcsv($get2);
$starter3 = str_getcsv($get3);
$starter4 = str_getcsv($get4);
$starter5 = str_getcsv($get5);
$starter6 = str_getcsv($get6);
$starter7 = str_getcsv($get7);
$starter8 = str_getcsv($get8);
$starter9 = str_getcsv($get9);
$starter10 = str_getcsv($get10);

$t1 = $matchups['matchup'][0]['franchise'][0]['id'];
$t2 = $matchups['matchup'][0]['franchise'][1]['id'];
$t3 = $matchups['matchup'][1]['franchise'][0]['id'];
$t4 = $matchups['matchup'][1]['franchise'][1]['id'];
$t5 = $matchups['matchup'][2]['franchise'][0]['id'];
$t6 = $matchups['matchup'][2]['franchise'][1]['id'];
$t7 = $matchups['matchup'][3]['franchise'][0]['id'];
$t8 = $matchups['matchup'][3]['franchise'][1]['id'];
$t9 = $matchups['matchup'][4]['franchise'][0]['id'];
$t10 = $matchups['matchup'][4]['franchise'][1]['id'];

$s1 = $matchups['matchup'][0]['franchise'][0]['score'];
$s2 = $matchups['matchup'][0]['franchise'][1]['score'];
$s3 = $matchups['matchup'][1]['franchise'][0]['score'];
$s4 = $matchups['matchup'][1]['franchise'][1]['score'];
$s5 = $matchups['matchup'][2]['franchise'][0]['score'];
$s6 = $matchups['matchup'][2]['franchise'][1]['score'];
$s7 = $matchups['matchup'][3]['franchise'][0]['score'];
$s8 = $matchups['matchup'][3]['franchise'][1]['score'];
$s9 = $matchups['matchup'][4]['franchise'][0]['score'];
$s10 = $matchups['matchup'][4]['franchise'][1]['score'];

$home1 = $matchups['matchup'][0]['franchise'][0]['isHome'];
$home2 = $matchups['matchup'][0]['franchise'][1]['isHome'];
$home3 = $matchups['matchup'][1]['franchise'][0]['isHome'];
$home4 = $matchups['matchup'][1]['franchise'][1]['isHome'];
$home5 = $matchups['matchup'][2]['franchise'][0]['isHome'];
$home6 = $matchups['matchup'][2]['franchise'][1]['isHome'];
$home7 = $matchups['matchup'][3]['franchise'][0]['isHome'];
$home8 = $matchups['matchup'][3]['franchise'][1]['isHome'];
$home9 = $matchups['matchup'][4]['franchise'][0]['isHome'];
$home10 = $matchups['matchup'][4]['franchise'][1]['isHome'];

$op1 = $matchups['matchup'][0]['franchise'][0]['opt_pts'];
$op2 = $matchups['matchup'][0]['franchise'][1]['opt_pts'];
$op3 = $matchups['matchup'][1]['franchise'][0]['opt_pts'];
$op4 = $matchups['matchup'][1]['franchise'][1]['opt_pts'];
$op5 = $matchups['matchup'][2]['franchise'][0]['opt_pts'];
$op6 = $matchups['matchup'][2]['franchise'][1]['opt_pts'];
$op7 = $matchups['matchup'][3]['franchise'][0]['opt_pts'];
$op8 = $matchups['matchup'][3]['franchise'][1]['opt_pts'];
$op9 = $matchups['matchup'][4]['franchise'][0]['opt_pts'];
$op10 = $matchups['matchup'][4]['franchise'][1]['opt_pts'];

$r1 = $matchups['matchup'][0]['franchise'][0]['result'];
$r2 = $matchups['matchup'][0]['franchise'][1]['result'];
$r3 = $matchups['matchup'][1]['franchise'][0]['result'];
$r4 = $matchups['matchup'][1]['franchise'][1]['result'];
$r5 = $matchups['matchup'][2]['franchise'][0]['result'];
$r6 = $matchups['matchup'][2]['franchise'][1]['result'];
$r7 = $matchups['matchup'][3]['franchise'][0]['result'];
$r8 = $matchups['matchup'][3]['franchise'][1]['result'];
$r9 = $matchups['matchup'][4]['franchise'][0]['result'];
$r10 = $matchups['matchup'][4]['franchise'][1]['result'];

$weekstarters = array(
	$mflteamids[$t1] => array( 
		'starters' => array (
			'QB' => array( $starter1[0], get_weekly_mfl_player_results($starter1[0], $year, $week)),
			'RB' => array( $starter1[1], get_weekly_mfl_player_results($starter1[1], $year, $week)),
			'WR' => array( $starter1[2], get_weekly_mfl_player_results($starter1[2], $year, $week)),
			'PK' => array( $starter1[3], get_weekly_mfl_player_results($starter1[3], $year, $week))
		), 
		'team' => $mflteamids[$t1], 
		'team_score' => $s1, 
		'versus' => $mflteamids[$t2], 
		'vs_score' => $s2, 
		'result' => $r1, 
		'isHome' => $home1
	),
	$mflteamids[$t2] => array( 
		'starters' => array (
			'QB' => array( $starter2[0], get_weekly_mfl_player_results($starter2[0], $year, $week)),
			'RB' => array( $starter2[1], get_weekly_mfl_player_results($starter2[1], $year, $week)),
			'WR' => array( $starter2[2], get_weekly_mfl_player_results($starter2[2], $year, $week)),
			'PK' => array( $starter2[3], get_weekly_mfl_player_results($starter2[3], $year, $week))
		), 
		'team' => $mflteamids[$t2], 
		'team_score' => $s2, 
		'versus' => $mflteamids[$t1], 
		'vs_score' => $s1, 
		'result' => $r2, 
		'isHome' => $home2
	),
	$mflteamids[$t3] => array( 
		'starters' => array (
			'QB' => array( $starter3[0], get_weekly_mfl_player_results($starter3[0], $year, $week)),
			'RB' => array( $starter3[1], get_weekly_mfl_player_results($starter3[1], $year, $week)),
			'WR' => array( $starter3[2], get_weekly_mfl_player_results($starter3[2], $year, $week)),
			'PK' => array( $starter3[3], get_weekly_mfl_player_results($starter3[3], $year, $week))
		), 
		'team' => $mflteamids[$t3], 
		'team_score' => $s3, 
		'versus' => $mflteamids[$t4], 
		'vs_score' => $s4, 
		'result' => $r3, 
		'isHome' => $home3
	),
	$mflteamids[$t4] => array( 
		'starters' => array (
			'QB' => array( $starter4[0], get_weekly_mfl_player_results($starter4[0], $year, $week)),
			'RB' => array( $starter4[1], get_weekly_mfl_player_results($starter4[1], $year, $week)),
			'WR' => array( $starter4[2], get_weekly_mfl_player_results($starter4[2], $year, $week)),
			'PK' => array( $starter4[3], get_weekly_mfl_player_results($starter4[3], $year, $week))
		), 
		'team' => $mflteamids[$t4], 
		'team_score' => $s4, 
		'versus' => $mflteamids[$t3], 
		'vs_score' => $s3, 
		'result' => $r4, 
		'isHome' => $home4
	),
	$mflteamids[$t5] => array( 
		'starters' => array (
			'QB' => array( $starter5[0], get_weekly_mfl_player_results($starter5[0], $year, $week)),
			'RB' => array( $starter5[1], get_weekly_mfl_player_results($starter5[1], $year, $week)),
			'WR' => array( $starter5[2], get_weekly_mfl_player_results($starter5[2], $year, $week)),
			'PK' => array( $starter5[3], get_weekly_mfl_player_results($starter5[3], $year, $week))
		), 
		'team' => $mflteamids[$t5], 
		'team_score' => $s5, 
		'versus' => $mflteamids[$t6], 
		'vs_score' => $s6, 
		'result' => $r5, 
		'isHome' => $home5
	),
	$mflteamids[$t6] => array( 
		'starters' => array (
			'QB' => array( $starter6[0], get_weekly_mfl_player_results($starter6[0], $year, $week)),
			'RB' => array( $starter6[1], get_weekly_mfl_player_results($starter6[1], $year, $week)),
			'WR' => array( $starter6[2], get_weekly_mfl_player_results($starter6[2], $year, $week)),
			'PK' => array( $starter6[3], get_weekly_mfl_player_results($starter6[3], $year, $week))
		), 
		'team' => $mflteamids[$t6], 
		'team_score' => $s6, 
		'versus' => $mflteamids[$t5], 
		'vs_score' => $s5, 
		'result' => $r6, 
		'isHome' => $home6
	),
	$mflteamids[$t7] => array( 
		'starters' => array (
			'QB' => array( $starter7[0], get_weekly_mfl_player_results($starter7[0], $year, $week)),
			'RB' => array( $starter7[1], get_weekly_mfl_player_results($starter7[1], $year, $week)),
			'WR' => array( $starter7[2], get_weekly_mfl_player_results($starter7[2], $year, $week)),
			'PK' => array( $starter7[3], get_weekly_mfl_player_results($starter7[3], $year, $week))
		), 
		'team' => $mflteamids[$t7], 
		'team_score' => $s7, 
		'versus' => $mflteamids[$t8], 
		'vs_score' => $s8, 
		'result' => $r7, 
		'isHome' => $home7
	),
	$mflteamids[$t8] => array( 
		'starters' => array (
			'QB' => array( $starter8[0], get_weekly_mfl_player_results($starter8[0], $year, $week)),
			'RB' => array( $starter8[1], get_weekly_mfl_player_results($starter8[1], $year, $week)),
			'WR' => array( $starter8[2], get_weekly_mfl_player_results($starter8[2], $year, $week)),
			'PK' => array( $starter8[3], get_weekly_mfl_player_results($starter8[3], $year, $week))
		), 
		'team' => $mflteamids[$t8], 
		'team_score' => $s8, 
		'versus' => $mflteamids[$t7], 
		'vs_score' => $s7, 
		'result' => $r8, 
		'isHome' => $home8
	),
	$mflteamids[$t9] => array( 
		'starters' => array (
			'QB' => array( $starter9[0], get_weekly_mfl_player_results($starter9[0], $year, $week)),
			'RB' => array( $starter9[1], get_weekly_mfl_player_results($starter9[1], $year, $week)),
			'WR' => array( $starter9[2], get_weekly_mfl_player_results($starter9[2], $year, $week)),
			'PK' => array( $starter9[3], get_weekly_mfl_player_results($starter9[3], $year, $week))
		), 
		'team' => $mflteamids[$t9], 
		'team_score' => $s9, 
		'versus' => $mflteamids[$t10], 
		'vs_score' => $s10, 
		'result' => $r9, 
		'isHome' => $home9
	),
	$mflteamids[$t10] => array( 
		'starters' => array (
			'QB' => array( $starter10[0], get_weekly_mfl_player_results($starter10[0], $year, $week)),
			'RB' => array( $starter10[1], get_weekly_mfl_player_results($starter10[1], $year, $week)),
			'WR' => array( $starter10[2], get_weekly_mfl_player_results($starter10[2], $year, $week)),
			'PK' => array( $starter10[3], get_weekly_mfl_player_results($starter10[3], $year, $week))
		), 
		'team' => $mflteamids[$t10], 
		'team_score' => $s10, 
		'versus' => $mflteamids[$t9], 
		'vs_score' => $s9, 
		'result' => $r10, 
		'isHome' => $home10
	)
);

//printr($weekstarters, 0);

// build array to insert results into team data for regular season

$weekform = sprintf('%02d', $week);

// get values of players and teams from .json file and organize as an array for database insert
if($week <= 14):
    foreach ($weekstarters as $key => $value){
	
	if($value['isHome'] == 1){
		$homeaway = 'H';
		$stadium = $teamdetails[$key]['stadium'];
	} else {
		$homeaway = 'A';
		$stadium = $teamdetails[$value['versus']]['stadium'];
	}
	
	if($value['team_score'] == $value['versus_score']){
		$isot = 1;
	}
	
	$result = $value['team_score'] - $value['vs_score'];
	
	if($result > 0){
		$winloss = 1;
	}
	if($result < 0){
		$winloss = 0;
	}
	
	$iQB = $covertids[$value['starters']['QB'][0]];
	$iRB = $covertids[$value['starters']['RB'][0]];
	$iWR = $covertids[$value['starters']['WR'][0]];
	$iPK = $covertids[$value['starters']['PK'][0]];
	
	$insert_team[$key] = array(
		'id' 		=> $year.$weekform,
		'season'	=> $year,
		'week'		=> $week,
		'team_int'	=> $key,
		'points'	=> $value['team_score'],
		'vs'		=> $value['versus'],
		'vs_points'	=> $value['vs_score'],
		'home_away'	=> $homeaway,
		'stadium'	=> $stadium,
		'result'	=> $result,
		'QB1'		=> $iQB,
		'RB1'		=> $iRB,
		'WR1'		=> $iWR,
		'PK1'		=> $iPK,
		'overtime'	=> $isot,
		'QB2'		=> '',
		'RB2'		=> '',
		'WR2'		=> '',
		'PK2'		=> '',
		'extra_ot'	=> ''
	);
	
	$startersteam = $value['starters'];
	foreach ($startersteam as $k => $v){
		$pid = $covertids[$v[0]];
		
		$insert_player[$pid] = array(
			'week_id' 	=> $year.$weekform,
			'year'		=> $year,
			'week'		=> $week,
			'points'	=> $v[1][$week],
			'team'		=> $key,
			'versus'	=> $value['versus'],
			'playerid'	=> $pid,
			'win_loss'	=> $winloss,
			'home_away'	=> $homeaway,
			'location'	=> $stadium
		);
	}
}
endif;

// get for week 15 - playoffs
// You must finalize the yearly standings table before you run the playoff script for week 15 or 16.
// Otherwise the seeding will not be accessible.
if($week >= 15):
    echo '<h2>IS PLAYOFFS - WEEK 15</h2>';

    foreach ($weekstarters as $key => $value){

        if($value['isHome'] == 1){
            $homeaway = 'H';
            $stadium = $teamdetails[$key]['stadium'];
        } else {
            $homeaway = 'A';
            $stadium = $teamdetails[$value['versus']]['stadium'];
        }

        if($value['team_score'] == $value['versus_score']){
            $isot = 1;
        }

        $result = $value['team_score'] - $value['vs_score'];

        if($result > 0){
            $winloss = 1;
        }
        if($result < 0){
            $winloss = 0;
        }

        $iQB = $covertids[$value['starters']['QB'][0]];
        $iRB = $covertids[$value['starters']['RB'][0]];
        $iWR = $covertids[$value['starters']['WR'][0]];
        $iPK = $covertids[$value['starters']['PK'][0]];

        $standings = get_standings($year);
        foreach ($standings as $kstand => $valuestand):
            $teamseed[$valuestand['teamid']] = $valuestand['seed'];
        endforeach;

        $startersteam = $value['starters'];
        foreach ($startersteam as $k => $v){
            $pid = $covertids[$v[0]];
            $plv = 0;
            $sdv = $teamseed[$key];
            $pos = substr($pid, 0 -2);

            if ($pos == 'QB'):
                $psv = 1;
            endif;
            if ($pos == 'RB'):
                $psv = 2;
            endif;
            if ($pos == 'WR'):
                $psv = 3;
            endif;
            if ($pos == 'PK'):
                $psv = 4;
            endif;

            $resultcalc = ( $value['result'] == 'W' ? 1 : 0);

            $otv = 0;

            $insert_player[$pid] = array(
                'id'     	=> $year.$plv.$sdv.$psv.$otv,
                'year'		=> $year,
                'week'		=> $week,
                'playerid'	=> $pid,
                'points'	=> $v[1][$week],
                'team'		=> $key,
                'versus'	=> $value['versus'],
                'overtime'	=> $otv,
                'result'	=> $resultcalc,
                'seed'      => $sdv
            );
        }
    }
endif;

//array_pop($insert_player);

// resort week data indexed by player id

// INSERT FORMATTED DATA INTO ALL TEAM TABLES
global $wpdb;

// Regular season insert statements array and insert
if($run == 'true' && $week <= 14){
	foreach ($insert_team as $key => $insert){
		$wpdb->insert(
			'wp_team_'.$key,
			array(
				'id' 		=> $insert['id'],
				'season' 	=> $insert['season'],
				'week' 		=> $insert['week'],
				'team_int' 	=> $insert['team_int'],
				'points' 	=> $insert['points'],
				'vs' 		=> $insert['vs'],
				'vs_points' => $insert['vs_points'],
				'home_away' => $insert['home_away'],
				'stadium' 	=> $insert['stadium'],
				'result' 	=> $insert['result'],
				'QB1' 		=> $insert['QB1'],
				'RB1' 		=> $insert['RB1'],
				'WR1' 		=> $insert['WR1'],
				'PK1' 		=> $insert['PK1'],
				'overtime' 	=> $insert['overtime'],
				'QB2'		=> '',
				'RB2'		=> '',
				'WR2'		=> '',
				'PK2'		=> '',
				'extra_ot'	=> ''
			),
			array(
				'%d','%d','%d','%s','%d','%s','%d','%s','%s','%d','%s','%s','%s','%s','%d','%s','%s','%s','%s','%d'
			)
		);
	}

	echo '<h3>TEAMS INSERTED</h3>';
	
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

	echo '<h3>PLAYERS INSERTED</h3>';
	
}

// Postseason - Week 15 - insert statements array and insert
if($run == 'true' && $week >= 15){

    foreach ($insert_player as $key => $playoffs){
        $wpdb->insert(
            'wp_playoffs',
            array(
                'id' 	    => $playoffs['id'],
                'year'		=> $playoffs['year'],
                'week'		=> $playoffs['week'],
                'playerid'	=> $playoffs['playerid'],
                'points'	=> $playoffs['points'],
                'team'	    => $playoffs['team'],
                'versus'	=> $playoffs['versus'],
                'overtime'	=> $playoffs['overtime'],
                'result'	=> $playoffs['result']
            ),
            array (
                '%d','%d','%d','%s','%d','%s','%s','%d','%d'
            )
        );
    }

    echo '<h3>PLAYOFFS INSERTED</h3>';

}

//INSERT FORMATTED PLAYER DATA INTO TABLES
// check if all players have a table....
	
?>
<!--CONTENT CONTAINER-->
<div class="boxed">
	<div id="content-container">
		<div id="page-content">
			<div class="row">
				<div class="col-sm-8">
					<div class="panel">
						<div class="panel-body">
							<ol>
                                <li>At the beginning of the season.  Check to make sure weeklyReults is pulling data.  API key may nahe changed from last season and would need to be reset in MFL.  Also check if year values in GET url are correct.</li>
								<li>Set Year and Week Values and load the page once.  This will get the MFL Matchup data, store it locally as json, then die the page. </li>
								<li>Reload the page.  Check if any players are not found.  If so create player from MFL using 'Create New Player' widget on Homepage.</li> 
								<li>Once all player tables are found or created. Change SQL to true to insert into database.</li>
								<li>Once inserted change SQL back to false and set url CURL value to true to load player pages for all players who played this week and refresh leaders data.</li>
                                <li>If there is an OVERTIME game, you will need to manually insert that data.  First figure out OT rosters and scores.  Then break the tie(s) and add one point to the winner on the MFL site.  Then add records to the wp_overtime table.  Add OT player ids to the team tables (ex. wp_team_WRZ).  Then add a line record for the game to the individual player tables.</li>
                                <li>As of 2022 -- This script also works for returning Playoffs and Possebowl player scores and results.  Just pass the week 15 or 16 var into the url.</li>
                            </ol>
						</div>
					</div>
				</div>
			</div>
		
			<div class="row">

				<div class="col-sm-8">
					<?php 
						if($run == 'false'){
							echo '<pre><h3>NO DATA INSERTED</h3></pre>';
						}
						
						foreach ($insert_player as $key => $value){
						$query = $wpdb->get_results("select * from $key", ARRAY_N);
						//var_dump($query);
						if(empty($query)){
							echo 'TABLE MISSING --- '.$key.'<br>';
						} 
						
						if(!empty($query)){                 
						 	echo 'Table Found - <a href="/player/?id='.$key.'" target="_blank">'.$key.'</a><br>';
						 	$storetoload[] = $key;
						}
					} 

					?>
				
				</div>
				
				<div class="col-sm-8">
					<h4 class="mar-no">Data Inserted Into Teams</h4>
					<?php printr($insert_team, 0); ?>
				
				</div>
					<!--===================================================-->
				
				
				<div class="col-sm-8">						
					<h4 class="mar-no"><?php echo $year;?> Data Inserted into Players</h4>
					<?php printr($insert_player, 0); ?>
					
				</div>
				
				<div class="col-sm-2">	
					
					
				</div>
			

			</div>
			
			<?php

			// SUNDOWN THIS WITH TOUCH -- set url CURL value to true to load player pages for all players who played this week and refresh leaders data


            function touchUrl($url) {
                $ch = curl_init($url);

                // Set cURL options
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Set timeout in seconds

                // Execute the request
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                // Check for errors or success based on HTTP status code
                if ($httpCode >= 200 && $httpCode < 300) {
                    // Successful request
                    return "Successful: HTTP Code - $httpCode";
                } else {
                    // Unsuccessful request
                    return "Unsuccessful: HTTP Code - $httpCode";
                }

                // Close cURL session
                curl_close($ch);
            }
            touchUrl('http://pfl-data.local/player/?id=2017EkelRB');
            if($touch == 'true'):

//                foreach($storetoload as $pid):
//                endforeach;
            endif;

			?>
			
		
		</div>
	</div>
</div>


<?php get_footer(); ?>