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

$randomize = array_rand($players);

/*
echo $i;
printr($playersid, 1);
*/

// this function will insert players seasonal data to wp_allleaders
insert_wp_career_leaders($playerid);
insert_wp_season_leaders($playerid);

//printr($players, 0);
$is_te = check_tightend($playerid);

$firstname = $players[$playerid][0];
$lastname = $players[$playerid][1];
$playerposition = $players[$playerid][2];
if($is_te == 1){
    $playerposition = 'te';
}
$rookieyear = $players[$playerid][3];
$mflid = $players[$playerid][4];
$height = $players[$playerid][5];
$weight = $players[$playerid][6];
$college = $players[$playerid][7];
$birthdate = $players[$playerid][8];
$playernumber = $players[$playerid][9];
$profburi = $players[$playerid][10];
$f_init = substr($profburi, 0, 1);
$nickname = $players[$playerid][12];


// for plyer pro football reference link
$profblink = 'https://www.pro-football-reference.com/players/'.$f_init.'/'.$profburi.'.htm';
// CURL TO PRO FOOTBALL REFERNECE .com REMOVE LATER
//insert_pfrcurl($playerid, $profblink);


$weeklydata = get_player_data($playerid);
$careerdata = get_player_career_stats($playerid);
$playoffsplayer = playerplayoffs($playerid);
$basicinfo = get_player_basic_info($playerid);
$weeksplayed = get_player_weeks_played($playerid);

//printr($careerdata, 1);

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
$enhanced = 1;


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
if($pbwins >= 1){
	$counttitles = count($pbwins);
}
if ($counttitles > 0){
	$titlewon = 1;
}

if(isset($plwinarr)){
	$pswins = array_sum($plwinarr);
}
$psloss = $playoffgames - $pswins;

$awards = get_player_award($playerid);
$wonaward = 0;
if ($awards > 0){
	$countawards = count($awards);
}
if ($countawards > 0){
	$wonaward = 1;
}
$ringofhonor = get_ring_of_honor();
//printr($ringofhonor, 0);

$draftedplayerfirst = get_drafts_player_first_instance($playerid);
$firstseasondrafted = $draftedplayerfirst['season'];

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
		$season = substr($value['id'], 2, -1);
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

if (($year - end($playseasons)) > 4){
	$year_retired = end($playseasons);
} else {
	$year_retired = '';
}

//printr($build_draft, 0);

// get first and last player years in an array
$first = $basicinfo[0]['rookie'];
// checks if player was drafted before the season where they first appeared.  If so, set that to be the first season in thier years played array

if ($firstseasondrafted < $first){
	if($firstseasondrafted > 0){
		$first = $firstseasondrafted;
	}
} 

//printr($basicinfo, 1);

if(count($playseasons) > 0){
	$last = end($playseasons) + 1;
} else {
	$last = $first +1;
}

//printr($playseasons, 0);

if($last != ''){
	while ($first != $last){
		$buildtheyears[] = $first;
		$first++;
	} 
} else {
	echo 'NO DATA';
} 

// Trades by player
$playerbytrade = get_trade_by_player($playerid);
//printr($playerbytrade, 0);										

//printr($buildtheyears, 0);

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
	
	if($careerdata['highseason'] == $season){
		$careerhigh = $careerdata['highseasonpts'];
	} else {
		$careerhigh = '';
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
		'careerhigh' => $careerhigh,
		'retired' => $get_retired,
		'dnp' => $empty
	);
	
	$get_rook = array();
	$get_awards = array();
	$get_champs = array();
	$get_leaders = array();
	
}


$highestgame = $careerdata['high'];

$potw = get_player_of_week_player($playerid);

$curlsuccess = get_check_for_pfr_success($playerid);


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

if($teamall):
	foreach ($teamall as $key => $value){
		if($check != $value):
			$check = $value;
			$teamall_no_change[$key] = $check; 
		else:
			$teamall_no_change[$key] = '';
		endif;		
	}
endif;

//$teamcolors = get_helmet_name_history_by_team('ATK', 1992);

//printr($teamcolors, 1);

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
			<?php	 
			$playerimgobj = get_attachment_url_by_slug($playerid);
			$imgid =  attachment_url_to_postid( $playerimgobj );
			$image_attributes = wp_get_attachment_image_src($imgid, 'thumbnail');	
			?>
		<!-- Left COL -->
		<div class="col-xs-24 col-sm-4 left-column">
			<div class="panel widget">
				<div id="player_widget_img" class="player-img-top">
					<img src="<?php echo $image_attributes[0]; ?>" style="height: 200px;"/>
				</div>
				<div class="widget-body text-center">
					<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $playerposition; ?>.jpg">
					<h3 class="playername"><?php echo $firstname.' '.$lastname; ?></h3>
					
					<?php
					if(!empty($nickname)){
						echo '<h5><span class="text-muted">"'.$nickname.'"</span></h5>';
					}	
					if (false !== $key = array_search($playerid, $ringofhonor)) {
					   $honorteam = substr($key, 0, 3);
					} 
					if(in_array($playerid, $ringofhonor)){
						echo '<h5><span class="text-thin">'.$teamids[$honorteam].'</span> Ring of Honor</h5>';
					}
					
				    
				    //printr($curlsuccess, 0);
				    
					?>
					
					
					
					<p class="mar-btm">
						<span class="text-muted">IDs: </span><?php echo $playerid; ?><?php 
							if(!empty($mflid)){
								echo ' / '.$mflid;
							}
							if ($curlsuccess == 200){
								echo ' / <a href="'.$profblink.'" target="_blank">'.$profburi.'</a>';
							}
							?>
						</p><br/>
						<?php if(!empty($college)){ ?>
							<span class="text-muted">College: </span><span class="text-bold"><?php echo $college; ?></span><br>
							<span class="text-muted">Height: </span><span class="text-bold"><?php echo $height; ?></span>
							<span class="text-muted"> - Weight: </span><span class="text-bold"><?php echo $weight; ?></span><br>
							<?php if($draftedplayerfirst['season']): ?>
								<span class="text-muted">First Drafted:<br></span><span class="text-bold"><?php 
									echo $draftedplayerfirst['season']; ?>: Round <?php echo $draftedplayerfirst['round']; ?>-<?php echo $draftedplayerfirst['pick']; ?></span> by <span class="text-bold"><?php echo $draftedplayerfirst['acteam'];
								else:
									echo 'Undrafted';		 
								endif;	
								?></span><br/>
							
						<?php } ?>
						
						<h4 class="mar-no text-sm">
							<?php 
								
								//printr($teamall, 0);
								
								echo '<p class="text-muted teams-list">'.$teamList.'</p>';
								
							?>
						</h4>
						
				
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
			<?php }
			?>

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
					<?php $count = count($pb_apps); 
						
						//echo $count;
						if($count == 1){
							echo '<p>Appearances: <span class="text-bold">';
							foreach($pb_apps as $key => $value){
								echo $key.' ';
							}
							echo '</span></p>';
						} 
						$i = 0;
						if($count > 1){
							foreach($pb_apps as $key => $value){
								if ($i == $count - 2) {
									$printkeys .= $key.' ';
								} else {
									if ($i == $count - 1) {
										$printkeys .= '& '.$key;
							    	} else {
								    	$printkeys .= $key.', ';
							    	}
								}
						
							    $i++;
							}
							
							echo '<p>Appearances: <span class="text-bold">'.$printkeys.'</span></p>';
						} 
						?>
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
			
			
			<div class="panel">
				<!--Panel heading-->
				<div class="panel-heading">
					<div class="panel-control">
						<button class="btn btn-default" data-target="#demo-panel-collapse" data-toggle="collapse" aria-expanded="true"><i class="fa fa-chevron-down"></i></button>
						<!-- <button class="btn btn-default" data-dismiss="panel"><i class="fa fa-times"></i></button> -->
					</div>
					<h3 class="panel-title">Team Numbers</h3>
				</div>
	
				<!--Panel body-->
				<div id="demo-panel-collapse" class="collapse in" aria-expanded="false">
					<div class="panel-body">
				<?php
                //printr($teamall_no_change, 0);
				if($teamall_no_change):
					echo '<div class="uniwrapper">';
						$team_switches = array_filter($teamall_no_change);
						//printr($team_switches, 0);
                        $i = 0;
						foreach($team_switches as $key => $value){
                            $uni_info = get_uni_info_by_team($value);
                            $yearuni = substr($key, 0, 4);
                            //echo $yearuni;
                            $jerseyvalue = $uni_info[$yearuni];
                            if($jerseyvalue < 1):
                                $jerseyvalue = 1;
                            endif;
                            if($i % 2 == 0):
                                $jcheck = 'H';
                            else:
                                $jcheck = 'R';
                            endif;
                            $numberarray = get_numbers_by_season($playerid);
                            if($numberarray):
                                $getjersey = show_jersey_svg($value, $jcheck, $jerseyvalue, $numberarray->$yearuni );
                                //echo'<p class="text-thin">From Number Array</p>';
                            else:
                                $getjersey = show_jersey_svg($value, $jcheck, $jerseyvalue, $playernumber );
                                //echo'<p class="text-thin">From Simple Number</p>';
                            endif;
                            echo '<div class="jersey-small">
				                <img src="'.get_stylesheet_directory_uri().$getjersey.'" class=""/>
                            </div>';
                            $i++;
						}
				    echo '</div>';
			    else:
				    echo '<p>&nbsp;</p>';
			    endif;
				?>
					</div>
				</div>
			</div>
			
			
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
					<!--<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/award-top-scorer.jpg">-->
					<?php if($ranker != ''){
							echo '<h5>Overall Career<br>Position Rank</h5><h3>'.ordinal($ranker).'</h3>'; 
						}
						echo '<p>&nbsp;</p>';
						if(!empty($player_number_ones)){
							foreach ($player_number_ones as $key => $value){
								echo '<span class="text-bold">'.$value['season'].' '.$value['team'].'</span>&emsp;'.$posaction. ' Title - '.$value['points'].' Points<br>';
							}
						} 
						
 						//printr($number_ones, 0);
					?>
				
				
				</div>
			</div>
			
			<!-- only if player won a sesaonal pvq -->
			<?php 
			$pvqplayer = $careerdata['pvqbyseason'];
			//printr($pvqplayer, 0);
			if(isset($pvqplayer)):
				if (false !== $key = array_search(1, $pvqplayer)) {?>
					<div class="panel widget">
					<div class="widget-body text-center">
						<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/award-top-pvq.jpg">
						<h4>PVQ Leader</h4>
						<?php 
							foreach ($pvqplayer as $key => $value){
								if( $value == 1):
									echo '<h3><span class="text-bold">'.$key.'</span> - 1.000</h3>';	
								endif;
							}
							echo '<i>Highest scorer according to player PVQ.</i>';
						?>
					</div>
				</div>
				<?php } 
				endif;
			?>

			
			<!-- only if probowl selections exsist -->
			<?php $probowlplayer = probowl_boxscores_player($playerid);
			
			if(!empty($probowlplayer)){?>	
			
			<div class="panel widget">
				<div class="widget-body text-center probowl">
					<h5>Pro Bowl Selections</h5>
					<?php 
						//printr($probowlplayer, 0);
						foreach ($probowlplayer as $key => $value){
							$starter = $value['starter'];
							$prostarter = '';
							if($starter == 0){
								$prostarter = 'Starter';
							}
							if($starter == 1){
								$prostarter = 'Backup';
							}
							if($starter == 2){
								$prostarter = 'Alternate';
							}
							echo '<span class="text-bold">'.$value['year'].' '.$prostarter.'</span> - '.$teamids[$value['team']].'<br>';		
						}
					?>
				</div>
			</div>
			<?php } ?>
			
			
			<!-- only if named player of the week -->
			<?php
			
			if(!empty($potw)){?>	
			
			<div class="panel widget">
				<div class="widget-body text-center probowl">
					<h5>Player of the Week Selections</h5>
					<?php 
						foreach ($potw as $value){
							$w = substr($value, -2);
							$y = substr($value, 0, 4);
							if($w == 15):
                                    echo '<span class="text-bold">Playoffs - '.$y.'</span><br>';
                                else:
							        echo '<span class="text-bold">Week '.$w.', '.$y.'</span><br>';
                                endif;
						}
					?>
				</div>
			</div>
			<?php } ?>
			
			
		</div>
		
				
		
	
		
		
		<!-- Right COL -->
		<div class="col-xs-24 col-sm-18 col-md-12">
		<!-- PFL Career Stats-->
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
										echo number_format($careerdata['ppg'], 1);
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

            <!-- NFL Career Stats-->
            <div class="panel">

                <?php
                    //echo $playerposition;
                    $passingyards = $careerdata['passingyards'];
                    $passingtds = $careerdata['passingtds'];
                    $passingint = $careerdata['passingint'];
                    $rushyrds = $careerdata['rushyrds'];
                    $rushtds = $careerdata['rushtds'];
                    $recyrds = $careerdata['recyrds'];
                    $rectds = $careerdata['rectds'];
                    $xpm = $careerdata['xpm'];
                    $xpa = $careerdata['xpa'];
                    $fgm = $careerdata['fgm'];
                    $fga = $careerdata['fga'];

                    if($rushyrds):
                        $rushypg = $rushyrds / $careerdata['games'];
                    endif;
                    if($recyrds):
                        $recypg = $recyrds / $careerdata['games'];
                    endif;
                    if($xpa):
                        $xpper = $xpm / $xpa;
                        $fgper = $fgm / $fga;
                    endif;

                    if($playerposition == 'QB'):
                        $stat1 = number_format((float)$passingyards, '0', '.', ',');
                        $label1 = 'Passing Yards';
                        $stat2 = $passingtds + $rushtds + $rectds;
                        $label2 = 'Touchdowns';
                        $stat3 = $passingint;
                        $label3 = 'Interceptions';
                        $stat4 = number_format((float)$rushyrds, '0', '.', ',');
                        $label4 = 'Rushing Yards';
                    endif;

                    if($playerposition == 'RB'):
                        $stat1 = number_format((float)$rushyrds, '0', '.', ',');
                        $label1 = 'Rushing Yards';
                        $stat2 = number_format((float)$rushypg, '1', '.', ',');
                        $label2 = 'Yards Per Game';
                        $stat3 = $passingtds + $rushtds + $rectds;
                        $label3 = 'Touchdowns';
                        $stat4 = number_format((float)$recyrds, '0', '.', ',');
                        $label4 = 'Receiving Yards';
                    endif;

                    if($playerposition == 'WR'):
                        $stat1 = number_format((float)$recyrds, '0', '.', ',');
                        $label1 = 'Receiving Yards';
                        $stat2 = number_format((float)$recypg, '1', '.', ',');
                        $label2 = 'Yards Per Game';
                        $stat3 = $passingtds + $rushtds + $rectds;
                        $label3 = 'Touchdowns';
                        $stat4 = number_format((float)$rushyrds, '0', '.', ',');
                        $label4 = 'Rushing Yards';
                    endif;

                    if($playerposition == 'te'):
                        $stat1 = number_format((float)$recyrds, '0', '.', ',');
                        $label1 = 'Receiving Yards';
                        $stat2 = number_format((float)$recypg, '1', '.', ',');
                        $label2 = 'Yards Per Game';
                        $stat3 = $passingtds + $rushtds + $rectds;
                        $label3 = 'Touchdowns';
                        $stat4 = number_format((float)$rushyrds, '0', '.', ',');
                        $label4 = 'Rushing Yards';
                    endif;

                    if($playerposition == 'PK'):
                        $stat1 = number_format((float)$xpm, '0', '.', ',');
                        $label1 = 'Extra Points';
                        $stat2 = number_format((float)$xpper, '3', '.', ',');
                        $label2 = 'XP Percent';
                        $stat3 = number_format((float)$fgm, '0', '.', ',');
                        $label3 = 'Field Goals';
                        $stat4 = number_format((float)$fgper, '3', '.', ',');
                        $label4 = 'FG Percent';
                    endif;

                ?>

                <!--Panel heading-->
                <div class="panel-heading">
                    <div class="panel-control">
                        <button class="btn btn-default" data-target="#demo-panel-collapse" data-toggle="collapse" aria-expanded="true"><i class="fa fa-chevron-down"></i></button>
                        <!-- <button class="btn btn-default" data-dismiss="panel"><i class="fa fa-times"></i></button> -->
                    </div>
                    <h3 class="panel-title">NFL Career Stats</h3>
                </div>

                <!--Panel body-->
                <div id="demo-panel-collapse" class="collapse in" aria-expanded="true">
                    <div class="panel-body">

                        <?php if($stat1): ?>
                        <div class="col-xs-12 col-sm-8 col-md-6">
                            <div class="panel panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-2x text-thin"><?php echo $stat1; ?></span>
                                    <p class=""><?php echo $label1; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if($stat2): ?>
                        <div class="col-xs-12 col-sm-8 col-md-6">
                            <div class="panel panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-2x text-thin"><?php echo $stat2 ?></span>
                                    <p class=""><?php echo $label2; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if($stat3): ?>
                        <div class="col-xs-12 col-sm-8 col-md-6">
                            <div class="panel panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-2x text-thin"><?php echo $stat3; ?></span>
                                    <p class=""><?php echo $label3; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if($stat4): ?>
                        <div class="col-xs-12 col-sm-8 col-md-6">
                            <div class="panel panel-colorful">
                                <div class="pad-all text-center">
                                    <span class="text-2x text-thin"><?php echo $stat4; ?></span>
                                    <p class=""><?php echo $label4; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>

                    <div class="panel-footer">
                        <p class="text-thin">In PFL Gameplay / Regular Season Only.</p>
                    </div>

                </div>
            </div>


		<!-- Start Playoffs -->

         <?php if($playoffgames): ?>
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
        <?php endif; ?>

		<!-- Center COL -->
			<div class="panel">
				<!--Panel heading-->

				<div class="panel-heading">
                    <h3 class="panel-title">Career Details</h3>

					<div class="panel-control career-details-panel">
	
						<!--Nav tabs-->
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#demo-tabs-box-1">PFL Season Stats</a>
							</li>
							
							<li><a data-toggle="tab" href="#demo-tabs-box-2">PFL Game Stats</a></li>

							<?php if (!empty($playoffsplayer)){  ?>
							<li><a data-toggle="tab" href="#demo-tabs-box-3">PFL Postseason</a></li>
							<?php } ?>
							
							<li><a data-toggle="tab" href="#demo-tabs-box-4">NFL Boxscores</a></li>
							
							<li><a data-toggle="tab" href="#demo-tabs-box-5">Player Supercard</a></li>
							
						</ul>
                    </div>
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
														<th class="text-center"><a class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="Position Rank">Rank</a></th>
														<th class="text-center"><a class="add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="Player Value">PVQ</a></th>
														
<!-- 														<th class="hidden-xs">Acquisition</th> -->
													</tr>
												</thead>
												<tbody>
																		
														<?php 	
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
																			$pvq  = $pvqplayer[$printyear];
																			$irank = get_player_season_rank ($playerid, $printyear);
																		
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
																				$string = '';
																			}
																			
																			$seasonsList .= '</td>';															
																			$seasonsList .= '<td class="text-center">'.$ipoints.'</td>';
																			$seasonsList .= '<td class="text-center">'.$igames.'</td>';
																			$seasonsList .= '<td class="text-center">'.$ippg.'</td>';
																			$seasonsList .= '<td class="text-center">'.$ihigh.'</td>';
																			$seasonsList .= '<td class="text-center">'.$irank.'</td>'; 
																			$seasonsList .= '<td class="text-center">'.number_format((float)$pvq, 3, '.', '').'</td>';
																			
																				
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
														
															$storepoints[$weekids] = $ppoints; // used to compare PFL points to NFL points
															
															if ($pyear != $checkyear){
																$gametable .= '<td class="text-center text-bold switch-year">'.$pyear.'</td>';
																$checkyear = $pyear;
															} else {
																$gametable .= '<td class="text-center">&emsp;</td>';
															}
															
															
															$gametable .= '<td class="text-center">'.$pweek.'</td>';
															$gametable .= '<td class="text-center">'.$pteam.'</td>';
															if ($ppoints == $highestgame){
																$gametable .= '<td class="text-center text-bold" style="background-color:#afd1ee;">'.$ppoints.'</td>';
															} else {
																$gametable .= '<td class="text-center">'.$ppoints.'</td>';
															}
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
														
// 														printr($pbteams_index, 0);
														
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
														// This will not work until Week 16 Final Data is added.
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


                        <div id="demo-tabs-box-4" class="tab-pane fade clear" style="overflow: scroll;">

                                <div class="row clear">
							    <div class="table-responsive">
									<table class="table table-striped">
										<thead>
												<tr>
													<th class="text-center min-width">Yr</th>
													<th class="text-center min-width">Wk</th>
                                                    <th class="text-center">Date</th>
													<th class="text-center">Game</th>
													<?php if ($playerposition != 'PK'){ ?>
													<th class="text-center">Pass Yds</th>
													<th class="text-center">Pass TD</th>
													<th class="text-center">Pass Int</th>
													<th class="text-center">Rush Yds</th>
													<th class="text-center">Rush TD</th>
													<th class="text-center">Rec Yds</th>
													<th class="text-center">Rec TD</th>
                                                    <?php } else { ?>
                                                        <th class="text-center min-width">XP</th>
                                                        <th class="text-center min-width">FG</th>
                                                    <?php } ?>
                                                    <th class="text-center">NFL</th>

													<th class="text-center">PFL</th>
													<th class="text-center">DIFF</th>
												</tr>
											</thead>
										<tbody>
											<?php

											if($weeklydata ):
												foreach ($weeklydata as $key => $data){
													if(in_array($key, $weeksplayed)):

														if($playerposition != 'PK'){
															$nflscore = pos_score_converter($data['year'], $data['pass_yds'], $data['pass_td'], $data['rush_yds'], $data['rush_td'], $data['pass_int'], $data['rec_yds'], $data['rec_td']);
														} else {
															$nflscore = pk_score_converter($data['year'], $data['xpm'], $data['fgm']);
														}

														$final_nfl_score = $nflscore + $data['score_correct'];

														if($storepoints[$key] == $final_nfl_score){
															$check = '-';
														} else {
															$check = 'X';
														}


														echo '<tr>';
														echo '<td class="text-center">'.$data['year'].'</td>';
														echo '<td class="text-center">'.$data['week'].'</td>';
														echo '<td class="text-center">'.date("m/d", strtotime($data['game_date'])).'</td>';
														echo '<td class="text-center">'.$data['nflteam'].' '.$data['game_location'].' '.$data['nflopp'].'</td>';
														if ($playerposition != 'PK'){
															echo '<td class="text-center">'.$data['pass_yds'].'</td>';
															echo '<td class="text-center">'.$data['pass_td'].'</td>';
															echo'<td class="text-center">'.$data['pass_int'].'</td>';
															echo '<td class="text-center">'.$data['rush_yds'].'</td>';
															echo '<td class="text-center">'.$data['rush_td'].'</td>';
															echo '<td class="text-center">'.$data['rec_yds'].'</td>';
															echo '<td class="text-center">'.$data['rec_td'].'</td>';
														} else {
															echo '<td class="text-center">'.$data['xpm'].' / '.$data['xpa'].'</td>';
															echo '<td class="text-center">'.$data['fgm'].' / '.$data['fga'].'</td>';
														}
														echo '<td class="text-center" style="border-left:2px solid grey;">'.$data['nflscore'].'</td>';
														echo '<td class="text-center">'.$data['points'].'</td>';
														echo '<td class="text-center">'.$data['scorediff'].'</td>';
														echo '</tr>';
													endif;
												}
											else:
												echo 'No NFL Player Data';
											endif;
											?>
										</tbody>
									</table>
								 </div>
                                </div>


						</div>
						
						
						<div id="demo-tabs-box-5" class="tab-pane fade">
							<?php
								supercard($playerid);
							?>
						</div>

					</div>
				</div>


            <!-- MFL TRANSACTIONS 2011 - Present -->
            <div class="panel panel-bordered panel-light">
                <div class="panel-heading">
                    <h3 class="panel-title">MFL Player Transactions</h3>
                </div>
                <div class="panel-body">
                    <p>2011 - Present.  Must export json of Transactions from MFL api each season and save to 'mfl-transactions' directory.</p>
                    <?php
                    $printit = new_mfl_transactions($playerid);
                    $removeempty = array_filter($printit);
                    if($removeempty): ?>
                    <div class="table-responsive">
                        <table id="transactionstable" class="transactions-table table table-hover table-vcenter stripe">
                            <thead>
                            <tr>
                                <th>Type</th>
                                <th>Player</th>
                                <th>Year</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Team</th>
                                <th>Action</th>
                                <!-- 														<th class="hidden-xs">Acquisition</th> -->
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                foreach($removeempty as $year):
                                    foreach($year as $key => $value):
                                    $type = $value['type'];
                                        if($type == 'TRADE'):
                                            $timeexplodet = explode(' ',$value['realtime']);
                                            $dateexplodet = explode('-', $timeexplode[0]);
                                            ?>
                                            <tr>
                                                <td class="text-bold">TRADE</td>
                                                <td><?php echo $firstname.' '.$lastname; ?></td>
                                                <td><?php echo $dateexplode[2]; ?></td>
                                                <td><?php echo $dateexplodet[0].'/'.$dateexplodet[1]; ?></td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td><?php echo $value['franchise1'].' from '.$value['franchise2']; ?></td>
                                            </tr>
                                        <?php else:
                                            $action = in_array($playerid, $value['dropped']) ? 'Dropped':
                                                $action = in_array($playerid, $value['added']) ? 'Added':
                                                    $action = in_array($playerid, $value['activated']) ? 'Activated':
                                                        $action = in_array($playerid, $value['transaction']) ? 'Transaction':
                                                            $action = in_array($playerid, $value['deactivated']) ? 'Deactivated' : '--';
                                            $timeexplode = explode(' ',$value['realtime']);
                                            $dateexplode = explode('-', $timeexplode[0]);
                                            //$dateof = date('Y-m-d',$timeexplode[0]);
                                            ?>
                                                <tr>
                                                    <td class="text-bold"><?php echo $type; ?></td>
                                                    <td><?php echo $firstname.' '.$lastname; ?></td>
                                                    <td><?php echo $dateexplode[2]; ?></td>
                                                    <td><?php echo $dateexplode[0].'/'.$dateexplode[1]; ?></td>
                                                    <td><?php echo $timeexplode[1]; ?></td>
                                                    <td><?php echo $value['franchise']; ?></td>
                                                    <td><?php echo $action; ?></td>
                                                </tr>
                                            <?php
                                        endif;
                                    endforeach;
                                endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                    <?php else:
                        echo '<h5>No MFL Transaction Data Found</h5>';
                        echo '</div>';
                    endif;?>
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
							<div class="col-xs-24">
								<?php echo '<br><p><a id="randombutton" href="/player/?id='.$randomize.'"/>Random Player</a></p>'; ?>
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
							    //printr($career_timeline, 0);
							    foreach ($career_timeline as $key => $value){
								    $tradechecker[$key] = $value['traded'][0]['when'];
							    }
							    
							    //printr($tradechecker, 0);
								
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
								 
								
								// protected (order based on season or preseason trade)
								
								// player not traded
								if($tradechecker[$key] == ''){			
									include('inc/player_timeline_protected.php');					
								}
								
								// traded player preseason	
								if($tradechecker[$key] == 'Preseason'){								
							    	include('inc/player_timeline_traded.php');
							    	include('inc/player_timeline_protected.php');
								}
								
								// traded player Draft	
								if($tradechecker[$key] == 'Draft'){			
									include('inc/player_timeline_protected.php');					
							    	include('inc/player_timeline_traded.php');	
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
								
								// free agents
								if (empty($value['dnp'])){
									if(empty($value['drafted'])){
										if(empty($value['protected'])){
											include('inc/player_timeline_free_agent.php');
										}
									}
								}
								
								// traded player mid-season	
								if($tradechecker[$key] == 'Season'){			
									include('inc/player_timeline_protected.php');					
							    	include('inc/player_timeline_traded.php');	
								}
								
								
								
								
								//reset traded player values in the loop
								$value['traded']['when'] = array();
								
								// career high 
								if(!empty($value['careerhigh'])){
									include('inc/player_timeline_max_points.php'); 
								} 
									
								if(!empty($value['awards'])){ ?>
								<div class="timeline-entry">
							        <div class="timeline-stat">
							            <div class="timeline-icon bg-success">
								            <img class="" src="/wp-content/themes/tif-child-bootstrap/img/award-leaders.jpg" />
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
								            <img class="" src="/wp-content/themes/tif-child-bootstrap/img/award-top-scorer.jpg" />
							            </div>
							            <div class="timeline-time"><?php $getaward['year']; ?></div>
							        </div>
							        <div class="timeline-label">
							            <?php echo '<span class="text-bold">'.$posaction.' Title</span> - '.$value['leader'].' Points'; ?>
							        </div>
						    	</div>
								<?php } 
								
								if($pvqplayer[$key] == 1){ ?>
								<div class="timeline-entry">
							        <div class="timeline-stat">
							            <div class="timeline-icon bg-success">
								            <img class="" src="/wp-content/themes/tif-child-bootstrap/img/award-top-pvq.jpg" />
							            </div>
							            <div class="timeline-time"><?php $pvqplayer[$key]; ?></div>
							        </div>
							        <div class="timeline-label">
							            <?php echo '<span class="text-bold">1.000 PVQ</span>'; ?>
							        </div>
						    	</div>
								<?php } 	
										    
								    
								if(!empty($value['pfltitle'])){ ?>
								<div class="timeline-entry">
							        <div class="timeline-stat">
							            <div class="timeline-icon bg-success">
								            <img class="" src="/wp-content/themes/tif-child-bootstrap/img/award-trophy.jpg" />
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
								            <img class="" src="/wp-content/themes/tif-child-bootstrap/img/award-hall.jpg" />
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
	<!--===================================================-->
	<!--End page content-->


</div>
<!--===================================================-->
<!--END CONTENT CONTAINER-->
<?php include_once('main-nav.php'); ?>		
</div><!--BOXED->

<!--
<script type="text/javascript">
window.onload=function(){
  var delayInMilliseconds = 3000; 
  	setTimeout(function() {
  		document.getElementById("randombutton").click();
	}, delayInMilliseconds);	
};
</script>
-->

<?php get_footer(); ?>