<?php
/*
 * Template Name: Schedules
 * Description: Used for displaying all schedules and results
 */
 ?>

<?php
$month = date('m');
$smonth = sprintf('%0d', $month);
$week = $_GET["W"];
$weeks = the_weeks();
//$week = 199201;
$teamdata = get_all_team_data();

$schedule = schedule_by_week();
//echo $smonth;
?>

<?php get_header(); ?>

<div class="boxed">

			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold">PFL Schedules</h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
                    <div class="row">
                    <?php //printr($schedule[199902], 0); ?>
                    <!-- Extra Point % -->

                        <?php
                        if($week):
                            //display just one week if '?W=199101' var is set in url
                            echo '<div class="col-xs-24 col-sm-12 col-md-6">';
                                $labels = array('Home', '', 'Away', '');
                                tablehead($week, $labels);
                                $getsched = $schedule[$week];
                                foreach ($getsched as $key => $value){
                                    $tableprint .='<tr><td>'.team_long($value['hometeam']).'</td>';
                                    $tableprint .='<td class="min-width">'.$value['homescore'].'</td>';
                                    $tableprint .='<td>'.team_long($value['roadteam']).'</td>';
                                    $tableprint .='<td class="min-width">'.$value['roadscore'].'</td></tr>';
                                }
                                echo $tableprint;
                                $tableprint = '';
                                tablefoot('');
                            echo '</div>';
                        else:
                            //loop through all weeks
                            $i = 1;
                            foreach($weeks as $wk):

                            $year = substr($wk, 0, 4);
                            $printwk = 'Week '.substr($wk,-2);

                                if($i == 1):
                                    echo '<div class="clearfix"></div>';
                                    echo '<div class="col-xs-24"><h4>'.$year.'</h4></div>';
                                    $i = 15;
                                endif;

                                echo '<div class="col-xs-24 col-sm-2 col-md-4 col-lg-6">';
                                    $labels = array('Home', '', 'Away', '');
                                    tablehead($printwk, $labels);

                                    $getsched = $schedule[$wk];

                                    foreach ($getsched as $key => $value):
                                            if($value['homescore'] > $value['roadscore']):
                                                $winh = 'sced-win';
                                                $winr = '';
                                            else:
                                                $winh = '';
                                                $winr = 'sced-win';
                                            endif;

                                            $tableprint .='<tr><td class="'.$winh.'">'.team_long($value['hometeam']).'</td>';
                                            $tableprint .='<td class="min-width">'.$value['homescore'].'</td>';
                                            $tableprint .='<td class="'.$winr.'">'.team_long($value['roadteam']).'</td>';
                                            $tableprint .='<td class="min-width">'.$value['roadscore'].'</td></tr>';
                                    endforeach;

                                    echo $tableprint;

                                    $tableprint = '';
                                    tablefoot('');
                                echo '</div>';
                                $i --;

                            endforeach;
                        endif;
                        ?>


                    </div><!--End Row-->

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