<?php
/*
 * Template Name: Era Page
 * Description: Page for displaying player eras by team
 */
 ?>


<?php get_header(); ?>

<!-- SET GLOBAL PLAYER VAR -->
<?php 
	
// $playerid = '2011SproRB';
$teamid = $_GET['id'];
$year = date("Y");
$team_all_ids = get_teams();
$seasons = the_seasons();
$players = get_players_assoc();
$champs = get_champions();
$thisteam = get_team_results('wp_team_'.$teamid);
$theweeks = the_weeks();

//$player = get_player_data('2004BreeQB');
//$player = get_raw_player_data_team('2004BreeQB', $teamid);

foreach ($thisteam as $key => $value){
	$qb_length = strlen($value['qb1']);
	$rb_length = strlen($value['rb1']);
	$wr_length = strlen($value['wr1']);
	$pk_length = strlen($value['pk1']);
	
	if ($qb_length == 10){
		$qbs[$key] = $value['qb1'];	
	} else {
		$qbs[$key] = 'NONE';
	}
	if ($rb_length == 10){
		$rbs[$key] = $value['rb1'];	
	} else {
		$rbs[$key] = 'NONE';
	}	
	if ($wr_length == 10){	
		$wrs[$key] = $value['wr1'];	
	} else {
		$wrs[$key] = 'NONE';
	}
	if ($pk_length == 10){
		$pks[$key] = $value['pk1'];	
	} else {
		$pks[$key] = 'NONE';
	}					
}

function mostpop($array){
	$values = array_count_values($array);
	arsort($values);
	return $values;
}

$group_qb = mostpop($qbs);
$group_rb = mostpop($rbs);
$group_wr = mostpop($wrs);
$group_pk = mostpop($pks);

// Load injury data from MFL JSON files (2011+)
function load_injury_data() {
	$injury_data = [];
	$base_path = get_stylesheet_directory() . '/mfl-weekly-rosters/';
	$mfl_to_pfl = playerid_mfl_to_pfl(); // Get MFL to PFL ID mapping
	
	// Load files from 2011 to current year
	for($year = 2011; $year <= date('Y'); $year++) {
		for($week = 1; $week <= 17; $week++) {
			$file = $base_path . $year . $week . '.json';
			if(file_exists($file)) {
				$json = file_get_contents($file);
				$data = json_decode($json, true);
				
				if(isset($data['rosters']['franchise'])) {
					foreach($data['rosters']['franchise'] as $franchise) {
						if(isset($franchise['player'])) {
							foreach($franchise['player'] as $player) {
								if(isset($player['status']) && $player['status'] == 'INJURED_RESERVE') {
									$mfl_id = $player['id'];
									// Convert MFL ID to PFL ID
									if(isset($mfl_to_pfl[$mfl_id])) {
										$pfl_id = $mfl_to_pfl[$mfl_id];
										$weekid = sprintf('%04d%02d', $year, $week);
										$injury_data[$weekid][$pfl_id] = 'IR';
									}
								}
							}
						}
					}
				}
			}
		}
	}
	return $injury_data;
}

// Load bye week data from JSON files
function load_bye_week_data() {
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

// Load NFL injury report data from JSON files (fallback for IR detection)
function load_nfl_injury_data() {
	$nfl_injury_data = [];
	$base_path = get_stylesheet_directory() . '/nfl-injuries/';
	global $players;
	
	// Load files from 2011 to current year
	for($year = 2011; $year <= date('Y'); $year++) {
		for($week = 1; $week <= 17; $week++) {
			$file = $base_path . 'nfl_injuries_' . $year . '_' . $week . '.json';
			if(file_exists($file)) {
				$json = file_get_contents($file);
				$data = json_decode($json, true);
				
				if(is_array($data)) {
					$weekid = sprintf('%04d%02d', $year, $week);
					
					foreach($data as $injury) {
						// Only process Out or Doubtful status
						if(!isset($injury['game_status']) || 
						   !in_array($injury['game_status'], ['Out', 'Doubtful'])) {
							continue;
						}
						
						$player_name = isset($injury['player_name']) ? $injury['player_name'] : '';
						$position = isset($injury['position']) ? $injury['position'] : '';
						$status = $injury['game_status'];
						
						if(empty($player_name) || empty($position)) {
							continue;
						}
						
						// Try to match player name to PFL ID
						// Parse the name (handle formats like "First Last" or "First Middle Last")
						$name_parts = explode(' ', trim($player_name));
						if(count($name_parts) < 2) {
							continue;
						}
						
						// Search through players array to find matching PFL ID
						foreach($players as $pfl_id => $player_data) {
							if(!isset($player_data[0]) || !isset($player_data[1])) {
								continue;
							}
							
							$pfl_first = strtolower(trim($player_data[0]));
							$pfl_last = strtolower(trim($player_data[1]));
							$pfl_pos = substr($pfl_id, -2);
							
							// Clean name parts for comparison
							$injury_first = strtolower(trim($name_parts[0]));
							$injury_last = strtolower(trim($name_parts[count($name_parts) - 1]));
							
							// Match first name, last name, and position
							if($pfl_first == $injury_first && 
							   $pfl_last == $injury_last && 
							   $pfl_pos == $position) {
								// Store the injury status
								$nfl_injury_data[$weekid][$pfl_id] = $status;
								break;
							}
						}
					}
				}
			}
		}
	}
	return $nfl_injury_data;
}

$injury_data = load_injury_data();
$bye_week_data = load_bye_week_data();
$nfl_injury_data = load_nfl_injury_data();

// Load playoff data from wp_playoffs table
function load_playoff_data($teamid) {
	global $wpdb;
	$playoff_data = [];
	
	// Get all playoff games for this team
	$results = $wpdb->get_results(
		"SELECT year, week, playerid FROM wp_playoffs WHERE team = '$teamid' ORDER BY year, week",
		ARRAY_A
	);
	
	if($results) {
		foreach($results as $row) {
			$year = $row['year'];
			$week = $row['week'];
			$playerid = $row['playerid'];
			$position = substr($playerid, -2);
			
			// Store by year, week, and position
			$playoff_data[$year][$week][$position] = $playerid;
		}
	}
	
	return $playoff_data;
}

$playoff_data = load_playoff_data($teamid);

// Debug: Check if we have bye week data for 2025 week 6
if(isset($bye_week_data['202506'])) {
	// Uncomment to debug:
	// echo '<!-- Bye week data for 202506: ' . print_r($bye_week_data['202506'], true) . ' -->';
}

$teamresults = get_players_played_by_team($teamid);

$reduction = 5;
foreach ($teamresults as $weekid => $value):
    foreach ($value as $key => $val):
        $getpts = get_player_points_by_week($val, $weekid);
        $builder[$val][$weekid] = $getpts[0][0];
    endforeach;
endforeach;

foreach ($builder as $player => $weeks):
    $count = count($weeks);
    if($count >= 10):
        $first = array_key_first($weeks);
            $fy = substr($first, 0, 4);
            $fw = substr($first, -2);
        $last = array_key_last($weeks);
            $ly = substr($last, 0, 4);
            $lw = substr($last, -2);
        $dumbbell[$player] = array(
            'first' => $first,
            'last' => $last
        );
    endif;
endforeach;
unset($dumbbell['None']);
unset($dumbbell['']);
unset($dumbbell['[Null]']);

//printr($dumbbell, 0);

?>
<style>
.era-player {
	width: 25%;
	background-color: grey;
	display: block;
	overflow: hidden;
	float: left;
	clear: both;
}
#eracontainer {
    height:100%;
    width:100%;
}
</style>


<script type="text/javascript">
        jQuery(document).ready(function() {
            var allData = [
            <?php foreach ($dumbbell as $key => $value): 
                $pos = substr($key, -2);
                if($pos == 'QB'){
                    $color = 'rgb(44, 175, 254)';
                } elseif($pos == 'RB'){
                    $color = 'rgb(76, 175, 80)';
                } elseif($pos == 'WR'){
                    $color = 'rgb(234, 166, 66)';
                } elseif($pos == 'PK'){
                    $color = 'rgb(156, 39, 176)';
                } else {
                    $color = '#999999';
                }
                // Get player image URL
                $playerimgobj = get_attachment_url_by_slug($key);
                $imgid = attachment_url_to_postid($playerimgobj);
                $image_attributes = wp_get_attachment_image_src($imgid, array(400, 400));
                $playerimg = $image_attributes[0];
                if(empty($playerimg)){
                    $playerimg = get_stylesheet_directory_uri() . '/img/players/' . $key . '.jpg';
                }
            ?>
            {
                name: '<?php echo pid_to_name($key, 1); ?>',
                low: <?php echo $value['first']; ?>,
                high: <?php echo $value['last'];  ?>,
                color: '<?php echo $color; ?>',
                playerid: '<?php echo $key; ?>',
                playerimg: '<?php echo $playerimg; ?>',
                position: '<?php echo $pos; ?>'
            },
            <?php endforeach; ?>
            ];
            
            var chart;
            
            function updateChart(position) {
                var filteredData = position === 'all' ? allData : allData.filter(function(item) {
                    return item.position === position;
                });
                
                if(chart) {
                    // Update data and recalculate height
                    var chartHeight = Math.max(400, filteredData.length * 30);
                    chart.setSize(null, chartHeight);
                    chart.series[0].setData(filteredData);
                } else {
                    createChart(filteredData);
                }
            }
            
            function createChart(data) {
                // Calculate height based on number of data points (30px per player)
                var chartHeight = Math.max(400, data.length * 30);
                
                chart = Highcharts.chart('eracontainer', {

                chart: {
                    type: 'dumbbell',
                    inverted: true,
                    height: chartHeight,
                },

                legend: {
                    enabled: false
                },

                subtitle: {
                    text: ''
                },

                title: {
                    text: 'Player'
                },

                tooltip: {
                    useHTML: true,
                    formatter: function() {
                        return '<div style="text-align: center;">' +
                               '<img src="' + this.point.playerimg + '" style="width: 100px; height: 100px; object-fit: cover; display: block; margin: 0 auto;" />' +
                               '<div style="margin-top: 10px; font-weight: bold; font-size: 13px;">' + this.point.name + ', ' + this.point.position + '</div>' +
                               '</div>';
                    }
                },

                xAxis: {
                    type: 'category',
                    labels: {
                        step: 1,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },

                yAxis: {
                    title: {
                        text: 'Season'
                    },
                    labels: {
                        align: 'right',
                        rotation: 270,
                        formatter: function() {
                            // Extract just the year from values like 199101
                            var str = this.value.toString();
                            return str.substring(0, 4);
                        }
                    },
                    min: 199101,
                    max: 202514
                },

                series: [{
                    name: '',
                    lineWidth: 15,
                    connectorWidth: 15,
                    lowColor: '#0000FF',
                    marker: {
                        enabled: false
                    },
                    dataLabels: [{
                        enabled: true,
                        formatter: function() {
                            // Format high value (end)
                            var str = this.point.high.toString();
                            var year = str.substring(0, 4);
                            var week = parseInt(str.substring(4, 6));
                            return 'W' + week + '-' + year;
                        },
                        style: {
                            fontSize: '9px',
                            fontWeight: 'normal'
                        },
                        verticalAlign: 'middle',
                        y: 0
                    }, {
                        enabled: true,
                        formatter: function() {
                            // Format low value (start)
                            var str = this.point.low.toString();
                            var year = str.substring(0, 4);
                            var week = parseInt(str.substring(4, 6));
                            return 'W' + week + '-' + year;
                        },
                        style: {
                            fontSize: '9px',
                            fontWeight: 'normal'
                        },
                        verticalAlign: 'middle',
                        y: 0
                    }],
                    data: data
                }]

                });
            }
            
            // Initialize chart with all data
            updateChart('all');
            
            // Add click handlers to filter buttons
            jQuery('.position-filter').on('click', function() {
                var position = jQuery(this).data('position');
                
                // Update button styles
                jQuery('.position-filter').removeClass('btn-primary').addClass('btn-default');
                jQuery(this).removeClass('btn-default').addClass('btn-primary');
                
                // Update chart
                updateChart(position);
            });
        });

    </script>


<!--CONTENT CONTAINER-->
<div class="boxed">
	

<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
	<!-- Championship banners -->

	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">
		<div class="row">
			<div class="col-xs-24">
				<h2><?php echo $team_all_ids[$teamid]['team']; ?></h2>
			</div>
		</div>

        <div class="row">
            <div class="col-xs-24">
                <div class="panel" style="margin-bottom: 10px;">
                    <div class="panel-body" style="padding: 10px;">
                        <div class="row" style="margin: 0;">
                            <div class="col-xs-24 col-sm-12" style="padding: 5px;">
                                <div style="text-align: center; padding: 0; margin: 0;">
                                    <strong style="font-size: 12px;">Position Filter:</strong><br/>
                                    <button class="btn btn-primary btn-sm position-filter" data-position="all">All</button>
                                    <button class="btn btn-default btn-sm position-filter" data-position="QB">QB</button>
                                    <button class="btn btn-default btn-sm position-filter" data-position="RB">RB</button>
                                    <button class="btn btn-default btn-sm position-filter" data-position="WR">WR</button>
                                    <button class="btn btn-default btn-sm position-filter" data-position="PK">PK</button>
                                </div>
                            </div>
                            <div class="col-xs-24 col-sm-12" style="padding: 5px;">
                                <div style="text-align: center; padding: 0; margin: 0;">
                                    <strong style="font-size: 12px;">Team:</strong><br/>
                                    <?php include_once('inc/eras_select.php');?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-xs-24">
                <div class="panel era-charts">
                    <div class="panel-body">
                        <div id="eracontainer"></div>
                    </div>
                </div>
            </div>
        </div>
		
		<div class="row">
		<?php
		// Helper function to check if a player was rostered on this team during the specific week
		// Looks for player in the posarray (roster history for this position/team)
		if (!function_exists('was_player_on_team')) {
			function was_player_on_team($player_id, $check_weekid, $posarray) {
				if(empty($player_id) || $player_id == 'NONE' || $player_id == '' || $player_id == '[Null]') {
					return false;
				}
				
				$check_year = (int)substr($check_weekid, 0, 4);
				$check_week = (int)substr($check_weekid, 4);
				
				// Only check the same season - look from 2 weeks before to 2 weeks after
				// This catches roster changes but prevents false positives from other seasons
				for($w = max(1, $check_week - 2); $w <= min(17, $check_week + 2); $w++) {
					$weekid = sprintf('%04d%02d', $check_year, $w);
					if(isset($posarray[$weekid]) && $posarray[$weekid] == $player_id) {
						return true;
					}
				}
				
				return false;
			}
		}
		
		function the_eras($posarray, $year, $injury_data = array(), $bye_week_data = array(), $playoff_data = array(), $nfl_injury_data = array()){
				global $players;
				
				// Convert to array and include all games
				$games = [];
				foreach($posarray as $key => $val){
					$games[] = ['key' => $key, 'val' => $val];
				}
				
				$current = '';
				$starter = ''; // Track the main starter
				$starter_name = ''; // Track the starter's name
				$currentyear = '';
				$last_week_key = '';
				
			for($i = 0; $i < count($games); $i++){
					$key = $games[$i]['key'];
					$val = $games[$i]['val'];
					
					// Check if this is NONE/empty
					$is_none = ($val == 'NONE' || $val == '' || $val == '[Null]');
					
					// Check if current starter is on IR this week
					// UNIVERSAL RULE: A player can't be on IR if they're actually playing this week ($val)
					// Also: Only flag as IR if player was actually on this team's roster
					$starter_on_ir = false;
					$ir_player_name = '';
					// Don't check IR for the player who is actively playing
					if($starter != '' && $starter != $val) {
						// Verify player was on this team's roster
						$starter_was_on_team = was_player_on_team($starter, $key, $posarray);
						
						if($starter_was_on_team) {
							// First check MFL transaction data
							if(isset($injury_data[$key][$starter])) {
								$starter_on_ir = true;
								// Get the IR player's name
								if(strlen($starter) == 10 && isset($players[$starter])){
									$ir_player_name = $players[$starter][0] . ' ' . $players[$starter][1];
								}
							} 
							// Fallback to NFL injury data if MFL data not available
							elseif(isset($nfl_injury_data[$key][$starter])) {
								$nfl_status = $nfl_injury_data[$key][$starter];
								// Out = definitely didn't play, Doubtful = likely didn't play
								if($nfl_status == 'Out' || $nfl_status == 'Doubtful') {
									$starter_on_ir = true;
									// Get the IR player's name
									if(strlen($starter) == 10 && isset($players[$starter])){
										$ir_player_name = $players[$starter][0] . ' ' . $players[$starter][1];
									}
								}
							}
						}
					}
					
					// Check if current starter is on bye this week
					$starter_on_bye = false;
					$bye_player_name = '';
					if($starter != '' && !$starter_on_ir && isset($bye_week_data[$key])) {
						// Get the NFL team for the starter - try current week first, then check all weeks
						$nfl_teams_all = get_player_nfl_team_by_week($starter);
						$nfl_team = null;
						
						// Try to get NFL team from this week or most recent week
						if(isset($nfl_teams_all[$key])) {
							$nfl_team = $nfl_teams_all[$key];
						} else {
							// Player didn't play this week (likely on bye), get their most recent team
							$year = substr($key, 0, 4);
							$week = (int)substr($key, 4);
							// Look backwards for the most recent week they played
							for($w = $week - 1; $w >= 1; $w--) {
								$check_key = sprintf('%04d%02d', $year, $w);
								if(isset($nfl_teams_all[$check_key])) {
									$nfl_team = $nfl_teams_all[$check_key];
									break;
								}
							}
						}
						
						if($nfl_team) {
							// Check if this team is on bye this week
							if(in_array($nfl_team, $bye_week_data[$key])) {
								$starter_on_bye = true;
								// Get the bye player's name
								if(strlen($starter) == 10 && isset($players[$starter])){
									$bye_player_name = $players[$starter][0] . ' ' . $players[$starter][1];
								}
							}
						}
					}
					
					$year = substr($key, 0, 4);
					$week = ltrim(substr($key, 4), '0');
					
					// Show year if it changed
					if($currentyear != $year){
						$display_year = $year;
					} else {
						$display_year = '';
					}
					
				if($is_none){
					// Display NONE row
					echo '<div style="display: flex; margin-bottom: 2px; align-items: center;">';
					echo '<div style="width: 50px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">';
					if($display_year != ''){
						echo '<strong>' . $display_year . '</strong>';
					}
					echo '</div>';
					echo '<div style="width: 35px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">W' . $week . '</div>';
					echo '<div style="flex: 1; padding: 3px 6px; background-color: #ffe0b3; border-left: 3px solid #ff9933; font-size: 12px; line-height: 18px; min-height: 24px;">None</div>';
					echo '</div>';
					$current = 'NONE'; // Reset current player
				} else {
					// Get player name
					$player_name = '';
					if(strlen($val) == 10 && isset($players[$val])){
						$player_name = $players[$val][0] . ' ' . $players[$val][1];
					} else {
						$player_name = $val;
					}
					
					// Check if this is same player as current/starter (and not on IR or bye)
					if (($val == $current || $val == $starter) && !$starter_on_ir && !$starter_on_bye){
						// Same player - regular game (continuation)
						echo '<div style="display: flex; margin-bottom: 2px; align-items: center;">';
						echo '<div style="width: 50px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">';
						if($display_year != ''){
							echo '<strong>' . $display_year . '</strong>';
						}
						echo '</div>';
						echo '<div style="width: 35px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">W' . $week . '</div>';
						echo '<div style="flex: 1; padding: 3px 6px; background-color: #f5f5f5; border-left: 3px solid #337ab7; font-size: 12px; line-height: 18px; min-height: 24px;"><span style="color: #999;">' . $starter_name . '</span></div>';
						echo '</div>';
						$current = $val;
					} else {
						// Different player (or starter is on IR/bye)
						// Look ahead to see if this is a one-game substitution or if player plays 3+ consecutive weeks
						$is_one_game_sub = false;
						$plays_three_consecutive = false;
						
						// Check if this player plays for 3 consecutive weeks (becomes new starter)
						if($i + 2 < count($games)){
							$week_1_val = $val;
							$week_2_val = $games[$i + 1]['val'];
							$week_3_val = $games[$i + 2]['val'];
							
							if($week_1_val == $week_2_val && $week_2_val == $week_3_val && $week_1_val != 'NONE'){
								$plays_three_consecutive = true;
							}
						}
						
						if(!$plays_three_consecutive && $i + 1 < count($games)){
							$next_val = $games[$i + 1]['val'];
							$next_key = $games[$i + 1]['key'];
							// Check if next player is the starter (not on IR or bye)
							// UNIVERSAL RULE: Don't check IR for player who is actively playing next week
							$next_starter_on_ir = false;
							// Only check if starter != next_val (not playing next week)
							if($starter != $next_val && was_player_on_team($starter, $next_key, $posarray)) {
								$next_starter_on_ir = isset($injury_data[$next_key][$starter]);
								// Fallback to NFL injury data
								if(!$next_starter_on_ir && isset($nfl_injury_data[$next_key][$starter])) {
									$nfl_status = $nfl_injury_data[$next_key][$starter];
									if($nfl_status == 'Out' || $nfl_status == 'Doubtful') {
										$next_starter_on_ir = true;
									}
								}
							}
							$next_starter_on_bye = false;
							if(!$next_starter_on_ir && isset($bye_week_data[$next_key])) {
								// Get all NFL teams for starter
								$nfl_teams_all = get_player_nfl_team_by_week($starter);
								$nfl_team = null;
								
								// Try to get NFL team from next week or most recent week
								if(isset($nfl_teams_all[$next_key])) {
									$nfl_team = $nfl_teams_all[$next_key];
								} else {
									// Look backwards for the most recent week they played
									$next_year = substr($next_key, 0, 4);
									$next_week = (int)substr($next_key, 4);
									for($w = $next_week - 1; $w >= 1; $w--) {
										$check_key = sprintf('%04d%02d', $next_year, $w);
										if(isset($nfl_teams_all[$check_key])) {
											$nfl_team = $nfl_teams_all[$check_key];
											break;
										}
									}
								}
								
								if($nfl_team && in_array($nfl_team, $bye_week_data[$next_key])) {
									$next_starter_on_bye = true;
								}
							}
							if($next_val == $starter && !$next_starter_on_ir && !$next_starter_on_bye){
								// Next game returns to starter - this is a one-game sub
								$is_one_game_sub = true;
							}
						}
						
					if($current == '' || $current == 'NONE'){
						// First player - set as starter
						// UNIVERSAL RULE: If player is in roster ($val), they're playing and can't be on IR
						echo '<div style="display: flex; margin-bottom: 2px; align-items: center;">';
						echo '<div style="width: 50px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">';
						if($display_year != ''){
							echo '<strong>' . $display_year . '</strong>';
						}
						echo '</div>';
						echo '<div style="width: 35px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">W' . $week . '</div>';
						echo '<div style="flex: 1; padding: 3px 6px; background-color: #f5f5f5; border-left: 3px solid #337ab7; font-size: 12px; line-height: 18px; min-height: 24px;"><strong>' . $player_name . '</strong></div>';
						echo '</div>';
						$starter = $val;
						$starter_name = $player_name;
						$current = $val;
					} elseif($plays_three_consecutive && $val != $starter){
							// Player plays 3+ consecutive weeks - promote to starter
							echo '<div style="display: flex; margin-bottom: 2px; align-items: center;">';
							echo '<div style="width: 50px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">';
							if($display_year != ''){
								echo '<strong>' . $display_year . '</strong>';
							}
							echo '</div>';
							echo '<div style="width: 35px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">W' . $week . '</div>';
							echo '<div style="flex: 1; padding: 3px 6px; background-color: #f5f5f5; border-left: 3px solid #337ab7; font-size: 12px; line-height: 18px; min-height: 24px;"><strong>' . $player_name . '</strong></div>';
							echo '</div>';
							$starter = $val;
							$starter_name = $player_name;
							$current = $val;
					} elseif($is_one_game_sub || $starter_on_ir || $starter_on_bye){
							// One-game substitution or starter on IR/bye - don't change starter
							// CRITICAL: If starter == val, the same player is listed as playing
							// This means they're NOT on IR/Bye, so treat as normal game
							if($starter == $val) {
								// Same player - they're playing normally, ignore IR/Bye flags
								echo '<div style="display: flex; margin-bottom: 2px; align-items: center;">';
								echo '<div style="width: 50px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">';
								if($display_year != ''){
									echo '<strong>' . $display_year . '</strong>';
								}
								echo '</div>';
								echo '<div style="width: 35px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">W' . $week . '</div>';
								echo '<div style="flex: 1; padding: 3px 6px; background-color: #f5f5f5; border-left: 3px solid #337ab7; font-size: 12px; line-height: 18px; min-height: 24px;"><span style="color: #999;">' . $starter_name . '</span></div>';
								echo '</div>';
								$current = $val;
							} else {
								// Different player - show as SUB with IR/Bye reason if applicable
								$show_ir = $starter_on_ir && $ir_player_name && was_player_on_team($starter, $key, $posarray);
								$show_bye = $starter_on_bye && $bye_player_name && was_player_on_team($starter, $key, $posarray);
								$ir_text = $show_ir ? ' <span style="color: #d9534f; font-weight: bold;">[' . $ir_player_name . ' - IR]</span>' : '';
								$bye_text = $show_bye ? ' <span style="color: #337ab7; font-weight: bold;">[' . $bye_player_name . ' - Bye]</span>' : '';
								echo '<div style="display: flex; margin-bottom: 2px; align-items: center;">';
								echo '<div style="width: 50px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">';
								if($display_year != ''){
									echo '<strong>' . $display_year . '</strong>';
								}
								echo '</div>';
								echo '<div style="width: 35px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">W' . $week . '</div>';
								echo '<div style="flex: 1; padding: 3px 6px 3px 20px; background-color: #fff3cd; border-left: 3px solid #ffc107; font-size: 12px; line-height: 18px; min-height: 24px;"><em>SUB: <strong>' . $player_name . '</strong></em>' . $ir_text . $bye_text . '</div>';
								echo '</div>';
								$current = $val;
							}
					} else {
						// New starter (permanent change)
						// UNIVERSAL RULE: If player is in roster ($val), they're playing and can't be on IR
						echo '<div style="display: flex; margin-bottom: 2px; align-items: center;">';
						echo '<div style="width: 50px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">';
						if($display_year != ''){
							echo '<strong>' . $display_year . '</strong>';
						}
						echo '</div>';
						echo '<div style="width: 35px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">W' . $week . '</div>';
						echo '<div style="flex: 1; padding: 3px 6px; background-color: #f5f5f5; border-left: 3px solid #337ab7; font-size: 12px; line-height: 18px; min-height: 24px;"><strong>' . $player_name . '</strong></div>';
						echo '</div>';
						$starter = $val;
						$starter_name = $player_name;
						$current = $val;
					}
					}
				}
					
					$currentyear = $year;
					$last_week_key = $key;
					
					// After week 14, add placeholder boxes for weeks 15 (playoffs) and 16 (championship)
					if($week == '14') {
						// Determine position from first element of posarray
						$position = '';
						foreach($posarray as $player_id) {
							if($player_id != 'NONE' && $player_id != '' && strlen($player_id) == 10) {
								$position = substr($player_id, -2);
								break;
							}
						}
						
						// Week 15 - Playoffs
						$playoff_player_15 = '';
						$playoff_name_15 = '';
						if(isset($playoff_data[$year][15][$position])) {
							$playoff_player_15 = $playoff_data[$year][15][$position];
							if(isset($players[$playoff_player_15])) {
								$playoff_name_15 = $players[$playoff_player_15][0] . ' ' . $players[$playoff_player_15][1];
							}
						}
						
						echo '<div style="display: flex; margin-bottom: 2px; align-items: center;">';
						echo '<div style="width: 50px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;"></div>';
						echo '<div style="width: 35px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">PL</div>';
						$playoff_display_15 = $playoff_name_15 ? '<strong>' . $playoff_name_15 . '</strong>' : '';
						echo '<div style="flex: 1; padding: 3px 6px; background-color: #f5f5f5; border-left: 3px solid #d9534f; font-size: 12px; line-height: 18px; min-height: 24px;">' . $playoff_display_15 . '</div>';
						echo '</div>';
						
						// Week 16 - Championship (Posse Bowl)
						$playoff_player_16 = '';
						$playoff_name_16 = '';
						if(isset($playoff_data[$year][16][$position])) {
							$playoff_player_16 = $playoff_data[$year][16][$position];
							if(isset($players[$playoff_player_16])) {
								$playoff_name_16 = $players[$playoff_player_16][0] . ' ' . $players[$playoff_player_16][1];
							}
						}
						
						echo '<div style="display: flex; margin-bottom: 2px; align-items: center;">';
						echo '<div style="width: 50px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;"></div>';
						echo '<div style="width: 35px; flex-shrink: 0; padding: 3px 6px; font-size: 12px; line-height: 18px;">PB</div>';
						$playoff_display_16 = $playoff_name_16 ? '<strong>' . $playoff_name_16 . '</strong>' : '';
						echo '<div style="flex: 1; padding: 3px 6px; background-color: #f5f5f5; border-left: 3px solid #d9534f; font-size: 12px; line-height: 18px; min-height: 24px;">' . $playoff_display_16 . '</div>';
						echo '</div>';
					}
				}
			}
		?>


		<div class="col-xs-24 col-sm-6">
			<div class="panel eras-player">
				<div class="panel-heading">
					<h3 class="panel-title">Quarterbacks</h3>
				</div>
				<div class="panel-body">
					<?php 
						the_eras($qbs, $year, $injury_data, $bye_week_data, $playoff_data, $nfl_injury_data); 
					?>
					
				</div>
			</div>
		</div>
		
		<div class="col-xs-24 col-sm-6">
			<div class="panel eras-player">
				<div class="panel-heading">
					<h3 class="panel-title">Runningbacks</h3>
				</div>
				<div class="panel-body">
					<?php 
						the_eras($rbs, $year, $injury_data, $bye_week_data, $playoff_data, $nfl_injury_data); 
					?>
				</div>
			</div>
		</div>
		
		<div class="col-xs-24 col-sm-6">
			<div class="panel eras-player">
				<div class="panel-heading">
					<h3 class="panel-title">Receivers</h3>
				</div>
				<div class="panel-body">
					<?php 
						the_eras($wrs, $year, $injury_data, $bye_week_data, $playoff_data, $nfl_injury_data); 
					?>
				</div>
			</div>
		</div>
		
		<div class="col-xs-24 col-sm-6">
			<div class="panel eras-player">
				<div class="panel-heading">
					<h3 class="panel-title">Kickers</h3>
				</div>
				<div class="panel-body">
					<?php 
						the_eras($pks, $year, $injury_data, $bye_week_data, $playoff_data, $nfl_injury_data); 
					?>
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

			
</div>

<?php get_footer(); ?>