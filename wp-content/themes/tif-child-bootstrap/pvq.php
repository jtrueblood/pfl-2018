<?php
/*
 * Template Name: Player Value
 * Description: Calculation for Overall PVQ
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->


<?php 
	get_header(); 
	
	$theyears = the_seasons();
	
	$allstandings = get_all_standings();
	
	//printr($allstandings, 1);
?>

<div class="boxed add-to-top">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php the_title();?></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
				
						<?php 
						
						$byyear = get_allpvqs_year();
						printr($byyear, 0);
						
						$player = get_player_pvqs('2004BreeQB');
						printr($player, 0);
						
						$allpvq = get_allpvqs();
						printr($allpvq, 0);

						echo '<h3>Standings By Week for Results</h3>';
						
						//$weekstand = get_all_team_results_by_week(199111);
						
						//$test = get_team_results_by_week('PEP', 199101);
						
						$testweek = 199201;
						$year = substr($testweek, 0, 4);
						$weeks = the_weeks();	
						$weekformat = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14');
						
						
						
						printr($dweek, 0);

						?>
						
						<div class="row">
							<div class="col-xs-24 col-md-8">
														
								<div id="standingschart" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
							
							</div>
						</div>
						
				
				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->
			
			<?php include_once('main-nav.php'); ?>
			<?php include_once('aside.php'); ?>

		</div>
		
</div> 

		
</div>
</div>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/drilldown.js"></script>

<script>
	// Create the chart
Highcharts.chart('standingschart', {
  chart: {
    type: 'column'
  },
  title: {
    text: 'Playoff Chase'
  },
  subtitle: {
    text: 'Modified Standings Showing Race for the Playoffs'
  },
  xAxis: {	    
	crosshair: true,
	allowDecimals: false,
	title: {
      text: 'Teams'
    },
	labels: {
	    align: 'right',
	    reserveSpace: true,
	    rotation: 0
	    },
	},
  yAxis: {
    title: {
      text: 'Games'
    },
    allowDecimals: false
  },
  legend: {
    enabled: false
  },
  plotOptions: {
    series: {
      borderWidth: 0,
      dataLabels: {
        enabled: true,
        format: ''
      }
    }
  },

  tooltip: {
    headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
    pointFormat: '<span style="color:{point.color}">{point.name}</span><br/>'
  },

  series: [
    {
      name: "Teams",
      colorByPoint: true,
        data: [<?php 
	        foreach ($weekstand as $key => $value){
		        echo '{';
		        echo 'name: "'.$key.'",';
				echo 'y: 1,';
				echo 'color: "#5fa2dd"'; 
				echo '},';
	        } 
	    ?>],
    }
  ]
});
</script>


<?php get_footer(); ?>