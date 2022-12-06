<?php
/*
 * Template Name: Two Point Conversion Check
 * Description: Used for trying to idenitfy and assign differences between the PFL score and the NFL calculated score froNFL game data.
 */
 ?>

<?php

$year = $_GET['year'];

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



                    <?php
                        $players = get_players_assoc ();
                        foreach ($players as $key => $value):
                            $ids[] = $key;
                        endforeach;





                        foreach ($ids as $i):
                            $data = get_player_data($i);
                            if($data):
                                foreach ($data as $k => $v):
                                    if($v['points'] == 0):
                                        $store[$k][$i] = $v['team'];
                                    endif;
                                endforeach;
                            endif;
                        endforeach;

                        printr($store, 0);
                    ?>


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