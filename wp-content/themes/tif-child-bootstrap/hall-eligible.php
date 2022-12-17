<?php
/*
 * Template Name: HOF Eligible
 * Description: Page for list of Players Eligible for the Hall of Fame
 */
 ?>

<!-- In Dec of 2017 this template was switched over to pull data from mysql not from cached files.  -->
<!-- Make the required arrays and cached files availible on the page -->
<?php 
$season = date("Y");

$playerassoc = get_players_assoc();
$allleaders = get_allleaders();
$hall = get_award('Hall of Fame Inductee', 2);	
foreach ($hall as $winners){
	$newhall[$winners['year']] = $winners['pid'];
}
	
$allhall = get_award_hall();

foreach ($allleaders as $key => $value){
	$justleaders[] = $key;
}

//$test = get_player_career_stats('1998MannQB');

?>

<style>
.form-control {height: 30px !important;}
</style>

<?php get_header(); ?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">

                <!--Value set as 850 in 2015-->
                <!--Revised to 900 in 2022-->
                <?php $checkval = 900; ?>
				<h4>3 Years Retired, <?php echo $checkval;?> Career Points min or Already in Hall</h4>
				<?php 
					
					foreach ($allleaders as $eligible){
						if ($eligible['points'] > $checkval){
							$getleaders[] = $eligible['pid'];
						}
					}
					$build = array_merge($getleaders, $allhall);
					$unique = array_unique($build);
					//printr($unique, 0);
					
					foreach($unique as $e){
						$better[$e] = get_player_career_stats($e);
					}

					?>
					<div class="row">
						<div class="panel widget">
							<div class="widget-body">
								<table class="table table-responsive hall-table">
									<thead>
										<tr>
											<th class="text-center min-width">INHALL</th>
											<th class="text-center min-width">Name</th>
											<th class="text-center min-width">Last Season</th>
											<th class="text-center min-width">Points</th>
											<th class="text-center min-width">Games</th>
											<th class="text-center min-width">Seasons</th>
											<th class="text-center min-width">PPG</th>											
											<th class="text-center min-width">Record</th>
											<th class="text-center min-width">WinPer</th>
											<th class="text-center min-width">Game High</th>
											<th class="text-center min-width">Season High</th>
											<th class="text-center min-width">Titles</th>
										</tr>
									</thead>
									<tbody>
									<?php
									$year = date('Y');

									foreach ($better as $key => $p){

										$checkhall = in_array($p['pid'], $allhall);
										$last = end($p['years']);
										if(($year-2) > $last){
										
											$winper = $p['wins']/$p['games'];
											$name = get_player_name($p['pid']);

											echo '<tr>';
											echo '<th class="text-center min-width">'.$checkhall.'</th>';
											echo '<th class="text-center min-width">'.$name['first'].' '.$name['last'].'</th>';
											echo '<th class="text-center min-width">'.$last.'</th>';
											echo '<th class="text-center min-width">'.$p['points'].'</th>';
											echo '<th class="text-center min-width">'.$p['games'].'</th>';
											echo '<th class="text-center min-width">'.$p['seasons'].'</th>';
											echo '<th class="text-center min-width">'.$p['ppg'].'</th>';
											echo '<th class="text-center min-width">'.$p['wins'].'-'.$p['loss'].'</th>';
											echo '<th class="text-center min-width">'.number_format($winper, 3).'</th>';
											echo '<th class="text-center min-width">'.$p['high'].'</th>';
											echo '<th class="text-center min-width">'.$p['highseasonpts'].'</th>';
											if($p['possebowlwins'] != 0):
                                                echo '<th class="text-center min-width">'.count($p['possebowlwins']).'</th>';
											else:
                                                echo '<th class="text-center min-width">0</th>';
											endif;
											echo '</tr>';

											if($checkhall != 1){
												$notinyet[] = $key;
											}
										}
										// Get Players that have retired and been out for two years but not in the hall yet.  This is the voting eligible group.
                                        if($checkhall != 1){
                                            $notinyetmore[] = $key;
                                        }
									}

									// This is the list og players that have the right number of carrer points, but have not retired yet or only just retired.
									$notinyetalmost = array_diff($notinyetmore, $notinyet);
                                    //printr($notinyetalmost, 0);
										
									?>
										
									</tbody>
								</table>
							</div>
						</div>
						
						<div class="row">
							<div class="col-xs-24">
								<hr>
								<h4>Eligible for PFL Hall of Fame</h4>
								<hr>
								<?php 
									$count = 1;
									//printr($notinyet, 0);
									foreach ($notinyet as $nextup){
										if($count % 3 == 0){
										    echo '<div class="row">';
										}
										echo '<div class="col-xs-24 col-sm-12 col-md-8">';
										 	supercard($nextup);
										echo '</div>';	
										if($count % 3 == 0){
										    echo '</div>';
										}
										$count++;
									}
									
								?>
							</div>
						</div>

                        <div class="row">
                            <div class="col-xs-24">
                                <hr>
                                <h4>Enough Points, But Need to Retire or Still Wait 2 Years.</h4>
                                <hr>
                                <?php
                                $count = 1;
                                //printr($notinyet, 0);
                                foreach ($notinyetalmost as $nextupalmost){
                                    if($count % 3 == 0){
                                        echo '<div class="row">';
                                    }
                                    echo '<div class="col-xs-24 col-sm-12 col-md-8">';
                                    supercard($nextupalmost);
                                    echo '</div>';
                                    if($count % 3 == 0){
                                        echo '</div>';
                                    }
                                    $count++;
                                }

                                ?>
                            </div>
                        </div>

					</div>	
					
					
						
				
								
								
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