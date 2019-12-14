<?php
/*
 * Template Name: Python Scripts
 * Description: Run Python scripts for sportsreference api
 */
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
				
<!--
						<?php 

						$command = escapeshellcmd('/usr/custom/test.py');
						$output = shell_exec($command);
						echo $output;
						
						?>
-->

					<?php 
							$get = get_sportsref_shedule_just_ids();
							//printr($get, 0);
							
							
							
							
							foreach ($get as $key => $value){
	
								insert_pfl_week_id($key, $value);
									
							}

							
							
					?>

				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->
			
			<?php include_once('main-nav.php'); ?>
			<?php include_once('aside.php'); ?>

		</div>
		
</div> 

		
</div>
</div>



<?php get_footer(); ?>