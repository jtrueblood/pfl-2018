<?php
/*
 * Template Name: Protections Team
 * Description: Page for displaying all of the Protections by Team
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->

<?php get_header(); ?>



<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold">Team Protections</h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">	
						<div class="col-xs-24">
						
						<?php
							$protections = get_protections();
							$loop = 0;
							
							
							
							foreach($protections as $key => $item){
							   $arr_resort[$item['team']][$key] = $item;
							}
							
							ksort($arr_resort, SORT_NUMERIC);
							
							//printr($arr_resort, 1);
						$loop = 0;
	
						foreach ($arr_resort as $team){
							foreach ($team as $key => $value){

								$team = $value['team'];
								$first = $value['first'];
								$last = $value['last'];
								$position = $value['position'];
								$playerid = $value['playerid'];
							    $year = $value['year'];
								
								
								if($team != $checkteam){
										$checkteam = $team;
										?>
										<div class="col-xs-24">		
										
											<h4 class="text-center protection-season"><?php echo $team; ?> Protections</h4>
									
										</div>
										
										<?php  }  
											
										if ($loop % 3 == 0){ ?>
										<div class="row">
										<div class="col-xs-2">	
											<h3 class="text-bold text-center"><?php echo $year; ?></h3>												
										</div>		
				
										<?php } ?>
												
										<div class="col-xs-7">		
											<div class="panel protections">
												<div class="panel-body <?php echo $position;?>">	
												<?php
												if ($first == 'No Protection'){	
													echo 'No Protection';
												 } else {
													 echo '<img src="https://posse-football.dev/wp-content/themes/tif-child-bootstrap/img/players/'.$playerid.'.jpg" class="leaders-image"><h4 class="text-bold"><a href="/player/?id='.$playerid.'">'.$first.' '.$last.'</a></h4>, '.$position; 
												 }?>
												</div>
											</div>
										</div>
									
										<?php 
										$loop++;
										if ($loop % 3 == 0){ 
											echo '</div>';
										}
											
								}
							}
						?>
							
							
											
						</div>

										
						
						<!-- start sidebar -->
						
<!--
						<div class="col-xs-24 col-sm-6">
					
								<div class="panel">
									<div class="panel-body">
										
										<?php echo '<h3>A Title</h3>';?>
										
							
									</div>
								</div>
							</div>
											
						</div>
-->
						
						<!-- end sidebar -->
						
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