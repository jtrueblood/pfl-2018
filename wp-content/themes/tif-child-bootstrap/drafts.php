<?php
/*
 * Template Name: Drafts
 * Description: Page for displaying all of the annual drafts
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
		
	$season = date("Y");
	$year = $_GET['id'];
			
/*
	get_cache('theyears', 0);	
	$theyears = $_SESSION['theyears'];

	get_cache('drafts', 0);	
	$drafts = $_SESSION['drafts'];
*/
	$drafts = get_drafts();
 	//printr($drafts, 1);

	
	// get draft for just this year
	foreach ($drafts as $getdraft){
		if ($getdraft['season'] == $year){
			if ($getdraft['orteam'] != $getdraft['acteam']){
				$tradehappened = 1;
			} else {
				$tradehappened = 0;
			}
			$year_array[] = array(
				'key' => $getdraft['key'], 
				'season' => $getdraft['season'],
				'round' => $getdraft['round'],	
				'pick' => $getdraft['pick'],	
				'overall' => $getdraft['overall'],
				'playerfirst' => $getdraft['playerfirst'],
				'playerlast' => $getdraft['playerlast'],	
				'position' => $getdraft['position'],
				'playerid' => $getdraft['playerid'],	
				'orteam' => $getdraft['orteam'],	
				'acteam' => $getdraft['acteam'],	
				'tradeid' => $getdraft['tradeid'],
				'tradehappened' => $tradehappened		
			);
		}
	}
	
	//printr($year_array, 0);	
	
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
						<!-- start main col -->
						
						<div class="col-xs-24 col-sm-12">
							<?php 
								getdraft($year, $year_array);
							?>
						</div>

                        <!-- Previous/Next Year Navigation -->
                        <div class="text-center mar-btm">
                            <?php
                            $prev_year = $year - 1;
                            $next_year = $year + 1;
                            $current_year = date("Y");

                            // Check if previous draft exists (assuming drafts start from 1991)
                            if ($prev_year >= 1991) {
                                echo '<a href="?id=' . $prev_year . '" class="btn btn-primary"><i class="fa fa-chevron-left"></i> ' . $prev_year . ' Draft</a> ';
                            } else {
                                echo '<a href="#" class="btn btn-default disabled"><i class="fa fa-chevron-left"></i> Previous</a> ';
                            }

                            echo '<span class="text-bold" style="margin: 0 15px;">' . $year . ' Draft</span>';

                            // Check if next draft exists (don't go beyond current year)
                            if ($next_year <= $current_year) {
                                echo ' <a href="?id=' . $next_year . '" class="btn btn-primary">' . $next_year . ' Draft <i class="fa fa-chevron-right"></i></a>';
                            } else {
                                echo ' <a href="#" class="btn btn-default disabled">Next <i class="fa fa-chevron-right"></i></a>';
                            }
                            ?>
                        </div>

						<!-- start sidebar -->
						
						<div class="col-xs-24 col-sm-12">
							<?php selectseason(); ?>							
						</div>
							
								
							<?php 
							if( have_rows('draft_info') ):
								while ( have_rows('draft_info') ) : the_row();
									
									$rep_year = get_sub_field('year');
									$rep_date = get_sub_field('date');
									$rep_location = get_sub_field('location');
									$rep_address = get_sub_field('address');
									
								
									if ($rep_year == $year){
										$currdraft[$rep_year] = array($rep_date, $rep_location, $rep_address);
									}
								
								endwhile;
							else :
							endif;
							
							if( have_rows('draft_details') ):
								while ( have_rows('draft_details') ) : the_row();
									
									$det_year = get_sub_field('year');
									$det_att = get_sub_field('attendees');
									$det_phone = get_sub_field('attendees_phone');
									$det_notes = get_sub_field('notes');
                                    $det_pick_notes = get_sub_field('best_pick_notes');
									
									if ($det_year == $year){
										$detdraft[$det_year] = array($det_att, $det_phone, $det_notes, $det_pick_notes);
									}

								endwhile;
							else :
							endif;
							
							foreach ($currdraft as $getnew){
								$verynew = array($getnew[0], $getnew[1], $getnew[2],$getnew[3]);
							}
							foreach ($detdraft as $getnew){
								$thenew = array($getnew[0], $getnew[1], $getnew[2],$getnew[3]);
							}
						
							?>
							<div class="col-xs-24 col-sm-12">
								<div class="panel">
									<div class="panel-body">
										
										<?php echo '<h3>The '.$year.' PFL Draft</h3>';?>
										
										<?php if(!empty($verynew[0])){
											echo '<p class="text-primary">Date:</p>';
											echo '<h5 class="mar-btm">'.$verynew[0].'</h5>';
										} ?>
										
										<p class="text-primary">Location:</p>
										<h5 class="mar-btm"><?php echo $verynew[1]; ?></h5>
										
										<p class="text-primary">Address:</p>
										<h5 class="mar-btm"><?php echo $verynew[2]; ?></h5>
										
										<p class="text-primary">Attendees:</p>
										<h5 class="mar-btm"><?php echo $thenew[0]; ?></h5>
										
										<?php if(!empty($verynew[0])){
											echo '<p class="text-primary">On The Phone:</p>';
											echo '<h5 class="mar-btm">'.$thenew[1].'</h5>';
										} ?>
										
										<p class="text-primary">Draft Notes:</p>
										<p><?php echo $thenew[2]; ?></p>
									
									</div>
								</div>

                                <div class="panel">
                                    <div class="panel-body">
                                        <p class="text-primary">Draft Analysis:</p>
                                        <?php 
                                        // Read draft analysis from HTML file
                                        $analysis_file = get_stylesheet_directory() . '/draft-analysis/draft_analysis_' . $year . '.html';
                                        if (file_exists($analysis_file)) {
                                            echo file_get_contents($analysis_file);
                                        } else {
                                            echo '<p class="text-muted">No analysis available for this draft year. Looking for: ' . $analysis_file . '</p>';
                                        }
                                        if($season - $year >= 10){
                                            echo '<br>';
                                            echo '<p class="text-primary">This draft is at least 10 years old, so the analysis is final.</p>';
                                        }
                                       ?>
                                    </div>
                                </div>
							</div>
					
						</div>
						
						<!-- end sidebar -->
						
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