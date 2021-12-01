<?php
/*
 * Template Name: PFL Scorigami
 * Description: Used for creating and testing new ideas
 */
 ?>

<?php 
$season = date("Y");
$theweeks = the_weeks();
$playerassoc = get_players_assoc();
//$weekvar = 199101;
$weekvar = $_GET["W"];

$RBS = $wpdb->get_results("select * from wp_team_RBS", ARRAY_N);
$ETS = $wpdb->get_results("select * from wp_team_ETS", ARRAY_N);
$PEP = $wpdb->get_results("select * from wp_team_PEP", ARRAY_N);
$WRZ = $wpdb->get_results("select * from wp_team_WRZ", ARRAY_N);
$CMN = $wpdb->get_results("select * from wp_team_CMN", ARRAY_N);
$BUL = $wpdb->get_results("select * from wp_team_BUL", ARRAY_N);
$SNR = $wpdb->get_results("select * from wp_team_SNR", ARRAY_N);
$TSG = $wpdb->get_results("select * from wp_team_TSG", ARRAY_N);
$BST = $wpdb->get_results("select * from wp_team_BST", ARRAY_N);
$MAX = $wpdb->get_results("select * from wp_team_MAX", ARRAY_N);
$PHR = $wpdb->get_results("select * from wp_team_PHR", ARRAY_N);
$SON = $wpdb->get_results("select * from wp_team_SON", ARRAY_N);
$ATK = $wpdb->get_results("select * from wp_team_ATK", ARRAY_N);
$HAT = $wpdb->get_results("select * from wp_team_HAT", ARRAY_N);
$DST = $wpdb->get_results("select * from wp_team_DST", ARRAY_N);

function scoreforweeks($array)
{
    foreach ($array as $key => $value):
        if($value[7] == 'H'):
            $scoreone = $value[4];
            $scoretwo = $value[6];
            if($scoreone > $scoretwo):
                $output[$value[0]] = array(
                    'winner'   => $scoreone,
                    'loser'    => $scoretwo
                );
            else:
                $output[$value[0]] = array(
                    'winner'   => $scoretwo,
                    'loser'    => $scoreone
                );
            endif;
        endif;
    endforeach;
    return $output;
}

$rbs_score = scoreforweeks($RBS);
$pep_score = scoreforweeks($PEP);
$ets_score = scoreforweeks($ETS);
$wrz_score = scoreforweeks($WRZ);
$cmn_score = scoreforweeks($CMN);
$bul_score = scoreforweeks($BUL);
$snr_score = scoreforweeks($SNR);
$tsg_score = scoreforweeks($TSG);
$bst_score = scoreforweeks($BST);
$max_score = scoreforweeks($MAX);
$phr_score = scoreforweeks($PHR);
$son_score = scoreforweeks($SON);
$atk_score = scoreforweeks($ATK);
$hat_score = scoreforweeks($HAT);
$dst_score = scoreforweeks($DST);

foreach ($theweeks as $week):
    $allscores[$week] = array(
        $rbs_score[$week],
        $pep_score[$week],
        $ets_score[$week],
        $wrz_score[$week],
        $cmn_score[$week],
        $bul_score[$week],
        $snr_score[$week],
        $tsg_score[$week],
        $bst_score[$week],
        $max_score[$week],
        $phr_score[$week],
        $son_score[$week],
        $atk_score[$week],
        $hat_score[$week],
        $dst_score[$week]
    );
endforeach;

foreach ($allscores as $week => $score):
    foreach($score as $key => $value):
        if($value):
            $cleanscores['cell_'.$value['winner'].'_'.$value['loser']][$week] = $value;
            $cleankeys['cell_'.$value['winner'].'_'.$value['loser']][] = $week;
        endif;
    endforeach;
endforeach;

function getpostseasonpoints($year, $week, $team){
    global $wpdb;
    $getpoints = $wpdb->get_results("select points from wp_playoffs where year = $year && week = $week && team = '$team' && overtime = 0", ARRAY_N);
    foreach($getpoints as $key => $value):
        $newpspoints[] = $value[0];
    endforeach;
    $pssum = array_sum($newpspoints);
    return $pssum;
}

$postseason = get_postseason();
foreach ($postseason as $key => $value):
    $playoffidpos = substr($value['playoffid'], -2, 1);
    $playoffidreg = substr($value['playoffid'], -1);
    if($value['result'] == 1):
        $winner = getpostseasonpoints($value['year'], $value['week'], $value['team']);
        $loser = getpostseasonpoints($value['year'], $value['week'], $value['versus']);
        $mypostseason['cell_'.$winner.'_'.$loser][$value['year'].$value['week']] = array(
            'winner' => $winner,
            'loser' => $loser
        );
        if($playoffidreg == 0):
            if($playoffidpos == 1):
                $mypostseasonkeys['cell_'.$winner.'_'.$loser][] = $value['year'].$value['week'];
                $postseasongame[] = 'cell_'.$winner.'_'.$loser;
            endif;
        endif;
    endif;
endforeach;

//printr($postseason, 0);

?>


<?php get_header(); ?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold">PFL Scorigami</h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
					
					<div class="panel panel-bordered panel-light">
                                <!--Bordered Table-->
                                <!--===================================================-->
                        <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="bootstrap-table table-bordered table-scorigami" width="98%">
                                            <thead>
                                            <h4 class="text-center">Winning Team Score</h4>
                                            <tr>
                                                <?php
                                                    $i = 0;

                                                    while($i <= 75):
                                                        echo '<th>'.$i.'</th>';
                                                        $i++;
                                                    endwhile;
                                                    echo '<th></th>';
                                                ?>

                                            </tr>
                                            </thead>
                                            <tbody>

                                             <?php
                                                $j = 0;
                                                while($j <= 75):
                                                echo
                                                '<tr>';
                                                    $k = 0;
                                                    while($k <= 75):
                                                        if(in_array('cell_'.$k.'_'.$j, $postseasongame)):
                                                            $postseasoncolor = 'postseasoncolor';
                                                        else:
                                                            $postseasoncolor = '';
                                                        endif;
                                                        $printkey = '';
                                                        $checkweek = '';
                                                        $count = 0;
                                                        if($cleankeys['cell_'.$k.'_'.$j]):
                                                            foreach($cleankeys['cell_'.$k.'_'.$j] as $value):
                                                                $printkey .= $value.' ';
                                                                $count++;
                                                                if($value == $weekvar):
                                                                    $checkweek = 'checkweek';
                                                                endif;
                                                            endforeach;
                                                        endif;
                                                        if($mypostseasonkeys['cell_'.$k.'_'.$j]):
                                                            foreach($mypostseasonkeys['cell_'.$k.'_'.$j] as $value):
                                                                $printkey .= $value.' ';
                                                                $count++;
                                                                if($value == $weekvar):
                                                                    $checkweek = 'checkweek';
                                                                endif;
                                                            endforeach;
                                                        endif;


                                                        if($j >= $k):
                                                            echo '<td id="cell_'.$k.'_'.$j.'" class="none-score"></td>';
                                                        else:
                                                            if($count):
                                                                echo '<td id="cell_'.$k.'_'.$j.'" class="yes-score myCell '.$postseasoncolor.' '.$checkweek.' '.$newscorigami.'"><div class="add-tooltip" data-toggle="tooltip" href="#" data-original-title="'.$printkey.'">'.$count.'</div></td>';
                                                            else:
                                                                echo '<td id="cell_'.$k.'_'.$j.'" class="no-score myCell"></td>';
                                                            endif;
                                                        endif;
                                                        $k++;
                                                    endwhile;
                                                    echo '<td class="firstrow">'.$j.'</td>';
                                                echo '</tr>';
                                                    $j++;
                                                endwhile;

                                                ?>

                                            </tbody>
                                            <div class="vertical">Losing Team Score</div>
                                        </table>
                                        <h5>Postseason score that week (in yellow), Selected week var (in orange), --Ability to build or isolate individual weeks, --New Scorigami alert</h5>
                                    </div>

                                <!--===================================================-->
                                <!--End Bordered Table-->
                        </div>
								
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