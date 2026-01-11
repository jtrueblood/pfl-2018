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
$weeks_2dig_asso = array('0' => '00','1' => '01','2' => '02','3' => '03','4' => '04','5' => '05','6' => '06','7' => '07','8' => '08','9' => '09','10' => '10','11' => '11','12' => '12','13' => '13','14' => '14','15' => '15','16' => '16','17' => '17');

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
	$raw_data = $weekresults['weeklyResults'];
	
	// Week 16 (Posse Bowl) has different JSON structure - single matchup with 2 teams
	if($week == 16 && isset($raw_data['matchup']['franchise'])):
		// Restructure week 16 data to match week 15 format
		$matchups = array(
			'matchup' => array($raw_data['matchup'])
		);
	else:
		$matchups = $raw_data;
	endif;
	//printr($matchups, 0);
else:
	$curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www58.myfantasyleague.com/$year/export?TYPE=weeklyResults&L=38954&W=$week&JSON=1&APIKEY=aRNp1sySvuWqx0CmO1HIZDYeFbox",
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

// Week 16 (Posse Bowl) only has 1 matchup with 2 teams
if($week == 16):
	$get1 = rtrim($matchups['matchup'][0]['franchise'][0]['starters'],",");
	$get2 = rtrim($matchups['matchup'][0]['franchise'][1]['starters'],",");
	$get3 = $get4 = $get5 = $get6 = $get7 = $get8 = $get9 = $get10 = '';
else:
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
endif;

$starter1 = !empty($get1) ? str_getcsv($get1) : array();
$starter2 = !empty($get2) ? str_getcsv($get2) : array();
$starter3 = !empty($get3) ? str_getcsv($get3) : array();
$starter4 = !empty($get4) ? str_getcsv($get4) : array();
$starter5 = !empty($get5) ? str_getcsv($get5) : array();
$starter6 = !empty($get6) ? str_getcsv($get6) : array();
$starter7 = !empty($get7) ? str_getcsv($get7) : array();
$starter8 = !empty($get8) ? str_getcsv($get8) : array();
$starter9 = !empty($get9) ? str_getcsv($get9) : array();
$starter10 = !empty($get10) ? str_getcsv($get10) : array();

$t1 = $matchups['matchup'][0]['franchise'][0]['id'];
$t2 = $matchups['matchup'][0]['franchise'][1]['id'];
$t3 = ($week == 16) ? null : $matchups['matchup'][1]['franchise'][0]['id'];
$t4 = ($week == 16) ? null : $matchups['matchup'][1]['franchise'][1]['id'];
$t5 = ($week == 16) ? null : $matchups['matchup'][2]['franchise'][0]['id'];
$t6 = ($week == 16) ? null : $matchups['matchup'][2]['franchise'][1]['id'];
$t7 = ($week == 16) ? null : $matchups['matchup'][3]['franchise'][0]['id'];
$t8 = ($week == 16) ? null : $matchups['matchup'][3]['franchise'][1]['id'];
$t9 = ($week == 16) ? null : $matchups['matchup'][4]['franchise'][0]['id'];
$t10 = ($week == 16) ? null : $matchups['matchup'][4]['franchise'][1]['id'];

$s1 = $matchups['matchup'][0]['franchise'][0]['score'];
$s2 = $matchups['matchup'][0]['franchise'][1]['score'];
$s3 = ($week == 16) ? null : $matchups['matchup'][1]['franchise'][0]['score'];
$s4 = ($week == 16) ? null : $matchups['matchup'][1]['franchise'][1]['score'];
$s5 = ($week == 16) ? null : $matchups['matchup'][2]['franchise'][0]['score'];
$s6 = ($week == 16) ? null : $matchups['matchup'][2]['franchise'][1]['score'];
$s7 = ($week == 16) ? null : $matchups['matchup'][3]['franchise'][0]['score'];
$s8 = ($week == 16) ? null : $matchups['matchup'][3]['franchise'][1]['score'];
$s9 = ($week == 16) ? null : $matchups['matchup'][4]['franchise'][0]['score'];
$s10 = ($week == 16) ? null : $matchups['matchup'][4]['franchise'][1]['score'];

$home1 = $matchups['matchup'][0]['franchise'][0]['isHome'];
$home2 = $matchups['matchup'][0]['franchise'][1]['isHome'];
$home3 = ($week == 16) ? null : $matchups['matchup'][1]['franchise'][0]['isHome'];
$home4 = ($week == 16) ? null : $matchups['matchup'][1]['franchise'][1]['isHome'];
$home5 = ($week == 16) ? null : $matchups['matchup'][2]['franchise'][0]['isHome'];
$home6 = ($week == 16) ? null : $matchups['matchup'][2]['franchise'][1]['isHome'];
$home7 = ($week == 16) ? null : $matchups['matchup'][3]['franchise'][0]['isHome'];
$home8 = ($week == 16) ? null : $matchups['matchup'][3]['franchise'][1]['isHome'];
$home9 = ($week == 16) ? null : $matchups['matchup'][4]['franchise'][0]['isHome'];
$home10 = ($week == 16) ? null : $matchups['matchup'][4]['franchise'][1]['isHome'];

$op1 = $matchups['matchup'][0]['franchise'][0]['opt_pts'];
$op2 = $matchups['matchup'][0]['franchise'][1]['opt_pts'];
$op3 = ($week == 16) ? null : $matchups['matchup'][1]['franchise'][0]['opt_pts'];
$op4 = ($week == 16) ? null : $matchups['matchup'][1]['franchise'][1]['opt_pts'];
$op5 = ($week == 16) ? null : $matchups['matchup'][2]['franchise'][0]['opt_pts'];
$op6 = ($week == 16) ? null : $matchups['matchup'][2]['franchise'][1]['opt_pts'];
$op7 = ($week == 16) ? null : $matchups['matchup'][3]['franchise'][0]['opt_pts'];
$op8 = ($week == 16) ? null : $matchups['matchup'][3]['franchise'][1]['opt_pts'];
$op9 = ($week == 16) ? null : $matchups['matchup'][4]['franchise'][0]['opt_pts'];
$op10 = ($week == 16) ? null : $matchups['matchup'][4]['franchise'][1]['opt_pts'];

$r1 = $matchups['matchup'][0]['franchise'][0]['result'];
$r2 = $matchups['matchup'][0]['franchise'][1]['result'];
$r3 = ($week == 16) ? null : $matchups['matchup'][1]['franchise'][0]['result'];
$r4 = ($week == 16) ? null : $matchups['matchup'][1]['franchise'][1]['result'];
$r5 = ($week == 16) ? null : $matchups['matchup'][2]['franchise'][0]['result'];
$r6 = ($week == 16) ? null : $matchups['matchup'][2]['franchise'][1]['result'];
$r7 = ($week == 16) ? null : $matchups['matchup'][3]['franchise'][0]['result'];
$r8 = ($week == 16) ? null : $matchups['matchup'][3]['franchise'][1]['result'];
$r9 = ($week == 16) ? null : $matchups['matchup'][4]['franchise'][0]['result'];
$r10 = ($week == 16) ? null : $matchups['matchup'][4]['franchise'][1]['result'];

// Build weekstarters array - only 2 teams for week 16, all 10 for other weeks
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
	)
);

// Add remaining 8 teams only if NOT week 16
if($week != 16):
	$weekstarters[$mflteamids[$t3]] = array( 
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
	);
	
	$weekstarters[$mflteamids[$t4]] = array( 
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
	);
	
	$weekstarters[$mflteamids[$t5]] = array( 
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
	);
	
	$weekstarters[$mflteamids[$t6]] = array( 
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
	);
	
	$weekstarters[$mflteamids[$t7]] = array( 
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
	);
	
	$weekstarters[$mflteamids[$t8]] = array( 
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
	);
	
	$weekstarters[$mflteamids[$t9]] = array( 
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
	);
	
	$weekstarters[$mflteamids[$t10]] = array( 
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
	);
endif;

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
    echo '<h2>IS PLAYOFFS - WEEK '.$week.'</h2>';
    echo '<p>Processing '.count($weekstarters).' teams for week '.$week.'</p>';

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
            $plv = ($week == 15) ? 0 : 1;  // 0 for semifinals (week 15), 1 for Posse Bowl (week 16)
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
        sleep(1);
	}

	echo '<h3>PLAYERS INSERTED</h3>';
	
}

// Postseason - Week 15 - insert statements array and insert
if($run == 'true' && $week >= 15){
    echo '<p>Week '.$week.' - About to insert '.count($insert_player).' player records into wp_playoffs</p>';
    //printr($insert_player, 0);

    foreach ($insert_player as $key => $playoffs){
        $result = $wpdb->insert(
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
        
        if($result === false){
            echo '<p style="color:red;">INSERT FAILED for player '.$key.' - Error: '.$wpdb->last_error.'</p>';
            echo '<pre>Data: '.print_r($playoffs, true).'</pre>';
        } else {
            echo '<p style="color:green;">✓ Inserted player '.$key.'</p>';
        }
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
                                <li>If there is an OVERTIME game, you will need to manually insert that data.  USE THE OT SCORE WIDGET on the homepage -- http://pfl-data.local/player-ot-score/?SQL=0 -- First figure out OT rosters and scores.  Then break the tie(s) and add one point to the winner on the MFL site.  Then add records to the wp_overtime table.  Add OT player ids to the team tables (ex. wp_team_WRZ).  Then add a line record for the game to the individual player tables.</li>
                                <li>As of 2022 -- This script also works for returning Playoffs and Possebowl player scores and results.  Just pass the week 15 or 16 var into the url.</li>
                            </ol>
						</div>
					</div>
				</div>
			</div>
		
			<div class="row">

				<div class="col-sm-8">

                    <style>
                        a:active {
                            color:blue;
                        }
                        a:visited {
                            color:blue;
                        }
                        .players-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 20px 0;
                            background-color: white;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        }
                        .players-table thead {
                            background-color: #23282d;
                            color: white;
                        }
                        .players-table th {
                            padding: 12px;
                            text-align: left;
                            font-weight: 600;
                            font-size: 13px;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                        }
                        .players-table td {
                            padding: 10px 12px;
                            border-bottom: 1px solid #e5e5e5;
                        }
                        .players-table tbody tr:hover {
                            background-color: #f9f9f9;
                        }
                        .players-table tbody tr.missing-table {
                            background-color: #fff3cd;
                        }
                        .players-table tbody tr.missing-table:hover {
                            background-color: #ffe69c;
                        }
                        .player-link {
                            text-decoration: none;
                            color: #0073aa;
                            font-weight: 500;
                        }
                        .player-link:hover {
                            color: #005177;
                            text-decoration: underline;
                        }
                        .position-badge {
                            display: inline-block;
                            padding: 2px 8px;
                            background-color: #e5e5e5;
                            color: #555;
                            border-radius: 3px;
                            font-size: 11px;
                            font-weight: 600;
                            margin-left: 6px;
                        }
                        .points-value {
                            font-weight: 600;
                            color: #2c3e50;
                            font-size: 14px;
                        }
                        .copy-script-btn {
                            display: inline-block;
                            padding: 4px 10px;
                            background-color: #0073aa;
                            color: white;
                            border-radius: 3px;
                            cursor: pointer;
                            font-size: 11px;
                            text-decoration: none;
                            border: none;
                            transition: all 0.2s;
                            white-space: nowrap;
                        }
                        .copy-script-btn:hover {
                            background-color: #005177;
                            color: white;
                            transform: translateY(-1px);
                            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                        }
                        .copy-script-btn.copied {
                            background-color: #46b450;
                        }
                        .copy-script-btn:before {
                            content: '📋 ';
                        }
                        .missing-label {
                            color: #d63638;
                            font-weight: 600;
                        }
                        .status-indicator {
                            display: inline-block;
                            width: 8px;
                            height: 8px;
                            border-radius: 50%;
                            background-color: #46b450;
                            margin-right: 6px;
                        }
                        .status-indicator.missing {
                            background-color: #d63638;
                        }
                    </style>
                    
                    <script>
                    function copyPythonScript(playerName, year, week, button) {
                        const command = `python3 build-single-player-mfl.py "${playerName}" ${year} ${week}`;
                        
                        // Copy to clipboard using modern API
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(command).then(function() {
                                // Success feedback
                                const originalText = button.innerHTML;
                                button.innerHTML = '✓ Copied!';
                                button.classList.add('copied');
                                
                                setTimeout(function() {
                                    button.innerHTML = originalText;
                                    button.classList.remove('copied');
                                }, 2000);
                            }).catch(function(err) {
                                console.error('Failed to copy:', err);
                                alert('Failed to copy to clipboard');
                            });
                        } else {
                            // Fallback for older browsers
                            const textarea = document.createElement('textarea');
                            textarea.value = command;
                            textarea.style.position = 'fixed';
                            textarea.style.opacity = '0';
                            document.body.appendChild(textarea);
                            textarea.select();
                            
                            try {
                                document.execCommand('copy');
                                const originalText = button.innerHTML;
                                button.innerHTML = '✓ Copied!';
                                button.classList.add('copied');
                                
                                setTimeout(function() {
                                    button.innerHTML = originalText;
                                    button.classList.remove('copied');
                                }, 2000);
                            } catch (err) {
                                console.error('Failed to copy:', err);
                                alert('Failed to copy to clipboard');
                            }
                            
                            document.body.removeChild(textarea);
                        }
                    }
                    </script>

					<?php 
						if($run == 'false'){
							echo '<div style="padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin-bottom: 15px;"><strong>ℹ️ NO DATA INSERTED</strong> - SQL parameter is set to false</div>';
						}
						
						echo '<h4 style="margin-top: 0;">Players for '.$year.' Week '.$week.'</h4>';
						echo '<table class="players-table">';
						echo '<thead><tr>';
						echo '<th>Status</th>';
						echo '<th>Player</th>';
						echo '<th>Position</th>';
						echo '<th>Points</th>';
						echo '<th>Actions</th>';
						echo '</tr></thead>';
						echo '<tbody>';
						
						foreach ($insert_player as $key => $value){
							$query = $wpdb->get_results("select * from $key", ARRAY_N);
							
							if(empty($query)){
								echo '<tr class="missing-table">';
								echo '<td><span class="status-indicator missing"></span></td>';
								echo '<td colspan="4"><span class="missing-label">TABLE MISSING: '.$key.'</span></td>';
								echo '</tr>';
							} 
							
							if(!empty($query)){
								$weekid = $year.$weeks_2dig_asso[$week];
								$plscore = get_player_score_by_week($key, $weekid);
								$thescore = $plscore['points'];
								$pos = pid_to_position($key);
								$player_full_name = pid_to_name($key, 0);
								$player_name_escaped = htmlspecialchars($player_full_name, ENT_QUOTES, 'UTF-8');
								
								echo '<tr>';
								echo '<td><span class="status-indicator"></span></td>';
								echo '<td><a href="/player/?id='.$key.'" target="_blank" class="player-link">'.$player_full_name.'</a></td>';
								echo '<td><span class="position-badge">'.$pos.'</span></td>';
								echo '<td><span class="points-value">'.$thescore.'</span></td>';
								echo '<td><button class="copy-script-btn" onclick="copyPythonScript(\''.addslashes($player_full_name).'\', '.$year.', '.$week.', this)" title="Copy Python script command to clipboard">Copy Script</button></td>';
								echo '</tr>';
								
								$storetoload[] = $key;
							}
						} 
						
						echo '</tbody></table>';

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

		</div>
	</div>
</div>


<?php get_footer(); ?>