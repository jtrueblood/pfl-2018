<?php
/*
 * Template Name: Team Page
 * Description: Page for displaying team history 
 */
 ?>


<?php get_header(); ?>

<!-- SET GLOBAL PLAYER VAR -->
<?php 
	
// $playerid = '2011SproRB';
$teamid = $_GET['id'];
$year = date("Y");
$team_all_ids = get_teams();
$seasons = the_seasons();
$players = get_players_assoc();
$champs = get_champions();
$thisteam = get_team_results('wp_team_'.$teamid);

//$player = get_player_data('2004BreeQB');
//$player = get_raw_player_data_team('2004BreeQB', $teamid);
//printr($player, 1);
foreach ($seasons as $year){
	$stand[$year] = get_standings_by_team($year, $teamid); 
}

foreach ($stand as $key => $value){
	$diffo = $pts - $value[0]['ptsvs'];
	
	$highpts[] = $value[0]['pts'];
	$highdiff[] = $diffo;
	$wins[] = $value[0]['win'];
	$loss[] = $value[0]['loss'];
}

$besthigh = max($highpts);
$bestdiff = max($highdiff);
$bestwins = max($wins);
$totalwins = array_sum($wins);
$totalloss = array_sum($loss);
$gamesplayed = $totalwins + $totalloss;
$totalwinper = $totalwins / $gamesplayed;


// get hall of famers for this team.
$hall = get_award('Hall of Fame Inductee', 2);
foreach($hall as $key => $item){
   $arr_hall[$item['team']][$key] = $item;
}
if(isset($arr_hall)){
	ksort($arr_hall, SORT_NUMERIC);
	$award_hall = $arr_hall[$teamid];
}

// get all player awards by team
$teamawards = get_award_team($teamid);
if(isset($teamawards)){
    foreach($teamawards as $key => $item){
       $arr_taward[$item['award']][$key] = $item;
    }
	ksort($arr_taward, SORT_NUMERIC);
}

// build leaders by team
$playersall = get_players_assoc();
//$careerstats = get_player_career_stats_team('1994ElamPK', $teamid);
//printr($playersall, 1);

foreach ($playersall as $key => $val){
	$careerstats_team[] = get_player_career_stats_team($key, $teamid);
}
//printr($careerstats_team, 1);
?>

<!--CONTENT CONTAINER-->
<div class="boxed">

<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
	<!-- Championship banners -->
    <?php include_once('inc/team_championships.php');?>

	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">

		<div class="row">
		
		<!-- LEFT COLUMN -->
		
		<div class="col-xs-24 col-sm-6 left-column">
			
			<?php include_once('inc/team_helmet_name.php');?>
			
			<?php include_once('inc/team_retired_nos.php');?>
				
			<?php include_once('inc/team_awards.php');?>	
			
			<?php include_once('inc/team_probowl_selections.php');?>

            <?php include_once('inc/team_overtime.php');?>
			
		</div>
		
		
		<!-- CENTER COLUMN -->
		
		<div class="col-xs-24 col-sm-12">
		
			<!-- Standings -->
			<?php include_once('inc/team_standings.php');?>
			
			<?php include_once('inc/team_points_wins_chart.php');?>
		
			<?php include_once('inc/team_leaders.php');?>
			
			<?php include_once('inc/team_playoffs.php');?>

            <?php include_once('inc/team_head_to_head.php');?>
						
		</div>
	
	
		<!-- SELECT DROPDOWN -->
		<div class="hidden-xs hidden-sm col-md-6">	
				
			<?php include_once('inc/teams_select.php');?>
			
			<?php include_once('inc/team_stadium.php');?>
			
			<?php include_once('inc/team_hall.php');?>	
			
			<?php include_once('inc/teams_timeline.php');?>	
							
		</div>
		
		<div class="hidden-xs col-xs-24">
		
			<?php include_once('inc/team_chart_streak.php');?>
		
		</div>

		
	</div>
	<!--===================================================-->
	<!--End page content-->


</div>
<!--===================================================-->
<!--END CONTENT CONTAINER-->
<?php include_once('main-nav.php'); ?>		
</div>

			
</div>



<?php get_footer(); ?>