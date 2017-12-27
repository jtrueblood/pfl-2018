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


get_cache('players', 0);	
$players = $_SESSION['players'];

get_cache('awards', 0);	
$awards = $_SESSION['awards'];

/*
get_cache('1991plbox', 0);	
$plbox = $_SESSION['1991plbox'];
*/

get_cache('playoffall', 0);	
$playoffall = $_SESSION['playoffall'];

get_cache('champions', 0);	
$champions = $_SESSION['champions'];

get_cache('protections', 0);	
$protections = $_SESSION['protections'];

get_cache('drafts', 0);	
$drafts = $_SESSION['drafts'];

get_cache('teaminfo', 0);	
$teaminfo = $_SESSION['teaminfo'];

get_cache('playersid', 0);	
$playersid = $_SESSION['playersid'];

get_cache('playersassoc', 0);	
$playersassoc = $_SESSION['playersassoc'];

$playerinfo = $playersassoc[$playerid];

@get_cache('mfl/linkidcache', 0);	
$linkid = $_SESSION['mfl/linkidcache'];

@get_cache('fantasydata/fantasydataids', 0);	
$fantasydataids = $_SESSION['fantasydata/fantasydataids'];

get_cache('playerdetails', 0);	
$playerdetails = $_SESSION['playerdetails'];

get_cache('postseasonbox/allpro', 0);	
$allpro = $_SESSION['postseasonbox/allpro'];


//mfl player profile news from API
foreach ($linkid as $getlinkid){
	if ($getlinkid[2] == $playerid){
		$mflid = $getlinkid[1];
	}
}
// $mflid = 2842;

/* HIDDEN  DONT NEED FOR THIS USING FANTASY CACHED DATA FOR PLAYER DETAILS INSTEAD
$jsonplayerprofile = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=playerProfile&L='.$mflleagueid.'&W=8&JSON=1&PLAYERS='.$mflid.'');
$mflplayerprofile = json_decode($jsonplayerprofile, true); 
*/


$first = $playerinfo[0];
$last = $playerinfo[1];
$position = $playerinfo[2];	
$rookie = $playerinfo[3];

$commaname = $last.', '.$first;	
$fantasydata_playerid = $fantasydataids[$commaname];	

get_cache('fantasydata/'.$fantasydata_playerid, 0);	
$fantasydata_details = $_SESSION['fantasydata/'.$fantasydata_playerid];

get_cache('rankyears/'.$position.'top', 0);	
$topscorer = $_SESSION['rankyears/'.$position.'top'];
				

if (!empty($fantasydata_details)){
	$det_nflteam = $fantasydata_details['Team'];
	$det_number = $fantasydata_details['Number'];
	$det_height = $fantasydata_details['Height'];
	$det_weight = $fantasydata_details['Weight'];
	$det_college = $fantasydata_details['College'];
	$det_bday = $fantasydata_details['BirthDate'];
	$det_age = date_diff(date_create($det_bday), date_create('today'))->y;
	$det_format_date = date("m/d/Y", strtotime($det_bday));
} else {
	$det_number = $playerdetails[$playerid]['Number'];
	$det_height = $playerdetails[$playerid]['Height'];
	$det_weight = $playerdetails[$playerid]['Weight'];
	$det_college = $playerdetails[$playerid]['College'];
	$det_bday = $playerdetails[$playerid]['Age'];
	$det_age = date_diff(date_create($det_bday), date_create('today'))->y;
	$det_format_date = date("m/d/Y", strtotime($det_bday));
}

	
$playercache = 'http://posse-football.dev/wp-content/themes/tif-child-bootstrap/cache/playerdata/'.$playerid.'.txt';
$playerget = file_get_contents($playercache, FILE_USE_INCLUDE_PATH);
$playerdata = unserialize($playerget);
$playercount = count(array_keys($playerdata));

$games = $playercount;

$getpoints = array();
$getteams = array();
foreach ($playerdata as $seasondata){
	$getpoints[] = $seasondata[3];
	$getteams[] = $seasondata[4];
	$lastseason = $seasondata[1];
	$getwins[] = $seasondata[7];
}
$points = array_sum($getpoints);
$high = max($getpoints);
$wins = array_sum($getwins);
$losses = $games - $wins;
$winper = number_format($wins / $games, 3);

$ppg = $points / $games;

$seasons = ($lastseason - $rookie) + 1;

// use $enhanced to check for players with more complex careers, and therefore a need to display additional information like charts and timeline
if ($seasons > 3){
		$enhanced = 1;
	} else {
		$enhanced = 0;
}	
							

$teams = array_unique($getteams);

if ($lastseason == $year){
		$active = 1; 
	} else {
		$active = 0;
	}

foreach ($awards as $findaward){
	if ($findaward['pid'] == $playerid){
		$wonaward = 1;
		break;
	} else {
		$wonaward = 0;
	}
}

// get data for gamescores
foreach ($playerdata as $key => $value) {
 $return[$value[1]][] = $value[3];
}

foreach ($return as $key => $value) {
  $yearpoints[$key] = $value; 
}


foreach ($yearpoints as $addpoints){
	$sumpoints[] = array_sum($addpoints);
	$countgames[] = count($addpoints);
	$highval[] = max($addpoints);
}

foreach ($playerdata as $yearof) {
	$years[] = substr($yearof[0], 0, 4);
}

$allyears = array_unique($years);

$y = 0;
foreach ($allyears as $thenumberedyears){
	$alltheyears[$y] = $thenumberedyears;
	$y++;
}

foreach ($playerdata as $thekey => $thevalue) {
 $thereturn[$thevalue[1]][] = $thevalue[4];
}

foreach ($thereturn as $thekey => $thevalue) {
  $teamsbuild[$thekey] = $thevalue; 
}

foreach ($teamsbuild as $getuniqueteams){
	$alltheteams[] = array_unique($getuniqueteams);
}


// $sumpoints  is points
// $countgames is games
// $highval is season high
// $alltheyears is seasons played
// $alltheteams is unique team ids of teams played for


// get pfl title winners


foreach($playoffall as $buildps){
	if ($buildps[3] == $playerid){
		$playerplayoffs[] = array($buildps[0], $buildps[1], $buildps[2], $buildps[3], $buildps[4], $buildps[5], $buildps[6]);
		$plpoints[] = $buildps[4];
	}
}

if(!empty($plpoints)){
	$playoffspts = array_sum($plpoints);
}

$winner = array();
if (!empty($playerplayoffs)){
	foreach ($playerplayoffs as $gettitles){
		if ($gettitles[2] == 16){
			$playoffyear = $gettitles[1];
			$playoffteam = $gettitles[5];
			$thetitles[] = array($playoffyear, $playoffteam);
			if ($champions[$playoffyear][2] == $playoffteam ){
				$winner[] = array($playoffyear, $playoffteam);	
			}
		}
	}
}

$titlewon = 0;
if ($thetitles[0] > 1){
	$titlewon = 1;
}

$inhall = 0;
foreach ($awards as $printaward ){
	if ($printaward['award'] == 'Hall of Fame Inductee'){
		if ($printaward['pid'] == $playerid){
			$inhall = 1;
		}
	}
}


foreach ($protections as $playerprotect){
	if ($playerprotect[0] == $playerid){
		$theprotections[$playerprotect[1]] = $playerprotect[2];
	}
}

foreach ($drafts as $playerdrafts){
	if ($playerdrafts[8] == $playerid){
		$thedrafts[$playerdrafts[1]] = $playerdrafts[0];
	}
}

$w = $alltheyears[0];
$d = 0;
while ($w <= $year){
	$setprotections[$d][0] = $theprotections[$w]; 
	$setprotections[$d][1] = $thedrafts[$w]; 
	$d++;
	$w++;
}


$r = 0;
foreach ($sumpoints as $get){
	$yearlydata[] = array($sumpoints[$r], $countgames[$r], $highval[$r], $alltheyears[$r], $alltheteams[$r], $setprotections[$r]);
	$r++;
}

// get allpro selections for just this player
foreach ($allpro as $printit){
	foreach ($printit as $key => $value){
		if ($key == $playerid){
			$proselections[] = $value;
		}
	}
}

// buid array needed for player timeline

$streak_arr = gamestreak($playerid);
$streak_games = $streak_arr[0];
$streak_games = $streak_arr[2];

?>

<!-- <?php printr($playersid, 0); ?>	 -->	
			
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
					<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $position; ?>.jpg">
					<h3 class="mar-no"><?php echo $first.' '.$last; ?></h3>
					<p></p>
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
					
					<?php if (!empty($det_number)){ ?>
					<h3 class="text-bold mar-btm"><?php echo $det_number; ?></h3> 
					
					<p class="mar-btm">
					<span class="text-muted">College: </span><span class="text-bold"><?php echo $det_college; ?></span><br/>
					<span class="text-muted">Height: </span><span class="text-bold"><?php echo $det_height; ?></span><br/>
					<span class="text-muted">Weight: </span><span class="text-bold"><?php echo $det_weight; ?></span><br/>
					<span class="text-muted">Birthdate: </span><span class="text-bold"><?php echo $det_format_date ?></span><br/>
							<?php if ($det_age < 40){
							echo '<span class="text-muted">Age: </span><span class="text-bold">'.$det_age.'</span><br/>';
							}
						
					} ?></p>
					
					
					<!--<h5>Player News</h5>
					
					
						<div id="demo-carousel" class="carousel slide" data-ride="carousel">
					
							<div class="carousel-inner text-center">
								<?php $getthenews = player_id_transient($fantasydata_playerid); 
								$newsbody = $getthenews['body'];
								$json_a=json_decode($newsbody ,true);
								//printr($json_a);
								
								foreach ($json_a as $getnews){
									$title = $getnews['Title'];
									$link = $getnews['Url'];
									$content = $getnews['Content'];
									$printnews .= '<div class="item">';
									$printnews .= '<h3><a href="'.$link.'">'.$title.'</a></h3>';
									$printnews .= '<p>'.$content.'</p>';
									$printnews .= '</div>';
									echo $printnews;
									
								}
								
						?>
		
						</div>
				
						<a class="carousel-control left" data-slide="prev" href="#demo-carousel"><i class="fa fa-chevron-left fa-2x"></i></a>
						<a class="carousel-control right" data-slide="next" href="#demo-carousel"><i class="fa fa-chevron-right fa-2x"></i></a>
						<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
					</div>-->
					
					
				</div>
			</div>
		
			<!-- only if player is in hall of fame -->
			<?php if($inhall == 1){ ?>
			<div class="panel widget">
				<div class="widget-body text-center">
					<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/award-hall.jpg">
					
					<?php 
						foreach ($awards as $printaward ){
							if ($printaward['award'] == 'Hall of Fame Inductee'){
								if ($printaward['pid'] == $playerid){
									echo '<h4>'.$printaward['year'].'</h4><h5>'.$printaward['award'].'</h5>';
								}
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
						foreach ($winner as $printwinner){
							echo $printwinner[0].' PFL Champion<br/>';
						}
					?>
					<p></p>
					<p>Appearances: <span class="text-bold"><?php 
						$prefix = '';
						foreach ($thetitles as $printthetitles) { 
							$winnerlist .= $prefix . '' . $printthetitles[0]; 
							$prefix = ', ';
						}
						
						echo $winnerlist;
						
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
						foreach ($awards as $printaward ){
							if ($printaward['pid'] == $playerid){
								echo $printaward['year'].' '.$printaward['award'].'<br/>';
							}
						}
						
					?>
				</div>
			</div>
			<?php } ?>
			
			<?php 
				foreach ($topscorer as $key => $gettop){
					if ($playerid == $gettop){
						$istop[] = $key;
					}
				}
			?>
			
			<!-- only if player was league leading scorer -->
			<?php if(!empty($istop)){ ?>
			<div class="panel widget">
				<div class="widget-body text-center">
					<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/award-top-scorer.jpg">
					<?php 
					echo '<h4>'.$position.' Scoring Title</h4>';
					
						$prefixx = '';
						foreach ($istop as $printtop ){
							$gettopper .= $prefixx . '' . $printtop;
							$prefixx = ', ';
						}
						echo $gettopper;
					?>
				</div>
			</div>
			<?php } ?>

			
			<!-- only if probowl selections exsist -->
			<?php if(!empty($proselections)){ ?>
			<div class="panel widget">
				<div class="widget-body text-center probowl">
					<h5>Probowl Selections</h5>
					<?php 
						$prefix = '';
						foreach ($proselections as $print){
							$getpros .= $prefix . '' . $print;
							$prefix = ', ';
						}
						
						echo $getpros;
						
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
								<span class="text-2x text-thin"><?php echo number_format($points);?></span>
								<p class="text-white">Points</p>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin"><?php echo $games; ?></span>
								<p class="text-white">Games</p>
							</div>
						</div>
					</div>	
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin"><?php echo number_format($ppg, 1);?></span>
								<p class="text-white">PPG</p>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin"><?php echo $seasons; ?></span>
								<p class="text-white">Seasons</p>
								<p class="text-white"></p>
								
								
								
							</div>
						</div>
					</div>
					
					<!-- row 2 -->
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-player-data text-thin"><?php echo $wins.'-'.$losses; ?></span>
								<p class="text-white">Record</p>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-player-data text-thin"><?php echo $winper; ?></span>
								<p class="text-white">Winning %</p>
							</div>
						</div>
					</div>	
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin"><?php echo $high; ?></span>
								<p class="text-white">Game High</p>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin">
									<?php 
										$printstreak = gamestreak ($playerid); 
										echo $printstreak[0];
									?></span>
								<p class="text-white">Game Streak</p>
							</div>
						</div>
					</div>
					
					<?php if (!empty($playerplayoffs)){  ?>
					<hr/>
					<div class="col-xs-12 col-sm-8 col-md-6">
						<div class="panel panel-primary panel-colorful">
							<div class="pad-all text-center">
								<span class="text-2x text-thin"><?php echo $playoffspts; ?></span>
								<p class="text-white">Playoff Points</p>
							</div>
						</div>
					</div>
					<?php } ?>
					
				</div>
			</div>
		</div>
		
		
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
							
							<?php if (!empty($playerplayoffs)){  ?>
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
														<th class="hidden-xs">Acquisition</th>
													</tr>
												</thead>
												<tbody>
													
												
														
														<?php 
															function getteam($theteam){
																if(!empty($theteam)){
																$seasonsList .= '/'.$theteam;
																}
															}
															
															foreach ($yearlydata as $printval) {
																
																
																$iyear = $printval[3];
																$ipoints = $printval[0];
																$igames = $printval[1];
																$ippg = number_format(($ipoints / $igames), 1);
																$ihigh = $printval[2];
																$iteam1 = $printval[4][0];
																$iteam2 = $printval[4][1];
																$iteam3 = $printval[4][2];
																$iprotect = $printval[5][0];
																$idraft = $printval[5][1];
																$iround = substr($idraft, -6, 2);
																$ipick = substr($idraft, -4, 2);
																
																
																
																$seasonsList .= '<tr>';
																$seasonsList .= '<td><a href="#fakelink" class="btn-link">'.$iyear.'</a></td>';
																
																$seasonsList .= '<td>'.$iteam1.' ';
																$seasonsList .= $iteam2.' ';
																$seasonsList .= $iteam3;
																$seasonsList .= '</td>';
																
																$seasonsList .= '<td class="text-center">'.$ipoints.'</td>';
																$seasonsList .= '<td class="text-center">'.$igames.'</td>';
																$seasonsList .= '<td class="text-center">'.$ippg.'</td>';
																$seasonsList .= '<td class="text-center">'.$ihigh.'</td>';
																$seasonsList .= '<td class="hidden-xs">';
																	
																	if(!empty($idraft)){ 
																		$seasonsList .= 'Drafted R'.$iround.'-'.$ipick; 
																	} else {
																		if(!empty($iprotect)){ 
																			$seasonsList .= 'Protected'; 
																		} else {
																			$seasonsList .= 'Free Agent'; 
																		}
																	}
																	
																$seasonsList .= '</td>';
																$seasonsList .= '</tr>';
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
								<span class="seasonbar">
									
									<?php 
										$prefixc = '';
										foreach ($yearlydata as $printchart){
											$chartList .= $prefixc . '' . $printchart[0];
											$prefixc = ', ';
										}
										
										echo $chartList;
										
									?>
								</span><br/>
									
									<?php foreach ($yearlydata as $printyearchart){
										echo '<span class="labelchart">'.$printyearchart[3].'</span>';
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
													
<!--
														<pre>
															<?php print_r($playerdata);?>
														</pre>
-->
													<?php 
														
														$i = -1;
														foreach ($playerdata as $printplayer) {
														
														$pyearnext = $playerdata[$i][1];
														$pyear = $printplayer[1];
														$pweek = $printplayer[2];
														$ppoints = $printplayer[3];
														$pteam = $printplayer[4];
														$pversus = $printplayer[5];
														$pplayerid = $printplayer[6];
														$presult = $printplayer[7];
														$plocation = $printplayer[8];

														
														if ($pyear != $pyearnext){
															$gametable .= '<td class="text-center text-bold switch-year">'.$pyear.'</td>';
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
															
															if ($plocation == 'H'){
																$gametable .= '<td class="text-bold">'.$teaminfo[$pteam][2].'</td></tr>';
															} else {
																$gametable .= '<td>@ '.$teaminfo[$pversus][2].'</td></tr>';
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
<!-- 													<th class="text-center">Result</th> -->
														
													</tr>
												</thead>
												<tbody>
													<?php
													$j = -1;
														foreach ($playerplayoffs as $printplay) {
														
														$plyearnext = $playerplayoffs[$j][1];
														$plyear = $printplay[1];
														$plweek = $printplay[2];
														$plpoints = $printplay[4];
														$plteam = $printplay[5];
														$plversus = $printplay[6];
														$plplayerid = $printplay[3];
														
														

														if ($plyear != $plyearnext){
															$playtable .= '<td class="text-center text-bold switch-year">'.$plyear.'</td>';
														} else {
															$playtable .= '<td class="text-center">&emsp;</td>';
														}
														
														if ($plweek == '15'){
															$playtable .= '<td class="text-center">Playoffs</td>';
														} else {
															$playtable .= '<td class="text-center">Posse Bowl '.$champions[$plyear][1].'</td>';
														}
														
														
														
														$playtable .= '<td class="text-center">'.$plteam.'</td>';
														$playtable .= '<td class="text-center">'.$plpoints.'</td>';
														$playtable .= '<td class="text-center">'.$plversus.'</td></tr>';
														
														
												$j++;
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
								foreach ($playersid as $selectplayer){
									$firsto = $playersassoc[$selectplayer][0];
									$lasto = $playersassoc[$selectplayer][1];
									$printselect .= '<option value="/player/?id='.$selectplayer.'">'.$firsto.' '.$lasto.'</option>';
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
							
							<?php 
								
								//printr($yearlydata, 0);

							?>
							
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
		
</div>

			
<?php include_once('main-nav.php'); ?>


<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>