<?php
/*
 * Template Name: Player Page
 * Description: Page for displaying player profiles -- should be added into a modal
 */
 ?>

<!-- necessary cache fies are pulled in via the 'pointsleader' function in functions.php -->

<?php get_header(); ?>

<!-- SET GLOBAL PLAYER VAR -->
<?php 
	
// $playerid = '2011SproRB';
$playerid = $_GET['id'];

$year = date("Y");

$teamids = $_SESSION['teamids'];

$o = 1991;
while ($o < $year){
	$theseasons[] = $o;
	$o++;
}



$games = $playercount;

$getpoints = array();
$getteams = array();

$teaminfo = get_teams();

$teams = array_unique($getteams);

if ($lastseason == $year){
	$active = 1; 
} else {
	$active = 0;
}

?>


<!-- AREA WHERE WE REPLACE CACHED WITH DB -->

<?php 
$players = get_players_assoc();
$i = 0;
foreach ($players as $key => $value){
	$playersid[] = $key;
}
/*
echo $i;
printr($playersid, 1);
*/

// this function will insert players seasonal data to wp_allleaders
insert_wp_career_leaders($playerid);
insert_wp_season_leaders($playerid);

// printr($players, 0);

$firstname = $players[$playerid][0];
$lastname = $players[$playerid][1];
$playerposition = $players[$playerid][2];
$rookieyear = $players[$playerid][3];
$mflid = $players[$playerid][4];
$height = $players[$playerid][5];
$weight = $players[$playerid][6];
$college = $players[$playerid][7];
$birthdate = $players[$playerid][8];
$playernumber = $players[$playerid][9];


$weeklydata = get_player_data($playerid);
$careerdata = get_player_career_stats($playerid);
$playoffsplayer = playerplayoffs($playerid);

printr($careerdata, 0);

if(!empty( $careerdata['years'])){
	$playseasons = $careerdata['years'];
} else {
	// set to zeros for players who played in the postseason but not regular season
	$playseasons = array(
/*
		'games' => 0,
		'points' => 0,
		'ppg' => 0,
		'seasons' => 1,
		'high' => 0,
		'low' => 0,
		'wins' => 0,
		'loss' => 0
*/
	);
}
														

if (!empty($playoffsplayer)){
	foreach ($playoffsplayer as $getplay){
		$playpts[] = $getplay['points'];

		if ($getplay['year'] == $checkyear){
			$plwins++;
		}
		$checkyear = $getplay['year'];
	}
}




if($playpts > 0){
	$totalplayoffpts = array_sum($playpts);
	$playoffgames = count($playpts);
	$playppg = number_format($totalplayoffpts / $playoffgames, 1);
	
}

$champions = get_champions();
$justchamps = get_just_champions();
$table = get_table('playoffs');

// sets an enhanced value for players with more complex careers.  It displays tables, timelines, etc
if ($careerdata['seasons'] > 3){
		$enhanced = 1;
	} else {
		$enhanced = 0;
}	


// get Posse Bowl Apperances
if(!empty($playoffsplayer)){
	foreach($playoffsplayer as $key => $value){
		if ($value['week'] == '16'){
			$pb_apps[$value['year']] = $value['team'];	
		}
	}
}

// printr($bp_apps, 0);

// get Posse Bowl Wins 

foreach ($justchamps as $key => $possebowls){
	if ($possebowls == $pb_apps[$key]){
		$pbwins[$key] = $possebowls;
		$pbwins_index[$key] = 1;
	}
}

//printr($pbwins, 0);

$titlewon = 0;
$counttitles = count($pbwins);
if ($counttitles > 0){
	$titlewon = 1;
}

$pswins = $plwins + $counttitles;
$psloss = $playoffgames - $pswins;

// printr($pb_apps, 0);

$awards = get_player_award($playerid);
$wonaward = 0;
$countawards = count($awards);
if ($countawards > 0){
	$wonaward = 1;
}

function in_array_r($needle, $haystack, $strict = false) {
	if (!empty($haystack)){
	    foreach ($haystack as $item) {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
	            return true;
	        }
	    }
	}
    return false;
}

if(in_array_r('Hall of Fame Inductee', $awards, false)){
	$inhall = 1;
} else {
	$inhall = 0;
}

// set up arrays for leader rankings
$leaders = get_allleaders();

foreach($leaders as $key => $item){
   $arr_position[$item['position']][$key] = $item;
}

ksort($arr_position, SORT_NUMERIC);

$qb_leaders = $arr_position['QB'];
$rb_leaders = $arr_position['RB'];
$wr_leaders = $arr_position['WR'];
$pk_leaders = $arr_position['PK'];

function sortByOrder($a, $b) {
    return $b['points'] - $a['points'];
}

usort($qb_leaders, 'sortByOrder');
usort($rb_leaders, 'sortByOrder');
usort($wr_leaders, 'sortByOrder');
usort($pk_leaders, 'sortByOrder');

foreach ($qb_leaders as $key => $value){
	$qbrank[$value['pid']] = $key + 1;
}
foreach ($rb_leaders as $key => $value){
	$rbrank[$value['pid']] = $key + 1;
}
foreach ($wr_leaders as $key => $value){
	$wrrank[$value['pid']] = $key + 1;
}
foreach ($pk_leaders as $key => $value){
	$pkrank[$value['pid']] = $key + 1;
}

$number_ones = get_number_ones();
foreach ($number_ones as $key => $value){
	if ($value['playerid'] == $playerid){
		$season = substr($value['id'], -4);
		$player_number_ones[$key] = array(
			'season' => $season,
			'points' => $value['points'],
			'team' => $value['teams']
		);
	}
}


// build the array for constructing the career timeline

$get_player_teams = get_player_teams_season($playerid);
//printr($get_player_teams, 0);

if (($year - end($playseasons)) > 3){
	$year_retired = end($playseasons);
} else {
	$year_retired = '';
}

if(!empty($awards)){
	foreach ($awards as $key => $value){
		$build_awards[$value['awardid']] = array(
			'year' => $value['year'],
			'award' => $value['award']
		);
	}
}


// protections, drafts, trades
$protections = get_protections_player($playerid);
$drafts = get_drafts_player($playerid);

if(!empty($drafts)){
	foreach($drafts as $value){
		$build_draft[$value['season']] = array(
		'team' => $value['acteam'],
		'round' => $value['round'],
		'pick' => $value['pick'],
		'overall' => $value['overall']
		);
	}
}

if(!empty($protections)){
	foreach($protections as $value){
		$build_protect[$value['year']] = $value['team'];
	}
}

foreach($playseasons as $season ){
	
	if($season == $rookieyear){
		$get_rook = $season;
	} 
	
	if($pbwins_index[$season] == 1){
		$get_champs = $season;
	}
	
	if($season == $year_retired){
		$get_retired = $season;
	}
	
	if(!empty($build_awards)){
		foreach ($build_awards as $key => $value){
			if ($value['year'] == $season){
				$get_awards[] = $value['award'];
			}
		}
	}
	
	if(!empty($player_number_ones)){
		foreach ($player_number_ones as $key => $value){
			if($value['season'] == $season){
				$get_leaders = $value['points'];
			}
		}
	}
	
	$career_timeline[$season] = array(
		'rookie' => $get_rook,
		'teams' => $get_player_teams[$season],
		'pfltitle' => $get_champs,
		'leader' => $get_leaders,
		'awards' => $get_awards,
		'drafted' => $build_draft[$season],
		'protected' => $build_protect[$season],
		'careerhigh' => '',
		'retired' => $get_retired
	);
	
	$get_rook = '';
	$get_awards = '';
	$get_champs = '';
	$get_leaders = '';
}

//printr($career_timeline, 0);

//$simpleteam = get_team_results_expanded('ETS');

//$masterschedlue = master_schedule();

//$data = just_team_record('ETS', '199201');

//$data = gettheweek($playerid);

//$data = get_player_record($playerid);

//$data = probowl_boxscores_player($playerid);

//insert data into tables to store .....





?>

			
<!--CONTENT CONTAINER-->
<div class="boxed add-to-top">

<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
	

	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">
		
		<div class="row">

		<!-- Left COL -->
		<div class="col-xs-24 col-sm-5 left-column">
			<div class="panel widget">
				<div class="widget-header bg-purple">
					<img src="<?php echo get_stylesheet_directory_uri();?>/img/players/<?php echo $playerid; ?>.jpg" class="widget-bg img-responsive">

				</div>
				<div class="widget-body text-center">
					<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $playerposition; ?>.jpg">
					<h3 class="" style="margin-bottom: 0;"><?php echo $firstname.' '.$lastname; ?></h3>
					<h4 class="mar-no text-sm">
						<?php 
							$prefix = '';
							foreach ($teams as $printteams) { 
								$teamList .= $prefix . '' . $teamids[$printteams];
								$prefix = ', ';
							} 
							
							echo $teamList;
							
							?>
					</h4>
					
					<?php if (!empty($weight)){ ?>
					<h3 class="text-bold mar-btm" style="margin-top: 0;"><?php echo $playernumber; ?></h3> 
					
					<p class="mar-btm">
						<span class="text-muted">IDs: </span><?php echo $playerid; ?><?php 
							if(!empty($mflid)){
								echo ' / '.$mflid;
							} ?>
						</span><br/>
						<span class="text-muted">College: </span><span class="text-bold"><?php echo $college; ?></span><br/>
						<span class="text-muted">Height: </span><span class="text-bold"><?php echo $height; ?></span><br/>
						<span class="text-muted">Weight: </span><span class="text-bold"><?php echo $weight; ?></span><br/>
						<span class="text-muted">Birthdate: </span><span class="text-bold"><?php echo $birthdate; ?></span><br/>
						
					<?php } ?>
					</p>					
				</div>
			</div>
		
			<!-- only if player is in hall of fame -->
			<?php if($inhall == 1){ ?>
			<div class="panel widget">
				<div class="widget-body text-center">
					<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/award-hall.jpg">
					
					<?php 
						foreach ($awards as $getaward){
							if ($getaward['award'] == 'Hall of Fame Inductee'){
								echo '<h4>'.$getaward['year'].'</h4><h5>Hall of Fame Inductee</h5>';
							}	
						}
						
					?>
				</div>
			</div>
			<?php } ?>
		
		
			<!-- only if player won title -->
			<?php if($titlewon == 1){ ?>
			<div class="panel widget">
				<div class="widget-body text-center">
					<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/award-trophy.jpg">
					<h4>Posse Bowls</h4>	
					<?php 
						foreach ($pbwins as $key => $value){
							echo $key.' PFL Champion<br/>';
						}
					?>
					<p></p>
					<p>Appearances: <span class="text-bold"><?php 
						foreach($pb_apps as $key => $value){
							echo $key.' ';
						}
						?></span></p>
				</div>
			</div>
			<?php } ?>
			
			<!-- only if player won awards -->
			<?php if($wonaward == 1){ ?>
			<div class="panel widget">
				<div class="widget-body text-center">
					<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/award-leaders.jpg">
					<h4>Awards</h4>
					<?php 
						foreach ($awards as $getaward){
							echo '<span class="text-bold">'.$getaward['year'].'</span> '.$getaward['award'].'<br>';
						}
						
					?>
				</div>
			</div>
			<?php } ?>
		
			
			<!-- only if player was league leading scorer -->
			
			<?php 
				$ranker = '';
				if ($playerposition == 'QB'){ 
					$ranker = $qbrank[$playerid]; 
					$posaction = 'Passing';
					}
				if ($playerposition == 'RB'){ 
					$ranker = $rbrank[$playerid];
					$posaction = 'Rushing'; 
					}
				if ($playerposition == 'WR'){ 
					$ranker = $wrrank[$playerid]; 
					$posaction = 'Receiving';
					}
				if ($playerposition == 'PK'){ 
					$ranker = $pkrank[$playerid];
					$posaction = 'Kicking'; 
					}
			?>
			
			<div class="panel widget">
				<div class="widget-body text-center">
					<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/award-top-scorer.jpg">
					<?php echo '<h5>Overall Career<br>Position Rank</h5><h4>'.$ranker.'</h4>'; 
						echo '<p>&nbsp;</p>';
						if(!empty($player_number_ones)){
							foreach ($player_number_ones as $key => $value){
								echo '<span class="text-bold">'.$value['season'].' '.$value['team'].'</span>&emsp;'.$posaction. ' Title - '.$value['points'].' Points<br>';
							}
						} else {
							echo 'N/A';
						}
						
					?>
				
				
				</div>
			</div>
			
			
			

			
			<!-- only if probowl selections exsist -->
			<?php $probowlplayer = probowl_boxscores_player($playerid);
			
			if(!empty($probowlplayer)){?>	
			
			<div class="panel widget">
				<div class="widget-body text-center probowl">
					<h5>Probowl Selections</h5>
					<?php 
						foreach ($probowlplayer as $key => $value){
								echo '<span class="text-bold">'.$value['year'].'</span> Probowl Selection<br>';
						}
					?>
				</div>
			</div>
			<?php } ?>
			
			
		</div>
		
				
		
	
		
		
		<!-- Right COL -->
		<div class="col-xs-24 col-sm-18 col-md-12">
		<div class="panel">

			<!--Panel heading-->
			<div class="panel-heading">
				<div class="panel-control">
					<button class="btn btn-default" data-target="#demo-panel-collapse" data-toggle="collapse" aria-expanded="true"><i class="fa fa-chevron-down"></i></button>
					<!-- <button class="btn btn-default" data-dismiss="panel"><i class="fa fa-times"></i></button> -->
				</div>
				<h3 class="panel-title">Career Highlights</h3>
			</div>

			<!--Panel body-->
			<div id="demo-panel-collapse" class="collapse in" aria-expanded="true">
				<div class="panel-body">
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
							<span class="text-2x text-thin"><?php echo number_format($careerdata['points']);?></span>
								<p class="text-white">Points</p>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin"><?php 
									if(!empty($careerdata['games'])){
										echo $careerdata['games']; 
									} else {
										echo 0;
									}
								?></span>
								<p class="text-white">Games</p>
							</div>
						</div>
					</div>	
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin"><?php 
									if(!empty($careerdata['ppg'])){
										echo $careerdata['ppg'];
									} else {
										echo '0.0';
									}
								?></span>
								<p class="text-white">PPG</p>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin"><?php 
									if(!empty($careerdata['seasons'])){
										echo $careerdata['seasons']; 
									} else {
										echo 0;
									}
								
								?></span>
								<p class="text-white">Seasons</p>
								<p class="text-white"></p>
								
								
								
							</div>
						</div>
					</div>
					
					<!-- row 2 -->
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<?php
								// get the function from functions.  This is delicate and has a bunch of dependancies including transients for team set on the homepage.php
								
								$playerrecord = get_player_results($playerid); 
								
								if(!empty($playerrecord)){
									foreach ($playerrecord as $value){
										$get_wins[] = $value['result'];
									}
								
									$player_wins = $careerdata['wins'];
									$player_losses = $careerdata['loss'];
									$winper = number_format($player_wins / $careerdata['games'], 3);
								}
								?>
								
								
								<span class="text-player-data text-thin"><?php echo $player_wins.' - '.$player_losses; ?></span>
								<p class="text-white">Record</p>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-player-data text-thin">
									<?php 
										if(!empty($winper)){
											echo $winper; 
										} else {
											echo '0.000';
										}
									
									?>
									
								</span>
								<p class="text-white">Winning %</p>
							</div>
						</div>
					</div>	
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin"><?php 
									if(!empty($careerdata['high'])){
										echo $careerdata['high']; 
									} else {
										echo 0;
									}
								
								?></span>
								<p class="text-white">Game High</p>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin">
									<?php 
										$gamestreak = get_player_game_streak($playerid);
										echo $gamestreak;
									?></span>
								<p class="text-white">Game Streak</p>
							</div>
						</div>
					</div>
					
				</div>
				
			</div>
		</div>
		
		<!-- Start Playoffs -->	
		<?php if (!empty($playoffsplayer)){  ?>
		<div class="panel">
		    <div class="panel-heading">
			    <h3 class="panel-title">Postseason</h3>
		    </div>
		    
		    <div class="panel-body">
				<div class="col-xs-12 col-sm-8 col-md-6">
					<div class="panel panel-primary panel-colorful">
						<div class="pad-all text-center">
							<span class="text-2x text-thin"><?php echo $totalplayoffpts; ?></span>
							<p class="text-white">Playoff Points</p>
						</div>
					</div>
				</div>
				
				<div class="col-xs-12 col-sm-8 col-md-6">
					<div class="panel panel-primary panel-colorful">
						<div class="pad-all text-center">
							<span class="text-2x text-thin"><?php echo $playoffgames; ?></span>
							<p class="text-white">Playoff Games</p>
						</div>
					</div>
				</div>
				
				<div class="col-xs-12 col-sm-8 col-md-6">
					<div class="panel panel-primary panel-colorful">
						<div class="pad-all text-center">
							<span class="text-2x text-thin"><?php echo $playppg; ?></span>
							<p class="text-white">Playoff PPG</p>
						</div>
					</div>
				</div>
				
				<div class="col-xs-12 col-sm-8 col-md-6">
					<div class="panel panel-primary panel-colorful">
						<div class="pad-all text-center">
							<span class="text-2x text-thin"><?php echo $pswins.' - '.$psloss; ?></span>
							<p class="text-white">Playoff Record</p>
						</div>
					</div>
				</div>
		    </div>
						
		</div>
		<?php } ?>
					
				
			
		<!-- Center COL -->
			<div class="panel panel-primary">	
				<!--Panel heading-->
				<div class="panel-heading">
					<div class="panel-control">
	
						<!--Nav tabs-->
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#demo-tabs-box-1">By Season</a>
							</li>
							
							<li><a data-toggle="tab" href="#demo-tabs-box-2">By Game</a></li>
							
							<?php if (!empty($playoffsplayer)){  ?>
							<li><a data-toggle="tab" href="#demo-tabs-box-3">Postseason</a></li>
							<?php } ?>
						</ul>
	
					</div>
					<h3 class="panel-title">Career Details</h3>
				</div>
	
				<!--Panel body-->
				<div class="panel-body">
	
					<!--Tabs content-->
					<div class="tab-content">
						<div id="demo-tabs-box-1" class="tab-pane fade in active">
							<!--  TABLE START -->
								<div class="panel">
									
									<!-- Striped Table -->
									<!--===================================================-->
										<div class="table-responsive">
											<table class="table table-striped">
												<thead>
													<tr>
														<th>Year</th>
														<th>Team</th>
														<th class="text-center">Points</th>
														<th class="text-center">Games</th>
														<th class="text-center">PPG</th>
														<th class="text-center">High</th>
<!-- 														<th class="hidden-xs">Acquisition</th> -->
													</tr>
												</thead>
												<tbody>
													
												
														
														<?php 
														
														
														//printr($playseasons, 0);
	
															foreach ($playseasons as $printyear) {
																
																$playerseasonstats = get_player_season_stats($playerid, $printyear);
																
																$ipoints = $playerseasonstats['points'];
																$igames = $playerseasonstats['games'];
																$ippg = number_format(($ipoints / $igames), 1);
																$ihigh = $playerseasonstats['high'];
																$theteams = $playerseasonstats['teams'];
																$steams = array_unique($theteams);
																
// 																printr($steams, 0);
																
																$teamsplayer = array($steams[0],$steams[1],$steams[2],$steams[3]);
/*
																$iteam1 = $steams[0]; 
																$iteam2 = $steams[1]; 
																$iteam3 = $steams[2];
																$iteam4 = $steams[3];
*/ 
																
																//printr($teamsplayer, 0);
																$seasonsList .= '<tr>';
																$seasonsList .= '<td class="text-bold">'.$printyear.'</td>';
																
/*
																$seasonsList .= '<td>'.$teamids[$iteam1].' ';
																$seasonsList .= $iteam2.' ';
																$seasonsList .= $iteam3.' ';
																$seasonsList .= $iteam4.'</td>';
*/
																$seasonsList .= '<td>';
																
																$countteams = count($teamsplayer);
																	
																if(empty($teamsplayer[1])){
																	$seasonsList .= $teamids[$teamsplayer[0]];
																} else {
																	foreach ($teamsplayer as $value){
																		if (!empty($value)){
																			$string .= $teamids[$value].', ';
																		}
																	}
																	$seasonsList .= substr($string, 0, -2);
																}
																
																
																
																$seasonsList .= '</td>';															
																$seasonsList .= '<td class="text-center">'.$ipoints.'</td>';
																$seasonsList .= '<td class="text-center">'.$igames.'</td>';
																$seasonsList .= '<td class="text-center">'.$ippg.'</td>';
																$seasonsList .= '<td class="text-center">'.$ihigh.'</td>';
// 																$seasonsList .= '<td class="hidden-xs"> NEED TO DO';
																	
																$seasonsList .= '</td>';
																$seasonsList .= '</tr>';
																
																$pointarray[$printyear] = $ipoints;
																
															}
														echo $seasonsList;
														
														?>
													
												</tbody>
											</table>
										</div>
									<!--===================================================-->
									<!-- End Striped Table -->
								</div>
							<!-- TABLE END -->
								
							<!-- Season Data Chart -->
							<?php if( $enhanced == 1) { ?>
							<div class="panel hidden-xs">
								<h4 class="text-thin">Points Over PFL Lifetime</h4>
								<hr>
								<span class="seasonbar">
									
									<?php
										//printr($pointarray, 1); 
										$seasons = the_seasons();
										
										$prefixc = '';
										foreach ($seasons as $printchart){
											$chartList .= $prefixc . '' . $pointarray[$printchart];
											$prefixc = ', ';
										}
										
										echo $chartList;
										
									?>
								</span><br/>
									
									<?php foreach ($seasons as $key => $printchart){
										$trimyear = substr($printchart, -2);
										echo '<span class="labelchart">'.$trimyear.'</span>';
										//echo '&bull;';
									}
									?>
							</div>
							<?php } else {
								echo null;	
							} ?>
							
							
							<!-- End Data Chart -->	
							
							
							
						</div>
						<div id="demo-tabs-box-2" class="tab-pane fade">
<!-- 							<h5 class="text-thin">Game Summary</h5> -->
							<!-- Striped Table -->
									<!--===================================================-->
										<div class="table-responsive">
											<table class="table table-striped">
												<thead>
													<tr>
														<th class="text-center">Year</th>
														<th class="text-center">Week</th>
														<th class="text-center">Team</th>
														<th class="text-center">Points</th>
														<th class="text-center">Versus</th>
														<th class="text-center">Result</th>
														<th class="hidden-xs">Location</th>
													</tr>
												</thead>
												<tbody>
												<?php 												
 														//printr($playerrecord, 0);
														$i = -1;
														
// 														printr($weeklydata,0);
														if(!empty($weeklydata)){
														foreach ($weeklydata as $printplayer) {
														
														
															//$pyearnext = $playerdata[$i][1];
															$weekids = $printplayer['weekids'];
															$pyear = $printplayer['year'];
															$pweek = $printplayer['week'];
															$ppoints = $printplayer['points'];
															$pteam = $printplayer['team'];
															$pversus = $printplayer['versus'];
															$pplayerid = $printplayer['playerid'];
															$presult = $printplayer['win_loss'];
															$phomeaway = $printplayer['home_away'];
															$plocation = $printplayer['location'];
														}
														
														if ($pyear != $checkyear){
															$gametable .= '<td class="text-center text-bold switch-year">'.$pyear.'</td>';
															$checkyear = $pyear;
														} else {
															$gametable .= '<td class="text-center">&emsp;</td>';
														}
														
														
														$gametable .= '<td class="text-center">'.$pweek.'</td>';
														$gametable .= '<td class="text-center">'.$pteam.'</td>';
														$gametable .= '<td class="text-center">'.$ppoints.'</td>';
														$gametable .= '<td class="text-center">'.$pversus.'</td>';
														
														
															if ($presult == '1'){
																$gametable .= '<td class="text-bold text-center">Win</td>';
															} else {
																$gametable .= '<td class="text-center">Loss</td>';
															}
															
															if ($phomeaway == 'H'){
																$gametable .= '<td><span class="text-bold ">'.$teaminfo[strtoupper($pteam)]['stadium'].'</span></td></tr>';
															} else {
																$gametable .= '<td>'.$teaminfo[strtoupper($pversus)]['stadium'].'</td></tr>';
															}
														$i++;
														}
										
														echo $gametable;
													?>													
												</tbody>
											</table>
										</div>
								
							</pre>
						</div>
						
						
						<div id="demo-tabs-box-3" class="tab-pane fade">
<!-- 							<h5 class="text-thin">Postseason Game Summary</h5> -->
								<!-- Striped Table -->
									<!--===================================================-->
										<div class="table-responsive">
											<table class="table table-striped">
												<thead>
													<tr>
														<th class="text-center">Year</th>
														<th class="text-center">Week</th>
														<th class="text-center">Team</th>
														<th class="text-center">Points</th>
														<th class="text-center">Versus</th>
														<th class="text-center">Result</th>
														
													</tr>
												</thead>
												<tbody>
													<?php
														foreach($champions as $key => $value){
															$pbteams_assoc[$key] = array(
																$value['winner'] => 1,
																$value['loser'] => 0
															);
															$pbteams_index[$key] = array(
																$value['winner'],
																$value['loser']															
															);
														}
														
														//printr($pbteams_index, 0);
														
														foreach ($playoffsplayer as $printplay) {
														
														
														
														$plyear = $printplay['year'];
														$plweek = $printplay['week'];
														$plpoints = $printplay['points'];
														$plteam = $printplay['team'];
														$plversus = $printplay['versus'];
														$plplayerid = $printplay['playerid'];
														$overtime = $printplay['overtime'];
														
														
														
														if ($plyear != $checkyear){
															$playtable .= '<td class="text-center text-bold switch-year">'.$plyear.'</td>';
															$checkyear = $plyear;
														} else {
															$playtable .= '<td class="text-center">&emsp;</td>';
														}
														if ($plweek == '15'){
															$playtable .= '<td class="text-center">Playoffs</td>';
															if(in_array($plteam, $pbteams_index[$plyear])){
																$plresult = 'Advanced';
															} else {
																$plresult = 'Lost';
															}
														} else {
															$playtable .= '<td class="text-center">Posse Bowl '.$champions[$plyear][1].'</td>';
															if ($pbteams_assoc[$plyear][$plteam] == 1 ){
																$plresult = '<b>Champion</b>';
															} else {
																$plresult = 'Lost';
															}
														}
														
														
														
														$playtable .= '<td class="text-center">'.$plteam.'</td>';
														$playtable .= '<td class="text-center">'.$plpoints.'</td>';
														$playtable .= '<td class="text-center">'.$plversus.'</td>';
														$playtable .= '<td class="text-center">'.$plresult.'</td></tr>';
														
														
												}	
												
												echo $playtable;

												?>	
												
												
												</tbody>
											</table>
										</div>

						</div>
				
					</div>
				</div>
			</div>
		</div>
		
		
		<!-- Right COL timeline -->
			<div class="hidden-xs hidden-sm col-md-7">
				
				
				<div class="panel">
					<div class="panel-body">

						<!-- Default choosen -->
						<!--===================================================-->
						<div class="row">
						
						<div class="col-xs-24 col-sm-18">
							<select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" tabindex="2" id="playerDrop">
								<option value=""></option>
								
								<?php 	
								foreach ($players as $key => $selectplayer){
									$firsto = $selectplayer[0];
									$lasto = $selectplayer[1];
									$printselect .= '<option value="/player/?id='.$key.'">'.$firsto.' '.$lasto.'</option>';
								}
								echo $printselect;
								?>
							</select>
							</div>
							<div class="col-xs-24 col-sm-6">
								<button class="btn btn-warning" id="playerSelect">Select</button>
							</div>
						</div>
						<!--===================================================-->

					</div>
				</div>
				
				
				
<!--
				<div class="panel">
					<div class="panel-body">
						<button class="btn btn-warning btn-labeled fa fa-user" onclick="randPlayer()">Random Player</button>
					</div>
				</div>
-->
				
				<?php if( $enhanced == 1) { ?>
				<div class="panel">
					<div class="panel-heading">
						<h3 class="panel-title">Career Timeline</h3>
					</div>
					<div class="panel-body">
						<!-- Timeline -->
						<!--===================================================-->
						
					
				        <div class="timeline">
						    <!-- Timeline header -->
<!--
						    <div class="timeline-header">
						        <div class="timeline-header-title bg-success">Rookie Year <?php echo $rookieyear; ?></div>
						    </div>
-->
						    
						    <?php 
							    $count = 0;
								foreach ($career_timeline as $key => $value){
								?>
								<!-- post the years -->
								<div class="timeline-entry">
									<div class="timeline-stat">
								        <div class="timeline-icon"></div>
								        <div class="timeline-time"><?php echo $key; ?></div> 
							        </div>
									
								</div>
								
								<?php
								if (isset($value['drafted'])){
								?>
								
								 <div class="timeline-entry">
							        <div class="timeline-label no-label">
							            <p class="protected-by">Drafted by <span class="text-bold"><?php echo $value['drafted']['team'].' Round '.$value['drafted']['round'].', Pick '.$value['drafted']['pick']; ?></span></p>
							        </div>
							    </div>
							    
							    <?php } 
								    
								if ($count == 0){
								?>
								
								 <div class="timeline-entry">
							        <div class="timeline-label">
							            Rookie Season
							        </div>
							    </div>
							    
							    <?php } 
							    
							    if (isset($value['protected'])){
								?>
								
								 <div class="timeline-entry">
									 
							        <div class="timeline-label no-label">
							            <p class="protected-by">Protected by <span class="text-bold"><?php echo $value['protected']; ?></span></p>
							        </div>
							    </div>
							    
							    <?php } 
							    
								
								if(!empty($value['awards'])){ ?>
								<div class="timeline-entry">
							        <div class="timeline-stat">
							            <div class="timeline-icon bg-success">
								            <img class="" src="https:/wp-content/themes/tif-child-bootstrap/img/award-leaders.jpg" />
							            </div>
							            <div class="timeline-time"><?php $getaward['year']; ?></div>
							        </div>
							        <?php 
								        foreach($value['awards'] as $car_award){
							       ?>
							        <div class="timeline-label">
							            <?php echo '<span class="text-bold">'.$car_award.'</span>'; ?>
							        </div>
							        <?php
							        } ?>
						    	</div>
								<?php } 
								
								
								if(!empty($value['leader'])){ ?>
								<div class="timeline-entry">
							        <div class="timeline-stat">
							            <div class="timeline-icon bg-success">
								            <img class="" src="https:/wp-content/themes/tif-child-bootstrap/img/award-top-scorer.jpg" />
							            </div>
							            <div class="timeline-time"><?php $getaward['year']; ?></div>
							        </div>
							        <div class="timeline-label">
							            <?php echo '<span class="text-bold">'.$posaction.' Title</span> - '.$value['leader'].' Points'; ?>
							        </div>
						    	</div>
								<?php } 
										    
								    
								if(!empty($value['pfltitle'])){ ?>
								<div class="timeline-entry">
							        <div class="timeline-stat">
							            <div class="timeline-icon bg-success">
								            <img class="" src="https:/wp-content/themes/tif-child-bootstrap/img/award-trophy.jpg" />
							            </div>
							            <div class="timeline-time"><?php $getaward['year']; ?></div>
							        </div>
							        <div class="timeline-label">
							            <?php echo '<span class="text-bold">PFL CHAMPION</span>'; ?>
							        </div>
						    	</div>
								<?php } 
 
						    		$count++;
								}  
								?>
<!-- 						 HOF and Retired can be outside of foreach career_timeline loop -->
								
								<?php if($year_retired > 3){ ?>
								 <div class="timeline-entry">
							        <div class="timeline-label">
							            Retired from PFL
							        </div>
							    </div>
								<?php }
								
							    if($inhall == 1){ ?>
								
								<div class="timeline-entry">
							        <div class="timeline-stat">
							            <div class="timeline-icon bg-success">
								            <img class="" src="https:/wp-content/themes/tif-child-bootstrap/img/award-hall.jpg" />
							            </div>
							            <div class="timeline-time"><?php $getaward['year']; ?></div>
							        </div>
							        <div class="timeline-label">
							            <?php echo '<span class="text-bold"></span>'.$getaward['year'].' Hall of Fame Inductee'; ?>
							        </div>
						    	</div>
								<?php }
						
								?>

						 						    
				        </div>
						
					           
					</div>
						<!--===================================================-->
						<!-- End Timeline -->
				</div>
				<?php } ?>
					
		</div>
		
			</div>
			
		</div>
		
		
	</div>
	<!--===================================================-->
	<!--End page content-->


</div>
<!--===================================================-->
<!--END CONTENT CONTAINER-->
<?php include_once('main-nav.php'); ?>		
</div>

			



<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>