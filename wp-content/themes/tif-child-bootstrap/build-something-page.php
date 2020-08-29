<?php
/*
 * Template Name: Build Something
 * Description: Page used temporarilly to just build something
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
					
					<?php
						$games = get_all_players_games_played();
						printr($games, 0);
						
						$career_duration = get_all_players_games_played();
					
						$labels = array('POS', 'Games', 'Seasons');	
						tablehead('Career Duration By Posittion', $labels);	
					
						$n = 0;
						foreach ($career_duration as $key => $val){		
							$printduration .='<tr><td>'.$key.'</td>';
							$printduration .='<td>'.$val['avg'].'</td>';
							$printduration .='<td>'.$val['season'].'</td></tr>';
						}
	
						echo $printduration;
							
						tablefoot('');	
						
/*
						$qb_sum = $games['qb_sum'];
						$qb_count = $games['qb_count'];
						$rb_sum = $games['rb_sum'];
						$rb_count = $games['rb_count'];
						$wr_sum = $games['wr_sum'];
						$wr_count = $games['wr_count'];
						$pk_sum = $games['pk_sum'];
						$pk_count = $games['pk_count'];
						
						$qb_avg = $qb_sum / $qb_count;
						$qb_avg_season = $qb_avg / 13;
						$rb_avg = $rb_sum / $rb_count;
						$rb_avg_season = $rb_avg / 13;
						$wr_avg = $wr_sum / $wr_count;
						$wr_avg_season = $wr_avg / 13;
						$pk_avg = $pk_sum / $pk_count;
						$pk_avg_season = $pk_avg / 13;
						
						echo '<h3>Average QB Games Played:'.number_format($qb_avg, 1).'</h3>';
						echo '<h3>Average QB Seasons Played:'.number_format($qb_avg_season, 1).'</h3>';
						echo '<h3>Average RB Games Played:'.number_format($rb_avg, 1).'</h3>';
						echo '<h3>Average RB Seasons Played:'.number_format($rb_avg_season, 1).'</h3>';
						echo '<h3>Average WR Games Played:'.number_format($wr_avg, 1).'</h3>';
						echo '<h3>Average WR Seasons Played:'.number_format($wr_avg_season, 1).'</h3>';
						echo '<h3>Average PK Games Played:'.number_format($pk_avg, 1).'</h3>';
						echo '<h3>Average PK Seasons Played:'.number_format($pk_avg_season, 1).'</h3>';
*/
						
						
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