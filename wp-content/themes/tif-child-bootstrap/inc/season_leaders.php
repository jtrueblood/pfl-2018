<div class="panel">
	<div class="panel-heading">
		<h3 class="panel-title">Position Title Winners</h3>
	</div>
	
	<div class="panel-body">
		
		<div id="seasonawards">
		
		<?php 
		$getleaders = get_season_leaders($year);
		
		foreach($getleaders as $key => $item){
			$leaders[$item['position']][$item['playerid']] = $item;
			if( $item['games'] >= 8){
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
		
		<div class="row">
			
			
			
				<?php 		
				foreach ($yearone['QB'] as $key => $value){ ?>
				<div class="col-xs-24 col-sm-12 col-md-8">		
					<div class="panel">
						<div class="widget-header">
							
							<?php							
							$info = get_player_basic_info($key);
							$playerteam = get_player_teams_season($key);
							$teams = $playerteam[$year];
								
							$playerimgobj = get_attachment_url_by_slug($key);
							$imgid =  attachment_url_to_postid( $playerimgobj );
							$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
							$playerimg = $image_attributes[0];
							?>
						
							<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image">
						</div>
						<div class="widget-body text-center">
							<h5><? echo $year ?> Passing Title</h5>
							<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
							<p class="text-light text-center mar-top">Points: <?php echo $value['points'] ?></p>
							<?php $tags = implode(', ', $teams); 
								echo '<p class="text-light text-center">'.$tags.'</p>';
							?>
						</div>
					</div>
				</div>	
				<?php } ?>

				<?php 
				foreach ($yearone['RB'] as $key => $value){ ?>
				<div class="col-xs-24 col-sm-12 col-md-8">		
					<div class="panel">
						<div class="widget-header">
							
							<?php							
							$info = get_player_basic_info($key);
							$playerteam = get_player_teams_season($key);
							$teams = $playerteam[$year];
								
							$playerimgobj = get_attachment_url_by_slug($key);
							$imgid =  attachment_url_to_postid( $playerimgobj );
							$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
							$playerimg = $image_attributes[0];
							?>
						
							<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image">
						</div>
						<div class="widget-body text-center">
							<h5><? echo $year ?> Rushing Title</h5>
							<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
							<p class="text-light text-center mar-top">Points: <?php echo $value['points'] ?></p>
							<?php $tags = implode(', ', $teams); 
								echo '<p class="text-light text-center">'.$tags.'</p>';
							?>
						</div>
					</div>
				</div>	
				<?php } ?>
			
				<?php 
				foreach ($yearone['WR'] as $key => $value){ ?>
				<div class="col-xs-24 col-sm-12 col-md-8">		
					<div class="panel">
						<div class="widget-header">
							
							<?php							
							$info = get_player_basic_info($key);
							$playerteam = get_player_teams_season($key);
							$teams = $playerteam[$year];
								
							$playerimgobj = get_attachment_url_by_slug($key);
							$imgid =  attachment_url_to_postid( $playerimgobj );
							$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
							$playerimg = $image_attributes[0];
							?>
						
							<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image">
						</div>
						<div class="widget-body text-center">
							<h5><? echo $year ?> Receiving Title</h5>
							<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
							<p class="text-light text-center mar-top">Points: <?php echo $value['points'] ?></p>
							<?php $tags = implode(', ', $teams); 
								echo '<p class="text-light text-center">'.$tags.'</p>';
							?>
						</div>
					</div>
				</div>	
				<?php } ?>


				<?php 
				foreach ($yearone['PK'] as $key => $value){ ?>
				<div class="col-xs-24 col-sm-12 col-md-8">		
					<div class="panel">
						<div class="widget-header">
							
							<?php							
							$info = get_player_basic_info($key);
							$playerteam = get_player_teams_season($key);
							$teams = $playerteam[$year];
								
							$playerimgobj = get_attachment_url_by_slug($key);
							$imgid =  attachment_url_to_postid( $playerimgobj );
							$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
							$playerimg = $image_attributes[0];
							?>
						
							<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image">
						</div>
						<div class="widget-body text-center">
							<h5><? echo $year ?> Kicking Title</h5>
							<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
							<p class="text-light text-center mar-top">Points: <?php echo $value['points'] ?></p>
							<?php $tags = implode(', ', $teams); 
								echo '<p class="text-light text-center">'.$tags.'</p>';
							?>
						</div>
					</div>
				</div>	
				<?php } 
					
				?>
				
				<div class="col-xs-24 col-sm-12 col-md-8">		
					<div class="panel">
						<div class="widget-header">
							
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
							
							printr($pidppg, 0);
							?>
						
							<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image">
						</div>
						<div class="widget-body text-center">
							<h5><? echo $year ?> Points Per Game</h5>
							<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
							<p class="text-light text-center mar-top"><?php echo round($topppg, 1); ?> PPG</p>
							<?php //$tags = implode(', ', $teams); 
								//printr($teams, 0);
								foreach ($teams as $te){
									echo '<p class="text-light text-center">'.$te.'</p>';
								}
							?>
						</div>
					</div>
				</div>	

				

				<div class="col-xs-24 col-sm-12 col-md-8">		
					<div class="panel">
						<div class="widget-header">
							
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
						
							<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image">
						</div>
						<div class="widget-body text-center">
							<h5><? echo $year ?> PVQ Leader</h5>
							<h4 class="mar-no text-center"><?php echo $info[0]['first'].'<br>'.$info[0]['last']; ?></h4>
							<p class="text-light text-center mar-top">1.000 PVQ</p>
							<?php //$tags = implode(', ', $teams); 
								//printr($teams, 0);
								foreach ($teams as $te){
									echo '<p class="text-light text-center">'.$te.'</p>';
								}
							?>
						</div>
					</div>
				</div>	

				
		</div>
		</div>
	
	</div>
</div>