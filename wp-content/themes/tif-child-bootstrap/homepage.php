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



// pulls data from MFL API and inserts into wp_players as well as creating player table for weekly score data
function createnewplayer($array){
												
	global $wpdb;
	$arr = $array;
	
	$pid = $arr['p_id'];
	
	// insert info into wp_players
	$insertarr = $wpdb->insert(
		 'wp_players',
	     array(
	        'p_id' 			=> $arr['p_id'],
			'playerFirst' 	=> $arr['playerFirst'],
			'playerLast' 	=> $arr['playerLast'],
			'position' 		=> $arr['position'],
			'rookie' 		=> $arr['rookie'],
			'mflid' 		=> $arr['mflid'],	
			'height' 		=> $arr['height'],
			'weight' 		=> $arr['weight'],
			'college' 		=> $arr['college'],
			'birthdate' 	=> '',
			'number' 		=> $arr['number']
	     ),
		 array( 
			'%s','%s','%s','%s','%d','%s','%s','%d','%s','','%d' 
		 )
	);	

	// create new table
	$wpdb->query("CREATE TABLE $pid LIKE 1991SmitRB" );
			
	return $insertarr;
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
					
					<div class="col-xs-12 col-sm-7 eq-box-sm">
						
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
								<h3 class="panel-title">Create New Player</h3>
							</div>
								<div class="panel-body">
									<div class="col-xs-24">
										<p><small>Check for player above and if they do not exist enter MFL ID below.</small></p>	
										<form action="" method="post">

											MFL ID: <input type="text" name="mflid" /><br>
											<br>
											<input type="submit" />
											
											
										</form>
										
									</div>
									
									<div class="col-xs-24">
										
										
										<?php 
											if ( isset( $_POST['mflid'] ) ){
												
												$form_mfl_id = 	$_POST['mflid'];
												//echo $form_mfl_id;
												$mfl_data = get_mfl_player_details($form_mfl_id);
												//printr($mfl_data, 0);
												
												$name = $mfl_data['name'];
												$xname = explode(',', $name);
												
												$first = $xname[1];
												$last = $xname[0];
												$justfour = substr($last, 0, 4);
												
												$themflid = $mfl_data['id']; 
												$draftyear = $mfl_data['draft_year']; 
												$position = $mfl_data['position']; 
												$weight = $mfl_data['weight'];
												$jersey = $mfl_data['jersey']; 
												$college = $mfl_data['college'];
												$pflid = $draftyear.$justfour.$position;
												
												$h = $mfl_data['height'];
												$getfeet = $h / 12;
												$explode = explode('.', $getfeet);
												
												$feet = $explode[0];
												$i = '.'.strval($explode[1]);
												$inches = round($i * 12);
												
												
												$covheight = $feet.'-'.$inches;
												
												$insertplayer = array(
													'p_id' => $pflid,
													'playerFirst' => ltrim($first),
													'playerLast' => $last,
													'position' => $position,
													'rookie' => $draftyear,
													'mflid' => $themflid,	
													'height' => $covheight,
													'weight' => $weight,
													'college' => $college,
													'birthdate' => '',
													'number' => $jersey
												);
												
												//var_dump($covheight);
												//printr($explode, 0);
	
												if(isset($insertplayer)){
													createnewplayer($insertplayer);
													printr($insertplayer, 0);
												}												

										     } ?>
										     
									</div>
									
									<div class="col-xs-24">
										
										   
									</div>
								</div>
							
						</div>
						
						
						
						<div class="panel panel-bordered panel-light">
							<div class="panel-heading">
								<h3 class="panel-title">Quick Links</h3>
							</div>
								<div class="panel-body">
									<div class="col-xs-24 col-sm-12">	
										<a href="http://www58.myfantasyleague.com/2018/home/38954#0" target="_blank">MFL Website 2018</a><br>
										<a href="https://www.pro-football-reference.com/" target="_blank">Pro Football Reference</a><br>
										<a href="https://www.fantasypros.com/" target="_blank">Fantasy Pros</a><br>
										<hr>
										<a href="http://www58.myfantasyleague.com/2017/home/38954#0" target="_blank">MFL Website 2017</a><br>
										<a href="http://www58.myfantasyleague.com/2016/home/38954#0" target="_blank">MFL Website 2016</a><br>
										<a href="http://www58.myfantasyleague.com/2015/home/47099#0" target="_blank">MFL Website 2015</a><br>
										<hr>
										<a href="/builds/build-mfl-weekly/?Y=2017&W=1&SQL=false">Run MFL Weekly Data Insert</a>
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
							
					<!-- PLAYER SPOTLIGHT -->
					<div class="col-xs-24 col-sm-7 left-column">
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
									<h3 class="panel-title">Player Playoff Boxscores</h3>
								</div>
								<div class="panel-body">
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
									<?php 
										$playerbytrade = get_trade_by_player($randomplayer);
										printr($playerbytrade, 0);

									?>
								</div>
							</div>
							
							
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
									<p></p>
							<?php 		/*
get_cache('mfl/linkidcache', 0);	
										$linkidcache = $_SESSION['mfl/linkidcache']; 
										
										foreach ($linkidcache as $key => $value){
											$mflids[$value[2]] = $key;
										}
*/
										
										$mflids = playerid_mfl_to_pfl();
										
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