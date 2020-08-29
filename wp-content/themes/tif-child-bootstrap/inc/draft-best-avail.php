<div class="panel">
	<div class="panel-body">
<!-- 		<?php printr($best_player_availible, 0); ?> -->
		
		<div class="col-xs-24 col-sm-12 col-md-6">
			
			<?php 
				$o = 1;
				$bestqb = $best_player_availible['QB'];
				arsort($bestqb);
				$qbhigh = max($bestqb);
				
				$labels = array('Player', 'Proj Pts', '% Drop');	
					tablehead('Best QB', $labels);	
					
					foreach ($bestqb as $key => $value){
				
							$bestqb_print .='<tr style="font-weight:400;"><td class="text-left">'.$key.'</td>';
							$bestqb_print .='<td>'.$value.'</td>';
							if($value == $qbhigh):
								$bestqb_print .='<td>-</td></tr>';
							else: 
								$qbrespon = $value / $qbhigh;
								$bestqb_print .='<td>'.round($qbrespon, 3).'</td></tr>';
							endif;	
							
							if($o > 10):
								break;
							endif;
							
							$o++;
					}
					
					echo $bestqb_print;
					
					tablefoot('');	
				?>
		</div>
		
		<div class="col-xs-24 col-sm-12 col-md-6">
			
			<?php 
				$i = 1;
				$bestrb = $best_player_availible['RB'];
				arsort($bestrb);
				$rbhigh = max($bestrb);
				
				$labels = array('Player', 'Proj Pts', '% Drop');	
					tablehead('Best RB', $labels);	
					
					foreach ($bestrb as $key => $value){
				
							$bestrb_print .='<tr style="font-weight:400;"><td class="text-left">'.$key.'</td>';
							$bestrb_print .='<td class="text-left">'.$value.'</td>';
							
							if($value == $rbhigh):
								$bestrb_print .='<td>-</td></tr>';
							else: 
								$rbrespon = $value / $rbhigh;
								$bestrb_print .='<td>'.round($rbrespon, 3).'</td></tr>';
							endif;	
							
							if($i > 10):
								break;
							endif;
							
							$i++;
					}
					
					echo $bestrb_print;
					
					tablefoot('');	
				?>
		</div>
		
		
		<div class="col-xs-24 col-sm-12 col-md-6">
			
			<?php 
				$u = 1;
				$bestwr = $best_player_availible['WR'];
				$bestte = $best_player_availible['TE'];
				$merge_wrte = array_merge($bestte, $bestwr);
				arsort($merge_wrte);
				$wrhigh = max($merge_wrte);
				
				$labels = array('Player', 'Proj Pts', '% Drop');	
					tablehead('Best WR / TE', $labels);	
					
					foreach ($merge_wrte as $key => $value){
				
							$bestwr_print .='<tr style="font-weight:400;"><td class="text-left">'.$key.'</td>';
							$bestwr_print .='<td class="text-left">'.$value.'</td>';
							
							if($value == $wrhigh):
								$bestwr_print .='<td>-</td></tr>';
							else: 
								$wrrespon = $value / $wrhigh;
								$bestwr_print .='<td>'.round($wrrespon, 3).'</td></tr>';
							endif;	
							
							if($u > 10):
								break;
							endif;
							
							$u++;
					}
					
					echo $bestwr_print;
					
					tablefoot('');	
				?>
		</div>
		
		
		<div class="col-xs-24 col-sm-12 col-md-6">
			
			<?php 
				$y = 1;
				$bestpk = $best_player_availible['PK'];
				arsort($bestpk);
				$pkhigh = max($bestpk);
				
				$labels = array('Player', 'Proj Pts', '% Drop');	
					tablehead('Best PK', $labels);	
					
					foreach ($bestpk as $key => $value){
				
							$bestpk_print .='<tr style="font-weight:400;"><td class="text-left">'.$key.'</td>';
							$bestpk_print .='<td class="text-left">'.$value.'</td>';
							
							if($value == $pkhigh):
								$bestpk_print .='<td>-</td></tr>';
							else: 
								$pkrespon = $value / $pkhigh;
								$bestpk_print .='<td>'.round($pkrespon, 3).'</td></tr>';
							endif;	
							
							if($y > 10):
								break;
							endif;
							
							$y++;
					}
					
					echo $bestpk_print;
					
					tablefoot('');	
				?>
		</div>
		
	</div>
</div>
