<?php
/*
 * Template Name: Scrape PFR for Two Point Conversions
 * Description: Script to Scrape Player Numbers From Pro Football Reference
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
								<select data-placeholder="Select an Existing Player" class="chzn-select" style="width:100%;" tabindex="1" id="playerDropScrapeNumber">
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
								<button class="btn btn-warning" id="playerSelectScrapeNumber">Select</button>
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
					<div class="col-xs-24 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php 


								$firstInitCap = strtoupper($last[0]);
								$pfr_id = $featuredplayer[10];	
								
								
								include_once('simplehtmldom/simple_html_dom.php');
								$html = file_get_html('https://www.pro-football-reference.com/players/'.$firstInitCap.'/'.$pfr_id.'.htm');

                                echo '<h2>TESTING HERE</h2>';

								if($pfr_id):					
										if($html):
//
                                            foreach($html->find('.uni_holder a=[data-tip]') as $element):
                                                $teamyear[] = explode('<', $element->outertext);
                                            endforeach;
                                            foreach($teamyear as $values):
                                                $newnumber[] = explode( '"', $values[1]);
                                            endforeach;

                                            foreach($newnumber as $value):
                                                $i = substr($value[5],  -4);
                                                $numberagain[$i] = explode( '=', $value[1]);
                                            endforeach;

                                            foreach($numberagain as $k => $v):
                                                $numersclean[$k] = $v[2];
                                            endforeach;

                                            $yearclean = array_values($yearsplayed);
                                            //arsort($yearclean);
                                            $firstyear = reset($yearclean);
                                            $firstnumber = reset($numersclean);
                                            foreach ($yearclean as $year):

                                                    if($numersclean[$year]):
                                                        $finalnumbers[$year] = $numberagain[$year][2];
                                                        $set = $numberagain[$year][2];
                                                    else:
                                                        $finalnumbers[$year] = $firstnumber;
                                                    endif;

                                            endforeach;

                                            echo $firstyear.' // '.$firstnumber;
                                            printr($numersclean, 0);
                                            printr($finalnumbers, 0);
                                            $encode = json_encode($finalnumbers);

                                            // in functions.php insert data into table
                                            $updatetable  = insert_player_number_array($getplayer, $encode);
											
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
<!--								<p>Pro Football Ref ID: --><?php //echo $firstInit.'/'.$pfr_id; ?><!--</p>-->
<!--								--><?php echo $encode; ?>
								</div>
					
							</div>
	
						</div>
					
					</div>
					
					<!-- GET BASIC PLAYER INFO -->
					<div class="col-xs-12 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php
								//printr($yearclean, 0);
								?>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php
								//printr($alldata, 0); 
								?>
							</div>
						</div>
					</div>
					
				</div>	
				
		</div>
		<?php include_once('main-nav.php'); ?>
	</div>
	
</div>


<script>

// DISABLE TO STOP AUTO RELOAD

//setTimeout(function(){
//	var scrapeclick = '/scrape-pfr-for-numbers/?id=<?php //echo $nextplayer; ?>//';
//    window.location.href = scrapeclick;
// }, 2000);

</script>




		
<?php get_footer(); ?>