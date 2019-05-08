<?php
/*
 * Template Name: Seasons
 * Description: Page for displaying information by Year
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->

<?php get_header(); ?>

<?php 	
	

$season = date("Y");
$year = $_GET['id'];

$years = the_seasons();
$standing = get_standings($year);
$playersassoc = get_players_assoc();
$teamlist = teamlist();

?>

<div class="boxed">
			
	<div id="page-content">	
					
		<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<!-- LEFT COL -->
				
				<div class="col-xs-24 col-md-4">
										
					<div class="panel widget">
						<div class="widget-header">
							<h2><?php echo $year; ?></h2>
							<h3>PFL Season</h3>
						</div>
						<div class="widget-body">
							<?php $champs = get_just_champions(); 
								echo '<h3>'.$teamlist[$champs[$year]].'</h3>';
							?>
							<h4>PFL Champions</h4>
						</div>
					</div>
				</div>
				
				
				<!-- MIDDLE COL -->
				
				<div class="col-xs-24 col-sm-11">
						
					<?php 
					include_once('inc/season_awards.php');
					include_once('inc/season_leaders.php');
					?>
						
				</div>
				
				
				
				<!-- RIGHT COL -->
					
				<div class="col-xs-24 col-md-9">

					<?php 
						selectseason();
						include_once('inc/season_standings.php');
						include_once('inc/season_draft.php');
					?>
						
				</div>
						

			</div><!--End page content-->

		</div><!--END CONTENT CONTAINER-->


		<?php include_once('main-nav.php'); ?>

		</div>
</div> 

<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>