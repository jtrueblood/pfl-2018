<?php  
//printr($careerstats_team, 1);	



foreach ($seasons as $value){
	$thewin = $stand[$value][0]['win'];
	
	if(isset($thewin)){
		$win[$value] = $thewin;
	} else {
		$win[$value] = 0;
	}
	
	$thepoints = $stand[$value][0]['pts'];
	$thediff[] = $thepoints - $stand[$value][0]['ptsvs'];
	

}

?>

<div class="panel">
	<div class="panel-heading">
    	<h3 class="panel-title">Plus / Minus vs. Opponents</h3>
	</div>
	<div class="panel-body text-center">
		
 		
		
		<script type="text/javascript">
			jQuery(document).ready(function() {
				Highcharts.chart('teamchart', {
				title: {
				        text: ''
				    },
				    xAxis: {
				        categories: [<?php 
					        foreach ($win as $key => $value){
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
				        max: 200,
				        min: -200,
				        title: {
				            text: 'Points'
				        }
				    
				    }],
				    labels: {
				        items: [{
				            html: '',
				            style: {
				                top: '18px',
				                color: '#5fa2dd',
				            },
				            
				        }]
				    },

				    series: [{
				        type: 'column',
				        name: 'Team Plus / Minus',
				        color: '#5fa2dd',
				        negativeColor: '#eaa642',
				        marker: {
							enabled: false
						},
				        data: [<?php 
					        foreach ($thediff as $key => $value){
						        echo $value.',';
					        } 
					    ?>]
				    
				    }]
				});	
			});	
			</script>
			
			<div class="panel hidden-xs">
				<div id="teamchart"></div> 
			</div>


		
	</div>
</div>

