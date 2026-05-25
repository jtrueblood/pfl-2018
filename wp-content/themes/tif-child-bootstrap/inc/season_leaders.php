<div class="panel">
	<div class="panel-heading">
		<h3 class="panel-title">Position Title Winners</h3>
	</div>
	
	<div class="panel-body">
		
		<div id="seasonawards">
		
		<?php 
		$getleaders = get_season_leaders($year);

		$setgames = 7; // Set Number of Games for PPG
		foreach($getleaders as $key => $item){
			$leaders[$item['position']][$item['playerid']] = $item;
			if( $item['games'] >= $setgames){  // Set Number of Games
				$allppg[$item['playerid']] = $item['points'] / $item['games'];
			}
		}
		
		$qb_leaders = $leaders['QB'];
		uasort($qb_leaders, function ($a, $b) {
			return $b['points'] <=> $a['points'];
		});
		$qb_top = reset($qb_leaders);
		
		$rb_leaders = $leaders['RB'];
		uasort($rb_leaders, function ($a, $b) {
			return $b['points'] <=> $a['points'];
		});
		$rb_top = reset($rb_leaders);

		$wr_leaders = $leaders['WR'];
		uasort($wr_leaders, function ($a, $b) {
			return $b['points'] <=> $a['points'];
		});
		$wr_top = reset($wr_leaders);	
		
		$pk_leaders = $leaders['PK'];
		uasort($pk_leaders, function ($a, $b) {
			return $b['points'] <=> $a['points'];
		});
		$pk_top = reset($pk_leaders);
		
		$numberones = get_number_ones();
		
		foreach ($numberones as $item){
			$thisyearone[$item['year']][] = $item;
		}
		foreach ($thisyearone[$year] as $newitem){
			$yearone[$newitem['pos']][$newitem['playerid']] = $newitem;
		}
		
		?>
		
		<div class="row" style="display: flex; flex-wrap: wrap;">
			<style>
				#seasonawards .col-xs-24 { display: flex; flex-direction: column; }
				#seasonawards .panel { display: flex; flex-direction: column; height: 100%; box-shadow: none; border: 3px solid #ddd; }
				#seasonawards .panel .widget-body { flex-grow: 1; display: flex; flex-direction: column; justify-content: center; border: none; }
			</style>
			
			
			
				<?php 
				if(count($yearone['QB']) == 2) {
					// Two tied QBs
					?>
					<div class="col-xs-24 col-sm-6">
						<div class="panel" style="display: flex; flex-direction: column;">
							<div style="display: flex;">
								<?php foreach($yearone['QB'] as $key => $value) { ?>
								<div style="flex: 1;">
									<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
										<?php
										$info = get_player_basic_info($key);
										$playerimgobj = get_attachment_url_by_slug($key);
										$imgid = attachment_url_to_postid($playerimgobj);
										$image_attributes = wp_get_attachment_image_src($imgid, array(400, 400));
										$playerimg = $image_attributes[0];
										?>
										<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
									</div>
								</div>
								<?php } ?>
							</div>
							<div style="text-align: center; padding: 10px;">
								<h5 style="margin: 0;"><?php echo $year; ?> Passing Title (Tied)</h5>
							</div>
							<div style="display: flex; flex: 1;">
								<?php foreach($yearone['QB'] as $key => $value) { ?>
								<div style="flex: 1;">
									<?php
									$info = get_player_basic_info($key);
									?>
									<div class="widget-body text-center" style="padding: 10px;">
										<h6 class="mar-no text-center" style="font-size: 12px;"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h6>
										<p class="text-light text-center mar-top" style="font-size: 12px;"><?php echo $value['points']; ?> pts</p>
										<?php 
										$playerteam = get_player_teams_season($key);
										$teams = $playerteam[$year];
										 if(is_array($teams)) { $tags = implode(', ', $teams); } else { $tags = $teams; }
										?>
										<p class="text-light text-center" style="font-size: 11px;"><?php echo $tags; ?></p>
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php
				} else {
					foreach ($yearone['QB'] as $key => $value){ ?>
					<div class="col-xs-24 col-sm-6">
						<div class="panel">
							<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
								
								<?php							
								$info = get_player_basic_info($key);
								$playerteam = get_player_teams_season($key);
								$teams = $playerteam[$year];
									
								$playerimgobj = get_attachment_url_by_slug($key);
								$imgid =  attachment_url_to_postid( $playerimgobj );
								$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
								$playerimg = $image_attributes[0];
								?>
							
								<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
							</div>
							<div class="widget-body text-center">
								<h5><?php echo $year; ?> Passing Title</h5>
								<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
								<p class="text-light text-center mar-top">Points: <?php echo $value['points'] ?></p>
								<?php 
									if(is_array($teams)){
										$tags = implode(', ', $teams);
									} else {
										$tags = $teams;
									}
									echo '<p class="text-light text-center">'.$tags.'</p>';
								?>
							</div>
						</div>
					</div>	
					<?php } ?>
				<?php } ?>

				<?php 
				if(count($yearone['RB']) == 2) {
					?>
					<div class="col-xs-24 col-sm-6">
						<div class="panel" style="border: 1px solid #ddd; display: flex; flex-direction: column;">
							<div style="display: flex;">
								<?php foreach($yearone['RB'] as $key => $value) { ?>
								<div style="flex: 1;">
									<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
										<?php
										$info = get_player_basic_info($key);
										$playerimgobj = get_attachment_url_by_slug($key);
										$imgid = attachment_url_to_postid($playerimgobj);
										$image_attributes = wp_get_attachment_image_src($imgid, array(400, 400));
										$playerimg = $image_attributes[0];
										?>
										<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
									</div>
								</div>
								<?php } ?>
							</div>
							<div style="text-align: center; padding: 10px;">
								<h5 style="margin: 0;"><?php echo $year; ?> Rushing Title (Tied)</h5>
							</div>
							<div style="display: flex; flex: 1;">
								<?php foreach($yearone['RB'] as $key => $value) { ?>
								<div style="flex: 1;">
									<?php
									$info = get_player_basic_info($key);
									?>
									<div class="widget-body text-center" style="padding: 10px;">
										<h6 class="mar-no text-center" style="font-size: 12px;"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h6>
										<p class="text-light text-center mar-top" style="font-size: 12px;"><?php echo $value['points']; ?> pts</p>
										<?php 
										$playerteam = get_player_teams_season($key);
										$teams = $playerteam[$year];
										 if(is_array($teams)) { $tags = implode(', ', $teams); } else { $tags = $teams; }
										?>
										<p class="text-light text-center" style="font-size: 11px;"><?php echo $tags; ?></p>
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php
				} else {
					foreach ($yearone['RB'] as $key => $value){ ?>
					<div class="col-xs-24 col-sm-6">
						<div class="panel">
							<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
								
								<?php							
								$info = get_player_basic_info($key);
								$playerteam = get_player_teams_season($key);
								$teams = $playerteam[$year];
									
								$playerimgobj = get_attachment_url_by_slug($key);
								$imgid =  attachment_url_to_postid( $playerimgobj );
								$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
								$playerimg = $image_attributes[0];
								?>
							
								<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
							</div>
							<div class="widget-body text-center">
								<h5><?php echo $year; ?> Rushing Title</h5>
								<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
								<p class="text-light text-center mar-top">Points: <?php echo $value['points'] ?></p>
								<?php 
									if(is_array($teams)){
										$tags = implode(', ', $teams);
									} else {
										$tags = $teams;
									}
									echo '<p class="text-light text-center">'.$tags.'</p>';
								?>
							</div>
						</div>
					</div>	
					<?php } ?>
				<?php } ?>
			
				<?php 
				if(count($yearone['WR']) == 2) {
					?>
					<div class="col-xs-24 col-sm-6">
						<div class="panel" style="border: 1px solid #ddd; display: flex; flex-direction: column;">
							<div style="display: flex;">
								<?php foreach($yearone['WR'] as $key => $value) { ?>
								<div style="flex: 1;">
									<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
										<?php
										$info = get_player_basic_info($key);
										$playerimgobj = get_attachment_url_by_slug($key);
										$imgid = attachment_url_to_postid($playerimgobj);
										$image_attributes = wp_get_attachment_image_src($imgid, array(400, 400));
										$playerimg = $image_attributes[0];
										?>
										<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
									</div>
								</div>
								<?php } ?>
							</div>
							<div style="text-align: center; padding: 10px;">
								<h5 style="margin: 0;"><?php echo $year; ?> Receiving Title (Tied)</h5>
							</div>
							<div style="display: flex; flex: 1;">
								<?php foreach($yearone['WR'] as $key => $value) { ?>
								<div style="flex: 1;">
									<?php
									$info = get_player_basic_info($key);
									?>
									<div class="widget-body text-center" style="padding: 10px;">
										<h6 class="mar-no text-center" style="font-size: 12px;"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h6>
										<p class="text-light text-center mar-top" style="font-size: 12px;"><?php echo $value['points']; ?> pts</p>
										<?php 
										$playerteam = get_player_teams_season($key);
										$teams = $playerteam[$year];
										 if(is_array($teams)) { $tags = implode(', ', $teams); } else { $tags = $teams; }
										?>
										<p class="text-light text-center" style="font-size: 11px;"><?php echo $tags; ?></p>
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php
				} else {
					foreach ($yearone['WR'] as $key => $value){ ?>
					<div class="col-xs-24 col-sm-6">
						<div class="panel">
							<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
								
								<?php							
								$info = get_player_basic_info($key);
								$playerteam = get_player_teams_season($key);
								$teams = $playerteam[$year];
									
								$playerimgobj = get_attachment_url_by_slug($key);
								$imgid =  attachment_url_to_postid( $playerimgobj );
								$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
								$playerimg = $image_attributes[0];
								?>
							
								<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
							</div>
							<div class="widget-body text-center">
								<h5><?php echo $year; ?> Receiving Title</h5>
								<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
								<p class="text-light text-center mar-top">Points: <?php echo $value['points'] ?></p>
								<?php 
									if(is_array($teams)){
										$tags = implode(', ', $teams);
									} else {
										$tags = $teams;
									}
									echo '<p class="text-light text-center">'.$tags.'</p>';
								?>
							</div>
						</div>
					</div>	
					<?php } ?>
				<?php } ?>


				<?php 
				if(count($yearone['PK']) == 2) {
					?>
					<div class="col-xs-24 col-sm-6">
						<div class="panel" style="border: 1px solid #ddd; display: flex; flex-direction: column;">
							<div style="display: flex;">
								<?php foreach($yearone['PK'] as $key => $value) { ?>
								<div style="flex: 1;">
									<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
										<?php
										$info = get_player_basic_info($key);
										$playerimgobj = get_attachment_url_by_slug($key);
										$imgid = attachment_url_to_postid($playerimgobj);
										$image_attributes = wp_get_attachment_image_src($imgid, array(400, 400));
										$playerimg = $image_attributes[0];
										?>
										<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
									</div>
								</div>
								<?php } ?>
							</div>
							<div style="text-align: center; padding: 10px;">
								<h5 style="margin: 0;"><?php echo $year; ?> Kicking Title (Tied)</h5>
							</div>
							<div style="display: flex; flex: 1;">
								<?php foreach($yearone['PK'] as $key => $value) { ?>
								<div style="flex: 1;">
									<?php
									$info = get_player_basic_info($key);
									?>
									<div class="widget-body text-center" style="padding: 10px;">
										<h6 class="mar-no text-center" style="font-size: 12px;"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h6>
										<p class="text-light text-center mar-top" style="font-size: 12px;"><?php echo $value['points']; ?> pts</p>
										<?php 
										$playerteam = get_player_teams_season($key);
										$teams = $playerteam[$year];
										 if(is_array($teams)) { $tags = implode(', ', $teams); } else { $tags = $teams; }
										?>
										<p class="text-light text-center" style="font-size: 11px;"><?php echo $tags; ?></p>
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php
				} else {
					foreach ($yearone['PK'] as $key => $value){ ?>
					<div class="col-xs-24 col-sm-6">
						<div class="panel">
							<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
								
								<?php							
								$info = get_player_basic_info($key);
								$playerteam = get_player_teams_season($key);
								$teams = $playerteam[$year];
									
								$playerimgobj = get_attachment_url_by_slug($key);
								$imgid =  attachment_url_to_postid( $playerimgobj );
								$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
								$playerimg = $image_attributes[0];
								?>
							
								<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
							</div>
							<div class="widget-body text-center">
								<h5><?php echo $year; ?> Kicking Title</h5>
								<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
								<p class="text-light text-center mar-top">Points: <?php echo $value['points'] ?></p>
								<?php 
									if(is_array($teams)){
										$tags = implode(', ', $teams);
									} else {
										$tags = $teams;
									}
									echo '<p class="text-light text-center">'.$tags.'</p>';
								?>
							</div>
						</div>
					</div>	
					<?php } ?>
				<?php } ?>
				
				<div class="col-xs-24 col-sm-6">		
					<div class="panel">
						<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
							
							<?php
							arsort($allppg);
							$topppg = reset($allppg);
							$pidppg = key($allppg);					
							$info = get_player_basic_info($pidppg);
							$playerteam = get_player_teams_season($pidppg);
							$teams = $playerteam[$year];
								
							$playerimgobj = get_attachment_url_by_slug($pidppg);
							$imgid =  attachment_url_to_postid( $playerimgobj );
							$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
							$playerimg = $image_attributes[0];
							?>
						
							<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
						</div>
						<div class="widget-body text-center">
							<h5><? echo $year ?> Pts Per Game</h5>
							<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
							<p class="text-light text-center mar-top"><?php echo number_format($topppg, 1); ?> PPG</p>
							<?php 
								if(is_array($teams) && !empty($teams)){
									foreach ($teams as $te){
										echo '<p class="text-light text-center">'.$te.'</p>';
									}
								} elseif($teams && !is_array($teams)){
									echo '<p class="text-light text-center">'.$teams.'</p>';
								}
							?>
                            <p class="text-sm">Min <?php echo $setgames;?> Games</p>
						</div>
					</div>
				</div>	

				

				<div class="col-xs-24 col-sm-6">		
					<div class="panel">
						<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
							
							<?php
							$seasonpvq = get_season_pvq_leader();
							$pvqwinner = $seasonpvq[$year]['playerid'];						
							$info = get_player_basic_info($pvqwinner);
							$playerteam = get_player_teams_season($pvqwinner);
							$teams = $playerteam[$year];
								
							$playerimgobj = get_attachment_url_by_slug($pvqwinner);
							$imgid =  attachment_url_to_postid( $playerimgobj );
							$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
							$playerimg = $image_attributes[0];
							?>
						
							<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
						</div>
						<div class="widget-body text-center">
							<h5><? echo $year ?> PVQ Leader</h5>
							<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
							<p class="text-light text-center mar-top">1.000 PVQ</p>
							<?php
                            //printr($teams, 0);
								if(is_array($teams) && !empty($teams)){
									foreach ($teams as $te){
										echo '<p class="text-light text-center">'.$te.'</p>';
									}
								} elseif($teams && !is_array($teams)){
									echo '<p class="text-light text-center">'.$teams.'</p>';
								}
							?>
						</div>
					</div>
				</div>	

				<?php 
				// Display highest individual game score
				$highest = get_highest_individual_game_score($year);
				if(!empty($highest)){
				?>
				<div class="col-xs-24 col-sm-6">		
					<div class="panel">
						<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
							
							<?php
							$playerimgobj = get_attachment_url_by_slug($highest['pid']);
							$imgid =  attachment_url_to_postid( $playerimgobj );
							$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
							$playerimg = $image_attributes[0];
							?>
						
							<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
						</div>
						<div class="widget-body text-center">
							<h5><? echo $year ?> Highest Game Score</h5>
							<h4 class="mar-no text-center"><?php echo $highest['first'].'<br>'.$highest['last']; ?></h4>
							<p class="text-light text-center mar-top"><?php echo $highest['points']; ?> Points</p>
							<p class="text-light text-center"><?php echo $teamlist[$highest['team']]; ?></p>
						</div>
					</div>
				</div>	
				<?php 
				}
				?>

				<?php 
				// Display best bench player(s)
				$bestbench = get_best_bench_player($year);
				if(!empty($bestbench)){
					if(count($bestbench) == 2) {
						// Two players tied - display in one panel side by side
						?>
						<div class="col-xs-24 col-sm-6">
							<div class="panel" style="display: flex; flex-direction: column;">
								<div style="display: flex;">
									<?php foreach($bestbench as $player) { ?>
									<div style="flex: 1;">
										<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
											<?php
											$playerimgobj = get_attachment_url_by_slug($player['pid']);
											$imgid =  attachment_url_to_postid( $playerimgobj );
											$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
											$playerimg = $image_attributes[0];
											?>
											<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
										</div>
									</div>
									<?php } ?>
								</div>
								<div style="text-align: center; padding: 10px;">
									<h5 style="margin: 0;"><? echo $year ?> Best Bench Player (Tied)</h5>
								</div>
								<div style="display: flex; flex: 1;">
									<?php foreach($bestbench as $player) { ?>
									<div style="flex: 1;">
										<?php
										$playerimgobj = get_attachment_url_by_slug($player['pid']);
										$imgid =  attachment_url_to_postid( $playerimgobj );
										$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
										$playerimg = $image_attributes[0];
										?>
										<div class="widget-body text-center" style="padding: 10px;">
											<h6 class="mar-no text-center" style="font-size: 12px;"><?php echo $player['first'].'<br>'.$player['last']; ?></h6>
											<p class="text-light text-center mar-top" style="font-size: 12px;"><?php echo number_format($player['ppg'], 1); ?> PPG</p>
											<p class="text-light text-center" style="font-size: 11px;"><?php echo $teamlist[$player['team']]; ?></p>
											<p class="text-sm" style="font-size: 10px; margin: 5px 0 0 0;"><?php echo $player['games']; ?> G</p>
										</div>
									</div>
									<?php } ?>
								</div>
							</div>
						</div>
						<?php
					} else {
						// Single player
						$player = $bestbench[0];
						?>
						<div class="col-xs-24 col-sm-6">		
							<div class="panel">
								<div class="widget-header" style="height: 200px; overflow: hidden; position: relative;">
									
									<?php
									$playerimgobj = get_attachment_url_by_slug($player['pid']);
									$imgid =  attachment_url_to_postid( $playerimgobj );
									$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
									$playerimg = $image_attributes[0];
									?>
								
									<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover; object-position: center top;">
								</div>
								<div class="widget-body text-center">
									<h5><? echo $year ?> Best Bench Player</h5>
									<h4 class="mar-no text-center"><?php echo $player['first'].'<br>'.$player['last']; ?></h4>
									<p class="text-light text-center mar-top"><?php echo number_format($player['ppg'], 1); ?> PPG</p>
									<p class="text-light text-center"><?php echo $teamlist[$player['team']]; ?></p>
									<p class="text-sm"><?php echo $player['games']; ?> Games (<?php echo $player['points']; ?> pts)</p>
								</div>
							</div>
						</div>
						<?php
					}
				}
				?>
				
		</div>
		</div>
	
	</div>
</div>