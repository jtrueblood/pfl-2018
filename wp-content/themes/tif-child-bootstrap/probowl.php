<?php
/*
 * Template Name: Pro Bowl
 * Description: Page Pro Bowl overviews
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	$season = 2015;
	
	get_cache('probowl', 0);	
	$probowl = $_SESSION['probowl'];
		
	$mydb = new wpdb('root','root','pflmicro','localhost');
	
	$getpros = $mydb->get_results("select * from probowl", ARRAY_N);
	
	$buildpros = array();
	foreach ($getpros  as $revisequery){
		$probowldata[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'year' => $revisequery[1], 
			'winner' => $revisequery[2], 
			'host' => $revisequery[3],  
			'egad_pts' => $revisequery[4],
			'dgas_pts' => $revisequery[5],
			'egad_mgr' => $revisequery[6],
			'dgas_mgr' => $revisequery[7]
		);
	}
	
// 	printr($probowldata, 1);
	
	$getproboxes = $mydb->get_results("select * from probowlbox", ARRAY_N);

	foreach ($getproboxes  as $revisequery){
		$proboxes[$revisequery[0]] = array(
			'id' => $revisequery[0], 
			'playerid' => $revisequery[1], 
			'pos' => $revisequery[2], 
			'team' => $revisequery[3],  
			'league' => $revisequery[4],
			'year' => $revisequery[5],
			'points' => $revisequery[6],
			'starter' => $revisequery[7],
			'pointsused' => $revisequery[8]
		);
	}
	
	//regroup boxscores by year	
	foreach($proboxes as $key => $item)
	{
	   $arr[$item['year']][$key] = $item;
	}
	
	ksort($arr, SORT_NUMERIC);
	
	
	
	// get an associative array of all players
	$players = get_players_assoc();
	
// 	printr($players, 1);
	
	$promvp = get_award('Pro Bowl MVP', 2);
?>

<?php get_header(); ?>

<div class="boxed add-to-top">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php the_title();?></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
			
					<?php
				foreach ($probowldata as $get){	
						// get the first row
					
					echo '<div class="col-sm-12 col-lg-12 eq-box-sm pro-bowl-box">';
						echo '<div class="panel panel-bordered panel-dark">';
							echo '<div class="panel-heading">';
							echo '<h3 class="panel-title"><span class="text-bold">'.$get['year'].'</span> Pro Bowl</h3>';
							echo '</div>';
							echo '<div class="panel-body">';
							if ($get['egad_pts'] > $get['dgas_pts']){
								echo '<span class="text-2x text-bold">EGAD </span>  <span class="text-2x text-bold pull-right">'.$get['egad_pts'].'</span><br>';
								echo '<span class="text-2x text-thin">DGAS </span>  <span class="text-2x text-thin pull-right">'.$get['dgas_pts'].'</span>';
							}  else {
								echo '<span class="text-2x text-bold">DGAS </span>  <span class="text-2x text-bold pull-right">'.$get['dgas_pts'].'</span><br>';
								echo '<span class="text-2x text-thin">EGAD </span>  <span class="text-2x text-thin pull-right">'.$get['egad_pts'].'</span>';
							} 

								echo '<div class="row">';
									echo '<hr/>
									<div class="col-xs-24"><h5>Boxscores</h5></div>';
										
										$myyear = $arr[$get['year']];
										
									
// 										printr($myyear, 0);
										
										echo '<div class="col-xs-24 col-sm-12">';
											foreach ($myyear as $year){
												if($year['league'] == 'EGAD'){
												$id = $year['playerid'];	
												echo '<div class="col-xs-18 to-the-left">';
													if($year['starter'] == 1){ echo '<span class="text-bold">';}
														echo $players[$id][0].' '.$players[$id][1].' ('.$year['team'].')';
													if($year['starter'] == 1){ echo '</span>';}
												echo '</div>';
												echo '<div class="col-xs-6">';
												    if($year['pointsused'] == 1){ echo '<span class="text-bold text-underline">';}
														echo $year['points'];
													if($year['pointsused'] == 1){ echo '</span>';}
												echo '</div>';
												}
											}
										echo '</div>';	
										
										echo '<div class="col-xs-24 col-sm-12">';
											foreach ($myyear as $year){
												if($year['league'] == 'DGAS'){
												$id = $year['playerid'];
												echo '<div class="col-xs-18 to-the-left">';
													if($year['starter'] == 1){ echo '<span class="text-bold">';}
														echo $players[$id][0].' '.$players[$id][1].' ('.$year['team'].')';
													if($year['starter'] == 1){ echo '</span>';}
												echo '</div>';
												echo '<div class="col-xs-6">';
													if($year['pointsused'] == 1){ echo '<span class="text-bold text-underline">';}
														echo $year['points'];
													if($year['pointsused'] == 1){ echo '</span>';}
												echo '</div>';
												}
											}
										echo '</div>';	
											
									
						
										
							
								echo '</div>';	
								
							echo '</div>';
							
							$theman = $promvp[$get['year']];
								
							echo '<hr><div class="row"><div class="pro-mvp">Game MVP: <span class="text-bold text-dark">'.$theman['first'].' '.$theman['last'].'</span>, '.$theman['team'].'</div></div>';
						
						echo '</div>';
					echo '</div>';
					
				}	
				?>


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