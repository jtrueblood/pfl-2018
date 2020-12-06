<?php

foreach ($teamlist as $key => $value){
	$teamstanding[$key] = get_standings_weekly_by_team($key, $year_sel, $week_sel);
}
	
//$teamstanding = get_standings_weekly_by_team('DST', $year_sel, $week_sel);
//printr($teamstanding, 0);

function get_week_stand_this_team($teamarray){
	if(is_array($teamarray)){
		foreach ($teamarray as $key => $value){
			$wins += $value['win'];
			$loss += $value['loss'];
			$games = $wins + $loss;
			$points += $value['points'];
			$ppg = $points / $games;
			$vspoints += $value['vspoints'];
			$plusmin = $points - $vspoints;
			$divwin += $value['divwin'];
			$divloss += $value['divloss'];
			
			$cumweek = array(	
				'team' => $value['teamid'],
				'div' => 'EGAD',
				'win' => $wins,
				'loss' => $loss,
				'per' => $wins / $games,
				'pts' => $points,
				'ppg' => $ppg,
				'ptsvs' => $vspoints,
				'plusmin' => $plusmin,
				'divwin' => $divwin,
				'divloss' => $divloss,
				'gmback' => ''
			);
		}
	}
	return $cumweek;
}

$standingarray = array();
foreach ($teamlist as $key => $value){
	$get = get_week_stand_this_team($teamstanding[$key]);
	if (isset($get)){
		$standingarray[$key] = $get;
	}
}


					
foreach($standingarray as $key => $item){
   $arr_resort[$item['div']][$key] = $item;
}

//printr($arr_resort, 0);

$pfl = $arr_resort['PFL'];
$egad = $arr_resort['EGAD'];
$dgas = $arr_resort['DGAS'];
$mgac = $arr_resort['MGAC'];

function sort_by_wins_points($array){
	uasort($array, function ($a, $b) {
	    return $b['win'] - $a['win'];
	});
	return $array;
}

if($pfl){
	$print_pfl = sort_by_wins_points($pfl);
	$arr_resort_new['PFL'] = $print_pfl; 
	//printr($print_pfl, 0);
}
if($egad){
	$print_egad = sort_by_wins_points($egad);
	$arr_resort_new['EGAD'] = $print_egad;
	//printr($print_egad, 0);
}
if($dgas){
	$print_dgas = sort_by_wins_points($dgas);
	$arr_resort_new['DGAS'] = $print_dgas;
	//printr($print_dgas, 0);
}
if($mgac){
	$print_mgac = sort_by_wins_points($mgac);
	$arr_resort_new['MGAC'] = $print_mgac;
	//printr($print_mgac, 0);
}

//printr($arr_resort, 0);

?>

<style>
.dataTables_filter, .dataTables_paginate {
	display: none;
}
.dataTables_wrapper {
	margin-bottom: 25px;
}
</style>


<div class="panel-body">

	<?php
	foreach ($arr_resort_new as $div => $thedivision){	
	?>
	<div class="table-responsive">
		<table class="table table-striped week-standings-table">
			<thead>
				<?php echo '<h4>'.$div.' Standings</h4>'; ?>
				<tr>
					<th>Team</th>
					<th class="text-center">Win</th>
					<th class="text-center">Loss</th>
					<th class="text-center">Win%</th>
					<th class="text-center">Pt</th>
					<th class="text-center">PPG</th>
					<th class="text-center">Pt Vs</th>
					<th class="text-center">+/-</th>
					<th class="text-center">Div W</th>
					<th class="text-center">Div L</th>
					<th class="text-center">GB</th>
				</tr>
			</thead>
			<tbody>
		
			<?php
			foreach ($thedivision as $key => $value){
				$allwins[$div][$key] = $value['win'];
			}
					
			foreach ($thedivision as $key => $value){
							
				$topwin = max($allwins[$value['div']]);
				$gb = $topwin - $value['win'];
				if ($gb == 0){
					$prgp = '-';
				} else {
					$prgp = $gb;
				}
				
				echo '<tr>';
				
					echo '<td class="text-bold">'.$teamlist[$value['team']].'</td>';
					echo '<td class="text-center">'.$value['win'].'</td>';
					echo '<td class="text-center">'.$value['loss'].'</td>';
					echo '<td class="text-center">'.number_format($value['per'], 3).'</td>';
					echo '<td class="text-center">'.$value['pts'].'</td>';
					echo '<td class="text-center">'.number_format($value['ppg'], 1).'</td>';
					echo '<td class="text-center">'.$value['ptsvs'].'</td>';
					echo '<td class="text-center">'.$value['plusmin'].'</td>';
					echo '<td class="text-center">'.$value['divwin'].'</td>';
					echo '<td class="text-center">'.$value['divloss'].'</td>';
					echo '<td class="text-center">'.$prgp.'</td>';
				
				echo '</tr>';
				
				}
				
			}
				
			?>
			</tbody>
		</table>
	</div>

</div>