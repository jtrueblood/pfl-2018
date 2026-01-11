<?php
/*
 * Template Name: Pro Bowl
 * Description: Page Pro Bowl overviews
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	$season = 2018;
	$promvp = get_award('Pro Bowl MVP', 2);
 	//printr($probowldata, 0);
	
	$getproboxes = $wpdb->get_results("select * from wp_probowlbox", ARRAY_N);
	
	//printr($getproboxes, 1);
	
	foreach ($getproboxes  as $revisequery){
		$proboxes[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'playerid' => $revisequery[1], 
			'pos' => $revisequery[2], 
			'team' => $revisequery[3],  
			'league' => $revisequery[4],
			'year' => $revisequery[5],
			'points' => $revisequery[6],
			'starter' => $revisequery[7],
			'pointsused' => $revisequery[8]
		);
	}
	
	//regroup boxscores by year	
	foreach($proboxes as $key => $item){
	   $probowldata[$item['year']][$item['playerid']] = $item;
	}
	
	ksort($probowldata, SORT_NUMERIC);
	// get an associative array of all players
	$pname = get_players_assoc();
	
	// probowl details
	$getprobowl = $wpdb->get_results("select * from wp_probowl", ARRAY_N);
	
	foreach ($getprobowl as $value){
		$probowldetails[$value[1]] = array(
			'winner' => $value[2],	
			'host' => $value[3],
			'egad' => $value[4],
			'dgas' => $value[5],
			'mvp' => $promvp[$value[1]]['pid']
		);
	}
	//printr($probowldetails, 1);
	
	
	
?>

<?php get_header(); ?>

<div class="boxed add-to-top">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php the_title();?></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
				
					<div class="row">
					
					<!-- Left column for pro bowl panels -->
					<div class="col-xs-24 col-md-16">
					<?php
				foreach ($probowldetails as $key => $value){
                    $theyear = $key;
						// get the first row
                echo '<div class="row">';
					echo '<div class="col-xs-24">';
						echo '<div class="panel panel-bordered panel-dark">';
							echo '<div class="panel-heading">';
							echo '<h3 class="panel-title"><span class="text-bold">'.$key.'</span> Pro Bowl</h3>';
							echo '</div>';
							echo '<div class="panel-body">';
							if ($value['egad'] > $value['dgas']){
								echo '<span class="text-2x text-bold">EGAD </span>  <span class="text-2x text-bold pull-right">'.$value['egad'].'</span><br>';
								echo '<span class="text-2x text-thin">DGAS </span>  <span class="text-2x text-thin pull-right">'.$value['dgas'].'</span>';
							}  else {
								echo '<span class="text-2x text-bold">DGAS </span>  <span class="text-2x text-bold pull-right">'.$value['dgas'].'</span><br>';
								echo '<span class="text-2x text-thin">EGAD </span>  <span class="text-2x text-thin pull-right">'.$value['egad'].'</span>';
							} 
?>
							<div class="row">
								<hr/>
										<?php 
											$probowlyearteam = array();
											foreach($probowldata[$key] as $key => $item){
											   $probowlyearteam[$item['league']][] = $item;
											}
											ksort($probowlyearteam, SORT_NUMERIC);
	
										?>
		
										<div class="col-xs-24 col-sm-12">
											<div class="row">
												<?php 
													$probowlegad = array(); 
													foreach($probowlyearteam['EGAD'] as $key => $item){
													   $probowlegad[$item['pos']][] = $item;
													}
													//printr($probowlegad, 0);
												
												$labels = array('Player', 'Team', 'Starter', 'Points');	
												tablehead('EGAD Boxscore', $labels);	
												
												$egad = '';
												foreach ($probowlegad as $val){
													foreach ($val as $key => $valued){
														$egad .= '<tr><td>'.$pname[$valued['playerid']][0].' '.$pname[$valued['playerid']][1].'</td>';
														$egad .= '<td class="min-width">'.$valued['team'].'</td>';
														if($valued['starter'] == 0){
															$egad .= '<td class="min-width text-center"><i class="fa fa-circle"></i></td>';
														} else {
															$egad .= '<td class="min-width"></td>';
														}
														if($valued['pointsused'] == 1){
															$egad .= '<td class="min-width text-right"><strong>'.$valued['points'].'</strong></td></tr>';
														} else {
															$egad .= '<td class="min-width text-right">'.$valued['points'].'</td></tr>';
														}
													}
												}
												
												echo $egad;

												tablefoot('');
												
												?>
												

											</div>
										</div>		
										
										<div class="col-xs-24 col-sm-12">
											<div class="row">
												<?php 
													$probowldgas = array(); 
													foreach($probowlyearteam['DGAS'] as $key => $item){
													   $probowldgas[$item['pos']][] = $item;
													}
													//printr($probowldgas, 0);
													
													$labels = array('Player', 'Team', 'Starter', 'Points');	
													tablehead('DGAS Boxscore', $labels);	
													
													$dgas = '';
													foreach ($probowldgas as $val){
														foreach ($val as $key => $valued){
															$dgas .= '<tr><td>'.$pname[$valued['playerid']][0].' '.$pname[$valued['playerid']][1].'</td>';
															$dgas .= '<td class="min-width">'.$valued['team'].'</td>';
															if($valued['starter'] == 0){
															$dgas .= '<td class="min-width text-center"><i class="fa fa-circle"></i></td>';
															} else {
																$dgas .= '<td class="min-width"></td>';
															}
															if($valued['pointsused'] == 1){
																$dgas .= '<td class="min-width text-right"><strong>'.$valued['points'].'</strong></td></tr>';
															} else {
																$dgas .= '<td class="min-width text-right">'.$valued['points'].'</td></tr>';
															}
														}
													}
													
													echo $dgas;
	
													tablefoot('');
												
												?>
												
											</div>

										</div>

										
								</div>
                            <?php
                            if($theyear == 2023) {
                                echo '<p>This was the first ever OT Pro Bowl.  All starters scores were used, followed by the backups. Normal OT rules applied.</p>';
                            }
                            ?>
							</div>
							
							<?php
							$theman = $value['mvp'];
								
							echo '<hr><div class="row">
                                <div class="pro-mvp">Game MVP: <span class="text-bold text-dark">'.$pname[$theman][0].' '.$pname[$theman][1].'</div>
                            </div>';
						
						echo '</div>';
					echo '</div>';
                echo '</div>';
				}	
				
				?>
					</div><!-- End left column -->
					
					<!-- Right column for summary -->
					<div class="col-xs-24 col-md-8">
						<?php
							// Calculate overall wins, total points, and consecutive wins
							$egad_wins = 0;
							$dgas_wins = 0;
							$egad_total_points = 0;
							$dgas_total_points = 0;
							
							// Sort by year to calculate consecutive wins
							$sorted_details = $probowldetails;
							ksort($sorted_details);
							
							$egad_consecutive = 0;
							$dgas_consecutive = 0;
							$egad_max_consecutive = 0;
							$dgas_max_consecutive = 0;
							
							foreach($sorted_details as $year => $detail):
								$egad_total_points += $detail['egad'];
								$dgas_total_points += $detail['dgas'];
								
								if($detail['winner'] == 'EGAD'):
									$egad_wins++;
									$egad_consecutive++;
									$dgas_consecutive = 0;
									if($egad_consecutive > $egad_max_consecutive):
										$egad_max_consecutive = $egad_consecutive;
									endif;
								elseif($detail['winner'] == 'DGAS'):
									$dgas_wins++;
									$dgas_consecutive++;
									$egad_consecutive = 0;
									if($dgas_consecutive > $dgas_max_consecutive):
										$dgas_max_consecutive = $dgas_consecutive;
									endif;
								endif;
							endforeach;
							
							$labels = array('League', 'Wins', 'Total Pts', 'Consec Wins');
							tablehead('Pro Bowl Summary', $labels);
							echo '<tr><td>EGAD</td><td>'.$egad_wins.'</td><td>'.$egad_total_points.'</td><td>'.$egad_max_consecutive.'</td></tr>';
							echo '<tr><td>DGAS</td><td>'.$dgas_wins.'</td><td>'.$dgas_total_points.'</td><td>'.$dgas_max_consecutive.'</td></tr>';
							tablefoot('');
							
							// Calculate top team performances
							$team_performances = array();
							foreach($probowldetails as $year => $detail):
								$team_performances[] = array(
									'year' => $year,
									'team' => 'EGAD',
									'points' => $detail['egad']
								);
								$team_performances[] = array(
									'year' => $year,
									'team' => 'DGAS',
									'points' => $detail['dgas']
								);
							endforeach;
							
							// Sort by points descending
							usort($team_performances, function($a, $b) {
								return $b['points'] - $a['points'];
							});
							
							// Get top 5
							$top_5 = array_slice($team_performances, 0, 5);
							
							echo '<br>';
							$labels = array('Year', 'Team', 'Points');
							tablehead('Top 5 Team Performances', $labels);
							foreach($top_5 as $perf):
								echo '<tr><td>'.$perf['year'].'</td>';
								echo '<td>'.$perf['team'].'</td>';
								echo '<td>'.$perf['points'].'</td></tr>';
							endforeach;
							tablefoot('');
							
							// Count games by player
							$player_games = array();
							foreach($proboxes as $box):
								if(!isset($player_games[$box['playerid']])):
									$player_games[$box['playerid']] = 0;
								endif;
								$player_games[$box['playerid']]++;
							endforeach;
							
							// Sort by games descending
							arsort($player_games);
							
							// Get top 10
							$top_10_players = array_slice($player_games, 0, 10, true);
							
							echo '<br>';
							$labels = array('Player', 'Games');
							tablehead('Most Games Played', $labels);
							foreach($top_10_players as $playerid => $games):
								echo '<tr><td>'.$pname[$playerid][0].' '.$pname[$playerid][1].'</td>';
								echo '<td>'.$games.'</td></tr>';
							endforeach;
							tablefoot('');
							
							// Calculate total points by player
							$player_points = array();
							foreach($proboxes as $box):
								if(!isset($player_points[$box['playerid']])):
									$player_points[$box['playerid']] = 0;
								endif;
								$player_points[$box['playerid']] += $box['points'];
							endforeach;
							
							// Sort by points descending
							arsort($player_points);
							
							// Get top 10
							$top_10_scorers = array_slice($player_points, 0, 10, true);
							
							echo '<br>';
							$labels = array('Player', 'Points');
							tablehead('Most Total Points', $labels);
							foreach($top_10_scorers as $playerid => $points):
								echo '<tr><td>'.$pname[$playerid][0].' '.$pname[$playerid][1].'</td>';
								echo '<td>'.$points.'</td></tr>';
							endforeach;
							tablefoot('');
							
							// Find highest individual game scores
							$top_games = array();
							foreach($proboxes as $box):
								$top_games[] = array(
									'playerid' => $box['playerid'],
									'year' => $box['year'],
									'league' => $box['league'],
									'team' => $box['team'],
									'points' => $box['points']
								);
							endforeach;
							
							// Sort by points descending
							usort($top_games, function($a, $b) {
								return $b['points'] - $a['points'];
							});
							
							// Get top 10
							$top_10_games = array_slice($top_games, 0, 10);
							
							echo '<br>';
							$labels = array('Player', 'Year', 'PB Team', 'PFL Team', 'Points');
							tablehead('Highest Individual Games', $labels);
							foreach($top_10_games as $game):
								echo '<tr><td>'.$pname[$game['playerid']][0].' '.$pname[$game['playerid']][1].'</td>';
								echo '<td>'.$game['year'].'</td>';
								echo '<td>'.$game['league'].'</td>';
								echo '<td>'.$game['team'].'</td>';
								echo '<td>'.$game['points'].'</td></tr>';
							endforeach;
							tablefoot('');
							
							// Calculate consecutive Pro Bowl appearances
							$player_years = array();
							foreach($proboxes as $box):
								if(!isset($player_years[$box['playerid']])):
									$player_years[$box['playerid']] = array();
								endif;
								$player_years[$box['playerid']][] = $box['year'];
							endforeach;
							
							// Find longest streaks for each player
							$player_streaks = array();
							foreach($player_years as $playerid => $years):
								$years = array_unique($years);
								sort($years);
								
								$current_streak = 1;
								$max_streak = 1;
								$streak_start = $years[0];
								$max_start = $years[0];
								
								for($i = 1; $i < count($years); $i++):
									if($years[$i] == $years[$i-1] + 1):
										$current_streak++;
										if($current_streak > $max_streak):
											$max_streak = $current_streak;
											$max_start = $streak_start;
										endif;
									else:
										$current_streak = 1;
										$streak_start = $years[$i];
									endif;
								endfor;
								
								$player_streaks[] = array(
									'playerid' => $playerid,
									'streak' => $max_streak,
									'start_year' => $max_start,
									'end_year' => $max_start + $max_streak - 1
								);
							endforeach;
							
							// Sort by streak length descending
							usort($player_streaks, function($a, $b) {
								return $b['streak'] - $a['streak'];
							});
							
							// Get top 10
							$top_10_streaks = array_slice($player_streaks, 0, 10);
							
							echo '<br>';
							$labels = array('Player', 'Streak', 'Years');
							tablehead('Longest Consecutive Appearances', $labels);
							foreach($top_10_streaks as $streak):
								echo '<tr><td>'.$pname[$streak['playerid']][0].' '.$pname[$streak['playerid']][1].'</td>';
								echo '<td>'.$streak['streak'].'</td>';
								echo '<td>'.$streak['start_year'].' - '.$streak['end_year'].'</td></tr>';
							endforeach;
							tablefoot('');
						?>
					</div><!-- End right column -->
					
					</div><!-- End row -->

				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->
			
			<?php include_once('main-nav.php'); ?>
			<?php include_once('aside.php'); ?>

		</div>
		
</div> 

		
</div>
</div>


<?php get_footer(); ?>