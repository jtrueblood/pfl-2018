<?php
/*
 * Template Name: Results
 * Description: Page for weekly results
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
// PERFORMANCE MONITORING - Start timing
$perf_start_time = microtime(true);
$perf_query_count = get_num_queries();
$perf_timings = array();

function perf_checkpoint($label) {
    global $perf_start_time, $perf_query_count, $perf_timings;
    $current_time = microtime(true);
    $current_queries = get_num_queries();
    $perf_timings[] = array(
        'label' => $label,
        'time' => round(($current_time - $perf_start_time) * 1000, 2),
        'queries' => $current_queries - $perf_query_count,
        'elapsed' => round((isset($perf_timings[count($perf_timings)-1]) ? $current_time - ($perf_start_time + $perf_timings[count($perf_timings)-1]['time']/1000) : $current_time - $perf_start_time) * 1000, 2)
    );
}

$season = date("Y");
$allWeeksZero = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14");

$year_sel = $_GET["Y"];
$week_sel = $_GET["W"];
$weekvar = $year_sel.$week_sel;
$nonzeroweek = $str = ltrim($week_sel, '0');

$namenum = 15;

// get options from option page
$get_update_pdf = get_field('update_pdfs', 'options');
foreach ($get_update_pdf as $val){
	$update_pdf[$val['week_id']] = $val['pdf_file'];
}
$get_weekly_notes = get_field('week_notes', 'options');
foreach ($get_weekly_notes as $val){
    $update_notes[$val['week_id']] = $val['weekly_note'];
}

global $wpdb;

// Load bye week data for all seasons
function load_bye_week_data_results() {
	$bye_data = [];
	$base_path = get_stylesheet_directory() . '/nfl-bye-weeks/';
	
	// Load files from 1991 to current year
	for($year = 1991; $year <= date('Y'); $year++) {
		$file = $base_path . 'bye_weeks_' . $year . '.json';
		if(file_exists($file)) {
			$json = file_get_contents($file);
			$data = json_decode($json, true);
			
			if(isset($data['bye_weeks'])) {
				foreach($data['bye_weeks'] as $bye_week) {
					$week = $bye_week['week'];
					$teams = $bye_week['teams'];
					$weekid = sprintf('%04d%02d', $year, $week);
					
					// Store teams on bye for this week
					foreach($teams as $team) {
						$bye_data[$weekid][] = $team;
					}
				}
			}
		}
	}
	return $bye_data;
}

$bye_week_data = load_bye_week_data_results();

perf_checkpoint('Init complete');

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

perf_checkpoint('All team queries complete');

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
			'uniform'   => $week[20],
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

perf_checkpoint('Built byweek array');

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

//printr($ETS_week , 0);

// get_player_week moved to functions.

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

function format_scorediff($scorediff, $year) {
	if ($scorediff === null || $scorediff === '') {
		return '';
	}
	
	if ($scorediff == 0) {
		$color = '#cccccc'; // light grey
	} elseif ($scorediff == 1 && $year >= 1994) {
		$color = '#0066cc'; // blue for suspected 2pt conversions
	} else {
		$color = '#cc0000'; // red for errors
	}
	
	return ' <span style="color: '.$color.';">('. $scorediff .')</span>';
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
							<?php echo '<h2>Week '.$week_sel.'</h2>'; ?>
                            <?php //$playerdate = get_gamedate_by_player ('2018AlleQB', $weekvar);
                                //printr($playerdate, 0);

                            $justplayers = get_flat_players_by_week($weekvar);

                                foreach ($justplayers as $key => $value):
                                    if($value == ''):
                                        unset($value);
                                    else:
                                        if($value != 'None'):
                                            $justdates[$value] = get_gamedate_by_player($value, $weekvar);
                                        endif;
                                    endif;
                                endforeach;


                                $unique = array_unique($justdates);
                                arsort($unique);

                                $firstdate = getLastNotNullValueInArray($unique);
                                $lastdate = reset($unique);

                                $expfirst = explode('-', $firstdate);
                                $explast = explode('-', $lastdate);

                                $monthfirst = date('F', mktime(0, 0, 0, $expfirst[1], 10));
                                $monthlast = date('F', mktime(0, 0, 0, $explast[1], 10));

                                echo '<h4>'.$monthfirst.' '.$expfirst[2].' - '.$monthlast.' '.$explast[2].', '.$year_sel.'</h4>';
                                $uniquedates = array_unique($justdates);
                                //printr($uniquedates, 0);
                            ?>
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
					perf_checkpoint('Starting game loop');
					
					// OPTIMIZED: Get all number two data once instead of per-team
					$all_number_twos = get_number_twoed();
					$number_two_cache = array();
					$week_two_data = isset($all_number_twos[$weekvar]) ? $all_number_twos[$weekvar] : null;
					if($week_two_data) {
						$two_team = $week_two_data['team_int'];
						$number_two_cache[$two_team] = $two_team.' got Number Two-ed!<br>';
					}
					perf_checkpoint('Number two checks cached');
					
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
							
						if($w == 1) { perf_checkpoint('Game 1: Player queries done'); }
							
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
							
						if($w == 1) { perf_checkpoint('Game 1: OT player queries done'); }
							
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
							$soredata = array(
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
							
							<div class="panel panel-dark game-panel">
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
							if($w == 1) { perf_checkpoint('Game 1: Helmet queries done'); }
									
							if ($homepoints > $awaypoints){
								echo '<span class="text-2x text-bold">'.$get_the_helmet_home['name'].'</span><span class="text-2x pull-right text-bold">'.$homepoints.'</span><br>';
								echo '<span class="text-2x">'.$get_the_helmet_away['name'].'</span><span class="text-2x pull-right">'.$awaypoints.'</span><br>';
							    $winning_team = $hometeam;
							    $winning_location = 'H';
							} else {
								echo '<span class="text-2x">'.$get_the_helmet_home['name'].'</span>  <span class="text-2x pull-right">'.$homepoints.'</span><br>';
								echo '<span class="text-2x text-bold">'.$get_the_helmet_away['name'].'</span>  <span class="text-2x pull-right text-bold">'.$awaypoints.'</span><br>';
                                $winning_team = $awayteam;
                                $winning_location = 'R';
							}

					// boxscore left image
					
				if ($is_overtime) {
					echo '<div style="text-align: right; font-weight: bold; color: #cc0000; margin: 5px 0;">OVERTIME</div>';
				}
				echo '<hr>';
				echo '<h5>Boxscores</h5>';
						
							echo '<div class="col-xs-12 team-bar boxscorebox" style="background-image:url('.get_stylesheet_directory_uri().'/img/helmets/weekly/'.$hometeam.'-helm-right-'.$get_the_helmet_home['helmet'].'.png);">';
							echo '</div>';
					
					// boxscore right image	
							
							echo '<div class="col-xs-12 team-bar boxscorebox" style="background-image:url('.get_stylesheet_directory_uri().'/img/helmets/weekly/'.$awayteam.'-helm-left-'.$get_the_helmet_away['helmet'].'.png);">';
							echo '</div>';
							
							
					
					// boxscore left players
						echo '<div class="col-xs-12 boxscorebox">';
						
						// Check for home team boxscore error
						$checkhometotal = $h_qb1_data['points'] + $h_rb1_data['points'] + $h_wr1_data['points'] + $h_pk1_data['points'];
						$hdiff_check = $homepoints - $checkhometotal;
						// Hide error if it's OT and home team won with diff of 1
						$isOTHomeWinner = ($is_overtime && $hdiff_check == 1 && $homepoints > $awaypoints);
						$homeboxscoreerror = ($homepoints != $checkhometotal && !$isOTHomeWinner);
						
						// Get scorediff for home players
						$h_qb1_scorediff = $h_qb1_data['scorediff'];
						$h_rb1_scorediff = $h_rb1_data['scorediff'];
						$h_wr1_scorediff = $h_wr1_data['scorediff'];
						$h_pk1_scorediff = $h_pk1_data['scorediff'];
						
						$h_qb1_pos = substr($h_qb1, -2);
						$h_qb1_pfr_url = get_player_game_pfr_url($h_qb1, $weekvar);
						$h_qb1_pfr_button = (($h_qb1_scorediff != 0 || $homeboxscoreerror) && !empty($h_qb1_pfr_url)) ? '<button class="copy-cmd-btn" onclick="window.open(\''. $h_qb1_pfr_url . '\', \'_blank\')" style="margin-left: 1px;" title="View on Pro Football Reference">➜</button>' : '';
						$h_qb1_show_buttons = ($h_qb1_scorediff != 0 || $homeboxscoreerror);
						$h_qb1_show_2pt_btn = ($h_qb1_scorediff == 1 && $year_sel >= 1994);
						$h_qb1_buttons = '';
						if ($h_qb1_show_buttons) {
							$h_qb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$h_qb1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
							$h_qb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$h_qb1_pos.' '.$h_qb1_data['points'].'\', this)" style="margin-right: ' . ($h_qb1_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
							if ($h_qb1_show_2pt_btn) {
								$h_qb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$h_qb1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
							}
						}
						$h_qb1_scorediff_display = ($h_qb1_scorediff != 0) ? format_scorediff($h_qb1_scorediff, $year_sel) : '';
						echo $h_qb1_buttons . $h_qb1_pfr_button . ' <a href="/player/?id='.$h_qb1.'">' . ($h_qb1_data['first'] ? $h_qb1_data['first'] : 'None') . ' ' . $h_qb1_data['last'].'</a><span class="pull-right">'.$h_qb1_data['points'].$h_qb1_scorediff_display.'</span><br>';
						$h_rb1_pos = substr($h_rb1, -2);
						$h_rb1_pfr_url = get_player_game_pfr_url($h_rb1, $weekvar);
						$h_rb1_pfr_button = (($h_rb1_scorediff != 0 || $homeboxscoreerror) && !empty($h_rb1_pfr_url)) ? '<button class="copy-cmd-btn" onclick="window.open(\''. $h_rb1_pfr_url . '\', \'_blank\')" style="margin-left: 1px;" title="View on Pro Football Reference">➜</button>' : '';
						$h_rb1_show_buttons = ($h_rb1_scorediff != 0 || $homeboxscoreerror);
						$h_rb1_show_2pt_btn = ($h_rb1_scorediff == 1 && $year_sel >= 1994);
						$h_rb1_buttons = '';
						if ($h_rb1_show_buttons) {
							$h_rb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$h_rb1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
							$h_rb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$h_rb1_pos.' '.$h_rb1_data['points'].'\', this)" style="margin-right: ' . ($h_rb1_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
							if ($h_rb1_show_2pt_btn) {
								$h_rb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$h_rb1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
							}
						}
						$h_rb1_scorediff_display = ($h_rb1_scorediff != 0) ? format_scorediff($h_rb1_scorediff, $year_sel) : '';
						echo $h_rb1_buttons . $h_rb1_pfr_button . ' <a href="/player/?id='.$h_rb1.'">' . ($h_rb1_data['first'] ? $h_rb1_data['first'] : 'None') . ' ' . $h_rb1_data['last'].'</a><span class="pull-right">'.$h_rb1_data['points'].$h_rb1_scorediff_display.'</span><br>'; 
						$h_wr1_pos = substr($h_wr1, -2);
						$h_wr1_pfr_url = get_player_game_pfr_url($h_wr1, $weekvar);
						$h_wr1_pfr_button = (($h_wr1_scorediff != 0 || $homeboxscoreerror) && !empty($h_wr1_pfr_url)) ? '<button class="copy-cmd-btn" onclick="window.open(\''. $h_wr1_pfr_url . '\', \'_blank\')" style="margin-left: 1px;" title="View on Pro Football Reference">➜</button>' : '';
						$h_wr1_show_buttons = ($h_wr1_scorediff != 0 || $homeboxscoreerror);
						$h_wr1_show_2pt_btn = ($h_wr1_scorediff == 1 && $year_sel >= 1994);
						$h_wr1_buttons = '';
						if ($h_wr1_show_buttons) {
							$h_wr1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$h_wr1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
							$h_wr1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$h_wr1_pos.' '.$h_wr1_data['points'].'\', this)" style="margin-right: ' . ($h_wr1_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
							if ($h_wr1_show_2pt_btn) {
								$h_wr1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$h_wr1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
							}
						}
						$h_wr1_scorediff_display = ($h_wr1_scorediff != 0) ? format_scorediff($h_wr1_scorediff, $year_sel) : '';
						echo $h_wr1_buttons . $h_wr1_pfr_button . ' <a href="/player/?id='.$h_wr1.'">' . ($h_wr1_data['first'] ? $h_wr1_data['first'] : 'None') . ' ' . $h_wr1_data['last'].'</a><span class="pull-right">'.$h_wr1_data['points'].$h_wr1_scorediff_display.'</span><br>';
						$h_pk1_pos = substr($h_pk1, -2);
						$h_pk1_pfr_url = get_player_game_pfr_url($h_pk1, $weekvar);
						$h_pk1_pfr_button = (($h_pk1_scorediff != 0 || $homeboxscoreerror) && !empty($h_pk1_pfr_url)) ? '<button class="copy-cmd-btn" onclick="window.open(\''. $h_pk1_pfr_url . '\', \'_blank\')" style="margin-left: 1px;" title="View on Pro Football Reference">➜</button>' : '';
						$h_pk1_show_buttons = ($h_pk1_scorediff != 0 || $homeboxscoreerror);
						$h_pk1_show_2pt_btn = ($h_pk1_scorediff == 1 && $year_sel >= 1994);
						$h_pk1_buttons = '';
						if ($h_pk1_show_buttons) {
							$h_pk1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$h_pk1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
							$h_pk1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$h_pk1_pos.' '.$h_pk1_data['points'].'\', this)" style="margin-right: ' . ($h_pk1_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
							if ($h_pk1_show_2pt_btn) {
								$h_pk1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$h_pk1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
							}
						}
						$h_pk1_scorediff_display = ($h_pk1_scorediff != 0) ? format_scorediff($h_pk1_scorediff, $year_sel) : '';
						echo $h_pk1_buttons . $h_pk1_pfr_button . ' <a href="/player/?id='.$h_pk1.'">' . ($h_pk1_data['first'] ? $h_pk1_data['first'] : 'None') . ' ' . $h_pk1_data['last'].'</a><span class="pull-right">'.$h_pk1_data['points'].$h_pk1_scorediff_display.'</span><br>';

                        if($homeboxscoreerror):
                            echo '<div style="color: red;">ERROR: Boxscore is '.$checkhometotal.' / Diff: '.$hdiff_check.'</div>';
                        endif;

                    $startershome = array($h_qb1,$h_rb1,$h_wr1,$h_pk1,$h_qb2,$h_rb2,$h_wr2,$h_pk2);
                    $bench_home = get_the_bench($year_sel, $nonzeroweek, $hometeam);
                    if($w == 1) { perf_checkpoint('Game 1: Home bench query done'); }
                    
                    // Check for players on bye first
                    $benchonbye = array();
                    if(isset($bye_week_data[$weekvar]) && is_array($bench_home)):
                        // Check all roster statuses for bye week players
                        foreach($bench_home as $status => $players):
                            if(is_array($players)):
                                foreach($players as $pid => $pdata):
                                // Get player's NFL team - try current week first, then try surrounding weeks
                                $nflteam_result = get_player_team_played_week_nfl($pid, $weekvar);
                                $nflteam = (isset($nflteam_result[0][0]) && !empty($nflteam_result[0][0])) ? $nflteam_result[0][0] : null;
                                
                                // If no team found for this week, try previous week
                                if(empty($nflteam)):
                                    $prev_week = $weekvar - 1;
                                    $nflteam_result = get_player_team_played_week_nfl($pid, $prev_week);
                                    $nflteam = (isset($nflteam_result[0][0]) && !empty($nflteam_result[0][0])) ? $nflteam_result[0][0] : null;
                                endif;
                                
                                // If still no team, try next week
                                if(empty($nflteam)):
                                    $next_week = $weekvar + 1;
                                    $nflteam_result = get_player_team_played_week_nfl($pid, $next_week);
                                    $nflteam = (isset($nflteam_result[0][0]) && !empty($nflteam_result[0][0])) ? $nflteam_result[0][0] : null;
                                endif;
                                
                                if(!empty($nflteam) && in_array($nflteam, $bye_week_data[$weekvar])):
                                    $benchonbye[$pid] = $pdata;
                                endif;
                                endforeach;
                            endif;
                        endforeach;
                    endif;
                    
                    // Now display bench excluding players on bye and starters
                    $benchroster = $bench_home['ROSTER'];
                    if($benchroster):
                        echo '<div style="margin-top: 15px;"><strong>Bench:</strong></div>';
                        echo '<div style="font-size: 10px;">';
                        $print_bench = '';
                        foreach ($benchroster as $key => $value):
                            if(!in_array($key,$startershome) && !isset($benchonbye[$key])):
                                $print_bench .= $value['name'].', '.$value['position'].' / ';
                            endif;
                        endforeach;
                        echo substr($print_bench, 0, -2);
                        echo '</div>';
                    endif;
                    
                    if($benchonbye):
                            echo '<div style="margin-top: 15px;"><strong>On Bye:</strong></div>';
                            echo '<div style="font-size: 10px;">';
                            $print_bye = '';
                            foreach ($benchonbye as $key => $value):
                                if($value['name'] != ''):
                                    $print_bye .= $value['name'].', '.$value['position'].' / ';
                                endif;
                            endforeach;
                            echo substr($print_bye, 0, -2);
                        echo '</div>';
                    endif;
                    
                    $benchinjured = $bench_home['INJURED_RESERVE'];
                    if($benchinjured):
                        echo '<div style="margin-top: 15px;"><strong>Injured Reserve:</strong></div>';
                        echo '<div style="font-size: 10px;">';
                        $print_injured = '';
                        foreach ($benchinjured as $key => $value):
                            if($value['name'] != ''):
                                $print_injured .= $value['name'].', '.$value['position'].' / ';
                            endif;
                        endforeach;
                        echo substr($print_injured, 0, -2);
                        echo '</div>';
                    endif;
/*
						$get_the_helmet_home = get_helmet($hometeam, $year_sel);
						printr($get_the_helmet_home, 0);
*/
								
						echo '</div>';		
					
					// boxscore right players
						echo '<div class="col-xs-12 boxscorebox">';
						
						// Check for away team boxscore error
						$checkroadtotal = $a_qb1_data['points'] + $a_rb1_data['points'] + $a_wr1_data['points'] + $a_pk1_data['points'];
						$adiff_check = $awaypoints - $checkroadtotal;
						// Hide error if it's OT and away team won with diff of 1
						$isOTAwayWinner = ($is_overtime && $adiff_check == 1 && $awaypoints > $homepoints);
						$awayboxscoreerror = ($awaypoints != $checkroadtotal && !$isOTAwayWinner);
						
						// Get scorediff for away players
						$a_qb1_scorediff = $a_qb1_data['scorediff'];
						$a_rb1_scorediff = $a_rb1_data['scorediff'];
						$a_wr1_scorediff = $a_wr1_data['scorediff'];
						$a_pk1_scorediff = $a_pk1_data['scorediff'];
								
						$a_qb1_pos = substr($a_qb1, -2);
						$a_qb1_pfr_url = get_player_game_pfr_url($a_qb1, $weekvar);
						$a_qb1_pfr_button = (($a_qb1_scorediff != 0 || $awayboxscoreerror) && !empty($a_qb1_pfr_url)) ? '<button class="copy-cmd-btn" onclick="window.open(\''. $a_qb1_pfr_url . '\', \'_blank\')" style="margin-left: 1px;" title="View on Pro Football Reference">➜</button>' : '';
						$a_qb1_show_buttons = ($a_qb1_scorediff != 0 || $awayboxscoreerror);
						$a_qb1_show_2pt_btn = ($a_qb1_scorediff == 1 && $year_sel >= 1994);
						$a_qb1_buttons = '';
						if ($a_qb1_show_buttons) {
							$a_qb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$a_qb1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
							$a_qb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$a_qb1_pos.' '.$a_qb1_data['points'].'\', this)" style="margin-right: ' . ($a_qb1_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
							if ($a_qb1_show_2pt_btn) {
								$a_qb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$a_qb1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
							}
						}
						$a_qb1_scorediff_display = ($a_qb1_scorediff != 0) ? format_scorediff($a_qb1_scorediff, $year_sel) : '';
						echo $a_qb1_buttons . $a_qb1_pfr_button . ' <a href="/player/?id='.$a_qb1.'">' . ($a_qb1_data['first'] ? $a_qb1_data['first'] : 'None') . ' ' . $a_qb1_data['last'].'</a><span class="pull-right">'.$a_qb1_data['points'].$a_qb1_scorediff_display.'</span><br>';
						$a_rb1_pos = substr($a_rb1, -2);
						$a_rb1_pfr_url = get_player_game_pfr_url($a_rb1, $weekvar);
						$a_rb1_pfr_button = (($a_rb1_scorediff != 0 || $awayboxscoreerror) && !empty($a_rb1_pfr_url)) ? '<button class="copy-cmd-btn" onclick="window.open(\''. $a_rb1_pfr_url . '\', \'_blank\')" style="margin-left: 1px;" title="View on Pro Football Reference">➜</button>' : '';
						$a_rb1_show_buttons = ($a_rb1_scorediff != 0 || $awayboxscoreerror);
						$a_rb1_show_2pt_btn = ($a_rb1_scorediff == 1 && $year_sel >= 1994);
						$a_rb1_buttons = '';
						if ($a_rb1_show_buttons) {
							$a_rb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$a_rb1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
							$a_rb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$a_rb1_pos.' '.$a_rb1_data['points'].'\', this)" style="margin-right: ' . ($a_rb1_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
							if ($a_rb1_show_2pt_btn) {
								$a_rb1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$a_rb1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
							}
						}
						$a_rb1_scorediff_display = ($a_rb1_scorediff != 0) ? format_scorediff($a_rb1_scorediff, $year_sel) : '';
						echo $a_rb1_buttons . $a_rb1_pfr_button . ' <a href="/player/?id='.$a_rb1.'">' . ($a_rb1_data['first'] ? $a_rb1_data['first'] : 'None') . ' ' . $a_rb1_data['last'].'</a><span class="pull-right">'.$a_rb1_data['points'].$a_rb1_scorediff_display.'</span><br>';
						$a_wr1_pos = substr($a_wr1, -2);
						$a_wr1_pfr_url = get_player_game_pfr_url($a_wr1, $weekvar);
						$a_wr1_pfr_button = (($a_wr1_scorediff != 0 || $awayboxscoreerror) && !empty($a_wr1_pfr_url)) ? '<button class="copy-cmd-btn" onclick="window.open(\''. $a_wr1_pfr_url . '\', \'_blank\')" style="margin-left: 1px;" title="View on Pro Football Reference">➜</button>' : '';
						$a_wr1_show_buttons = ($a_wr1_scorediff != 0 || $awayboxscoreerror);
						$a_wr1_show_2pt_btn = ($a_wr1_scorediff == 1 && $year_sel >= 1994);
						$a_wr1_buttons = '';
						if ($a_wr1_show_buttons) {
							$a_wr1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$a_wr1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
							$a_wr1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$a_wr1_pos.' '.$a_wr1_data['points'].'\', this)" style="margin-right: ' . ($a_wr1_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
							if ($a_wr1_show_2pt_btn) {
								$a_wr1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$a_wr1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
							}
						}
						$a_wr1_scorediff_display = ($a_wr1_scorediff != 0) ? format_scorediff($a_wr1_scorediff, $year_sel) : '';
						echo $a_wr1_buttons . $a_wr1_pfr_button . ' <a href="/player/?id='.$a_wr1.'">' . ($a_wr1_data['first'] ? $a_wr1_data['first'] : 'None') . ' ' . $a_wr1_data['last'].'</a><span class="pull-right">'.$a_wr1_data['points'].$a_wr1_scorediff_display.'</span><br>';
						$a_pk1_pos = substr($a_pk1, -2);
						$a_pk1_pfr_url = get_player_game_pfr_url($a_pk1, $weekvar);
						$a_pk1_pfr_button = (($a_pk1_scorediff != 0 || $awayboxscoreerror) && !empty($a_pk1_pfr_url)) ? '<button class="copy-cmd-btn" onclick="window.open(\''. $a_pk1_pfr_url . '\', \'_blank\')" style="margin-left: 1px;" title="View on Pro Football Reference">➜</button>' : '';
						$a_pk1_show_buttons = ($a_pk1_scorediff != 0 || $awayboxscoreerror);
						$a_pk1_show_2pt_btn = ($a_pk1_scorediff == 1 && $year_sel >= 1994);
						$a_pk1_buttons = '';
						if ($a_pk1_show_buttons) {
							$a_pk1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$a_pk1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
							$a_pk1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$a_pk1_pos.' '.$a_pk1_data['points'].'\', this)" style="margin-right: ' . ($a_pk1_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
							if ($a_pk1_show_2pt_btn) {
								$a_pk1_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$a_pk1.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
							}
						}
						$a_pk1_scorediff_display = ($a_pk1_scorediff != 0) ? format_scorediff($a_pk1_scorediff, $year_sel) : '';
						echo $a_pk1_buttons . $a_pk1_pfr_button . ' <a href="/player/?id='.$a_pk1.'">' . ($a_pk1_data['first'] ? $a_pk1_data['first'] : 'None') . ' ' . $a_pk1_data['last'].'</a><span class="pull-right">'.$a_pk1_data['points'].$a_pk1_scorediff_display.'</span><br>';

                        if($awayboxscoreerror):
                            echo '<div style="color: red;">ERROR: Boxscore is '.$checkroadtotal.' / Diff: '.$adiff_check.'</div>';
                        endif;

                    $startersroad = array($a_qb1,$a_rb1,$a_wr1,$a_pk1,$a_qb2,$a_rb2,$a_wr2,$a_pk2);
                    $bench_away = get_the_bench($year_sel, $nonzeroweek, $awayteam);
                    if($w == 1) { perf_checkpoint('Game 1: Away bench query done'); }
                    
                    // Check for players on bye first
                    $benchonbye_away = array();
                    if(isset($bye_week_data[$weekvar]) && is_array($bench_away)):
                        // Check all roster statuses for bye week players
                        foreach($bench_away as $status => $players):
                            if(is_array($players)):
                                foreach($players as $pid => $pdata):
                                // Get player's NFL team - try current week first, then try surrounding weeks
                                $nflteam_result = get_player_team_played_week_nfl($pid, $weekvar);
                                $nflteam = (isset($nflteam_result[0][0]) && !empty($nflteam_result[0][0])) ? $nflteam_result[0][0] : null;
                                
                                // If no team found for this week, try previous week
                                if(empty($nflteam)):
                                    $prev_week = $weekvar - 1;
                                    $nflteam_result = get_player_team_played_week_nfl($pid, $prev_week);
                                    $nflteam = (isset($nflteam_result[0][0]) && !empty($nflteam_result[0][0])) ? $nflteam_result[0][0] : null;
                                endif;
                                
                                // If still no team, try next week
                                if(empty($nflteam)):
                                    $next_week = $weekvar + 1;
                                    $nflteam_result = get_player_team_played_week_nfl($pid, $next_week);
                                    $nflteam = (isset($nflteam_result[0][0]) && !empty($nflteam_result[0][0])) ? $nflteam_result[0][0] : null;
                                endif;
                                
                                if(!empty($nflteam) && in_array($nflteam, $bye_week_data[$weekvar])):
                                    $benchonbye_away[$pid] = $pdata;
                                endif;
                                endforeach;
                            endif;
                        endforeach;
                    endif;
                    
                    // Now display bench excluding players on bye and starters
                    $benchroster = $bench_away['ROSTER'];
                    if($benchroster):
                        echo '<div style="margin-top: 15px;"><strong>Bench:</strong></div>';
                        echo '<div style="font-size: 10px;">';
                        $print_bench_away = '';
                        foreach ($benchroster as $key => $value):
                            if(!in_array($key,$startersroad) && !isset($benchonbye_away[$key])):
                                $print_bench_away .= $value['name'].', '.$value['position'].' / ';
                            endif;
                        endforeach;
                        echo substr($print_bench_away, 0, -2);
                        echo '</div>';
                    endif;
                    
                    if($benchonbye_away):
                            echo '<div style="margin-top: 15px;"><strong>On Bye:</strong></div>';
                            echo '<div style="font-size: 10px;">';
                            $print_bye_away = '';
                            foreach ($benchonbye_away as $key => $value):
                                if($value['name'] != ''):
                                    $print_bye_away .= $value['name'].', '.$value['position'].' / ';
                                endif;
                            endforeach;
                            echo substr($print_bye_away, 0, -2);
                        echo '</div>';
                    endif;
                    
                    $benchinjured = $bench_away['INJURED_RESERVE'];
                    if($benchinjured):
                        echo '<div style="margin-top: 15px;"><strong>Injured Reserve:</strong></div>';
                        echo '<div style="font-size: 10px;">';
                        $print_injured_away = '';
                        foreach ($benchinjured as $key => $value):
                            if($value['name'] != ''):
                                $print_injured_away .= $value['name'].', '.$value['position'].' / ';
                            endif;
                        endforeach;
                        echo substr($print_injured_away, 0, -2);
                        echo '</div>';
                    endif;
/*
						$get_the_helmet_away = get_helmet($awayteam, $year_sel);
						printr($get_the_helmet_away, 0);
*/
						
						echo '</div>';				
								
					//overtime area 
					
						if ( $is_overtime == 1){

                            $soredata_ot = array(
                                $h_qb2,
                                $h_rb2,
                                $h_wr2,
                                $h_pk2,
                                $a_qb2,
                                $a_rb2,
                                $a_wr2,
                                $a_pk2
                            );

							echo '<div class="overtime">';
								echo '<hr>';
								echo '<span class="text-bold" style="display:block;">Overtime Game</span><br>';
							
									echo '<div class="col-xs-12 boxscorebox">';
									
										// Get scorediff for home OT players
										$h_qb2_scorediff = $h_qb2_data['scorediff'];
										$h_rb2_scorediff = $h_rb2_data['scorediff'];
										$h_wr2_scorediff = $h_wr2_data['scorediff'];
										$h_pk2_scorediff = $h_pk2_data['scorediff'];
								
									$h_qb2_pos = substr($h_qb2, -2);
									$h_qb2_show_buttons = ($h_qb2_scorediff != 0 || $homeboxscoreerror);
									$h_qb2_show_2pt_btn = ($h_qb2_scorediff == 1 && $year_sel >= 1994);
									$h_qb2_buttons = '';
									if ($h_qb2_show_buttons) {
										$h_qb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$h_qb2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
										$h_qb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$h_qb2_pos.' '.$h_qb2_data['points'].'\', this)" style="margin-right: ' . ($h_qb2_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
										if ($h_qb2_show_2pt_btn) {
											$h_qb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$h_qb2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
										}
										$h_qb2_buttons .= '<button class="copy-cmd-btn" onclick="copyGetNFLDataScript(\''. $h_qb2_data['first'] . '\', \''. $h_qb2_data['last'] . '\', '.$year_sel.', '.$nonzeroweek.', this)" style="margin-right: 1px;" title="getplayernfldata"><span style="color: #cc0000; font-weight: bold;">N</span></button> ';
									}
									$h_qb2_scorediff_display = ($h_qb2_scorediff != 0 || $homeboxscoreerror) ? format_scorediff($h_qb2_scorediff, $year_sel) : '';
									echo $h_qb2_buttons . '<a href="/player/?id='.$h_qb2.'">' . ($h_qb2_data['first'] ? $h_qb2_data['first'] : 'None') . ' ' . $h_qb2_data['last'].'</a><span class="pull-right">'.$h_qb2_data['points'].$h_qb2_scorediff_display.'</span><br>';
									$h_rb2_pos = substr($h_rb2, -2);
									$h_rb2_show_buttons = ($h_rb2_scorediff != 0 || $homeboxscoreerror);
									$h_rb2_show_2pt_btn = ($h_rb2_scorediff == 1 && $year_sel >= 1994);
									$h_rb2_buttons = '';
									if ($h_rb2_show_buttons) {
										$h_rb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$h_rb2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
										$h_rb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$h_rb2_pos.' '.$h_rb2_data['points'].'\', this)" style="margin-right: ' . ($h_rb2_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
										if ($h_rb2_show_2pt_btn) {
											$h_rb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$h_rb2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
										}
										$h_rb2_buttons .= '<button class="copy-cmd-btn" onclick="copyGetNFLDataScript(\''. $h_rb2_data['first'] . '\', \''. $h_rb2_data['last'] . '\', '.$year_sel.', '.$nonzeroweek.', this)" style="margin-right: 1px;" title="getplayernfldata"><span style="color: #cc0000; font-weight: bold;">N</span></button> ';
									}
									$h_rb2_scorediff_display = ($h_rb2_scorediff != 0 || $homeboxscoreerror) ? format_scorediff($h_rb2_scorediff, $year_sel) : '';
									echo $h_rb2_buttons . '<a href="/player/?id='.$h_rb2.'">' . ($h_rb2_data['first'] ? $h_rb2_data['first'] : 'None') . ' ' . $h_rb2_data['last'].'</a><span class="pull-right">'.$h_rb2_data['points'].$h_rb2_scorediff_display.'</span><br>';
									$h_wr2_pos = substr($h_wr2, -2);
									$h_wr2_show_buttons = ($h_wr2_scorediff != 0 || $homeboxscoreerror);
									$h_wr2_show_2pt_btn = ($h_wr2_scorediff == 1 && $year_sel >= 1994);
									$h_wr2_buttons = '';
									if ($h_wr2_show_buttons) {
										$h_wr2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$h_wr2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
										$h_wr2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$h_wr2_pos.' '.$h_wr2_data['points'].'\', this)" style="margin-right: ' . ($h_wr2_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
										if ($h_wr2_show_2pt_btn) {
											$h_wr2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$h_wr2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
										}
										$h_wr2_buttons .= '<button class="copy-cmd-btn" onclick="copyGetNFLDataScript(\''. $h_wr2_data['first'] . '\', \''. $h_wr2_data['last'] . '\', '.$year_sel.', '.$nonzeroweek.', this)" style="margin-right: 1px;" title="getplayernfldata"><span style="color: #cc0000; font-weight: bold;">N</span></button> ';
									}
									$h_wr2_scorediff_display = ($h_wr2_scorediff != 0 || $homeboxscoreerror) ? format_scorediff($h_wr2_scorediff, $year_sel) : '';
									echo $h_wr2_buttons . '<a href="/player/?id='.$h_wr2.'">' . ($h_wr2_data['first'] ? $h_wr2_data['first'] : 'None') . ' ' . $h_wr2_data['last'].'</a><span class="pull-right">'.$h_wr2_data['points'].$h_wr2_scorediff_display.'</span><br>';
									$h_pk2_pos = substr($h_pk2, -2);
									$h_pk2_show_buttons = ($h_pk2_scorediff != 0 || $homeboxscoreerror);
									$h_pk2_show_2pt_btn = ($h_pk2_scorediff == 1 && $year_sel >= 1994);
									$h_pk2_buttons = '';
									if ($h_pk2_show_buttons) {
										$h_pk2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$h_pk2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
										$h_pk2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$h_pk2_pos.' '.$h_pk2_data['points'].'\', this)" style="margin-right: ' . ($h_pk2_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
										if ($h_pk2_show_2pt_btn) {
											$h_pk2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$h_pk2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
										}
										$h_pk2_buttons .= '<button class="copy-cmd-btn" onclick="copyGetNFLDataScript(\''. $h_pk2_data['first'] . '\', \''. $h_pk2_data['last'] . '\', '.$year_sel.', '.$nonzeroweek.', this)" style="margin-right: 1px;" title="getplayernfldata"><span style="color: #cc0000; font-weight: bold;">N</span></button> ';
									}
									$h_pk2_scorediff_display = ($h_pk2_scorediff != 0 || $homeboxscoreerror) ? format_scorediff($h_pk2_scorediff, $year_sel) : '';
									echo $h_pk2_buttons . '<a href="/player/?id='.$h_pk2.'">' . ($h_pk2_data['first'] ? $h_pk2_data['first'] : 'None') . ' ' . $h_pk2_data['last'].'</a><span class="pull-right">'.$h_pk2_data['points'].$h_pk2_scorediff_display.'</span><br>';
									
									// Home OT Totals
										$h_ot_total = intval($h_qb2_data['points']) + intval($h_rb2_data['points']) + intval($h_wr2_data['points']) + intval($h_pk2_data['points']);
										echo '<div style="border-top: 1px solid #ddd; padding-top: 5px; font-size: 14px; color: #666;"><strong>OT Total:</strong> <span class="pull-right">' . $h_ot_total . '</span></div>';

									echo '</div>';
									
									echo '<div class="col-xs-12 boxscorebox">';
									
										// Get scorediff for away OT players
										$a_qb2_scorediff = $a_qb2_data['scorediff'];
										$a_rb2_scorediff = $a_rb2_data['scorediff'];
										$a_wr2_scorediff = $a_wr2_data['scorediff'];
										$a_pk2_scorediff = $a_pk2_data['scorediff'];
								
									$a_qb2_pos = substr($a_qb2, -2);
									$a_qb2_show_buttons = ($a_qb2_scorediff != 0 || $awayboxscoreerror);
									$a_qb2_show_2pt_btn = ($a_qb2_scorediff == 1 && $year_sel >= 1994);
									$a_qb2_buttons = '';
									if ($a_qb2_show_buttons) {
										$a_qb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$a_qb2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
										$a_qb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$a_qb2_pos.' '.$a_qb2_data['points'].'\', this)" style="margin-right: ' . ($a_qb2_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
										if ($a_qb2_show_2pt_btn) {
											$a_qb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$a_qb2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
										}
										$a_qb2_buttons .= '<button class="copy-cmd-btn" onclick="copyGetNFLDataScript(\''. $a_qb2_data['first'] . '\', \''. $a_qb2_data['last'] . '\', '.$year_sel.', '.$nonzeroweek.', this)" style="margin-right: 1px;" title="getplayernfldata"><span style="color: #cc0000; font-weight: bold;">N</span></button> ';
									}
									$a_qb2_scorediff_display = ($a_qb2_scorediff != 0 || $awayboxscoreerror) ? format_scorediff($a_qb2_scorediff, $year_sel) : '';
									echo $a_qb2_buttons . '<a href="/player/?id='.$a_qb2.'">' . ($a_qb2_data['first'] ? $a_qb2_data['first'] : 'None') . ' ' . $a_qb2_data['last'].'</a><span class="pull-right">'.$a_qb2_data['points'].$a_qb2_scorediff_display.'</span><br>';
									$a_rb2_pos = substr($a_rb2, -2);
									$a_rb2_show_buttons = ($a_rb2_scorediff != 0 || $awayboxscoreerror);
									$a_rb2_show_2pt_btn = ($a_rb2_scorediff == 1 && $year_sel >= 1994);
									$a_rb2_buttons = '';
									if ($a_rb2_show_buttons) {
										$a_rb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$a_rb2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
										$a_rb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$a_rb2_pos.' '.$a_rb2_data['points'].'\', this)" style="margin-right: ' . ($a_rb2_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
										if ($a_rb2_show_2pt_btn) {
											$a_rb2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$a_rb2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
										}
										$a_rb2_buttons .= '<button class="copy-cmd-btn" onclick="copyGetNFLDataScript(\''. $a_rb2_data['first'] . '\', \''. $a_rb2_data['last'] . '\', '.$year_sel.', '.$nonzeroweek.', this)" style="margin-right: 1px;" title="getplayernfldata"><span style="color: #cc0000; font-weight: bold;">N</span></button> ';
									}
									$a_rb2_scorediff_display = ($a_rb2_scorediff != 0 || $awayboxscoreerror) ? format_scorediff($a_rb2_scorediff, $year_sel) : '';
									echo $a_rb2_buttons . '<a href="/player/?id='.$a_rb2.'">' . ($a_rb2_data['first'] ? $a_rb2_data['first'] : 'None') . ' ' . $a_rb2_data['last'].'</a><span class="pull-right">'.$a_rb2_data['points'].$a_rb2_scorediff_display.'</span><br>';
									$a_wr2_pos = substr($a_wr2, -2);
									$a_wr2_show_buttons = ($a_wr2_scorediff != 0 || $awayboxscoreerror);
									$a_wr2_show_2pt_btn = ($a_wr2_scorediff == 1 && $year_sel >= 1994);
									$a_wr2_buttons = '';
									if ($a_wr2_show_buttons) {
										$a_wr2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$a_wr2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
										$a_wr2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$a_wr2_pos.' '.$a_wr2_data['points'].'\', this)" style="margin-right: ' . ($a_wr2_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
										if ($a_wr2_show_2pt_btn) {
											$a_wr2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$a_wr2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
										}
										$a_wr2_buttons .= '<button class="copy-cmd-btn" onclick="copyGetNFLDataScript(\''. $a_wr2_data['first'] . '\', \''. $a_wr2_data['last'] . '\', '.$year_sel.', '.$nonzeroweek.', this)" style="margin-right: 1px;" title="getplayernfldata"><span style="color: #cc0000; font-weight: bold;">N</span></button> ';
									}
									$a_wr2_scorediff_display = ($a_wr2_scorediff != 0 || $awayboxscoreerror) ? format_scorediff($a_wr2_scorediff, $year_sel) : '';
									echo $a_wr2_buttons . '<a href="/player/?id='.$a_wr2.'">' . ($a_wr2_data['first'] ? $a_wr2_data['first'] : 'None') . ' ' . $a_wr2_data['last'].'</a><span class="pull-right">'.$a_wr2_data['points'].$a_wr2_scorediff_display.'</span><br>';
									$a_pk2_pos = substr($a_pk2, -2);
									$a_pk2_show_buttons = ($a_pk2_scorediff != 0 || $awayboxscoreerror);
									$a_pk2_show_2pt_btn = ($a_pk2_scorediff == 1 && $year_sel >= 1994);
									$a_pk2_buttons = '';
									if ($a_pk2_show_buttons) {
										$a_pk2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 player_boxscore.py '.$a_pk2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: -3px;" title="player_boxscore">📋</button> ';
										$a_pk2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 find_player_by_points.py '.$year_sel.' '.$nonzeroweek.' '.$a_pk2_pos.' '.$a_pk2_data['points'].'\', this)" style="margin-right: ' . ($a_pk2_show_2pt_btn ? '-3px' : '1px') . ';" title="find_player_by_points">🔍</button> ';
										if ($a_pk2_show_2pt_btn) {
											$a_pk2_buttons .= '<button class="copy-cmd-btn" onclick="copyCommand(\'python3 confirm_two_pts.py '.$a_pk2.' '.$year_sel.' '.$nonzeroweek.'\', this)" style="margin-right: 1px;" title="confirm_two_pts">2</button>';
										}
										$a_pk2_buttons .= '<button class="copy-cmd-btn" onclick="copyGetNFLDataScript(\''. $a_pk2_data['first'] . '\', \''. $a_pk2_data['last'] . '\', '.$year_sel.', '.$nonzeroweek.', this)" style="margin-right: 1px;" title="getplayernfldata"><span style="color: #cc0000; font-weight: bold;">N</span></button> ';
									}
									$a_pk2_scorediff_display = ($a_pk2_scorediff != 0 || $awayboxscoreerror) ? format_scorediff($a_pk2_scorediff, $year_sel) : '';
									echo $a_pk2_buttons . '<a href="/player/?id='.$a_pk2.'">' . ($a_pk2_data['first'] ? $a_pk2_data['first'] : 'None') . ' ' . $a_pk2_data['last'].'</a><span class="pull-right">'.$a_pk2_data['points'].$a_pk2_scorediff_display.'</span><br>';
									
									// Away OT Totals
										$a_ot_total = intval($a_qb2_data['points']) + intval($a_rb2_data['points']) + intval($a_wr2_data['points']) + intval($a_pk2_data['points']);
										echo '<div style="border-top: 1px solid #ddd; padding-top: 5px; font-size: 14px; color: #666;"><span class="pull-right">' . $a_ot_total . '</span></div>';

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
											echo '<span class="text-bold">'.$hometeam_full.'</span> by '.$differential.'<br>';
										} else {
											echo '<span class="text-bold">'.$awayteam_full. '</span> by '.abs($differential).'<br>';
										}
										
										if ($differential > 20 or abs($differential) > 20){
											echo ' in a Blowout!&emsp;<br>';
										}
										
										if ($totalgamescore > 99){
											echo 'This was a Barnburner!&emsp;<br>';
										}

										// OPTIMIZED: Use cached number two results
										$gettwos_h = isset($number_two_cache[$hometeam]) ? $number_two_cache[$hometeam] : '';
										$gettwos_a = isset($number_two_cache[$awayteam]) ? $number_two_cache[$awayteam] : '';

										echo $gettwos_h;
                                        echo $gettwos_a;

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


                                    // JERSEY SECTION

                                    if($w == 1) { perf_checkpoint('Game 1: Before jersey section'); }
                                    $getpvqmult = getpvqmultipliers($year_sel);
                                    if($soredata_ot):
                                        $combined_soredata = array_merge($soredata, $soredata_ot);
                                    else:
                                        $combined_soredata = $soredata;
                                    endif;

                                    $pvqbygame = array();
                                    // OPTIMIZED: Use already-fetched player data instead of querying again
                                    $player_points_map = array(
                                        $h_qb1 => $h_qb1_data['points'],
                                        $h_rb1 => $h_rb1_data['points'],
                                        $h_wr1 => $h_wr1_data['points'],
                                        $h_pk1 => $h_pk1_data['points'],
                                        $a_qb1 => $a_qb1_data['points'],
                                        $a_rb1 => $a_rb1_data['points'],
                                        $a_wr1 => $a_wr1_data['points'],
                                        $a_pk1 => $a_pk1_data['points']
                                    );
                                    if($is_overtime == 1) {
                                        $player_points_map[$h_qb2] = $h_qb2_data['points'];
                                        $player_points_map[$h_rb2] = $h_rb2_data['points'];
                                        $player_points_map[$h_wr2] = $h_wr2_data['points'];
                                        $player_points_map[$h_pk2] = $h_pk2_data['points'];
                                        $player_points_map[$a_qb2] = $a_qb2_data['points'];
                                        $player_points_map[$a_rb2] = $a_rb2_data['points'];
                                        $player_points_map[$a_wr2] = $a_wr2_data['points'];
                                        $player_points_map[$a_pk2] = $a_pk2_data['points'];
                                    }
                                    
                                    $winning_team_players = ($winning_team == $hometeam) ? 
                                        array($h_qb1, $h_rb1, $h_wr1, $h_pk1) : 
                                        array($a_qb1, $a_rb1, $a_wr1, $a_pk1);
                                    if($is_overtime == 1) {
                                        if($winning_team == $hometeam) {
                                            $winning_team_players = array_merge($winning_team_players, array($h_qb2, $h_rb2, $h_wr2, $h_pk2));
                                        } else {
                                            $winning_team_players = array_merge($winning_team_players, array($a_qb2, $a_rb2, $a_wr2, $a_pk2));
                                        }
                                    }
                                    
                                    foreach ($winning_team_players as $value):
                                        if($value != 'None' && isset($player_points_map[$value])):
                                            $pvqpos = substr($value, 0 -2);
                                            $playerpointsweek = $player_points_map[$value];
                                            $pvqbygame[$value] = $getpvqmult[$pvqpos.'_Mult'] * $playerpointsweek;
                                        endif;
                                    endforeach;
                                    if($w == 1) { perf_checkpoint('Game 1: After PVQ loop'); }

                                    arsort($pvqbygame);
                                    //printr($pvqbygame, 0);
                                    $game_best_effort = array_key_first($pvqbygame);
                                    $playerbasic = get_player_basic_info($game_best_effort);
                                    //$playernumber = $playerbasic[0]['number'];
                                    $playernumber = get_numbers_by_season($game_best_effort);
                                    $playername = $playerbasic[0]['first'].' '.$playerbasic[0]['last'];

                                    // GET ALTERNATES OF THROWBACKS IF LISTED IN W_Team Table
                                    $uni_info = get_uni_info_by_team($winning_team);
                                    if($playernumber->$year_sel):
                                        $yearjerseynum = $playernumber->$year_sel;
                                    else:
                                        $yearjerseynum = end($playernumber);
                                    endif;
                                    $jersey_url = show_jersey_svg($winning_team, $winning_location, $uni_info[$year_sel], $yearjerseynum);

                                    $alternate_uni = ${$winning_team.'_week'};
                                    if($alternate_uni['uniform']):
                                        //echo $alternate_uni['uniform'];
                                        $altvar = explode('-', $alternate_uni['uniform']);
                                        //printr($altvar, 0);
                                        if($altvar[1] == 'TBK'):
                                            $extrajersey = $winning_location;
                                        endif;
                                        if($altvar[1] == 'ALT'):
                                            $extrajersey = 'A';
                                        endif;
                                        $jersey_url = show_jersey_svg($altvar[0], $extrajersey, $altvar[2], $yearjerseynum);
                                    endif;
                                    //printr($playernumber, 0);
                                    //echo $playernumber->$year_sel;
                                    ?>
                                    <p class="text-sm">Winning Team Game MVP </p>
                                    <h5><?php echo $playername;?></h5>
                                    <div class="game-pvq-mvp" style="background-image: url(<?php echo get_stylesheet_directory_uri().$jersey_url;?>);">
                                    </div>
                                     </div>
										</div>

									</div>

								</div>
								
								<?php
								// clearfix on different device sizes
								// clearfix on different device sizes
								if($w % 3 == 0){
									echo '<div class="clear"></div>';
								}
								$w++;
								
								
						} // END THE FOREACH 
						perf_checkpoint('Game loop complete');
						?>

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

                        <?php if($update_notes[$weekvar]): ?>
                            <div class="panel panel-dark">
                                <div class="panel-body">
                                    <div id="weeklynotes" style="">
                                        <?php echo $update_notes[$weekvar]; ?>
                                    </div>
                                </div>
                                <div class="panel-footer">
                                    <p>Week Notes</p>
                                </div>
                            </div>
                        <?php endif; ?>

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

							//insert_week_stand($weekstand);
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
        description: 'Team position comparisons.'
    },

    title: {
        text: '<?php echo $hometeam.' vs '.$awayteam.' <span class="small">Comparison</span>';?>'
    },

    pane: {
        size: '100%'
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
} 

perf_checkpoint('Page complete');

// Log performance report to console (hidden from front-end)
if (current_user_can('administrator')) {
	$total_time = round((microtime(true) - $perf_start_time) * 1000, 2);
	$total_queries = get_num_queries() - $perf_query_count;
	
	echo '<script>';
	echo 'console.group("⚡ PERFORMANCE REPORT");';
	echo 'console.table([' . PHP_EOL;
	foreach ($perf_timings as $i => $timing) {
		echo '\t{"Checkpoint": "' . addslashes($timing['label']) . '", "Time (ms)": ' . $timing['time'] . ', "Queries": ' . $timing['queries'] . ', "Δ Time (ms)": ' . $timing['elapsed'] . '}';
		if ($i < count($perf_timings) - 1) echo ',';
		echo PHP_EOL;
	}
	echo ']);' . PHP_EOL;
	echo 'console.log("%cTOTAL: ' . $total_time . 'ms | ' . $total_queries . ' queries", "font-weight:bold;font-size:14px;color:#0f0");';
	echo 'console.groupEnd();';
	echo '</script>';
}
?>

<style>
.copy-cmd-btn {
	background: none;
	border: 1px solid #ccc;
	border-radius: 3px;
	padding: 2px 5px;
	cursor: pointer;
	font-size: 11px;
	line-height: 1;
	vertical-align: middle;
	margin-right: 5px;
	color: #666;
	transition: all 0.2s;
}
.copy-cmd-btn:hover {
	background-color: #f0f0f0;
	border-color: #999;
	color: #333;
}
.copy-cmd-btn:active {
	background-color: #e0e0e0;
}
.copy-cmd-btn.copied {
	background-color: #4CAF50;
	border-color: #4CAF50;
	color: white;
}
</style>

<script>
function copyCommand(cmd, btn) {
	// Create a temporary textarea element
	const textarea = document.createElement('textarea');
	textarea.value = cmd;
	textarea.style.position = 'fixed';
	textarea.style.opacity = '0';
	document.body.appendChild(textarea);
	
	// Select and copy the text
	textarea.select();
	textarea.setSelectionRange(0, 99999); // For mobile devices
	
	try {
		const successful = document.execCommand('copy');
		if (successful) {
			// Change button appearance to show success
			const originalText = btn.innerHTML;
			btn.innerHTML = '✓';
			btn.classList.add('copied');
			
			// Reset button after 1.5 seconds
			setTimeout(function() {
				btn.innerHTML = originalText;
				btn.classList.remove('copied');
			}, 1500);
		} else {
			alert('Failed to copy to clipboard');
		}
	} catch (err) {
		console.error('Failed to copy: ', err);
		alert('Failed to copy to clipboard');
	} finally {
		// Remove the textarea
		document.body.removeChild(textarea);
	}
}
</script>


<?php get_footer(); ?>
