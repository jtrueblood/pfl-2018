<?php
/*
 * Template Name: Scrape PFR New
 * Description: Script to Scrape Player Stats From Pro Football Reference
 */


/*
$url1=$_SERVER['REQUEST_URI'];
header("Refresh: 5; URL=$url1");
*/

$getplayer = $_GET['id'];
$getyearclean = $_GET['year'];
$yearclean = array($getyearclean);
$weekclean = $_GET['week'];

//  Set Run Value to '1' if you want to override the .json file that exists in 'pfr-gamelogs'.  This is used for updating players that have played in the past.
$getrun = $_GET['run'];
 
$playersassoc = get_players_assoc();
$i = 0;
foreach ($playersassoc as $key => $value){
	$playersid[] = $key;
}

$randomize = array_rand($playersid);


$jusplayerids = just_player_ids();
$currentid = array_search($getplayer, $jusplayerids);
$nextplayer = $jusplayerids[$currentid + 1];

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

$yearsplayed = get_player_years_played($randomplayer);
//printr($yearsplayed, 1);

$stylesheet_uri = get_stylesheet_directory_uri();

get_header(); 
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
								<select data-placeholder="Select an Existing Player" class="chzn-select" style="width:100%;" tabindex="1" id="playerDropScrapeNew">
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
								<button class="btn btn-warning" id="playerSelectScrapeNew">Select</button>
							</div>
						</div>
						<?php echo $nextplayer ; ?>
						
						
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
										echo '<img src="'.$stylesheet_uri.'/img/players/'.$randomplayer.'.jpg" class="widget-bg img-responsive">';
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
							
					<!-- GET BASIC PLAYER INFO -->
					<div class="col-xs-24 col-sm-6 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php 


								$firstInitCap = strtoupper($last[0]);
								$pfr_id = $featuredplayer[10];	
								
								
								include_once('simplehtmldom/simple_html_dom.php');
								$html = file_get_html('https://www.pro-football-reference.com/players/'.$firstInitCap.'/'.$pfr_id.'.htm');
								
								
								if($pfr_id):
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

//											$wpdb->query(
//											"UPDATE wp_players
//											SET weight = '$info[weight]', height = '$info[height]', college = '$info[college]', number = '$info[number]', pfruri = '$pfr_id'
//											WHERE p_id = '$randomplayer'"
//											);

											else:

											$report_message = $randomplayer." -- ref page not found || ";
											echo '<script>console.log("'.$randomplayer.' - ref page not found");</script>';
											echo $report_message;

										endif;

									else:

										$report_message = $randomplayer." -- ref player PFR ID not found || ";
										echo '<script>console.log("'.$randomplayer.' - ref player PFR ID not found");</script>';
										echo $report_message;

									endif;
									
								?>

								<div class="panel-heading">
									<h3 class="panel-title">Player Info Scraped from Pro Football Reference</h3>
									
								</div>
								<div class="panel-body">
								<p>Pro Football Ref ID: <?php echo $firstInit.'/'.$pfr_id; ?></p>
								<?php printr($info,0); ?>
								</div>
					
							</div>
	
						</div>
					
					</div>
					
					<!-- GET BASIC PLAYER INFO -->
					<div class="col-xs-12 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php 
									


 // Toggle this value here to set to either all years player played or a simple array of one or a few years.
								//$yearclean = array_values($yearsplayed);
                                // $yearclean = array(2022);

                                echo $getyearclean.'-'.$weekclean.'<br>';
								//printr($yearclean, 0);
								// single last year
								$r = $yearclean[0];
								
								$passyards = array();
								
								foreach($yearclean as $y){

									$htmlgame = '';
									
									$htmlgame = file_get_html('https://www.pro-football-reference.com/players/'.$firstInitCap.'/'.$pfr_id.'/gamelog/'.$y.'/');	
									
										
										// Scrape the Dom	
										if($pfr_id):					
											if($htmlgame):
												$tablehead = $htmlgame->getElementById("stats thead");
											    $gamescore = $htmlgame->getElementById("stats tbody");
											    
											    //$passyards = $htmlgame->find('td'); 
											    
											endif;
										endif;
										
										
										foreach($htmlgame->find('tbody tr [data-stat=week_num]') as $e){
											$week_num[$y] .= $e->plaintext.',';
										}
										
										foreach($htmlgame->find('tbody tr [data-stat=game_date]') as $e){
											$game_date[$y] .= $e->plaintext.',';
										}
										
										foreach($htmlgame->find('tbody tr [data-stat=team]') as $e){
											$team[$y] .= $e->plaintext.',';
										}
										
										foreach($htmlgame->find('tbody tr [data-stat=game_location]') as $e){
											$game_location[$y] .= $e->plaintext.',';
										}
										
										foreach($htmlgame->find('tbody tr [data-stat=opp]') as $e){
											$opp[$y] .= $e->plaintext.',';
										}

										foreach($htmlgame->find('tbody tr [data-stat=pass_yds]') as $e){
											$pass_yds[$y] .= $e->plaintext.',';
										}	
										
										foreach($htmlgame->find('tbody tr [data-stat=pass_td]') as $e){
											$pass_td[$y] .= $e->plaintext.',';
										}
										
										foreach($htmlgame->find('tbody tr [data-stat=pass_int]') as $e){
											$pass_int[$y] .= $e->plaintext.',';
										}
										
										foreach($htmlgame->find('tbody tr [data-stat=rush_yds]') as $e){
											$rush_yds[$y] .= $e->plaintext.',';
										}
										
										foreach($htmlgame->find('tbody tr [data-stat=rush_td]') as $e){
											$rush_td[$y] .= $e->plaintext.',';
										}
										
										foreach($htmlgame->find('tbody tr [data-stat=rec_yds]') as $e){
											$rec_yds[$y] .= $e->plaintext.',';
										}
										
										foreach($htmlgame->find('tbody tr [data-stat=rec_td]') as $e){
											$rec_td[$y] .= $e->plaintext.',';
										}
										
										if($position == 'PK'):
										
											foreach($htmlgame->find('tbody tr [data-stat=xpm]') as $e){
												$xpm[$y] .= $e->plaintext.',';
											}
											
											foreach($htmlgame->find('tbody tr [data-stat=xpa]') as $e){
												$xpa[$y] .= $e->plaintext.',';
											}
											
											foreach($htmlgame->find('tbody tr [data-stat=fgm]') as $e){
												$fgm [$y] .= $e->plaintext.',';
											}
											
											foreach($htmlgame->find('tbody tr [data-stat=fga]') as $e){
												$fga[$y] .= $e->plaintext.',';
											}
										
										endif;
																		
									}

								//$arr_week_num = explode(',', $week_num[$y]);

								//organize the scraped dataset as arrays
									$playerstats = array(
										'week_num' => explode(',', $week_num[$r]),
										'game_date' => explode(',', $game_date[$r]),
										'team' => explode(',', $team[$r]),
										'game_location' => explode(',', $game_location[$r]),
										'opp' => explode(',', $opp[$r]),
 										'pass_yds' => explode(',', $pass_yds[$r]),
										'pass_td' => explode(',', $pass_td[$r]),
										'pass_int' => explode(',', $pass_int[$r]),
										'rush_yds' => explode(',', $rush_yds[$r]),
										'rush_td' => explode(',', $rush_td[$r]),
										'rec_yds' => explode(',', $rec_yds[$r]),
										'rec_td' => explode(',', $rec_td[$r]),
										'xpm' => explode(',', $xpm[$r]),
										'xpa' => explode(',', $xpa[$r]),
										'fgm' => explode(',', $fgm[$r]),
										'fga' => explode(',', $fga[$r])
									);

								//organize the arrays by week and clean data for insert into database
                                $i = 0;
                                foreach($playerstats['week_num'] as $clean):
                                    if($playerstats['game_location'][$i] == '@'):
                                        $vs = '@';
                                    else:
                                        $vs = 'vs';
                                    endif;
                                    $wzero = sprintf('%02d', $clean);
                                    $cleanarray[$r.$wzero] = array(
                                        'game_date' => $playerstats['game_date'][$i],
                                        'team' => $playerstats['team'][$i],
                                        'game_location' => $vs,
										'opp' => $playerstats['opp'][$i],
 										'pass_yds' => empty($playerstats['pass_yds'][$i]) ? 0 : $playerstats['pass_yds'][$i],
										'pass_td' => empty($playerstats['pass_td'][$i]) ? 0 : $playerstats['pass_td'][$i],
										'pass_int' => empty($playerstats['pass_int'][$i]) ? 0 : $playerstats['pass_int'][$i],
										'rush_yds' => empty($playerstats['rush_yds'][$i]) ? 0 : $playerstats['rush_yds'][$i],
										'rush_td' => empty($playerstats['rush_td'][$i]) ? 0 : $playerstats['rush_td'][$i],
										'rec_yds' => empty($playerstats['rec_yds'][$i]) ? 0 : $playerstats['rec_yds'][$i],
										'rec_td' => empty($playerstats['rec_td'][$i]) ? 0 : $playerstats['rec_td'][$i],
										'xpm' => empty($playerstats['xpm'][$i]) ? 0 : $playerstats['xpm'][$i],
										'xpa' => empty($playerstats['xpa'][$i]) ? 0 : $playerstats['xpa'][$i],
										'fgm' => empty($playerstats['fgm'][$i]) ? 0 : $playerstats['fgm'][$i],
										'fga' => empty($playerstats['fga'][$i]) ? 0 : $playerstats['fga'][$i]
                                    );
                                $i++;
                                endforeach;
                                array_pop($cleanarray);

                                function insert_player_gamestats_scrape($playerid, $array, $pos, $year){
                                    global $wpdb;
                                    foreach ($array as $key => $value):

                                            $gamedate = $value['game_date'];
                                            $nflteam = $value['team'];
                                            $gamelocation = $value['game_location'];
                                            $opp  = $value['opp'];
                                            $pass_yds = $value['pass_yds'];
                                            $pass_td = $value['pass_td'];
                                            $pass_int = $value['pass_int'];
                                            $rush_yds = $value['rush_yds'];
                                            $rush_td = $value['rush_td'];
                                            $rec_yds = $value['rec_yds'];
                                            $rec_td = $value['rec_td'];
                                            $xpm = $value['xpm'];
                                            $xpa = $value['xpa'];
                                            $fgm = $value['fgm'];
                                            $fga = $value['fga'];

                                            if($pos == "PK"):
                                                $nflscore = pk_score_converter($year, $xpm, $fgm);
                                            else:
                                                $nflscore = pos_score_converter($year, $pass_yds, $pass_td, $rush_yds, $rush_td, $pass_int, $rec_yds, $rec_td);
                                            endif;

                                            $pflscore = get_player_score_by_week($playerid, $key);
                                            $pflpts = $pflscore['points'];
                                            $ptdiff = $pflpts - $nflscore;

                                            $query = $wpdb->query("
                                                    UPDATE $playerid 
                                                    SET game_date = '$gamedate', nflteam = '$nflteam', game_location = '$gamelocation', nflopp = '$opp', pass_yds = '$pass_yds', pass_td = '$pass_td', pass_int = '$pass_int', rush_yds = '$rush_yds', rush_td = '$rush_td', rec_yds = '$rec_yds', rec_td = '$rec_td', xpm = '$xpm', xpa = '$xpa', fgm = '$fgm', fga = '$fga', nflscore = '$nflscore', scorediff = '$ptdiff'
                                                    WHERE week_id = '$key'
                                                    " );
                                            return $query.'<br>'.$playerid.'<br>'.$key.'<br>'.$pflpts.'<br>'.$nflscore;

                                    endforeach;

                                }

                                $results = insert_player_gamestats_scrape($randomplayer, $cleanarray, $position, $r);
                                echo $results;

								?>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php
                                printr($cleanarray, 0);
                               //printr($playerstats, 0);

								?>
							</div>
						</div>
					</div>
					
				</div>	
				
		</div>
		<?php include_once('main-nav.php'); ?>
	</div>
	
</div>

<?php 
	//$log_file = $destination_folder.'/file.log';
	//error_log($report_message, 3, $log_file);
?>


<script>

    // DISABLE TO STOP AUTO RELOAD

//setTimeout(function(){
//	var scrapeclick = '/scrape-pro-football-ref/?id=--><?php //echo $nextplayer; ?>//';
//    window.location.href = scrapeclick;
// }, 7000);

</script>


<script>
	//  var scrapeclick = '/scrape-pro-football-ref/?id=<?php echo $nextplayer; ?>';
       //    console.log(scrapeclick);
</script>


		
<?php get_footer(); ?>