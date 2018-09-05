<div class="panel">
	<?php 
	if( have_rows('honored_player') ):
	    while ( have_rows('honored_player') ) : the_row();
	
	        $honteam = get_sub_field('team');
	        $playerid = get_sub_field('player_id');
	        //echo $playerid;
			
			if($honteam == $teamid){
				$honor[] = $playerid;
			}
			
	    endwhile;
	endif; 
	
	if(isset($honor)){
		foreach ($honor as $val){
			$get[] = get_player_basic_info($val);
		}
	}
	?>
	
	<div class="ring-of-honor">
	
		<?php 
			if(isset($get)){
				foreach ($get as $val){	
					echo '<div class="honored">
					<a class="btn-link text-semibold add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="'.$val[0]['first'].' '.$val[0]['last'].'" aria-describedby="tooltip906941"><div class="uniform">'.$val[0]['number'].'</div></a>
					</div>';
				}
			}
			?>
	
	</div>
</div>