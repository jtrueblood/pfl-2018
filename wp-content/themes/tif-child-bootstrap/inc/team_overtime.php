<?php  
//printr($careerstats_team, 1);	

$overtime = get_team_overtime($teamid);

?>

<div class="panel">

	
		

	<?php 
	$labels = array('Week', 'Win', 'Loss', 'EOT');	
	tablehead('Overtime Games', $labels);	
	$w = 0; 
	$l = 0;
	
	if(is_array($overtime)):
		foreach ($overtime as $key => $value){
				$year = substr($key, 0, 4);
				$week = substr($key, 4, 2);
				$winner = $value[1];
				$loser = $value[2];
				$eot = $value[12];
				$otprint .='<tr><td class="text-left">Week '.$week.', '.$year.'</td>';
				if ($teamid == $winner){
					$otprint .='<td class="min-width text-left text-bold">'.$winner.'</td>';
					$w++;
				} else {
					$otprint .='<td class="min-width text-left">'.$winner.'</td>';
				}
				if ($teamid == $loser){
					$otprint .='<td class="min-width text-left text-bold">'.$loser.'</td>';
					$l++;
				} else {
					$otprint .='<td class="min-width text-left">'.$loser.'</td>';
				}
				if( $eot == 1){
					$otprint .='<td class="min-width text-left">Double OT</td></tr>';
				} else {
					$otprint .='<td class="min-width text-left"></td></tr>';
				}
		}
	endif;
	
	echo $otprint;
	
	tablefoot('OT Record '.$w.' - '.$l);	
	?>	
	
		
<!-- 	<?php printr($overtime, 0); ?> -->
		

</div>

