<?php
/*
 * Template Name: Scoring Titles Tables
 * Description: Used for...
 */
 ?>

<?php

$yeartwenty = get_season_leaders(2020);
$yeartwentyone = get_season_leaders(2021);

$playerid = $_GET['id'];

$playerposition = substr($playerid, -2);
$playerdata = get_player_career_stats($playerid);
$playerdeets = get_player_data($playerid);
$jusplayerids = just_player_ids();
$numberones = get_number_ones();

foreach ($numberones as $key => $value):
    $newnumones[$value['pos']][$value['year']][] = $value;
endforeach;

$qbs = $newnumones['QB'];
$rbs = $newnumones['RB'];
$wrs = $newnumones['WR'];
$pks = $newnumones['PK'];

?>

<?php get_header(); ?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold">Scoring Title Tables</h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">

                        <!-- Quarterbacks -->
                        <div class="col-xs-24 col-sm-12 col-md-6">
                            <?php
                            $labels = array('Year', 'Player', 'Team', 'Points');
                            tablehead('Passing Title Winners', $labels);
                            foreach ($qbs as $k => $v):
                                $first_in_year = true;
                                foreach($v as  $key => $value):
                                    $name = get_player_name($value['playerid']);
                                    $names[] = $value['playerid'];
                                    $count = array_count_values_of($value['playerid'], $names);
                                    if($count > 1):
                                        $occur = ' ('.$count.')';
                                    else:
                                        $occur = '';
                                    endif;
                                    $year_display = $first_in_year ? $k : '';
                                    $tableprint .='<tr><td>'.$year_display.'</td>';
                                    $tableprint .='<td>'.$name['first'].' '.$name['last'].$occur.'</td>';
                                    $tableprint .='<td>'.$value['teams'].'</td>';
                                    $tableprint .='<td class="min-width text-right">'.number_format($value['points'], '0', '.', ',').'</td></tr>';
                                    $first_in_year = false;
                                endforeach;
                            endforeach;
                            echo $tableprint;
                            $tableprint = '';
                            tablefoot('');
                            ?>
                        </div>

                    <!-- Runningbacks -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Year', 'Player', 'Team', 'Points');
                        tablehead('Rushing Title Winners', $labels);
                        foreach ($rbs as $k => $v):
                            $first_in_year = true;
                            foreach($v as  $key => $value):
                                $name = get_player_name($value['playerid']);
                                $names[] = $value['playerid'];
                                $count = array_count_values_of($value['playerid'], $names);
                                if($count > 1):
                                    $occur = ' ('.$count.')';
                                else:
                                    $occur = '';
                                endif;
                                $year_display = $first_in_year ? $k : '';
                                $tableprint .='<tr><td>'.$year_display.'</td>';
                                $tableprint .='<td>'.$name['first'].' '.$name['last'].$occur.'</td>';
                                $tableprint .='<td>'.$value['teams'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value['points'], '0', '.', ',').'</td></tr>';
                                $first_in_year = false;
                            endforeach;
                        endforeach;
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('');
                        ?>
                    </div>

                    <!-- Recievers -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Year', 'Player', 'Team', 'Points');
                        tablehead('Receiving Title Winners', $labels);
                        foreach ($wrs as $k => $v):
                            $first_in_year = true;
                            foreach($v as  $key => $value):
                                $name = get_player_name($value['playerid']);
                                $names[] = $value['playerid'];
                                $count = array_count_values_of($value['playerid'], $names);
                                if($count > 1):
                                    $occur = ' ('.$count.')';
                                else:
                                    $occur = '';
                                endif;
                                $year_display = $first_in_year ? $k : '';
                                $tableprint .='<tr><td>'.$year_display.'</td>';
                                $tableprint .='<td>'.$name['first'].' '.$name['last'].$occur.'</td>';
                                $tableprint .='<td>'.$value['teams'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value['points'], '0', '.', ',').'</td></tr>';
                                $first_in_year = false;
                            endforeach;
                        endforeach;
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('');
                        ?>
                    </div>

                    <!-- Kickers -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Year', 'Player', 'Team', 'Points');
                        tablehead('Kicking Title Winners', $labels);
                        foreach ($pks as $k => $v):
                            $first_in_year = true;
                            foreach($v as  $key => $value):
                                $name = get_player_name($value['playerid']);
                                $names[] = $value['playerid'];
                                $count = array_count_values_of($value['playerid'], $names);
                                if($count > 1):
                                    $occur = ' ('.$count.')';
                                else:
                                    $occur = '';
                                endif;
                                $year_display = $first_in_year ? $k : '';
                                $tableprint .='<tr><td>'.$year_display.'</td>';
                                $tableprint .='<td>'.$name['first'].' '.$name['last'].$occur.'</td>';
                                $tableprint .='<td>'.$value['teams'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value['points'], '0', '.', ',').'</td></tr>';
                                $first_in_year = false;
                            endforeach;
                        endforeach;
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('');
                        ?>
                    </div>

                    <!-- Award comparison by year -->
                    <div class="col-xs-24">


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