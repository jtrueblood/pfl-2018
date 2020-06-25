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

<?php include_once('main-nav.php'); ?>
	
<div id="page-content" class="season-page">	
				
	<!--CONTENT CONTAINER-->
		<div id="content-container">
			
			<!-- LEFT COL -->
			<div class="col-xs-24 col-sm-6">
									
				<div class="panel widget">
					<div class="left-widget widget-body">
						<h3><?php echo $year; ?> PFL Season</h3>
						<hr>
						<?php $champs = get_just_champions(); 
							echo '<h4>'.$teamlist[$champs[$year]].'</h4>';
						?>
						<h5>PFL Champions</h5>
					</div>
				</div>
				
			</div>		
					
			<!-- MIDDLE COL -->
			<div class="col-xs-24 col-md-9">

				<?php 
					selectseason();
					include_once('inc/season_standings.php');
					//include_once('inc/season_draft.php');
				?>
				
			</div>
			
			
			
			
			<!-- RIGHT COL -->
			<div class="col-xs-24 col-sm-9">
					
				<?php 
				include_once('inc/season_awards.php');
				include_once('inc/season_leaders.php');
				?>
					
			</div>
				
			

	</div><!--END CONTENT CONTAINER-->

</div><!--End page content-->		







<?php get_footer(); ?>