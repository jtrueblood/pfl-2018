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
	$uni_info = get_uni_info_by_team($teamid);
	?>

	<div class="ring-of-honor">
		<?php

			if(isset($get)){
			    $j = 0;
				foreach ($get as $val){
				    $jerseyvalue = $uni_info[$val[0]['rookie']];
				    if($jerseyvalue < 1):
                        $jerseyvalue = 1;
				    endif;

				    $top = ( $j % 2 == 0 ? 'H' : 'R');
				    $getjersey = show_jersey_svg($teamid, $top, $jerseyvalue, $val[0]['number'] );
					echo '<div class="honored">
					     <div class="btn-link text-semibold add-tooltip" data-toggle="tooltip" data-placement="bottom" href="#" data-original-title="'.$val[0]['first'].' '.$val[0]['last'].'" aria-describedby="tooltip906941">
				            <img src="'.get_stylesheet_directory_uri().$getjersey.'" class="svg-jersey"/>
                         </div>
					</div>';
					$j++;
				}
			}
			?>
	
	</div>
</div>