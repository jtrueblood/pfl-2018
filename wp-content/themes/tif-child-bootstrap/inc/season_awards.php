<div class="panel">
	<div class="panel-body">
		<h4>Season Awards</h4>
		<div id="seasonawards">
		
		<?php 
		$getawards = get_season_award($year);
			
			foreach($getawards as $key => $item){
			   $awards[$item['award']] = $item;
			}
			
			asort($awards);
			
			foreach ($awards as $value){
			
				if ($value['award'] != 'Owner of the Year'){
			?>
					<div class="col-xs-24 col-sm-8 eq-box-sm">	
						<div class="panel">
							<div class="widget-header">
								
								<?php
								$playerimgobj = get_attachment_url_by_slug($value['pid']);
								$imgid =  attachment_url_to_postid( $playerimgobj );
								$image_attributes = wp_get_attachment_image_src($imgid, array( 400, 400 ));	
								$playerimg = $image_attributes[0];
								?>
								
								<img class="widget-bg img-responsive" src="<?php echo $playerimg;?>" alt="Image">
							</div>
							<div class="widget-body text-center">
								<h4 class="mar-no text-center"><?php echo $value['first'].'<br>'.$value['last']; ?></h4>
								<p class="text-light text-center mar-btm"><?php echo $teamlist[$value['team']]; ?></p>
								<div class="">
									<h5><?php echo $value['award']; ?></h5>
								</div>
								</p>
							</div>
						</div>
					</div>
						
						
						<?php 
				}
				
				if ($value['award'] == 'Owner of the Year'){
			?>
					<div class="col-xs-24 col-sm-8 eq-box-sm ooty-season">	
						<div class="panel">
							<div class="widget-header">
																
							<img class="widget-bg img-responsive" src="/wp-content/uploads/<?php echo $value['team']; ?>-helmet-full-250x250.png" alt="Image">
							</div>
							<div class="widget-body text-center">
								<?php $ownername = explode(' ', $value['owner']); ?>
								<h4 class="mar-no text-center"><?php echo $ownername[0].'<br>'.$ownername[1]; ?></h4>
								<p class="text-light text-center mar-btm"><?php echo $teamlist[$value['team']]; ?></p>
								<div class="">
									<h5><?php echo $value['award']; ?></h5>
								</div>
								</p>
							</div>
						</div>
					</div>
						
						
						<?php 
				}
				
			}
			
// 			printr($awards, 0);
			?>

			
		</div>
		
	</div>
</div>