<div class="panel">
	<div class="panel-heading">
    	<h3 class="panel-title">Win Streaks</h3>
	</div>
	<div class="panel-body text-center">
		<?php 
		$count = 0;
		$yearone = 1991;
		
		
		foreach ($thisteam as $key => $value){
			if($value['result'] > 0){
				$count++;
			} else {
				$count--;
			}
			$allresult[$value['id']] = $count;
			
			if($value['result'] > 0){
				$winstreakcount++;
				$winstreak[$value['id']] = $winstreakcount; 
				$losestreakcount = 0;
				$losestreak[$value['id']] = $losestreakcount; 
				
			} else {
				$winstreakcount = 0;
				$winstreak[$value['id']] = $winstreakcount;
				$losestreakcount++;
				$losestreak[$value['id']] = $losestreakcount; 
			}
			
		}
		
		//printr($storestreak, 0);
		echo '<h4>Longest Winning Streak = '.max($winstreak).'  |  Worst Losing Streak = '.max($losestreak).'</h4>';
		 
		?>
		
		<script type="text/javascript">
			jQuery(document).ready(function() {
				Highcharts.chart('streakchart', {
				title: {
				        text: ''
				    },
				    xAxis: {
				        categories: [<?php 
					        foreach ($allresult as $key => $value){
						        echo $key.',';
					        } 
					    ?>],
					    crosshair: true,
					    allowDecimals: false,
					    labels: {
				            align: 'right',
				            reserveSpace: true,
				            rotation: 270
				        },
				    },
				    yAxis: [{
				        title: {
				            text: 'Wins'
				        }
				    
				    }],
				    labels: {
				        items: [{
				            html: '',
				            style: {
				                top: '18px',
				                color: '#fff',
				            },
				            
				        }]
				    },

				    series: [{
				        type: 'column',
				        name: 'Games Over',
				        color: '#5fa2dd',
				        negativeColor: '#eaa642',
				        marker: {
							enabled: false
						},
				        data: [<?php 
					        foreach ($allresult as $key => $value){
						        echo $value.',';
					        } 
					    ?>]
				    
				    }]
				});	
			});	
			</script>
			
			<div class="panel hidden-xs">
				<div id="streakchart"></div> 
			</div>
		
	</div>
</div>