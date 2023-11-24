<?php
/*
 * Template Name: Position Differences
 * Description: Stuff Goes Here
 */
 ?>

<?php get_header(); ?>

<?php
function print_array_as_csv($die, $team, $pos){
    $top_team_qb_test = get_player_leaders_by_team($team, $pos);
    $i = 0;
    foreach ($top_team_qb_test as $playerid => $points):
        if($i < $die):
            $string .= pid_to_name($playerid, 1) . ', ';
        else:
            break;
        endif;
        $i++;
    endforeach;
    rtrim($string ,", ");
    return $string;
}



?>
<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <div id="page-content">
                <H3>Player Positional Differences.</H3>
                <h4>Players plus/minus in head to head lifetime games.</h4>
                <p>This page requires the custom funtions on the function page position_difference, single_team_player_difference, team_output, get_or_set_comps.</p>
                <p>It will take a long time to load, but then will be very fast once the main transient is set.</p>

                <?php

                // all required functions moved to functions.php
                $comps_print = get_or_set_comps();

                ?>

                <div class="row">
                    <div class="col-xs-24 col-sm-12">
                        <?php echo '<h3>Position Plus / Minus</h3>'; ?>
                    </div>
                </div>
                <div class="row">
                    <?php
                    foreach ($comps_print as $team => $players):
                        echo '<div class="col-xs-12 col-sm-3">';

                            $labels = array('Pos', 'Diff');
                            tablehead(team_long($team), $labels);

                            foreach ($players as $pos => $value):
                                $postprint .='<tr><td>'.$pos.'</td>';
                                $postprint .='<td>'.$value.'</td></tr>';
                            endforeach;

                            echo $postprint;

                            tablefoot('');

                        echo '</div>';
                        $postprint = '';
                    endforeach;
                    ?>
                </div>

                <div class="row">
                    <?php

                    foreach ($comps_print as $team => $players):
                        $new_comp_qb[$team] = $players['QB'];
                        $new_comp_rb[$team] = $players['RB'];
                        $new_comp_wr[$team] = $players['WR'];
                        $new_comp_pk[$team] = $players['PK'];
                    endforeach;


                    // Quarterbacks
                    arsort($new_comp_qb);
                    echo '<div class="col-xs-12 col-sm-6">';
                        $labels = array('Team', 'Diff', 'Top Players');
                        tablehead('Quarterbacks', $labels);

                        foreach ($new_comp_qb as $team => $value):
                            $printqb .='<tr><td>'.$team.'</td>';
                            $printqb .='<td class="min-width">'.$value.'</td>';
                            $transient_qb = get_transient( $team.'_QB_leaders' );
                            if( ! empty( $transient_qb  ) ) {
                                $printqb .='<td>'.$transient_qb.'</td></tr>';
                            } else {
                                $top_team_qb_csv = print_array_as_csv(3, $team, 'QB');
                                set_transient( $team.'_QB_leaders', $top_team_qb_csv, 600000 );
                                $printqb .='<td>'.$top_team_qb_csv.'</td></tr>';
                            }
                        endforeach;

                        echo $printqb;
                        tablefoot('');


                    echo '</div>';

                    // Runningbacks
                    arsort($new_comp_rb);
                    echo '<div class="col-xs-12 col-sm-6">';
                    $labels = array('Team', 'Diff', 'Top Players');
                    tablehead('Runningbacks', $labels);

                    foreach ($new_comp_rb as $team => $value):
                        $printrb .='<tr><td>'.$team.'</td>';
                        $printrb .='<td>'.$value.'</td>';
                        $transient_rb = get_transient( $team.'_RB_leaders' );
                        if( ! empty( $transient_rb  ) ) {
                            $printrb .='<td>'.$transient_rb.'</td></tr>';
                        } else {
                            $top_team_rb_csv = print_array_as_csv(3, $team, 'RB');
                            set_transient( $team.'_RB_leaders', $top_team_rb_csv, 600000 );
                            $printrb .='<td>'.$top_team_rb_csv.'</td></tr>';
                        }
                    endforeach;

                    echo $printrb;

                    tablefoot('');
                    echo '</div>';

                    // Receivers
                    arsort($new_comp_wr);
                    echo '<div class="col-xs-12 col-sm-6">';
                    $labels = array('Team', 'Diff', 'Top Players');
                    tablehead('Receivers', $labels);

                    foreach ($new_comp_wr as $team => $value):
                        $printwr .='<tr><td>'.$team.'</td>';
                        $printwr .='<td>'.$value.'</td>';
                        $transient_wr = get_transient( $team.'_WR_leaders' );
                        if( ! empty( $transient_wr  ) ) {
                            $printwr .='<td>'.$transient_wr.'</td></tr>';
                        } else {
                            $top_team_wr_csv = print_array_as_csv(3, $team, 'WR');
                            set_transient( $team.'_WR_leaders', $top_team_wr_csv, 600000 );
                            $printwr .='<td>'.$top_team_wr_csv.'</td></tr>';
                        }
                    endforeach;

                    echo $printwr;

                    tablefoot('');
                    echo '</div>';

                    // Kickers
                    arsort($new_comp_pk);
                    echo '<div class="col-xs-12 col-sm-6">';
                    $labels = array('Team', 'Diff', 'Top Players');
                    tablehead('Kickers', $labels);

                    foreach ($new_comp_pk as $team => $value):
                        $printpk .='<tr><td>'.$team.'</td>';
                        $printpk .='<td>'.$value.'</td>';
                        $transient_pk = get_transient( $team.'_PK_leaders' );
                        if( ! empty( $transient_pk  ) ) {
                            $printpk .='<td>'.$transient_pk.'</td></tr>';
                        } else {
                            $top_team_pk_csv = print_array_as_csv(3, $team, 'PK');
                            set_transient( $team.'_PK_leaders', $top_team_pk_csv, 600000 );
                            $printpk .='<td>'.$top_team_pk_csv.'</td></tr>';
                        }
                    endforeach;

                    echo $printpk;

                    tablefoot('');
                    echo '</div>';


                    ?>
                </div>

                </div>

            </div>
        </div>
</div>



    </div><!--End page content-->

</div><!--END CONTENT CONTAINER-->


<?php include_once('main-nav.php'); ?>
<?php include_once('aside.php'); ?>

</div>

<?php get_footer(); ?>