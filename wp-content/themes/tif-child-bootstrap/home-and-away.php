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

            <div class="row" style="margin-top: 20px;">
            <div class="col-xs-24 col-sm-6">
            <?php

                //loop through all weeks
                $total_series = 0;
                $sweep_count = 0;
                $split_count = 0;
                $team_stats = array();

                foreach($thegames as $key => $thegame):
                    if(is_array($thegame) && $thegame):
                        foreach ($thegame as $k => $v):
                            echo '<div class="row">';
                            echo '<div class="col-xs-24">';
                            
                            $games_data = array();
                            foreach ($v as $mykey => $gamer):
                                if (is_array($gamer)):
                                    foreach ($gamer as $t => $z):
                                        $games_data[] = $z;
                                    endforeach;
                                endif;
                            endforeach;
                            
                            if(count($games_data) == 2):
                                $game1 = $games_data[0];
                                $game2 = $games_data[1];
                                $printwk = 'Week '.$game1['week'].' - '.$game2['week'].', '.$game1['season'];
                                $labels = array('Week', 'Home', '', 'Away', '');
                                tablehead($printwk, $labels);
                                
                                // Game 1
                                $winner1_home = ($game1['versus_pts'] > $game1['points']) ? '✓' : '';
                                $winner1_away = ($game1['points'] > $game1['versus_pts']) ? '✓' : '';
                                $tableprint .='<tr><td class="min-width">'.$game1['week'].'</td>';
                                $tableprint .='<td><strong>'.team_long($game1['versus']).'</strong> '.$winner1_home.'</td>';
                                $tableprint .='<td class="min-width">'.$game1['versus_pts'].'</td>';
                                $tableprint .='<td>'.team_long($game1['team_int']).' '.$winner1_away.'</td>';
                                $tableprint .='<td class="min-width">'.$game1['points'].'</td></tr>';
                                
                                // Game 2
                                $winner2_home = ($game2['versus_pts'] > $game2['points']) ? '✓' : '';
                                $winner2_away = ($game2['points'] > $game2['versus_pts']) ? '✓' : '';
                                $tableprint .='<tr><td class="min-width">'.$game2['week'].'</td>';
                                $tableprint .='<td><strong>'.team_long($game2['versus']).'</strong> '.$winner2_home.'</td>';
                                $tableprint .='<td class="min-width">'.$game2['versus_pts'].'</td>';
                                $tableprint .='<td>'.team_long($game2['team_int']).' '.$winner2_away.'</td>';
                                $tableprint .='<td class="min-width">'.$game2['points'].'</td></tr>';
                                
                                echo $tableprint;
                                $tableprint = '';
                                
                                // Determine series winner - track by actual team codes
                                $team_a = $game1['team_int'];
                                $team_b = $game1['versus'];
                                $team_a_wins = 0;
                                $team_b_wins = 0;
                                
                                // Initialize team stats if needed
                                if(!isset($team_stats[$team_a])):
                                    $team_stats[$team_a] = array('wins' => 0, 'losses' => 0);
                                endif;
                                if(!isset($team_stats[$team_b])):
                                    $team_stats[$team_b] = array('wins' => 0, 'losses' => 0);
                                endif;
                                
                                // Game 1 winner
                                if($game1['points'] > $game1['versus_pts']):
                                    $team_a_wins++;
                                    $team_stats[$team_a]['wins']++;
                                    $team_stats[$team_b]['losses']++;
                                else:
                                    $team_b_wins++;
                                    $team_stats[$team_b]['wins']++;
                                    $team_stats[$team_a]['losses']++;
                                endif;
                                
                                // Game 2 winner - teams are in opposite positions
                                if($game2['points'] > $game2['versus_pts']):
                                    // team_int won game 2
                                    if($game2['team_int'] == $team_a):
                                        $team_a_wins++;
                                        $team_stats[$team_a]['wins']++;
                                        $team_stats[$team_b]['losses']++;
                                    else:
                                        $team_b_wins++;
                                        $team_stats[$team_b]['wins']++;
                                        $team_stats[$team_a]['losses']++;
                                    endif;
                                else:
                                    // versus won game 2
                                    if($game2['versus'] == $team_a):
                                        $team_a_wins++;
                                        $team_stats[$team_a]['wins']++;
                                        $team_stats[$team_b]['losses']++;
                                    else:
                                        $team_b_wins++;
                                        $team_stats[$team_b]['wins']++;
                                        $team_stats[$team_a]['losses']++;
                                    endif;
                                endif;
                                
                                if($team_a_wins == 2):
                                    $summary = team_long($team_a).' won both';
                                    $sweep_count++;
                                elseif($team_b_wins == 2):
                                    $summary = team_long($team_b).' won both';
                                    $sweep_count++;
                                else:
                                    $summary = 'Series Split';
                                    $split_count++;
                                endif;
                                
                                $total_series++;
                                tablefoot($summary);
                            endif;
                            
                            echo '</div>';
                            echo '</div>';
                        endforeach;
                    endif;
                endforeach;

            ?>

            </div>
            
            <div class="col-xs-24 col-md-8">
            <?php
                // Calculate percentages
                $sweep_pct = $total_series > 0 ? round(($sweep_count / $total_series) * 100, 1) : 0;
                $split_pct = $total_series > 0 ? round(($split_count / $total_series) * 100, 1) : 0;
                
                $labels = array('Stat', 'Value');
                tablehead('Home & Away Series Summary', $labels);
                echo '<tr><td>Total Series</td><td>'.$total_series.'</td></tr>';
                echo '<tr><td>One Team Swept</td><td>'.$sweep_count.' ('.$sweep_pct.'%)</td></tr>';
                echo '<tr><td>Series Split</td><td>'.$split_count.' ('.$split_pct.'%)</td></tr>';
                tablefoot('');
                
                // Team performance table
                // Calculate win percentages and sort by them
                $team_performance = array();
                foreach($team_stats as $team => $stats):
                    $total_games = $stats['wins'] + $stats['losses'];
                    $win_pct = $total_games > 0 ? round(($stats['wins'] / $total_games) * 100, 1) : 0;
                    $team_performance[] = array(
                        'team' => $team,
                        'wins' => $stats['wins'],
                        'losses' => $stats['losses'],
                        'pct' => $win_pct
                    );
                endforeach;
                
                // Sort by win percentage descending
                usort($team_performance, function($a, $b) {
                    return $b['pct'] <=> $a['pct'];
                });
                
                echo '<br>';
                $labels = array('Team', 'W', 'L', 'Win %');
                tablehead('Team Performance', $labels);
                foreach($team_performance as $perf):
                    echo '<tr><td>'.team_long($perf['team']).'</td>';
                    echo '<td>'.$perf['wins'].'</td>';
                    echo '<td>'.$perf['losses'].'</td>';
                    echo '<td>'.$perf['pct'].'%</td></tr>';
                endforeach;
                tablefoot('');
            ?>
            </div>
            </div><!-- END ROW -->
            
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