<?php
/*
 * Template Name: Homepage
 * Description: Homepage for the PFL Website
 */


/*
 $url1=$_SERVER['REQUEST_URI'];
 header("Refresh: 5; URL=$url1");
*/ 

 
 
$playersassoc = get_players_assoc();
$i = 0;
foreach ($playersassoc as $key => $value){
	$playersid[] = $key;
}

$randomize = array_rand($playersid);
$randomplayer = $playersid[$randomize];
$featuredplayer = $playersassoc[$randomplayer];
$first = $featuredplayer[0];
$last = $featuredplayer[1];
$position = $featuredplayer[2];

insert_allleaders($randomplayer);

// sets transient to player data array to random player on homepage
function set_randomplayerdata_trans() {
  global $randomplayer;
  $transient = get_transient( $randomplayer.'_trans' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set[$randomplayer] = get_player_data($randomplayer);
    set_transient( $randomplayer.'_trans', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

$randomplayerdata = set_randomplayerdata_trans();


// used to set transient to player data array anywhere
function set_allplayerdata_trans($pid) {
  global $randomplayer;
  $transient = get_transient( $pid.'_trans' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set[$pid] = get_player_data($pid);
    set_transient( $pid.'_trans', $set, DAY_IN_SECONDS );
    return $set;
  }
  
}

// set transient for team data
function set_team_data_trans($teamid) {
  $transient = get_transient( $teamid.'_trans' );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
   	$set = get_team_results($teamid);
    set_transient( $teamid.'_trans', $set, MONTH_IN_SECONDS );
    return $set;
  }
  
}



/*
$teamget = get_team_results('PEP');
printr($teamget, 1);
*/

set_team_trans();
set_schedule_trans();

/*
$teams = get_teams();
printr($teams, 0);
*/

/*
$teamer = get_team_results_expanded('DST');
$teamrec = team_record('SNR');
printr($teamer, 1);
*/

/*
foreach ($playersid as $id){
	set_allplayerdata_trans($id);
}
*/


get_header(); 
//start the loop

//build player id array. 

foreach ($playersid as $play){
			
}

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
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Welcome</h3>
								</div>
								<div class="panel-body">
									<?php while (have_posts()) : the_post(); ?>
										<p><?php the_content();?></p>
									<?php endwhile; wp_reset_query(); ?>
								</div>
							</div>
					</div>
					
					<div class="col-xs-12 col-sm-10 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Something</h3>
								</div>
								
								<div class="panel-body">
									<?php printr($featuredplayer, 0); ?>
								</div>
							</div>
					</div>
					
					<!-- PLAYER SPOTLIGHT -->
					<div class="col-xs-24 col-sm-4 left-column">
						<div class="panel widget">
							<div class="widget-header bg-purple">
					<img src="<?php echo get_stylesheet_directory_uri();?>/img/players/<?php echo $randomplayer; ?>.jpg" class="widget-bg img-responsive">

							</div>
							<div class="widget-body text-center">
								<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $position; ?>.jpg">
								<h3 class="mar-no"><a href="/player/?id=<?php echo $randomplayer;?>"><?php echo $first.' '.$last; ?></a></h3>
								<p></p>
<!-- 								<h4 class="mar-no text-sm">	text could go here </h4> -->
							</div>
						</div>
					</div>
								

					
				</div>
				<!-- THE ROW -->
				<div class="row">
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Games</h3>
								</div>
								<div class="panel-body">
									<p>Gamestreak</p>
									
									<?php 
										
										$gamestreak = get_player_game_streak($randomplayer); 
										printr($gamestreak, 0);
										echo '<p>Player Matchup</p>';
										$record = get_player_record($randomplayer);
										printr($record, 0);
										echo '<p>Player Results</p>';
										$playerrecord = get_player_results($randomplayer);
										printr($playerrecord, 0);
									?>
								</div>
							</div>
					</div>
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Set Team Results</h3>
								</div>
								<div class="panel-body">
									<p>Commented out unless needed</p>
									<?php 
										
										// used to set transient to team data tables.  It is memory heavy so sometimes you need to disable other page functions or set printr val to 1 to die() after function.  Must manually toggle team IDS and build each.
										

/*
										$getteam = set_team_data_trans('PEP');
									    printr($getteam, 1);
*/

										
									?>
								</div>
							</div>
					</div>
					
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Player Table Transients</h3>
								</div>
								
								<div class="panel-body">
									<p>This area generates player table transients that expire in 12 months.</p>
									<?php
										$getplayer = get_allplayerdata_trans($randomplayerdata); 
										if (isset($getplayer)){
											echo 'Transient Exsists';
										} else {
											echo 'Transient Created';
										}
										printr($randomplayerdata,0);
										?>
								</div>
							</div>
					</div>
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">G</h3>
								</div>
								
								<div class="panel-body">
							
								</div>
							</div>
					</div>
					
				</div>
				
				
		</div>
		
	</div>
	
</div>



		
<?php get_footer(); ?>