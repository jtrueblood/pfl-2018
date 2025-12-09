<?php
/*
 * Template Name: All Awards
 * Description: Consolidated page displaying all awards and notable events
 */
 ?>

<?php get_header(); ?>

<?php 
	// Get the list of seasons
	$theseasons = the_seasons();
	
	// Get champions data
	$champions = get_just_champions();
	
	// Get team IDs for display names
	$teamids = $_SESSION['teamids'];
?>

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
				
				<div class="col-xs-24">
					<?php
					$labels = array('Season', 'Champion');
					tablehead('All Awards by Season', $labels);
					
					foreach ($theseasons as $season) {
						echo '<tr>';
						echo '<td class="min-width bord-rgt">' . $season . '</td>';
						
						// Display champion if exists
						if (isset($champions[$season])) {
							$champion_id = $champions[$season];
							$champion_name = isset($teamids[$champion_id]) ? $teamids[$champion_id] : $champion_id;
							echo '<td><strong>' . $champion_name . '</strong></td>';
						} else {
							echo '<td>—</td>';
						}
						
						echo '</tr>';
					}
					
					tablefoot('');
					?>
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
