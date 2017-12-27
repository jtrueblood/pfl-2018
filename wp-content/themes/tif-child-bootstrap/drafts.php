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
						
						<!-- start sidebar -->
						
						<div class="col-xs-24 col-sm-8">
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
									
									if ($det_year == $year){
										$detdraft[$det_year] = array($det_att, $det_phone, $det_notes);
									}
								
								
								
								endwhile;
							else :
							endif;
							
							foreach ($currdraft as $getnew){
								$verynew = array($getnew[0], $getnew[1], $getnew[2],);
							}
							foreach ($detdraft as $getnew){
								$thenew = array($getnew[0], $getnew[1], $getnew[2],);
							}
						
							?>
							<div class="col-xs-24 col-sm-8">
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