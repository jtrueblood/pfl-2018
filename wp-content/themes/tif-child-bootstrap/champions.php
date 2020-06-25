<?php
/*
 * Template Name: Champions
 * Description: Page for list of PFL Champions
 */
 ?>

<!-- In Dec of 2017 this template was switched over to pull data from mysql not from cached files.  -->
<!-- Make the required arrays and cached files availible on the page -->
<?php 
	$season = date("Y");
	
	$mydb = new wpdb('root','root','pflmicro','localhost');
	$getchampionstable = $wpdb->get_results("select * from wp_champions", ARRAY_N);
	
	$buildchamps = array();
	foreach ($getchampionstable as $revisequery){
		$champions[$revisequery[0]] = array(
			$revisequery[0], 
			$revisequery[1], 
			$revisequery[2], 
			$revisequery[3],  
			$revisequery[4],
			$revisequery[5],
			$revisequery[6],
			$revisequery[7],
			$revisequery[8]
		);
	}


	$getpbmvp = $wpdb->get_results("select * from wp_awards WHERE award = 'Posse Bowl MVP'", ARRAY_N);
	
	foreach ($getpbmvp as $revisequery){
		$pbmvp[] = array(
			'awardid' => $revisequery[0], 
			'award' => $revisequery[1], 
			'year' => $revisequery[2], 
			'first' => $revisequery[3],  
			'last' => $revisequery[4],
			'team' => $revisequery[5],
			'position' => $revisequery[6],
			'owner' => $revisequery[7],
			'pid' => $revisequery[8],
			'gamepoints' => $revisequery[9]
		);
		
		$theyears[] = $revisequery[2];
		
	}
	
	$getplayoffs = $wpdb->get_results("select * from wp_playoffs WHERE week = '16'", ARRAY_N);

	foreach ($getplayoffs as $revisequery){
		$playoffs[] = array(
			$revisequery[0], 
			$revisequery[1], 
			$revisequery[2], 
			$revisequery[3],  
			$revisequery[4],
			$revisequery[5],
			$revisequery[6],
			$revisequery[7]
		);
	}
	
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
					$i = 0;
					$gamereview = get_field('game_review' ); // get all the rows
					
					for ($c = 1991; $c <= $season; $c++) {
						if($champions[$c][0] > 0){ // check to see if PB happened yet that year.
							
							$theyear = $champions[$c][0];
							$numeral = $champions[$c][1];
							$teama = $champions[$c][2];
							$scorea = $champions[$c][3];
							$seeda = $champions[$c][4];
							$teamb = $champions[$c][5];
							$scoreb = $champions[$c][6];
							$seedb = $champions[$c][7];
							$location = $champions[$c][8];
							
							// get the first row
						
							$modulus = 3;
						
							if ($i % $modulus == 0){
								echo '<div class="row">';
							}	
							echo '<div class="col-sm-12 col-lg-8 eq-box-sm">';
								echo '<div class="panel panel-bordered panel-dark">';
									echo '<div class="panel-heading">';
									echo '<div class="panel-control">
									<em><small class="text-muted">Location: </small>'.$location.'</em>
										</div>';
										echo '<h3 class="panel-title">'.$theyear.' <i class="fa fa-angle-double-right text-muted"></i> Posse Bowl '.$numeral.'</h3>';
									echo '</div>';
									echo '<div class="panel-body">';
										echo $seeda.'  <span class="text-2x text-bold">'.$teamids[$teama].'</span>  <span class="text-2x text-bold pull-right">'.$scorea.'</span><br>';
										echo $seedb.'  <span class="text-2x text-thin">'.$teamids[$teamb].'</span>  <span class="text-2x text-thin pull-right">'.$scoreb.'</span>';
									
									echo '<hr/><h5>Game Details</h5>';
									echo '<p class="text-dark">';
										echo $gamereview[$i]['writeup'];
										echo '<hr/><h5>Boxscores</h5>';
																				
											echo '<div class="col-xs-12 team-bar" style="background-image:url('.get_stylesheet_directory_uri().'/img/'.$teama.'-bar.png);">';
											echo '</div>'; 
											echo '<div class="col-xs-12 team-bar" style="background-image:url('.get_stylesheet_directory_uri().'/img/'.$teamb.'-bar.png);">';
											echo '</div>';
											
										
											$scoresa = $arr[$theyear.$teama];								
											$scoresb = $arr[$theyear.$teamb];
											
											echo '<div class="col-xs-12">';
												foreach ($scoresa as $key => $value){
													echo $players[$key][0].' '.$players[$key][1].' <span class="pull-right">'.$value.'</span><br>';
												}
											echo '</div>';
											
											echo '<div class="col-xs-12">';
												foreach ($scoresb as $key => $value){
													echo $players[$key][0].' '.$players[$key][1].' <span class="pull-right">'.$value.'</span><br>';
												}
											echo '</div>';
											
									echo '</div>';	
									
									$onlychamps[$c] = $champions[$c][2];
									$freqs = array_count_values($onlychamps);
									$freq_of = $freqs[$teama];
									
									echo '<div class="well well-sm">Game MVP: <span class="text-bold text-dark">'.$pbmvp[$i]['first'].' '.$pbmvp[$i]['last'].'</span>, '.$pbmvp[$i]['team'].'<br/>';
									echo 'Number of <span class="text-bold text-dark">'.$teamids[$teama].'</span> Titles: <span class="text-bold text-dark">'.$freq_of.'</span>';
									echo '</div>';
				
									$i++;
									
								echo '</div>';
							echo '</div>';
							if ($i % $modulus == 0){
								echo '</div>';
							} /* close 'row' every 3rd time through the loop */
						}
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