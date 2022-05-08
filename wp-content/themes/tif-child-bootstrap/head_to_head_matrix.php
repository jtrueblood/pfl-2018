<?php
/*
 * Template Name: Head to Head
 * Description: Head to Head Matrix
 */

get_header();

$teamlist = teamlist();

//printr($pep_vs, 0);

function get_head_to_matrix(){
    global $wpdb;
    //$RBS = $wpdb->get_results("select * from wp_team_RBS", ARRAY_N);
    $query = $wpdb->get_results("SELECT * FROM wp_head_matrix", ARRAY_N );
    return $query;
}

$tableget = get_head_to_matrix();

foreach ($tableget as $value):
    $matrix_list[$value[0]] = json_decode($value[1]);
endforeach;

foreach ($teamlist as $k => $name):
    foreach ($matrix_list as $key => $value):
        $cleanedup[$k][$key] = $value->$k;
    endforeach;
endforeach;

arsort($cleanedup);

?>
<!--CONTENT CONTAINER-->
<div class="boxed">

<!--CONTENT CONTAINER-->
<!--===================================================-->
    <div id="content-container">

        <!--Page content-->
        <!--===================================================-->
        <div id="page-content">

            <div class="row">

                <div class="col-xs-24">

                <div class="panel">
                    <div class="panel-heading">
                        <h3 class="panel-title">Head to Head Record <small>(regular season)</small></h3>
                    </div>
                    <div class="panel-body text-center">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center"></th>
                                        <?php
                                        foreach($cleanedup as $key => $value):
                                            echo '<th class="text-center">'.$key.'</th>';
                                        endforeach;
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php

                                    foreach($cleanedup as $key => $value):
                                        //arsort($value);
                                        echo '<tr>';
                                            echo '<td class="text-bold">'.$key.'</td>';
                                                if($value['PEP']):
                                                echo '<td class="text-center">'.$value['PEP'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['WRZ']):
                                                echo '<td class="text-center">'.$value['WRZ'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['BUL']):
                                                echo '<td class="text-center">'.$value['BUL'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['ETS']):
                                                    echo '<td class="text-center">'.$value['ETS'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['TSG']):
                                                echo '<td class="text-center">'.$value['TSG'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;



                                                if($value['SNR']):
                                                echo '<td class="text-center">'.$value['SNR'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['CMN']):
                                                echo '<td class="text-center">'.$value['CMN'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['PHR']):
                                                echo '<td class="text-center">'.$value['PHR'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['HAT']):
                                                echo '<td class="text-center">'.$value['HAT'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['ATK']):
                                                echo '<td class="text-center">'.$value['ATK'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['DST']):
                                                echo '<td class="text-center">'.$value['DST'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['SON']):
                                                echo '<td class="text-center">'.$value['SON'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['RBS']):
                                                echo '<td class="text-center">'.$value['RBS'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['BST']):
                                                echo '<td class="text-center">'.$value['BST'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;

                                                if($value['MAX']):
                                                echo '<td class="text-center">'.$value['MAX'].'</td>';
                                                else :
                                                    echo '<td class="text-center" style="background-color: #ddd;"></td>';
                                                endif;
                                        echo '</tr>';
                                    endforeach;
                                ?>
                                </tbody>
                            </table>
                            <p>The number to the left in each box belongs to the team on the left.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<?php // printr($cleanedup, 0); ?>

<?php get_footer(); ?>