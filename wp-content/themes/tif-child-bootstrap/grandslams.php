<?php
/*
 * Template Name: Grandslams
 * Description: Page to display all Slams!
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
	<?php 
$teamlist = array(
	'RBS' => 'Red Barons',
	'ETS' => 'Euro-Trashers',
	'PEP' => 'Peppers',
	'WRZ' => 'Space Warriorz',
	'CMN' => 'C-Men',
	'BUL' => 'Raging Bulls',
	'SNR' => 'Sixty Niners',
	'TSG' => 'Tsongas',
	'BST' => 'Booty Bustas',
	'MAX' => 'Mad Max',
	'PHR' => 'Paraphernalia',
	'SON' => 'Rising Son',
	'ATK' => 'Melmac Attack',
	'HAT' => 'Jimmys Hats',
	'DST' => 'Destruction'	
);

	 ?>

<?php get_header(); ?>


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
				
				<?php 
					$get_slams = get_grandslams();
					$get = array();
			
					foreach ($get_slams as $key => $value){
						$slam = get_team_results_by_week($value['teamid'], $value['weekid']);
						$get[] = $slam;
					}
					
					//$test = get_team_results_by_week('DST', 201711);
					//printr($slamboxes, 0);
					
					//$wilson = get_one_player_week('2012WilsQB', 201711);
					//printr($get[0], 0);
					echo '<div class="row">';
					
					foreach($get as $value){
						foreach ($value as $key => $val){
                            $result = $val['result'];
                            if($result >= 0){
                                $result = 'W';
                            } else {
                                $result = 'L';
                            }

							$qb = $val['qb1'];
							$rb = $val['rb1'];
							$wr = $val['wr1'];
							$pk = $val['pk1'];
							
							$qb_i = get_player_name($qb);
							$rb_i = get_player_name($rb);
							$wr_i = get_player_name($wr);
							$pk_i = get_player_name($pk);
							
							$qb_score = get_one_player_week($qb, $val['id']);
							$rb_score = get_one_player_week($rb, $val['id']);
							$wr_score = get_one_player_week($wr, $val['id']);
							$pk_score = get_one_player_week($pk, $val['id']);
							
						echo '
						<div class="col-xs-6">		
							<div class="panel protections">
								<div class="panel-body">
									<h4>Week '.$val['week'].', '.$val['season'].' - '.$teamlist[$val['team_int']].' - '.$result.'</h4>
									<p>'.$qb_i['first'].' '.$qb_i['last'].' - '.$qb_score.'<br>
									'.$rb_i['first'].' '.$rb_i['last'].' - '.$rb_score.'<br>
									'.$wr_i['first'].' '.$wr_i['last'].' - '.$wr_score.'<br>
									'.$pk_i['first'].' '.$pk_i['last'].' - '.$pk_score.'</p>
								</div>
								<div class="panel-footer">
									<p>Versus: <strong>'.$teamlist[$val['versus']].'</strong> at <strong>'.$val['stadium'].'</strong> ('.$val['home_away'].')<br>
									Differential: '.$val['result'].' | Total Points: '.$val['points'].'</p>	
								</div>
							</div>
						</div>';
						}
					}
					
					echo '</div>';
					?>
					
					<?php
						$countslams = count($get_slams); 
						
						foreach($get_slams as $key => $item){
						   $arr_team_slam[$item['teamid']][] = $item;
						}
						
						foreach ($arr_team_slam as $key => $value){
							$team_slam[$key] = count($value);
						}
						arsort($team_slam, SORT_NUMERIC);
						
						$getgames = the_games();
						$games = count($getgames);
						
		
						
						echo '<div class="row">
						<div class="col-xs-6">		
							<div class="panel protections">
								<div class="panel-body">
								<h4>Total Number of Slams: '.$countslams.'</h4>
								<h5>Total Slams by Team:</h5>';
								foreach($team_slam as $key => $val){
									echo $teamlist[$key].' - '.$val.'<br>';
								}
								
								echo'</div>
							</div>
							</div>
						</div>';
					$totalgamesplayed = get_total_games_played();
					//printr($totalgamesplayed, 0);
					
					
					?>
					
					
					
				</div>
				<!--End page content-->

			</div><!--END CONTENT CONTAINER-->


		<?php include_once('main-nav.php'); ?>
		<?php include_once('aside.php'); ?>

		</div>
</div> 

		
</div>
</div>


<?php get_footer(); ?>