<?php
/*
 * Template Name: Get Rosters from MFL
 * Description: Gets the weekly rosters for years 2010 and later when this data was availible for MFL.
 * Run after each week of play is complete.
 * Saves api call to a locally stored .json file
 */
 ?>

<?php get_header(); ?>

<?php
$pid = $_GET['ID'];
$year = $_GET['Y'];
$week = $_GET['W'];
$run = $_GET['SQL'];
$playerinfo = get_player_basic_info($pid);
$mflid = $playerinfo[0]['mflid'];
$weekform = sprintf('%02d', $week);

$playersas = get_players_assoc();
$teams = get_teams();
$seasons = the_seasons();

$seasonstrans = get_or_set($seasons, 'seasons', 1200);

$getseasonids = get_pfl_mfl_ids_season();
$oneseasonids = $getseasonids[$year];

$destination_folder = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/mfl-weekly-rosters';

function get_team_mfl_roster_by_week($year, $week){
    $getseasonids = get_pfl_mfl_ids_season();
    $leagueid = get_mfl_league_id();
    $seasonleagueid = $leagueid[$year];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www48.myfantasyleague.com/$year/export?TYPE=rosters&L=$seasonleagueid&APIKEY=aRNp1sySvuWrx0GmO1HIZDYeFbox&W=$week&JSON=1",
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
    return $weekresults;
}
//$teamstrans = get_or_set($teams, 'teams');
//$playersassoc = get_or_set($playersas, 'playersassoc');

//$count = count($unique);
//echo $count;

//get rosters by week

if (file_exists($destination_folder.'/'.$year.$week.'.json')):
    $report_message = $year.$week.'roster -- file exsists || ';
    echo '<script>console.log("'.$year.$week.'roster - file exsists");</script>';
    echo $report_message;
    $get_roster = file_get_contents($destination_folder.'/'.$year.$week.'.json');
    $results = json_decode($get_roster, true);
    $rosterarray = $results['rosters']['franchise'];
else:
    $rosterresults = get_team_mfl_roster_by_week($year, $week);
    $encode = json_encode($rosterresults);
    file_put_contents("$destination_folder/$year$week.json", $encode);
endif;

foreach ($rosterarray as $key => $value):
    $pflid = $oneseasonids[$value['id']];
    $newrosterarray[$pflid] = $value['player'];
endforeach;

foreach ($newrosterarray as $key => $value):
    if($key != ''):
        foreach($value as $k => $v):
            $status = $v['status'];
            $playerid = one_player_mfl_to_pfl($v['id']);
            $playerinfo = get_player_basic_info($playerid);
            $fullname = $playerinfo[0]['first'].' '.$playerinfo[0]['last'];
            $position = $playerinfo[0]['position'];
            if($k != ''):
                $teamrosters[$key][$status][$playerid] = array(
                    'player' => $v['id'],
                    'name' => $fullname,
                    'position' => $position,
                    'drafted' => $v['drafted']
                );
            endif;
        endforeach;
    endif;
endforeach;

 ?>

<div class="boxed">
			
        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->
            <div id="page-content">

                <?php
                //printr($new['2001BradQB'],0);
                //printr($getseasonids , 0);
                //printr($rosterarray, 0);
                //printr($players, 0);
                //printr($teamrosters, 0);
                printr($teamrosters, 0);
                echo $teamidconverted;
                echo $seasonleagueid;
                ?>

                <div class="col-xs-4">
                        <div class="panel">
                            <div class="panel-heading">
                                <h3 class="panel-title"><?php echo $teamid; ?> History Timeline</h3>

                            </div>
                        </div>
                </div>
            </div>

            </div><!--End page content-->

        </div><!--END CONTENT CONTAINER-->


    <?php include_once('main-nav.php'); ?>
    <?php include_once('aside.php'); ?>

</div>

<?php get_footer(); ?>