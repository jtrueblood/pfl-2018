<?php
/*
 * Template Name: Number Ones
 * Description: Displaying a list of number one overall picks
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
	<?php 
	
	$season = date("Y");
	$teams = get_teams();
	
	$drafts = get_draft_number_ones();

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
						//printr($drafts, 0);
						$count = 0;
						
						foreach ($drafts as $key => $value){
							
							if($count % 3 == 0): 
							    echo '<div class="row">';
							endif;
				
								$playerid = $value['playerid'];
								echo '<div class="col-xs-24 col-sm-12 col-md-8">';
									echo '<div class="panel">
										<div class="panel-heading">
											<h3 class="panel-title"><strong>'.$value['season'].'</strong> Selected by <strong>'.$teams[$value['acteam']]['team'].'</strong></h3>';
										echo '</div>
										<div class="panel-body">';	
											if (!empty($playerid)){
												supercard($playerid);
											} else {
												echo 'No Pick Made';
											}
										echo '</div>	
									</div>';
									
								echo '</div>';
								
							if($count % 3 == 2): 
							    echo '</div>';
							endif;
							
							$count++;	
							
						}	
					?>
					
					
					
				</div>
				<!--End page content-->

			</div><!--END CONTENT CONTAINER-->


		<?php include_once('main-nav.php'); ?>
		<?php include_once('aside.php'); ?>

		</div>
</div> 


</div>
</div>


<?php get_footer(); ?>