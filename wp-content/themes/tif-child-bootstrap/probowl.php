<?php
/*
 * Template Name: Pro Bowl
 * Description: Page Pro Bowl overviews
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	$season = 2018;
	$promvp = get_award('Pro Bowl MVP', 2);
 	//printr($probowldata, 0);
	
	$getproboxes = $wpdb->get_results("select * from wp_probowlbox", ARRAY_N);
	
	//printr($getproboxes, 1);
	
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
	foreach($proboxes as $key => $item){
	   $probowldata[$item['year']][$item['playerid']] = $item;
	}
	
	ksort($probowldata, SORT_NUMERIC);
	// get an associative array of all players
	$pname = get_players_assoc();
	
	// probowl details
	$getprobowl = $wpdb->get_results("select * from wp_probowl", ARRAY_N);
	
	foreach ($getprobowl as $value){
		$probowldetails[$value[1]] = array(
			'winner' => $value[2],	
			'host' => $value[3],
			'egad' => $value[4],
			'dgas' => $value[5],
			'mvp' => $promvp[$value[1]]['pid']
		);
	}
	//printr($probowldetails, 1);
	
	
	
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
				foreach ($probowldetails as $key => $value){
                    $theyear = $key;
						// get the first row
                echo '<div class="row">';
					echo '<div class="col-xs-24 col-sm-10 eq-box-sm pro-bowl-box">';
						echo '<div class="panel panel-bordered panel-dark">';
							echo '<div class="panel-heading">';
							echo '<h3 class="panel-title"><span class="text-bold">'.$key.'</span> Pro Bowl</h3>';
							echo '</div>';
							echo '<div class="panel-body">';
							if ($value['egad'] > $value['dgas']){
								echo '<span class="text-2x text-bold">EGAD </span>  <span class="text-2x text-bold pull-right">'.$value['egad'].'</span><br>';
								echo '<span class="text-2x text-thin">DGAS </span>  <span class="text-2x text-thin pull-right">'.$value['dgas'].'</span>';
							}  else {
								echo '<span class="text-2x text-bold">DGAS </span>  <span class="text-2x text-bold pull-right">'.$value['dgas'].'</span><br>';
								echo '<span class="text-2x text-thin">EGAD </span>  <span class="text-2x text-thin pull-right">'.$value['egad'].'</span>';
							} 
?>
							<div class="row">
								<hr/>
										<?php 
											$probowlyearteam = array();
											foreach($probowldata[$key] as $key => $item){
											   $probowlyearteam[$item['league']][] = $item;
											}
											ksort($probowlyearteam, SORT_NUMERIC);
	
										?>
		
										<div class="col-xs-24 col-sm-12">
											<div class="row">
												<?php 
													$probowlegad = array(); 
													foreach($probowlyearteam['EGAD'] as $key => $item){
													   $probowlegad[$item['pos']][] = $item;
													}
													//printr($probowlegad, 0);
												
												$labels = array('Player', 'Team', 'Starter', 'Points');	
												tablehead('EGAD Boxscore', $labels);	
												
												$egad = '';
												foreach ($probowlegad as $val){
													foreach ($val as $key => $valued){
														$egad .= '<tr><td>'.$pname[$valued['playerid']][0].' '.$pname[$valued['playerid']][1].'</td>';
														$egad .= '<td class="min-width">'.$valued['team'].'</td>';
														if($valued['starter'] == 0){
															$egad .= '<td class="min-width text-center"><i class="fa fa-circle"></i></td>';
														} else {
															$egad .= '<td class="min-width"></td>';
														}
														if($valued['pointsused'] == 1){
															$egad .= '<td class="min-width text-right"><strong>'.$valued['points'].'</strong></td></tr>';
														} else {
															$egad .= '<td class="min-width text-right">'.$valued['points'].'</td></tr>';
														}
													}
												}
												
												echo $egad;

												tablefoot('');
												
												?>
												

											</div>
										</div>		
										
										<div class="col-xs-24 col-sm-12">
											<div class="row">
												<?php 
													$probowldgas = array(); 
													foreach($probowlyearteam['DGAS'] as $key => $item){
													   $probowldgas[$item['pos']][] = $item;
													}
													//printr($probowldgas, 0);
													
													$labels = array('Player', 'Team', 'Starter', 'Points');	
													tablehead('DGAS Boxscore', $labels);	
													
													$dgas = '';
													foreach ($probowldgas as $val){
														foreach ($val as $key => $valued){
															$dgas .= '<tr><td>'.$pname[$valued['playerid']][0].' '.$pname[$valued['playerid']][1].'</td>';
															$dgas .= '<td class="min-width">'.$valued['team'].'</td>';
															if($valued['starter'] == 0){
															$dgas .= '<td class="min-width text-center"><i class="fa fa-circle"></i></td>';
															} else {
																$dgas .= '<td class="min-width"></td>';
															}
															if($valued['pointsused'] == 1){
																$dgas .= '<td class="min-width text-right"><strong>'.$valued['points'].'</strong></td></tr>';
															} else {
																$dgas .= '<td class="min-width text-right">'.$valued['points'].'</td></tr>';
															}
														}
													}
													
													echo $dgas;
	
													tablefoot('');
												
												?>
												
											</div>

										</div>

										
								</div>
                            <?php
                            if($theyear == 2023) {
                                echo '<p>This was the first ever OT Pro Bowl.  All starters scores were used, followed by the backups. Normal OT rules applied.</p>';
                            }
                            ?>
							</div>
							
							<?php
							$theman = $value['mvp'];
								
							echo '<hr><div class="row">
                                <div class="pro-mvp">Game MVP: <span class="text-bold text-dark">'.$pname[$theman][0].' '.$pname[$theman][1].'</div>
                            </div>';
						
						echo '</div>';
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

		
</div>
</div>


<?php get_footer(); ?>