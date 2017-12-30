<?php
/*
 * Template Name: Standings
 * Description: Page for displaying all of the Standings by Year
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->

<?php get_header(); ?>

<?php 	
	

$season = date("Y");
$year = $_GET['id'];
?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php echo $year; ?> Standings</h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">	
						<?php 
							$years = the_seasons();
			
							$standing = get_standings($year);
							?>
							
							<div class="row">
								<div class="col-xs-24 col-sm-20 col-md-16">
									<?php
									
									foreach($standing as $key => $item){
									   $arr_resort[$item['division']][$key] = $item;
									}
									
									foreach ($arr_resort as $div => $thedivision){
										
									
									?>
									<div class="table-responsive">
											<table class="table table-striped">
												<thead>
													<?php echo '<h3>'.$div.'</h3>'; ?>
													<tr>
														<th>Team</th>
														<th class="text-center">Wins</th>
														<th class="text-center">Loss</th>
														<th class="text-center">Win%</th>
														<th class="text-center">Points</th>
														<th class="text-center">PPG</th>
														<th class="text-center">Pt Vs</th>
														<th class="text-center">Diff</th>
														<th class="text-center">Div W</th>
														<th class="text-center">Div L</th>
														<th class="text-center">GB</th>
													</tr>
												</thead>
												<tbody>
									
									<?php
									
											
										foreach ($thedivision as $key => $value){
										$id = $value['id'];
										$seed = $value['seed'];
										$division = $value['division'];
										$teamid = $value['teamid'];
										$teamname = strtoupper($value['teamname']);
										$win = $value['win'];
										$loss = $value['loss'];
										$gb = $value['gb'];
										$pts = $value['pts'];
										$ptsvs = $value['ptsvs'];
										$divwin = $value['divwin'];
										$divloss = $value['divloss'];
										
										if($gb == 0){
											$gb = '-';
										}
										
										if($seed > 0){
											$playoffs = '('.$seed.') ';
										} else {
											$playoffs = '';
										}
										
										$winper = number_format($win / 14 , 3);
										$ppg = number_format($pts / 14 , 1);
										$diff = $pts - $ptsvs;
										
										echo '<tr>';
										echo '<td class="text-bold">'.$playoffs.$teamname.'</td>';
										echo '<td class="text-center">'.$win.'</td>';
										echo '<td class="text-center">'.$loss.'</td>';
										echo '<td class="text-center">'.$winper.'</td>';
										echo '<td class="text-center">'.$pts.'</td>';
										echo '<td class="text-center">'.$ppg.'</td>';
										echo '<td class="text-center">'.$ptsvs.'</td>';
										echo '<td class="text-center">'.$diff.'</td>';
										echo '<td class="text-center">'.$divwin.'</td>';
										echo '<td class="text-center">'.$divloss.'</td>';
										echo '<td class="text-center">'.$gb.'</td>';
										
										echo '</tr>';
										}
										
									}

									
									?>
										</tbody>
									</table>
								</div>
							</div>
							
							<div class="col-xs-24 col-sm-8">
								<?php selectseason(); ?>	
							</div>

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