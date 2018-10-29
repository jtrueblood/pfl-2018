<?php
/*
 * Template Name: Player Page
 * Description: Page for displaying player profiles
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

// printr($careerdata, 0);

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
		$plwinarr[] = $getplay['result'];
		$checkyear = $getplay['year'];
	}
}

//printr($playoffsplayer, 0);

if($playpts > 0){
	$totalplayoffpts = array_sum($playpts);
	$playoffgames = count($playpts);
	$playppg = number_format($totalplayoffpts / $playoffgames, 1);
}

$champions = get_champions();
$justchamps = get_just_champions();
$table = get_table('playoffs');

// sets an enhanced value for players with more complex careers.  It displays tables, timelines, etc

if ($careerdata['seasons'] > 2){
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

if(isset($plwinarr)){
	$pswins = array_sum($plwinarr);
}
$psloss = $playoffgames - $pswins;

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

//printr($build_draft, 0);

// get first and last player years in an array
$first = $playseasons[0];

$last = end($playseasons) + 1;

while ($first != $last){
	$buildtheyears[] = $first;
	$first++;
} 

// Trades by player
$playerbytrade = get_trade_by_player($playerid);
										

foreach($buildtheyears as $season ){
	
	$teams = $get_player_teams[$season];
	
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
	
	if(empty($teams)){
		$empty = 'Did Not Play';
	} else {
		$empty = '';
	}
	
	$career_timeline[$season] = array(
		'rookie' => $get_rook,
		'teams' => $teams,
		'pfltitle' => $get_champs,
		'leader' => $get_leaders,
		'awards' => $get_awards,
		'drafted' => $build_draft[$season],
		'protected' => $build_protect[$season],
		'traded' => $playerbytrade[$season],
		'careerhigh' => '',
		'retired' => $get_retired,
		'dnp' => $empty
	);
	
	$get_rook = '';
	$get_awards = '';
	$get_champs = '';
	$get_leaders = '';
}


//printr($playseasons, 0);

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
<div class="boxed">

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
				<div class="widget-header">
					
					<?php
					
						$playerimgobj = get_attachment_url_by_slug($playerid);
						$imgid =  attachment_url_to_postid( $playerimgobj );
						$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
						echo '<img src="'.$image_attributes[0].'" class="widget-bg img-responsive">';
						
						//printr($image_attributes, 0);
						
					?>

				</div>
				<div class="widget-body text-center">
					<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $playerposition; ?>.jpg">
					<h3 class="" style="margin-bottom: 0;"><?php echo $firstname.' '.$lastname; ?></h3>
					
					<?php if (!empty($weight)){ 
						echo '<div class="uniform">'.$playernumber.'</div> ';
					?>
					
					<p class="mar-btm">
						<span class="text-muted">IDs: </span><?php echo $playerid; ?><?php 
							if(!empty($mflid)){
								echo ' / '.$mflid;
							} ?>
						</span><br/>
						<span class="text-muted">College: </span><span class="text-bold"><?php echo $college; ?></span><br/>
						<span class="text-muted">Height: </span><span class="text-bold"><?php echo $height; ?></span><br/>
						<span class="text-muted">Weight: </span><span class="text-bold"><?php echo $weight; ?></span><br/>
<!-- 					<span class="text-muted">Birthdate: </span><span class="text-bold"><?php echo $birthdate; ?></span><br/> -->
						
						<h4 class="mar-no text-sm">
							<?php 
								$prefix = '';
								
								$teamall = get_player_record($playerid);
								//printr($teamall, 0);
								if(isset($teamall)){
									$teams = array_unique($teamall);
									foreach ($teams as $printteams) { 
										$teamList .= $prefix . '' . $teamids[$printteams];
										$prefix = ', ';
									} 
								}
								
								echo '<p class="text-muted teams-list">'.$teamList.'</p>';
								
							?>
						</h4>
						
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
														
 //														printr($playseasons, 0);
															
															
															if(isset($buildtheyears)){
																foreach ($buildtheyears as $printyear) {
																																		
																		$playerseasonstats_all[] = get_player_season_stats($playerid, $printyear);
																		$playerseasonstats = get_player_season_stats($playerid, $printyear);
																		
																		if(isset($playerseasonstats)){
																			$ipoints = $playerseasonstats['points'];
																			$igames = $playerseasonstats['games'];
																			$ippg = number_format(($ipoints / $igames), 1);
																			$ihigh = $playerseasonstats['high'];
																			$theteams = $playerseasonstats['teams'];
																			$iteams = array_unique($theteams);
																			$steams = array_values($iteams);
																			$teamsplayer = array($steams[0],$steams[1],$steams[2],$steams[3]);
																			$yearsplayed[] = $printyear;
																		
																			$seasonsList .= '<tr>';
																			$seasonsList .= '<td class="text-bold">'.$printyear.'</td>';
																					
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
																			
																				
																			$seasonsList .= '</td>';
																			$seasonsList .= '</tr>';
																			
																			$pointarray[$printyear] = $ipoints;		
																			
																		// condition if the player never played in a particular season	
																		} else {
																			$seasonsList .= '<tr>';
																			$seasonsList .= '<td class="text-bold">'.$printyear.'</td>';
																			$seasonsList .= '</td>';															
																			$seasonsList .= '<td class="text-bold">Did Not Play</td>';
																			$seasonsList .= '<td class="text-center">-</td>';
																			$seasonsList .= '<td class="text-center">-</td>';
																			$seasonsList .= '<td class="text-center">-</td>';
																			$seasonsList .= '<td class="text-center">-</td>';
																				
																			$seasonsList .= '</td>';
																			$seasonsList .= '</tr>';
																		}	
																	
																}
															}
															echo $seasonsList;
														
														?>
													
												</tbody>
											</table>
										</div>
									<!--===================================================-->
									<!-- End Striped Table -->
								</div>
								
				
							<!-- Try the Highchart-->
							<?php //printr($pointarray, 0);
							
								
							if($enhanced == 1){	
								$seasons = the_seasons();
								foreach ($seasons as $value){
									if (empty($pointarray[$value])){
										$theval = 0; 
									} else {
										$theval = $pointarray[$value];
									}
									$playerchartpts[$value] = $theval;
								}
								
								// get avarage arrays
								
								foreach($number_ones as $key => $item){
								   $arr_t_ones[$item['pos']][$key] = $item;
								}
								
								$qb_for_chart = $arr_t_ones['QB'];
								$rb_for_chart = $arr_t_ones['RB'];
								$wr_for_chart = $arr_t_ones['WR'];
								$pk_for_chart = $arr_t_ones['PK'];
								
								if($playerposition == 'QB'){
									foreach ($qb_for_chart as $value){
										$chart_data[$value['year']] = array(
											'high' => $value['points'],
											'avg' => $value['avg']	
										);
									}
								}
								
								if($playerposition == 'RB'){
									foreach ($rb_for_chart as $value){
										$chart_data[$value['year']] = array(
											'high' => $value['points'],
											'avg' => $value['avg']	
										);
									}
								}
								
								if($playerposition == 'WR'){
									foreach ($wr_for_chart as $value){
										$chart_data[$value['year']] = array(
											'high' => $value['points'],
											'avg' => $value['avg']	
										);
									}
								}
								
								if($playerposition == 'PK'){
									foreach ($pk_for_chart as $value){
										$chart_data[$value['year']] = array(
											'high' => $value['points'],
											'avg' => $value['avg']	
										);
									}
								}
								
 								//printr($chart_data, 0);
							?>
							<script type="text/javascript">
							jQuery(document).ready(function() {
								Highcharts.chart('testhighchart', {
								title: {
								        text: 'Career Points'
								    },
								    xAxis: {	    
								    crosshair: true,
								    allowDecimals: false,
								    labels: {
							            align: 'right',
							            reserveSpace: true,
							            rotation: 270
							        },
								        categories: [<?php 
									        foreach ($playerchartpts as $key => $value){
										        echo $key.',';
									        } 
									    ?>]
								    },
								    labels: {
								        items: [{
								            html: '',
								            style: {
								                top: '18px',
								                color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
								            },
								             
								        }]
								    },
								    series: [{
								        type: 'column',
								        name: 'Player Points',
								        data: [<?php 
									        foreach ($playerchartpts as $value){
										        echo $value.',';
									        } 
									    ?>]
								    },
								    	{
								        type: 'spline',
								        name: 'Position Average',
								        color: '#a1a1a1',
								        data: [<?php 
									        foreach ($chart_data as $key => $value){
										        echo $value['avg'].',';
									        } 
									    ?>],
								        marker: {
								            lineWidth: 2,
								            lineColor: '#a1a1a1',
								            fillColor: 'white'
								        }
								    },
								    	{
								        type: 'spline',
								        name: 'Position High Value',
								        color: '#eaa642',
								        data: [<?php 
									        foreach ($chart_data as $key => $value){
										        echo $value['high'].',';
									        } 
									    ?>],
								        marker: {
								            lineWidth: 2,
								            lineColor: '#eaa642',
								            fillColor: 'white'
								        }
								    }]
								});	
							});	
							</script>
							
							<div class="panel hidden-xs">
								<div id="testhighchart"></div> 
							</div>
							<?php } ?>
							
						</div>
						
						
						
						<div id="demo-tabs-box-2" class="tab-pane fade">
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
														<th class="hidden-xs" width="20">OT</th>
													</tr>
												</thead>
												<tbody>
												<?php 												
 														$playerot = get_player_overtime($playerid);
														$u = -1;
														
 														//printr($weeklydata,0);
													if(!empty($weeklydata)){
														
														
														$checkyear = $rookieyear - 1;
														
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
															$checkot = $playerot[$weekids];
														
															
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
																$ishome = $teaminfo[strtoupper($pteam)]['stadium'];
																// 	alter CMN stadium name based on year
																if ($ishome == 'Spankoni Center'){
																	if($pyear <= 2004){
																		$ishome = 'The Gonad Bowl';
																	}
																}
																// 	alter ETS stadium name based on year
																if ($ishome == 'The Woodshed'){
																	if($pyear <= 2017){
																		$ishome = 'Hutchence Field';
																	}
																}
																$gametable .= '<td><span class="text-bold ">'.$ishome.'</span></td>';
															} else {
																$isaway = $teaminfo[strtoupper($pversus)]['stadium'];
																if ($isaway == 'Spankoni Center'){
																	if($pyear <= 2004){
																		$isaway = 'The Gonad Bowl';
																	}
																}
																if ($isaway == 'The Woodshed'){
																	if($pyear <= 2017){
																		$isaway = 'Hutchence Field';
																	}
																}
																$gametable .= '<td>'.$isaway.'</td>';
															}
															
															if ($checkot == 1){
																$gametable .= '<td width="20px"><span class="text-semibold"><i class="fa fa-circle"></i></span></td></tr>';
															} else {
																$gametable .= '<td width="20px"></td></tr>';
															}
						
														$u++;
														}
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
														
														$checkyear = $rookieyear - 1;
														
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
				
				
				<?php if( $enhanced == 1) { ?>
				<div class="panel">
					<div class="panel-heading">
						<h3 class="panel-title">Career Timeline</h3>
					</div>
					<div class="panel-body">
						<!-- Timeline -->
						<!--===================================================-->
						
					
				        <div class="timeline">
						    
						    <?php 
							    $count = 0;
								foreach ($career_timeline as $key => $value){
								?>
								<!-- post the years -->
								<div class="timeline-entry">
									<div class="timeline-stat">
								        <div class="timeline-icon <?php echo 'val'.$count; ?>"></div>
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
								 
								// free agents
								if (empty($value['dnp'])){
									if(empty($value['drafted'])){
										if(empty($value['protected'])){
											include('inc/player_timeline_free_agent.php');
										}
									}
								}
								 
								// rookie season    
								if ($count == 0){
								echo '<div class="timeline-entry">
									<div class="timeline-label no-label">
							        	<p class="protected-by"><span class="text-bold">Rookie Season</p>
							        </div>
							    </div>';							    
							    } 
								 
								// did not play    
								if (!empty($value['dnp'])){
									include('inc/player_timeline_dnp.php');
								}    
								
								// protected (order based on season or preseason trade)
								if ($value['traded']['when'] == ''){
									include('inc/player_timeline_protected.php');
								}
								
								// traded player									
							    if (isset($value['traded'])){
								    
									$tradedto = $teamids[$value['traded']['traded_to_team']];
									$tradedfrom = $teamids[$value['traded']['traded_from_team']];
									$when = $value['traded']['when'];
									$alongwith_players = $value['traded']['received_players'];
									$alongwith_picks = $value['traded']['received_picks'];
									$sent_players = $value['traded']['sent_players'];
									$sent_picks = $value['traded']['sent_picks'];
									$notes = $value['traded']['notes'];
									
									$a_picks = implode( ", ", $alongwith_picks);
									$s_picks = implode( ", ", $sent_picks);
	
								?>
								
								 <div class="timeline-entry">
									 
							        <div class="timeline-label no-label">
							            <p class="protected-by"><span class="text-bold">
								            Traded to <?php echo $tradedto; ?></span> during the <?php echo $when; ?></p>
								        <p class="protected-by"><span class="text-bold"><?php echo $value['traded']['traded_to_team'];?></span> &mdash; Get <?php echo implode( ", ", $alongwith_players); ?> <?php echo $a_picks; ?> </p> 
										<p class="protected-by"><span class="text-bold"><?php echo $value['traded']['traded_from_team'];?></span> &mdash; Get <?php echo implode( ", ", $sent_players); ?> <?php echo $s_picks; ?>  
								        </p>
								         <p class="protected-by"><?php echo $notes; ?> </p>
							        </div>
							    </div>
							    
							    <?php } 
							    
							    if ($value['traded']['when'] == 'Preseason'){
									include('inc/player_timeline_protected.php');
								}
									
  
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
							            <?php echo '<span class="text-bold">PFL CHAMPION </span>'.$teamids[$justchamps[$key]];?>
							        </div>
						    	</div>
								<?php } 
 
						    		$count++;
								}  
								?>
<!-- 						 HOF and Retired can be outside of foreach career_timeline loop -->
								
								<?php if($year_retired > 3){ ?>
								 <div class="timeline-entry" style="margin-top:50px;">
							        <div class="timeline-label no-label">
							            <span class="text-bold">Retired</span> from PFL
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