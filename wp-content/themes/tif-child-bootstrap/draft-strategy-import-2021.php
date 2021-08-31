<?php
/*
 * Template Name: Draft Strategy Import 2021
 * Description: HOW TO USE
 * 1. Download csv for QB, RB, WR, TE, K from here :
 * https://www.fantasypros.com/nfl/projections/k.php?week=draft
 */


$getdate = $_GET['d'];
$position = 'qb';

if($getdate):
	$date = $getdate;
else:
	$date = date('Y-m-d');
endif;

$allteams = teamlist();
 
$playersassoc = get_players_assoc();
$i = 0;
foreach ($playersassoc as $key => $value){
	$playersid[] = $key;
}

$getprotections = get_table('wp_draftplan_protected');
foreach($getprotections as $key => $value):
    $protections[$value[0]] = $value[1];
endforeach;

$stylesheet_uri = get_stylesheet_directory_uri();

$positions = array('QB' => 'qb', 'RB' => 'rb', 'WR' => 'wr', 'TE' => 'te', 'K' => 'k');

// Add Projections CSV for all 5 Positions QB, RB, WR, TE, PK.
// Download these files as often as you want.
// First change the name of the exsiting files to append the date they were downloaded.
// Then download the new files from the link provided on the page.
// Each season you will need to increment the 'year' value

foreach($positions as $key => $pos):
    $file = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/draft-fantasypros/2021/FantasyPros_Fantasy_Football_Projections_'.$key.'.csv';
    if (!empty($file)) {
        $csv = array_map('str_getcsv', file($file));
        array_splice($csv, 0, 1); # remove column header and blank row
        $projection[$pos] = $csv;
    }
endforeach;

// Download the rookie file as often as you like using the same manner above.
// This is in the Rookie Dynasty Section
// This is used to append the 'QB1, RB1, ect.' values in the lists so you can see who is a rookie and where they are ranked.

$rookies = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/draft-fantasypros/2021/FantasyPros_2021_Dynasty_RK_Rankings.csv';
if (!empty($rookies)) {
    $csv = array_map('str_getcsv', file($rookies));
    array_splice($csv, 0, 1); # remove column header and blank row
    $rookieprojection = $csv;
}
foreach ($rookieprojection as $key => $value){
    $rookielist[$value[2]] = $value[4];
}

// Download this to add Dynasty ranking, Age and Bye week values.
// This is the Dynasty All File
// You probably only need to do this file once a season, but you could do it more for updated fantasy values

$dynasty = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/draft-fantasypros/2021/FantasyPros_2021_Dynasty_ALL_Rankings.csv';
if (!empty($dynasty)) {
    $csv = array_map('str_getcsv', file($dynasty));
    array_splice($csv, 0, 1); # remove column header and blank row
    $dynastyprojection = $csv;
}
foreach ($dynastyprojection as $key => $value){
    $dynastylist[$value[2]] = array(
        'team' => $value[3],
        'pos' => $value[4],
        'bye' => $value[5],
        'age' => $value[6]
    );
}

//printr($dynastylist , 0);

$i = 1;
$j = 1;
$k = 1;
$l = 1;
$m = 1;

foreach($projection as $key => $value):
    if($key == 'qb'):
        foreach ($value as $subkey => $item):
            $passyards = str_replace( ',', '', $item[4]);
            $est_season_score = estimated_pfl_score($passyards, $item[5], $item[6], $item[8], $item[9],0,0,0,0,0,0);
            if($item[1]):
                $newprojection[$key][] = array(
                    'selected' => '',
                    'name' => $item[0],
                    'position' => strtoupper($key),
                    'team' => $item[1],
                    'pass_att' => $item[2],
                    'pass_comp' => $item[3],
                    'pass_yards' => $passyards,
                    'pass_tds' => $item[5],
                    'pass_int' => $item[6],
                    'rush_att' => $item[7],
                    'rush_yards' => $item[8],
                    'rush_tds' => $item[9],
                    'fpts' => $item[11],
                    'fp_pos_rank' => $i,
                    'pfl_standard_ratio' => round($est_season_score / $item[11], 3),
                    'est_full_season_score' => $est_season_score,
                    'est_game_score' => round($est_season_score / 17, 1),
                    'est_pfl_season_score' => round(($est_season_score / 17) * 13, 1),
                    'valdiff' => $subkey,
                    'info' => $dynastylist[$item[0]] // inject dynasty rank, bye and age from Dynasty all
                );
                $i++;
            endif;
            if($i == 33):
                break;
            endif;
        endforeach;
    endif;
    if($key == 'rb'):
        foreach ($value as $item):
            if($item[1]):
                $rushyards = str_replace( ',', '', $item[3]);
                $recyards = str_replace( ',', '', $item[6]);
                $est_season_score = estimated_pfl_score(0, 0,0, $rushyards, $item[4], $recyards, $item[7],0,0, 0, 0);
                $newprojection[$key][] = array(
                    'selected' => '',
                    'name' => $item[0],
                    'position' => strtoupper($key),
                    'team' => $item[1],
                    'rush_att' => $item[2],
                    'rush_yards' => $rushyards,
                    'rush_tds' => $item[4],
                    'receptions' => $item[5],
                    'rec_yards' => $recyards,
                    'rec_tds' => $item[7],
                    'fpts' => $item[9],
                    'fp_pos_rank' => $j,
                    'est_full_season_score' => $est_season_score,
                    'est_game_score' => round ($est_season_score / 17, 1),
                    'est_pfl_season_score' => round(($est_season_score / 17) * 13, 1),
                    'info' => $dynastylist[$item[0]]
                );
                $j++;
                if($j == 75):
                    break;
                endif;
            endif;
        endforeach;
    endif;
    if($key == 'wr'):
        foreach ($value as $item):
            if($item[1]):
                $rushyards = str_replace( ',', '', $item[6]);
                $recyards = str_replace( ',', '', $item[3]);
                $est_season_score = estimated_pfl_score(0, 0,0, $rushyards, $item[7], $recyards, $item[4],0,0, 0, 0);
                $newprojection[$key][] = array(
                    'selected' => '',
                    'name' => $item[0],
                    'position' => strtoupper($key),
                    'team' => $item[1],
                    'receptions' => $item[2],
                    'rec_yards' => $recyards,
                    'rec_tds' => $item[4],
                    'rush_att' => $item[5],
                    'rush_yards' => $rushyards,
                    'rush_tds' => $item[7],
                    'fpts' => $item[9],
                    'fp_pos_rank' => $k,
                    'est_full_season_score' => $est_season_score,
                    'est_game_score' => round($est_season_score / 17, 1),
                    'est_pfl_season_score' => round(($est_season_score / 17) * 13, 1),
                    'info' => $dynastylist[$item[0]]
                );
                $k++;
                if($k == 75):
                    break;
                endif;
            endif;
        endforeach;
    endif;
    if($key == 'te'):
        foreach ($value as $item):
            if($item[1]):
                $recyards = str_replace( ',', '', $item[3]);
                $est_season_score = estimated_pfl_score(0, 0,0, 0, 0, $recyards, $item[4],0,0, 0, 0);
                $newprojection[$key][] = array(
                    'selected' => '',
                    'name' => $item[0],
                    'position' => strtoupper($key),
                    'team' => $item[1],
                    'receptions' => $item[2],
                    'rec_yards' => $recyards,
                    'rec_tds' => $item[4],
                    'fp_pos_rank' => $l,
                    'fpts' => $item[6],
                    'est_full_season_score' => $est_season_score,
                    'est_game_score' => round($est_season_score / 17, 1),
                    'est_pfl_season_score' => round(($est_season_score / 17) * 13, 1),
                    'info' => $dynastylist[$item[0]]
                );
                $l++;
                if($l == 6):
                    break;
                endif;
            endif;
        endforeach;
    endif;
    if($key == 'k'):
        foreach ($value as $item):
            if($item[1]):
                $est_season_score = estimated_pfl_score(0, 0,0, 0, 0, 0, 0,$item[2],$item[4], 0, 0);
                $newprojection[$key][] = array(
                    'selected' => '',
                    'name' => $item[0],
                    'position' => strtoupper($key),
                    'team' => $item[1],
                    'fg' => $item[2],
                    'fga' => $item[3],
                    'ep' => $item[4],
                    'fp_pos_rank' => $m,
                    'fpts' => $item[5],
                    'est_full_season_score' => $est_season_score,
                    'est_game_score' => round($est_season_score / 17, 1),
                    'est_pfl_season_score' => round(($est_season_score / 17) * 14, 1),
                    'info' => $dynastylist[$item[0]]
                );
                $m++;
                if($m == 20):
                    break;
                endif;
            endif;
        endforeach;
    endif;
endforeach;

foreach ($newprojection['qb'] as $key => $value):
    if(!$protections[$value['name']]):
        $remaining[$value['name']] = $value['est_pfl_season_score'];
    endif;
endforeach;
foreach ($newprojection['rb'] as $key => $value):
    if(!$protections[$value['name']]):
        $remaining[$value['name']] = $value['est_pfl_season_score'];
    endif;
endforeach;
foreach ($newprojection['wr'] as $key => $value):
    if(!$protections[$value['name']]):
        $remaining[$value['name']] = $value['est_pfl_season_score'];
    endif;
endforeach;
foreach ($newprojection['te'] as $key => $value):
    if(!$protections[$value['name']]):
        $remaining[$value['name']] = $value['est_pfl_season_score'];
    endif;
endforeach;


//printr($newprojection, 1);

get_header(); 
?>

<div class="boxed">
			
    <!--CONTENT CONTAINER-->
    <div id="content-container">

				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"><?php the_title();?></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
        <!--Page content-->
        <div id="page-content">
				
				<!-- THE ROW -->
				<div class="row">

					<!-- INFO PANELS -->
					<div class="col-xs-24 col-sm-12 col-md-6">
						<div class="panel panel-primary">
                            <div class="panel-body">
                            <h4>Projections Pages</h4>
                            <a href="https://www.fantasypros.com/nfl/projections/qb.php?week=draft" target="_blank">Quarterbacks</a><br>
                            <a href="https://www.fantasypros.com/nfl/projections/rb.php?week=draft" target="_blank">Runningbacks</a><br>
                            <a href="https://www.fantasypros.com/nfl/projections/wr.php?week=draft" target="_blank">Wide Receivers</a><br>
                            <a href="https://www.fantasypros.com/nfl/projections/te.php?week=draft" target="_blank">Tight Ends</a><br>
                            <a href="https://www.fantasypros.com/nfl/projections/k.php?week=draft" target="_blank">Kickers</a><br>
                            </div>
                        </div>
					</div>
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Best Remaining</h4>
                                <?php
                                arsort($remaining);
                                $r = 0;
                                foreach ($remaining as $key => $value):
                                    echo '<h5>'.$key.'</h5>';
                                    $r++;
                                    if($r == 11):
                                        break;
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>LIST ALL ROOKIES HERE FOR 2022/h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Info Here</h4>
                            </div>
                        </div>
                    </div>

                </div>


                <div class="row">

                    <!-- QUARTERBACKS -->
                    <div class="col-xs-24 eq-box-sm">
                        <div class="panel panel-bordered panel-light">
                            <div class="panel-heading">
                                <h3 class="panel-title">Quarterbacks</h3>
                            </div>

                            <div class="panel-body">
                                <table id="quarterbackdraftnew" class="qb-draft-table-new table table-hover table-vcenter stripe">
                                    <thead>

                                    <?php echo '<tr>
                                        <th class="text-center min-width">Rank</th>
                                        <th class="">Name</th>
                                        <th class="text-center min-width">PFL</th>
                                        <th class="text-center min-width">POS</th>
                                        <th class="text-center min-width">TEAM</th>
                                        <th class="text-center min-width">AGE</th>
                                        <th class="text-center min-width">BYE</th>      
                                        <th class="text-center min-width">Att</th>
                                        <th class="text-center min-width">Comp</th>
                                        <th class="text-center min-width">Yards</th>
                                        <th class="text-center min-width">TD</th>
                                        <th class="text-center min-width">INT</th>
                                        <th class="text-center min-width">Rush Att</th>
                                        <th class="text-center min-width">Rush Yards</th>
                                        <th class="text-center min-width">Rush TD</th>
                                        <th class="text-center min-width">FPTS</th>
                                        <th class="text-center min-width">Ratio</th>
                                        <th class="text-center min-width">PFL PPG</th>
                                        <th class="text-center min-width">PFL Proj</th>                                  
                                    </tr>';?>

                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($newprojection['qb'] as $key => $value):
                                        if($protections[$value['name']]):
                                            $select = "selected";
                                        else:
                                            $select = "";
                                        endif;
                                        echo '<tr class="'.$select.'">
                                            <td class="text-center">'.$value['fp_pos_rank'].'</td>
                                            <td class="">'.$value['name'].' <strong>'.$rookielist[$value['name']].'</strong></td>
                                            <td class="text-center">'.$protections[$value['name']].'</td>
                                            <td class="text-center">'.$value['position'].'</td>
                                            <td class="text-center">'.$value['team'].'</td>
                                            <td class="text-center">'.$value['info']['age'].'</td>
                                            <td class="text-center">'.$value['info']['bye'].'</td>
                                            <td class="text-center">'.$value['pass_att'].'</td>
                                            <td class="text-center">'.$value['pass_comp'].'</td>
                                            <td class="text-center">'.$value['pass_yards'].'</td>
                                            <td class="text-center">'.$value['pass_tds'].'</td>
                                            <td class="text-center">'.$value['pass_int'].'</td>
                                            <td class="text-center">'.$value['rush_att'].'</td>
                                            <td class="text-center">'.$value['rush_yards'].'</td>
                                            <td class="text-center">'.$value['rush_tds'].'</td>
                                            <td class="text-center">'.$value['fpts'].'</td>
                                            <td class="text-center">'.$value['pfl_standard_ratio'].'</td>
                                            <td class="text-center">'.$value['est_game_score'].'</td>
                                            <td class="text-center">'.$value['est_pfl_season_score'].'</td>
                                        </>';
                                    endforeach;
                                    ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RUNNINGBACKS -->
                    <div class="col-xs-24 eq-box-sm">
                        <div class="panel panel-bordered panel-light">
                            <div class="panel-heading">
                                <h3 class="panel-title">Runningbacks</h3>
                            </div>

                            <div class="panel-body">
                                <table id="runningbackdraftnew" class="rb-draft-table-new table table-hover table-vcenter stripe">
                                    <thead>

                                    <?php echo '<tr>
                                        <th class="text-center min-width">Rank</th>
                                        <th class="">Name</th>
                                        <th class="text-center min-width">PFL</th>
                                        <th class="text-center min-width">POS</th>
                                        <th class="text-center min-width">TEAM</th>
                                        <th class="text-center min-width">AGE</th>
                                        <th class="text-center min-width">BYE</th>  
                                        <th class="text-center min-width">Rush Att</th>
                                        <th class="text-center min-width">Rush Yards</th>
                                        <th class="text-center min-width">Rush TDs</th>
                                        <th class="text-center min-width">Rec</th>
                                        <th class="text-center min-width">Rec Yards</th>
                                        <th class="text-center min-width">Rec TDs</th>
                                        <th class="text-center min-width">FPTS</th>
                                        <th class="text-center min-width">PFL PPG</th>
                                        <th class="text-center min-width">PFL Proj</th>                                
                                    </tr>';?>

                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($newprojection['rb'] as $key => $value):
                                        if($protections[$value['name']]):
                                            $select = "selected";
                                        else:
                                            $select = "";
                                        endif;
                                        echo '<tr class="'.$select.'">
                                            <td class="text-center">'.$value['fp_pos_rank'].'</td>
                                            <td class="">'.$value['name'].' <strong>'.$rookielist[$value['name']].'</strong></td>
                                            <td class="text-center">'.$protections[$value['name']].'</td>
                                            <td class="text-center">'.$value['position'].'</td>
                                            <td class="text-center">'.$value['team'].'</td>
                                            <td class="text-center">'.$value['info']['age'].'</td>
                                            <td class="text-center">'.$value['info']['bye'].'</td>
                                            <td class="text-center">'.$value['rush_att'].'</td>
                                            <td class="text-center">'.$value['rush_yards'].'</td>
                                            <td class="text-center">'.$value['rush_tds'].'</td>
                                            <td class="text-center">'.$value['receptions'].'</td>
                                            <td class="text-center">'.$value['rec_yards'].'</td>
                                            <td class="text-center">'.$value['rec_tds'].'</td>
                                            <td class="text-center">'.$value['fpts'].'</td>
                                            <td class="text-center">'.$value['est_game_score'].'</td>
                                            <td class="text-center">'.$value['est_pfl_season_score'].'</td>
                                        </tr>';
                                    endforeach;
                                    ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RECEIVERS AND TIGHT ENDS -->
                    <div class="col-xs-24 eq-box-sm">
<!--                        --><?php //printr($newprojection['wr'], 0); ?>
                        <div class="panel panel-bordered panel-light">
                            <div class="panel-heading">
                                <h3 class="panel-title">Receivers and Tight Ends</h3>
                            </div>

                            <div class="panel-body">
                                <table id="receiverdraftnew" class="wr-draft-table-new table table-hover table-vcenter stripe">
                                    <thead>

                                    <?php echo '<tr>
                                        <th class="text-center min-width">Rank</th>
                                        <th class="">Name</th>
                                        <th class="text-center min-width">PFL</th>
                                        <th class="text-center min-width">POS</th>
                                        <th class="text-center min-width">TEAM</th>
                                        <th class="text-center min-width">AGE</th>
                                        <th class="text-center min-width">BYE</th>  
                                        <th class="text-center min-width">Rec</th>
                                        <th class="text-center min-width">Rec Yards</th>
                                        <th class="text-center min-width">Rec TDs</th>
                                        <th class="text-center min-width">Rush Att</th>
                                        <th class="text-center min-width">Rush Yards</th>
                                        <th class="text-center min-width">Rush TDs</th>
                                        <th class="text-center min-width">FPTS</th>
                                        <th class="text-center min-width">PFL PPG</th>
                                        <th class="text-center min-width">PFL Proj</th>                                
                                    </tr>';?>

                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($newprojection['wr'] as $key => $value):
                                        if($protections[$value['name']]):
                                            $select = "selected";
                                        else:
                                            $select = "";
                                        endif;
                                        echo '<tr class="'.$select.'">                                         
                                            <td class="text-center">'.$value['fp_pos_rank'].'</td>
                                            <td class="">'.$value['name'].' <strong>'.$rookielist[$value['name']].'</strong></td>
                                            <td class="text-center">'.$protections[$value['name']].'</td>
                                            <td class="text-center">'.$value['position'].'</td>
                                            <td class="text-center">'.$value['team'].'</td>
                                            <td class="text-center">'.$value['info']['age'].'</td>
                                            <td class="text-center">'.$value['info']['bye'].'</td>
                                            <td class="text-center">'.$value['receptions'].'</td>
                                            <td class="text-center">'.$value['rec_yards'].'</td>
                                            <td class="text-center">'.$value['rec_tds'].'</td>
                                            <td class="text-center">'.$value['rush_att'].'</td>
                                            <td class="text-center">'.$value['rush_yards'].'</td>
                                            <td class="text-center">'.$value['rush_tds'].'</td>
                                            <td class="text-center">'.$value['fpts'].'</td>
                                            <td class="text-center">'.$value['est_game_score'].'</td>
                                            <td class="text-center">'.$value['est_pfl_season_score'].'</td>
                                        </tr>';
                                    endforeach;
                                    foreach ($newprojection['te'] as $key => $value):
                                        if($protections[$value['name']]):
                                            $select = "selected";
                                        else:
                                            $select = "";
                                        endif;
                                        echo '<tr class="'.$select.'">
                                            <td class="text-center">'.$value['fp_pos_rank'].'</td>
                                            <td class="">'.$value['name'].' <strong>'.$rookielist[$value['name']].'</strong></td>
                                            <td class="text-center">'.$protections[$value['name']].'</td>
                                            <td class="text-center">'.$value['position'].'</td>
                                            <td class="text-center">'.$value['team'].'</td>
                                            <td class="text-center">'.$value['info']['age'].'</td>
                                            <td class="text-center">'.$value['info']['bye'].'</td>
                                            <td class="text-center">'.$value['receptions'].'</td>
                                            <td class="text-center">'.$value['rec_yards'].'</td>
                                            <td class="text-center">'.$value['rec_tds'].'</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">'.$value['fpts'].'</td>
                                            <td class="text-center">'.$value['est_game_score'].'</td>
                                            <td class="text-center">'.$value['est_pfl_season_score'].'</td>
                                        </tr>';
                                    endforeach;
                                    ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- KICKERS -->
                    <div class="col-xs-24 eq-box-sm">
                        <div class="panel panel-bordered panel-light">
                            <div class="panel-heading">
                                <h3 class="panel-title">Kickers</h3>
                            </div>

                            <div class="panel-body">
                                <table id="kickerdraftnew" class="pk-draft-table-new table table-hover table-vcenter stripe">
                                    <thead>
                                    <?php echo '<tr>
                                        <th class="text-center min-width">Rank</th>
                                        <th class="">Name</th>
                                        <th class="text-center min-width">PFL</th>
                                        <th class="text-center min-width">POS</th>
                                        <th class="text-center min-width">TEAM</th>
                                        <th class="text-center min-width">AGE</th>
                                        <th class="text-center min-width">BYE</th>  
                                        <th class="text-center min-width">FG</th>
                                        <th class="text-center min-width">FGA</th>
                                        <th class="text-center min-width">EP</th>
                                        <th class="text-center min-width">FPTS</th>
                                        <th class="text-center min-width">PFL PPG</th>
                                        <th class="text-center min-width">PFL Proj</th>                                
                                    </tr>';?>

                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($newprojection['k'] as $key => $value):
                                        if($protections[$value['name']]):
                                            $select = "selected";
                                        else:
                                            $select = "";
                                        endif;
                                        echo '<tr class="'.$select.'">                                          
                                            <td class="text-center">'.$value['fp_pos_rank'].'</td>
                                            <td class="">'.$value['name'].' <strong>'.$rookielist[$value['name']].'</strong></td>
                                            <td class="text-center">'.$protections[$value['name']].'</td>
                                            <td class="text-center">'.$value['position'].'</td>
                                            <td class="text-center">'.$value['team'].'</td>
                                            <td class="text-center">'.$value['info']['age'].'</td>
                                            <td class="text-center">'.$value['info']['bye'].'</td>
                                            <td class="text-center">'.$value['fg'].'</td>
                                            <td class="text-center">'.$value['fga'].'</td>
                                            <td class="text-center">'.$value['ep'].'</td>
                                            <td class="text-center">'.$value['fpts'].'</td>
                                            <td class="text-center">'.$value['est_game_score'].'</td>
                                            <td class="text-center">'.$value['est_pfl_season_score'].'</td>
                                        </tr>';
                                    endforeach;
                                    ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
	
				</div>	
				
		</div>
		<?php include_once('main-nav.php'); ?>
	</div>
	
</div>

<?php 
/*
	$log_file = $destination_folder.'/file.log'; 
	error_log($report_message, 3, $log_file); 
*/
?>

		
<?php get_footer(); ?>