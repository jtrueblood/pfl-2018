<?php
/*
 * Template Name: Team Page
 * Description: Page for displaying team history 
 */
 ?>


<?php get_header(); ?>

<!-- SET GLOBAL PLAYER VAR -->
<?php 
	
// $playerid = '2011SproRB';
$teamid = $_GET['id'];
$year = date("Y");
$team_all_ids = get_teams();
$seasons = the_seasons();
//printr($seasons, 0);

foreach ($seasons as $year){
	$stand[$year] = get_standings_by_team($year, $teamid); 
}


foreach ($stand as $key => $value){
	$diffo = $pts - $value[0]['ptsvs'];
	
	$highpts[] = $value[0]['pts'];
	$highdiff[] = $diffo;
	$wins[] = $value[0]['win'];
	$loss[] = $value[0]['loss'];
}

$besthigh = max($highpts);
$bestdiff = max($highdiff);
$bestwins = max($wins);
$totalwins = array_sum($wins);
$totalloss = array_sum($loss);
$gamesplayed = $totalwins + $totalloss;
$totalwinper = $totalwins / $gamesplayed;

// printr($totalwins, 1);
?>


<!--CONTENT CONTAINER-->
<div class="boxed add-to-top">

<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
	

	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">
		
		<div class="row">

		<!-- HELMET AND NAME -->
		<div class="col-xs-24 col-sm-6 left-column">
			
			<div class="panel">
				<div class="team-helmet widget-header">
					<img src="<?php echo get_stylesheet_directory_uri();?>/img/<?php echo $teamid;?>-bar.png" class="widget-bg img-responsive">

				</div>
				<div class="text-center">
					<?php 
						echo '<h2>'.$team_all_ids[$teamid]['team'].'</h2>';
						echo '<h5>'.$team_all_ids[$teamid]['owner'].'</h5>'; 
						echo '<h4><span class="text-thin">Record:</span> '.$totalwins.' - '.$totalloss.'</h4>'; 
						echo '<h4><span class="text-thin">Win %:</span> '.number_format($totalwinper, 3).'</h4>'; ?>
				</div>
				<div class="panel-footer">
				</div>
				
			</div>
			
		</div>
		
		
		<!-- STANDFINGS -->
		<div class="col-xs-24 col-sm-12">
			
			<div class="panel">
				<div class="panel-heading">
			    	<h3 class="panel-title">Standings &mdash; By Season</h3>
		    	</div>
				<div class="panel-body text-center">
					
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
												$standtable .=  '<td><span class="text-bold text-primary">'.$win.'</span> - '.$loss.'</td>';
											} else {
												$standtable .=  '<td>'.$win.' - '.$loss.'</td>';
											}
																	
											if ($pts == $besthigh){
												$standtable .= '<td class="text-bold text-primary">* '.$pts.' *</td>';
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
												$standtable .=  '<td class="text-bold text-primary">'.$division.'</td>';
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
			
		</div>
	
	
		<!-- SELECT DROPDOWN -->
		<div class="hidden-xs hidden-sm col-md-6">
				
				
				<div class="panel">
					<div class="panel-body">

						<!-- Default choosen -->
						<!--===================================================-->
						<div class="row">
						
						<div class="col-xs-24 col-sm-18">
							<select data-placeholder="Select A Team..." class="chzn-select" style="width:100%;" tabindex="2" id="teamDrop">
								<option value=""></option>
								
								<?php 	
								foreach ($team_all_ids as $key => $value){
									$printselect .= '<option value="/teams/?id='.$key.'">'.$value['team'].'</option>';
								}
								echo $printselect;
								?>
							</select>
							</div>
							<div class="col-xs-24 col-sm-6">
								<button class="btn btn-warning" id="teamSelect">Select</button>
							</div>
						</div>
						<!--===================================================-->

					</div>
				</div>
		
		</div>
		
		
		
		
		
		
		
	</div>
	<!--===================================================-->
	<!--End page content-->


</div>
<!--===================================================-->
<!--END CONTENT CONTAINER-->
<?php include_once('main-nav.php'); ?>		
</div>

			
</div>



<?php get_footer(); ?>