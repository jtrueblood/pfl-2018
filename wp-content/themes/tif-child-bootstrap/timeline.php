<?php
/*
 * Template Name: Timeline
 * Description: Page for displaying history timeline
 */
 ?>

<!-- In Dec of 2017 this template was switched over to pull data from mysql not from cached files.  -->
<!-- Make the required arrays and cached files availible on the page
Page uses the following transients to manage the large computational loops required to get player data...
player_win_milestone	7 days
player_high_weeks	7 days
player_pts_milestone	7 days
-->
<?php 
$season = date("Y");

$playerassoc = get_players_assoc();
$weeks = the_weeks_with_post();
$reg_season = the_weeks();
$notes = get_field('custom_timeline');

foreach ($notes as $note):
    $newnote[$note['week_id']][] = $note['note'];
endforeach;

$champs = get_champions();
foreach ($champs as $key => $champ):
    $sixteen = 16;
    $newchamp[$key.$sixteen] = $champ;
endforeach;

$teams = get_teams();
//printr($teams, 0);

$probowl = probowl_details();
foreach ($probowl as $key => $pro):
    $seventeen = 17;
    $egad = $pro['egad'];
    $dgas = $pro['dgas'];
    if($egad > $dgas):
        $score = 'EGAD '.$egad.' - '. 'DGAS '.$dgas;
    else:
        $score = 'DGAS '.$dgas.' - '. 'EGAD '.$egad;
    endif;
    $newpro[$key.$seventeen] = array(
            'winner' => $pro['winner'],
            'score' => $score
    );
endforeach;

$awards = get_awards();
foreach ($awards as $key => $value):
    $fourteen = 14;
    $fifteen = 15;
    $sixteen = 16;
    $seventeen = 17;
    $roty[$key.$fourteen] = $value['roty'.$key]['pid'];
    $ooty[$key.$fifteen] = $value['ooty'.$key]['owner'];
    $mvp[$key.$fourteen] = $value['mvp'.$key]['pid'];
    $pbmvp[$key.$sixteen] = $value['pbm'.$key]['pid'];
    $promvp[$key.$seventeen] = $value['pro'.$key]['pid'];
    $hall[$key.$seventeen] = $value['hall'.$key]['pid'];
endforeach;

$brady = get_player_career_stats('2001BradQB');

function playerwincumulative(){
    $playerassoc = get_players_assoc();
        $trans = get_transient('player_win_milestone');
        if (!empty($trans)) {
            return $trans;
        } else {
            foreach ($playerassoc as $key => $value):
                $playerdata = get_player_career_stats($key);
                if ($playerdata['winmilestone']):
                    $playerwincum[$key] = $playerdata['winmilestone'];
                endif;
            endforeach;
            set_transient('player_win_milestone', $playerwincum, WEEK_IN_SECONDS);
            return $playerwincum;
    }
}

function playerptscumulative(){
    $playerassoc = get_players_assoc();
    $trans = get_transient('player_pts_milestone');
    if (!empty($trans)) {
        return $trans;
    } else {
        foreach ($playerassoc as $key => $value):
            $playerdata = get_player_career_stats($key);
            if ($playerdata['pointsmilestone']):
                $playerptscum[$key] = $playerdata['pointsmilestone'];
            endif;
        endforeach;
        set_transient('player_pts_milestone', $playerptscum, WEEK_IN_SECONDS);
        return $playerptscum;
    }
}

$ppc = playerptscumulative();
$pwc = playerwincumulative();
$bswoty = get_bswins();
$grandslams = get_grandslams();
//

function get_player_inc_highs(){
    $reg_season = the_weeks();
    $trans = get_transient('player_high_weeks');
    if (!empty($trans)) {
        return $trans;
    } else {
        foreach ($reg_season as $week):
            $box = get_boxscore_by_week($week);
            if ($box):
                foreach ($box as $key => $value):

                    $playerpts[$week][] = array(
                        $value['qb1']['pid'] => $value['qb1']['points'],
                        $value['rb1']['pid'] => $value['rb1']['points'],
                        $value['wr1']['pid'] => $value['wr1']['points'],
                        $value['pk1']['pid'] => $value['pk1']['points'],
                        $value['qb2']['pid'] => $value['qb2']['points'],
                        $value['rb2']['pid'] => $value['rb2']['points'],
                        $value['wr2']['pid'] => $value['wr2']['points'],
                        $value['pk2']['pid'] => $value['pk2']['points'],
                    );
                endforeach;
            endif;

        endforeach;
        set_transient('player_high_weeks', $playerpts, WEEK_IN_SECONDS);
        return $playerpts;
    }
}

$playerpts = get_player_inc_highs();

// Get postseason team scoring records using postseason function
$postseason_data = get_postseason();
$postseason_scores = array();
$postseason_high = 0;
$postseason_player_scores = array();
$postseason_player_high = 0;

// Group postseason scores by week and team, and track individual player scores
foreach($postseason_data as $game):
    // Pad week to 2 digits
    $week_padded = str_pad($game['week'], 2, '0', STR_PAD_LEFT);
    $weekid = $game['year'].$week_padded;
    $team = $game['team'];
    $playerid = $game['playerid'];
    $player_score = floatval($game['score']);
    
    // Store player scores
    if(!isset($postseason_player_scores[$weekid])):
        $postseason_player_scores[$weekid] = array();
    endif;
    $postseason_player_scores[$weekid][] = array(
        'playerid' => $playerid,
        'score' => $player_score
    );
    
    // Get team total points for this game
    $team_points = get_playoff_points_by_team_year($game['year'], $team, $game['week']);
    if($team_points > 0):
        // Only store each team once per week (avoid duplicates from multiple players)
        if(!isset($postseason_scores[$weekid][$team])):
            $postseason_scores[$weekid][$team] = $team_points;
        endif;
    endif;
endforeach;

foreach ($playerpts as $week => $team):
    foreach ($team as $key => $value):
        foreach($value as $k => $v):
        $pos = substr($k, -2);
        if($pos == 'QB'):
            $theqb[$week][$k] = $v;
        endif;
        if($pos == 'RB'):
            $therb[$week][$k] = $v;
        endif;
        if($pos == 'WR'):
            $thewr[$week][$k] = $v;
        endif;
        if($pos == 'PK'):
            $thepk[$week][$k] = $v;
        endif;
        endforeach;
    endforeach;
endforeach;

foreach($theqb as $key => $value):
    $maxval = max($value);
    $maxKey = array_search($maxval, $value);
    $maxqb[$key][$maxKey] = $maxval;
endforeach;
foreach($therb as $key => $value):
    $maxval = max($value);
    $maxKey = array_search($maxval, $value);
    $maxrb[$key][$maxKey] = $maxval;
endforeach;
foreach($thewr as $key => $value):
    $maxval = max($value);
    $maxKey = array_search($maxval, $value);
    $maxwr[$key][$maxKey] = $maxval;
endforeach;
foreach($thepk as $key => $value):
    $maxval = max($value);
    $maxKey = array_search($maxval, $value);
    $maxpk[$key][$maxKey] = $maxval;
endforeach;

//printr($playerpts, 0);

//$testbox = get_boxscore_by_week(199102);
//printr($maxwr, 0);
?>

<?php get_header(); ?>

<div class="boxed">
			
    <!--CONTENT CONTAINER-->
    <div id="content-container">

        <div id="page-title">
            <?php while (have_posts()) : the_post(); ?>
                <h1 class="page-header text-bold">PFL Timeline</h1>
            <?php endwhile; wp_reset_query(); ?>
        </div>

        <!--Page content-->
        <div id="page-content">

            <div class="timeline-content">
                <h3>List of Ideas</h3>
                <p>Best Individual Season by a Player</p>
                <p>Highest team game score</p>
                <p>Highest team season score</p>
                <p>Largest Margin of vicory</p>

                <?php
                foreach ($weeks as $week):
                    $a = substr($week, 0,4);
                    $b = substr($week, 4,2);
                    $c = ltrim($b, '0');
                    echo '<div class="row the-week">
                        <div class="col-xs-24">
                            <!-- LABEL -->
                            <span class="week-label">Week '.$b.', '.$a.'</span>';
                            echo '<span class="label label-dark label-plus">+</span>';
                            // General Note
                            if($newnote[$week]):
                                foreach($newnote[$week] as $id => $value):
                                    echo '<span class="label label-default">'.$value.'</span>';
                                endforeach;
                            endif;
                            // PFL Champ
                            if($newchamp[$week]):
                                $score = $newchamp[$week]['winner'].' '.$newchamp[$week]['win_pts'].' - '.$newchamp[$week]['loser'].' '.$newchamp[$week]['lose_pts'].' ('.$newchamp[$week]['location'].')';
                                echo '<a class="label label-primary add-tooltip" data-toggle="tooltip" href="#" data-original-title="'.$score.'">Posse Bowl '.$newchamp[$week]['numeral'].' - '.$teams[$newchamp[$week]['winner']]['team'].' PFL Champs</a>';
                            endif;
                            // Posse Bowl MVP
                            if($pbmvp[$week]):
                                echo '<span class="label label-mint">PB MVP: '.pid_to_name($pbmvp[$week], 1).'</span>';
                            endif;
                            // Hall of Fame
                            if($hall[$week]):
                                echo '<span class="label label-mint">Hall of Fame: '.pid_to_name($hall[$week], 1).'</span>';
                            endif;
                            // Owner of the Year
                            if($ooty[$week]):
                                echo '<span class="label label-mint">OOTY: '.$ooty[$week].'</span>';
                            endif;
                            // Probowl
                            if($newpro[$week]):
                                echo '<span class="label label-primary">Pro Bowl: '.$newpro[$week]['score'].'</span>';
                            endif;
                            // Probowl MVP
                            if($promvp[$week]):
                                echo '<span class="label label-mint">Pro Bowl MVP: '.pid_to_name($promvp[$week], 1).'</span>';
                            endif;
                            // Most Valuable Player
                            if($mvp[$week]):
                                echo '<span class="label label-mint">Season MVP: '.pid_to_name($mvp[$week], 1).'</span>';
                            endif;
                            // Rookie of the Year
                            if($roty[$week]):
                                echo '<span class="label label-mint">Rookie: '.pid_to_name($roty[$week], 1).'</span>';
                            endif;
                            // BS WIN of the Year
                            if($bswoty[$week]):
                                echo '<span class="label label-info">BS WIN: '.$bswoty[$week]['winner'].'</span>';
                            endif;

                            // Individual Player New QB High
                            if($week != 199101):
                                if($maxqb[$week]):
                                   foreach ($maxqb[$week] as $key => $value):
                                       if($q <= $value):
                                           echo '<span class="label label-purple add-tooltip" data-toggle="tooltip" href="#" data-original-title="New QB Week High after '.$qbw.' weeks.">'.pid_to_name($key, 1).' - '.$value.' Points</span>';
                                           $q = $value;
                                           $qbw = 0;
                                       endif;
                                       $qbw++;
                                   endforeach;
                                endif;
                                // Individual Player New RB High
                                if($maxrb[$week]):
                                    foreach ($maxrb[$week] as $key => $value):
                                        if($r <= $value):
                                            echo '<span class="label label-purple add-tooltip" data-toggle="tooltip" href="#" data-original-title="New RB Week High after '.$rbw.' weeks.">'.pid_to_name($key, 1).' - '.$value.' Points</span>';
                                            $r = $value;
                                            $rbw = 0;
                                        endif;
                                        $rbw++;
                                    endforeach;
                                endif;
                                // Individual Player New WR High
                                if($maxwr[$week]):
                                    foreach ($maxwr[$week] as $key => $value):
                                        if($i <= $value):
                                            echo '<span class="label label-purple add-tooltip" data-toggle="tooltip" href="#" data-original-title="New WR Week High after '.$wrw.' weeks.">'.pid_to_name($key, 1).' - '.$value.' Points</span>';
                                            $i = $value;
                                            $wrw = 0;
                                        endif;
                                        $wrw++;
                                    endforeach;
                                endif;
                                // Individual Player New PK High
                                if($maxpk[$week]):
                                    foreach ($maxpk[$week] as $key => $value):
                                        if($k <= $value):
                                            echo '<span class="label label-purple add-tooltip" data-toggle="tooltip" href="#" data-original-title="New PK Week High after '.$pkw.' weeks.">'.pid_to_name($key, 1).' - '.$value.' Points</span>';
                                            $k = $value;
                                            $pkw = 0;
                                        endif;
                                        $pkw++;
                                    endforeach;
                                endif;
                            endif; // end check of not week 1, 1991
                            // Grandslams
                            foreach ($grandslams as $key => $value):
                                if($value['weekid'] == $week):
                                    echo '<span class="label label-info">Grand Slam: '.$value['teamid'].'</span>';
                                endif;
                            endforeach;
                            
                            // Postseason Team Scoring Record
                            $weeknum = intval(substr($week, 4, 2));
                            $year = intval(substr($week, 0, 4));
                            if($year >= 1992 && ($weeknum == 15 || $weeknum == 16) && isset($postseason_scores[$week])):
                                foreach($postseason_scores[$week] as $team => $score):
                                    if($postseason_high <= $score):
                                        $weektype = ($weeknum == 15) ? 'Semifinal' : 'Posse Bowl';
                                        echo '<span class="label label-warning">Postseason Record: '.$teams[$team]['team'].' - '.$score.' pts ('.$weektype.')</span>';
                                        $postseason_high = $score;
                                    endif;
                                endforeach;
                            endif;
                            
                            // Postseason Individual Player Scoring Record
                            if($year >= 1992 && ($weeknum == 15 || $weeknum == 16) && isset($postseason_player_scores[$week])):
                                foreach($postseason_player_scores[$week] as $player_data):
                                    if($postseason_player_high <= $player_data['score']):
                                        $weektype = ($weeknum == 15) ? 'Semifinal' : 'Posse Bowl';
                                        echo '<span class="label label-warning">Postseason Player Record: '.pid_to_name($player_data['playerid'], 1).' - '.$player_data['score'].' pts ('.$weektype.')</span>';
                                        $postseason_player_high = $player_data['score'];
                                    endif;
                                endforeach;
                            endif;

                            // Player Career Point Milestones
                            if($ppc):
                                foreach ($ppc as $pid => $value):
                                    foreach ($value as $w => $pts):
                                        if($week == $w):
                                            echo '<span class="label label-pink">'.pid_to_name($pid, 1).' - '.number_format($pts, 0).' Career Pts</span>';
                                        endif;
                                    endforeach;
                                endforeach;
                            endif;
                            // Player Wins Point Milestones
                            if($pwc):
                                foreach ($pwc as $pid => $value):
                                    foreach ($value as $w => $wins):
                                        if($week == $w):
                                            echo '<span class="label label-purple">'.pid_to_name($pid, 1).' - '.$wins.' Career Wins</span>';
                                        endif;
                                    endforeach;
                                endforeach;
                            endif;
                            // Player High Points Milestones

                            //echo '<span class="label label-pink">Test Here</span>';
                        echo '</div>
                    </div>';

                endforeach;
                ?>
            </div>

        </div><!--End page content-->

    </div><!--END CONTENT CONTAINER-->


<?php include_once('main-nav.php'); ?>
<?php include_once('aside.php'); ?>

</div>
</div>

<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>