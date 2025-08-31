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
$weeks = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14');
$weekids = the_weeks();


//printr($playersassoc, 0);

 ?>
<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <H3>Build Something Ideas</H3>
            <p>1. Cumulative rosters by team and season.  Include how player was aquired and how many points, games they had for that team.</p>
            <p>2. Rebuild / Fix Head to Head Matrix</p>
            <p>Figure out Bye Weeks</p>


            <div class="row">
                <div class="col-xs-24 col-sm-8">
                    <?php
                    // find home and home series games
                    $checkaweek_a = $schedule[202413];
                    //$checkaweek_b = $schedule[202414];
                    //$myweekall = checkheadhead(202414);
                    //printr($myweekall, 0);

                    foreach ($weekids as $week):
                        $myweekall[$week] = checkheadhead($week);
                    endforeach;

                    printr($myweekall, 0);

                    ?>




                <?php

//                foreach($weekids as $week):
//                    $boxscore  = get_boxscore_by_week($week);
//                    foreach ($boxscore as $key => $value):
//                            $qb1 = $value['qb1']['points'];
//                            $rb1 = $value['rb1']['points'];
//                            $wr1 = $value['wr1']['points'];
//                            $pk1 = $value['pk1']['points'];
//                            $qb2 = $value['qb2']['points'];
//                            $rb2 = $value['rb2']['points'];
//                            $wr2 = $value['wr2']['points'];
//                            $pk2 = $value['pk2']['points'];
//
//                            if($qb1 === $rb1 && $rb1 === $wr1 && $wr1 === $pk1):
//                                echo '<p>'.$week.'-'.$qb1.'-'.$rb1.'-'.$wr1.'-'.$pk1.'- SAME!!</p>';
//                            else:
//                                echo '<p>'.$qb1.'-'.$rb1.'-'.$wr1.'-'.$pk1.'- xxx</p>';
//                            endif;
//                    endforeach;
//                endforeach;
//
//                printr ($justplayerscores, 1);

                $teamsCountByYear = [
                    1991 => 28,
                    1992 => 28,
                    1993 => 28,
                    1994 => 28,
                    1995 => 30,  // Addition of the Carolina Panthers and Jacksonville Jaguars
                    1996 => 30,
                    1997 => 30,
                    1998 => 30,
                    1999 => 31,  // Addition of the Cleveland Browns (reinstated after the original team moved to Baltimore in 1996)
                    2000 => 31,
                    2001 => 31,
                    2002 => 32,  // Addition of the Houston Texans (final expansion team)
                    2003 => 32,
                    2004 => 32,
                    2005 => 32,
                    2006 => 32,
                    2007 => 32,
                    2008 => 32,
                    2009 => 32,
                    2010 => 32,
                    2011 => 32,
                    2012 => 32,
                    2013 => 32,
                    2014 => 32,
                    2015 => 32,
                    2016 => 32,
                    2017 => 32,
                    2018 => 32,
                    2019 => 32,
                    2020 => 32,
                    2021 => 32,
                    2022 => 32,
                    2023 => 32,
                    2024 => 32
                ];

                $nflteams = all_nfl_teams();
                $flip = array_flip($nflteams);

                include_once('simplehtmldom/simple_html_dom.php');

                $yhtml = 1991;
                $whtml = 11;
                $html = file_get_html('https://www.nfl.com/schedules/'.$yhtml.'/reg'.$whtml.'/');

                //$html->find('#meta div p[3] span[1]', 0);
                if($html):
                    $byeweek = $html->find('.nfl-c-teams-on-bye', 0)->plaintext;
                endif;
                $string = preg_replace('/\s+/', ' ', $byeweek);
                $string = str_replace(' TEAMS ON BYE ', '', $string);
                $string = str_replace(' ', ',', $string);
                $data = str_getcsv($string);
                $data = array_filter($data);

                if($data[0] == 'No'):
                    printr('No Byes', 0);
                else:
                    $formatnum = sprintf('%02d', $whtml);
                    $weekvar = $yhtml.$formatnum;
                    $byeWeeks[$weekvar] = $data;

                    printr($byeWeeks, 0);
                endif;

                printr($flip, 0);

                foreach ($seasons as $key => $value):
                    $allbyeweeks[$value] = ${'byeWeeks'.$value};
                endforeach;

                //check if number is right
                foreach ($allbyeweeks as $key => $value):
                    if($value) {
                        if (count($value) != $allbyeweeksr[$key]) {
                            echo $key . ' ' . count($value) . ' ' . $teamsCountByYear[$key] . '<br>';
                        }
                    }
                endforeach;

                function getnflbye($year, $team){
                    global $allbyeweeks;
                    return $allbyeweeks[$year][$team];
                }

                $pid = '1991SmitRB';
                $getbye = getnflbye(1991, 'DAL');
                $getgames = get_player_team_games($pid);
                $team = $getteam[0][0];
                $playercheck = get_player_season_stats($pid, 1991);

                foreach($weeks as $week):
                    $getteam = get_player_team_played_week_nfl($pid, '1991'.$week);
                    $teamo = $getteam[0][0];
                    if(!isset($check[$week])){
                        $check[$week] = $teamo;
                    }
                endforeach;



                foreach($check as $key => $value):
                    $keynone = ltrim($key, '0');
                    if($keynone == $getbye) :
                        $newbye[$keynone] = 'BYE';
                    else:
                        $newbye[$keynone] = $check[$key];
                    endif;

                endforeach;


                //printr($newbye, 0);


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