<?php
/*
 * Template Name: Leaders Season
 * Description: Page for displaying league leaders for seasons and total
 */
 ?>

<!-- necessary cache fies are pulled in via the 'pointsleader' function in functions.php -->

<?php get_header(); 
//$geturl = $_SERVER['REQUEST_URI'];
//$geturl = the_permalink();
//echo $geturl;
//$yearid = 1998;
$yearid = $_GET['id'];
	
$theyears = the_seasons();
$playersassoc = get_players_assoc ();
//$playerdata = set_allplayerdata_trans('1991SmitRB');
$getplayer = get_allplayerdata_trans('1992KosaQB'); 
printr($getplayer, 0);



if ($yearid <= 2015){


// Points	
foreach ($theyears as $getyear){	
	get_cache('rankyears/yearleaders/QB'.$getyear, 0);	
	${'QB'.$getyear} = $_SESSION['rankyears/yearleaders/QB'.$getyear];
	get_cache('rankyears/yearleaders/RB'.$getyear, 0);	
	${'RB'.$getyear} = $_SESSION['rankyears/yearleaders/RB'.$getyear];
	get_cache('rankyears/yearleaders/WR'.$getyear, 0);	
	${'WR'.$getyear} = $_SESSION['rankyears/yearleaders/WR'.$getyear];
	get_cache('rankyears/yearleaders/PK'.$getyear, 0);	
	${'PK'.$getyear} = $_SESSION['rankyears/yearleaders/PK'.$getyear];
}	



// PVQ

get_cache('rankyears/pvq/pvq'.$yearid, 0);	
$getpvq = $_SESSION['rankyears/pvq/pvq'.$yearid];
$currentpvq = array_slice($getpvq, 0, 25);

?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
		<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php the_title();?></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content add-to-top">
				
					<div class="col-xs-24 col-sm-offset-18 col-sm-6">
						
						<div class="panel">
							<div class="panel-body">
							
							<!-- Default choosen -->
							<!--===================================================-->
							<div class="row">
								<div class="col-xs-24 col-sm-18">
									<select data-placeholder="Select Season..." class="chzn-select" style="width:100%;" tabindex="2" id="pickyear">
									<option value=""></option>
									<?php 
										foreach($theyears as $select_year){ 
										echo'<option value="'.$select_year.'">'.$select_year.'</option>';    
										}
									?>
									</select>
								</div>
								<div class="col-xs-24 col-sm-6">
									<button class="btn btn-warning" id="yearbtn">Submit</button>
								</div>
							</div>
							<!--===================================================-->
							
							</div>
						</div>
							
					</div>
					
					<!-- Leaders By Position -->
					<div class="col-xs-24 col-sm-24 eq-box-sm">
							
							<?php 
							    	echo '<h3 class="panel-title">'.$yearid.' Season Leaders</h3>';
									echo '<div class="row">';
									
									leadersbyseason(${'QB'.$yearid}, $yearid, 'Quarterbacks');
									leadersbyseason(${'RB'.$yearid}, $yearid, 'Runningbacks');
									leadersbyseason(${'WR'.$yearid}, $yearid, 'Wide Receivers');
									leadersbyseason(${'PK'.$yearid}, $yearid, 'Kickers');
							
									
							?>
							</div>
					</div>
					
					
					<!-- Leaders By All -->
					<div class="col-xs-24 col-sm-24 eq-box-sm">
							
							<?php 
							    	echo '<h3 class="panel-title">'.$yearid.' Overall Leaders</h3>';
									echo '<div class="row">';
									
									$theqbs = ${'QB'.$yearid};
									$therbs = ${'RB'.$yearid};
									$thewrs = ${'WR'.$yearid};
									$thepks = ${'PK'.$yearid};
									$combineall = array_merge($theqbs, $therbs, $thewrs, $thepks);
									arsort($combineall);
									
									foreach ($combineall as $key => $val){
										$newcombine[] = array($key, $val);
									}
									
									$r = 0;
									while ($r < 25){
										$thecombine[$r] = $newcombine[$r];
										$r++; 
									}
									
									?>
									<div class="col-xs-24 col-sm-12 col-md-6">
										<div class="panel">							
											<div class="panel-heading">
												<h2 class="panel-title">Overall Points</h2>
											</div>
											<div class="panel-body">
												<div class="table-responsive">
													<table class="table table-striped">
														<thead>
															<tr>
																<th></th><th>Player</th><th>Pos</th><th>Points</th>
															</tr>
														</thead>
														<tbody>
															<?php
																$rank = 1;
																foreach ($thecombine as $value){
																	$pid = $value[0]; 
																	$points = $value[1];
																	$first = $playersassoc[$pid][0];
																	$last = $playersassoc[$pid][1];
																	$position = $playersassoc[$pid][2];
																	if ($rank == 1){
																		echo '<tr class="top-scorer">';
																	} else {
																		echo '<tr>';
																	}
																	echo '<td>'.$rank.'.</td>';
																	echo '<td><a href="/player/?id='.$key.'" class="btn-link">'.$first.' '.$last.'</a></td>';
																	echo '<td>'.$position.'</td>';
																	echo '<td>'.$points.'</td>';
																	echo '</tr>';
																	$rank++;
																}	
																?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>

	
									<div class="col-xs-24 col-sm-12 col-md-6">
										<div class="panel">
											<div class="panel-heading">
												<h2 class="panel-title">Player Value Quotient</h2>
											</div>
											<div class="panel-body">
												<div class="table-responsive">
													<table class="table table-striped">
														<thead>
															<tr>
																<th></th>
																<th>Player</th>
																<th>Pos</th>
																<th>PVQ</th>
															</tr>
														</thead>
														<tbody>
															<?php
// 																printr($currentpvq, 0);
																$rank = 1;
																foreach ($currentpvq as $key => $getpvq){
																	$first = $playersassoc[$key][0];
																	$last = $playersassoc[$key][1];
																	$pos = $playersassoc[$key][2];
																	$pvqscore = number_format($getpvq, 1, '.', '');
																	if ($rank == 1){
																		echo '<tr class="top-scorer">';
																	} else {
																		echo '<tr>';
																	}
																	echo '<td>'.$rank.'.</td>';
																	echo '<td><a href="/player/?id='.$key.'" class="btn-link">'.$first.' '.$last.'</a></td>';
																	echo '<td>'.$pos.'</td>';
																	echo '<td>'.$pvqscore.'</td>';
																	echo '</tr>';
																	$rank++;
																}
																?>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
									
							
		

<?php include_once('main-nav.php'); ?>
		<?php include_once('aside.php'); ?>

<?php session_destroy(); ?>
		
</div>
</div>

<?php 
} else {

	echo $yearid;

}
?>

<?php get_footer(); ?>