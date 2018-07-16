<?php
/*
 * Template Name: Update Team Data
 * Description: Master Build for updating team tables with wins, locations, etc.
 */
 ?>


<?php 
	
get_header(); 
	
	
	echo 'hhhhh';
	
	$current_team = 'WRZ';

	
	$wpdb = new wpdb('root','root','local','localhost');
	$mydb = new wpdb('root','root','pflmicro','localhost');
	

	$RBS = $mydb->get_results("select * from RBS", ARRAY_N);
	$ETS = $mydb->get_results("select * from ETS", ARRAY_N);
	$PEP = $mydb->get_results("select * from PEP", ARRAY_N);
	$WRZ = $mydb->get_results("select * from WRZ", ARRAY_N);
	$CMN = $mydb->get_results("select * from CMN", ARRAY_N);
	$BUL = $mydb->get_results("select * from BUL", ARRAY_N);
	$SNR = $mydb->get_results("select * from SNR", ARRAY_N);
	$TSG = $mydb->get_results("select * from TSG", ARRAY_N);
	$BST = $mydb->get_results("select * from BST", ARRAY_N);
	$MAX = $mydb->get_results("select * from MAX", ARRAY_N);
	$PHR = $mydb->get_results("select * from PHR", ARRAY_N);
	$SON = $mydb->get_results("select * from SON", ARRAY_N);
	$ATK = $mydb->get_results("select * from ATK", ARRAY_N);
	$HAT = $mydb->get_results("select * from HAT", ARRAY_N);
	$DST = $mydb->get_results("select * from DST", ARRAY_N);
	
	function get_team_score_by_weekid($teamid){
		foreach ($teamid as $value){
			$result[$value[0]] = $value[5];
		}
		return $result;		
	}
	
	$RBS_scores = get_team_score_by_weekid($RBS);
	$ETS_scores = get_team_score_by_weekid($ETS);
	$PEP_scores = get_team_score_by_weekid($PEP);
	$WRZ_scores = get_team_score_by_weekid($WRZ);
	$CMN_scores = get_team_score_by_weekid($CMN);
	$BUL_scores = get_team_score_by_weekid($BUL);
	$SNR_scores = get_team_score_by_weekid($SNR);
	$TSG_scores = get_team_score_by_weekid($TSG);
	$BST_scores = get_team_score_by_weekid($BST);
	$MAX_scores = get_team_score_by_weekid($MAX);
	$PHR_scores = get_team_score_by_weekid($PHR);
	$SON_scores = get_team_score_by_weekid($SON);
	$ATK_scores = get_team_score_by_weekid($ATK);
	$HAT_scores = get_team_score_by_weekid($HAT);
	$DST_scores = get_team_score_by_weekid($DST);
	
	
	$teaminfo = $mydb->get_results("select * from teams", ARRAY_N);
	
	foreach ($teaminfo as $value){
		$stadium[$value[4]] = $value[3];
	}	
	

	//printr($stadium, 0);
	
	$overtime = $mydb->get_results("select * from overtime", ARRAY_N);
	
	
	
	foreach ($overtime as $byweekid){
		$theweek = substr($byweekid[0], 0, -2);
		
		$newovertime[$byweekid[0]] = array (
			'weekid' => $theweek,	
			$byweekid[1] => array (
				'QB2' => $byweekid[3], 
				'RB2' => $byweekid[4],
				'WR2' => $byweekid[5],
				'PK2' => $byweekid[6],
				'extra_ot' => $byweekid[12]
			),
			'loser' => $byweekid[2],
			$byweekid[2] => array (
				'QB2' => $byweekid[7], 
				'RB2' => $byweekid[8],
				'WR2' => $byweekid[9],
				'PK2' => $byweekid[10],
				'extra_ot' => $byweekid[12]
			)
		);
	}
		
 	//printr($newovertime, 0);
 
 	
	
	
	// get list of all player ids from database
	
	$allplayers = $mydb->get_results("select * from players", ARRAY_N);
	foreach ($allplayers as $player){
		$playerids[] = $player[0];
	}
	
	function getplayersbyid(){
		global $playerids;
		global $mydb;
		foreach ($playerids as $get){
			$gettable = $mydb->get_results("select * from $get", ARRAY_N);
			$allplayerdata[$get] = $gettable; 
		}
		return $allplayerdata;
	}
	
	// get all player weekly data into one mamma jamma array
	
	// THIS WON'T SET AS A TRANSIENT!!! IM NOT SURE WHY...
	function get_all_player_tables() {	
		global $playerids;
		$transient = get_transient( 'all_player_table_trans' );
		if( ! empty( $transient ) ) {
			return $transient;
		} else {
			$set = getplayersbyid();
			set_transient( 'all_player_table_trans', $set, DAY_IN_SECONDS );
			return $set;
		}
	}
		
	$allplayertables = get_all_player_tables();	
	
	foreach ($allplayertables as $key => $player){
		foreach ($player as $week){
			if ($week[4] == $current_team){
				$justteam[] = array(
					'player' => $key,
					'team' => $week[4],
					'week' => $week[0],
					'position' => substr($key, -2)
				);
			}
		}
	}
	
	// resorts the justteam array so that weeks are grouped together and sorted by oldest to newest
	function sortByOrder($a, $b) {
    return $a['week'] - $b['week'];
	}
	usort($justteam, 'sortByOrder');
	
	foreach($justteam as $key => $item){
		$arr_player_team_week[$item['week']][$item['position']] = $item['player'];
	}

	ksort($arr_player_team_week, SORT_NUMERIC);
	
	//printr($arr_player_team_week, 0);
	
	
	
	
	
	foreach (${$current_team} as $values){
		
		$id 		= $values[0];
		$season 	= $values[1];
		$week 		= $values[2];
		$vs 		= $values[3];
		$home		= $values[4];
		$points		= $values[5];
		$team_int	= $values[6];
		


		if($home == 'H'){
			$location = $stadium[$team_int];
		} else {
			$location = $stadium[$vs];
		}
		
		$vs_points = ${$vs.'_scores'}[$id];
		$difference = $points - $vs_points;
		
		// get up to three OT games during the same week.
		$getot1 = $newovertime[$id.'01'][$team_int];
		$getot2 = $newovertime[$id.'02'][$team_int];
		$getot3 = $newovertime[$id.'03'][$team_int];
		
		$printot = '';
		if(!empty($getot1)){ $printot = $getot1; }
		if(!empty($getot2)){ $printot = $getot2; }
		if(!empty($getot3)){ $printot = $getot3; }
		
		$wpinsert[$id] = array (
			'id' => $id,
			'season' => $season,
			'week' => $week,
			'team_int' => $team_int,
			'points' => $points,
			'vs' => $vs,
			'vs_points' => $vs_points,
			'home_away' => $home,
			'stadium' => $location,
			'result' => $difference,
			'QB1' => $arr_player_team_week[$id]['QB'],
			'RB1' => $arr_player_team_week[$id]['RB'],
			'WR1' => $arr_player_team_week[$id]['WR'],
			'PK1' => $arr_player_team_week[$id]['PK'],
			'overtime' => $printot
		);
	}

/*
	printr($wpinsert, 0);
	die();
*/

	
	if(!empty( $wpinsert )){
		foreach ($wpinsert as $key => $insert){
			
			if (!empty($insert['overtime'])){
				$ot = 1;
			} else {
				$ot = 0;
			}
			
			if (!empty($insert['overtime']['QB2'])){
				$qb2 = $insert['overtime']['QB2'];
			} else {
				$qb2 = '';
			}
			if (!empty($insert['overtime']['RB2'])){
				$rb2 = $insert['overtime']['RB2'];
			} else {
				$rb2 = '';
			}
			if (!empty($insert['overtime']['WR2'])){
				$wr2 = $insert['overtime']['WR2'];
			} else {
				$wr2 = '';
			}
			if (!empty($insert['overtime']['PK2'])){
				$pk2 = $insert['overtime']['PK2'];
			}
			else {
				$pk2 = '';
			}
			if (!empty($insert['overtime']['extra_ot'])){
				$extra_ot = $insert['overtime']['extra_ot'];
			}
			
			
			
			$wpdb->insert(
				'wp_team_'.$current_team, 
				array(
					'id' => $insert['id'],
					'season' => $insert['season'],
					'week' => $insert['week'],
					'team_int' => $insert['team_int'],
					'points' => $insert['points'],
					'vs' => $insert['vs'],
					'vs_points' => $insert['vs_points'],
					'home_away' => $insert['home_away'],
					'stadium' => $insert['stadium'],
					'result' => $insert['result'],
					'QB1' => $insert['QB1'],
					'RB1' => $insert['RB1'],
					'WR1' => $insert['WR1'],
					'PK1' => $insert['PK1'],
					'overtime' => $ot,
					'QB2' => $qb2,
					'RB2' => $rb2,
					'WR2' => $wr2,
					'PK2' => $pk2,
					'extra_ot' => $extra_ot
				)
			);
			echo 'inserted row '.$insert['id'].'<br>';
		}
	}

	

get_footer(); ?>