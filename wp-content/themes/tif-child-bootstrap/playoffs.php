<?php
/*
 * Template Name: Playoffs
 * Description: Page for list of PFL Playoff Round Games
 */
 ?>

<!-- In Dec of 2017 this template was switched over to pull data from mysql not from cached files.  -->
<!-- Make the required arrays and cached files availible on the page -->
<?php 
	$season = date("Y");
	
	$playoffs = get_playoffs();
	
	foreach($playoffs as $item)
	{
	   $arr[$item[1].$item[5]][$item[3]] = $item[4];
	}

	ksort($arr, SORT_NUMERIC);
	

// 	printr($arr['1995ETS'], 1);

	$teamids = $_SESSION['teamids'];

/*
	get_cache('playersassoc', 0);	
	$players = $_SESSION['playersassoc'];
*/

	$players = get_players_assoc();

	$onlychamps == array();

	$teams = get_teams();

//printr($playoffs, 1);

foreach ($playoffs as $value){
	$id = $value['playoffid'];
	$week = substr($id, 4, 1);
	$seed = substr($id, 5, 1);
	$position = substr($value['playerid'], -2);
	$playerid = $value['playerid'];
	if($seed == 1 OR $seed == 2){
		$hometeam = 'H';
	} else {
		$hometeam = 'A';
	}
	
	$newplayoffs[$id] = array(
		'playerid' => $playerid, 
		'year' => $value['year'], 
		'week' => $week,
		'seed' => $seed,
		'position' => $position,
		'score' => $value['score'],
		'team' => $value['team'],
		'versus' => $value['versus'],
		'overtime' => $value['overtime'],
		'result' => $value['result'],
		'hometeam' => $hometeam
	);
	
}

foreach($newplayoffs as $key => $item)
{
   $playoffs_year[$item['year']][$item['seed']][] = $item;
}

ksort($playoffs_year, SORT_NUMERIC);

foreach ($playoffs_year as $key => $value){
	foreach ($value as $get){
		foreach ($get as $v){
			if($v['overtime'] != 1){
				$scores[$key][$v['team']][] = $v['score'];
			}
			if($v['overtime'] == 1){
				$otscores[$key][$v['team']][] = $v['score'];
			}
		}
	}
}

foreach ($scores as $key => $value){
	foreach ($value as $skey => $svalue){
		$final_scores[$key][$skey] = array_sum($svalue);
	}
}
foreach ($otscores as $key => $value){
	foreach ($value as $skey => $svalue){
		$final_ot_scores[$key][$skey] = array_sum($svalue);
	}
}


foreach ($playoffs_year as $key => $value){
	$one_seed = $value[1];
	$two_seed = $value[2];
	$three_seed = $value[3];
	$four_seed = $value[4];
	
	$playoff_games[$key] = array(
		'game1' => array($one_seed, $four_seed),
		'game2' => array($two_seed, $three_seed)	
	);
}



//printr($playoff_games, 0);


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
			
					<?php
					foreach ($playoff_games as $key => $game){
						echo '<div class="row">';
// 						echo '<h4>'.$key.'</h4>';
							$count = 1;
							
							foreach ($game as $val){
								echo '<div class="col-xs-24 col-sm-12 eq-box-sm">';
									echo '<div class="panel panel-bordered panel-dark">';
										echo '<div class="panel-heading">';
											echo '<div class="panel-control">
											<em><small class="text-muted">Location: </small>'.$teams[$val[0][0]['team']]['stadium'].'</em>
												</div>';
											echo '<h3 class="panel-title">'.$key.' GAME '.$count.'</h3>';
										echo '</div>';
										echo '<div class="panel-body">';
											echo '<div class="row">';
												//INNER BOXES
												
												foreach ($val as $v){	
													
													
														
													$more = 0;
																
													echo '<div class="col-xs-24 col-sm-11">';
														
														$gamescore = $final_scores[$key][$v[0]['team']];
														
														
														if($v[0]['result'] == 1){
															$winner = $v[0]['team'];
														}
														
														if($v[1]['overtime'] == 1){
															$ottext = ' <span class="text-bold">in an overtime game </span>';
															if($v[0]['result'] == 1){
																$more = 1;
															} else {
																$more = 0;
															}
														} else {
															$ottext = '';
															
														}
														
														
														
														echo '<h3 class="playoff-team-game">'.$teamids[$v[0]['team']].'<span class="text-muted" style="font-size:10px;"> ('.$v[0]['seed'].')</span><span style="float:right;">'.( $gamescore + $more ).'</span></h3>';
														
														
														
														foreach ($v as $p){
																
															if(isset($p['playerid'])){
																$name = get_player_name($p['playerid']);
															}
															
															if(isset($name['first'])){
																$first = $name['first'];
																$last = $name['last'];
															} else {
																$first = 'None';
																$last = '';
															}
															
															if ($p['overtime'] == 0){
																echo $first.' '.$last.' <span style="float:right;">'.$p['score'].'</span><br>';
															} else {
																echo '<div class="is-ot">'.$first.' '.$last.' <span style="float:right;">'.$p['score'].'</span></div><br>';
															}
														

															
														}	
													echo '</div>';
												}
												
											echo '</div>';
										echo '</div>'; // close panel-body
										
										echo '<div class="well well-sm">';
											echo '<p><span class="text-bold">'.$teamids[$winner].'</span> win and advance '.$ottext.' to Posse Bowl.</p>';
										echo '</div>';
									
									echo '</div>';
								echo '</div>';
							$count++;
							}
							
							
						echo '</div>';
					} ?>
					

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