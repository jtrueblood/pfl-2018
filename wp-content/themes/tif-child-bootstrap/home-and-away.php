<?php
/*
 * Template Name: Home and Away
 * Description: Stuff Goes Here
 */

// IDEAS:  1. Scorigami checking finction.  Pass game id to see if the game is a scorigami.  '2010ETSWRZ'.  Would need so save the event to a db 'wp_check_scorigami'
// Would also need to step through each week to save historical data, then make a function that checks it moving forward.
 ?>

<?php get_header();

$seasons = the_seasons();
$teams = get_teams();
$playerid = $_GET['id'];
$season = $_GET['season'];
$weeks = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14');
$weekids = the_weeks();
$prevweeks = the_weeks_with_key();


// find home and home series games
$checkaweek_a = $schedule[202413];
//$checkaweek_b = $schedule[202414];
//$myweekall = checkheadhead(202414);

//$boxscore = get_team_boxscore_by_week(199712, 'CMN');
//printr($boxscore, 1);

$newweeks = the_weeks();
// $fruit = array_shift($newweeks);
foreach ($newweeks as $week):
    $myweekall[$week] = checkheadhead($week);
endforeach;

foreach ($myweekall as $key => $value):
    if($value):
        foreach ($value as $k => $v):
            if($v == 1):
                $matchup = explode('-', $k);
                $headgames[$key][$matchup[0]] = $matchup[1];
            endif;
        endforeach;
    endif;
endforeach;

foreach ($headgames as $key => $value):
    $previous = $prevweeks[$key - 1];
    if (is_array($value)):
        foreach ($value as $k => $v):
            $thegames[$key][] = array(
                'firstend' => array(
                    $k => get_team_boxscore_by_week($previous, $k)
                ),
                'secondend' => array(
                    $v => get_team_boxscore_by_week($key, $v)
                )
            );
        endforeach;
    endif;
endforeach;


//printr($playersassoc, 0);

 ?>
<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->

            <?php
            //printr($thegames, 0);
            ?>

            <div class="col-xs-24">
            <?php

                //loop through all weeks

                foreach($thegames as $key => $thegame):
                    if(is_array($thegame) && $thegame):
                        foreach ($thegame as $k => $v):
                            $result = array();
                            echo '<div class="row">';
                            foreach ($v as $mykey => $gamer):
                                if (is_array($gamer)):
                                    foreach ($gamer as $t => $z):
                                        if($z['versus_pts'] < $z['points']):
                                            $result['gameone'] = 'win';
                                        else:
                                            $result['gameone'] = 'loss';
                                        endif;
                                        $printwk = 'Week '.$z['week'].', '.$z['season'];
                                        echo '<div class="col-xs-24 col-sm-2 col-md-4 col-lg-6">';
                                        $labels = array('Home', '', 'Away', '');
                                        tablehead($printwk, $labels);
                                        $tableprint .='<tr><td class="'.$winh.'">'.team_long($z['versus']).'</td>';
                                        $tableprint .='<td class="min-width">'.$z['versus_pts'].'</td>';
                                        $tableprint .='<td class="'.$winr.'">'.team_long($z['team_int']).'</td>';
                                        $tableprint .='<td class="min-width">'.$z['points'].'</td></tr>';
                                        echo $tableprint;
                                        $tableprint = '';
                                        tablefoot('');
                                        echo '</div>';
                                    endforeach;
                                endif;
                            endforeach;
                            echo '<div class="col-xs-24 col-sm-2 col-md-4 col-lg-6">';
                            printr($result, 0);
                            echo '</div>';
                            echo '</div>';
                        endforeach;
                    endif;
                endforeach;

            ?>

            </div>
        </div>

    <?php

    ?>

    </div><!--End page content-->

</div><!--END CONTENT CONTAINER-->


<?php include_once('main-nav.php'); ?>
<?php include_once('aside.php'); ?>

</div>

    <?php
    $jusplayerids = just_player_ids();
    $currentid = array_search($playerid, $jusplayerids);
    $nextplayer = $jusplayerids[$currentid + 1];
    $holeplayer = $jusplayerids[$currentid + 2];
    ?>

    <script>

        // DISABLE TO STOP AUTO RELOAD

        //setTimeout(function(){
        //	var reloadpage = '/build-something/?id=<?php //echo $nextplayer; ?>//';
        //    window.location.href = reloadpage;
        // }, 3000);

    </script>


    <script>
        var reloadpage = '/build-something/?id=<?php echo $nextplayer; ?>';
        console.log(reloadpage);
    </script>

<?php get_footer(); ?>