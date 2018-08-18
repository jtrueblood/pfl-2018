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
//$randomplayer = '2004PalmQB';

$featuredplayer = $playersassoc[$randomplayer];
$first = $featuredplayer[0];
$last = $featuredplayer[1];
$position = $featuredplayer[2];
$rookie = $featuredplayer[3];
$mflid = $featuredplayer[4];

insert_wp_career_leaders($randomplayer);
$testprint = insert_wp_season_leaders($randomplayer);
// printr($testprint, 0);

// sets transient to player data array to random player on homepage
function set_randomplayerdata_trans() {
  global $randomplayer;
  $transient = get_transient( $randomplayer.'_trans' );
  if( ! empty( $transient ) ) {
    return $transient;
  } /*
else {
   	$set[$randomplayer] = get_player_data($randomplayer);
    set_transient( $randomplayer.'_trans', $set, DAY_IN_SECONDS );
    return $set;
  }
*/
  
}

$randomplayerdata = set_randomplayerdata_trans();

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



//$teamer = set_team_data_trans('SNR');
//$teamrec = team_record('SNR');
//printr($teamer, 1);



/*
foreach ($playersid as $id){
	set_allplayerdata_trans($id);
}
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
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Title</h3>
								</div>
								
								<div class="panel-body">
									<p>This area is open</p>
									<a href="http://www58.myfantasyleague.com/2018/home/38954#0" target="_blank">MFL Website 2018</a><br>
									<a href="http://www58.myfantasyleague.com/2017/home/38954#0" target="_blank">MFL Website 2017</a><br>
									<a href="http://www58.myfantasyleague.com/2016/home/38954#0" target="_blank">MFL Website 2016</a><br>
									<a href="http://www58.myfantasyleague.com/2015/home/47099#0" target="_blank">MFL Website 2015</a><br>
								</div>
							</div>
					</div>
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Basic Info</h3>
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
							
					<!-- PLAYER SPOTLIGHT -->
					<div class="col-xs-24 col-sm-6 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php 
/*
									$pmflid = $wpdb->get_results("select mflid from wp_players where p_id = '$randomplayer'");
									$theid = $pmflid[0]->mflid;
*/
// 									printr($theid, 0);
									if(!empty($mflid)){
										echo 'From MFL API ';
										$mfldeet = get_mfl_player_details($mflid); 
										printr($mfldeet, 0);
										
										$inches = $mfldeet['height'];
										$getweight = $mfldeet['weight'];
										$getcollege = $mfldeet['college'];
										$getnumber = $mfldeet['jersey'];
										$getbirthdate = $mfldeet['birthdate'];
										
										$feet = floor($inches/12);
										$inches = ($inches%12);
										$getheight = $feet.'-'.$inches;
										
										//$php_timestamp_date = date("Y/m/d", $getbirthdate);
										//echo "".$php_timestamp_date."";
										
// 										foreach ($mflids as $key => $value){

											$wpdb->query(
											"UPDATE wp_players
											SET weight = $getweight, height = '$getheight', college = '$getcollege', number = $getnumber 
											WHERE p_id = '$randomplayer'"
											);

// 										}

										
									} else {
										echo 'MFL ID Not Found';
									}
									
									
								?>
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
									
									
									<?php 
										echo '<p>Gamestreak -- get_player_game_streak($playerid)</p>';
										echo '<p>displays games in a row played by a player.  Excludes bye weeks.</p>';
											$gamestreak = get_player_game_streak($randomplayer); 
											printr($gamestreak, 0);
										
										echo '<p>Player Matchup -- get_player_record($playerid)</p>';
										echo '<p>displays just the teams that the player played for by weekid => ETS</p>';
											$record = get_player_record($randomplayer);
											printr($record, 0);
										
										echo '<p>Player Results -- get_player_data($playerid)</p>';
										echo '<p>gets all player data from table including record, vs, location, etc</p>';
											$playerrecord = get_player_data($randomplayer);
											printr($playerrecord, 0);
									?>
								</div>
							</div>
					</div>
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Return By Season - Rookie Year Shown</h3>
								</div>
								<div class="panel-body">
									<p>get_player_season_stats($player, $year);</p>
									<?php 
										
										// this used to set transient to team data tables.  It is memory heavy so sometimes you need to disable other page functions or set printr val to 1 to die() after function.  Must manually toggle team IDS and build each.
										
										$byseason = get_player_season_stats($randomplayer, $rookie);
										if (isset($byseason)){
									    	printr($byseason, 0);
									    }


										
									?>
								</div>
							</div>
					</div>
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Boxscore By Week.  Pass Week ID</h3>
								</div>
								<div class="panel-body">
									<?php while (have_posts()) : the_post(); ?>
										<p><?php the_content();?></p>
									<?php endwhile; wp_reset_query(); 
										
										

										$getboxscore = put_boxscore_results(199101);
										printr($getboxscore, 0);

									?>
								</div>
							</div>
					</div>
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Career Stats</h3>
								</div>
								
								<div class="panel-body">
									<p>get_player_career_stats($pid);</p>
							<?php $career = get_player_career_stats($randomplayer); 
								printr($career, 0);
							?>
							
								</div>
							</div>
					</div>
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">MFL Data</h3>
								</div>
								
								<div class="panel-body">
									<p>g</p>
							<?php 		get_cache('mfl/linkidcache', 0);	
										$linkidcache = $_SESSION['mfl/linkidcache']; 
										
										foreach ($linkidcache as $key => $value){
											$mflids[$value[2]] = $key;
										}
										
										printr($mflids, 0);
										
										
/*
										foreach ($mflids as $key => $value){
											$wpdb->query("UPDATE wp_players SET mflid = $value WHERE p_id = '$key'");
										}
										
										
*/
										

										
							?>
							
								</div>
							</div>
					</div>

					
				</div>
				
				
		</div>
		<?php include_once('main-nav.php'); ?>
	</div>
	
</div>



		
<?php get_footer(); ?>