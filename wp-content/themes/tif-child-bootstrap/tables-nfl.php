<?php
/*
 * Template Name: NFL Career Stat Tables
 * Description: Used for displaying NFL Stats listings of players (Pass, Rush, TD, Etc...)
 */
 ?>

<?php

$yeartwenty = get_season_leaders(2020);
$yeartwentyone = get_season_leaders(2021);

$playerid = $_GET['id'];

$playerposition = substr($playerid, -2);

$playerdata = get_player_career_stats($playerid);

$playerdeets = get_player_data($playerid);

$jusplayerids = just_player_ids();

foreach ($jusplayerids as $key => $value):
    $pos = substr($value, -2);
    $deets = get_player_career_stats($value);
    if($deets['passingyards'] != 0 && $pos == 'QB'):
        $passyards[$value] = $deets['passingyards'];
        $qbgm = $deets['games'];
        if($qbgm > 50):
            $qbypg[$value] = $deets['passingyards'] / $qbgm;
        endif;
    endif;
    if($deets['rushyrds'] != 0):
        $rushyards[$value] = $deets['rushyrds'];
        $rbgm = $deets['games'];
        if($rbgm > 50 && $pos == 'RB'):
            $rbypg[$value] = $deets['rushyrds'] / $rbgm;
        endif;
    endif;
    if($deets['rushyrds'] != 0):
        $rbgm = $deets['games'];
        if($rbgm > 50):
            $allpurpose[$value] = $deets['rushyrds'] + $deets['recyrds'];
        endif;
    endif;
    if($deets['recyrds'] != 0):
        $recyards[$value] = $deets['recyrds'];
        $wrgm = $deets['games'];
        if($wrgm > 50 && $pos == 'WR'):
            $wrypg[$value] = $deets['recyrds'] / $wrgm;
        endif;
    endif;
    if($deets['rushyrds'] != 0 && $pos == 'QB'):
        $qbrushyards[$value] = $deets['rushyrds'];
    endif;
    if($deets['recyrds'] != 0 && $pos == 'RB'):
        $rbrecyards[$value] = $deets['recyrds'];
    endif;
    if($deets['rushyrds'] != 0 && $pos == 'WR'):
        $wrrushyards[$value] = $deets['rushyrds'];
    endif;
    if($deets['xpa'] != 0 && $pos == 'PK'):
        $xpm[$value] = $deets['xpm'];
        $xpa[$value] = $deets['xpa'];
        if($deets['xpa'] > 100):
            $xppct[$value] = $deets['xpm'] / $deets['xpa'];
        endif;
    endif;
    if($deets['fga'] != 0 && $pos == 'PK'):
        $fgm[$value] = $deets['fgm'];
        $fga[$value] = $deets['fga'];
        if($deets['fga'] > 50):
            $fgpct[$value] = $deets['fgm'] / $deets['fga'];
        endif;
    endif;
    if($deets['passingtds'] != 0):
        $passtds[$value] = $deets['passingtds'];
    endif;
    if($deets['rushtds'] != 0):
        $rushtds[$value] = $deets['rushtds'];
    endif;
    if($deets['rectds'] != 0):
        $rectds[$value] = $deets['rectds'];
    endif;
    $alltds = $deets['passingtds'] +  $deets['rushtds'] + $deets['rectds'];
    if($alltds > 0):
        $counttds[$value] = $alltds;
    endif;

endforeach;

arsort($passyards);
arsort($rushyards);
arsort($recyards);
arsort($qbrushyards);
arsort($rbrecyards);
arsort($wrrushyards);
arsort($xpm);
arsort($xpa);
arsort($xppct);
arsort($fgm);
arsort($fga);
arsort($fgpct);
arsort($qbypg);
arsort($rbypg);
arsort($wrypg);
arsort($passtds);
arsort($rushtds);
arsort($rectds);
arsort($counttds);
arsort($allpurpose);

//NFL Games played by Team Logic
$playersassoc = get_players_assoc();
foreach ($playersassoc as $key => $value) {
    if ($value):
        $playerdata[] = get_player_nfl_team_by_week($key);
    endif;
}
$new = array_flatten($playerdata);
$newnew = array_filter($new);
$count = array_count_values($newnew);

$raiders = $count['OAK'] + $count['LVR'] + $count['RAI'];
$rams = $count['STL'] + $count['LAR'] + $count['RAM'];
$chargers = $count['SD'] + $count['LAC'] + $count['SDG'];
$summed = array(
    'OAK' => $raiders,
    'LAR' => $rams,
    'LAC' => $chargers
);

unset($count['OAK'], $count['LVR'], $count['RAI'], $count['STL'], $count['LAR'], $count['SD'], $count['LAC'], $count['SDG'], $count['RAM']);

$final = array_merge($count, $summed);

foreach ($final as $key => $value):
    $get = get_nfl_full_team_name_from_id($key);
    $nflteamfinal[$get] = $value;
endforeach;

arsort($nflteamfinal);

?>

<?php get_header(); ?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">


                        <?php //printr($wrypg, 0); ?>
                        <?php //printr($playerdeets, 1); ?>

                    <div class="col-xs-24">
                        <h4>NFL Player Stats - Regular Season</h4>
                        <p>This page displays a summary of NFL stats acquired during PFL gameplay.</p>
                        <hr>
                    </div>

                    <!-- Passing Yards -->
                        <div class="col-xs-24 col-sm-12 col-md-6">
                            <?php
                            $labels = array('Player', 'Yards');
                            tablehead('Passing Yards', $labels);
                            $i = 1;
                            foreach ($passyards as $key => $value){
                                if($i <= 25):
                                    $name = get_player_name($key);
                                    $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                    $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                    $i++;
                                endif;
                            }
                            echo $tableprint;
                                $tableprint = '';
                            tablefoot('Top 25');
                            ?>
                        </div>

                    <!-- Rushing Yards -->
                        <div class="col-xs-24 col-sm-12 col-md-6">
                            <?php
                            $labels = array('Player', 'Yards');
                            tablehead('Rushing Yards', $labels);
                            $i = 1;
                            foreach ($rushyards as $key => $value){
                                if($i <= 25):
                                    $name = get_player_name($key);
                                    $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                    $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                    $i++;
                                endif;
                            }
                            echo $tableprint;
                                $tableprint = '';
                            tablefoot('Top 25');
                            ?>
                        </div>

                    <!-- Receiving Yards -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'Yards');
                        tablehead('Receiving Yards', $labels);
                        $i = 1;
                        foreach ($recyards as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25');
                        ?>
                    </div>

                    <!-- Hold the Column -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                    </div>

                    <div class="clearfix"></div>

                    <!-- Field Goals Made -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'FG');
                        tablehead('Field Goals Made', $labels);
                        $i = 1;
                        foreach ($fgm as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25 / 50 attempts min.');
                        ?>
                    </div>

                    <!-- Field Goal % -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'Percent');
                        tablehead('Field Goal Percentage', $labels);
                        $i = 1;
                        foreach ($fgpct as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '3', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25 / 50 attempts min.');
                        ?>
                    </div>

                    <!-- PAT Made -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'PAT');
                        tablehead('Extra Points Made', $labels);
                        $i = 1;
                        foreach ($xpm as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25 / 100 attempts min.');
                        ?>
                    </div>

                    <!-- Extra Point % -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'Percent');
                        tablehead('Extra Point Percentage', $labels);
                        $i = 1;
                        foreach ($xppct as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '3', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25 / 100 attempts min');
                        ?>
                    </div>

                    <div class="clearfix"></div>

                    <!-- Passing TDS -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'TDs');
                        tablehead('Passing Touchdowns', $labels);
                        $i = 1;
                        foreach ($passtds as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25');
                        ?>
                    </div>

                    <!-- Rushing Touchdowns -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'TDs');
                        tablehead('Rushing Touchdowns', $labels);
                        $i = 1;
                        foreach ($rushtds as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25');
                        ?>
                    </div>

                    <!-- Receiving Touchdowns -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'TDs');
                        tablehead('Receiving Touchdowns', $labels);
                        $i = 1;
                        foreach ($rectds as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25');
                        ?>
                    </div>

                    <!-- Hold the Column -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                    </div>

                    <div class="clearfix"></div>

                    <!-- QB Rushing Yards -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'Yards');
                        tablehead('QB Rushing Yards', $labels);
                        $i = 1;
                        foreach ($qbrushyards as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25');
                        ?>
                    </div>

                    <!-- RB Receiving Yards -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'Yards');
                        tablehead('RB Receiving Yards', $labels);
                        $i = 1;
                        foreach ($rbrecyards as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25');
                        ?>
                    </div>

                    <!-- WR Rush Yards -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'Yards');
                        tablehead('WR Rush Yards', $labels);
                        $i = 1;
                        foreach ($wrrushyards as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25');
                        ?>
                    </div>

                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'Yards');
                        tablehead('All Purpose Yards', $labels);
                        $i = 1;
                        foreach ($allpurpose as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '0', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25');
                        ?>
                    </div>

                    <div class="clearfix"></div>


                    <!-- QB Yards Per Game -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'Yards');
                        tablehead('QB Passing Yards Per Game', $labels);
                        $i = 1;
                        foreach ($qbypg as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '1', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25 / Min 50 Games');
                        ?>
                    </div>

                    <!-- RB Yards Per Game -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'Yards');
                        tablehead('RB Rushing Yards Per Game', $labels);
                        $i = 1;
                        foreach ($rbypg as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '1', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25 / Min 50 Games');
                        ?>
                    </div>

                    <!-- WR Yards Per Game -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Player', 'Yards');
                        tablehead('WR Receiving Yards Per Game', $labels);
                        $i = 1;
                        foreach ($wrypg as $key => $value){
                            if($i <= 25):
                                $name = get_player_name($key);
                                $tableprint .='<tr><td>'.$i.'. '.$name['first'].' '.$name['last'].'</td>';
                                $tableprint .='<td class="min-width text-right">'.number_format($value, '1', '.', ',').'</td></tr>';
                                $i++;
                            endif;
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Top 25 / Min 50 Games');
                        ?>
                    </div>

                    <!-- Count of Games by NFL Team -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <?php
                        $labels = array('Team', 'Player Games');
                        tablehead('Games Played for each NFL Team', $labels);
                        $i = 1;
                        foreach ($nflteamfinal as $key => $value){
                            $tableprint .='<tr><td>'.$key.'</td>';
                            $tableprint .='<td>'.$value.'</td></tr>';
                        }
                        echo $tableprint;
                        $tableprint = '';
                        tablefoot('Combined Teams - Raiders, Chargers, Rams');
                        ?>
                    </div>

                    <!-- Hold the Column -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                    </div>

                    <div class="clearfix"></div>


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