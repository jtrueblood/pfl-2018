<?php  
//printr($arr_taward, 0);	

foreach ($careerstats_team as $get){
	$posget = $get['pid'];
	$pos = substr($posget, -2);
	if($pos == 'QB'){
		$qbs_pts[$get['pid']] = $get['points'];
	}
	if($pos == 'RB'){
		$rbs_pts[$get['pid']] = $get['points'];
	}
	if($pos == 'WR'){
		$wrs_pts[$get['pid']] = $get['points'];
	}
	if($pos == 'PK'){
		$pks_pts[$get['pid']] = $get['points'];
	}
	
	$all_games[$get['pid']] = $get['games'];
	
	$all_wins[$get['pid']] = $get['wins'];
	
	$for_ppg[$get['pid']] = array(
		'g' => $get['games'],
		'p' => $get['points'],
		'w' => $get['wins'],
		'l' => $get['losses']
	);
}

foreach($for_ppg as $key => $value){
	if(!empty($value['g'])){	
		// check 15 games or more for player
		if($value ['g'] > 15){
			$all_ppg[$key] = $value['p'] / $value ['g'];
			$all_win_per[$key] = $value['w'] / $value ['g'];
		}
	}
}

arsort($qbs_pts);
arsort($rbs_pts);
arsort($wrs_pts);
arsort($pks_pts);
arsort($all_wins);
arsort($all_games);
arsort($all_ppg);
arsort($all_win_per);

?>
<div class="panel">
	<div class="panel-heading">
    	<h3 class="panel-title">Team All-Time Leaders</h3>
	</div>
	<div class="panel-body text-center">
		
		<?php 
/*
			printr($all_win_per, 0);
			printr($all_wins, 0);
			printr($qbs_pts, 0);
			printr($rbs_pts, 0);
			printr($wrs_pts, 0);
			printr($pks_pts, 0);
			printr($all_ppg, 0);	
			printr($all_games, 0);
			
*/
		
		//quarterbacks points		
		if(isset($qbs_pts)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Quarterbacks</h4>';
					
					echo' <table id="" class="table table-hover stripe">
						<thead>
							<tr>
								<th class="text-left">Name</th>
								<th class="text-right">Points</th>
							</tr>
						</thead>';
						
						$i = 0;
						foreach ($qbs_pts as $key => $val){
							
							$name = get_player_name($key);
							
							echo '<tr>
								<td class="text-left">'.$name['first'].' '.$name['last'].'</td>
								<td class="text-right">'.number_format($val, 0).'</td>
							</tr>';
							$i++;
							if ($i == 8){
								break;
							}				
						}
						
						
						echo '</table>';
					
				echo '</div>';
			echo '</div>';		
		}
		
		// runningbacks points
		if(isset($rbs_pts)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Runningbacks</h4>';
					
					echo' <table id="" class="table table-hover stripe">
						<thead>
							<tr>
								<th class="text-left">Name</th>
								<th class="text-right">Points</th>
							</tr>
						</thead>';
						
						$i = 0;
						foreach ($rbs_pts as $key => $val){
							
							$name = get_player_name($key);
							
							echo '<tr>
								<td class="text-left">'.$name['first'].' '.$name['last'].'</td>
								<td class="text-right">'.number_format($val, 0).'</td>
							</tr>';
							$i++;
							if ($i == 8){
								break;
							}				
						}
						
						
						echo '</table>';
					
				echo '</div>';
			echo '</div>';		
		}


		// receiver points
		if(isset($wrs_pts)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Wide Receivers</h4>';
					
					echo' <table id="" class="table table-hover stripe">
						<thead>
							<tr>
								<th class="text-left">Name</th>
								<th class="text-right">Points</th>
							</tr>
						</thead>';
						
						$i = 0;
						foreach ($wrs_pts as $key => $val){
							
							$name = get_player_name($key);
							
							echo '<tr>
								<td class="text-left">'.$name['first'].' '.$name['last'].'</td>
								<td class="text-right">'.number_format($val, 0).'</td>
							</tr>';
							$i++;
							if ($i == 8){
								break;
							}				
						}
						
						
						echo '</table>';
					
				echo '</div>';
			echo '</div>';		
		}


		// kicker points
		if(isset($pks_pts)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Kickers</h4>';
					
					echo' <table id="" class="table table-hover stripe">
						<thead>
							<tr>
								<th class="text-left">Name</th>
								<th class="text-right">Points</th>
							</tr>
						</thead>';
						
						$i = 0;
						foreach ($pks_pts as $key => $val){
							
							$name = get_player_name($key);
							
							echo '<tr>
								<td class="text-left">'.$name['first'].' '.$name['last'].'</td>
								<td class="text-right">'.number_format($val, 0).'</td>
							</tr>';
							$i++;
							if ($i == 8){
								break;
							}				
						}
						
						
						echo '</table>';
					
				echo '</div>';
			echo '</div>';		
		}
		
		
		//games played
		if(isset($all_games)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Games Played</h4>';
					
					echo' <table id="" class="table table-hover stripe">
						<thead>
							<tr>
								<th class="text-left">Name</th>
								<th class="text-right">Games</th>
							</tr>
						</thead>';
						
						$i = 0;
						foreach ($all_games as $key => $val){
							
							$name = get_player_name($key);
							
							echo '<tr>
								<td class="text-left">'.$name['first'].' '.$name['last'].'</td>
								<td class="text-right">'.number_format($val, 0).'</td>
							</tr>';
							$i++;
							if ($i == 8){
								break;
							}				
						}
						
						
						echo '</table>';
					
				echo '</div>';
			echo '</div>';		
		}
		
		// points per game
		if(isset($all_ppg)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Points Per Game <small>(15 game min)</small></h4>';
					
					echo' <table id="" class="table table-hover stripe">
						<thead>
							<tr>
								<th class="text-left">Name</th>
								<th class="text-right">PPG</th>
							</tr>
						</thead>';
						
						$i = 0;
						foreach ($all_ppg as $key => $val){
							
							$name = get_player_name($key);
							
							echo '<tr>
								<td class="text-left">'.$name['first'].' '.$name['last'].'</td>
								<td class="text-right">'.number_format(round($val, 1), 1).'</td>
							</tr>';
							$i++;
							if ($i == 8){
								break;
							}				
						}
						
						
						echo '</table>';
					
				echo '</div>';
			echo '</div>';		
		}

		// total wins
		if(isset($all_wins)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Wins With Team</h4>';
					
					echo' <table id="" class="table table-hover stripe">
						<thead>
							<tr>
								<th class="text-left">Name</th>
								<th class="text-right">Wins</th>
							</tr>
						</thead>';
						
						$i = 0;
						foreach ($all_wins as $key => $val){
							
							$name = get_player_name($key);
							
							echo '<tr>
								<td class="text-left">'.$name['first'].' '.$name['last'].'</td>
								<td class="text-right">'.$val.'</td>
							</tr>';
							$i++;
							if ($i == 5){
								break;
							}				
						}
						
						
						echo '</table>';
					
				echo '</div>';
			echo '</div>';		
		}
		
		// win percentage
		if(isset($all_win_per)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Winning Percentage <small>(15 game min)</small></h4>';
					
					echo' <table id="" class="table table-hover stripe">
						<thead>
							<tr>
								<th class="text-left">Name</th>
								<th class="text-right">Win %</th>
							</tr>
						</thead>';
						
						$i = 0;
						foreach ($all_win_per as $key => $val){
							
							$name = get_player_name($key);
							
							echo '<tr>
								<td class="text-left">'.$name['first'].' '.$name['last'].'</td>
								<td class="text-right">'.number_format(round($val, 3), 3).'</td>
							</tr>';
							$i++;
							if ($i == 5){
								break;
							}				
						}
						
						
						echo '</table>';
					
				echo '</div>';
			echo '</div>';		
		}


		
		?>
		
	</div>
</div>

