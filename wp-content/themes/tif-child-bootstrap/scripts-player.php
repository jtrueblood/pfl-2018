<?php
/*
 * Template Name: Player Scripts
 * Description: Homepage for the PFL Website
 */



/*
$url1=$_SERVER['REQUEST_URI'];
header("Refresh: 5; URL=$url1");
*/


 
 
$playersids = just_player_ids();

printr($playersids, 0);

//$career = insert_wp_career_leaders($playerid);
//$season = insert_wp_season_leaders($playerid);
// printr($testprint, 0);

// sets transient to player data array to random player on homepage

/*
set_team_trans();
set_schedule_trans();
*/



get_header(); 
//start the loop

//build player id array. 

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
		
		<!-- THE ROW -->
		<div class="row">
			<div class="col-xs-12 col-sm-8 eq-box-sm">
			</div>
		</div>
		
	</div>
	
</div>



		
<?php get_footer(); ?>