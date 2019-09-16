<!-- Timeline -->
<!--===================================================-->
<?php if( $enhanced == 1) { ?>
<div class="panel">
	<div class="panel-heading">
		<h3 class="panel-title">Career Timeline</h3>
	</div>
	<div class="panel-body">
	<div class="timeline">
	    
	    <?php 
		    $count = 0;
		    //printr($career_timeline, 0);
			foreach ($career_timeline as $key => $value){
			?>
			<!-- post the years -->
			<div class="timeline-entry">
				<div class="timeline-stat">
			        <div class="timeline-icon <?php echo 'val'.$count; ?>"></div>
			        <div class="timeline-time"><?php echo $key; ?></div> 
		        </div>
				
			</div>
			
			<?php
			if (isset($value['drafted'])){
			?>
			
			 <div class="timeline-entry">
		        <div class="timeline-label no-label">
		            <p class="protected-by">Drafted by <span class="text-bold"><?php echo $value['drafted']['team'].' Round '.$value['drafted']['round'].', Pick '.$value['drafted']['pick']; ?></span></p>
		        </div>
		    </div>
		    
		    <?php } 
			 
			// free agents
			if (empty($value['dnp'])){
				if(empty($value['drafted'])){
					if(empty($value['protected'])){
						include('inc/player_timeline_free_agent.php');
					}
				}
			}
			 
			// rookie season    
			if ($count == 0){
			echo '<div class="timeline-entry">
				<div class="timeline-label no-label">
		        	<p class="protected-by"><span class="text-bold">Rookie Season</p>
		        </div>
		    </div>';							    
		    } 
			 
			// did not play    
			if (!empty($value['dnp'])){
				include('inc/player_timeline_dnp.php');
			}   
			
			// career high 
			if(!empty($value['careerhigh'])){
				include('inc/player_timeline_max_points.php'); 
			} 
			
			// protected (order based on season or preseason trade)
			if ($value['traded']['when'] == ''){
				include('inc/player_timeline_protected.php');
			}
			
			// traded player									
		    if (isset($value['traded'])){
			    
				$tradedto = $teamids[$value['traded']['traded_to_team']];
				$tradedfrom = $teamids[$value['traded']['traded_from_team']];
				$when = $value['traded']['when'];
				$alongwith_players = $value['traded']['received_players'];
				$alongwith_picks = $value['traded']['received_picks'];
				$sent_players = $value['traded']['sent_players'];
				$sent_picks = $value['traded']['sent_picks'];
				$notes = $value['traded']['notes'];
				
				$a_picks = implode( ", ", $alongwith_picks);
				$s_picks = implode( ", ", $sent_picks);
				
				foreach ($alongwith_players as $playerf){
					$alongwith_format = array();
					$trim = ltrim($playerf);
					$alongwith_format[] = substr($players[$trim][0], 0, 1).'.'.$players[$trim][1];
					
				}
				foreach ($sent_players as $playern){
					$sent_players = array();
					$trim = ltrim($playern);
					$sent_format[] = substr($players[$trim][0], 0, 1).'.'.$players[$trim][1];	
				}
				
				//printr($sent_format, 0);
			?>
			
			 <div class="timeline-entry">
				 
		        <div class="timeline-label no-label">
		            <p class="protected-by"><span class="text-bold">
			            Traded to <?php echo $tradedto; ?></span> during the <?php echo $when; ?></p>
			        <p class="protected-by"><span class="text-bold"><?php echo $value['traded']['traded_to_team'];?></span> &mdash; Get <span class="text-bold"><?php echo implode( ", ", $alongwith_format).' '.format_draft_pick($a_picks); ?></span> </p> 
					<p class="protected-by"><span class="text-bold"><?php echo $value['traded']['traded_from_team'];?></span> &mdash; Get <span class="text-bold"><?php echo implode( ", ", $sent_format).' '.format_draft_pick($s_picks); ?>  </span>
			        </p>
			         <p class="protected-by"><?php echo $notes; ?> </p>
		        </div>
		    </div>
		    
		    <?php } 
		    
		    if ($value['traded']['when'] == 'Preseason'){
				include('inc/player_timeline_protected.php');
			}
		
			
				
			if(!empty($value['awards'])){ ?>
			<div class="timeline-entry">
		        <div class="timeline-stat">
		            <div class="timeline-icon bg-success">
			            <img class="" src="https:/wp-content/themes/tif-child-bootstrap/img/award-leaders.jpg" />
		            </div>
		            <div class="timeline-time"><?php $getaward['year']; ?></div>
		        </div>
		        <?php 
			        foreach($value['awards'] as $car_award){
		       ?>
		        <div class="timeline-label">
		            <?php echo '<span class="text-bold">'.$car_award.'</span>'; ?>
		        </div>
		        <?php
		        } ?>
	    	</div>
			<?php } 
			
			
			if(!empty($value['leader'])){ ?>
			<div class="timeline-entry">
		        <div class="timeline-stat">
		            <div class="timeline-icon bg-success">
			            <img class="" src="https:/wp-content/themes/tif-child-bootstrap/img/award-top-scorer.jpg" />
		            </div>
		            <div class="timeline-time"><?php $getaward['year']; ?></div>
		        </div>
		        <div class="timeline-label">
		            <?php echo '<span class="text-bold">'.$posaction.' Title</span> - '.$value['leader'].' Points'; ?>
		        </div>
	    	</div>
			<?php } 
			
			if($pvqplayer[$key] == 1){ ?>
			<div class="timeline-entry">
		        <div class="timeline-stat">
		            <div class="timeline-icon bg-success">
			            <img class="" src="https:/wp-content/themes/tif-child-bootstrap/img/award-top-pvq.jpg" />
		            </div>
		            <div class="timeline-time"><?php $pvqplayer[$key]; ?></div>
		        </div>
		        <div class="timeline-label">
		            <?php echo '<span class="text-bold">1.000 PVQ</span>'; ?>
		        </div>
	    	</div>
			<?php } 	
					    
			    
			if(!empty($value['pfltitle'])){ ?>
			<div class="timeline-entry">
		        <div class="timeline-stat">
		            <div class="timeline-icon bg-success">
			            <img class="" src="https:/wp-content/themes/tif-child-bootstrap/img/award-trophy.jpg" />
		            </div>
		            <div class="timeline-time"><?php $getaward['year']; ?></div>
		        </div>
		        <div class="timeline-label">
		            <?php echo '<span class="text-bold">PFL CHAMPION </span>'.$teamids[$justchamps[$key]];?>
		        </div>
	    	</div>
			<?php } 
	
	    		$count++;
			}  
			?>
	<!-- 						 HOF and Retired can be outside of foreach career_timeline loop -->
			
			<?php if($year_retired > 3){ ?>
			 <div class="timeline-entry" style="margin-top:50px;">
		        <div class="timeline-label no-label">
		            <span class="text-bold">Retired</span> from PFL
		        </div>
		    </div>
			<?php }
			
		    if($inhall == 1){ ?>
			
			<div class="timeline-entry">
		        <div class="timeline-stat">
		            <div class="timeline-icon bg-success">
			            <img class="" src="https:/wp-content/themes/tif-child-bootstrap/img/award-hall.jpg" />
		            </div>
		            <div class="timeline-time"><?php $getaward['year']; ?></div>
		        </div>
		        <div class="timeline-label">
		            <?php echo '<span class="text-bold"></span>'.$getaward['year'].' Hall of Fame Inductee'; ?>
		        </div>
	    	</div>
			<?php }
	
			?>
	
	 						    
	</div>
	</div>
	</div>
<?php } ?>
       
</div>
<!--===================================================-->
<!-- End Timeline -->