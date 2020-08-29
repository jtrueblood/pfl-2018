<?php
/*
 * Template Name: Draft Strategy
 * Description: Requires that you build all 5 positions as json files in 'draft-fantasy-pros.php'
 */


$getdate = $_GET['d'];
$position = 'qb';

if($getdate):
	$date = $getdate;
else:
	$date = date('Y-m-d');
endif;

$allteams = teamlist();
 
$playersassoc = get_players_assoc();
$i = 0;
foreach ($playersassoc as $key => $value){
	$playersid[] = $key;
}


$stylesheet_uri = get_stylesheet_directory_uri();

$acf_notes = get_field('player');
foreach ($acf_notes as $val)
	$player_acf[$val['player_name']] = array(
		'name' => $val['player_name'],
		'teamid' => $val['team_id'],
		'protected' => $val['protected'],
		'rookie' => $val['rookie'],
		'notes' => $val['notes'],
		'drafted' => $val['drafted']
	); 

$added_player = array();
$acf_add_player = get_field('additional_player');
foreach($acf_add_player as $value){
		
	$pos = $value['add_pos'];
	$name = $value['add_name'];
	$exname = explode('.', $name);
	$init = $exname[0].'.';
	$last = $exname[1];
	
	$csv = $value['add_data_csv'];
	$csvarr = explode(',', $csv);
	
	if($pos == 'QB'):
		$pos_data = array(
			'att' => $csvarr[0],
			'comp' => $csvarr[1],
			'pass_yrds' => $csvarr[2],
			'pass_tds' => $csvarr[3],
			'ints' => $csvarr[4],
			'rush_att' => $csvarr[5],	
			'rush_yds' => $csvarr[6],
			'rush_tds' => $csvarr[7]
		);
	endif;
	
	if($pos == 'RB'):
		$pos_data = array(
			'att' => $csvarr[0],
			'rush_yds' => $csvarr[1],
			'rush_tds' => $csvarr[2],
			'rec' => $csvarr[3],
			'rec_yds' => $csvarr[4],
			'rec_tds' => $csvarr[5]
		);
	endif;
	
	if($pos == 'WR'):
		$pos_data = array(
			'att' => $csvarr[0],
			'rush_yds' => $csvarr[4],
			'rush_tds' => $csvarr[5],
			'rec' => $csvarr[3],
			'rec_yds' => $csvarr[1],
			'rec_tds' => $csvarr[2]
		);
	endif;
			
	
	if($pos == 'PK'):
		$pos_data = array(	
			'att' => $csvarr[2],
			'fg' => $csvarr[0],
			'fga' => $csvarr[1],
			'xp' => $csvarr[2]
		);
	endif;		
	
	$added_player[$value['add_pos']][$name] = array(
		'rank' => $value[0],
		'first_init' => $init,
		'last_name' => $last,
		'team' => $value['add_nfl_team'], 
		'bye' => $value['add_bye'],
		'best' => $value['add_best'],
		'worst' => $value['add_worst'],
		'avg' => $value['add_avg'],
		'std_dev' => $value['add_std_dev'],
		'adp' => $value['add_adp'],
		'vs_adp' => '',
		'position_change' => '',
		'position_change_value' => '', 
		'data' => (object)$pos_data

	);
}

$qb_added =  (object)$added_player['QB'];
foreach ($qb_added as $key => $value){
    $qb_added->$key = (object)$value;
}

$rb_added =  (object)$added_player['RB'];
foreach ($rb_added as $key => $value){
    $rb_added->$key = (object)$value;
}

$wr_added =  (object)$added_player['WR'];
foreach ($wr_added as $key => $value){
    $wr_added->$key = (object)$value;
}

$pk_added =  (object)$added_player['PK'];
foreach ($pk_added as $key => $value){
    $pk_added->$key = (object)$value;
}



$destination_folder = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/draft-fantasypros';

//QB
$filename = 'qb-'.$date.'-projections';	
$file = file_get_contents("$destination_folder/$filename.json");
$qb_json_array = json_decode($file);
$json_array = (object) array_merge( 
	(array) $qb_json_array, (array) $qb_added);

//RB
$rb_filename = 'rb-'.$date.'-projections';	
$rb_file = file_get_contents("$destination_folder/$rb_filename.json");
$d_rb_json_array = json_decode($rb_file);
$rb_json_array = (object) array_merge( 
	(array) $d_rb_json_array, (array) $rb_added);

//WR
$wr_filename = 'wr-'.$date.'-projections';	
$wr_file = file_get_contents("$destination_folder/$wr_filename.json");
$d_wr_json_array = json_decode($wr_file);
$wr_json_array = (object) array_merge( 
	(array) $d_wr_json_array, (array) $wr_added);

//TE
$te_filename = 'te-'.$date.'-projections';	
$te_file = file_get_contents("$destination_folder/$te_filename.json");
$te_json_array = json_decode($te_file);

//PK
$k_filename = 'k-'.$date.'-projections';	
$k_file = file_get_contents("$destination_folder/$k_filename.json");
$k_json_array = json_decode($k_file);

//Drafted Players
$player_draft_check = array();
//

//printr($wr_json_array , 0);

get_header(); 
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
				<div id="page-content">
				
				<!-- THE ROW -->
				<div class="row">
					
					<div class="col-xs-12 col-sm-4 eq-box-sm">
						<div class="panel panel-bordered panel-light">
							<div class="panel-body">
								<p>Requires that you build all 5 positions as json files in ‘draft-fantasy-pros.php’</p>
								<hr>
								<h4>Build Links</h4>
								<p><a href="/fantasy-pros-draft-analysis/?pos=qb" target="_blank">Quarterbacks</a></p>
								<p><a href="/fantasy-pros-draft-analysis/?pos=rb" target="_blank">Runningbacks</a></p>
								<p><a href="/fantasy-pros-draft-analysis/?pos=wr" target="_blank">Wide Receivers</a></p>
								<p><a href="/fantasy-pros-draft-analysis/?pos=te" target="_blank">Tight Ends</a></p>
								<p><a href="/fantasy-pros-draft-analysis/?pos=k" target="_blank">Kickers</a></p>
								<hr>
								<p>Pass date at '?d=2020-08-11' format or date will default to the current date.</p>
								
							</div>
						</div>
					</div>
					
					
				
							
					<!-- GET BASIC PLAYER INFO -->
					<div class="col-xs-24 col-sm-20">
						
						<div class="panel panel-primary">	
						<!--Panel heading-->
						<div class="panel-heading">
							<div class="panel-control">
			
								<!--Nav tabs-->
								<ul class="nav nav-tabs">
									<li class="active"><a data-toggle="tab" href="#demo-tabs-box-build">Notes</a></li>
									<li><a data-toggle="tab" href="#demo-tabs-box-qb">Quarterbacks</a></li>
									<li><a data-toggle="tab" href="#demo-tabs-box-rb">Runningbacks</a></li>
									<li><a data-toggle="tab" href="#demo-tabs-box-wr">Receivers</a></li>
									<li><a data-toggle="tab" href="#demo-tabs-box-te">Tight Ends</a></li>
									<li><a data-toggle="tab" href="#demo-tabs-box-pk">Kickers</a></li>
									<li><a data-toggle="tab" href="#demo-tabs-box-teams">Teams</a></li>
								</ul>
			
							</div>

						</div>
						
						
<!-- START TAB CONTENT --><div class="tab-content">
				
							
						<!-- QUARTERBACKS -->
						<div id="demo-tabs-box-qb" class="panel widget tab-pane fade">
							<div class="widget-body text-center">
								
								<table class="qb-draft-table table table-hover table-vcenter stripe">
									<thead>
										<tr>
											<th class="min-width">PFL</th>
											<th class="min-width">PFR</th>
											<th class="text-left">Player</th>
											<th class="min-width">Notes</th>
											<th class="min-width">NFL</th>
											<th class="min-width">PFL</th>
											<th class="min-width">Bye</th>
											<th class="min-width">Best</th>
											<th class="min-width">Worst</th>
											<th class="min-width">Avg</th>
											<th class="min-width">ADP</th>
											<th class="min-width">ATT</th>
											<th class="min-width">Yards</th>
											<th class="min-width">TDs</th>
											<th class="min-width">INT</th>
											<th class="min-width">Rush YD</th>
											<th class="min-width">Rush TD</th>
											<th class="text-center">PFL Proj</th>
										</tr>
									</thead>
									<tbody>

									<?php
										
										$a = 1;	
										foreach ($json_array as $data){
											$pname = $data->first_init.''.$data->last_name;
											$avg = round($data->avg);
											$adp = round($data->adp);
											$att = round($data->data->att);
											$pass_yards = $data->data->pass_yrds;
											$pass_yards = str_replace(',', '', $pass_yards);
											$pass_yards = round($pass_yards);
											$pass_td = round($data->data->pass_tds);
											$ints = round($data->data->ints);	
											$rush_yd = round($data->data->rush_yds);
											$rush_tds = round($data->data->rush_tds);
											$protect_team = $player_acf[$pname]['protected'];
											$protected_by_team = $player_acf[$pname]['teamid'];
											$rookie = $player_acf[$pname]['rookie'];
											$notes = $player_acf[$pname]['notes'];
											$drafted = $player_acf[$pname]['drafted'];
											
											$change = $data-> position_change;
											$change_val = $data-> position_change_value;
											
											// projection
											$p = ($pass_yards / 30) + (($rush_tds + $pass_td) * 2) + ($rush_yd / 10) - $ints;
											$r = round($p);
											$s = $r / 17;
											$projection = round($s * 14);
											
											$colorclass = '#ffffff';
											if($protect_team):
												$colorclass = '#bfe4ff';
											endif;
											
											if($drafted):
												$colorclass = '#d1e6c9';
												$drafted_by_team = $protected_by_team;
											endif;
											
											if($protect_team or $drafted_by_team):
												$team_projections[$protected_by_team]['QB'][$pname] = array(
													'projection' => $projection,
													'team' => $protected_by_team,
													'position' => 'QB'
												);
											endif;
	
										if($projection > 0):
											echo '<tr style="background-color:'.$colorclass.';">';
												echo '<td></td>'; 
												echo '<td>'.$a.'.</td>';
												echo '<td class="text-left"><span class="text-semibold">'.$pname.'</span>';
												echo '<td>';
													if($notes):
														echo '<a class="add-tooltip" data-toggle="tooltip" data-placement="right" href="#" data-original-title="'.$notes.'">&nbsp;<i class="fa fa-comment-o"></i></a>';
													endif;	
													if($rookie):
														echo '&nbsp;<span class="text-bold">R</span>';
													endif;
													if($change == 'risen'):
														$colorval = 'text-success';
													endif;
													if($change == 'fallen'):
														$colorval = 'text-danger';
													endif;
													if($change):	
														echo '&nbsp;<span class="text-bold '.$colorval.'">'.$change_val.'</span>';
													endif;
												echo '</td>';
												echo '<td>'.$data->team.'</td>';
												echo '<td>'.$protected_by_team.'</td>';
												echo '<td>'.$data->bye.'</td>';
												echo '<td>'.$data->best.'</td>';
												echo '<td>'.$data->worst.'</td>';
												echo '<td>'.$avg.'</td>';
												echo '<td>'.$adp.'</td>';
												echo '<td>'.$att.'</td>';
												echo '<td>'.$pass_yards.'</td>';
												echo '<td>'.$pass_td.'</td>';
												echo '<td>'.$ints.'</td>';
												echo '<td>'.$rush_yd.'</td>';
												echo '<td>'.$rush_tds.'</td>';
												echo '<td class="text-center"><span class="text-semibold">'.$projection.'</span></td>';
												//echo '<td>'.$rank_v_adp.'</td>';
											echo '</tr>';
											
											if(!$protected_by_team):
												$best_player_availible['QB'][$pname] = $projection;
											endif;
											
										$a++;
										endif;
											
										}
									?>
									</tbody>
								</table>
								
								</div>
							</div>
						
						<!-- RUNNINGBACKS -->
						<div id="demo-tabs-box-rb" class="panel widget tab-pane fade">
							<div class="widget-body text-center">
								
								<table class="rb-draft-table table table-hover table-vcenter stripe">
									<thead>
										<tr>
											<th class="min-width">PFL</th>
											<th class="min-width">PFR</th>
											<th class="text-left">Player</th>
											<th class="min-width">Notes</th>
											<th class="min-width">NFL</th>
											<th class="min-width">PFL</th>
											<th class="min-width">Bye</th>
											<th class="min-width">Best</th>
											<th class="min-width">Worst</th>
											<th class="min-width">Avg</th>
											<th class="min-width">ADP</th>
											<th class="min-width">ATT</th>
											<th class="min-width">Rush YD</th>
											<th class="min-width">Rush TD</th>
											<th class="min-width">Rec YD</th>
											<th class="min-width">Rec TD</th>
											<th class="text-center">PFL Proj</th>
										</tr>
									</thead>
									<tbody>

									<?php	
										//printr($rb_json_array, 0);
										
										$b = 1;	
										foreach ($rb_json_array as $data){
										
										$pname = $data->first_init.''.$data->last_name;
										$avg = round($data->avg);
										$adp = round($data->adp);
										$att = round($data->data->att);
										
										$rush_yd = $data->data->rush_yds;
										$rush_yd = str_replace(',', '', $rush_yd);
										$rush_yd = round($rush_yd);
										
										$change = $data-> position_change;
										$change_val = $data-> position_change_value;
										
										$rush_tds = round($data->data->rush_tds);
										$rec_yd = round($data->data->rec_yds);
										$rec_tds = round($data->data->rec_tds);
										$protect_team = $player_acf[$pname]['protected'];
										$protected_by_team = $player_acf[$pname]['teamid'];
										$rookie = $player_acf[$pname]['rookie'];
										$notes = $player_acf[$pname]['notes'];
										$drafted = $player_acf[$pname]['drafted'];
									
										
										// projection
										$p = (($rush_tds + $rec_td) * 2) + ($rush_yd / 10) + ($rec_yd / 10);
										$r = round($p);
										$s = $r / 17;
										$projection = round($s * 14);
										
										$colorclass = '#ffffff';
										if($protect_team):
											$colorclass = '#bfe4ff';
										endif;
										
										if($drafted):
											$colorclass = '#d1e6c9';
											$drafted_by_team = $protected_by_team;
										endif;
										
										if($protect_team or $drafted_by_team):
											$team_projections[$protected_by_team]['RB'][$pname] = array(
												'projection' => $projection,
												'team' => $protected_by_team,
												'position' => 'RB'
											);
										endif;
										
										if($projection > 0):
											echo '<tr style="background-color:'.$colorclass.';">';
												echo '<td></td>'; 
												echo '<td>'.$b.'.</td>';
												echo '<td class="text-left"><span class="text-semibold">'.$pname.'</span>';
												echo '<td>';
													if($notes):
														echo '<a class="add-tooltip" data-toggle="tooltip" data-placement="right" href="#" data-original-title="'.$notes.'">&nbsp;<i class="fa fa-comment-o"></i></a>';
													endif;	
													if($rookie):
														echo '&nbsp;<span class="text-bold">R</span>';
													endif;
													if($change == 'risen'):
														$colorval = 'text-success';
													endif;
													if($change == 'fallen'):
														$colorval = 'text-danger';
													endif;
													if($change):	
														echo '&nbsp;<span class="text-bold '.$colorval.'">'.$change_val.'</span>';
													endif;
												echo '</td>';
												echo '<td>'.$data->team.'</td>';
												echo '<td>'.$protected_by_team.'</td>';
												echo '<td>'.$data->bye.'</td>';
												echo '<td>'.$data->best.'</td>';
												echo '<td>'.$data->worst.'</td>';
												echo '<td>'.$avg.'</td>';
												echo '<td>'.$adp.'</td>';
												echo '<td>'.$att.'</td>';
												echo '<td>'.$rush_yd.'</td>';
												echo '<td>'.$rush_tds.'</td>';
												echo '<td>'.$rec_yd.'</td>';
												echo '<td>'.$rec_tds.'</td>';
												echo '<td class="text-center"><span class="text-semibold">'.$projection.'</span></td>';
											echo '</tr>';
											
											if(!$protected_by_team):
												$best_player_availible['RB'][$pname] = $projection;
											endif;
											
											$b++;
										endif;	
										}
									?>
									</tbody>
								</table>
								
								
							</div>
						</div>
						
						<!-- RECEIVERS -->
						<div id="demo-tabs-box-wr" class="panel widget tab-pane fade">
							<div class="widget-body text-center">
								
								<table class="wr-draft-table table table-hover table-vcenter stripe">
									<thead>
										<tr>
											<th class="min-width">PFL</th>
											<th class="min-width">PFR</th>
											<th class="text-left">Player</th>
											<th class="min-width">Notes</th>
											<th class="min-width">NFL</th>
											<th class="min-width">PFL</th>
											<th class="min-width">Bye</th>
											<th class="min-width">Best</th>
											<th class="min-width">Worst</th>
											<th class="min-width">Avg</th>
											<th class="min-width">ADP</th>
											<th class="min-width">Rec</th>
											<th class="min-width">Rush YD</th>
											<th class="min-width">Rush TD</th>
											<th class="min-width">Rec YD</th>
											<th class="min-width">Rec TD</th>
											<th class="text-center">PFL Proj</th>
										</tr>
									</thead>
									<tbody>

									<?php	
										$c = 1;	
										foreach ($wr_json_array as $data){
										
										$pname = $data->first_init.''.$data->last_name;
										$avg = round($data->avg);
										$adp = round($data->adp);
										$rec = round($data->data->rec);
										
										$rec_yd = $data->data->rec_yds;
										$rec_yd = str_replace(',', '', $rec_yd);
										$rec_yd = round($rec_yd);
										
										$rush_tds = round($data->data->rush_tds);
										$rush_yd = round($data->data->rush_yds);
										$rec_tds = round($data->data->rec_tds);
										$protect_team = $player_acf[$pname]['protected'];
										$protected_by_team = $player_acf[$pname]['teamid'];
										$rookie = $player_acf[$pname]['rookie'];
										$notes = $player_acf[$pname]['notes'];
										$drafted = $player_acf[$pname]['drafted'];
										
										$change = $data-> position_change;
										$change_val = $data-> position_change_value;
										
										// projection
										$p = (($rush_tds + $rec_td) * 2) + ($rush_yd / 10) + ($rec_yd / 10);
										$r = round($p);
										$s = $r / 17;
										$projection = round($s * 14);
										
										$colorclass = '#ffffff';
										if($protect_team or $drafted_by_team):
											$colorclass = '#bfe4ff';
										endif;
										
										if($drafted):
											$colorclass = '#d1e6c9';
											$drafted_by_team = $protected_by_team;
										endif;
										
										if($protect_team or $drafted_by_team):
											$team_projections[$protected_by_team]['WR'][$pname] = array(
												'projection' => $projection,
												'team' => $protected_by_team,
												'position' => 'WR'
											);
										endif;
										
										//if($rec_yd > 100):
										if($projection > 0):
											echo '<tr style="background-color:'.$colorclass.';">';
												echo '<td></td>'; 
												echo '<td>'.$c.'.</td>';
												echo '<td class="text-left"><span class="text-semibold">'.$pname.'</span>';
												echo '<td>';
													if($notes):
														echo '<a class="add-tooltip" data-toggle="tooltip" data-placement="right" href="#" data-original-title="'.$notes.'">&nbsp;<i class="fa fa-comment-o"></i></a>';
													endif;
													if($rookie):
														echo '&nbsp;<span class="text-bold">R</span>';
													endif;	
													if($change == 'risen'):
														$colorval = 'text-success';
													endif;
													if($change == 'fallen'):
														$colorval = 'text-danger';
													endif;
													if($change):	
														echo '&nbsp;<span class="text-bold '.$colorval.'">'.$change_val.'</span>';
													endif;
												echo '</td>';
												echo '<td>'.$data->team.'</td>';
												echo '<td>'.$protected_by_team.'</td>';
												echo '<td>'.$data->bye.'</td>';
												echo '<td>'.$data->best.'</td>';
												echo '<td>'.$data->worst.'</td>';
												echo '<td>'.$avg.'</td>';
												echo '<td>'.$adp.'</td>';
												echo '<td>'.$rec.'</td>';
												echo '<td>'.$rush_yd.'</td>';
												echo '<td>'.$rush_tds.'</td>';
												echo '<td>'.$rec_yd.'</td>';
												echo '<td>'.$rec_tds.'</td>';
												echo '<td class="text-center"><span class="text-semibold">'.$projection.'</span></td>';
											echo '</tr>';
											
											if(!$protected_by_team):
												$best_player_availible['WR'][$pname] = $projection;
											endif;
											
											$c++;
										endif;	
										}
									?>
									</tbody>
								</table>
								
								
								
							</div>
						</div>
						
						
						<!-- TIGHT ENDS -->
						<div id="demo-tabs-box-te" class="panel widget tab-pane fade">
							<div class="widget-body text-center">
								
								<table class="te-draft-table table table-hover table-vcenter stripe">
									<thead>
										<tr>
											<th class="min-width">PFL</th>
											<th class="min-width">PFR</th>
											<th class="text-left">Player</th>
											<th class="min-width">Notes</th>
											<th class="min-width">NFL</th>
											<th class="min-width">PFL</th>
											<th class="min-width">Bye</th>
											<th class="min-width">Best</th>
											<th class="min-width">Worst</th>
											<th class="min-width">Avg</th>
											<th class="min-width">ADP</th>
											<th class="min-width">Rec</th>
											<th class="min-width">Rush YD</th>
											<th class="min-width">Rush TD</th>
											<th class="min-width">Rec YD</th>
											<th class="min-width">Rec TD</th>
											<th class="text-center">PFL Proj</th>
										</tr>
									</thead>
									<tbody>

									<?php	
										$c = 1;	
										foreach ($te_json_array as $data){
										
										$pname = $data->first_init.''.$data->last_name;
										$avg = round($data->avg);
										$adp = round($data->adp);
										$rec = round($data->data->rec);
										
										$rec_yd = $data->data->rec_yds;
										$rec_yd = str_replace(',', '', $rec_yd);
										$rec_yd = round($rec_yd);
										
										$rush_tds = round($data->data->rush_tds);
										$rush_yd = round($data->data->rush_yds);
										$rec_tds = round($data->data->rec_tds);
										$protect_team = $player_acf[$pname]['protected'];
										$protected_by_team = $player_acf[$pname]['teamid'];
										$rookie = $player_acf[$pname]['rookie'];
										$notes = $player_acf[$pname]['notes'];
										$drafted = $player_acf[$pname]['drafted'];
										
										$change = $data-> position_change;
										$change_val = $data-> position_change_value;
										
										// projection
										$p = (($rush_tds + $rec_td) * 2) + ($rush_yd / 10) + ($rec_yd / 10);
										$r = round($p);
										$s = $r / 17;
										$projection = round($s * 14);
										
										$colorclass = '#ffffff';
										if($protect_team):
											$colorclass = '#bfe4ff';
										endif;
										
										if($drafted):
											$colorclass = '#d1e6c9';
											$drafted_by_team = $protected_by_team;
										endif;
										
										if($protect_team or $drafted_by_team):
											$team_projections[$protected_by_team]['TE'][$pname] = array(
												'projection' => $projection,
												'team' => $protected_by_team,
												'position' => 'TE'
											);
										endif;
										
										//if($rec_yd > 500):
										if($projection > 0):
											echo '<tr style="background-color:'.$colorclass.';">';
												echo '<td></td>'; 
												echo '<td>'.$c.'.</td>';
												echo '<td class="text-left"><span class="text-semibold">'.$pname.'</span>';
												echo '<td>';
													if($notes):
														echo '<a class="add-tooltip" data-toggle="tooltip" data-placement="right" href="#" data-original-title="'.$notes.'">&nbsp;<i class="fa fa-comment-o"></i></a>';
													endif;
													if($rookie):
														echo '&nbsp;<span class="text-bold">R</span>';
													endif;	
													if($change == 'risen'):
														$colorval = 'text-success';
													endif;
													if($change == 'fallen'):
														$colorval = 'text-danger';
													endif;
													if($change):	
														echo '&nbsp;<span class="text-bold '.$colorval.'">'.$change_val.'</span>';
													endif;
												echo '</td>';
												echo '<td>'.$data->team.'</td>';
												echo '<td>'.$protected_by_team.'</td>';
												echo '<td>'.$data->bye.'</td>';
												echo '<td>'.$data->best.'</td>';
												echo '<td>'.$data->worst.'</td>';
												echo '<td>'.$avg.'</td>';
												echo '<td>'.$adp.'</td>';
												echo '<td>'.$rec.'</td>';
												echo '<td>'.$rush_yd.'</td>';
												echo '<td>'.$rush_tds.'</td>';
												echo '<td>'.$rec_yd.'</td>';
												echo '<td>'.$rec_tds.'</td>';
												echo '<td class="text-center"><span class="text-semibold">'.$projection.'</span></td>';
											echo '</tr>';
											
											if(!$protected_by_team):
												$best_player_availible['TE'][$pname] = $projection;
											endif;
											
											$c++;
										endif;	
										}
									?>
									</tbody>
								</table>
								
								
								
							</div>
						</div>

						
						
						<!-- KICKERS -->
						<div id="demo-tabs-box-pk" class="panel widget tab-pane fade">
							<div class="widget-body text-center">
								
								<table class="pk-draft-table table table-hover table-vcenter stripe">
									<thead>
										<tr>
											<th class="min-width">PFL</th>
											<th class="min-width">PFR</th>
											<th class="text-left">Player</th>
											<th class="min-width">Notes</th>
											<th class="min-width">NFL</th>
											<th class="min-width">PFL</th>
											<th class="min-width">Bye</th>
											<th class="min-width">Best</th>
											<th class="min-width">Worst</th>
											<th class="min-width">Avg</th>
											<th class="min-width">ADP</th>
											<th class="min-width">FG</th>
											<th class="min-width">FGA</th>
											<th class="min-width">XP</th>
											<th class="text-center">PFL Proj</th>
										</tr>
									</thead>
									<tbody>

									<?php	
										$d = 1;	
										foreach ($k_json_array as $data){
											
											$pname = $data->first_init.''.$data->last_name;
											$avg = round($data->avg);
											$adp = round($data->adp);
											$rec = round($data->data->rec);
											
											$fg = $data->data->fg;
											$fg = str_replace(',', '', $fg);
											$fg = round($fg);
											
											$fga = $data->data->fga;
											$fga = str_replace(',', '', $fga);
											$fga = round($fga);
											
											$xp = $data->data->xp;
											$xp = str_replace(',', '', $xp);
											$xp = round($xp);
											
											$protect_team = $player_acf[$pname]['protected'];	
											$protected_by_team = $player_acf[$pname]['teamid'];
											$rookie = $player_acf[$pname]['rookie'];
											$notes = $player_acf[$pname]['notes'];
											$drafted = $player_acf[$pname]['drafted'];
											
											$change = $data-> position_change;
											$change_val = $data-> position_change_value;
																					
											// projection
											$p = ($fg * 2) + $xp;
											
											//$s = $p / 17;
											//$projection = $s * 14;
											$projection = round($p);
											
											$colorclass = '#ffffff';
											if($protect_team):
												$colorclass = '#bfe4ff';
											endif;
											
											if($drafted):
												$colorclass = '#d1e6c9';
												$drafted_by_team = $protected_by_team;
											endif;
											
											if($protect_team or $drafted_by_team):
												$team_projections[$protected_by_team]['PK'][$pname] = array(
													'projection' => $projection,
													'team' => $protected_by_team,
													'position' => 'PK'
												);
											endif;
											
											//if($xp > 25):
											if($projection > 0):
												echo '<tr style="background-color:'.$colorclass.';">';
													echo '<td></td>'; 
													echo '<td>'.$d.'.</td>';
													echo '<td class="text-left"><span class="text-semibold">'.$pname.'</span>';
												echo '<td>';
													if($notes):
														echo '<a class="add-tooltip" data-toggle="tooltip" data-placement="right" href="#" data-original-title="'.$notes.'">&nbsp;<i class="fa fa-comment-o"></i></a>';
													endif;	
													if($rookie):
														echo '&nbsp;<span class="text-bold">R</span>';
													endif;
													if($change == 'risen'):
														$colorval = 'text-success';
													endif;
													if($change == 'fallen'):
														$colorval = 'text-danger';
													endif;
													if($change):	
														echo '&nbsp;<span class="text-bold '.$colorval.'">'.$change_val.'</span>';
													endif;
												echo '</td>';
													echo '<td>'.$data->team.'</td>';
													echo '<td>'.$protected_by_team.'</td>';
													echo '<td>'.$data->bye.'</td>';
													echo '<td>'.$data->best.'</td>';
													echo '<td>'.$data->worst.'</td>';
													echo '<td>'.$avg.'</td>';
													echo '<td>'.$adp.'</td>';
													echo '<td>'.$fg.'</td>';
													echo '<td>'.$fga.'</td>';
													echo '<td>'.$xp.'</td>';
													echo '<td class="text-center"><span class="text-semibold">'.$projection.'</span></td>';
												echo '</tr>';
												
												if(!$protected_by_team):
													$best_player_availible['PK'][$pname] = $projection;
												endif;
												
												$d++;
											endif;
	
										}
									?>
									</tbody>
								</table>
								
								
							</div>
						</div>
						
						
						<!-- TEAMS SUMMARY -->
						<div id="demo-tabs-box-teams" class="panel widget tab-pane fade">
							<div class="widget-body text-center">
								<?php 
									include_once('inc/draft-team-projections.php');
								?>
							</div>
						</div>
						
						<!-- NOTES AREA -->
						<div id="demo-tabs-box-build" class="panel widget tab-pane fade in active">
							<div class="widget-body text-center">
								<?php 
									include_once('inc/draft-best-avail.php');
									
									while (have_posts()) : the_post();
										the_content();
									endwhile; wp_reset_query(); 

									printr($drafted_players, 0);
									//printr($rb_json_array , 0); 	
									//printr($json_array, 0);
									
								?>
							</div>
						</div>
						

<!-- END TAB CONTENT --></div>						
	
						</div>
					
					</div>
	
				</div>	
				
		</div>
		<?php include_once('main-nav.php'); ?>
	</div>
	
</div>

<?php 
/*
	$log_file = $destination_folder.'/file.log'; 
	error_log($report_message, 3, $log_file); 
*/
?>

		
<?php get_footer(); ?>