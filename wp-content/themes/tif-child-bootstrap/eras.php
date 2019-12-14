<?php
/*
 * Template Name: Era Page
 * Description: Page for displaying player eras by team
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
$players = get_players_assoc();
$champs = get_champions();
$thisteam = get_team_results('wp_team_'.$teamid);


//$player = get_player_data('2004BreeQB');
//$player = get_raw_player_data_team('2004BreeQB', $teamid);

foreach ($thisteam as $key => $value){
	$qb_length = strlen($value['qb1']);
	$rb_length = strlen($value['rb1']);
	$wr_length = strlen($value['wr1']);
	$pk_length = strlen($value['pk1']);
	
	if ($qb_length == 10){
		$qbs[$key] = $value['qb1'];	
	} else {
		$qbs[$key] = 'NONE';
	}
	if ($rb_length == 10){
		$rbs[$key] = $value['rb1'];	
	} else {
		$rbs[$key] = 'NONE';
	}	
	if ($wr_length == 10){	
		$wrs[$key] = $value['wr1'];	
	} else {
		$wrs[$key] = 'NONE';
	}
	if ($pk_length == 10){
		$pks[$key] = $value['pk1'];	
	} else {
		$pks[$key] = 'NONE';
	}					
}

function mostpop($array){
	$values = array_count_values($array);
	arsort($values);
	return $values;
}

$group_qb = mostpop($qbs);
$group_rb = mostpop($rbs);
$group_wr = mostpop($wrs);
$group_pk = mostpop($pks);

//printr($group_pk, 0);

?>
<style>
.era-player {
	width: 25%;
	background-color: grey;
	display: block;
	overflow: hidden;
	float: left;
	clear: both;
}
</style>

<!--CONTENT CONTAINER-->
<div class="boxed">
	

<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
	<!-- Championship banners -->

	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">
		<div class="row">
			<div class="col-xs-24 col-sm-18">
				<h2><?php echo $teamid; ?></h2>
			</div>
			<div class="col-xs-24 col-sm-6">
				<?php include_once('inc/eras_select.php');?>
			</div>
		</div>
		
		<div class="row">
		
		<?php 
			function the_eras($posarray, $year){
				$rowcheck = 5;	
				$storeval = 5;
				foreach ($posarray as $key => $val){
					$year = substr($key, 0, 4);
					$week = substr($key, 4);
					if($currentyear != $year){
						$pryear = $year;
					} else {
						$pryear = '----';
					}
					if ($current == $val){
						echo '<div class="">
							<div class="">'.$pryear.' / '.$week.' --- '.$i.'</div>
						</div>';
						$i++;
					} else {
						if($i == 0){
							echo '<div class="">'.$pryear.' / '.$week.' ----------------------'.$val.'</div>';
							$i = 0;
						} else {
							echo '<div class="">'.$pryear.' / '.$week.' ----SUB---- '.$val.'</div>';
							$i = 0;
						} 
						
						
					}
					
					$current = $val;
					$currentyear = $year;
				}
			}	
		?>


		<div class="col-xs-24 col-sm-6">
			<div class="panel eras-player">
				<div class="panel-heading">
					<h3 class="panel-title">Quarterbacks</h3>
				</div>
				<div class="panel-body">
					<?php 
						the_eras($qbs, $year); 
					?>
					
				</div>
			</div>
		</div>
		
		<div class="col-xs-24 col-sm-6">
			<div class="panel eras-player">
				<div class="panel-heading">
					<h3 class="panel-title">Runningbacks</h3>
				</div>
				<div class="panel-body">
					<?php 
						the_eras($rbs, $year); 
					?>
				</div>
			</div>
		</div>
		
		<div class="col-xs-24 col-sm-6">
			<div class="panel eras-player">
				<div class="panel-heading">
					<h3 class="panel-title">Receivers</h3>
				</div>
				<div class="panel-body">
					<?php 
						the_eras($wrs, $year); 
					?>
				</div>
			</div>
		</div>
		
		<div class="col-xs-24 col-sm-6">
			<div class="panel eras-player">
				<div class="panel-heading">
					<h3 class="panel-title">Kickers</h3>
				</div>
				<div class="panel-body">
					<?php 
						the_eras($pks, $year); 
					?>
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