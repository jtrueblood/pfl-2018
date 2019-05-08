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
		
		//printr($yearone, 0);
		
		?>
		
		<div class="row">
			
			
			
				<?php 		
				foreach ($yearone['QB'] as $key => $value){ ?>
				<div class="col-xs-24 col-sm-12 col-md-6">		
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
				<div class="col-xs-24 col-sm-12 col-md-6">		
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
				<div class="col-xs-24 col-sm-12 col-md-6">		
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
				<div class="col-xs-24 col-sm-12 col-md-6">		
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
				<?php } ?>
				
		</div>
		</div>
	
	</div>
</div>