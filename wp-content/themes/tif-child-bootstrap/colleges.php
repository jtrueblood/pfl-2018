<?php
/*
 * Template Name: Colleges
 * Description: Stuff Goes Here
 */
 ?>

<?php get_header(); ?>

<?php

 ?>
<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <H3>PFL Player Colleges</H3>
            <p>Played in the PFL at least one game or were drafted.</p>

            <div class="row">
                <div class="col-xs-24 col-sm-6">
                <?php
                $playersassoc = get_players_assoc ();
                $college = array();
                foreach ($playersassoc as $key => $value):
                    $college[$value[7]][] =  $key;
                endforeach;

                foreach ($college as $key => $value):
                    $collegecount[$key] = array(
                        'count' => count($value),
                        'players' => $value,
                    );
                endforeach;

                foreach ($college as $key => $value):
                    foreach ($value as $key2 => $value2):
                        $getpoints[$key][$value2] = get_player_career_points($value2);
                    endforeach;
                endforeach;

                arsort($collegecount);
                //$college($getpoints, 0);
                printr($college, 0);
                echo '</div>';

                echo '<div class="col-xs-12 col-sm-6">';
                $labels = array('College', 'Count', 'Best PFL Player');
                tablehead('Players by College', $labels);

                $i = 1;
                foreach ($collegecount as $school => $count):
                    $maxs = array_keys($getpoints[$school], max($getpoints[$school]));
                    $printcol .='<tr><td>'.$school.'</td>';
                    $printcol .='<td style="text-align: center;">'.$count['count'].'</td>';
                    $printcol .='<td>'.pid_to_name($maxs[0], 0).'</td></tr>';
                    $i++;
                    if($i >= 26):
                        break;
                    endif;
                endforeach;

                echo $printcol;
                tablefoot('');

                echo '</div>';

                echo '<div class="col-xs-12 col-sm-4">';

                $vandy = $college['Vanderbilt'];
                $psu = $college['Penn St.'];
                $florida = $college['Florida'];
                $ameherst = $college['Amherst'];
                $ursinus = $college['Ursinus'];
                $fandm = $college['Franklin & Marshall'];
                $syracuse = $college['Syracuse'];
                $denison = $college['Denison'];
                $bucknell = $college['Bucknell'];
                $posse_college = array('Vanderbilt' => $vandy, 'Penn State' => $psu, 'Florida' => $florida, 'Amherst' => $ameherst, 'Ursinus' => $ursinus, 'Franklin & Marshall' => $fandm, 'Syracuse' => $syracuse, 'Denison' => $denison, 'Bucknell' => $bucknell);

                foreach ($posse_college as $key => $value):
                    if($value):
                        $posse_college_count[$key] = count($value);
                    else:
                        $posse_college_count[$key] = 0;
                    endif;
                endforeach;
                arsort($posse_college_count);

                $labels = array('College', 'Count');
                tablehead('Players by Posse College', $labels);

                foreach ($posse_college_count as $school => $count):
                    $printcolposse .='<tr><td>'.$school.'</td>';
                    $printcolposse .='<td>'.$count.'</td></tr>';
                endforeach;

                echo $printcolposse;
                tablefoot('');

                echo '</div>';

                echo '<div class="col-xs-12 col-sm-4">';

                foreach ($psu as $key => $value):
                    $psu_players[] = pid_to_name($value, 0);
                endforeach;

                $labels = array();
                tablehead('Penn State Players', $labels);
                foreach ($psu_players as $school => $player):
                    $print_psu .='<tr><td>'.$player.'</td></tr>';
                endforeach;
                echo $print_psu;
                tablefoot('');

                foreach ($syracuse as $key => $value):
                    $syracuse_players[] = pid_to_name($value, 0);
                endforeach;

                $labels = array();
                tablehead('Syracuse Players', $labels);
                foreach ($syracuse_players as $school => $player):
                    $print_syracuse .='<tr><td>'.$player.'</td></tr>';
                endforeach;
                echo $print_syracuse;
                tablefoot('');

                foreach ($florida as $key => $value):
                    $florida_players[] = pid_to_name($value, 0);
                endforeach;

                $labels = array();
                tablehead('Florida', $labels);
                foreach ($florida_players as $school => $player):
                    $florida_psu .='<tr><td>'.$player.'</td></tr>';
                endforeach;
                echo $florida_psu;
                tablefoot('');

                foreach ($vandy as $key => $value):
                    $vandy_players[] = pid_to_name($value, 0);
                endforeach;

                $labels = array();
                tablehead('Vanderbilt Players', $labels);
                foreach ($vandy_players as $school => $player):
                    $print_vandy .='<tr><td>'.$player.'</td></tr>';
                endforeach;
                echo $print_vandy;
                tablefoot('');

                ?>
                </div>
            </div>
        </div>

    </div><!--End page content-->

</div><!--END CONTENT CONTAINER-->


<?php include_once('main-nav.php'); ?>
<?php include_once('aside.php'); ?>

</div>

<?php get_footer(); ?>