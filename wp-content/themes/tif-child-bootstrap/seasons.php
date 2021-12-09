<?php
/*
 * Template Name: Seasons
 * Description: Page for displaying information by Year
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->

<?php get_header(); ?>

<?php 	
	

$season = date("Y");
$year = $_GET['id'];

$years = the_seasons();
$standing = get_standings($year);
$playersassoc = get_players_assoc();
$teamlist = teamlist();

?>

<?php include_once('main-nav.php'); ?>
	
<div id="page-content" class="season-page">	
				
	<!--CONTENT CONTAINER-->
		<div id="content-container">
			
			<!-- LEFT COL -->
			<div class="col-xs-24 col-sm-6">
									
				<div class="panel widget">
					<div class="left-widget widget-body">
						<h3><?php echo $year; ?> PFL Season</h3>
						<hr>
						<?php $champs = get_just_champions(); 
							echo '<h4>'.$teamlist[$champs[$year]].' - PFL Champions</h4>';
							echo '<img class="" width="200px" src="/wp-content/uploads/'.$champs[$year].'-helmet-full-250x250.png" alt="Image">';
						?>
					</div>
				</div>

                <div class="panel widget">
                    <div class="left-widget widget-body">
                        <h4>Players of the Week</h4>
                        <hr>
                        <?php
                        $potw = get_player_of_week();

                        foreach($potw as $key => $value):
                            $potwtopoint = array();
                            $weekst = substr($key, -2);
                            $yearst = substr($key, 0, 4);
                            if($year == $yearst):
                                $playerst = get_player_team_played_week($value, $key);
                                $pointsst = get_player_points_by_week($value, $key);
                                $potyst = get_player_of_week_player($value);
                                foreach($potyst as $k => $pweeks):
                                    if($pweeks <= $key):
                                        $potwtopoint[] = $pweeks;
                                    endif;
                                endforeach;
                                $potwyear[$weekst] = array(
                                    'player' => pid_to_name($value, 0),
                                    'team' => $playerst[0][0],
                                    'points' => $pointsst[0][0],
                                    'poty' => $potwtopoint,
                                    'count' => count($potwtopoint)
                                );
                            endif;
                        endforeach;

                        foreach($potwyear as $key => $value):
                            if ($value['count'] > 1):
                                $c = '('.$value['count'].')';
                            else:
                                $c = '';
                            endif;
                            echo '<p>Week '.$key.' | <strong>'.$value['player'].'</strong>, '.$value['team'].' - '.$value['points'].' Points '.$c.'</p>';
                            endforeach;
                        ?>
                    </div>
                </div>

				
			</div>		
					
			<!-- MIDDLE COL -->
			<div class="col-xs-24 col-md-9">

				<?php 
					selectseason();
					include_once('inc/season_standings.php');
					//include_once('inc/season_draft.php');
				?>
				
			</div>
			
			
			
			
			<!-- RIGHT COL -->
			<div class="col-xs-24 col-sm-9">
					
				<?php 
				include_once('inc/season_awards.php');
				include_once('inc/season_leaders.php');
				?>
					
			</div>
				
			

	</div><!--END CONTENT CONTAINER-->

</div><!--End page content-->		







<?php get_footer(); ?>