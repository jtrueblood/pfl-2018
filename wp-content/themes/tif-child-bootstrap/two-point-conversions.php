<?php
/*
 * Template Name: Two Point Conversion Check
 * Description: Used for trying to identify and assign differences between the PFL score and the NFL calculated score froNFL game data.
 */
 ?>

<?php

$year = $_GET['year'];
$players = get_players_assoc ();

function get_player_extra_score ($playerid){
    global $wpdb;
    //$getdata = $wpdb->get_results("select * from wp_season_leaders where '$year' like season", ARRAY_N);
    $getdata = $wpdb->get_results("select week_id, scorediff from $playerid WHERE scorediff != 0;", ARRAY_N);
    if($getdata):
        foreach ($getdata as $key => $value):
            $getdatanew[$value[0]] = $value[1];
        endforeach;
    endif;
    return $getdatanew;
}

function get_player_possible_two_pts ($playerid){
    global $wpdb;
    $getdata = $wpdb->get_results("select week_id, scorediff from $playerid WHERE scorediff = 1;", ARRAY_N);
    if($getdata):
    foreach ($getdata as $key => $value):
        $gettwos[$value[0]] = $value[1];
    endforeach;
    endif;
    return $gettwos;
}

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
                    <h3>EXTRAS</h3>
                    <?php
                        //$print = get_player_extra_score ('2020HurtQB');
                        //printr($print, 0);
                        foreach ($players as $key => $value):
                            $extras[$key] = get_player_extra_score ($key);
                            $possibletwos[$key] = get_player_possible_two_pts($key);
                        endforeach;

                    ?>

                    <h3>SUSPECTED TWO POINTERS</h3>
                    <?php
                    foreach($possibletwos as $key => $value):
                        if($value):
                            $possibletwosnew[$key] = $value;
                        endif;
                    endforeach;
                    printr($possibletwosnew, 0);
                    ?>

                    <h3>SCORE DIFFERENCES</h3>
                    <?php

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