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
				
				
			$r = 1;
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
				
				
				
				
			if ($r % 4 == 0){ echo '<div class="row">'; }	
			?>
				
			<div class="col-xs-24 col-sm-12 col-md-6 eq-box-sm">	
				<div class="panel widget">
					<div class="widget-header bg-light">
						<?php
							$playerimgobj = get_attachment_url_by_slug($hallid);
							$imgid =  attachment_url_to_postid( $playerimgobj );
							$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
							$playerimg = $image_attributes[0];
						?>
						
						<img class="widget-bg img-responsive" src="<?php echo $playerimg; ?>" alt="Image">
					</div>
					<div class="widget-body text-center bg-grey">
						<img alt="Profile Picture" class="widget-img img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/pos-<?php echo $hallpos; ?>.jpg">
						<h4 class="mar-no text-center"><?php echo '<a href="/player?id='.$hallid.'">'.$hallfirst.' '.$halllast.'</a>'; ?></h4>
						<p class="text-muted text-center mar-btm"><?php echo $teamfull; ?></p>
						<span class="text-lg"><?php 
							echo $hallyear.' Hall of Fame Inductee'; 
						?> </span>
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
									<td><span class="text-bold"><?php echo $career['ppg']; ?></span></td>
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
											
											echo $stid.' - '.$stteam.' | '.$stpts.' Pts';
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
												echo $key.' - '.$value.'<br>';
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
												echo $key.' - '.$value.'<br>';
											}
										?>	
									</span></td>
								</tr>
								
								<?php 
									
									} ?>
								
								</tbody>
								</table>
								
								
<!--
								<?php printr($pbapps, 0); ?>
								<?php printr($playerchamps, 0); ?>
-->
								
								</div>
					</div>
				</div>
			</div>
			
					
			<?php 
				if ($r % 4 == 0){ echo '</div>'; }
				$r++;
				
				} ?>	
					
					
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