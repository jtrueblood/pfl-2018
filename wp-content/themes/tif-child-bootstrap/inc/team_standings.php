<div class="panel season-standing">
	<div class="panel-heading">
    	<h3 class="panel-title">Standings &mdash; By Season</h3>
	</div>
	<div class="panel-body text-center by-season">
		
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="text-center">Year</th>
						<th class="text-center">Record</th>
						<th class="text-center">Points</th>
						<th class="text-center">Point Dif</th>
						<th class="text-center">Back</th>
						<th class="text-center">Div Record</th>
						<th class="text-center">Division</th>
						<th class="hidden-xs">Seed</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ($stand as $key => $value){
							$win = $value[0]['win'];
							$loss = $value[0]['loss'];
							$pts = $value[0]['pts'];
							$diff = $pts - $value[0]['ptsvs'];
							$gb = $value[0]['gb'];
							$divw = $value[0]['divwin'];
							$divloss = $value[0]['divloss'];
							$division = $value[0]['division'];
							$seed = $value[0]['seed'];
							
							if($pts > 0){
								$standtable .=  '<tr><td class="text-bold">'.$key.'</td>';
								
								if($win == $highwins){
									$standtable .=  '<td><span class="text-bold text-orange">'.$win.'</span> - '.$loss.'</td>';
								} else {
									$standtable .=  '<td>'.$win.' - '.$loss.'</td>';
								}
														
								if ($pts == $besthigh){
									$standtable .= '<td class="text-bold text-orange">* '.$pts.' *</td>';
								} else {
									$standtable .= '<td>'.$pts.'</td>';
								}
								
								$standtable .=  '<td>'.$diff.'</td>';
								$standtable .=  '<td>'.$gb.'</td>';
								
								if(($divw + $divloss) != 0){
									$standtable .=  '<td>'.$divw.' - '.$divloss.'</td>';
								} else {
									$standtable .=  '<td> - </td>';
								}
								
								if ($gb == 0 && ($seed == 1 OR $seed == 2)){
									$standtable .=  '<td class="text-bold text-orange">* '.$division.' *</td>';
								} else {
									$standtable .=  '<td>'.$division.'</td>';
								}
								
								if($seed != 0){
									$standtable .=  '<td>'.$seed.'</td></tr>';
								} else {
									$standtable .=  '<td> - </td></tr>';
								}
							}
						}
						echo $standtable;
					?>
				</tbody>
			</table>
		
		</div>
	</div>
</div>
