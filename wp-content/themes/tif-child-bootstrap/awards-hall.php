<?php
/*
 * Template Name: Awards Hall
 * Description: Used for individual player awards where the career stats are displayed (Hall of Fame)
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
	<?php 
	
	$season = date("Y");
	
	$awardid = get_field('award_id' );
	
	$award = get_award('Hall of Fame Inductee', 2);	
	
	$teams = get_teams();

/*	
	$get = get_player_career_stats('2000GarcQB');
 	printr($get, 1);
*/	

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
			<?php 
			function labeltheseaward($awardid){
					if ($awardid == 'mvp'){
						echo 'Most Valuable Player';
					}
					if ($awardid == 'pbm'){
						echo 'Posse Bowl MVP';
					}
					if ($awardid == 'pro'){
						echo 'Pro Bowl MVP';
					}
					if ($awardid == 'roty'){
						echo 'Rookie of the Year';
					}
				}	
				?>
					
				<!-- start new layout -->
				<?php
					$i = 0;	
				
				foreach ($award as $hall){	
				$hallyear = $hall['year'];
				$hallfirst = $hall['first'];
				$halllast = $hall['last'];
				$hallid = $hall['pid'];
				$hallpos = $hall['position'];
				$playerstats = get_player_data($hallid);
				
				$career = get_player_career_stats($hallid); 
				
				$pbapps = array();
				$playerchamps = array();
				$printawards = array();
				
				$playerimgobj = get_attachment_url_by_slug($hallid);
				$imgid =  attachment_url_to_postid( $playerimgobj );
				$image_attributes = wp_get_attachment_image_src($imgid, array( 100, 100 ));	
				$playerimg = $image_attributes[0];
				
				$justchamps = get_just_champions();
									
				$get = playerplayoffs($hallid);
				foreach($get as $key => $value){
					if($value['week'] == 16){
						$pbapps[$value['year']] = $value['team'];
					}
				}
				
				foreach ($pbapps as $key => $value){
					if ($value == $justchamps[$key]){
						$playerchamps[$key] = $value;
					}
				}
			
				$plawards = get_player_award($hallid);
				
				foreach ($plawards as $key => $value){
					if ($value['award'] != 'Hall of Fame Inductee'){
						$printawards[] = $value['awardid'];
					}
				}
				
				$number_ones = get_number_ones();
				
				$modulus = 3;
					
					if ($i % $modulus == 0){
						echo '<div class="row">';
					}	
					
					echo '<div class="col-sm-12 col-lg-8 eq-box-sm">';
						echo '<div class="panel panel-bordered panel-dark">';
							echo '<div class="panel-heading">';
								echo '<h3 class="panel-title">'.$hallyear.' Hall of Fame Inductee</h3>';
							echo '</div>';
							
							echo '<div class="panel-body">';
								echo '<span class="text-2x text-bold"><a href="/player?id='.$hallid.'">'.$hallfirst.' '.$halllast.'</a></span>&nbsp;'.$hallpos;
							//echo '<a href="/player/?id='.$val['pid'].'"><img src="'.$playerimg.'" class="img-responsive"/></a>';
							echo '<img alt="Profile Picture" class="widget-img img-border-light" style="width:100px; height:100px; left:75%; top:10px;" src="'.$playerimg.'">';
							?>
							<div class="table-responsive mar-top">
									
								<table class="table table-striped">
								<tbody>
								<tr>
									<td class="text-left">Points Scored</td>
									<td><span class="text-bold"><?php echo number_format($career['points'],0); ?></span></td>
								</tr>
								<tr>
									<td class="text-left">Games Played</td>
									<td><span class="text-bold"><?php echo $career['games']; ?></span></td>
								</tr>
								<tr>
									<td class="text-left">Seasons</td>
									<td><span class="text-bold"><?php echo $career['years'][0].' - '.end($career['years']); ?></span></td>
								</tr>
								<tr>
									<td class="text-left">Points Per Game</td>
									<td><span class="text-bold"><?php echo number_format($career['ppg'], 1); ?></span></td>
								</tr>
								<tr>
									<td class="text-left">Career High</td>
									<td><span class="text-bold"><?php echo $career['high']; ?></span></td>
								</tr>
								<?php if(!empty($printawards)){
									asort($printawards);
									
									?>
								<tr>
									<td class="text-left">Career Awards</td>
									<td><span class="text-bold">
										<?php foreach ($printawards as $value){
											$awid = substr($value , 0, -4);
											$awyr = substr($value, -4);
											
											echo $awyr.' ';
											labeltheseaward($awid);
											echo '<br>';
											
											
											}?>
									</span></td>
								</tr>
								
								
								<?php
									foreach ($number_ones as $key => $value){
										if ($value['playerid'] == $hallid){
											$player_number_ones[$key] = array(
												'id' => $value['id'],
												'points' => $value['points'],
												'team' => $value['teams']
											);
										}
									}
									
									if(!empty($player_number_ones)){?>
									
									<tr>
									<td class="text-left">Position Scoring Titles</td>
									<td><span class="text-bold">
										<?php foreach ($player_number_ones as $value){
											$stid = substr($value['id'], -4);
											$stpts = $value['points'];
											$stteam = $value['team'];
											
											echo $stid.' - '.$teams[$stteam]['team'].' | '.$stpts.' Pts';
											echo '<br>';
											
											
											}?>
										</span></td>
									</tr>
									<?php 
										$player_number_ones = array();
										}
									?>
								
								<?php
									}
									if (!empty($pbapps)){ 
								?>
								<tr>
									<td class="text-left">Posse Bowl Appearances</td>
									<td><span class="text-bold">
										<?php foreach($pbapps as $key => $value){
												echo $key.' - '.$teams[$value]['team'].'<br>';
											}
										?>	
									</span></td>
								</tr>
								<?php 
									}
									if (!empty($playerchamps)){ 
								?>
								<tr>
									<td class="text-left">PFL Championships</td>
									<td><span class="text-bold">
										<?php foreach($playerchamps as $key => $value){
												echo $key.' - '.$teams[$value]['team'].'<br>';
											}
										?>	
									</span></td>
								</tr>
								
								<?php 
									
									} ?>
								
								</tbody>
								</table>
								<?php
							$i++;
							
						echo '</div>';
					echo '</div>';
					
					echo '</div>';
					echo '</div>';
					if ($i % $modulus == 0){echo '</div>';} /* close 'row' every 3rd time through the loop */
					
					} ?>
					<!-- end new layout -->
					
				</div>
				<!--End page content-->

			</div><!--END CONTENT CONTAINER-->


		<?php include_once('main-nav.php'); ?>
		<?php include_once('aside.php'); ?>

		</div>
</div> 

<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>