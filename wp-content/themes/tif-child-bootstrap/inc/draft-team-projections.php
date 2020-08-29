<div class="panel">
	<div class="panel-body">
		<?php
			$remove = get_field('remove_player');
			foreach ($remove as $val){
				$remove_player[$val['remove_name']] = $val['remove_position'];
			}
			
			$h = 1;
			foreach ($team_projections as $key => $value){
				$sumpoints = array();
				
					if ($h % 4 == 0) {
						echo '<div class="row">';
					}
					
						echo '<div class="col-xs-24 col-sm-12 col-md-5 col-md-offset-1 draft-teams">';
							echo '<h4>'.$allteams[$key].'</h4>';
							foreach ($value as $pos => $va){
								echo '<h5>'.$pos.'</h5>';
								foreach ($va as $k => $v){
									if($v['projection'] > 50):
										if (array_key_exists($k, $remove_player)) {
											$remove_pos = $remove_player[$k]; 
										} 
										if ((array_key_exists($k, $remove_player)) && ($remove_pos == $pos)) {
											$removed_note[] = $k.', '.$pos;
										} else {
											$pts = $v['projection'];
											echo '<p>'.$k.' <span class="push-right">'.$pts.'</span>';	
											
											$sumpoints[] = $pts;  
										}
									endif;
								}
								echo '<hr>';
							$points_for_table[$key] = array_sum($sumpoints);
							
							}
							echo '<h4>Total: <span class="text-bold">'.array_sum($sumpoints).'</span></h4>';
						echo '</div>';
					
					if ($h % 4 == 0) {
						echo '</div>';
					}
					$h++;	
			
			}
			
			
			
			?>
	</div>
</div>

<div class="row">
	<div class="col-xs-24 col-sm-6">
		<div class="panel">
			<div class="panel-body">
				<?php
					arsort($points_for_table);
					
					$labels = array('Team', 'Pts');	
					tablehead('Total Projected Points', $labels);	
					
					foreach ($points_for_table as $key => $value){
							$psprint .='<tr class="text-bold"><td class="text-left">'.$allteams[$key].'</td>';
							$psprint .='<td class="text-left">'.$value.'</td></tr>';
					}
					
					echo $psprint;
					
					tablefoot('');	 
				
					//printr($points_for_table, 0);
				?>
			</div>
		</div>
	</div>


	
	
	<div class="col-xs-24 col-sm-6">
		<div class="panel">
			<div class="panel-body">
				<h5 class="text-left">Notes:</h5>
				<?php 
					foreach ($removed_note as $r){
						echo '<p class="text-left">'.$r.' was removed</p>';
					}
					//printr($removed_note, 0); 
					
				?>
			</div>
		</div>
	</div>
	
	<div class="col-xs-24 col-sm-6">
		<div class="panel">
			<div class="panel-body">
			</div>
		</div>
	</div>

</div>