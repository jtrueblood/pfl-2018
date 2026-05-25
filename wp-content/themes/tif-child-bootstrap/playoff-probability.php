<?php
/*
 * Template Name: Playoff Probability
 * Description: Finds the probability of a team making the playoffs based on the number of wins they have by a given week in a season.
 */
 ?>

<?php get_header(); ?>

<?php

 ?>
<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">
        
            <div id="page-title">
                <h1 class="page-header text-bold">Playoff Probability</h1>
            </div>

            <!--Page content-->
            <div id="page-content">
                <?php
                $getyears = the_seasons();

                $teamlist = get_teams();
                foreach($teamlist as $key => $value):
                    $teams[] = $key;
                endforeach;

                //$year = 1991;
                //$team = 'SNR';

                foreach($getyears as $k => $y):
                    $seeds = get_seeds_by_year($y);
                    foreach ($teams as $t):
                        $curteam = $seeds[$t];
                        $teamstanding = get_standings_weekly_by_team($t, $y, 14);
                        if($teamstanding):
                            $w = 0;
                            foreach ($teamstanding as $key => $value):

                                $winget = $value['win'];
                                if($winget == 1):
                                    $w++;
                                endif;
                                if($curteam > 0):
                                    $playoffteam = 1;
                                else:
                                    $playoffteam = 0;
                                endif;
                                $cleanedstanding[$y.$t][$value['week']] = array(
                                        'team' => $value['teamid'],
                                        'year' => $y,
                                        'week' => $value['week'],
                                        'wins' => $w,
                                        'made_pl' => $playoffteam
                                );
                            endforeach;
                        endif;
                    endforeach;
                endforeach;

                foreach($cleanedstanding as $key => $values):
                    foreach($values as $k => $v):
                        $byweek[$v['week']][$v['wins']][] = $v;
                    endforeach;
                endforeach;

                // week first / then wins
                //printr($byweek[12][11], 0);

                function get_week_probability($dataset, $week, $wins) {
                    if ($week < $wins || empty($dataset[$week][$wins])) {
                        return 'X';
                    }

                    $countplayoffs = array_column($dataset[$week][$wins], 'made_pl');
                    $i = count($countplayoffs);
                    $count = array_count_values($countplayoffs);

                    if ($count[0] > 0) {
                        $percent = ($count[1] / $i) * 100;
                        $per = number_format($percent, 1) . '%';
                    } else {
                        $per = '100%';
                    }

                    return $per;
                }

                $testweek = 7;
                $testwins = 4;
                $checkweek = get_week_probability($byweek, $testweek, $testwins);
                //echo '<h4>The probability of a team with '.$testwins.' win in week '.$testweek.' making the playoffs is:'.$checkweek.'</h4>';

                ?>

                <div class="col-xs-14 mar-btm">
                        <div class="panel">
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>WEEK --><br>WINS</th>
                                            <th class="text-center">1</th>
                                            <th class="text-center">2</th>
                                            <th class="text-center">3</th>
                                            <th class="text-center">4</th>
                                            <th class="text-center">5</th>
                                            <th class="text-center">6</th>
                                            <th class="text-center">7</th>
                                            <th class="text-center">8</th>
                                            <th class="text-center">9</th>
                                            <th class="text-center">10</th>
                                            <th class="text-center">11</th>
                                            <th class="text-center">12</th>
                                            <th class="text-center">13</th>
                                            <th class="text-center">14</th>
                                        </tr>
                                        </thead>
                                        <?php
                                        $matrixarray = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14);


                                        foreach($matrixarray as $value):
                                        echo '<tr>';

                                            echo '<td class="text-bold text-center">'.$value.'</td>'; // Win List
                                            $a = $key ? get_week_probability($byweek, 1, $value) : null;
                                            echo '<td class="text-center">'.$a.'</td>';
                                            $b = $key ? get_week_probability($byweek, 2, $value) : null;
                                            echo '<td class="text-center">'.$b.'</td>';
                                            $c = $key ? get_week_probability($byweek, 3, $value) : null;
                                            echo '<td class="text-center">'.$c.'</td>';
                                            $d = $key ? get_week_probability($byweek, 4, $value) : null;
                                            echo '<td class="text-center">'.$d.'</td>';
                                            $e = $key ? get_week_probability($byweek, 5, $value) : null;
                                            echo '<td class="text-center">'.$e.'</td>';
                                            $f = $key ? get_week_probability($byweek, 6, $value) : null;
                                            echo '<td class="text-center">'.$f.'</td>';
                                            $g = $key ? get_week_probability($byweek, 7, $value) : null;
                                            echo '<td class="text-center">'.$g.'</td>';
                                            $h = $key ? get_week_probability($byweek, 8, $value) : null;
                                            echo '<td class="text-center">'.$h.'</td>';
                                            $i = $key ? get_week_probability($byweek, 9, $value) : null;
                                            echo '<td class="text-center">'.$i.'</td>';
                                            $j = $key ? get_week_probability($byweek, 10, $value) : null;
                                            echo '<td class="text-center">'.$j.'</td>';
                                            $k = $key ? get_week_probability($byweek, 11, $value) : null;
                                            echo '<td class="text-center">'.$k.'</td>';
                                            $l = $key ? get_week_probability($byweek, 12, $value) : null;
                                            echo '<td class="text-center">'.$l.'</td>';
                                            $m = $key ? get_week_probability($byweek, 13, $value) : null;
                                            echo '<td class="text-center">'.$m.'</td>';
                                            $n = $key ? get_week_probability($byweek, 14, $value) : null;
                                            echo '<td class="text-center">'.$n.'</td>';

                                        echo '</tr>';

                                        endforeach;
                                            ?>
                                        <tbody>

                                        </tbody>
                                    </table>

                                <?php

                                    //printr($new, 0);
                                ?>

                                </div><!-- end table-responsive -->
                            </div><!-- end panel-body -->
                        </div><!-- end panel -->
                </div><!-- end col-xs-14 -->

                    <?php //printr($getyears, 0);?>

            </div><!--End page content-->

        </div><!--END CONTENT CONTAINER-->

    <?php include_once('main-nav.php'); ?>
    <?php include_once('aside.php'); ?>

</div><!--END BOXED-->

<?php get_footer(); ?>
