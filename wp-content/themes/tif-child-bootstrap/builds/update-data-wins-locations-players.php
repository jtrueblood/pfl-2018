<?php
/*
 * Template Name: Build Additional Player Data
 * Description: Master Build for updating data on player tables with wins, locations, etc.
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 

?>
<!--
<p>Player ID: <input type="text" name="playerid" /></p>
<input type="submit" value="Update Player" />
<p></p>
-->
<?php

/*
get_cache('team/ETS_f', 0);	
$ets = $_SESSION['team/ETS_f'];
print_r($ets);
*/

$mydb = new wpdb('root','root','pflmicro','localhost');
$wpdb = new wpdb('root','root','local','localhost');


$playerA = '2001BradQB';
		
		$playerquery = $mydb->get_results("select * from $playerA", ARRAY_N);
		
		$teaminfo = $mydb->get_results("select * from teams", ARRAY_N);
		
		foreach ($teaminfo as $value){
			$stadium[$value[4]] = $value[3];
		}
		
		$buildnew = array();
		foreach ($playerquery as $revisequery){
			$weekid = $revisequery[0];
			$thisteam = $revisequery[4];
			$playerpoints = $revisequery[3];
			$vsteam = $revisequery[5];


	$ETS = $wpdb->get_results("select * from wp_team_ETS", ARRAY_N);
	$PEP = $wpdb->get_results("select * from wp_team_PEP", ARRAY_N);
	$WRZ = $wpdb->get_results("select * from wp_team_WRZ", ARRAY_N);
	$CMN = $wpdb->get_results("select * from wp_team_CMN", ARRAY_N);
	$BUL = $wpdb->get_results("select * from wp_team_BUL", ARRAY_N);
	$SNR = $wpdb->get_results("select * from wp_team_SNR", ARRAY_N);
	$TSG = $wpdb->get_results("select * from wp_team_TSG", ARRAY_N);
	$SON = $wpdb->get_results("select * from wp_team_SON", ARRAY_N);
	$HAT = $wpdb->get_results("select * from wp_team_HAT", ARRAY_N);
	$DST = $wpdb->get_results("select * from wp_team_DST", ARRAY_N);
	$ATK = $wpdb->get_results("select * from wp_team_ATK", ARRAY_N);
	$PHR = $wpdb->get_results("select * from wp_team_PHR", ARRAY_N);
	$MAX = $wpdb->get_results("select * from wp_team_MAX", ARRAY_N);
						
			$allteams = array('ETS' => $ETS, 'PEP' => $PEP, 'WRZ' => $WRZ, 'CMN' => $CMN, 'BUL' => $BUL, 'SNR' => $SNR, 'TSG' => $TSG, 'SON' => $SON, 'HAT' => $HAT, 'DST' => $DST, 'ATK' => $ATK, 'PHR' => $PHR, 'MAX' => $MAX);
			
			foreach ($allteams as $key => $teams){
				foreach ($teams as $games){
					$newallteams[$key][$games[0]] = $games;
				}
			}
			
			//get_cache('team/'.$vsteam.'_f', 0);	
			//$playing = $_SESSION['team/'.$vsteam.'_f'];
			if ($newallteams[$thisteam][$weekid][9] < 0){
				$result = 0;
			} else {
				$result = 1;
			}
				
			$buildnew[] = array(
				'week_id' 	=> $newallteams[$thisteam][$weekid][0], 
				'year' 		=> $newallteams[$thisteam][$weekid][1], 
				'week' 		=> $newallteams[$thisteam][$weekid][2], 
				'points' 	=> $playerpoints, 
				'team' 		=> $thisteam, 
				'versus' 	=> $newallteams[$thisteam][$weekid][5], 
				'playerid' 	=> $playerA, 
				'win_loss' 	=> $result, 
				'home_away' => $newallteams[$thisteam][$weekid][7],
				'location' 	=> $newallteams[$thisteam][$weekid][8]
			);
		}
		
	
	
/*
	foreach ($buildnew as $value){
		if($value['year'] == 2017){
			$yearbuild[] = $value;
		}
		
		$winloss[] = $value['win_loss'];
		$homeaway[] = $value['home_away'];
		$location[] = $value['location'];
	}
	

printr($winloss, 0);
*/


/*
	$table_name = $wpdb->prefix.$playerA;
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	     //table not in database. Create new table
	     $charset_collate = $wpdb->get_charset_collate();
	 
	     $sql = "CREATE TABLE $table_name like 1991AikmQB";
	     require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	     dbDelta( $sql );
	}
	
*/

	foreach ($buildnew as $build){
	
		$wpdb->insert(
			$playerA, 
			array(
				'week_id' 	=> $build['week_id'],
				'year' 		=> $build['year'], 
				'week' 		=> $build['week'], 
				'points' 	=> $build['points'],
				'team'		=> $build['team'],
				'versus' 	=> $build['versus'], 
				'playerid' 	=> $build['playerid'], 
				'win_loss' 	=> $build['win_loss'], 
				'home_away' => $build['home_away'],
				'location' 	=> $build['location']
			)
		);
		
	}

printr($buildnew, 0);


/*
'win_loss' 	=> $build['win_loss'], 
'home_away' => $build['home_away'],
'location' 	=> $build['location']
*/



/*

$allplayers = $mydb->get_results("select * from players", ARRAY_N);
foreach ($allplayers as $player){
	$playerids[] = $player[0];
}
*/


//printr($playerids,0);


//save_new_player_table($theplayer);


/*
$x = 0;
foreach ($playerids as $theplayer){	
	$check = $wpdb->get_results("select * from $theplayer", ARRAY_N);	
	if (empty($check)){
		echo $theplayer.' needs to have data added<br>';
		save_new_player_table($theplayer);
		$x++;
		if ($x > 15){
			die();
		}
	} else {
		echo $theplayer.' already has data<br>';
	}
	
}
*/







get_footer(); ?>