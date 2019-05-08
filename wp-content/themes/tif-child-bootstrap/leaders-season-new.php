<?php
/*
 * Template Name: Leaders Season New
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
$yearis = date('Y');
	
$theyears = the_seasons();
$playersassoc = get_players_assoc ();
//$playerdata = set_allplayerdata_trans('1991SmitRB');
//$getplayer = get_allplayerdata_trans('1992KosaQB'); 

$playerids = just_player_ids_with_position();

//retrieve all leaders from wp_leaders database





$get_season_leaders = get_season_leaders($yearid);
//printr($get_season_leaders, 0);

foreach ($get_season_leaders as $id => $value){
	$position = substr($value['playerid'], -2);
	if ($position == 'QB'){
		$qb_leaders[] = $value;
	}
	if ($position == 'RB'){
		$rb_leaders[] = $value;
	}
	if ($position == 'WR'){
		$wr_leaders[] = $value;
	}
	if ($position == 'PK'){
		$pk_leaders[] = $value;
	}
}

foreach ($qb_leaders as $get){
	$qb_number_one[$get['playerid']] = $get['points'];
}
foreach ($rb_leaders as $get){
	$rb_number_one[$get['playerid']] = $get['points'];
}
foreach ($wr_leaders as $get){
	$wr_number_one[$get['playerid']] = $get['points'];
}
foreach ($pk_leaders as $get){
	$pk_number_one[$get['playerid']] = $get['points'];
}

// sort the leaders so they are in the right order
arsort($qb_number_one, SORT_NUMERIC);
arsort($rb_number_one, SORT_NUMERIC);
arsort($wr_number_one, SORT_NUMERIC);
arsort($pk_number_one, SORT_NUMERIC);

// displays the #1 leaders for each position by year.
$qb_one[key($qb_number_one)] = $qb_number_one[key($qb_number_one)];
$rb_one[key($rb_number_one)] = $rb_number_one[key($rb_number_one)];
$wr_one[key($wr_number_one)] = $wr_number_one[key($wr_number_one)];
$pk_one[key($pk_number_one)] = $pk_number_one[key($pk_number_one)];

//printr($wr_number_one, 0);

$qb_avg = array_sum($qb_number_one)/count($qb_number_one);
$rb_avg = array_sum($rb_number_one)/count($rb_number_one);
$wr_avg = array_sum($wr_number_one)/count($wr_number_one);
$pk_avg = array_sum($pk_number_one)/count($pk_number_one);

$season_pos_values = array(
	'QB' => array (
		'avg' => $qb_avg,
		'high' => reset($qb_number_one)
	),
	'RB' => array (
		'avg' => $rb_avg,
		'high' => reset($rb_number_one)
	),
	'WR' => array (
		'avg' => $wr_avg,
		'high' => reset($wr_number_one)
	),
	'PK' => array (
		'avg' => $pk_avg,
		'high' => reset($pk_number_one)
	),
);

//printr($season_pos_values, 0);

// the following 4 functions get the #1 player for this season and inserts it into a table to be stored and used for player pages.
function insert_wp_number_ones_qb(){
	global $qb_one;		
	global $yearid;
	global $wpdb;
	global $qb_avg;
	
	
	// if more then 1 player tied for first, manually enter the info in wp_number_ones table and make the id TEA0000b with the b, c, d etc. at end
	$id = 'qb'.$yearid.'a';
	
	//remove the row for the player if it exsists
	$delete = $wpdb->query("delete from wp_number_ones where id = '$id'");
	
	foreach ($qb_one as $key => $value){
		
	$teams = get_player_teams_season($key);	
	$inserted = $wpdb->insert(
		 'wp_number_ones',
	     array(
		    'year' => $yearid, 
	        'id' => $id,
	        'pos' => 'QB',
	        'playerid' => $key,
	        'points' => $value,
	        'avg' => $qb_avg,
	        'teams' => $teams[$yearid][0]
	     ),
		 array( 
			'%d','%s','%s','%s','%d','%f','%s'
		 )
		);
		}

		
	 return $inserted;
 
}
insert_wp_number_ones_qb();


function insert_wp_number_ones_rb(){
	global $rb_one;		
	global $yearid;
	global $wpdb;
	global $rb_avg;
	
	$id = 'rb'.$yearid.'a';
	
	//remove the row for the player if it exsists
	$delete = $wpdb->query("delete from wp_number_ones where id = '$id'");
	
	foreach ($rb_one as $key => $value){
		
	$teams = get_player_teams_season($key);	
	$inserted = $wpdb->insert(
		 'wp_number_ones',
	     array(
		    'year' => $yearid,
	        'id' => $id,
	        'pos' => 'RB',
	        'playerid' => $key,
	        'points' => $value,
	        'avg' => $rb_avg,
	        'teams' => $teams[$yearid][0]
	     ),
		 array( 
			'%d','%s','%s','%s','%d','%f','%s'
		 )
		);
		}

		
	 return $inserted;
 
}
insert_wp_number_ones_rb();


function insert_wp_number_ones_wr(){
	global $wr_one;		
	global $yearid;
	global $wpdb;
	global $wr_avg;
	
	$id = 'wr'.$yearid.'a';
	
	//remove the row for the player if it exsists
	$delete = $wpdb->query("delete from wp_number_ones where id = '$id'");
	
	foreach ($wr_one as $key => $value){
	
	$teams = get_player_teams_season($key);	
	$inserted = $wpdb->insert(
		 'wp_number_ones',
	     array(
		    'year' => $yearid,
	        'id' => $id,
	        'pos' => 'WR',
	        'playerid' => $key,
	        'points' => $value,
	        'avg' => $wr_avg,
	        'teams' => $teams[$yearid][0]
	     ),
		 array( 
			'%d','%s','%s','%s','%d','%f','%s'
		 )
		);
		}

		
	 return $inserted;
 
}
insert_wp_number_ones_wr();


function insert_wp_number_ones_pk(){
	global $pk_one;		
	global $yearid;
	global $wpdb;
	global $pk_avg;
	
	$id = 'pk'.$yearid.'a';
	
	//remove the row for the player if it exsists
	$delete = $wpdb->query("delete from wp_number_ones where id = '$id'");
	
	foreach ($pk_one as $key => $value){
	
	$teams = get_player_teams_season($key);	
	$inserted = $wpdb->insert(
		 'wp_number_ones',
	     array(
		    'year' => $yearid, 
	        'id' => $id,
	        'pos' => 'PK',
	        'playerid' => $key,
	        'points' => $value,
	        'avg' => $pk_avg,
	        'teams' => $teams[$yearid][0]
	     ),
		 array( 
			'%d','%s','%s','%s','%d','%f','%s'
		 )
		);
		}

		
	 return $inserted;
 
}
insert_wp_number_ones_pk();

if ($yearid == $yearis){
	$gamelimit = 1;
} else {
	$gamelimit = 7;
}

// start PVQ calc
foreach ($qb_leaders as $key => $val){
	$qb_pvq_build[$val['id']] = $val['points'];
	if($val['games'] > $gamelimit){
		$qb_ppg_build[$val['id']] = $val['points'] / $val['games'];
	}
}
$qb_tots = array_sum($qb_pvq_build);

//printr($qb_leaders, 0);

foreach ($rb_leaders as $key => $val){
	$rb_pvq_build[$val['id']] = $val['points'];
	if($val['games'] > $gamelimit){
		$rb_ppg_build[$val['id']] = $val['points'] / $val['games'];
	}
}
$rb_tots = array_sum($rb_pvq_build);

foreach ($wr_leaders as $key => $val){
	$wr_pvq_build[$val['id']] = $val['points'];
	if($val['games'] > $gamelimit){
		$wr_ppg_build[$val['id']] = $val['points'] / $val['games'];
	}
}
$wr_tots = array_sum($wr_pvq_build);

foreach ($pk_leaders as $key => $val){
	$pk_pvq_build[$val['id']] = $val['points'];
	if($val['games'] > $gamelimit){
		$pk_ppg_build[$val['id']] = $val['points'] / $val['games'];
	}
}
$pk_tots = array_sum($pk_pvq_build);

$all_tots = array(
	'QB' => $qb_tots,
	'RB' => $rb_tots,
	'WR' => $wr_tots,
	'PK' => $pk_tots
);
	
//$all_high  = array_sum($all_tots);	
$all_high = max($all_tots);

$qb_rat = $all_high / $qb_tots;
$rb_rat = $all_high / $rb_tots;
$wr_rat = $all_high / $wr_tots;
$pk_rat = $all_high / $pk_tots;

$all_rats = array(
	'QB' => $qb_rat,
	'RB' => $rb_rat,
	'WR' => $wr_rat,
	'PK' => $pk_rat
);

foreach($qb_pvq_build as $key => $val){
	$qb_mults[$key] = $val * $qb_rat;
}

foreach($rb_pvq_build as $key => $val){
	$rb_mults[$key] = $val * $rb_rat;
}

foreach($wr_pvq_build as $key => $val){
	$wr_mults[$key] = $val * $wr_rat;
}

foreach($pk_pvq_build as $key => $val){
	$pk_mults[$key] = $val * $pk_rat;
}

$merge_mults = array_merge($qb_mults, $rb_mults, $wr_mults, $pk_mults);
arsort($merge_mults);

$bestval = reset($merge_mults);

foreach ($merge_mults as $key => $val){
	$final_pvq[$key] = $val / $bestval;
}
// end PVQ calc

// Calc PPG -- get arrays of positions player in loops above (at least 7 games played)
$merge_ppgs = array_merge($qb_ppg_build, $rb_ppg_build, $wr_ppg_build, $pk_ppg_build);
arsort($merge_ppgs);

?>
<?php include_once('main-nav.php'); ?>
<div class="boxed">
			
			<!--CONTENT CONTAINER-->
		<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php the_title();?> - <?php echo $yearid; ?></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content add-to-top">
				
					
					
					<!-- Leaders By Position -->
					<div class="col-xs-24 col-sm-18 eq-box-sm">
							
							<?php 
							echo '<div class="row">';
									
								leadersbyseason($qb_leaders, $yearid, 'Quarterbacks');
								leadersbyseason($rb_leaders, $yearid, 'Runningbacks');
							
							echo '</div>';
							echo '<div class="row">';
							
									leadersbyseason($wr_leaders, $yearid, 'Wide Receivers');
									leadersbyseason($pk_leaders, $yearid, 'Kickers');	
							?>
							</div>
					</div>
					
					
					<div class="col-xs-24 col-sm-6">
						
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
							</div>
								
							</div>
								
							<!-- PVQ PANEL -->
							<div class="panel">
								<div class="panel-heading">
									<h2 class="panel-title">Player Value Quotient</h2>
									
								</div>
								<div class="panel-body">
									<p class="">PVQ is a custom PFL Stat that looks at a player's individual value where scoring is leveled regardless of position.</p>
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
													
													$rank = 1;
													foreach ($final_pvq as $key => $getpvq){
														
														$keyname = substr($key, 0, -4);
														$name = get_player_name($keyname);
														$pos = substr($keyname, -2);
														
														$pvqscore = number_format($getpvq, 3, '.', '');
														if ($rank == 1){
															echo '<tr class="top-scorer">';
														} else {
															echo '<tr>';
														}
														echo '<td>'.$rank.'.</td>';
														echo '<td><a href="/player/?id='.$name.'" class="btn-link">'.$name['first'].' '.$name['last'].'</a></td>';
														echo '<td>'.$pos.'</td>';
														echo '<td>'.$pvqscore.'</td>';
														echo '</tr>';
													
														if($rank == 10){
															break;
														}
														
														$rank++;
														
													}

													?>
											</tbody>
										</table>
									
									</div>
								
								</div>
		
							</div>
							
							<!-- PPG PANEL -->
							<div class="panel">
								<div class="panel-heading">
									<h2 class="panel-title">Points Per Game <small>-- Minimum <?php echo $gamelimit; ?> Games Played</small></h2>
									
								</div>
								<div class="panel-body">
									
									<div class="table-responsive">
										<table class="table table-striped">
											<thead>
												<tr>
													<th></th>
													<th>Player</th>
													<th>Pos</th>
													<th>PPG</th>
												</tr>
											</thead>
											<tbody>
												<?php
													
 		//											printr($merge_ppgs, 0);
													
													$rank = 1;
													foreach ($merge_ppgs as $key => $get){
														
														$keyname = substr($key, 0, -4);
														$name = get_player_name($keyname);
														$pos = substr($keyname, -2);
														
														$ppgscore = number_format(round($get, 1), 1);
														if ($rank == 1){
															echo '<tr class="top-scorer">';
														} else {
															echo '<tr>';
														}
														echo '<td>'.$rank.'.</td>';
														echo '<td><a href="/player/?id='.$name.'" class="btn-link">'.$name['first'].' '.$name['last'].'</a></td>';
														echo '<td>'.$pos.'</td>';
														echo '<td>'.$ppgscore.'</td>';
														echo '</tr>';
													
														if($rank == 10){
															break;
														}
														
														$rank++;
														
													}

													?>
											</tbody>
										</table>
									
									</div>
								
								</div>
		
							</div>

							
							
							
							
					</div>
					
					
					<!-- Leaders By All -->
					<div class="col-xs-24 col-sm-24 eq-box-sm">
							
					</div>
	
									

<?php session_destroy(); ?>
		
</div>

</div>

<?php include_once('aside.php'); ?>

<?php get_footer(); ?>