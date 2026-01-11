<?php 

$teamplayoffs = get_team_postseason($teamid);
$plsumm = get_team_postseason_summary($teamid);
//$playoff_team_score_by_week = get_all_team_postseason_by_week($teamid);
$getteams = get_teams();
foreach ($getteams as $team){
	$teamlist[] = $team['int'];
}
	
	if(isset($teamplayoffs)){
		foreach($teamplayoffs as $key => $item){
		   $arr_team_playoff[$item['playerid']][] = $item;
		   $team = $teamid;
		   $year = $item['year'];
		   $week = $item['week'];
		   $vs = $item['versus'];
		   $score = get_playoff_points_by_team_year($year, $team, $week);
		   $vsscore = get_playoff_points_by_team_year($year, $vs, $week);
		   $result = $item['result'];
		   $diff = $score - $vsscore;
		   if($result == 1){
			   $win = 1;
			   $loss = 0;
		   } else {
			   $win = 0;
			   $loss = 1;
		   }
		   
		   $arr_team_year[$year.$week] = array(
			   'year' 		=> $year,
			   'week'		=> $week,
			   'team'		=> $team,
			   'score' 		=> $score,
			   'versus' 	=> $vs,
			   'vsscore' 	=> $vsscore,
			   'win' 		=> $win,
			   'loss' 		=> $loss,
			   'diff'		=> $diff
		   );
		}
		
		if(isset($arr_team_year)){
			foreach ($arr_team_year as $key => $value){
				$sort_arr_team_year[$value['versus']][] = $value;
			}
			
			// Aggregate by opponent, counting each unique game (year+week) only once
			foreach($sort_arr_team_year as $key => $value){
				// Use associative array keyed by year+week to ensure unique games
				$unique_games = array();
				foreach ($value as $k => $v){
					$game_key = $v['year'] . $v['week'];
					// Only store the first occurrence of each unique game
					if (!isset($unique_games[$game_key])) {
						$unique_games[$game_key] = $v;
					}
				}
				
				// Now aggregate the unique games
				$d = 0; $w = 0; $l = 0;
				foreach ($unique_games as $game) {
					$d += $game['diff'];
					$w += $game['win'];
					$l += $game['loss'];
				}
				
				$final_arr_team_year[$key] = array(
					'games' => $w + $l,	
					'wins' => $w,
					'loss' => $l,
					'diff' => $d
				);
			}
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
		
		if($plsumm['total_points'] > 1){
		echo '<div class="awards-team">';
			echo '<div class="col-xs-24 col-sm-8">';
					echo '<h4>Player Points <small>Top 15</small></h4>';
					
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
							if ($i == 15){
								break;
							}				
						}
						
						
					echo '</table>';
					
			echo '</div>';
		echo '</div>';
		?>

		<div class="playoff-summary-team">
			<div class="col-xs-24 col-sm-8">
				
				<?php 
					$getweekhigh = substr($plsumm['high']['highweek'], -2);
					if($getweekhigh == 15){
						$theweekhigh = 'Playoffs';
					} else {
						$theweekhigh = 'Posse Bowl';
					}
					$getyearhigh = substr($plsumm['high']['highweek'], 0, 4);
					$getweeklow = substr($plsumm['low']['lowweek'], -2);
					if($getweeklow == 15){
						$theweeklow = 'Playoffs';
					} else {
						$theweeklow = 'Posse Bowl';
					}
					$getyearlow = substr($plsumm['low']['lowweek'], 0, 4);
				?>
				
				<h4>Record</h4>
				<h2><?php echo $plsumm['total_wins'].'-'.$plsumm['total_loss'];?></h2>
				<div class="spacer"></div>
				
				<h4>Points</h4>
				<h2><?php echo number_format($plsumm['total_points'], 0);?></h2>
				<div class="spacer"></div>
				
				<h4>Win Percentage</h4>
				<h2><?php echo number_format($plsumm['winper'], 3);?></h2>
				<div class="spacer"></div>
				
				<h4>High Score</h4>
				<h2><?php echo $plsumm['high']['highpts'];?></h2>
				<small><?php echo $theweekhigh.' - '.$getyearhigh; ?></small>
				<div class="spacer"></div>
				
				<h4>Low Score</h4>
				
				<h2><?php echo $plsumm['low']['lowpts'];?></h2>
				<small><?php echo $theweeklow.' - '.$getyearlow; ?></small>
				
			</div>
		</div>
		
		<?php	
		echo '<div class="head-to-head-playoffs-team">';
			echo '<div class="col-xs-24 col-sm-8">';
					echo '<h4>Head to Head <small>Postseason</small></h4>';
					echo' <table id="" class="table table-hover stripe">
						<thead>
							<tr>
								<th class="text-left">Vs</th>
								<th class="text-center">Gms</th>
								<th class="text-center">Rec</th>
								<th class="text-center">Diff</th>
							</tr>
						</thead>';
					
					//printr($teamplayoffs, 0);
					
					foreach ($teamlist as $tea){
						if($tea != $teamid){							
							if(isset($final_arr_team_year[$tea])){
								echo '<tr>
									<td class="text-left">'.$tea.'</td>
									<td class="text-center">'.$final_arr_team_year[$tea]['games'].'</td>
									<td class="text-center">'.$final_arr_team_year[$tea]['wins'].'-'.$final_arr_team_year[$tea]['loss'].'</td>
									<td class="text-center">'.$final_arr_team_year[$tea]['diff'].'</td>
								</tr>';
								} else {
								echo '<tr>
									<td class="text-left">'.$tea.'</td>
									<td class="text-center">0</td>
									<td class="text-center">0-0</td>
									<td class="text-center">--</td>
								</tr>';	
							}
						}
					}
					
					echo '</table>';
					
				echo '</div>';
			echo '</div>';
			
			echo '<div class="col-xs-24 col-sm-8">';
				//printr($sort_arr_team_year, 0);
				//printr($final_arr_team_year, 0);
			echo '</div>';
		} else {
			echo 'Never Made The Playoffs';
		}	
	?>
		
	</div>
</div>
