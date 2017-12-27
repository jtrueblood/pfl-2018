<?php
/*
 * Template Name: Draft Plan
 * Description: Used for Euro-Trashers draft planning
 */
 ?>
 
 


<!-- Make the required arrays and cached files availible on the page -->
<?php 
	$season = date("Y");
	$year = $_GET['id'];
	$time = time();
	
	$mydb = new wpdb('root','root','pflmicro','localhost');	
	$drafttable = $mydb->get_row( "SELECT * FROM draftranking ORDER BY time DESC LIMIT 1" );							$lasttime = $drafttable->time;
	$timedif = $time - $lasttime;
	// one day is 86400
	
			
	// fantasy nerd 
	$cachednerdrankings = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/draftplan/cachednerd.txt';
	
	if ($timedif > 86400){
		$getnerdrankings = file_get_contents('http://www.fantasyfootballnerd.com/service/draft-rankings/json/hjurteyij8h5/');
		$put = serialize($getnerdrankings);
		file_put_contents($cachednerdrankings, $put);
	} else {
		simple_cache('draftplan/cachednerd');	
		$getnerdrankings = $_SESSION['draftplan/cachednerd'];
	}
		
	$nerdrankings = json_decode($getnerdrankings, true);
	
	if ($timedif > 86400){
		$nerdfile = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/draftplan/file'.$time.'.txt';

		$put = serialize($nerdrankings);
		file_put_contents($nerdfile, $put);
		$mydb->insert( 'draftranking', array('time' => $time, 'url' => $nerdfile));
	}
	
	get_cache("draftplan/file$lasttime", 0);	
	$laststoredarray = $_SESSION["draftplan/file$lasttime"];
	
	
	
	$result = array_diff($nerdrankings['DraftRankings'], $laststoredarray['DraftRankings']);	
	
	// compares current nerdrankings to previously stored values.  (current pos, previous position, change in position rank, current overall, previous overall, change in overall rank).  Positive change value means they improved.
	foreach($nerdrankings['DraftRankings'] as $checkdif){
		$playerid = $checkdif['playerId'];
		$currentposrank = $checkdif['positionRank'];
		$currentovrrank = $checkdif['overallRank'];
		foreach($laststoredarray['DraftRankings'] as $checkstored){
			if ($checkstored['playerId'] == $playerid){
				$thedif[$playerid] = array($currentposrank, $checkstored['positionRank'], $checkstored['positionRank'] - $currentposrank, $currentovrrank, $checkstored['overallRank'], $checkstored['overallRank'] - $currentovrrank);
			}	
		}
	}
	
	

	//printr($thedif, 0);
	
	// player ids of protected players
	$protected = array(
		1446 => 'WRZ', 1932 => 'WRZ', 259 => 'WRZ',
		1981 => 'PEP', 1398 => 'PEP', 1441 => 'PEP',
		2812 => 'ETS', 2771 => 'ETS', 2465 => 'ETS'
		);
	$flipped = array_flip($protected);
	
	
	foreach ($flipped as $getets){
		$etsprotect['ETS'] = $getets; 
	}	
	
	// players that I want to pay attention to 
	$watch = array(1895, 1170, 259);
	
	// players that have been drafted and are off the board
	$draft_array = array(
		1761 => 'ETS', 
		1172 => 'SON', 
		1895 => 'SNR',
		2828 => 'ETS'
		);
	
	$drafted = array_flip($draft_array);	
	
	
	
	
	//printr($nerdrankings, 1);
	//printr($etsprotect, 0);	
	
?>


<?php get_header(); ?>

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
			
					<div class="row">
							
						<div class="panel">
						<div class="panel-heading">
							<h3 class="panel-title">Filtering</h3>
						</div>
					
						<div class="panel-body">
							<table id="nerdrankingtable" class="table table-bordered dataTable no-footer dtr-inline" cellspacing="0" width="100%" role="grid" aria-describedby="demo-dt-basic_info" style="width: 100%;">
								<thead>
									<tr role="row">
										<th tabindex="0" aria-controls="demo-dt-basic">Status</th>
										<th class="sorting" tabindex="0" aria-controls="demo-dt-basic" style="width: 25%;">Name</th>
										<th class="sorting" tabindex="0" aria-controls="demo-dt-basic" style="width: 5%;">Position</th>
										<th class="sorting" tabindex="0" aria-controls="demo-dt-basic">Team</th>
										<th class="sorting" tabindex="0" aria-controls="demo-dt-basic">Protected</th>
										<th class="sorting" tabindex="0" aria-controls="demo-dt-basic">Bye</th>
										<th class="sorting" tabindex="0" aria-controls="demo-dt-basic">Float Rank</th>
										<th class="sorting" tabindex="0" aria-controls="demo-dt-basic">Pos Rank</th>
										<th class="sorting" tabindex="0" aria-controls="demo-dt-basic">Change</th>
										<th class="sorting" tabindex="0" aria-controls="demo-dt-basic" aria-sort="ascending">Overall</th>
										<th class="sorting" tabindex="0" aria-controls="demo-dt-basic">Nerd PID</th>
									</tr>

								</thead>
								<tbody>
								
								
								<?php foreach ($nerdrankings['DraftRankings'] as $rankings){
									
									// set each variables
									$name = $rankings['displayName'];
									$position = $rankings['position'];
									$team = $rankings['team'];
									$bye = $rankings['byeWeek'];	
									$float = $rankings['standDev'];	
									$nerdrank = $rankings['nerdRank'];
									$posrank = $rankings['positionRank'];	
									$overall = $rankings['overallRank'];
									$npid = $rankings['playerId'];
									$teamprotect = $protected[$npid];
									$change = $thedif[$npid][2];
									
									
									if ($teamprotect == 'ETS' OR $teamdraft == 'ETS'){
										$etsteam[] = array($npid, $name, $bye, $position, $posrank, $overall, $team);
									}
									
									
									//font awsome icons
																
									if (in_array($npid, $watch)){
										$target = '<i class="fa fa-dot-circle-o" aria-hidden="true"></i>'; //draft interest
									} else {
										$target = null;
									}
									
									if (in_array($npid, $drafted)){
										$check = '<i class="fa fa-check-square" aria-hidden="true"></i>'; //draft interest
										$isdrafted = 'greyout';
										$teamdraft = $draft_array[$npid];
									} else {
										$check = null;
										$isdrafted = null;
										$teamdraft = null;
									}
									
									if ($change > 0){
										$arrow = '<i class="fa fa-angle-up" aria-hidden="true"></i>';
										$rankcolor = 'greencell';
									} 
									if ($change < 0){
										$arrow = '<i class="fa fa-angle-down" aria-hidden="true"></i>';
										$rankcolor = 'redcell';
									}
									if ($change == 0){
										$arrow = '';
										$rankcolor = '';
									}
									
									
									// create table						
									if ($position == 'QB' OR $position == 'RB' OR $position == 'WR' OR $position == 'K' ){
										if ($position == 'K'){
											$position = 'PK';
										}
										if ($posrank < 100){
											if ($teamprotect != ''){
												$isprotected = 'greencell';
												$allprotections[] = array($npid, $name, $bye, $teamprotect, $position, $posrank, $overall);
												$heart = '<i class="fa fa-heart" aria-hidden="true"></i>'; //protected;
											} else {
												$isprotected = '';
												$heart = '';
											}
											
											echo '<tr role="row" class="odd">';
											echo '<td>'.$heart.' '.$target.' '.$check.'</td>';
											echo '<td class='.$isprotected.''.$isdrafted.'>'.$name.'</td>';
											echo '<td>'.$position.'</td>';
											echo '<td>'.$team.'</td>';
											echo '<td>'.$teamprotect.''.$teamdraft.'</td>';
											echo '<td>'.$bye.'</td>';
											echo '<td>'.$float.'</td>';
											echo '<td>'.$posrank.'</td>';
											echo '<td class='.$rankcolor.'>'.$change.' '.$arrow.'</td>';
											echo '<td>'.$overall.'</td>';
											echo '<td>'.$npid.'</td>';
											echo '</tr>';
											
											
										}
									}
									
									
									
								}	?>
								
								</tbody>
							</table></div></div>
						</div>

					
				</div>
					
						<div class="col-xs-24 col-sm-8">
						
							<div class="panel">
							
								<div class="panel-heading">
									<h3 class="panel-title">ETS Roster</h3>
								</div>

								<div class="panel-body">
								<?php 

										foreach ($etsteam as $putteam){
											$putname = $putteam[1];
											$putbye = $putteam[2];
											$putpos = $putteam[3];
											$putnflteam = $putteam[6];
											
											if ($putpos == 'QB'){
												echo '<h5>'.$putname.' - '.$putnflteam.' / '.$putbye.'</h5>';
											}
											if ($putpos == 'RB'){
												echo '<h5>'.$putname.' - '.$putnflteam.' / '.$putbye.'</h5>';
											}
											if ($putpos == 'WR'){
												echo '<h5>'.$putname.' - '.$putnflteam.' / '.$putbye.'</h5>';
											}
											if ($putpos == 'K'){
												echo '<h5>'.$putname.' - '.$putnflteam.' / '.$putbye.'</h5>';
											}
										}
									
								?>	
												
									
								</div>
								
							
							</div>
						
						</div>
						
					</div>
						
											
					</div><!-- end row -->			

				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->


		<?php include_once('main-nav.php'); ?>
		<?php include_once('aside.php'); ?>

		</div>
</div> 


<?php session_destroy(); ?>
		
</div>
</div>



<?php get_footer(); ?>