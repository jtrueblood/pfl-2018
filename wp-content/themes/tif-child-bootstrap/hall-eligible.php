<?php
/*
 * Template Name: HOF Eligible
 * Description: Page for list of Players Eligible for the Hall of Fame
 */
 ?>

<!-- In Dec of 2017 this template was switched over to pull data from mysql not from cached files.  -->
<!-- Make the required arrays and cached files availible on the page -->
<?php 
$season = date("Y");

$playerassoc = get_players_assoc();
$allleaders = get_allleaders();
$hall = get_award('Hall of Fame Inductee', 2);	
foreach ($hall as $winners){
	$newhall[$winners['year']] = $winners['pid'];
}
	
?>

<?php get_header(); ?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
				<h4>3 Years Retired, 800 career points or more</h4>
				<?php 
					foreach ($allleaders as $eligible){
						if ($eligible['points'] > 800){
							$better[$eligible['pid']] = array(
								'pid' => $eligible['pid'],
								'points' => $eligible['points'],
								'lastyear' => $eligible['lastyear']
							);
						}
					}
					
					foreach($better as $key => $item){
					   $arr_years[$item['lastyear']][$key] = $item;
					}

					ksort($arr_years, SORT_NUMERIC);
					
				
					foreach ($arr_years as $key => $value){
						
					$when = $key + 3;
					$then = $key + 1;	
					?>
					<div class="row">
						<div class="col-sm-6 eq-box-sm">
							<div class="panel panel-bordered panel-dark">
								<div class="panel-heading">
									<div class="panel-control">
										<em><small class="text-muted">Retired in<?php echo $key; ?></small> Eligible <?php echo $when; ?> </em>
									</div>
								</div>
								<div class="panel-body">
									<?php 
										foreach ($value as $players){
											
											$curr = $players['pid'];
											
											if(in_array($curr, $newhall)){
												$selected = '# - ';
											} else {
												$selected = '';
											}
											
											$f = $playerassoc[$curr][0];
											$l = $playerassoc[$curr][1];
											$p = $playerassoc[$curr][2];
											
											echo '<h5>'.$selected.$f.' '.$l.' - '.$p.'<h5>';
										}
									?>
								</div>
							</div>
						</div>
					</div>
					<?php 
					
					} ?>
						
								
								
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