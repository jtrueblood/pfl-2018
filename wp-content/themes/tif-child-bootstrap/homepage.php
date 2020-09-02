<?php
/*
 * Template Name: Homepage
 * Description: Homepage for the PFL Website
 */


/*
$url1=$_SERVER['REQUEST_URI'];
header("Refresh: 5; URL=$url1");
*/


$getplayer = $_GET['id'];
 
$playersassoc = get_players_assoc();
$i = 0;
foreach ($playersassoc as $key => $value){
	$playersid[] = $key;
}

$randomize = array_rand($playersid);


//$randomplayer = '2009AverWR';

if(isset($getplayer)){
	$randomplayer = $_GET['id'];
} else {
	$randomplayer = $playersid[$randomize];
}

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
								<h3 class="panel-title">Select A Player</h3>
							</div>
							<div class="panel-body">
							<div class="col-xs-24 col-sm-18">	
								<select data-placeholder="Select an Existing Player" class="chzn-select" style="width:100%;" tabindex="1" id="playerDrop">
								<option value=""></option>
								
								<?php 	
								foreach ($playersassoc as $key => $selectplayer){
									$firsto = $selectplayer[0];
									$lasto = $selectplayer[1];
									$printselect .= '<option value="/?id='.$key.'">'.$firsto.' '.$lasto.'</option>';
								}
								echo $printselect;
								?>
							</select>
							</div>
							<div class="col-xs-24 col-sm-4">
								<button class="btn btn-warning" id="playerSelect">Select</button>
							</div>
						</div>
						
						
						
						<div class="panel panel-bordered panel-light">
							<div class="panel-heading">
								<h3 class="panel-title">Scripts</h3>
							</div>
								<div class="panel-body">
									<div class="col-xs-24">
										<a href="/create-new-player">Create New Player</a><br>	
										<a href="/builds/build-mfl-weekly/?Y=2019&W=1&SQL=false&CURL=false">Run MFL Weekly Data Insert</a><br>
										<a href="/builds/build-drafts/">Import Draft</a><br>	
										<a href="/python-scripts">Sportsrefernce API Python Scripts</a><br>
									</div>	
								</div>
							
						</div>
						
						
						
						<div class="panel panel-bordered panel-light">
							<div class="panel-heading">
								<h3 class="panel-title">Quick Links</h3>
							</div>
								<div class="panel-body">
									<div class="col-xs-24">	
										<a href="https://www.pro-football-reference.com/" target="_blank">Pro Football Reference</a><br>
										<a href="https://www.fantasypros.com/" target="_blank">Fantasy Pros</a><br>
										<a href="https://docs.google.com/document/d/1D8VZPOBn04zVXYQB1gr-xb1NDe2nYLn9Rqgl4oZVhXI/edit?usp=sharing" target="_blank">PFL Rules - Rev 2019</a><br>
										<hr>
										<a href="https://www58.myfantasyleague.com/2020/home/38954#0" target="_blank">MFL Website 2020</a><br>
										<a href="http://www58.myfantasyleague.com/2019/home/38954#0" target="_blank">MFL Website 2019</a><br>
										<a href="http://www58.myfantasyleague.com/2018/home/38954#0" target="_blank">MFL Website 2018</a><br>
										<a href="http://www58.myfantasyleague.com/2017/home/38954#0" target="_blank">MFL Website 2017</a><br>
										<a href="http://www58.myfantasyleague.com/2016/home/38954#0" target="_blank">MFL Website 2016</a><br>
										<a href="http://www58.myfantasyleague.com/2015/home/47099#0" target="_blank">MFL Website 2015</a><br>
										<hr>
										
									</div>
								</div>
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
						<div class="panel widget" >
							
							<div class="widget-header" style="min-height: 200px;" >
								
								<?php 
									$ifimage = check_if_image($randomplayer);

									if($ifimage == 1){
										$playerimgobj = get_attachment_url_by_slug($randomplayer);
										$imgid =  attachment_url_to_postid( $playerimgobj );
										$image_attributes = wp_get_attachment_image_src($imgid);
										
										echo '<img src="'.$image_attributes[0].'" class="widget-bg img-responsive">';
									} else {
										echo '<img src="'.get_stylesheet_directory_uri().'/img/players/'.$randomplayer.'.jpg" class="widget-bg img-responsive">';
									}
								?>
								
							</div>
							<div class="widget-body text-center">
								<?php 
									if($ifimage == 1){	
										echo $ifimage.' source: /wp-content/uploads/';
									} else {
										echo $ifimage.' source: /img/players/';
									}
									?>
								<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $position; ?>.jpg">
								<h3 class="mar-no"><a href="/player/?id=<?php echo $randomplayer;?>"><?php echo $first.' '.$last; ?></a></h3>
								<p></p>
<!-- 								<h4 class="mar-no text-sm">	text could go here </h4> -->
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
						<div class="panel panel-bordered panel-light">
							<div class="panel-body">
								<?php while (have_posts()) : the_post(); ?>
								<p><?php the_content();?></p>
								<?php endwhile; wp_reset_query(); ?>
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
										
										// FOR POSITION PLAYERS (QB, RB, WR)
										//$pfr_id = $featuredplayer[10];
										//$firstInit =  $pfr_id[0];
										//$firstInitCap = strtoupper($firstInit);
										
										// FOR KICKERS (PK)
										//$pfr_id = 'reveifua01';

										$firstInitCap = strtoupper($last[0]);
										//$firstshort = substr($first, 0, 3);
										//$lastshort = substr($last, 0, 5);
										//$pfr_id = strtolower($lastshort.$firstshort).'01';
										$pfr_id = $featuredplayer[10];		
										
										if($pfr_id):	
											include_once('simplehtmldom/simple_html_dom.php');
											
											    $html = file_get_html('https://www.pro-football-reference.com/players/'.$firstInitCap.'/'.$pfr_id.'.htm');
											
												if($html):
												    $pfr_name = $html->find('h1', 0);
												    $pfr_height = $html->find('#meta div p[3] span[1]', 0);
													$pfr_weight = $html->find('#meta div p[3] span[2]', 0);
													
													$pfr_number = $html->find('.uni_holder', 0);
													$e = explode(" ", $pfr_number);
													$p = explode("=", $e[4]);
													$q = end($p);
													$number = rtrim($q, '"');
													
													$pfr_college = $html->find('#meta div p[5]', 0);
													$c = strip_tags($pfr_college);
													$d = str_replace("College:", "", $c );
													$e = explode(",", $d);
													$college = str_replace("(College Stats)", "", $e);
													
													$info = array(
														'name' => strip_tags($pfr_name), 
														'height' => strip_tags($pfr_height),
														'weight' => strip_tags($pfr_weight),
														'college' => trim($college[0]),
														'number' => $number,
													);
													
													printr($d, 0);
													
													$wpdb->query(
													"UPDATE wp_players
													SET weight = '$info[weight]', height = '$info[height]', college = '$info[college]', number = '$info[number]', pfruri = '$pfr_id'
													WHERE p_id = '$randomplayer'"
													);
													
													else:
													
													echo "PRO FOOTBALL REF PAGE NOT FOUND<br>";
													
												endif;
											
											else:
											
											echo "PRO FOOTBALL REF ID NOT FOUND";
											
											endif;
										?>
	
										<div class="panel-heading">
											<h3 class="panel-title">Player Info Scraped from Pro Football Reference</h3>
											
										</div>
										<div class="panel-body">
										<p>Pro Football Ref ID: <?php echo $firstInit.'/'.$pfr_id; ?></p>
										<?php printr($info,0); ?>
										</div>
					
										<?php
										
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
										echo '<p>get_player_game_streak($playerid);</p>';
										echo '<p>displays games in a row played by a player.  Excludes bye weeks.</p>';
											$gamestreak = get_player_game_streak($randomplayer); 
											printr($gamestreak, 0);
										
										echo '<p>get_player_record($playerid);</p>';
										echo '<p>displays just the teams that the player played for by weekid => ETS</p>';
											$record = get_player_record($randomplayer);
											printr($record, 0);
										
										echo '<p>get_player_data($playerid);</p>';
										echo '<p>gets all player data from table including record, vs, location, etc.  basically, all of players career boxscores</p>';
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
							
							
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Player Drafted</h3>
								</div>
								<div class="panel-body">
									<p>get_drafts_player($pid);</p>
									<p>All drafted instances by player</p>
									<?php 							
										$draftedplayer = get_drafts_player($randomplayer);
										if (isset($draftedplayer)){
									    	printr($draftedplayer, 0);
									    }
									?>
									<p>get_drafts_player_first_instance($pid);</p>
									<p>First drafted instance by player</p>
									<?php 							
										$draftedplayerfirst = get_drafts_player_first_instance($randomplayer);
										if (isset($draftedplayerfirst)){
									    	printr($draftedplayerfirst, 0);
									    }
									?>
									
								</div>
							</div>
							
							
					</div>
					
					
					
					
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
							
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Player Playoff Boxscores</h3>
								</div>
								<div class="panel-body">
									<p>playerplayoffs($pid);</p>
									<?php 
										$playoffsplayer = playerplayoffs($randomplayer);
										printr($playoffsplayer, 0);

									?>
								</div>
							</div>
							
							
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Trades Involving Player</h3>
								</div>
								<div class="panel-body">
									<p>get_trade_by_player($pid);</p>
									<?php 
										$playerbytrade = get_trade_by_player($randomplayer);
										printr($playerbytrade, 0);

									?>
								</div>
							</div>
							
							
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Score By Week.</h3>
									
								</div>
								<div class="panel-body">
									<p>get_one_player_week(pid, weekid);</p>
									<?php
										$firstweek = key($record);
										echo 'First Week Played as example - '.$firstweek;
										$getboxscore = get_one_player_week($randomplayer, $firstweek);
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
									<h3 class="panel-title">Awards</h3>
								</div>
								
								<div class="panel-body">
									<p>get_player_award($pid);</p>
							<?php $awards = get_player_award($randomplayer); 
								printr($awards, 0);
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
									<p>playerid_mfl_to_pfl();</p>
									<p>get array of linked MFL and PFL ids
									<?php
										$mflids = playerid_mfl_to_pfl();
										printr($mflids, 0);
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