<?php
/*
 * Template Name: Trades
 * Description: Page for displaying all of the Trades by Year
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->

<?php get_header(); 
	
	$theseasons = the_seasons();

?>



<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold">Trades</h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">	
						<div class="col-xs-24">
						
						<?php
							$trades = get_trades();
							
							$c = 0;
							foreach($trades as $value){
								
								$players1 = explode(',', $value['players1']);
								$picks1 = explode(',', $value['picks1']);
								$protections1 = explode(',', $value['protections1']);
								$players2 = explode(',', $value['players2']);
								$picks2 = explode(',', $value['picks2']);
								$protections2 = explode(',', $value['protections2']);
								
								$newtrades[$value['year'].$value['team1'].$value['team2'].$c] = array(
									'year' => $value['year'],
									'team1' => $value['team1'],
									'players1' => $players1,
									'picks1' => $picks1,
									'protections1' => $protections1,
									'team2' => $value['team2'],
									'players2' => $players2,
									'picks2' => $picks2,
									'protections2' => $protections2,
									'notes' => $value['notes'],
									'when' => $value['when']
								);
								$c++;
							}

							asort($newtrades);
 							//printr($newtrades, 0);
							
							$x = 0;			
							foreach ($newtrades as $key => $value){ 
							
							if ($x % 3 == 2) : 
								echo '<div class="row">';
							endif;
							?>
																								
								<div class="col-xs-8">		
									<div class="panel protections">
										<div class="panel-body">
											<h5><?php echo $value['year'].' / '.$value['when']; ?></h5>
											<div class="col-xs-12">	
											<p>
												<?php echo '<h4><span class="text-bold">'.$teamids[$value['team1']].'</span> get:</h4>';
													if(!empty($value['players1'][0])){
														foreach($value['players1'] as $player){
															$playername = get_player_name($player);
															echo $playername['first'].' '.$playername['last'].', '.$playername['pos'].'<br>';
														}
	
													}
													if(!empty($value['picks1'][0])){														
														foreach($value['picks1'] as $pick){
															format_draft_pick($pick).' ';
														}
													}
													if(!empty($value['protections1'][0])){
														foreach($value['protections1'] as $protection){
															echo $protection.' ';
														} 
													}
													?> 
											</p>
											</div>
											<div class="col-xs-12">	
											<p>
												<?php echo '<h4><span class="text-bold">'.$teamids[$value['team2']].'</span> get:</h4>';
													if(!empty($value['players2'][0])){
														foreach($value['players2'] as $player){
															$playername = get_player_name($player);
															echo $playername['first'].' '.$playername['last'].', '.$playername['pos'].'<br>';
															//echo $player;
														}
													}
													if(!empty($value['picks2'][0])){
														foreach($value['picks2'] as $pick){
															format_draft_pick($pick).' ';
														}
													}
													if(!empty($value['protections2'][0])){
														foreach($value['protections2'] as $protection){
															echo $protection.' ';
														} 
													}
													?>
											</p>
											</div>
										</div>
										<div class="panel-footer">
											<p><?php echo 'Note: '. $value['notes']; ?></p>
										</div>
									</div>
								</div>
							
								<?php 
								if ($x % 3 == 2) : 
									echo '</div>';
								endif;
								$x++;
							} ?>

					</div>					

				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->


		<?php include_once('main-nav.php'); ?>
		<?php include_once('aside.php'); ?>

		</div>
</div> 

<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>