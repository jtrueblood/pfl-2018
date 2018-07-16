<?php
/*
 * Template Name: Leaders
 * Description: Page for displaying league leaders for seasons and total
 */
 ?>

<?php get_header(); 

$year = date('Y');
$o = 1991;
while ($o < $year){
	$theyears[] = $o;
	$o++;
}
	
$playersassoc = get_players_assoc();
foreach ($playersassoc as $key => $value){
	$playersid[] = $key;
}

$leaders = get_allleaders();

foreach($leaders as $key => $item){
   $arr_position[$item['position']][$key] = $item;
}

ksort($arr_position, SORT_NUMERIC);

$qb_leaders = $arr_position['QB'];
$rb_leaders = $arr_position['RB'];
$wr_leaders = $arr_position['WR'];
$pk_leaders = $arr_position['PK'];

function sortByOrder($a, $b) {
    return $b['points'] - $a['points'];
}

usort($qb_leaders, 'sortByOrder');
usort($rb_leaders, 'sortByOrder');
usort($wr_leaders, 'sortByOrder');
usort($pk_leaders, 'sortByOrder');

// printr($arr_position, 0);

function get_leaders_page($array){	
	$playersassoc = get_players_assoc();
	foreach ($array as $key => $value)	{
		$rank = $key + 1;
		$fname = $playersassoc[$value['pid']][0];
		$lname = $playersassoc[$value['pid']][1];
		$points = $value['points'];
		$games = $value['games'];
		$ppg = number_format(($points / $games),1);
		
		
		echo '<tr>
			<td class="text-center">'.$rank.'</td>
			<td class="text-center"><img src="https://posse-football.dev/wp-content/themes/tif-child-bootstrap/img/players/'.$value['pid'].'.jpg" class="leaders-image"></td>
			<td>
			<a href="/player?id='.$value['pid'].'"><span class="text-semibold '.$isactive.'">'.$fname.' '.$lname.'</span></a>
			</td>
			<td class="text-center"><span class="text-semibold">'.number_format($points, 0).'</span></td>
			<td class="text-center"><span class="text-semibold">'.$games.'</span></td>
			<td class="text-center"><span class="text-semibold">'.$ppg.'</span></td>
			<td class="text-center"><span class="text-semibold">'.$value['seasons'].'</span></td>
		</tr>';
	}
}

function get_table_head(){
	echo '<tr>
		<th class="min-width">Rank</th>
		<th class="min-width"></th>
		<th>Name</th>
		<th class="text-center">Points</th>
		<th class="text-center">Games</th>
		<th class="text-center">PPG</th>
		<th class="text-center">Seasons</th>
	</tr>';
}
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
				

					<div class="col-xs-24 col-sm-12 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Quarterbacks</h3>
								</div>
								
								<div class="panel-body">
										<table id="quarterback" class="leader-table table table-hover table-vcenter stripe">
											<thead>
											
											<?php get_table_head();?>	
											
											</thead>
											<tbody>
											
											<?php get_leaders_page($qb_leaders); ?>
	
											</tbody>
										</table>
									</div>
							</div>
					</div>
					
				
					
					<div class="col-xs-24 col-sm-12 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Runningbacks</h3>
								</div>
								
								<div class="panel-body">
										<table id="runningback" class="leader-table table table-hover table-vcenter stripe">
											<thead>
												
											<?php get_table_head();?>	
											
											</thead>
											<tbody>
												
											<?php get_leaders_page($rb_leaders); ?>

											</tbody>
										</table>
									</div>
								
								
							</div>
					</div>
					
					
					<div class="col-xs-24 col-sm-12 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Wide Receivers</h3>
								</div>
								
								<div class="panel-body">
										<table id="rec" class="leader-table table table-hover table-vcenter stripe">
											<thead>
												
											<?php get_table_head();?>	
											
											</thead>
											<tbody>
												
											<?php get_leaders_page($wr_leaders); ?>

											</tbody>
										</table>
									</div>
								
								
							</div>
					</div>
					
					
					
					<div class="col-xs-24 col-sm-12 eq-box-sm">
							<div class="panel panel-bordered panel-light">
								<div class="panel-heading">
									<h3 class="panel-title">Kickers</h3>
								</div>
								
								<div class="panel-body">
										<table id="kick" class="leader-table table table-hover table-vcenter stripe">
											<thead>
												
											<?php get_table_head();?>	
											
											</thead>
											<tbody>
												
											<?php get_leaders_page($pk_leaders); ?>

											</tbody>
										</table>
									</div>
								
								
							</div>
					</div>
					
				
					
							
				</div>
				<!-- end of tab 1 content -->
				
				</div>

			</div>
			
		</div>
</div> 



</div>

<?php include_once('main-nav.php'); ?>		

</div>

<?php include_once('aside.php'); ?>
<?php get_footer(); ?>