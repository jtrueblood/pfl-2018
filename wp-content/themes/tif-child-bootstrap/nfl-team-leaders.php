<?php
/*
 * Template Name: NFL Team Leaders
 * Description: Stuff Goes Here
 */

// IDEAS:  1. Scorigami checking finction.  Pass game id to see if the game is a scorigami.  '2010ETSWRZ'.  Would need so save the event to a db 'wp_check_scorigami'
// Would also need to step through each week to save historical data, then make a function that checks it moving forward.
 ?>

<?php get_header();

$seasons = the_seasons();
$teams = get_teams();
$nflteams = all_nfl_teams();
$playerid = $_GET['id'];
$season = $_GET['season'];
$weeks = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14);



//printr($playersassoc, 0);

 ?>
<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <div class="row" style="margin-top: 20px;">
                <div class="col-xs-24">
                <?php

                function playerlistbynflteam ($teamvar){
                    $playersassoc = get_players_assoc();
                    foreach ($playersassoc as $key => $value){
                        if($value):
                            $playerdata[$key] = get_player_nfl_team_by_week($key);
                        endif;
                    }

                    foreach ($playerdata as $key => $value):
                        if(is_array($value)):
                            $playerdata[$key] = array_filter($value);
                            $filter = array_filter($value);
                            $chk = in_array($teamvar, $filter);
                            if($chk):
                                $teamplayers[$key] = array_count_values($filter);
                            endif;
                        endif;
                    endforeach;

                    foreach ($teamplayers as $key => $value):
                        $teamplayers[$key] = $value[$teamvar];
                    endforeach;
                    arsort($teamplayers);
                    return $teamplayers;
                }

                $nflteams = all_nfl_teams();

                foreach ($nflteams as $key => $value):
                    $simpteamarr[] = $key;
                endforeach;

                foreach ($simpteamarr as $key => $value):
                    $teamplayers = playerlistbynflteam($value);
                    $storeplayers[$value] = array_slice($teamplayers, 0, 20, true);
                endforeach;

                foreach($storeplayers as $key => $value):
                    $teamname = get_nfl_full_team_name_from_id($key);
                    foreach ($value as $k => $v):
                        $position = pid_to_position($k);
                        $playername = pid_to_name($k, $position);
                        $forprint[$key][$playername] = $v;
                    endforeach;
                endforeach;

               // printr($forprint, 1);
                echo '<div class="row">';
                foreach ($forprint as $key => $value){
                    echo '<div class="col-xs-24 col-md-6">';
                    $labels = array('Player', 'Games');
                    tablehead($nflteams[$key], $labels);

                    foreach ($value as $k => $v){

                        $nflteamprint .='<tr><td>'.$k.'</td>';
                        $nflteamprint .='<td class="min-width text-right">'.$v.'</td></tr>';

                    }

                    echo $nflteamprint;
                    $nflteamprint = '';
                    tablefoot('');
                    echo '</div>';
                }


                ?>

                </div>
                </div>
            </div>
        </div><!--END CONTENT CONTAINER-->

    <?php include_once('main-nav.php'); ?>
    <?php include_once('aside.php'); ?>

</div><!--END BOXED-->

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