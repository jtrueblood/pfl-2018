<?php
/*
 * Template Name: Create New Player
 * Description: 
 */
 ?>

<!-- In Dec of 2017 this template was switched over to pull data from mysql not from cached files.  -->
<!-- Make the required arrays and cached files availible on the page -->
<?php 
$season = date("Y");

$playerassoc = get_players_assoc();

	
?>

<?php get_header(); ?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
					
					
						
						<div class="panel panel-bordered panel-light">
							<div class="panel-heading">
								<h3 class="panel-title">Create New Player</h3>
							</div>
								<div class="panel-body">
									<div class="col-xs-24">
										<p><small>Check for player above and if they do not exist enter MFL ID below.</small></p>	
										<form action="" method="post">

											MFL ID: <input type="text" name="mflid" /><br>
											<br>
											<input type="submit" />
											
											
										</form>
										<p><small>Year and possibly league ID value needs to be updated for function get_mfl_player_details()</small></p>
									</div>
									
									<div class="col-xs-24">
										
										
										<?php 
											if ( isset( $_POST['mflid'] ) ){
												
												$form_mfl_id = 	$_POST['mflid'];
												//echo $form_mfl_id;
												$mfl_data = get_mfl_player_details($form_mfl_id);
												printr($mfl_data, 0);
												
												$name = $mfl_data['playerProfile']['name'];
												$xname = explode(',', $name);
												$xxname = explode(' ', $xname[1]);
												
												$draftyear = date('Y');
												
												$first = $xxname[1];
												$last = $xname[0];
												$position = $xxname[3];
												$justfour = substr($last, 0, 4);
												
												$themflid = $mfl_data['playerProfile']['player']['id']; 
												$weight = $mfl_data['playerProfile']['player']['weight'];
												$dob = $mfl_data['playerProfile']['player']['dob']; 
												$college = $mfl_data['college'];
												$pflid = $draftyear.$justfour.$position;
												$height = $mfl_data['playerProfile']['player']['height'];
																								
												$insertplayer = array(
													'p_id' => $pflid,
													'playerFirst' => ltrim($first),
													'playerLast' => $last,
													'position' => $position,
													'rookie' => $draftyear,
													'mflid' => $themflid,	
													'height' => $height,
													'weight' => $weight,
													'college' => $for_college,
													'birthdate' => $dob,
													'number' => '',
													'pfruri' => '',
													'pfrcurl' => '',
													'nickname' => ''
												);
												
												//var_dump($covheight);
												//printr($explode, 0);
	

												if(isset($insertplayer)){
													createnewplayer($insertplayer);
													printr($insertplayer, 0);
												}
											

										     } ?>
										     
									</div>
									
								
								</div>
							
						</div>
						<?php 
							echo '<h3><a href="'.$pfrlink.'" target="_blank">Pro Football Reference Link</a></h3>';
							printr($insertplayer, 0); ?>
<!--
						<?php
						//$mflid = '14803';
						$testplayer = get_mfl_player_details(14803);
						printr($testplayer, 0);						
						?>
-->

						
			
											
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