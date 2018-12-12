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

// get arrat of all team / all week data and index the values
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

//printr($weeknotes, 0);

?>

<?php get_header(); ?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<!--Page content-->
				<div id="page-content">
					<div id="page-title">
						<?php while (have_posts()) : the_post(); ?>
						<?php endwhile; wp_reset_query(); ?>	
					</div>
					
					<div class="row">
						
						<div class="col-xs-24 col-md-8">
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
						
						<div class="col-xs-24 col-sm-12 col-md-8">
							<?php echo '<h4>Results for Week '.$week_sel.', '.$year_sel.'</h4>'; ?>
						</div>
						
						<div class="col-xs-24 col-sm-12 col-md-8">
							<p>
							<a href="?Y=<?php echo $prev_year; ?>&W=<?php echo $prev_week; ?>">Prev Week</a>
							&emsp;|&emsp;
							<a href="?Y=<?php echo $next_year; ?>&W=<?php echo $next_week; ?>">Next Week</a>
							</p>
						</div>
					
					</div>
					
								
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
							
							// Display the Boxes Here...		
							echo '<div class="col-xs-24 col-sm-12 col-md-8">
							
							<div class="panel panel-bordered panel-dark">
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
									
							if ($homepoints > $awaypoints){
								echo '<span class="text-2x text-bold">'.$hometeam_full.'</span><span class="text-2x pull-right text-bold">'.$homepoints.'</span><br>';
								echo '<span class="text-2x">'.$awayteam_full.'</span><span class="text-2x pull-right">'.$awaypoints.'</span><br>';
							} else {
								echo '<span class="text-2x">'.$hometeam_full.'</span>  <span class="text-2x pull-right">'.$homepoints.'</span><br>';
								echo '<span class="text-2x text-bold">'.$awayteam_full.'</span>  <span class="text-2x pull-right text-bold">'.$awaypoints.'</span><br>';
							}						
					
					
							echo '<hr/><h5>Boxscores</h5>';
					// boxscore left image
							echo '<div class="col-xs-12 team-bar boxscorebox" style="background-image:url('.get_stylesheet_directory_uri().'/img/'.$hometeam.'-bar.png);">';
							echo '</div>';
					
					// boxscore right image	
							echo '<div class="col-xs-12 team-bar" style="background-image:url('.get_stylesheet_directory_uri().'/img/'.$awayteam.'-bar-horz.png);">';
							echo '</div>';
					
					// boxscore left players
						echo '<div class="col-xs-12 boxscorebox">';
								
						echo checkfornone ($h_qb1_data['first']).' '.$h_qb1_data['last'].'<span class="pull-right">'.$h_qb1_data['points'].'</span><br>';
						echo checkfornone ($h_rb1_data['first']).' '.$h_rb1_data['last'].'<span class="pull-right">'.$h_rb1_data['points'].'</span><br>';
						echo checkfornone ($h_wr1_data['first']).' '.$h_wr1_data['last'].'<span class="pull-right">'.$h_wr1_data['points'].'</span><br>';
						echo checkfornone ($h_pk1_data['first']).' '.$h_pk1_data['last'].'<span class="pull-right">'.$h_pk1_data['points'].'</span><br>';
								
						echo '</div>';		
					
					// boxscore right players
						echo '<div class="col-xs-12 boxscorebox">';
								
						echo checkfornone ($a_qb1_data['first']).' '.$a_qb1_data['last'].'<span class="pull-right">'.$a_qb1_data['points'].'</span><br>';
						echo checkfornone ($a_rb1_data['first']).' '.$a_rb1_data['last'].'<span class="pull-right">'.$a_rb1_data['points'].'</span><br>';
						echo checkfornone ($a_wr1_data['first']).' '.$a_wr1_data['last'].'<span class="pull-right">'.$a_wr1_data['points'].'</span><br>';
						echo checkfornone ($a_pk1_data['first']).' '.$a_pk1_data['last'].'<span class="pull-right">'.$a_pk1_data['points'].'</span><br>';
						
						
						echo '</div>';				
								
					//overtime area 
					
						if ( $is_overtime == 1){
							
							echo '<div class="overtime">';
								echo '<hr>';
								echo '<span class="text-bold" style="display:block;">Overtime Game</span><br>';
							
									echo '<div class="col-xs-12 boxscorebox">';
								
										echo checkfornone ($h_qb2_data['first']).' '.$h_qb2_data['last'].'<span class="pull-right">'.$h_qb2_data['points'].'</span><br>';
										echo checkfornone ($h_rb2_data['first']).' '.$h_rb2_data['last'].'<span class="pull-right">'.$h_rb2_data['points'].'</span><br>';
										echo checkfornone ($h_wr2_data['first']).' '.$h_wr2_data['last'].'<span class="pull-right">'.$h_wr2_data['points'].'</span><br>';
										echo checkfornone ($h_pk2_data['first']).' '.$h_pk2_data['last'].'<span class="pull-right">'.$h_pk2_data['points'].'</span><br>';
								
									echo '</div>';	
									
									echo '<div class="col-xs-12 boxscorebox">';
								
										echo checkfornone ($a_qb2_data['first']).' '.$a_qb2_data['last'].'<span class="pull-right">'.$a_qb2_data['points'].'</span><br>';
										echo checkfornone ($a_rb2_data['first']).' '.$a_rb2_data['last'].'<span class="pull-right">'.$a_rb2_data['points'].'</span><br>';
										echo checkfornone ($a_wr2_data['first']).' '.$a_wr2_data['last'].'<span class="pull-right">'.$a_wr2_data['points'].'</span><br>';
										echo checkfornone ($a_pk2_data['first']).' '.$a_pk2_data['last'].'<span class="pull-right">'.$a_pk2_data['points'].'</span><br>';
								
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
										
										

									// end notes area
									
									// tooltip....
									//echo '<a class="add-tooltip" data-placement="bottom" data-toggle="tooltip" data-original-title="Tooltip on top">Tooltip on top</a>';
/*
						
*/
									echo '</div> 
										</div>
									</div>
									
								</div>';
								
								// clearfix on different device sizes
								// clearfix on different device sizes
								if($w % 3 == 0){
									echo '<div class="clear"></div>';
								}
								$w++;
								
						} // END THE FOREACH	
					} else {  // END IF ISSET
						echo '<h3>WEEK NOT FOUND</h3>';
					}		
						?>
							
					
					
				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->
			
			<?php include_once('main-nav.php'); ?>
			<?php include_once('aside.php'); ?>

		</div>		
		
		
		
</div> 
		
</div>
</div>


<?php get_footer(); ?>