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
	
$theyears = the_seasons();
$playersassoc = get_players_assoc ();
//$playerdata = set_allplayerdata_trans('1991SmitRB');
//$getplayer = get_allplayerdata_trans('1992KosaQB'); 

$playerids = just_player_ids_with_position();

//retrieve all leaders from wp_leaders database



// Points	
function get_season_leaders($yearval){
	
	global $wpdb;
	$get_season_leaders = $wpdb->get_results("select * from wp_season_leaders where season = '$yearval'", ARRAY_N);
	
	foreach ($get_season_leaders as $revisequery){
		$season_leaders_all[$revisequery[0]] = array(
			'id' => $revisequery[0],
			'playerid' => $revisequery[1],
			'season' => $revisequery[2],
			'points' => $revisequery[3],
			'games' => $revisequery[4]
		);
	}

return $season_leaders_all;

}

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

$qb_one[key($qb_number_one)] = $qb_number_one[key($qb_number_one)];
$rb_one[key($rb_number_one)] = $rb_number_one[key($rb_number_one)];
$wr_one[key($wr_number_one)] = $wr_number_one[key($wr_number_one)];
$pk_one[key($pk_number_one)] = $pk_number_one[key($pk_number_one)];

//printr($pk_one, 0);

// the following 4 functions get the #1 player for this season and inserts it into a table to be stored and used for player pages.
function insert_wp_number_ones_qb(){
	global $qb_one;		
	global $yearid;
	global $wpdb;
	
	$id = 'qb'.$yearid;
	
	//remove the row for the player if it exsists
	$delete = $wpdb->query("delete from wp_number_ones where id = '$id'");
	
	foreach ($qb_one as $key => $value){
		
	$teams = get_player_teams_season($key);	
	$inserted = $wpdb->insert(
		 'wp_number_ones',
	     array(
	        'id' => $id,
	        'playerid' => $key,
	        'points' => $value,
	        'teams' => $teams[$yearid][0]
	     ),
		 array( 
			'%s','%s','%d','%s'
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
	
	$id = 'rb'.$yearid;
	
	//remove the row for the player if it exsists
	$delete = $wpdb->query("delete from wp_number_ones where id = '$id'");
	
	foreach ($rb_one as $key => $value){
		
	$teams = get_player_teams_season($key);	
	$inserted = $wpdb->insert(
		 'wp_number_ones',
	     array(
	        'id' => $id,
	        'playerid' => $key,
	        'points' => $value,
	        'teams' => $teams[$yearid][0]
	     ),
		 array( 
			'%s','%s','%d','%s'
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
	
	$id = 'wr'.$yearid;
	
	//remove the row for the player if it exsists
	$delete = $wpdb->query("delete from wp_number_ones where id = '$id'");
	
	foreach ($wr_one as $key => $value){
	
	$teams = get_player_teams_season($key);	
	$inserted = $wpdb->insert(
		 'wp_number_ones',
	     array(
	        'id' => $id,
	        'playerid' => $key,
	        'points' => $value,
	        'teams' => $teams[$yearid][0]
	     ),
		 array( 
			'%s','%s','%d','%s'
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
	
	$id = 'pk'.$yearid;
	
	//remove the row for the player if it exsists
	$delete = $wpdb->query("delete from wp_number_ones where id = '$id'");
	
	foreach ($pk_one as $key => $value){
	
	$teams = get_player_teams_season($key);	
	$inserted = $wpdb->insert(
		 'wp_number_ones',
	     array(
	        'id' => $id,
	        'playerid' => $key,
	        'points' => $value,
	        'teams' => $teams[$yearid][0]
	     ),
		 array( 
			'%s','%s','%d','%s'
		 )
		);
		}

		
	 return $inserted;
 
}
insert_wp_number_ones_pk();

?>

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
							<!--===================================================-->
							
							</div>
						</div>
							
					</div>
					
					
					<!-- Leaders By All -->
					<div class="col-xs-24 col-sm-24 eq-box-sm">
							
							<?php 
							    	// figure out this logic with new array
									
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
														 	TABLE HERE	
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
/*
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
*/
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


<?php get_footer(); ?>