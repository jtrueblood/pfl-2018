<?php
/*
 * Template Name: Fantasy Pros Draft
 * Description: Script to grab projections on fantasypros.com for Draft Analysis
 */

$position = $_GET['pos'];

//printr($yearsplayed, 1);

$stylesheet_uri = get_stylesheet_directory_uri();

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
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
						<div class="panel panel-bordered panel-light">
							<div class="panel-body">
								<?php while (have_posts()) : the_post(); ?>
								<p><?php the_content();?></p>
								<?php endwhile; wp_reset_query(); ?>
							</div>
						</div>
					</div>
					
					<div class="col-xs-12 col-sm-6 eq-box-sm">
					</div>					
				
							
					<!-- GET BASIC PLAYER INFO -->
					<div class="col-xs-24 col-sm-16 left-column">
						<div class="panel widget">
							<div class="widget-body text-center">
								<?php 
								
								//$position = 'qb';
									
								include_once('simplehtmldom/simple_html_dom.php');
								$html = file_get_html('https://www.fantasypros.com/nfl/rankings/'.$position.'-cheatsheets.php?show=stats-total');
								$htmldata = file_get_html('https://www.fantasypros.com/nfl/projections/'.$position.'.php');
				
								$table = $html->find('#ranking-table', 0);
								$tabledata = $htmldata->find('#data', 0);

								$pagename = $html->find('h1', 0);
								$subname = $html->find('h5', 0);
								
								echo $subname;
								
								$theData = array();

								// loop over rows
								foreach($table->find('tr') as $row) {
								
								    // initialize array to store the cell data from each row
								    $rowData = array();
								    foreach($row->find('td') as $cell) {
								
								        // push the cell's text to the array
								        $rowData[] = $cell->innertext;
								        $thenames[] = $cell->getAttribute ( 'fp-player-name' );
								    }
								
								    // push the row's data array to the 'big' array
								    $theData[] = $rowData;
								}
								printr($theData, 0);
								//echo $row;
								
								foreach($tabledata->find('tr') as $row) {
								    $rowData = array();
								    foreach($row->find('td') as $cell) {
								        $rowData[] = $cell->innertext;
								    }
								    $theTableData[] = $rowData;
								}
								
								$statsdata = array();
								
								$i = 0;
								
								foreach ($theTableData as $values){
									$fullname = explode(' ', $values[0]);
									$fn = substr($fullname[2] , strpos($fullname[2] , ">") + 1);;
									$ln = $fullname[3];
									$te = $fullname[4];
									$firstinit = substr($fn, 0, 1);
									$kc = clean($firstinit.$ln);
									$keyclean = substr($kc, 0, -1);
									
									if($position == 'qb'):									
										$statsdata[$keyclean] = array(
											'att' => $values[1],
											'comp' => $values[2],
											'pass_yrds' => $values[3],
											'pass_tds' => $values[4],
											'ints' => $values[5],
											'rush_att' => $values[6],	
											'rush_yds' => $values[7],
											'rush_tds' => $values[8]
										);
									endif;
									
									if($position == 'rb'):									
										$statsdata[$keyclean] = array(
											'att' => $values[1],
											'rush_yds' => $values[2],
											'rush_tds' => $values[3],
											'rec' => $values[4],
											'rec_yds' => $values[5],
											'rec_tds' => $values[6]
										);
									endif;
									
									
									if($position == 'wr' OR $position == 'te'):									
										$statsdata[$keyclean] = array(
											'att' => $values[1],
											'rush_yds' => $values[5],
											'rush_tds' => $values[6],
											'rec' => $values[1],
											'rec_yds' => $values[2],
											'rec_tds' => $values[3]
										);
									endif;
									
									
									if($position == 'k'):									
										$statsdata[$keyclean] = array(
											'att' => $values[3],
											'fg' => $values[1],
											'fga' => $values[2],
											'xp' => $values[3]
										);
									endif;
									
									$i++;
									
									if($i == 100){
										break;
									}
									
								}

								foreach($table->find('tr') as $row) {
								
								    // initialize array to store the cell data from each row
								    $rowData = array();
								    foreach($row->find('td') as $cell) {
								
								        // push the cell's text to the array
								        $rowData[] = $cell->innertext;
								    }
								
								    // push the row's data array to the 'big' array
								    $theData[] = $rowData;
								}
	

							
								foreach ($theData as $value){
									
									$getname = explode(' ', $value[2]);
									if (array_key_exists('24', $value)) {
										$init =  substr($getname[4], -2);
										$lt = clean($getname[6]);
										$last = substr($lt, 0, -5);
									} else {
										$init =  substr($getname[4], -2);
										$lt = clean($getname[5]);
										$last = substr($lt, 0, -5);
									}
									$theteam =  $getname[7];
									$tm =  substr($theteam , strpos($theteam , ">") + 1); 
									$tea = clean($tm); 
									$team = substr($tea, 0, -5);
									
									$cleankey = substr(clean($init.$lt), 0, -5);	
									$checkatt = $statsdata[$cleankey]['att'];
	
										
										$store[$cleankey] = array(
											
											'rank' => $value[0],
											'first_init' => $init,
											'last_name' => $last,
											'team' => $team,
											'comp_name' => $value[2], 
											'bye' => $value[3],
											'best' => $value[4],
											'worst' => $value[5],
											'avg' => $value[6],
											'std_dev' => $value[7],
											'adp' => $value[8],
											'vs_adp' => $value[9],
											'position_change' => $getname[30],
											'position_change_value' => $getname[31],
											'data' => $statsdata[$cleankey]
										);

								}
								
								
							    //printr($thenames, 0);
								
								$json_store = json_encode($store);
								
								$destination_folder = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/draft-fantasypros';
								$filename = $position.'-'.date('Y-m-d').'-projections';
								
								if($json_store):	
									file_put_contents("$destination_folder/$filename.json", $json_store);
									$report_message =  $filename.' -- Added to draft-fantasypros-- || ';
									echo $report_message;
								endif;

								?>
								</div>
					
							</div>
	
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