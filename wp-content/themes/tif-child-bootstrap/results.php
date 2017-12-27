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
	
	
	// you will need to add the year in here once it is complete unless you can figure out a way to turn this into a looping function.
	
	$teaminfo = get_teams();
	foreach ($teaminfo as $key => $value){
		$teamids[$key] = $value['team'];
	}
	
	//printr($teaminfo, 0);
		
	$allSeasons = the_seasons();
		
	$playern = get_players_index();
	
	$playernames = get_players_assoc();

	foreach ($playernames as $key => $value){
		$playersid[] = $key;
	}
	
	$overtime = get_overtime();
	
	foreach ($overtime as $key => $value){
		$keyval1 = $weekvar.'01';
		$keyval2 = $weekvar.'02';
		$keyval3 = $weekvar.'03';
		
		if ($key == $keyval1){
			$weekot[$value[11]] = array(
				'otid' => $key,
				'winteam' => $value[11],
				'doubleot' => $value[12],
				 $value[1] => array(
						'QB' => $value[3],
						'RB' => $value[4],
						'WR' => $value[5],
						'PK' => $value[6]
				),
				$value[2] => array(
						'QB' => $value[7],
						'RB' => $value[8],
						'WR' => $value[9],
						'PK' => $value[10]
				)
			);
		}
		
		if ($key == $keyval2){
			$weekot[$value[11]] = array(
				'otid' => $key,
				'winteam' => $value[11],
				'doubleot' => $value[12],
				 $value[1] => array(
						'QB' => $value[3],
						'RB' => $value[4],
						'WR' => $value[5],
						'PK' => $value[6]
				),
				$value[2] => array(
						'QB' => $value[7],
						'RB' => $value[8],
						'WR' => $value[9],
						'PK' => $value[10]
				)
			);
		}
		
		if ($key == $keyval3){
			$weekot[$value[11]] = array(
				'otid' => $key,
				'winteam' => $value[11],
				'doubleot' => $value[12],
				 $value[1] => array(
						'QB' => $value[3],
						'RB' => $value[4],
						'WR' => $value[5],
						'PK' => $value[6]
				),
				$value[2] => array(
						'QB' => $value[7],
						'RB' => $value[8],
						'WR' => $value[9],
						'PK' => $value[10]
				)
			);
		}
		
	}
		
	//printr($weekot, 0);
	
	
	// for previous and next navigation
	$allweekids = the_weeks();
		
	$keyid = array_search($weekvar, $allweekids); 		
	$curr_id = $allweekids[$keyid];
	
	$next_id = $allweekids[$keyid + 1];
	$next_year = substr($next_id, 0, 4);
	$next_week = substr($next_id, -2);
	
	$prev_id = $allweekids[$keyid - 1];
	$prev_year = substr($prev_id, 0, 4);
	$prev_week = substr($prev_id, -2);
	
	
	// need to replace the following with connecttions to team database.  This is last step in weekly conversion from cache to databse model.

// need to set these as transients because they seem to be super memory intensive
function set_ets_transient() {

  $transient = get_transient( 'ets_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('ETS');
    set_transient( 'ets_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$ets_f = set_ets_transient();


function set_pep_transient() {

  $transient = get_transient( 'pep_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('PEP');
    set_transient( 'pep_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$pep_f = set_pep_transient();


function set_wrz_transient() {

  $transient = get_transient( 'wrz_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('WRZ');
    set_transient( 'wrz_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$wrz_f = set_wrz_transient();	


function set_max_transient() {

  $transient = get_transient( 'max_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('MAX');
    set_transient( 'max_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$max_f = set_max_transient();


function set_son_transient() {

  $transient = get_transient( 'son_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('SON');
    set_transient( 'son_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$son_f = set_son_transient();
	

function set_phr_transient() {

  $transient = get_transient( 'phr_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('PHR');
    set_transient( 'phr_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$phr_f = set_phr_transient();	


function set_atk_transient() {

  $transient = get_transient( 'atk_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('ATK');
    set_transient( 'atk_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$atk_f = set_atk_transient();	


function set_hat_transient() {

  $transient = get_transient( 'hat_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('HAT');
    set_transient( 'hat_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$hat_f = set_hat_transient();


function set_cmn_transient() {

  $transient = get_transient( 'cmn_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('CMN');
    set_transient( 'cmn_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$cmn_f = set_cmn_transient();

function set_bul_transient() {

  $transient = get_transient( 'bul_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('BUL');
    set_transient( 'bul_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$bul_f = set_bul_transient();


function set_snr_transient() {

  $transient = get_transient( 'snr_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('SNR');
    set_transient( 'snr_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$snr_f = set_snr_transient();


function set_tsg_transient() {

  $transient = get_transient( 'tsg_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('TSG');
    set_transient( 'tsg_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$tsg_f = set_tsg_transient();


function set_bst_transient() {

  $transient = get_transient( 'bst_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('BST');
    set_transient( 'bst_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$bst_f = set_bst_transient();

function set_rbs_transient() {

  $transient = get_transient( 'rbs_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('RBS');
    set_transient( 'rbs_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$rbs_f = set_rbs_transient();


function set_dst_transient() {

  $transient = get_transient( 'dst_transient' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results_expanded('DST');
    set_transient( 'dst_transient', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$dst_f = set_dst_transient();

	// printr($dst_f, 0);
	
	//printr($rbs_f, 0);

	$etsweek = $ets_f[$weekvar];
	$pepweek = $pep_f[$weekvar];
	$wrzweek = $wrz_f[$weekvar];
	$maxweek = $max_f[$weekvar];
	
	$sonweek = $son_f[$weekvar];
	$phrweek = $phr_f[$weekvar];
	$atkweek = $atk_f[$weekvar];
	$hatweek = $hat_f[$weekvar];
	
	$cmnweek = $cmn_f[$weekvar];
	$bulweek = $bul_f[$weekvar];
	$snrweek = $snr_f[$weekvar];
	$tsgweek = $tsg_f[$weekvar];
	
	$bstweek = $bst_f[$weekvar];
	$rbsweek = $rbs_f[$weekvar];

	$dstweek = $dst_f[$weekvar];
	//printr($rbsweek, 0);
	
	$gameswhere = array(
		array('ETS', $etsweek[venue]),
		array('PEP', $pepweek[venue]),
		array('RBS', $rbsweek[venue]),
		array('WRZ', $wrzweek[venue]),	
		array('CMN', $cmnweek[venue]),
		array('BUL', $bulweek[venue]),
		array('SNR', $snrweek[venue]),
		array('TSG', $tsgweek[venue]),
		array('BST', $bstweek[venue]),
		array('ATK', $atkweek[venue]),
		array('HAT', $hatweek[venue]),
		array('PHR', $phrweek[venue]),
		array('SON', $sonweek[venue]),
		array('MAX', $maxweek[venue]),
		array('DST', $dstweek[venue])
	);
	
	//printr($gameswhere, 0);
	
	$hometeams = array();
	$r = 0;
	while($r < 14){
		if ($gameswhere[$r][1] == 'H'){
			$hometeams[] = $gameswhere[$r][0];
			$r++;
		} else {
			$r++;
		}
	
	}
	

	// IMPORTANT!!  Don't erase old cached files.... this section below relies on cached text files for years 1991 - 2015.  After that the data is pulled from the database.  
	
	$boxscorecache = array();
	
	if ($year_sel < 2016){
		$boxscorecache = 'http://posse-football.dev/wp-content/themes/tif-child-bootstrap/cache/boxscores/'.$weekvar.'box.txt';
		$boxscoreget = @file_get_contents($boxscorecache, FILE_USE_INCLUDE_PATH);
		$boxscoredata = @unserialize($boxscoreget);
		$boxscorecount = @count(array_keys($boxscoredata));
		
		foreach ($boxscoredata as $buildboxreg){
			if ($buildboxreg[4] == 0){
				$boxscoredata_reg[] = array($buildboxreg[0],$buildboxreg[1],$buildboxreg[2],$buildboxreg[3]);	
			}	
		}
		
// 		printr($boxscoredata_reg, 0);
	
	} else {
		$boxscorecache = '';
		//need to get all player data.
		
		echo 'AFTER 2016';
		
		get_cache('allplayerdata', 0);	
	    $allplayerdata = $_SESSION['allplayerdata'];
	
		
				
 	//printr($allplayerdata, 1);
		
	}

	
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
							$r = 0;
							$week = 'week';
							if ($year_sel <= 1994){
								$yearcount = 4;
							} else {
								if ($year_sel >= 2010){
									$yearcount = 6;	
								} else {
									$yearcount = 5;
								}
							}
							
							while ($r < $yearcount){				
								$lower = strtolower($hometeams[$r]);
								$home = $teamids[$hometeams[$r]];
								$homepoints = ${$lower.'week'}[points];
								$away = $teamids[${$lower.'week'}[versus]];
								$vspoints = ${$lower.'week'}[vspts];
 								$stadium = $teaminfo[$hometeams[$r]];
 								$stad = $stadium['stadium'];
								$team01 = strtolower($hometeams[$r]);
								$team02 = strtolower(${$lower.'week'}[versus]);
								$upteam01 = strtoupper($team01);
								$upteam02 = strtoupper($team02);
								$differential = $homepoints - $vspoints;
								$totalgamescore = $homepoints + $vspoints;
								
								echo '<div class="col-xs-24 col-sm-12 col-md-8">
									<div class="panel panel-bordered panel-dark">
									<div class="panel-heading">
										<div class="panel-control">
											<em><small class="text-muted">Location: </small>'.$stad.'</em>
										</div>
									</div>';
									
								echo '<div class="panel-body">';
									
									if ($homepoints > $vspoints){
										echo '<span class="text-2x text-bold">'.$home.'</span>  <span class="text-2x pull-right text-bold">'.$homepoints.'</span><br>';
										echo '<span class="text-2x">'.$away.'</span>  <span class="text-2x pull-right">'.$vspoints.'</span><br>';
									} else {
										echo '<span class="text-2x">'.$home.'</span>  <span class="text-2x pull-right">'.$homepoints.'</span><br>';
										echo '<span class="text-2x text-bold">'.$away.'</span>  <span class="text-2x pull-right text-bold">'.$vspoints.'</span><br>';
									}						
								$r++;
								
								
								
								echo '<hr/><h5>Boxscores</h5>';
						// boxscore left image
								echo '<div class="col-xs-12 team-bar boxscorebox" style="background-image:url('.get_stylesheet_directory_uri().'/img/'.$team01.'-bar.png);">';
								echo '</div>';
								
						// boxscore right image	
								echo '<div class="col-xs-12 team-bar" style="background-image:url('.get_stylesheet_directory_uri().'/img/'.$team02.'-bar.png);">';
								echo '</div>';
								
							
							// boxscore left players
									echo '<div class="col-xs-12 boxscorebox">';
										
										$theqb = 'None<br>';
										$therb = 'None<br>';
										$thewr = 'None<br>';
										$thepk = 'None<br>';
										
										
										
										$teambox01 = array();
										
										foreach ($boxscoredata_reg as $theboxes){
								
												$player = $theboxes[0];
												$score = $theboxes[2];
												$first = $playernames[$player][0];
												$last = $playernames[$player][1];
												$theplayer = $first.' '.$last;
												$regulation = $theboxes[4];
											
											if (strlen($theplayer) > $namenum){
												$theplayer = substr($first, 0, 1).'. '.$last;
											} 
											
											
											$position = $playernames[$player][3];
												if ($theboxes[1] == $upteam01 && $theboxes[3] == 'QB'){
													$theqb = $theplayer.'</a><span class="pull-right">'.$score.'</span><br>';
													$boxqb = array($player, $score);
												} 
												if ($theboxes[1] == $upteam01 && $theboxes[3] == 'RB'){
													$therb = $theplayer.'<span class="pull-right">'.$score.'</span><br>';
													$boxrb = array($player, $score);
												} 
												if ($theboxes[1] == $upteam01 && $theboxes[3] == 'WR'){
													$thewr = $theplayer.'<span class="pull-right">'.$score.'</span><br>';
													$boxwr = array($player, $score);
												} 
												if ($theboxes[1] == $upteam01 && $theboxes[3] == 'PK'){
													$thepk = $theplayer.'<span class="pull-right">'.$score.'</span><br>';
													$boxpk = array($player, $score);
												} 
												
												$gamebox01[$upteam01] = array("QB" => $boxqb, "RB" => $boxrb, "WR" => $boxwr, "PK" => $boxpk);
												
																				
	
											}
											
											echo $theqb;
											echo $therb;
											echo $thewr;
											echo $thepk;
											
											$grandslam01 = 0;
											
											if ($gamebox01[$upteam01][QB][1] > 9){
												if ($gamebox01[$upteam01][RB][1] > 9){
													if ($gamebox01[$upteam01][WR][1] > 9){
														if ($gamebox01[$upteam01][PK][1] > 9){
															$grandslam01 = 1;
														}
													}
												}
											}	
											
/*
											echo '<pre>';
												print_r($gamebox01[$upteam01]);
											echo '</pre>';
*/
												
											$theqb = 'None<br>';
											$therb = 'None<br>';
											$thewr = 'None<br>';
											$thepk = 'None<br>';
										
										
									echo '</div>';	
									
							// boxscore right players
									echo '<div class="col-xs-12">';
										
										$theqb_r = 'None<br>';
										$therb_r = 'None<br>';
										$thewr_r = 'None<br>';
										$thepk_r = 'None<br>';
										
										foreach ($boxscoredata_reg as $theboxes){
											
												$player = $theboxes[0];
												$score = $theboxes[2];
												$first = $playernames[$player][0];
												$last = $playernames[$player][1];
												$theplayer = $first.' '.$last;
												
											
											if (strlen($theplayer) > $namenum){
												$theplayer = substr($first, 0, 1).'. '.$last;
											} 
											
												if ($theboxes[1] == $upteam02 && $theboxes[3] == 'QB'){
													$theqb_r = $theplayer.'<span class="pull-right">'.$score.'</span><br>';
													$boxqb_r = array($player, $score);
												} 
												if ($theboxes[1] == $upteam02 && $theboxes[3] == 'RB'){
													$therb_r = $theplayer.'<span class="pull-right">'.$score.'</span><br>';
													$boxrb_r = array($player, $score);
												} 
												if ($theboxes[1] == $upteam02 && $theboxes[3] == 'WR'){
													$thewr_r = $theplayer.'<span class="pull-right">'.$score.'</span><br>';
													$boxwr_r = array($player, $score);
												} 
												if ($theboxes[1] == $upteam02 && $theboxes[3] == 'PK'){
													$thepk_r = $theplayer.'<span class="pull-right">'.$score.'</span><br>';
													$boxpk_r = array($player, $score);
												} 
												$gamebox02[$upteam02] = array("QB" => $boxqb_r, "RB" => $boxrb_r, "WR" => $boxwr_r, "PK" => $boxpk_r);
												


											}
											
											echo $theqb_r;
											echo $therb_r;
											echo $thewr_r;
											echo $thepk_r;
											
											$grandslam02 = 0;
										
											if ($gamebox02[$upteam02][QB][1] > 9){
												if ($gamebox02[$upteam02][RB][1] > 9){
													if ($gamebox02[$upteam02][WR][1] > 9){
														if ($gamebox02[$upteam02][PK][1] > 9){
															$grandslam02 = 1;
														}
													}
												}
											}
											
										
											$theqb_r = 'None<br>';
											$therb_r = 'None<br>';
											$thewr_r = 'None<br>';
											$thepk_r = 'None<br>';
											
										
									echo '</div>';
											
								
								
								//overtime area 
										echo '<div class="overtime">';
										
										$otcheck1 = $weekot[$upteam01];	
										$otcheck2 = $weekot[$upteam02];	
										
										if(!empty($otcheck1)){
											$otbox = $otcheck1;
										} else {
											$otbox = $otcheck2;
										}
										
// 										
										
										if (!empty($otbox)){
											echo '<span class="text-bold">Overtime Game</span><br>';
											foreach ($otbox as $key => $value){
												$teamot[] = $value; 
											}
																				
										echo $upteam01.' - '.$teamot[3]['QB'].', '.$teamot[3]['RB'].', '.$teamot[3]['WR'].', '.$teamot[3]['PK'].'<br>';
										echo $upteam02.' - '.$teamot[4]['QB'].', '.$teamot[4]['RB'].', '.$teamot[4]['WR'].', '.$teamot[4]['PK'];
										
										}

											
										echo '</div>';
										
										
									
																		
									// game notes area 

											
									echo '<div class="clear"></div>
									<div class="notes-area">';
									echo 'Game Notes:<br>';
									
									// point differential 
									if ($differential > 0){
										echo '<span class="text-bold">'.$home.'</span> by '.$differential.' ';
									} else {
										echo '<span class="text-bold">'.$away. '</span> by '.abs($differential).' ';
									}
									
									if ($differential > 20 or abs($differential) > 20){
										echo ' in a Blowout!&emsp;';
									}
									
									if ($totalgamescore > 99){
										echo 'Barnburner!&emsp;';
									}
									
									if ($totalgamescore < 40 && $year_sel > 1991){
										echo ' in a BS Win. ';
									}
									
									if ($homepoints >= 50 && $homepoints < 60){
										echo '<span class="text-bold">'.$home.'</span> with 50+ points.&emsp;';
									}
									
									if ($vspoints >= 50 && $vspoints < 60){
										echo '<span class="text-bold">'.$away.'</span> with 50+ points.&emsp;';
									}
									
									if ($homepoints >= 60 && $homepoints < 70){
										echo '<span class="text-bold">'.$home.'</span> with 60+ points!&emsp;';
									}
									
									if ($vspoints >= 60 && $vspoints < 70){
										echo '<span class="text-bold">'.$away.'</span> with 60+ points!&emsp;';
									}
									
									if ($homepoints >= 70){
										echo '<span class="text-bold">'.$home.'</span> with 70+ points!&emsp;';
									}
									
									if ($vspoints >= 70){
										echo '<span class="text-bold">'.$away.'</span> with 70+ points!&emsp;';
									}
									
									echo '<br>';
									
									if ($grandslam01 == 1){
										echo '<span class="text-bold">GRANDSLAM</span> for the '.$home.'! ';
									}
									
									if ($grandslam02 == 1){
										echo '<span class="text-bold">GRANDSLAM</span> for the '.$away.'! ';
									}
									
									// end notes area
									echo '</div> 
									
									</div>
								
								
								<div class="panel-footer">
								</div>
								</div>
								</div>';
								
								// clearfix on different device sizes
								// clearfix on different device sizes
								if ($r % 2 == 0){
									echo '<div class="clearfix visible-sm-block"></div>';
								}
								if ($r % 3 == 0){
									echo '<div class="clearfix visible-md-block visible-lg-block"></div>';
								}
																
							}
										
						?>
							
					</div>
					
				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->
			
			<?php include_once('main-nav.php'); ?>
			<?php include_once('aside.php'); ?>

		</div>		
		

<!-- 	<?php	printr($boxscoredata_ot, 0); ?> -->
		
		
</div> 

<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>