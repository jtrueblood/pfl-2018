<?php
/*
 * Template Name: Results
 * Description: Page for weekly results
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
$season = date("Y");
$allWeeksZero = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14");

$year_sel = $_GET["Y"];
$week_sel = $_GET["W"];
$weekvar = $year_sel.$week_sel;

$namenum = 15;

// get options from option page
$get_update_pdf = get_field('update_pdfs', 'options');
foreach ($get_update_pdf as $val){
	$update_pdf[$val['week_id']] = $val['pdf_file'];
}


$wpdb = new wpdb('root','root','local','localhost');
$mydb = new wpdb('root','root','pflmicro','localhost');


$RBS = $wpdb->get_results("select * from wp_team_RBS", ARRAY_N);
$ETS = $wpdb->get_results("select * from wp_team_ETS", ARRAY_N);
$PEP = $wpdb->get_results("select * from wp_team_PEP", ARRAY_N);
$WRZ = $wpdb->get_results("select * from wp_team_WRZ", ARRAY_N);
$CMN = $wpdb->get_results("select * from wp_team_CMN", ARRAY_N);
$BUL = $wpdb->get_results("select * from wp_team_BUL", ARRAY_N);
$SNR = $wpdb->get_results("select * from wp_team_SNR", ARRAY_N);
$TSG = $wpdb->get_results("select * from wp_team_TSG", ARRAY_N);
$BST = $wpdb->get_results("select * from wp_team_BST", ARRAY_N);
$MAX = $wpdb->get_results("select * from wp_team_MAX", ARRAY_N);
$PHR = $wpdb->get_results("select * from wp_team_PHR", ARRAY_N);
$SON = $wpdb->get_results("select * from wp_team_SON", ARRAY_N);
$ATK = $wpdb->get_results("select * from wp_team_ATK", ARRAY_N);
$HAT = $wpdb->get_results("select * from wp_team_HAT", ARRAY_N);
$DST = $wpdb->get_results("select * from wp_team_DST", ARRAY_N);


foreach ($WRZ as $value){
	$years[] = $value[1];
	$nextweek[$value[0]] = array( $value[1], sprintf("%02d", $value[2]) ); 
}
$allSeasons = array_unique($years);

$prev_year = $nextweek[$weekvar - 1][0];
$prev_week = $nextweek[$weekvar - 1][1];
$next_year = $nextweek[$weekvar + 1][0];
$next_week = $nextweek[$weekvar + 1][1];

$teamarrays = array (
	'RBS' => $RBS, 
	'ETS' => $ETS,
	'PEP' => $PEP,
	'WRZ' => $WRZ,
	'CMN' => $CMN,
	'BUL' => $BUL,
	'SNR' => $SNR,
	'TSG' => $TSG,
	'BST' => $BST,
	'MAX' => $MAX,
	'PHR' => $PHR,
	'SON' => $SON,
	'ATK' => $ATK,
	'HAT' => $HAT,
	'DST' => $DST	
);

$teamlist = array(
	'RBS' => 'Red Barons',
	'ETS' => 'Euro-Trashers',
	'PEP' => 'Peppers',
	'WRZ' => 'Space Warriorz',
	'CMN' => 'C-Men',
	'BUL' => 'Raging Bulls',
	'SNR' => 'Sixty Niners',
	'TSG' => 'Tsongas',
	'BST' => 'Booty Bustas',
	'MAX' => 'Mad Max',
	'PHR' => 'Paraphernalia',
	'SON' => 'Rising Son',
	'ATK' => 'Melmac Attack',
	'HAT' => 'Jimmys Hats',
	'DST' => 'Destruction'	
);

function insertslams($array){
												
	global $wpdb;
	$arr = $array;

	$insertarr = $wpdb->insert(
		 'wp_grandslams',
	     array(
		    'id' 		=> $arr['id'],
			'weekid' 	=> $arr['weekid'],
			'teamid' 	=> $arr['teamid']
		),
		 array( 
			'%s','%d','%s' 
		 )
	);
	
}

function insertpotw($y,$w,$player){
												
	global $wpdb;
	$arr = $array;

	$insertpoty = $wpdb->insert(
		 'wp_player_of_week',
	     array(
			'weekid' 	=> $y.$w,
			'playerid' 	=> $player
		),
		 array( 
			'%s','%s' 
		 )
	);
	
}


// get array of all team / all week data and index the values
foreach ($teamarrays as $key => $value){
	foreach ($value as $week){
		$byweek[$key][$week[0]] = array(
			'id' 		=> $week[0],
			'season' 	=> $week[1],
			'week' 		=> $week[2],
			'team_int' 	=> $week[3],
			'points' 	=> $week[4],
			'vs' 		=> $week[5],
			'vs_points' => $week[6],
			'home_away' => $week[7],
			'stadium' 	=> $week[8],
			'result' 	=> $week[9],
			'QB1' 		=> $week[10],
			'RB1' 		=> $week[11],
			'WR1' 		=> $week[12],
			'PK1' 		=> $week[13],
			'overtime' 	=> array (
				'is_overtime' => $week[14], 
				'QB2' 		=> $week[15],
				'RB2' 		=> $week[16],
				'WR2' 		=> $week[17],
				'PK2' 		=> $week[18],
				'extra_ot'	=> $week[19]
			)
		);
	}
}

// get an array of one specific week

$RBS_week = $byweek['RBS'][$weekvar];
$ETS_week = $byweek['ETS'][$weekvar];
$PEP_week = $byweek['PEP'][$weekvar];
$WRZ_week = $byweek['WRZ'][$weekvar];
$CMN_week = $byweek['CMN'][$weekvar];
$BUL_week = $byweek['BUL'][$weekvar];
$SNR_week = $byweek['SNR'][$weekvar];
$TSG_week = $byweek['TSG'][$weekvar];
$BST_week = $byweek['BST'][$weekvar];
$MAX_week = $byweek['MAX'][$weekvar];
$PHR_week = $byweek['PHR'][$weekvar];
$SON_week = $byweek['SON'][$weekvar];
$ATK_week = $byweek['ATK'][$weekvar];
$HAT_week = $byweek['HAT'][$weekvar];
$DST_week = $byweek['DST'][$weekvar];

$getwk = array(
	'RBS' => $RBS_week, 
	'ETS' => $ETS_week,
	'PEP' => $PEP_week,
	'WRZ' => $WRZ_week,
	'CMN' => $CMN_week,
	'BUL' => $BUL_week,
	'SNR' => $SNR_week,
	'TSG' => $TSG_week,
	'BST' => $BST_week,
	'MAX' => $MAX_week,
	'PHR' => $PHR_week,
	'SON' => $SON_week,
	'ATK' => $ATK_week,
	'HAT' => $HAT_week,
	'DST' => $DST_week
);

foreach ($getwk as $key => $value){
	if($value['home_away'] == 'H'){
		$schedulewk[$key] = $value['vs'];
	}
}

//printr($schedulewk , 0);

function get_player_week($playerid, $weekid){
	global $wpdb;
	global $mydb;
	
	$playerinfo = $wpdb->get_results("select * from wp_players", ARRAY_N);
	$playerdata = $wpdb->get_results("select * from $playerid", ARRAY_N);
	
	foreach ($playerinfo as $data){
		$plarray[$data[0]] = $data;
	}
	
	foreach ($playerdata as $data){
		$array[$data[0]] = $data;
	}
	$playerbyweek = array( 
		'points' 	=>  $array[$weekid][3], 
		'team'		=>	$array[$weekid][4],
		'first' 	=>  $plarray[$playerid][1],
		'last' 		=>  $plarray[$playerid][2],
		'position' 	=>  $plarray[$playerid][3]
		);
	return $playerbyweek;
}
//$rrr = get_player_week('2015GurlRB', $weekvar);
//printr($rrr , 0);

function checkfornone ($val){
	if($val == ''){
		echo 'None';
	} else {
		echo $val;
	}
}

$getgamenotes = $wpdb->get_results("select * from wp_game_notes", ARRAY_N);	

foreach ($getgamenotes as $note){
	if($note[1] == $weekvar){
		$weeknotes[$note[2]] = $note[3];
	}
}

function linktoplayerpage($pid, $all){
	echo '<a href="/player/?id='.$pid.'" style="cursor:hand;">'.$all.'</a>';
}

function get_helmet($team, $year){
	global $season;
	global $wpdb;
	$helmethistory = $wpdb->get_results("select * from wp_helmet_history where team = '$team'", ARRAY_N);
	
	foreach ($helmethistory as $value){
		$helmets[$value[2]] = array(
			'name' => $value[3],
			'helmet' => $value[4]
		);
	}
	
	$x = 1991;
	
	while ($x <= $season){
		if (isset($helmets[$x])){
		 	$myhelmets[$x] = $helmets[$x];
		 	$active = $helmets[$x];
		} else {
			$myhelmets[$x] = $active;
		} 
		$x++;
	}
	
	return $myhelmets[$year];
	
}

/*
$assoc = get_players_assoc ();
printr($assoc, 0);
*/


?>

<?php get_header(); ?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<!--Page content-->
				<div id="page-content">
								
					
						
						<div class="col-xs-24 col-sm-12 col-md-8">	
							<?php echo '<h2>Week '.$week_sel.', '.$year_sel.'</h2>'; ?>	
						</div>					
						
						<div class="col-xs-24 col-sm-12 col-md-8 next-prev-week">
							<p>
								<?php if ($week_sel == '14'){ ?>
										<a href="?Y=<?php echo $prev_year; ?>&W=<?php echo $prev_week; ?>">Prev Week</a>
											&emsp;|&emsp;
										<a href="?Y=<?php echo $year_sel + 1; ?>&W=<?php echo '01'; ?>">Next Week</a>
								<?php } 
									if($week_sel > 1 && $week_sel < 14) { ?>
										<a href="?Y=<?php echo $prev_year; ?>&W=<?php echo $prev_week; ?>">Prev Week</a>
											&emsp;|&emsp;
										<a href="?Y=<?php echo $next_year; ?>&W=<?php echo $next_week; ?>">Next Week</a>
								<?php }
								
									if($week_sel == '01'){ ?>
										<a href="?Y=<?php echo $year_sel - 1; ?>&W=<?php echo '14'; ?>">Prev Week</a>
											&emsp;|&emsp;
										<a href="?Y=<?php echo $next_year; ?>&W=<?php echo $next_week; ?>">Next Week</a>
								<?php } ?>
							</p>
						</div>
						
						<div class="col-xs-24 col-md-8 select-the-week">
							
							<select name="Words" id="comboYear"'; 
								<option value="1991">1991</option>
									<?php foreach($allSeasons as $select_year){ 
								echo'<option value="'.$select_year.'">'.$select_year.'</option>';    
								}
							?> </select> 
							
							<select name="Words" id="comboWeek">'; 
							<?php foreach($allWeeksZero as $select_week){ 
								echo'<option value="'.$select_week.'">'.$select_week.'</option>';    
								}
							?> </select>
							
													 
							<button id="schedulebtn" class="btn btn-default btn-hover-warning">Change Week</button><br/>
						</div>
					
						<div class="clear"></div>
					
								
				<?php
				$w = 1;	
					
				if(isset($schedulewk)){
					foreach ($schedulewk as $key => $value){
							
							$hometeam = $key;
							$awayteam = $value;
							
							$hometeam_full = $teamlist[$hometeam];
							$awayteam_full = $teamlist[$awayteam];
							
							$stadium = $getwk[$hometeam]['stadium'];
							
							$homepoints = $getwk[$hometeam]['points'];
							$awaypoints = $getwk[$awayteam]['points']; 
							
							$h_qb1 = $getwk[$hometeam]['QB1'];
							$h_rb1 = $getwk[$hometeam]['RB1'];
							$h_wr1 = $getwk[$hometeam]['WR1'];
							$h_pk1 = $getwk[$hometeam]['PK1'];	
							
							$a_qb1 = $getwk[$awayteam]['QB1'];
							$a_rb1 = $getwk[$awayteam]['RB1'];
							$a_wr1 = $getwk[$awayteam]['WR1'];
							$a_pk1 = $getwk[$awayteam]['PK1'];
						
							
							$h_qb1_data = get_player_week($h_qb1, $weekvar);
							$h_rb1_data = get_player_week($h_rb1, $weekvar);
							$h_wr1_data = get_player_week($h_wr1, $weekvar);
							$h_pk1_data = get_player_week($h_pk1, $weekvar);
							
							$a_qb1_data = get_player_week($a_qb1, $weekvar);
							$a_rb1_data = get_player_week($a_rb1, $weekvar);
							$a_wr1_data = get_player_week($a_wr1, $weekvar);
							$a_pk1_data = get_player_week($a_pk1, $weekvar);
							
							
							// overtime player data
							$is_overtime = $getwk[$hometeam]['overtime']['is_overtime'];
							
							$h_qb2 = $getwk[$hometeam]['overtime']['QB2'];
							$h_rb2 = $getwk[$hometeam]['overtime']['RB2'];
							$h_wr2 = $getwk[$hometeam]['overtime']['WR2'];
							$h_pk2 = $getwk[$hometeam]['overtime']['PK2'];	
							
							$a_qb2 = $getwk[$awayteam]['overtime']['QB2'];
							$a_rb2 = $getwk[$awayteam]['overtime']['RB2'];
							$a_wr2 = $getwk[$awayteam]['overtime']['WR2'];
							$a_pk2 = $getwk[$awayteam]['overtime']['PK2'];
							
							$h_qb2_data = get_player_week($h_qb2, $weekvar);
							$h_rb2_data = get_player_week($h_rb2, $weekvar);
							$h_wr2_data = get_player_week($h_wr2, $weekvar);
							$h_pk2_data = get_player_week($h_pk2, $weekvar);
							
							$a_qb2_data = get_player_week($a_qb2, $weekvar);
							$a_rb2_data = get_player_week($a_rb2, $weekvar);
							$a_wr2_data = get_player_week($a_wr2, $weekvar);
							$a_pk2_data = get_player_week($a_pk2, $weekvar);
							
							$is_extra_ot = $getwk[$hometeam]['overtime']['extra_ot'];
							
							$grandslam01 = '';
							$grandslam02 = '';
							
							if($h_qb1_data['points'] >= 10){
								if($h_rb1_data['points'] >= 10){
									if($h_wr1_data['points'] >= 10){
										if($h_pk1_data['points'] >= 10){
											$grandslam02 = 'GRANDSLAM';
										}
									}
								}
							}
							if($a_qb1_data['points'] >= 10){
								if($a_rb1_data['points'] >= 10){
									if($a_wr1_data['points'] >= 10){
										if($a_pk1_data['points'] >= 10){
											$grandslam01 = 'GRANDSLAM';
										}
									}
								}
							}
							
							if($h_qb2_data['points'] >= 10){
								if($h_rb2_data['points'] >= 10){
									if($h_wr2_data['points'] >= 10){
										if($h_pk2_data['points'] >= 10){
											$grandslam02 = 'GRANDSLAM';
										}
									}
								}
							}
							if($a_qb2_data['points'] >= 10){
								if($a_rb2_data['points'] >= 10){
									if($a_wr2_data['points'] >= 10){
										if($a_pk2_data['points'] >= 10){
											$grandslam01 = 'GRANDSLAM';
										}
									}
								}
							}
							
							// store the week scores as an array to use for high score
							$soredata[] = array(
								$h_qb1, 
								$h_rb1,
								$h_wr1,
								$h_pk1,
								$a_qb1,
								$a_rb1, 
								$a_wr1, 
								$a_pk1 
							);
							
							// Display the Boxes Here...		
							echo '<div class="col-xs-24 col-sm-12 col-md-8">
							
							<div class="panel panel-dark">
								<div class="panel-heading">
									<div class="panel-control">';
// 										alter CMN stadium name based on year
										if($stadium == 'Spankoni Center'){
											if($year_sel <= 2004){
												$stadium = 'The Gonad Bowl';
											}
										}
										echo '<em><small class="text-muted">Location: </small>'.$stadium.'</em>
									</div>
							</div>';
									
							echo '<div class="panel-body">';
							
							// get team name and helmet logo by season from the wp_helmet_history table - function above
							$get_the_helmet_home = get_helmet($hometeam, $year_sel);
							$get_the_helmet_away = get_helmet($awayteam, $year_sel);
									
							if ($homepoints > $awaypoints){
								echo '<span class="text-2x text-bold">'.$get_the_helmet_home['name'].'</span><span class="text-2x pull-right text-bold">'.$homepoints.'</span><br>';
								echo '<span class="text-2x">'.$get_the_helmet_away['name'].'</span><span class="text-2x pull-right">'.$awaypoints.'</span><br>';
							} else {
								echo '<span class="text-2x">'.$get_the_helmet_home['name'].'</span>  <span class="text-2x pull-right">'.$homepoints.'</span><br>';
								echo '<span class="text-2x text-bold">'.$get_the_helmet_away['name'].'</span>  <span class="text-2x pull-right text-bold">'.$awaypoints.'</span><br>';
							}						

					// boxscore left image
					
					echo '<hr>';
						echo '<h5>Boxscores</h5>';
						
							echo '<div class="col-xs-12 team-bar boxscorebox" style="background-image:url('.get_stylesheet_directory_uri().'/img/helmets/weekly/'.$hometeam.'-helm-right-'.$get_the_helmet_home['helmet'].'.png);">';
							echo '</div>';
					
					// boxscore right image	
							
							echo '<div class="col-xs-12 team-bar boxscorebox" style="background-image:url('.get_stylesheet_directory_uri().'/img/helmets/weekly/'.$awayteam.'-helm-left-'.$get_the_helmet_away['helmet'].'.png);">';
							echo '</div>';
							
							
					
					// boxscore left players
						echo '<div class="col-xs-12 boxscorebox">';
						
						echo '<a href="/player/?id='.$h_qb1.'">'.checkfornone ($h_qb1_data['first']).' '.$h_qb1_data['last'].'</a><span class="pull-right">'.$h_qb1_data['points'].'</span><br>';
						echo '<a href="/player/?id='.$h_rb1.'">'.checkfornone ($h_rb1_data['first']).' '.$h_rb1_data['last'].'</a><span class="pull-right">'.$h_rb1_data['points'].'</span><br>';
						echo '<a href="/player/?id='.$h_wr1.'">'.checkfornone ($h_wr1_data['first']).' '.$h_wr1_data['last'].'</a><span class="pull-right">'.$h_wr1_data['points'].'</span><br>';
						echo '<a href="/player/?id='.$h_pk1.'">'.checkfornone ($h_pk1_data['first']).' '.$h_pk1_data['last'].'</a><span class="pull-right">'.$h_pk1_data['points'].'</span><br>';
						
/*
						$get_the_helmet_home = get_helmet($hometeam, $year_sel);
						printr($get_the_helmet_home, 0);
*/
								
						echo '</div>';		
					
					// boxscore right players
						echo '<div class="col-xs-12 boxscorebox">';
								
						echo '<a href="/player/?id='.$a_qb1.'">'.checkfornone ($a_qb1_data['first']).' '.$a_qb1_data['last'].'</a><span class="pull-right">'.$a_qb1_data['points'].'</span><br>';
						echo '<a href="/player/?id='.$a_rb1.'">'.checkfornone ($a_rb1_data['first']).' '.$a_rb1_data['last'].'</a><span class="pull-right">'.$a_rb1_data['points'].'</span><br>';
						echo '<a href="/player/?id='.$a_wr1.'">'.checkfornone ($a_wr1_data['first']).' '.$a_wr1_data['last'].'</a><span class="pull-right">'.$a_wr1_data['points'].'</span><br>';
						echo '<a href="/player/?id='.$a_pk1.'">'.checkfornone ($a_pk1_data['first']).' '.$a_pk1_data['last'].'</a><span class="pull-right">'.$a_pk1_data['points'].'</span><br>';
						
/*
						$get_the_helmet_away = get_helmet($awayteam, $year_sel);
						printr($get_the_helmet_away, 0);
*/
						
						echo '</div>';				
								
					//overtime area 
					
						if ( $is_overtime == 1){
							
							echo '<div class="overtime">';
								echo '<hr>';
								echo '<span class="text-bold" style="display:block;">Overtime Game</span><br>';
							
									echo '<div class="col-xs-12 boxscorebox">';
								
										echo '<a href="/player/?id='.$h_qb2.'">'.checkfornone ($h_qb2_data['first']).' '.$h_qb2_data['last'].'</a><span class="pull-right">'.$h_qb2_data['points'].'</span><br>';
										echo '<a href="/player/?id='.$h_rb2.'">'.checkfornone ($h_rb2_data['first']).' '.$h_rb2_data['last'].'</a><span class="pull-right">'.$h_rb2_data['points'].'</span><br>';
										echo '<a href="/player/?id='.$h_wr2.'">'.checkfornone ($h_wr2_data['first']).' '.$h_wr2_data['last'].'</a><span class="pull-right">'.$h_wr2_data['points'].'</span><br>';
										echo '<a href="/player/?id='.$h_pk2.'">'.checkfornone ($h_pk2_data['first']).' '.$h_pk2_data['last'].'</a><span class="pull-right">'.$h_pk2_data['points'].'</span><br>';
								
									echo '</div>';	
									
									echo '<div class="col-xs-12 boxscorebox">';
								
										echo '<a href="/player/?id='.$a_qb2.'">'.checkfornone ($a_qb2_data['first']).' '.$a_qb2_data['last'].'</a><span class="pull-right">'.$a_qb2_data['points'].'</span><br>';
										echo '<a href="/player/?id='.$a_rb2.'">'.checkfornone ($a_rb2_data['first']).' '.$a_rb2_data['last'].'</a><span class="pull-right">'.$a_rb2_data['points'].'</span><br>';
										echo '<a href="/player/?id='.$a_wr2.'">'.checkfornone ($a_wr2_data['first']).' '.$a_wr2_data['last'].'</a><span class="pull-right">'.$a_wr2_data['points'].'</span><br>';
										echo '<a href="/player/?id='.$a_pk2.'">'.checkfornone ($a_pk2_data['first']).' '.$a_pk2_data['last'].'</a><span class="pull-right">'.$a_pk2_data['points'].'</span><br>';
								
									echo '</div>';	
								
								
							echo '</div>';
						}
										
																	
																		
									// game notes area 

											
									echo '<div class="clear"></div>
									
									<div class="notes-area">';
									
										$totalgamescore = $homepoints + $awaypoints;
										$differential = $homepoints - $awaypoints;
										
										if($is_extra_ot == 1){
											echo '<span class="text-bold">Double Overtime: </span>Home Team Wins<br>';
										}
										
										echo '<span class="text-bold">Total Game Score: </span>'.$totalgamescore.'<br>';	
										
									
										// point differential 
										if ($differential > 0){
											echo '<span class="text-bold">'.$hometeam_full.'</span> by '.$differential.' ';
										} else {
											echo '<span class="text-bold">'.$awayteam_full. '</span> by '.abs($differential).' ';
										}
										
										if ($differential > 20 or abs($differential) > 20){
											echo ' in a Blowout!&emsp;<br>';
										}
										
										if ($totalgamescore > 99){
											echo 'Barnburner!&emsp;<br>';
										}
										
										if ($totalgamescore < 40 && $year_sel > 1991){
											echo ' in a BS Win. <br>';
										}
										
										if ($homepoints >= 50 && $homepoints < 60){
											echo '<span class="text-bold">'.$hometeam_full.'</span> with 50+ points.&emsp;<br>';
										}
										
										if ($vspoints >= 50 && $awaypoints < 60){
											echo '<span class="text-bold">'.$awayteam_full.'</span> with 50+ points.&emsp;<br>';
										}
										
										if ($homepoints >= 60 && $homepoints < 70){
											echo '<span class="text-bold">'.$hometeam_full.'</span> with 60+ points!&emsp;<br>';
										}
										
										if ($vspoints >= 60 && $awaypoints < 70){
											echo '<span class="text-bold">'.$awayteam_full.'</span> with 60+ points!&emsp;<br>';
										}
										
										if ($homepoints >= 70){
											echo '<span class="text-bold">'.$hometeam_full.'</span> with 70+ points!&emsp;<br>';
										}
										
										if ($awaypoints >= 70){
											echo '<span class="text-bold">'.$awayteam_full.'</span> with 70+ points!&emsp;<br>';
										}
										
										echo '<br>';
										
										if (!empty($grandslam02)){
											echo '<span class="text-bold">GRANDSLAM</span> for the '.$hometeam_full.'!<br> ';
											$insertarr = array(
												'id' => $weekvar.$hometeam,
												'weekid' => $weekvar,
												'teamid' => $hometeam
											);
											insertslams($insertarr);
										}
										
										if (!empty($grandslam01)){
											echo '<span class="text-bold">GRANDSLAM</span> for the '.$awayteam_full.'!<br> ';
											$insertarr = array(
												'id' => $weekvar.$awayteam,
												'weekid' => $weekvar,
												'teamid' => $awayteam
											);
											insertslams($insertarr);
										}
										
										echo '<p></p><i>'.$weeknotes[$hometeam].'</i>'; 
										
										// check if kicker outscores all other players on team
										
/*
										if ($h_pk1_data['points'] > $h_qb1_data['points']){
											if ($h_pk1_data['points'] > $h_rb1_data['points']){
												if ($h_pk1_data['points'] > $h_wr1_data['points']){
													echo 'TRUE_HOME!';
												}
											}
										}
									
									
										if ($a_pk1_data['points'] > $a_qb1_data['points']){
											if ($a_pk1_data['points'] > $a_rb1_data['points']){
												if ($a_pk1_data['points'] > $a_wr1_data['points']){
													echo 'TRUE_AWAY!';
												}
											}
										}
*/
										
									// end notes area
									
									// tooltip....
									//echo '<a class="add-tooltip" data-placement="bottom" data-toggle="tooltip" data-original-title="Tooltip on top">Tooltip on top</a>';
/*
						
*/
									echo '</div> 
										</div>';
										?>

									</div>
									
								</div>
								
								<?php
								// clearfix on different device sizes
								// clearfix on different device sizes
								if($w % 3 == 0){
									echo '<div class="clear"></div>';
								}
								$w++;
								
								
						} // END THE FOREACH ?>
						

						<div class="col-xs-24 col-md-8">
							
							<!-- PLAYER OF THE WEEK --> 
							<!-- Calculate POTW if not previously set --> 
							<?php 
							$newdata = array_flatten($soredata);
							foreach ($newdata as $v){
								if ($v != ''){
									$bypoints[$v] = get_one_player_week($v, $year_sel.$week_sel);
								}
							}
							
							//printr($bypoints, 0);
							
							$pvqmult = get_allpvqs_year();
							
							foreach ($bypoints as $key => $val){
								$pos = substr($key, -2);
								if ($pos == 'QB'){
									$tops[$key] = $pvqmult['QB'] * $val;
								}
								if ($pos == 'RB'){
									$tops[$key] = $pvqmult['RB'] * $val;
								}
								if ($pos == 'WR'){
									$tops[$key] = $pvqmult['WR'] * $val;
								}
								if ($pos == 'PK'){
									$tops[$key] = $pvqmult['PK'] * $val;
								}
							}
							
							arsort($tops);
							
							reset($tops);
							$result = key($tops);
							
							//printr($tops, 0);
							
							?>	
							
							<div class="panel panel-dark">
								<div class="panel-heading">
									<div class="panel-control">
										Player of the Week
									</div>	
								</div>
								<div class="panel-body">
									<?php 
										
										$potw_table = $wpdb->get_results("select * from wp_player_of_week", ARRAY_N);
										foreach ($potw_table as $val){
											$sel_potw[$val[0]] = $val[1];
										}
										$setpotw = $sel_potw[$weekvar];
										//$setpotw = '1991SmitRB';
										$getpotw_data = get_player_data($setpotw);
										$getpotw_info = get_player_basic_info($setpotw);
										$potw = $getpotw_data[$weekvar];
/*
										printr($getpotw_info, 0);
										printr($potw, 0);
										*/

									if(isset($setpotw)){
									?>
								
									<div class="col-xs-24 col-sm-4">
 										<?php echo '<img src="/wp-content/uploads/'.$setpotw.'-50x50.jpg" class="img-responsive">'; ?> 
									</div>
									<div class="col-xs-24 col-sm-20">
										<h3 class="mar-no"><?php echo $getpotw_info[0]['first'].' '.$getpotw_info[0]['last']; ?></h3>
										<h4 style="margin-top: 7px;"><?php echo $teamlist[$potw['team']].' - '.$potw['points'].' Points';?></h4>
									</div>
									
									<?php 
										
										} else { 	
											echo 'No Player Found';
										}
									
									
									?>
									
								</div>
								<div class="panel-footer">
									<p><?php echo $result;	 ?> - Was PVQ Week High</p>
									<?php insertpotw($year_sel,$week_sel,$result); ?>
								</div>
								
								<?php		
								} else {  // END IF ISSET
									echo '<h3>WEEK NOT FOUND</h3>';
								}		  
			
								?>
								
							</div>
							
							
					
							

						<!-- PRINTED PDF if availible --> 
						<div class="panel panel-dark">
							<div class="panel-body">
								
									<?php $week_update_url = $update_pdf[$weekvar];
									if (isset($week_update_url)){
										echo '<h4><a href="'.$week_update_url .'" target="_blank"><i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;&nbsp;Update - '.$week_sel.', '.$year_sel.'</a></h4>';
									} else {
										echo '<h4>No Printed Update Found</h4>';
									} ?>
							
							</div>
							<div class="panel-footer">
								<p>(flipper)</p>
							</div>
						</div>
						
					
					</div>
					
					<div class="clear"></div>
					<!-- LEADERS WEEKLY -->
					<?php 
						include_once('inc/results_leaders.php');
					?>
										
					<div class="clear"></div>
					
				
						<?php if(isset($schedulewk)){
							foreach ($schedulewk as $key => $value){ ?>
								<div class="col-xs-24 col-md-6">
									<div class="panel panel-dark">
											<div class="panel-body">
												<div id="spider_<?php echo $key; ?>" style="height: 300px; margin: 0 auto"></div>
											</div>
									</div>
								</div>
						<?php }
						} ?>
						
						<div class="col-xs-24 col-md-18">
							<div class="panel panel-dark">
								<div class="panel-body">
									<?php 
										include_once('inc/weekly_standings.php'); ?>
								</div>
							</div>
						</div>	
						

<!--
						<div class="col-xs-24 col-md-12">
							<div class="panel panel-dark">
									<div class="panel-body">
										<div id="standingschart" style="height: 300px; margin: 0 auto"></div>
									</div>
							</div>
						</div>
-->
			
					
			</div>
	
				</div><!--End page content-->
				
				
								
						<?php
							// builds logic to get and insert values into wp_week_standings that returns the teams standing progress as the season goes.  It is used for the STANDINGS chart on the page.  Values reset for each year after week 14. 
							$standing = get_standings($year_sel);
							if(isset($standing)):
								foreach ($standing as $stand){
									$teamdiv[$stand['teamid']] = $stand['division'];
								}
								//printr($teamdiv, 0);
								
								foreach ($teamlist as $key => $value){
									$get = get_all_team_results_by_week($weekvar, $key);
									if($get != ''){
										$weekstand[$weekvar.$key] = $get;
									}
	
								}
							endif;
							
							function insert_week_stand($array){
								global $wpdb;
								global $teamdiv;
								global $week_sel;
								if(isset($array)):
									foreach ($array as $key => $value){
										$insertarr = $wpdb->insert(
											 'wp_week_standings',
										     array(
											    'id' 		=> $key,
											    'weekvar'	=> $value['season'].$week_sel,
												'season' 	=> $value['season'],
												'week' 		=> $value['week'],
												'team' 		=> $value['team'],
												'division'	=> $teamdiv[$value['team']],
												'points' 	=> $value['points'],
												'result' 	=> $value['result'],
												'victory' 	=> $value['victory']
											),
											array( 
												'%s','%d','%d','%d','%s','%s','%d','%d','%d' 
											)
										);
									}
								endif;
							}
							
							function insert_week_stand_check($array){
								global $wpdb;
								global $teamdiv;
								global $weekvar;
								global $week_sel;
								// gets all week ids
								$theweeks = the_weeks();
								// get current week key
								$weekkey = array_search($weekvar, $theweeks);
								$theweekneeded = $weekkey -1;
								
								// need to get previous week / weekvar based on current week var.  right now it is hardcoded.
								$getstand = $wpdb->get_results("select * from wp_week_standings where weekvar = $theweeks[$theweekneeded]", ARRAY_N);
								
								foreach ($getstand as $value){
									$lwv[$value[4]] = array(
										'points' 	=> $value[6],
										'result' 	=> $value[7],
										'victory' 	=> $value[8]
									);
								}
								
								if(isset($array)){
									foreach ($array as $key => $value){
										$tea = $value['team'];
										
										$insertarr = $wpdb->update(
											 'wp_week_standings',
										     array(
											    'id' 		=> $key,
											    'weekvar'	=> $value['season'].$week_sel,
												'season' 	=> $value['season'],
												'week' 		=> $value['week'],
												'team' 		=> $value['team'],
												'division'	=> $teamdiv[$value['team']],
												'points' 	=> $value['points'] + $lwv[$tea]['points'],
												'result' 	=> $value['result'] + $lwv[$tea]['result'],
												'victory' 	=> $value['victory'] + $lwv[$tea]['victory']
											),
											array( 
												'%s','%d','%d','%d','%s','%s','%d','%d','%d' 
											)
										);
									}
								}
								
								return $getstand;
							}
							
							if($week_sel == '01'){
								insert_week_stand($weekstand);
								//echo 'inserted week 1';
							} else {
								$lastweek = insert_week_stand_check($weekstand);
								//printr($lastweek, 0);
							}
							
						function get_wp_week_standings($week){
							global $wpdb;
							$getweek = $wpdb->get_results("select * from wp_week_standings where weekvar = $week", ARRAY_N);
							return $getweek;
						}
						
						$standingweek = get_wp_week_standings($weekvar);
						
							foreach ($standingweek as $value){
								$standingweekteam[$value[5]][$value[4]] = $value;
							}
							
						if(isset($standingweekteam)):	
							arsort($standingweekteam);
						endif;
							
						//printr($standingweekteam, 0);
						?>						
						
					
				
			</div><!--END CONTENT CONTAINER-->
			
			<?php include_once('main-nav.php'); ?>
			<?php include_once('aside.php'); ?>

		</div>		
		
		
		
</div> 
		
</div>
</div>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<?php 
	
function spiderpoints($value){
	if($value['points'] != ''){
		echo $value['points'];
	} else {
		echo 0;
	}
}

if(isset($schedulewk)){
	foreach ($schedulewk as $key => $value){ 
		
		$hometeam = $key;
		$awayteam = $value;	
		
		$spid_h_qb1 = $getwk[$hometeam]['QB1'];
		$spid_h_rb1 = $getwk[$hometeam]['RB1'];
		$spid_h_wr1 = $getwk[$hometeam]['WR1'];
		$spid_h_pk1 = $getwk[$hometeam]['PK1'];	
		
		$spid_a_qb1 = $getwk[$awayteam]['QB1'];
		$spid_a_rb1 = $getwk[$awayteam]['RB1'];
		$spid_a_wr1 = $getwk[$awayteam]['WR1'];
		$spid_a_pk1 = $getwk[$awayteam]['PK1'];
									
		$spid_h_qb1_data = get_player_week($spid_h_qb1, $weekvar);
		$spid_h_rb1_data = get_player_week($spid_h_rb1, $weekvar);
		$spid_h_wr1_data = get_player_week($spid_h_wr1, $weekvar);
		$spid_h_pk1_data = get_player_week($spid_h_pk1, $weekvar);
		
		$spid_a_qb1_data = get_player_week($spid_a_qb1, $weekvar);
		$spid_a_rb1_data = get_player_week($spid_a_rb1, $weekvar);
		$spid_a_wr1_data = get_player_week($spid_a_wr1, $weekvar);
		$spid_a_pk1_data = get_player_week($spid_a_pk1, $weekvar);
	
?>

<script type="text/javascript">
// spider chart
Highcharts.chart('spider_<?php echo $hometeam;?>', {

    chart: {
        polar: true,
        type: 'line'
    },

    accessibility: {
        description: 'A spiderweb chart'
    },

    title: {
        text: '<?php echo $hometeam.' vs '.$awayteam.' <span class="small">Comparison</span>';?>'
    },

    pane: {
        size: '90%'
    },

    xAxis: {
        categories: ['QB', 'RB', 'PK', 'WR'],
        tickmarkPlacement: 'on',
        lineWidth: 0
    },

    yAxis: {
        gridLineInterpolation: 'polygon',
        lineWidth: 1,
        min: 0
    },

    tooltip: {
        shared: true,
        pointFormat: '<span style="color:{series.color}">{series.name}: <b>{point.y:,.0f}</b><br/>'
    },

    legend: {
        align: 'bottom',
        verticalAlign: 'bottom'
    },

    series: [{
        name: '<?php echo $hometeam;?>',
        data: [<?php spiderpoints($spid_h_qb1_data);?>, <?php spiderpoints($spid_h_rb1_data);?>, <?php spiderpoints($spid_h_pk1_data);?>, <?php spiderpoints($spid_h_wr1_data);?>],
        pointPlacement: 'on',
        color: '#54abd9',
        fillColor: '#54abd9'
    }, {
        name: '<?php echo $awayteam;?>',
        data: [<?php spiderpoints($spid_a_qb1_data);?>, <?php spiderpoints($spid_a_rb1_data);?>, <?php  spiderpoints($spid_a_pk1_data);?>, <?php spiderpoints($spid_a_wr1_data);?>],
        pointPlacement: 'on',
        color: '#3b4146'
    }],

    responsive: {
        rules: [{
            condition: {
                maxWidth: 500
            },
            chartOptions: {
                legend: {
                    align: 'center',
                    verticalAlign: 'bottom'
                },
                pane: {
                    size: '80%'
                }
            }
        }]
    }

});
</script>




<?php }
} ?>


<?php get_footer(); ?>