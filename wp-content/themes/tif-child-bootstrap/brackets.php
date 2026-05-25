<?php
/*
 * Template Name: Brackets
 * Description: Page for list of PFL Postseason by Brackets
 */
 ?>

<!-- In Dec of 2017 this template was switched over to pull data from mysql not from cached files.  -->
<!-- Make the required arrays and cached files availible on the page -->
<?php 
$season = date("Y");

$theyears = the_seasons();
$teamids = $_SESSION['teamids'];
$teams = get_teams();
$players = get_players_assoc();
$champions = get_champions();

$newplayoffs = get_postseason();
$standings1991 = get_standings(1991);

foreach ($theyears as $year){
	$grabyear = get_standings($year);
	if(!empty($grabyear)){
		$stand[$year] = get_standings($year);
	}
}

//printr($stand, 1);

foreach ($stand as $key => $value){
	foreach ($value as $val){
		if($val['seed'] != 0){
			$teamid = $val['teamid'];
			$playoffs = get_playoff_points_by_team_year($key, $teamid, 15);
			$possebowl = get_playoff_points_by_team_year($key, $teamid, 16);
			
			$matchupteams[$key][$val['seed']] = array(
				'team' => $teamid,
				'seed' => $val['seed'],
				'pl_score' => $playoffs,
				'pb_score' =>  $possebowl
			);
		}
	}
}

//$testget = get_playoff_points_by_team_year(1991, 'BUL', 15);
//printr($champions[2017], 0);
//printr($matchupteams[1997], 0);
//printr($testget, 0);
?>

<?php get_header(); ?>

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
					<div class="row">
					<?php 
					foreach ($matchupteams as $key => $value){
	
							echo '<div class="col-xs-24 col-md-18 eq-box-sm">';
								echo '<div class="panel panel-bordered panel-dark">';
									echo '<div class="panel-heading">';
										echo '<div class="panel-control">
										<em><small class="text-muted"></small></em>
											</div>';
										echo '<h3 class="panel-title">'.$key.' Playoff Bracket</h3>';
									echo '</div>';
									echo '<div class="panel-body">';
										//printr($value, 0);
										echo '<div class="row">';
											
											echo '<div class="col-xs-1">';
												// hold column
											echo '</div>';
											
											echo '<div class="col-xs-10">';
												
												$score1 = $value[1]['pl_score'];
												$score2 = $value[2]['pl_score'];
												$score3 = $value[3]['pl_score'];
												$score4 = $value[4]['pl_score'];
												
												// fix overtime issue to break tie and add one point to winner in playoff round
												$overtimealert1 = '';
												$overtimealert2 = '';
												
												if($score1 == $score4){
													$overtimealert1 = 'Overtime';
													if($score1 > $score4){
														$score1++;
													} else {
														$score4++;
													}
												}
												if($score2 == $score3){
													$overtimealert2 = 'Overtime';
													if($score2 > $score3){
														$score2++;
													} else {
														$score3++;
													}
												}
												// end fix
												
												echo '<h3>Playoff Round</h3>';
												
												echo '<div class="boxbrack id="topbrackbox">';
													echo '<h4><small>1. </small><img src="'.get_stylesheet_directory_uri().'/img/'.$value[1]['team'].'-bar.png" class="brackethelm"/>'.$teamids[$value[1]['team']].'<span class="floatright">'.$score1.'</span></h4>';
													echo '<h4><small>4. </small><img src="'.get_stylesheet_directory_uri().'/img/'.$value[4]['team'].'-bar.png" class="brackethelm"/>'.$teamids[$value[4]['team']].'<span class="floatright">'.$score4.'</span></h4>';	
												echo '<h5 style="margin-top:10px;">'.$overtimealert1.'</h5>';
												echo '</div>';
												echo '<div class="location-box">@ '.$teams[$value[1]['team']]['stadium'].'</div>';
												
														
												echo '<div class="boxbrack">';
													echo '<h4><small>2. </small><img src="'.get_stylesheet_directory_uri().'/img/'.$value[2]['team'].'-bar.png" class="brackethelm"/>'.$teamids[$value[2]['team']].'<span class="floatright">'.$score2.'</span></h4>';
													echo '<h4><small>3. </small><img src="'.get_stylesheet_directory_uri().'/img/'.$value[3]['team'].'-bar.png" class="brackethelm"/>'.$teamids[$value[3]['team']].'<span class="floatright">'.$score3.'</span></h4>';
												echo '<h5 style="margin-top:10px;">'.$overtimealert2.'</h5>';
												echo '</div>';
												echo '<div class="location-box">@ '.$teams[$value[2]['team']]['stadium'].'</div>';
											
											echo '</div>';
										
/*
											echo '<div class="col-xs-1">';
												// hold column
											echo '</div>';
*/
											
											$cybpb = array();
											foreach ($value as $val){
												if(!empty($val['pb_score'])){
													$cybpb[$val['seed']] = $val;
												}
											}
											ksort($cybpb);
											
											$champion = $teamids[$champions[$key]['winner']];
											
											echo '<div class="col-xs-8">';
											
											
												echo '<h3>Posse Bowl</h3>';
													//printr($cybpb, 0);
													echo '<div class="boxbrack vert-align">';
														//$scorecheck = '';
														foreach ($cybpb as $k => $v){
															$scorecheck[] = $v['pb_score'];
														}

														foreach ($cybpb as $k => $v){
															$pbscore = $v['pb_score'];
															$pbteam = $teamids[$v['team']];
															if($scorecheck[0] == $scorecheck[1]){
																if($pbteam == $champion){
																	$addscore = $pbscore +1;
																	echo '<h4><strong>'.$pbteam.'<span class="floatright">'.$addscore.'</strong></span></h4>';
																} else {
																	echo '<h4>'.$pbteam.'<span class="floatright">'.$pbscore.'</span></h4>';
																	echo '<h5 style="margin-top:10px;">Overtime</h5>';
																}
																
															} else {
																if($pbteam == $champion){
																	echo '<h4><strong>'.$pbteam.'<span class="floatright">'.$pbscore.'</strong></span></h4>';
																} else {
																	echo '<h4>'.$pbteam.'<span class="floatright">'.$pbscore.'</span></h4>';
																}
															}
															
														}
														
													echo '</div>';
													
													echo '<div class="location-box">Posse Bowl '.$champions[$key]['numeral'].' &mdash; @ '.$champions[$key]['location'].'</div>';
												echo '</div>';
											
											
/*
											echo '<div class="col-xs-1">';
												// hold column
											echo '</div>';
*/
											
											
											echo '<div class="col-xs-4 champ-brack-box">';
											
												echo '<h3>PFL Champion <span class="text-muted">'.$key.'</span></h3>';
												echo '<h4>'.$champion.'</h4>';
												
											echo '</div>';
											
											
										echo '</div>';
										
									echo '</div>';
								echo '</div>';		
							echo '</div>';			
							
							echo '<div class="col-xs-24 col-md-6">';
/*
								echo '<div class="panel panel-bordered panel-dark">';
									echo 'Notes here';
								echo '</div>';
*/
							echo '</div>';
							
							$check++;
					
					}
				?>
					
					
					</div><!-- end row -->	
					
			
					
				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->


		<?php include_once('main-nav.php'); ?>
		<?php include_once('aside.php'); ?>

		</div>
</div> 

<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>