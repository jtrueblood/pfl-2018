<?php
/*
 * Template Name: Scrape PFR
 * Description: Script to Scrape Player Stats From Pro Football Reference
 */


/*
$url1=$_SERVER['REQUEST_URI'];
header("Refresh: 5; URL=$url1");
*/

$getplayer = $_GET['id'];

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
								<select data-placeholder="Select an Existing Player" class="chzn-select" style="width:100%;" tabindex="1" id="playerDropScrape">
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
								<button class="btn btn-warning" id="playerSelectScrape">Select</button>
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
                                $yearclean = array(2024);
								printr($yearclean, 0);
								
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
								
									$playerstats = array(
										'week_num' => $week_num,
										'game_date' => $game_date,
										'team' => $team,
										'game_location' => $game_location,
										'opp' => $opp,
 										'pass_yds' => $pass_yds,
										'pass_td' => $pass_td,
										'pass_int' => $pass_int,
										'rush_yds' => $rush_yds,
										'rush_td' => $rush_td,
										'rec_yds' => $rec_yds,
										'rec_td' => $rec_td,
					
										'xpm' => $xpm,
										'xpa' => $xpa,
										'fgm' => $fgm,
										'fga' => $fga
									
									);		
								
								$json_store = json_encode($playerstats);
								
								$destination_folder = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/pfr-gamelogs';


								if (file_exists($destination_folder.'/'.$randomplayer.'.json')):
                                    $report_message = $randomplayer.' -- file exsists || ';
                                    echo '<script>console.log("'.$randomplayer.' - file exsists");</script>';
                                    echo $report_message;
								else:
                                    if($json_store):
                                        file_put_contents("$destination_folder/$randomplayer.json", $json_store);
                                        $report_message =  $randomplayer.' -- Added to pfr-gamelogs-- || ';
                                        echo $json_store;
                                        echo '<script>console.log("'.$randomplayer.' - added to gamelog");</script>';
                                        echo $report_message;
                                    endif;
								endif;

								// if the 'run' uri value is set to run, run the file anyway...
                                $m = '_a';
								if($getrun == 1):
                                    file_put_contents("$destination_folder/$randomplayer$m.json", $json_store);
                                    $report_message =  $randomplayer.' -- Added to pfr-gamelogs-- || ';
                                    echo $json_store;
                                    echo '<script>console.log("'.$randomplayer.' - added to gamelog");</script>';
                                    echo $report_message;
                                endif;

								//printr($cleanlabels, 0);
								//printr($json_store, 0);	    
								?>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php
                               printr($playerstats, 0);
								//printr($dataarray[0], 0);
								//printr($yearsplayed, 0);
								printr($alldata, 0);
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
	$log_file = $destination_folder.'/file.log'; 
	error_log($report_message, 3, $log_file); 
?>


<script>

    // DISABLE TO STOP AUTO RELOAD

//setTimeout(function(){
//	var scrapeclick = '/scrape-pro-football-ref/?id=--><?php //echo $nextplayer; ?>//';
//    window.location.href = scrapeclick;
// }, 7000);

</script>


<script>
	var scrapeclick = '/scrape-pro-football-ref/?id=<?php echo $nextplayer; ?>';
       //    console.log(scrapeclick);
</script>


		
<?php get_footer(); ?>