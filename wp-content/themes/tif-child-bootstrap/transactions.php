<?php
/*
* Template Name: Transactions
* Description: Lists all transactions for teams since My Fantasy League Website data exsisted.
* Json files for rosters and transactions are downloaded at the end of the season and stored locally on the 'mfl-rosters' and 'mfl-transactions' directoies
*/
?>

<?php
//printr($teambyid, 0);
$teams = get_teams();
get_header();

$mfl_team_id_history = teams_for_mfl_history();
printr($mfl_team_id_history, 0);

?>

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
                    // gets json mfl transactions file that merges all of the files above into a name -> id format
                    //$yearid = 2011;
                    // data availible from 2011 on.
                    $gettwenty = get_mfl_transactions(2020);
                    printr($gettwenty, 0);

                    $printit = new_mfl_transactions('2020BassPK');
                    printr($printit, 0);
                    ?>


                    <?php
//                  $rosteryear = 2020;
//                    echo '<h2>'.$rosteryear.' MFL Rosters</h2>';
//
//                    foreach ($yearslist as $year):
//                        $getjson = get_mfl_year_rosters($year);
//                        $players = $getjson->players->player;
//                            foreach ($players as $key => $value):
//                                if($value->position == 'QB' OR $value->position == 'RB' OR $value->position == 'WR' OR $value->position == 'TE' OR $value->position == 'PK'):
//                                    $player_arr_name[$value->name] = $value->id;
//                                    $player_arr_id[$value->id] = $value->name;
//                                endif;
//                            endforeach;
//                    endforeach;
//
//                    $encode = json_encode($player_arr_name);
//                    echo $encode;
//                    printr($player_arr_name, 0);
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