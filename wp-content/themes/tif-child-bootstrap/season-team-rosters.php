<?php
/*
 * Template Name: Season Team Rosters
 * Description: Stuff Goes Here
 */

// IDEAS:  1. Scorigami checking finction.  Pass game id to see if the game is a scorigami.  '2010ETSWRZ'.  Would need so save the event to a db 'wp_check_scorigami'
// Would also need to step through each week to save historical data, then make a function that checks it moving forward.
 ?>

<?php get_header();

$seasons = the_seasons();
$teams = get_teams();
$playerid = $_GET['id'];
// SET THE SEASON VALUE IN THE URL
$season = $_GET['season'];
$weeks = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14);
$nonidplayers = get_non_pfl_players();
//$weeks = array(1,2,3,14);

//$rosters = get_all_rosters();
$rostered_players = get_rostered_player('1992StoyPK');
//$rookie = get_player_rookie_year('');
$basic_info = get_team_results_expanded_new('1992StoyPK');
$teams_rostered = get_player_teams_rostered_by_season('2016HenrRB');
//printr($teams_rostered, 1);

function check_if_drafted_is_rostered($playerid, $season)
{
    $howaquired = how_player_was_acquired($playerid, $season, );
    $seeifplayerisrostered = get_rostered_player_by_season($playerid, $season);

}


printr($seeifplayerisrostered, 0);

// NEED TO FIGURE OUT -------------------------------------------------
// 1. CHECK IF PLAYER WAS DRAFTED BY NOT ON ROSTER TABLE eg. 2016HenrRB
// 2. CHECK IF A PLAYER PLAYED BY IS NOT ADDED TO ROSTER TABLE
// 3. FIGURE OUT A CLEANER WAY OF DETERMINING RETIRED YEAR -- less important

// Used to check if a player played in a PFL game in a given season, then inserts into the database.
//get_player_teams_by_season($playerid);

function insert_nopid_players($nonid, $first, $last, $pos, $team){
    global $wpdb;
    $wpdb->insert(
        'wp_rosters_nopid',
        array(
            'id' => $nonid,
            'firstname' => $first,
            'lastname' => $last,
            'position' => $pos,
            'team' => $team
        ),
        array(
            '%s','%s','%s','%s','%s'
        )
    );
}


function print_roster_table($season, $team){
    $getroster = get_rosters($season, $team);
    $teams = get_teams();

    $otplayers = get_overtime_games_players($team, $season);
    $otcount = 0;
    if($otplayers):
        foreach($otplayers as $otgames):
            foreach ($otgames as $key => $value):
                    if($value != 'None'):
                        $otcount++;
                    endif;
            endforeach;
        endforeach;
    endif;
    printr($otplayers, 0);
    //echo $otcount;

    $nones = count_the_nones($team, $season);
    echo 'Nones: '.$nones;
    //printr($getroster, 0);
    $labels = array('Player', 'Acquired', 'Points', 'Games');
    tablehead($teams[$team]['team'].' '.$season.' - Season Roster', $labels);
        $QB = $getroster['QB'];
        $printtheroster .='<tr><td class="text-bold">Quarterbacks</td></tr>';
        foreach ($QB as $player):
            $getstats = get_player_points_team_season($player, $team, $season);
            $points = $getstats ? array_sum($getstats) : '--';
            $games = $getstats ? count($getstats) : '--';

            $acquired = how_player_was_acquired($player, $season, $team);
            $countarr = count($acquired);
            if ($countarr == 2):
                $printacquired = $acquired['traded'].'  '.$acquired['protected'];
            else:
                $printacquired =  $acquired['traded'].$acquired['protected'].$acquired['drafted'].$acquired['freeagent'];
            endif;

            $firstdig = substr($player, 0, 1);
            if($firstdig != 0):
                $printtheroster .='<tr><td>'.pid_to_name($player, 0).' - '.$player.'</td>';
            else:
                $printtheroster .='<tr><td>'.nonpid_to_name($player, 1).' - '.$player.'</td>';
            endif;

            $printtheroster .='<td>'.$printacquired.'</td>';
            $printtheroster .='<td>'.$points.'</td>';
            $printtheroster .='<td>'.$games.'</td></tr>';
            $qbgamescount[] = $games;
        endforeach;
        $qbgames = array_sum($qbgamescount);
        if($qbgames == 14):
            echo '<h5 class="text-success">QB -'.$qbgames.'</h5>';
        else:
            echo '<h5>QB -'.$qbgames.'</h5>';
        endif;

        $RB = $getroster['RB'];
        $printtheroster .='<tr><td class="text-bold">Runningbacks</td></tr>';
        foreach ($RB as $player):
            $getstats = get_player_points_team_season($player, $team, $season);
            $points = $getstats ? array_sum($getstats) : '--';
            $games = $getstats ? count($getstats) : '--';

            $acquired = how_player_was_acquired($player, $season, $team);
            $countarr = count($acquired);
            if ($countarr == 2):
                $printacquired = $acquired['traded'].' / '.$acquired['protected'];
            else:
                $printacquired =  $acquired['traded'].$acquired['protected'].$acquired['drafted'].$acquired['freeagent'];
            endif;

            $firstdig = substr($player, 0, 1);
            if($firstdig != 0):
                $printtheroster .='<tr><td>'.pid_to_name($player, 0).' - '.$player.'</td>';
            else:
                $printtheroster .='<tr><td>'.nonpid_to_name($player, 1).' - '.$player.'</td>';
            endif;

            $printtheroster .='<td>'.$printacquired.'</td>';
            $printtheroster .='<td>'.$points.'</td>';
            $printtheroster .='<td>'.$games.'</td></tr>';
            $rbgamescount[] = $games;
        endforeach;
        $rbgames = array_sum($rbgamescount);
        if($rbgames == 14):
            echo '<h5 class="text-success">RB -'.$rbgames.'</h5>';
        else:
            echo '<h5>RB -'.$rbgames.'</h5>';
        endif;

        $WR = $getroster['WR'];
        $printtheroster .='<tr><td class="text-bold">Receivers</td></tr>';
        foreach ($WR as $player):
            $getstats = get_player_points_team_season($player, $team, $season);
            $points = $getstats ? array_sum($getstats) : '--';
            $games = $getstats ? count($getstats) : '--';

            $acquired = how_player_was_acquired($player, $season, $team);
            $countarr = count($acquired);
            if ($countarr == 2):
                $printacquired = $acquired['traded'].' / '.$acquired['protected'];
            else:
                $printacquired =  $acquired['traded'].$acquired['protected'].$acquired['drafted'].$acquired['freeagent'];
            endif;

            $firstdig = substr($player, 0, 1);
            if($firstdig != 0):
                $printtheroster .='<tr><td>'.pid_to_name($player, 0).' - '.$player.'</td>';
            else:
                $printtheroster .='<tr><td>'.nonpid_to_name($player, 1).' - '.$player.'</td>';
            endif;

            $printtheroster .='<td>'.$printacquired.'</td>';
            $printtheroster .='<td>'.$points.'</td>';
            $printtheroster .='<td>'.$games.'</td></tr>';
            $wrgamescount[] = $games;
        endforeach;
        $wrgames = array_sum($wrgamescount);
        if($wrgames == 14):
            echo '<h5 class="text-success">WR -'.$wrgames.'</h5>';
        else:
            echo '<h5>WR -'.$wrgames.'</h5>';
        endif;

        $PK = $getroster['PK'];
        $printtheroster .='<tr><td class="text-bold">Kickers</td></tr>';
        foreach ($PK as $player):
            $getstats = get_player_points_team_season($player, $team, $season);
            $points = $getstats ? array_sum($getstats) : '--';
            $games = $getstats ? count($getstats) : '--';

            $acquired = how_player_was_acquired($player, $season, $team);
            $countarr = count($acquired);
            if ($countarr == 2):
                $printacquired = $acquired['traded'].' / '.$acquired['protected'];
            else:
                $printacquired =  $acquired['traded'].$acquired['protected'].$acquired['drafted'].$acquired['freeagent'];
            endif;

            $firstdig = substr($player, 0, 1);
            if($firstdig != 0):
                $printtheroster .='<tr><td>'.pid_to_name($player, 0).' - '.$player.'</td>';
            else:
                $printtheroster .='<tr><td>'.nonpid_to_name($player, 1).' - '.$player.'</td>';
            endif;

            $printtheroster .='<td>'.$printacquired.'</td>';
            $printtheroster .='<td>'.$points.'</td>';
            $printtheroster .='<td>'.$games.'</td></tr>';
            $pkgamescount[] = $games;
        endforeach;
        $pkgames = array_sum($pkgamescount);
        if($pkgames == 14):
            echo '<h5 class="text-success">PK -'.$pkgames.'</h5>';
        else:
            echo '<h5>PK -'.$pkgames.'</h5>';
        endif;

        //This part finds out how many player slots a team should have.  Should be (14 * 4) = 56,
        // but you can reduce for the count of 'nones' at the starter spot and add in additional players (- nones) for OT games.
        $gamesum = $qbgames + $rbgames + $wrgames + $pkgames;
        $shouldgames = 56;
        $checkgames = $gamesum - $shouldgames;
        $addnones = $checkgames + $nones;
        $finalnonecount = $addnones - $otcount;
        if($finalnonecount == 0):
            echo '<h3 class="text-success">COMPLETE!!!</h3>';
        endif;
        echo $printtheroster;
        echo '<h5>'.$finalnonecount.'</h5>';

    tablefoot('* Player never played in a PFL game.');
}


 ?>
<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <H3>Team Rosters By Season</H3>
            <p>This page pulls in players that played for a team, were drafted by, or were at some point rostered in a given season</p>
            <div class="row">
                <div class="col-xs-24 col-sm-8">

                <?php
                //printr($nonidplayers, 0);
                //$countnones = count_the_nones('ETS', $season);
                //echo $countnones;
                //$justplayers = get_just_players_by_team('ETS');
                //printr($justplayers, 1);
                //$acquired = how_player_was_acquired('2023MoodPK', 2023);
                //printr($acquired, 0);
                //echo '<h4>'.$acquired.'</h4>';
                //print_roster_table(1991, 'ETS');
                echo '<hr>';
                echo '<h2>'.$playerid.'</h2>';

                //foreach ($seasons as $season):
                    echo '<h3>'.$season.'</h3>';
                    foreach($teams as $key => $value):
                        $getroster = get_rosters($season, $key);
                        if ($getroster != null):
                            echo '<h4>'.$key.'</h4>';
                            //printr($getroster, 0);
                            print_roster_table($season, $key);
                            $rosterseason[$season][$key] = $getroster;
                            $rosterflat = array_flatten($rosterseason);
                        endif;
                    endforeach;
                //endforeach;

                $yeardraft = get_drafts_by_year($season);
                foreach ($yeardraft as $key => $value):
                    if($value['playerid'] != ''):
                        if($value['playerid'] == 0):
                            $cleandraft_others[] = array(
                                'firstname' => $value['playerfirst'],
                                'lastname' => $value['playerlast'],
                                'position' => $value['position'],
                                'team' => $value['acteam']
                            );
                        else:
                            $cleandraft[] = $value['playerid'];
                        endif;
                    else:
                        $cleandraft_others[] = array(
                            'firstname' => $value['playerfirst'],
                            'lastname' => $value['playerlast'],
                            'position' => $value['position'],
                            'team' => $value['acteam']
                        );
                    endif;
                endforeach;

                //printr($cleandraft_others, 0);
                $checkdiff = array_diff($cleandraft, $rosterflat);

                echo '<p>Players that were drafted but did not play this season.  But have a PFL ID for some other reason.</p>';
                printr($checkdiff, 0);

                //echo $testplayerpick;

//                foreach($checkdiff as $guy):
//                    $testplayerpick = get_draft_player_team($guy, $season);
//                    insert_roster($guy, $testplayerpick, $season);
//                    echo '<p>'.$testplayerpick.'</p>';
//                endforeach;

                echo '<p>These players were drafted, but never played in a PFL game.  So therefore do not have a PFL ID or page.</p>';
                printr($cleandraft_others, 0);

                if($cleandraft_others):
                    foreach ($cleandraft_others as $key => $value):
                        $first = $value['firstname'];
                        $last = $value['lastname'];
                        $fourname = substr($value['lastname'], 0, 4);
                        $position = $value['position'];
                        $team = $value['team'];
                        $non_pfl_id = '0000'.$fourname.$position;
                        echo $non_pfl_id.'<br>';
                        //insert_nopid_players($non_pfl_id, $first, $last, $position, $team);
                        //insert_roster($non_pfl_id, $team, $season);
                    endforeach;
                endif;

                //printr($cleandraft, 0);
                printr($rosterflat, 0);

                echo '<p>These players did not play, and were not drafted, but were still included on the MFL .json BENCH and IR rosters from 2011-present</p>';

                foreach($teams as $theteam => $thevalue):
                    foreach($weeks as $week):
                        if($theteam):
                            $mflbench = get_the_bench($season, $week, $theteam);
                            if($mflbench['ROSTER']):
                                $mflbenchflat = array();
                                foreach ($mflbench['ROSTER'] as $key => $value):
                                    $mflbenchflat[] = $key;
                                endforeach;
                            endif;
                            if($mflbench['INJURED_RESERVE']):
                                $mflirflat = array();
                                foreach ($mflbench['INJURED_RESERVE'] as $key => $value):
                                    $mflirflat[] = $key;
                                endforeach;
                            endif;
                            if($mflbenchflat):
                                $mfl_bench_ir = array_merge($mflbenchflat, $mflirflat);
                                $mfl_filter = array_filter($mfl_bench_ir);
                            endif;
                            if($mfl_bench_ir):
                                $mfl_diff[$theteam] = array_diff($mfl_filter, $rosterflat);
                            endif;
                        endif;
                    endforeach;
                endforeach;

                //printr($rosterflat, 0);
                //printr($mfl_bench_ir, 0);
                //printr($mfl_diff, 0);
                printr($mfl_diff, 0);

                //insert the players that were on the MFL rosters but not in any PFL games or drafted
                foreach($mfl_diff as $team => $values):
                    if($values):
                        foreach($values as $player):
                            insert_roster($player, $team, $season);
                        endforeach;
                    endif;
                endforeach;

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