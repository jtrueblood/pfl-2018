<?php
/*
 * Template Name: Get Probowl Player Boxcores
 * Description: Get week 17 data from MFL for creating Pro Bowl Scores
 */
 ?>

<?php get_header(); ?>

<?php
$year = $_GET['Y'];
$week = $_GET['W'];
$weekform = sprintf('%02d', $week);
$weekid = $year.$weekform;
$run = $_GET['SQL'];

$EQB1 = $_GET['EQB1'];
$EQB2 = $_GET['EQB2'];
$EQB3 = $_GET['EQB3'];
$ERB1 = $_GET['ERB1'];
$ERB2 = $_GET['ERB2'];
$ERB3 = $_GET['ERB3'];
$EWR1 = $_GET['EWR1'];
$EWR2 = $_GET['EWR2'];
$EWR3 = $_GET['EWR3'];
$EPK1 = $_GET['EPK1'];
$EPK2 = $_GET['EPK2'];
$EPK3 = $_GET['EPK3'];

$DQB1 = $_GET['DQB1'];
$DQB2 = $_GET['DQB2'];
$DQB3 = $_GET['DQB3'];
$DRB1 = $_GET['DRB1'];
$DRB2 = $_GET['DRB2'];
$DRB3 = $_GET['DRB3'];
$DWR1 = $_GET['DWR1'];
$DWR2 = $_GET['DWR2'];
$DWR3 = $_GET['DWR3'];
$DPK1 = $_GET['DPK1'];
$DPK2 = $_GET['DPK2'];
$DPK3 = $_GET['DPK3'];

$playersassoc = get_players_assoc();
$mflteams = teams_for_mfl_history();
$getyearteams = $mflteams[$year];

foreach ($getyearteams as $key => $value):
    $string = strval($key);
    $flipkeys[$value] = $string;
endforeach;

function get_score_for_probowl($year, $week){
    //$weekform = sprintf('%02d', $week);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www48.myfantasyleague.com/$year/export?TYPE=weeklyResults&L=38954&APIKEY=aRNp1sySvuWtx0emO1HIZDYeFbox&W=$week&MISSING_AS_BYE=&JSON=1",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Cookie: MFL_PW_SEQ=ah9q2M6Ss%2Bis3Q29; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $weekresults = json_decode($response, true);
    $franchise = $weekresults['weeklyResults']['franchise'];
    foreach ($franchise as $key => $value):
        $players[$value['id']] = $value['player'];
    endforeach;
    foreach ($players as $key => $value):
        foreach ($value as $k => $v):
            $players_simple[$key][$v['id']] = $v['score'];
        endforeach;
    endforeach;

    $franchisemore = $weekresults['weeklyResults']['matchup']['franchise'];
    foreach ($franchisemore as $key => $value):
        $playersmore[$value['id']] = $value['player'];
    endforeach;
    foreach ($playersmore as $key => $value):
        foreach ($value as $k => $v):
            $players_simple_more[$key][$v['id']] = $v['score'];
        endforeach;
    endforeach;

    $merge = array_merge($players_simple, $players_simple_more);

    //return $franchise;
    return $merge;
}

$mfl_probowl_data = get_score_for_probowl($year, $week);

foreach($mfl_probowl_data as $key => $value):
    foreach ($value as $k => $v):
        $pflid = one_player_mfl_to_pfl($k);
        $pflplayers[$pflid] = array(
            'points' => $v,
            'team' => $getyearteams[$key]
        );
    endforeach;
endforeach;

$group_url_array = array(
    'EQB1' => $EQB1,
    'EQB2' => $EQB2,
    'EQB3' => $EQB3,
    'ERB1' => $ERB1,
    'ERB2' => $ERB2,
    'ERB3' => $ERB3,
    'EWR1' => $EWR1,
    'EWR2' => $EWR2,
    'EWR3' => $EWR3,
    'EPK1' => $EPK1,
    'EPK2' => $EPK2,
    'EPK3' => $EPK3,
    'DQB1' => $DQB1,
    'DQB2' => $DQB2,
    'DQB3' => $DQB3,
    'DRB1' => $DRB1,
    'DRB2' => $DRB2,
    'DRB3' => $DRB3,
    'DWR1' => $DWR1,
    'DWR2' => $DWR2,
    'DWR3' => $DWR3,
    'DPK1' => $DPK1,
    'DPK2' => $DPK2,
    'DPK3' => $DPK3
);

$k = 0;
$counter = 1;
$i = 1;
foreach ($group_url_array as $key => $value):
    if($i < 10):
        $threedig = sprintf('%02d', $i);
    else:
        $threedig = $i;
    endif;
    $id = 'prb'.$year.$threedig;
    $position  = substr($value, strlen($value)-2);
    $getteam = get_player_teams_season($value);
    $teams = $getteam[$year];
    $getleague = substr($key, 0, 1,);
    $points = (isset($pflplayers[$value]['points']) ? $pflplayers[$value]['points'] : 0) ;
    $playerid = ($value ? $value : $key);
    $league = ($getleague == 'E' ? 'EGAD' : 'DGAS');
    $starter = substr($key, -1);
    $team = (isset($teams) ? end($teams) : '');

    //save as position groups to figure out points used
    $check_used[$k][$value] = $points;
    if ($counter % 3 == 0) {
        $k++;
    }
    $counter++;

    $insertpro[$playerid] = array(
        'id' => $id,
        'playerid' => $playerid,
        'pos' => $position,
        'team' => $team, //get last value in teams list
        'league' => $league,
        'year' => $year,
        'points' => $points,
        'starter' => $starter - 1, // I should have done this differently, but -1 from the key makes it the same as the 0,1,2 starter values in the table.
        'ptsused' => ''
    );
    $i++;
endforeach;

// find max vlaues in groups to figure out points used
foreach ($check_used as $key => $value):
    $maxplayer = array_keys($value, max($value));
    $max = max($value);
    $getstarter[$maxplayer[0]] = $max;
endforeach;

// then throw this into the $insert array so that it is ready for insertion
foreach ($insertpro as $key => $value):
    $check = $getstarter[$key];
    if($check):
        $insertpro[$key]['ptsused'] = 1;
    else:
        $insertpro[$key]['ptsused'] = 0;
    endif;
endforeach;

global $wpdb;
//Insert formatted data into wp_overtime table
if($run == 1):
    foreach ($insertpro as $key => $value){
        if($value['pos']):
        $wpdb->insert(
            'wp_probowlbox',
            array(
                'id' => $value['id'],
                'playerid' => $value['playerid'],
                'pos' => $value['pos'],
                'team' => $value['team'],
                'league' => $value['league'],
                'year' => $value['year'],
                'points' => $value['points'],
                'starter' =>  $value['starter'],
                'ptsused' => $value['ptsused']
            ),
            array (
                '%s','%s','%s','%s','%s','%d','%d','%d','%d'
            )
        );
        endif;
    }
endif;

?>

<div class="boxed" style="min-height: 5000px;">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <div id="page-content">
                <div class="col-xs-24">
                        <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title"><?php echo $year; ?> Probowl Results</h3>
                                <!-- EGAD -->
                                <div class="col-xs-12">
                                    <h4>EGAD Roster</h4>
                                    <h5>Quarterbacks</h5>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADQB1">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadqb1 .= '<option value="&EQB1='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadqb1; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADQB2">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadqb2 .= '<option value="&EQB2='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadqb2; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADQB3">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadqb3 .= '<option value="&EQB3='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadqb3; ?>
                                    </select>

                                    <h5>Runningbacks</h5>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADRB1">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadrb1 .= '<option value="&ERB1='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadrb1; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADRB2">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadrb2 .= '<option value="&ERB2='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadrb2; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADRB3">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadrb3 .= '<option value="&ERB3='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadrb3; ?>
                                    </select>

                                    <h5>Receivers</h5>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADWR1">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadwr1 .= '<option value="&EWR1='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadwr1; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADWR2">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadwr2 .= '<option value="&EWR2='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadwr2; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADWR3">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadwr3 .= '<option value="&EWR3='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadwr3; ?>
                                    </select>

                                    <h5>Kickers</h5>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADPK1">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadpk1 .= '<option value="&EPK1='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadpk1; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADPK2">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadpk2 .= '<option value="&EPK2='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadpk2; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="EGADPK3">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $egadpk3 .= '<option value="&EPK3='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $egadpk3; ?>
                                    </select>
                                </div>

                                <!-- DGAS -->
                                <div class="col-xs-12">
                                    <h4>DGAS Roster</h4>
                                    <h5>Quarterbacks</h5>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASQB1">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgasqb1 .= '<option value="&DQB1='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgasqb1; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASQB2">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgasqb2 .= '<option value="&DQB2='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgasqb2; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASQB3">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgasqb3 .= '<option value="&DQB3='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgasqb3; ?>
                                    </select>

                                    <h5>Runningbacks</h5>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASRB1">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgasrb1 .= '<option value="&DQR1='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgasrb1; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASRB2">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgasrb2 .= '<option value="&DRB2='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgasrb2; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASRB3">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgasrb3 .= '<option value="&DRB3='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgasrb3; ?>
                                    </select>

                                    <h5>Receivers</h5>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASWR1">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgaswr1 .= '<option value="&DWR1='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgaswr1; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASWR2">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgaswr2 .= '<option value="&DWR2='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgaswr2; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASWR3">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgaswr3 .= '<option value="&DWR3='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgaswr3; ?>
                                    </select>

                                    <h5>Kickers</h5>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASPK1">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgaspk1 .= '<option value="&DPK1='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgaspk1; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASPK2">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgaspk2 .= '<option value="&DPK2='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgaspk2; ?>
                                    </select>
                                    <select data-placeholder="Select A Player..." class="chzn-select" style="width:100%;" id="DGASPK3">
                                        <option value=""></option>
                                        <?php foreach ($playersassoc as $key => $selectplayer){
                                            $firsto = $selectplayer[0];
                                            $lasto = $selectplayer[1];
                                            $dgaspk3 .= '<option value="&DPK3='.$key.'">'.$firsto.' '.$lasto.'</option>';
                                        }
                                        echo $dgaspk3; ?>
                                    </select>
                                </div>

                                </div>

                                <div class="col-xs-24">
                                    <button class="btn btn-warning" id="ProBowlDrop">Select</button>
                                    <?php printr($getstarter, 0); ?>
                                    <?php printr($insertpro, 0); ?>

                                </div>
                            </div>
                        </div>
                </div>
            </div>


<!--    http://pfl-data.local/get-probowl-score-from-mfl/?Y=2022&W=17&EQB1=2018AlleQB&EQB2=2020HerbQB&EQB3=2021LawrQB&ERB1=2016HenrRB&ERB2=2018BarkRB&ERB3=2018ChubRB&EWR1=2015DiggWR&EWR2=2017KuppWR&EWR3=2020BrowWR&EPK1=2012TuckPK&EPK2=2020BassPK&EPK3=2020CarlPK&DQB1=2008RodgQB&DQB2=2018MahoQB&DQB3=2018JackQB&DQR1=2017McCaRB&DRB2=2022StevRB&DRB3=2022PollRB&DWR1=1997JeffWR&DWR2=2021ChasWR&DWR3=2021WaddWR&DPK1=2017ButkPK&DPK2=2021McPhPK&DPK3=2021MeyeWR-->
            </div><!--End page content-->

        </div><!--END CONTENT CONTAINER-->


    <?php include_once('main-nav.php'); ?>
    <?php include_once('aside.php'); ?>

</div>

<?php get_footer(); ?>