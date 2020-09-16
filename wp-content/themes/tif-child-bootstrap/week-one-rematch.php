<?php
/*
 * Template Name: Week One Rematch
 * Description: Used for creatibng a new player from the MFL API. Pass in the MFL player ID into the form field
 */
 ?>

<!-- In Dec of 2017 this template was switched over to pull data from mysql not from cached files.  -->
<!-- Make the required arrays and cached files availible on the page -->
<?php 
$season = date("Y");

?>

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
					
					<div class="panel panel-bordered panel-light">
						<div class="panel-heading">
							<h3 class="panel-title">Week One Revenge Games</h3>
						</div>
						<div class="panel-body">	
							<?php 
								$pbgames = revenge_game();
								
								//printr($pbgames, 0);
								$stands = 0;
								$revenge = 0;
								
								foreach ($pbgames as $key => $value):
									if($key >= 1996): //we started the rematch game at the beginning of the 1996 season
										if($value['pb_winner'] == $value['next_win']):
											echo '<h4>'.$key.' - It Stands / '.$value['next_win'].' over '.$value['next_loser'].'</h4>';
											$stands++;
										endif;
										if($value['pb_winner'] == $value['next_loser']):
											echo '<h4>'.$key.' - Revenge!/ '.$value['next_win'].' over '.$value['next_loser'].'</h4>';
											$revenge++;
										endif;	
									endif;
								endforeach;
								
								echo '<hr>';
								echo '<h4>Stands Up - '. $stands.'</h4>';
								echo '<h4>Stands Up - '. $revenge.'</h4>';							
								?>									     
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