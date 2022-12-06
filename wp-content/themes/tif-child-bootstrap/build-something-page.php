<?php
/*
 * Template Name: Two Point Conversion Check
 * Description: Used for trying to idenitfy and assign differences between the PFL score and the NFL calculated score froNFL game data.
 */
 ?>

<?php get_header(); ?>

<?php
  $teamloop = array('PEP','WRZ','ETS');

    function get_everything ($teamid)
    {
        $seasons = the_seasons();
        $postseason = get_team_postseason($teamid);
        $playoffs = get_playoffs();

        foreach ($postseason as $value):
            $newplayoff[$value['year'].$value['week']][$value['position'].$value['overtime']] = $value;
        endforeach;

        foreach ($newplayoff as $key => $value):
            $newplayoffformat[$key] = array(
                'id' => $key,
                'season' => $value['QB0']['year'],
                'week' => $value['QB0']['week'],
                'team_int' => $teamid,
                'points' => '',
                'versus' => '',
                'versus_pts' => '',
                'home_away' => '',
                'stadium' => '',
                'result' => $teampostseason[$key],
                'totalscore' => '',
                'qb1' =>  $value['QB0']['playerid'],
                'rb1' =>  $value['RB0']['playerid'],
                'wr1' =>  $value['WR0']['playerid'],
                'pk1' =>  $value['PK0']['playerid'],
                'overtime' =>  $value['QB0']['overtime'],
                'qb2' =>  $value['QB1']['playerid'],
                'rb2' =>  $value['RB1']['playerid'],
                'wr2' =>  $value['WR1']['playerid'],
                'pk2' =>  $value['PK1']['playerid'],
                'extra_ot' =>  ''
            );
        endforeach;

        $teamresults = get_team_results_expanded_new($teamid);
        foreach ($teamresults as $key => $value):
            $year = substr($key, 0, 4);
            foreach ($seasons as $season):
                if($season == $year):
                    $yearresults[$season][$key] = $value;
                endif;
            endforeach;
        endforeach;

        return $playoffs;
    }

    $everything = get_everything('ETS');
    printr($everything, 0);

    $seasons = the_seasons();
    $champs = get_champions();

    $teamawards = get_award_team($teamid);
    if(isset($teamawards)){
        foreach($teamawards as $key => $item){
            $arr_taward[$item['award']][$key] = $item;
        }

        ksort($arr_taward, SORT_NUMERIC);
    }

    foreach ($seasons as $year){
        $stand[$year] = get_standings_by_team($year, $teamid);
    }

    foreach ($stand as $key => $value){
        $diffo = $pts - $value[0]['ptsvs'];

        $highpts[] = $value[0]['pts'];
        $highdiff[] = $diffo;
        $wins[] = $value[0]['win'];
        $loss[] = $value[0]['loss'];
    }

    foreach($champs as $key => $item){
        $arr_champs[$item['winner']][$key] = $item;
    }
    ksort($arr_champs, SORT_NUMERIC);

    $teamchamps = $arr_champs[$teamid];

    // all seasons that team played
    if(isset($teamawards)){
        foreach($teamawards as $key => $item){
            $team_award_year[$item['year']][$key] = $item;
        }

        ksort($team_award_year, SORT_NUMERIC);
    }


    $number_ones = get_number_ones();

    if(isset($number_ones)){
        foreach($number_ones as $key => $item){
            $newkey = substr($key, 2, -1);
            if($item['teams'] == $teamid){
                $ones[$newkey][] = $item;
            }
        }
    }

    if(isset($ones)){
        ksort($ones, SORT_NUMERIC);
    }


    // get notes from Teams page
    if( have_rows('timeline_notes') ):
        while ( have_rows('timeline_notes') ) : the_row();
            $repteam = get_sub_field('teamid');
            if($repteam == $teamid){
                $notes[get_sub_field('year')] = get_sub_field('note');
            }
        endwhile;
    else :
        $notes = array();
    endif;

    $helmethist = get_helmet_name_history();
    foreach ($helmethist as $value){
        $helmet[$value['team']][$value['year']] = $value;
    }

    $teamhelmet = $helmet[$teamid];

    foreach ($stand as $key => $value){
        $team_timeline[$teamid][$key] = array(
            'standings' => $value[0],
            'champions' => $teamchamps[$key],
            'awards' => $team_award_year[$key],
            'number_ones' => $ones[$key],
            'notes' => $notes[$key],
            'helmets' => $teamhelmet[$key]
        );
    }
 ?>

<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <div id="page-content">

                <?php


                ?>

                <div class="col-xs-4">
                        <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title"><?php echo $teamid; ?> History Timeline</h3>

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