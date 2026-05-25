<?php
/*
 * Template Name: Scrape ESPN for Player Numbers
 * Description: Script to Get Player Numbers From ESPN API
 */

// Helper function to fetch player jersey number from ESPN API
function get_espn_player_jersey($first_name, $last_name, $year = null) {
	$jersey_number = null;
	
	// Try to search for player in ESPN API
	// Note: ESPN API doesn't have a direct player search, so we'll try team rosters
	// This is a simplified approach - you may need to enhance this based on available data
	
	try {
		// Current season approach - get all NFL teams and search rosters
		$teams_url = "https://site.api.espn.com/apis/site/v2/sports/football/nfl/teams";
		$teams_response = @file_get_contents($teams_url);
		
		if($teams_response) {
			$teams_data = json_decode($teams_response, true);
			
			if(isset($teams_data['sports'][0]['leagues'][0]['teams'])) {
				foreach($teams_data['sports'][0]['leagues'][0]['teams'] as $team) {
					if(isset($team['team']['id'])) {
						$team_id = $team['team']['id'];
						
						// Get team roster
						$roster_url = "https://site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{$team_id}/roster";
						$roster_response = @file_get_contents($roster_url);
						
						if($roster_response) {
							$roster_data = json_decode($roster_response, true);
							
							if(isset($roster_data['athletes'])) {
								foreach($roster_data['athletes'] as $athlete_group) {
									if(isset($athlete_group['items'])) {
										foreach($athlete_group['items'] as $athlete) {
											// Check if name matches
											$full_name = isset($athlete['fullName']) ? $athlete['fullName'] : '';
											$search_full = strtolower($first_name . ' ' . $last_name);
											
											if(strtolower($full_name) == $search_full) {
												if(isset($athlete['jersey'])) {
													return $athlete['jersey'];
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	} catch (Exception $e) {
		// Silently fail if API is unavailable
	}
	
	return $jersey_number;
}

/*
$url1=$_SERVER['REQUEST_URI'];
header("Refresh: 5; URL=$url1");
*/

$getplayer = $_GET['id'];

//  Set Run Value to '1' if you want to override the .json file that exists in 'pfr-gamelogs'.  This is used for updating players that have played in the past.
$getrun = $_GET['run'];
 
$playersassoc = get_players_assoc();
$i = 0;
foreach ($playersassoc as $key => $value){
	$playersid[] = $key;
}

$randomize = array_rand($playersid);


$jusplayerids = just_player_ids();
$currentid = array_search($getplayer, $jusplayerids);
$nextplayer = $jusplayerids[$currentid + 1];

//$randomplayer = '2009AverWR';

if(isset($getplayer)){
	$randomplayer = $_GET['id'];
} else {
	$randomplayer = $playersid[$randomize];
}

$featuredplayer = $playersassoc[$randomplayer];
$first = $featuredplayer[0];
$last = $featuredplayer[1];
$position = $featuredplayer[2];
$rookie = $featuredplayer[3];
$mflid = $featuredplayer[4];

$playerinfo = get_player_basic_info($randomplayer, 'number');
$dbrookie = $playerinfo[0]['rookie'];
$dbnumber = $playerinfo[0]['number'];

echo $dbrookie;

insert_wp_career_leaders($randomplayer);
$testprint = insert_wp_season_leaders($randomplayer);

$getyearsplayed = get_player_years_played($randomplayer);
if($getyearsplayed):
    $yearsplayed = $getyearsplayed;
else:
    $yearsplayed = array($dbrookie => $dbnumber);
    printr($yearsplayed, 0);
endif;


$stylesheet_uri = get_stylesheet_directory_uri();

get_header(); 
?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">

				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php the_title();?></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
				
				<!-- THE ROW -->
				<div class="row">
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">	
						
						<div class="panel panel-bordered panel-light">
							<div class="panel-heading">
								<h3 class="panel-title">Select A Player</h3>
							</div>
							<div class="panel-body">
							<div class="col-xs-24 col-sm-18">	
								<select data-placeholder="Select an Existing Player" class="chzn-select" style="width:100%;" tabindex="1" id="playerDropScrapeNumber">
								<option value=""></option>
								
								<?php 	
								foreach ($playersassoc as $key => $selectplayer){
									$firsto = $selectplayer[0];
									$lasto = $selectplayer[1];
									$printselect .= '<option value="/?id='.$key.'">'.$firsto.' '.$lasto.'</option>';
								}
								echo $printselect;
								?>
							</select>
							</div>
							<div class="col-xs-24 col-sm-4">
								<button class="btn btn-warning" id="playerSelectScrapeNumber">Select</button>
							</div>
						</div>
						<?php echo $nextplayer ; ?>
						
						
						</div>
						</div>					
					<!-- PLAYER SPOTLIGHT -->
					<div class="col-xs-24 col-sm-4 left-column">
						<div class="panel widget" >
							
							<div class="widget-header" style="min-height: 200px;" >
								
								<?php 
									$ifimage = check_if_image($randomplayer);

									if($ifimage == 1){
										$playerimgobj = get_attachment_url_by_slug($randomplayer);
										$imgid =  attachment_url_to_postid( $playerimgobj );
										$image_attributes = wp_get_attachment_image_src($imgid);
										
										echo '<img src="'.$image_attributes[0].'" class="widget-bg img-responsive">';
									} else {
										echo '<img src="'.$stylesheet_uri.'/img/players/'.$randomplayer.'.jpg" class="widget-bg img-responsive">';
									}
								?>
								
							</div>
							<div class="widget-body text-center">
								<?php 
									if($ifimage == 1){	
										echo $ifimage.' source: /wp-content/uploads/';
									} else {
										echo $ifimage.' source: /img/players/';
									}
									?>
								<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $position; ?>.jpg">
								<h3 class="mar-no"><a href="/player/?id=<?php echo $randomplayer;?>"><?php echo $first.' '.$last; ?></a></h3>
								<p></p>
<!-- 								<h4 class="mar-no text-sm">	text could go here </h4> -->
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
						<div class="panel panel-bordered panel-light">
							<div class="panel-body">
								<?php while (have_posts()) : the_post(); ?>
								<p><?php the_content();?></p>
								<?php endwhile; wp_reset_query(); ?>
							</div>
						</div>
					</div>
							
					<!-- GET BASIC PLAYER INFO -->
					<div class="col-xs-24 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php 
								// ESPN API LOGIC
								$espn_numbers_found = false;
								$finalnumbers = array();
								$manual_entry_needed = false;
								$espn_status_message = '';
								
								// Load existing numberarray from database
								$existing_numbers_obj = get_numbers_by_season($randomplayer);
								$existing_numbers = array();
								if($existing_numbers_obj):
									// Convert stdClass to array
									foreach($existing_numbers_obj as $year => $number):
										$existing_numbers[$year] = $number;
									endforeach;
								endif;
								
								// Start with existing numbers (preserve what we already have)
								$finalnumbers = $existing_numbers;
								
								// Try to find player on ESPN by searching for their name
								$search_name = urlencode($first . ' ' . $last);
								
								// Get years player played
								$yearclean = array_values($yearsplayed);
								
								// Try to find player data from ESPN for each year they played
								if($yearclean && count($yearclean) > 0):
									// First, try to get current jersey number from ESPN API
									$espn_jersey = get_espn_player_jersey($first, $last);
									
									if($espn_jersey):
										if(!empty($existing_numbers)):
											$espn_status_message = '<div class="alert alert-success">Found jersey number #'.$espn_jersey.' from ESPN API. Will be added to years without existing numbers.</div>';
										else:
											$espn_status_message = '<div class="alert alert-success">Found jersey number #'.$espn_jersey.' from ESPN API (will be applied to all years)</div>';
										endif;
										$espn_numbers_found = true;
									else:
										if(!empty($existing_numbers)):
											$espn_status_message = '<div class="alert alert-info">Player not found in current ESPN rosters. Using existing numbers from database. Manual entry available for new years.</div>';
										else:
											$espn_status_message = '<div class="alert alert-warning">Player not found in current ESPN rosters. Manual entry required.</div>';
										endif;
									endif;
									
									foreach($yearclean as $year):
										// Skip years that already have numbers in the database (don't overwrite)
										if(isset($existing_numbers[$year]) && $existing_numbers[$year]):
											// Already have a number for this year, keep it
											continue;
										endif;
										
										// Try ESPN API to get roster data for this year
										// Note: ESPN API doesn't easily provide historical jersey numbers
										// We'll use the current number for new years unless manually overridden
										$found_number = $espn_jersey;
										
										if(!$found_number):
											// Check if we have a manual entry
											if(isset($_POST['manual_numbers']) && isset($_POST['manual_numbers'][$year])):
												$found_number = sanitize_text_field($_POST['manual_numbers'][$year]);
											endif;
										endif;
										
										if($found_number):
											$finalnumbers[$year] = $found_number;
										else:
											$manual_entry_needed = true;
											// Use existing number from DB if available, or fallback to dbnumber
											if(isset($existing_numbers[$year])):
												$finalnumbers[$year] = $existing_numbers[$year];
											elseif($dbnumber):
												$finalnumbers[$year] = $dbnumber;
											endif;
										endif;
									endforeach;
								else:
									$yearsplayed = array($dbrookie => $dbnumber);
									$finalnumbers = $yearsplayed;
								endif;
								
								// Check if we have new numbers to save (ESPN found something or we have complete data)
								$should_auto_save = false;
								$data_changed = false;
								
								// Check if finalnumbers is different from existing_numbers
								if(!empty($finalnumbers)):
									// Compare arrays to see if anything changed
									if(json_encode($finalnumbers) !== json_encode($existing_numbers)):
										$data_changed = true;
									endif;
								endif;
								
								if(($espn_numbers_found || !empty($finalnumbers)) && $data_changed):
									// We have new data to save
									$should_auto_save = true;
								endif;
								
								// Handle form submission for manual entry
								if(isset($_POST['save_numbers']) && isset($_POST['manual_numbers'])):
									$manual_numbers = $_POST['manual_numbers'];
									// Merge manual entries with existing numbers (manual entries take precedence)
									foreach($manual_numbers as $year => $number):
										if($number && is_numeric($number)):
											$finalnumbers[$year] = sanitize_text_field($number);
										endif;
									endforeach;
									
									// Sort by year for cleaner JSON output
									ksort($finalnumbers);
									
									$encode = json_encode($finalnumbers);
									$updatetable = insert_player_number_array($getplayer, $encode);
									$manual_entry_needed = false;
									echo '<div class="alert alert-success">Numbers saved successfully! (Merged with existing data)</div>';
								elseif($should_auto_save && !isset($_POST['save_numbers'])):
									// Auto-save when ESPN found numbers on initial load
									ksort($finalnumbers);
									$encode = json_encode($finalnumbers);
									$updatetable = insert_player_number_array($getplayer, $encode);
									echo '<div class="alert alert-success">Auto-saved: ESPN numbers merged with existing data!<br>';
									echo '<small>Player ID: '.$getplayer.' | Result: '.$updatetable.'</small></div>';
								endif;
								?>
								
								<div class="well" style="background: #f5f5f5; padding: 10px; margin: 10px 0;">
									<h5>Debug Info:</h5>
									<p><strong>Player ID:</strong> <?php echo $getplayer; ?></p>
									<p><strong>Years Played:</strong> <?php printr($yearclean, 0); ?></p>
									<p><strong>ESPN Found:</strong> <?php echo $espn_numbers_found ? 'Yes' : 'No'; ?></p>
									<p><strong>Data Changed:</strong> <?php echo isset($data_changed) && $data_changed ? 'Yes' : 'No'; ?></p>
									<p><strong>Should Auto-Save:</strong> <?php echo isset($should_auto_save) && $should_auto_save ? 'Yes' : 'No'; ?></p>
								</div>
								
								<?php if(!empty($existing_numbers)): ?>
									<div class="alert alert-info"><strong>Existing Numbers in Database:</strong><br>
									<?php foreach($existing_numbers as $yr => $num): ?>
										Year <?php echo $yr; ?>: #<?php echo $num; ?><br>
									<?php endforeach; ?>
									</div>
								<?php endif; ?>
								
								<div class="well" style="background: #f5f5f5; padding: 10px; margin: 10px 0;">
									<strong>Final Numbers Array:</strong>
									<?php printr($finalnumbers, 0); ?>
								</div>
								
								<?php
								// Sort by year for cleaner JSON
								if(!empty($finalnumbers)):
									ksort($finalnumbers);
								endif;
								
								if($getyearsplayed):
									$encode = json_encode($finalnumbers);
								else:
									$yearsplayed = array($dbrookie => $dbnumber);
									$encode = json_encode($yearsplayed);
								endif;
								?>

								<div class="panel-heading">
									<h3 class="panel-title">Player Jersey Numbers from ESPN API</h3>
								</div>
								<div class="panel-body">
									<?php echo $espn_status_message; ?>
									<?php if($manual_entry_needed && !isset($_POST['save_numbers'])): ?>
										<div class="alert alert-warning">
											<strong>Manual Entry Required:</strong> Jersey numbers could not be automatically retrieved from ESPN API.
											Please enter the jersey numbers manually for each year below.
										</div>
										
										<form method="post" action="">
											<h4>Enter Jersey Numbers by Year:</h4>
											<?php foreach($yearclean as $year): ?>
												<div class="form-group">
													<label for="number_<?php echo $year; ?>">Year <?php echo $year; ?>:</label>
													<input type="number" 
														class="form-control" 
														id="number_<?php echo $year; ?>" 
														name="manual_numbers[<?php echo $year; ?>]" 
														value="<?php echo isset($finalnumbers[$year]) ? $finalnumbers[$year] : ''; ?>" 
														min="0" 
														max="99" 
														required>
												</div>
											<?php endforeach; ?>
											<button type="submit" name="save_numbers" class="btn btn-primary">Save Numbers</button>
										</form>
									<?php else: ?>
										<p><strong>Jersey Numbers (JSON):</strong></p>
										<pre><?php echo $encode; ?></pre>
										
										<form method="post" action="" style="margin-top: 15px;">
											<h4>Edit Jersey Numbers by Year:</h4>
											<p class="text-muted"><small>You can edit or add numbers for any year. Existing numbers can be overridden.</small></p>
											<?php foreach($yearclean as $year): 
												$has_existing = isset($existing_numbers[$year]) && $existing_numbers[$year];
											?>
												<div class="form-group">
													<label for="number_<?php echo $year; ?>">
														Year <?php echo $year; ?>:
														<?php if($has_existing): ?>
															<span class="label label-info">Existing: #<?php echo $existing_numbers[$year]; ?></span>
														<?php endif; ?>
													</label>
													<input type="number" 
														class="form-control" 
														id="number_<?php echo $year; ?>" 
														name="manual_numbers[<?php echo $year; ?>]" 
														value="<?php echo isset($finalnumbers[$year]) ? $finalnumbers[$year] : ''; ?>" 
														min="0" 
														max="99"
														placeholder="<?php echo $has_existing ? 'Change from '.$existing_numbers[$year] : 'Enter number'; ?>">
												</div>
											<?php endforeach; ?>
											<button type="submit" name="save_numbers" class="btn btn-primary">Update Numbers</button>
										</form>
									<?php endif; ?>
								</div>
					
							</div>
	
						</div>
					
					</div>
					
					<!-- GET BASIC PLAYER INFO -->
					<div class="col-xs-12 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php
								//printr($yearclean, 0);
								?>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php
								//printr($alldata, 0); 
								?>
							</div>
						</div>
					</div>
					
				</div>	
				
		</div>
		<?php include_once('main-nav.php'); ?>
	</div>
	
</div>


<script>

// DISABLE TO STOP AUTO RELOAD

//setTimeout(function(){
//	var scrapeclick = '/scrape-pfr-for-numbers/?id=<?php //echo $nextplayer; ?>//';
//    window.location.href = scrapeclick;
// }, 2000);

</script>




		
<?php get_footer(); ?>