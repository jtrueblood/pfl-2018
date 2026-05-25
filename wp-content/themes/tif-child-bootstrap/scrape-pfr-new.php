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


//$getyear = $_GET['year'];
// Toggle this value here to set to either all years player played or a simple array of one or a few years.
//$yearclean = array_values($yearsplayed);

//$getyearclean = $_GET['year'];
//$yearclean = array($getyearclean);
//$weekclean = $_GET['week'];


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

$gettheyear = 1993;

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
					
					<!-- GET PLAYER GAME DATA -->
					<div class="col-xs-12 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php

                            // SET YEAR HERE -- can be a single value or an array
                            $yearclean = array(1993);

                                echo $getyearclean.'-'.$weekclean.'<br>';
								//printr($yearclean, 0);
								// single last year
								$r = $yearclean[0];

								echo '<p>Each year set $yearclean as current year value.  Then step through each player that played to update player table with NFL stats by game.</p>';
								echo '<h3>YEAR SET as: '.$r.'</h3>';
								
								$passyards = array();
								
								foreach($yearclean as $y){

									$htmlgame = '';
									$htmlgame = file_get_html('https://www.pro-football-reference.com/players/'.$firstInitCap.'/'.$pfr_id.'/gamelog/'.$y.'/');

										// Scrape the Page Dom
										if($pfr_id):					
											if($htmlgame):
												$tablehead = $htmlgame->getElementById("stats thead");
											    $gamescore = $htmlgame->getElementById("stats tbody");
											endif;
										endif;

                                            //check if row is a reason for not playing
                                            foreach($htmlgame->find('tbody tr [reason]') as $e){
                                                $reasoncheck[$y] = $e->plaintext;
                                            }

                                            // Get the whole stats table for the season
                                            foreach($htmlgame->find('tbody tr td') as $e){
                                                $wholetable[$y] .= $e->plaintext.',';
                                            }

                                            // Get indivdual elements by data-stat name
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

                                //organize the scraped dataset as arrays
                                $data_week_reason = explode(',', $reasoncheck[$r]);
                                $data_week_num = explode(',', $week_num[$r]);
                                $data_passing = explode(',', $pass_yds[$r]);
                                $data_pass_td = explode(',', $pass_td[$r]);
                                $data_pass_int = explode(',', $pass_int[$r]);
                                $data_rush_yds = explode(',', $rush_yds[$r]);
                                $data_rush_td = explode(',', $rush_td[$r]);
                                $data_rec_yds = explode(',', $rec_yds[$r]);
                                $data_rec_td = explode(',', $rec_td[$r]);
                                $data_xpm = explode(',', $xpm[$r]);
                                $data_xpa = explode(',', $xpa[$r]);
                                $data_fgm = explode(',', $fgm[$r]);
                                $data_fga = explode(',', $fga[$r]);

                                printr($data_rec_yds, 0);

								// Convert $wholetable into an array where the week number is the key.
                                // Can only use this to get the first things since the table format changes by position and even by season
                                //

                                //printr($data_week_reason, 0);

                                $expwholetable = explode(',', $wholetable[$r]);

								foreach ($expwholetable as $key => $value):
                                    if (DateTime::createFromFormat('Y-m-d', $value) !== false):
                                        $dateval = $value;
                                        $storetable[$dateval][] = $value;
                                    else:
                                        $storetable[$dateval][] = $value;
                                    endif;
                                endforeach;

                                function checkzero($number){
                                    if($number):
                                        return $number;
                                    else:
                                        return 0;
                                    endif;
                                }

                                $j = 0;
                                foreach ($storetable as $date => $arr):
                                    if($arr[5] == '@'):
                                        $vs = '@';
                                    else:
                                        $vs = 'vs';
                                    endif;

                                    $starter = $arr[8];
                                        if($starter == 'Inactive' || $starter == 'Did Not Play'):
                                            $j--;
                                        endif;
                                            $pr_data_passing = $data_passing[$j];
                                            $pr_data_pass_td = $data_pass_td[$j];
                                            $pr_data_pass_int = $data_pass_int[$j];
                                            $pr_data_rush_yds = $data_rush_yds[$j];
                                            $pr_data_rush_td = $data_rush_td[$j];
                                            $pr_data_rec_yds = $data_rec_yds[$j];
                                            $pr_data_rec_td = $data_rec_td[$j];
                                            $pr_data_xpm = $data_xpm[$j];
                                            $pr_data_xpa = $data_xpa[$j];
                                            $pr_data_fgm = $data_fgm[$j];
                                            $pr_data_fga = $data_fga[$j];
                                            $j++;


                                    $weekbuild[$arr[2]] = array(
                                        'week_num' =>  $arr[2],
                                        'game_date' => $date,
                                        'team' => $arr[4],
                                        'game_location' => $vs,
                                        'opp' => $arr[6],
                                        'nfl_result' => $arr[7],
                                        'starter' => $arr[8],
                                        'pass_yds' => checkzero($pr_data_passing),
                                        'pass_td' => checkzero($pr_data_pass_td),
                                        'pass_int' => checkzero($pr_data_pass_int),
                                        'rush_yds' => checkzero($pr_data_rush_yds),
                                        'rush_td' => checkzero($pr_data_rush_td),
                                        'rec_yds' => checkzero($pr_data_rec_yds),
                                        'rec_td' => checkzero($pr_data_rec_td),
                                        'xpm' => checkzero($pr_data_xpm),
                                        'xpa' => checkzero($pr_data_xpa),
                                        'fgm' => checkzero($pr_data_fgm),
                                        'fga' => checkzero($pr_data_fga)
                                    );
                                endforeach;

                                printr($weekbuild, 0);

                                function insert_player_gamestats_scrape($playerid, $array, $pos, $year){

                                    foreach ($array as $key => $value):
                                        $xpm = $value['xpm'];
                                        $fgm = $value['fgm'];
                                        $pass_yds = $value['pass_yds'];
                                        $pass_td = $value['pass_td'];
                                        $rush_yds = $value['rush_yds'];
                                        $rush_td = $value['rush_td'];
                                        $pass_int = $value['pass_int'];
                                        $rec_yds = $value['rec_yds'];
                                        $rec_td = $value['rec_td'];

                                        if($pos == "PK"):
                                            $nflscore = pk_score_converter($year, $xpm, $fgm);
                                        else:
                                            $nflscore = pos_score_converter($year, $pass_yds, $pass_td, $rush_yds, $rush_td, $pass_int, $rec_yds, $rec_td);
                                        endif;
                                        $wzero = sprintf('%02d', $key);
                                        $playerscore = get_player_score_by_week($playerid, $year.$wzero);

                                        $insertarr[$year.$wzero] = array(
                                            'gamedate' => $value['game_date'],
                                            'nflteam' => $value['team'],
                                            'gamelocation' => $value['game_location'],
                                            'opp'  => $value['opp'],
                                            'pass_yds' => $value['pass_yds'],
                                            'pass_td' => $value['pass_td'],
                                            'pass_int' => $value['pass_int'],
                                            'rush_yds' => $value['rush_yds'],
                                            'rush_td' => $value['rush_td'],
                                            'rec_yds' => $value['rec_yds'],
                                            'rec_td' => $value['rec_td'],
                                            'xpm' => $value['xpm'],
                                            'xpa' => $value['xpa'],
                                            'fgm' => $value['fgm'],
                                            'fga' => $value['fga'],
                                            'pflscore' => $playerscore['points'],
                                            'nflscore' => $nflscore,
                                            'ptdiff' => $playerscore['points'] - $nflscore
                                            );

                                    endforeach;

                                    foreach ($insertarr as $ikey => $ivalue):
                                        global $wpdb;
                                         $query = $wpdb->update( $playerid, array(
                                            'game_date' => $ivalue['gamedate'],
                                            'nflteam' => $ivalue['nflteam'],
                                            'game_location' => $ivalue['gamelocation'],
                                            'nflopp' => $ivalue['opp'],
                                            'pass_yds' => $ivalue['pass_yds'],
                                            'pass_td' => $ivalue['pass_td'],
                                            'pass_int' => $ivalue['pass_int'],
                                            'rush_yds' => $ivalue['rush_yds'],
                                            'rush_td' => $ivalue['rush_td'],
                                            'rec_yds' => $ivalue['rec_yds'],
                                            'rec_td' => $ivalue['rec_td'],
                                            'xpm' => $ivalue['xpm'],
                                            'xpa' => $ivalue['xpa'],
                                            'fgm' => $ivalue['fgm'],
                                            'fga' => $ivalue['fga'],
                                            'nflscore' => $ivalue['nflscore'],
                                            'scorediff' => $ivalue['ptdiff']
                                         ),array('week_id'=> $ikey));
                                    endforeach;

                                    //return $insertarr;
                                    return 'Query:'.$query.'<br>Player id: '.$playerid.'<br>Week id:'.$key.'<br>PFL Points:'.$pflpts.'<br>NFL Points:'.$nflscore.'<br>Diff:'.$ptdiff;
                                }

                                // Uncomment the next line if you want to insert the data into the players table
                                $result = insert_player_gamestats_scrape($randomplayer, $weekbuild, $position, $r);


								?>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php

                                printr($result, 0);
                                // these arrays dont work.  use new $wholetable approch in 2022
                                //printr($cleanarray, 0);
                                //printr($data_passing, 0);
                                //printr($data_week_num, 0);
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