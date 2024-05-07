<?php
/*
 * Template Name: Build Something
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
$weeks = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14);
$playersassoc = get_players_assoc();

//printr($playersassoc, 0);

 ?>
<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <H3>Build Something Ideas</H3>
            <p>1. Cumulative rosters by team and season.  Include how player was aquired and how many points, games they had for that team.</p>
            <p>2. Rebuild / Fix Head to Head Matrix</p>

            <div class="row">
                <div class="col-xs-24 col-sm-8">

                <?php
                function check_nfl_pfl_score_diff($pid){
                    global $wpdb;
                    $get_player_diff = $wpdb->get_results("select * from $pid", ARRAY_N);
                    foreach ($get_player_diff as $key => $value){
                        if($value[26] != 0):
                            if($value[26] != 1):
                                $scorediff[$value[0]] = $value[26];
                            endif;
                        endif;
                    }
                    return $scorediff;
                }

                $i = 0;
                foreach ($playersassoc as $key => $value){
                    $thedifference[$key] = check_nfl_pfl_score_diff($key);
                }

                foreach ($thedifference as $key => $value){
                    if($value):
                        $thedifferencenow[$key] = $value;
                        $i++;
                    endif;
                }

                echo '<h2>'.$i.'</h2>';
                printr($thedifferencenow, 1);


                ?>

                </div>
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