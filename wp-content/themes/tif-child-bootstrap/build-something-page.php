<?php
/*
 * Template Name: Build Something
 * Description: Used for...
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

                    $insert_player =
                    array(
                        'week_id' => 202201,
                        'year' => 2022,
                        'week' => 1,
            'points  => 
            [team] => BUL
            [versus] => CMN
            [playerid] => 2018JackQB
            [win_loss] => 1
            [home_away] => H
            [location] => The Cuckoos Nest
        );

                    global $wpdb;
                    foreach ($insert_player as $key => $pi){
                        $wpdb->insert(
                            $key,
                            array(
                                'week_id' 	=> $pi['week_id'],
                                'year'		=> $pi['year'],
                                'week'		=> $pi['week'],
                                'points'	=> $pi['points'],
                                'team'		=> $pi['team'],
                                'versus'	=> $pi['versus'],
                                'playerid'	=> $pi['playerid'],
                                'win_loss'	=> $pi['win_loss'],
                                'home_away'	=> $pi['home_away'],
                                'location'	=> $pi['location'],
                                // Change made in 2022 after player tables were expanded to include NFL Game stats.
                                // Set values to empty.  Add weekly NFL Data using the scrape-pfr.php file
                                'game_date' => '2022-00-00',
                                'nflteam' => 'TTT',
                                'game_location' => 'S',
                                'nflopp' => 'ZZZ',
                                'pass_yds' => 0,
                                'pass_td' => 0,
                                'pass_int' => 0,
                                'rush_yds' => 0,
                                'rush_td' => 0,
                                'rec_yds' => 0,
                                'rec_td' => 0,
                                'xpm' => 0,
                                'xpa' => 0,
                                'fgm' => 0,
                                'fga' => 0,
                                'nflscore' => 0,
                                'scorediff' => 0
                            ),
                            array (
                                '%d','%d','%d','%d','%s','%s','%s','%d','%s','%s','%s','%s','%s','%s','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d','%d'
                            )
                        );
                    }
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