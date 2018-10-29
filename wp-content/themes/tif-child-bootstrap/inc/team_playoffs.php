<?php $teamplayoffs = get_team_postseason($teamid);
	//printr($teamplayoffs, 0);
	
	if(isset($teamplayoffs)){
		foreach($teamplayoffs as $key => $item){
		   $arr_team_playoff[$item['playerid']][] = $item;
		}
		
		//printr($arr_team_playoff, 0);
		
	
		foreach ($arr_team_playoff as $key => $value){
			foreach($value as $val){
				$player_scores[$key][] = $val['score'];
			}
		}
		
		foreach($player_scores as $key => $add){
			$player_tots[$key] = array_sum($add);
		}
		
		arsort($player_tots);
	} else {
		$player_tots = array();
	}
	//printr($player_tots, 0);
	
?>

<div class="panel">
	<div class="panel-heading">
    	<h3 class="panel-title">Team Postseason</h3>
	</div>
	<div class="panel-body text-center">
		
	<?php 
		echo '<div class="awards-team">';
			echo '<div class="col-xs-24 col-sm-8">';
					echo '<h4>Player Points <small>Top 10</small></h4>';
					
					echo' <table id="" class="table table-hover stripe">
						<thead>
							<tr>
								<th class="text-left">Name</th>
								<th class="text-right">Points</th>
							</tr>
						</thead>';
						
						$i = 0;
						foreach ($player_tots as $key => $val){
							
							$name = get_player_name($key);
							
							echo '<tr>
								<td class="text-left">'.$name['first'].' '.$name['last'].'</td>
								<td class="text-right">'.$val.'</td>
							</tr>';
							$i++;
							if ($i == 10){
								break;
							}				
						}
						
						
						echo '</table>';
					
				echo '</div>';
			echo '</div>';
	?>
		
	</div>
</div>
