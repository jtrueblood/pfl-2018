<?php

foreach($standing as $key => $item){
   $arr_resort[$item['division']][$key] = $item;
}

?>
<div class="panel">

<div class="panel-body">

<?php
foreach ($arr_resort as $div => $thedivision){	
?>
	<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<?php echo '<h4>'.$div.' Standings</h4>'; ?>
					<tr>
						<th>Team</th>
						<th class="text-center">W</th>
						<th class="text-center">L</th>
						<th class="text-center">%</th>
						<th class="text-center">Pt</th>
						<th class="text-center">PPG</th>
	                    <th class="text-center">PtA</th>
	
						<th class="text-center">+/-</th>
	<!--
						<th class="text-center">Div W</th>
						<th class="text-center">Div L</th>
	-->
	
						<th class="text-center">GB</th>
					</tr>
				</thead>
				<tbody>
	
	<?php
	
			
		foreach ($thedivision as $key => $value){
		$id = $value['id'];
		$seed = $value['seed'];
		$division = $value['division'];
		$teamid = $value['teamid'];
		$teamname = strtoupper($value['teamname']);
		$win = $value['win'];
		$loss = $value['loss'];
		$gb = $value['gb'];
		$pts = $value['pts'];
		$ptsvs = $value['ptsvs'];
		$divwin = $value['divwin'];
		$divloss = $value['divloss'];
		
		if($gb == 0){
			$gb = '-';
		}
		
		if($seed > 0){
			$playoffs = '('.$seed.') ';
		} else {
			$playoffs = '';
		}
		
		$winper = number_format($win / 14 , 3);
		$ppg = number_format($pts / 14 , 1);
		$diff = $pts - $ptsvs;
		
		echo '<tr>';
		echo '<td class="text-bold">'.$playoffs.$teamname.'</td>';
		echo '<td class="text-center">'.$win.'</td>';
		echo '<td class="text-center">'.$loss.'</td>';
		echo '<td class="text-center">'.$winper.'</td>';
		echo '<td class="text-center">'.$pts.'</td>';
		echo '<td class="text-center">'.$ppg.'</td>';
	    echo '<td class="text-center">'.$ptsvs.'</td>';
		echo '<td class="text-center">'.$diff.'</td>';
	/*
		echo '<td class="text-center">'.$divwin.'</td>';
		echo '<td class="text-center">'.$divloss.'</td>';
	*/
	
		echo '<td class="text-center">'.$gb.'</td>';
		
		echo '</tr>';
		}
		
	}
	
	
	?>
		</tbody>
	</table>
	</div>
	<p>For full standings see 'Standings'</p>

</div>

</div>