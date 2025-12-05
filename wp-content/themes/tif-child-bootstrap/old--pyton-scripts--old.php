<?php
/*
 * Template Name: Python Scripts
 * Description: Run Python scripts for sportsreference api
 */
 ?>
<?php get_header(); ?>

<div class="boxed add-to-top">
			
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
/*
						$shed = get_sportsref_shedule();
						//printr($shed, 0);
						
						$simple = get_sportsref_shedule_just_ids();
						//printr($simple, 0);
						
						
						//printr($players, 1);
						
						$players = get_players_name_pfrdata();
						
						function insert_pfruri($k){
							global $wpdb;
							global $players;
							$pfruri = $players[$k]['pro-uri'];
					 		$wpdb->update( 
							    'wp_players', 
							    array( 
							        'pfruri' => $pfruri,  // string
							    ), 
							    array( 
							    	'p_id' => $k
							    )
							);
							echo $k.'-'.$pfruri.'<br>';
						}
						
						foreach ($players as $key => $value){

							insert_pfruri($key);
								
						}
*/
?>
<?php
/*

						$errorcode = 301;
						
						$players = get_players_assoc();
						
						foreach ($players as $key => $value){
							$f = get_check_for_pfr_success($key);
							if($f == $errorcode):
								$playergetcheck[$key] = $value[10];
							endif;
						}
						$c = count($playergetcheck);
						echo '<h4>Code '.$errorcode.'s = '.$c.'</h4>';
						printr($playergetcheck,0);
*/

								
						?>
						<?php
						
						$lines = get_pfr_linescores_by_player('2017LutzPK');
						//$playerdata = get_player_weeks_played('2004BreeQB');
						printr($lines, 0);
						
						?>
				</div><!--End page content-->

			</div><!--END CONTENT CONTAINER-->
			
			<?php include_once('main-nav.php'); ?>
			<?php include_once('aside.php'); ?>

		</div>
		
</div> 

		
</div>
</div>



<?php get_footer(); ?>