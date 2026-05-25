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
// Validate required parameters
if (!isset($_GET['Y']) || !isset($_GET['W'])) {
    echo '<div class="error">Error: Missing required parameters. Please provide Y (year) and W (week).</div>';
    echo '<script>console.log("Error: Missing Y (year) or W (week) parameters");</script>';
    get_footer();
    exit;
}

$pid = isset($_GET['ID']) ? $_GET['ID'] : null;
$year = (int)$_GET['Y'];
$week = (int)$_GET['W'];
$run = isset($_GET['SQL']) ? $_GET['SQL'] : null;

// Validate year and week ranges
if ($year < 2010 || $year > date('Y')) {
    echo '<div class="error">Error: Invalid year. Must be between 2010 and ' . date('Y') . '.</div>';
    get_footer();
    exit;
}

if ($week < 1 || $week > 18) {
    echo '<div class="error">Error: Invalid week. Must be between 1 and 18.</div>';
    get_footer();
    exit;
}

echo '<script>console.log("Processing roster data for year ' . $year . ', week ' . $week . '");</script>';

$playerinfo = $pid ? get_player_basic_info($pid) : null;
$mflid = $playerinfo ? $playerinfo[0]['mflid'] : null;
$weekform = sprintf('%02d', $week);
$apikey = 'aRNp1sySvuWqx0CmO1HIZDYeFbox';

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
    
    // Check if we have a league ID for this year
    if (!isset($leagueid[$year])) {
        echo '<script>console.log("Error: No league ID found for year ' . $year . '");</script>';
        return false;
    }
    
    $seasonleagueid = $leagueid[$year];
    $url = "https://www48.myfantasyleague.com/$year/export?TYPE=rosters&L=$seasonleagueid&APIKEY=aRNp1sySvuWqx0CmO1HIZDYeFbox&W=$week&JSON=1";
    
    echo '<script>console.log("API URL: ' . $url . '");</script>';
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30, // Increased timeout
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Cookie: MFL_PW_SEQ=ah9q2M6Ss%2Bis3Q29; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D'
        ),
    ));
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);
    
    // Check for cURL errors
    if ($curlError) {
        echo '<script>console.log("cURL Error: ' . addslashes($curlError) . '");</script>';
        return false;
    }
    
    // Check HTTP response code
    if ($httpCode !== 200) {
        echo '<script>console.log("HTTP Error: ' . $httpCode . '");</script>';
        return false;
    }
    
    // Check if response is empty
    if (empty($response)) {
        echo '<script>console.log("Error: Empty response from API");</script>';
        return false;
    }
    
    // Try to decode JSON
    $weekresults = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo '<script>console.log("JSON Error: ' . json_last_error_msg() . '");</script>';
        echo '<script>console.log("Raw response: ' . addslashes(substr($response, 0, 200)) . '...");</script>';
        return false;
    }
    
    echo '<script>console.log("API call successful for year ' . $year . ', week ' . $week . '");</script>';
    return $weekresults;
}
//$teamstrans = get_or_set($teams, 'teams');
//$playersassoc = get_or_set($playersas, 'playersassoc');

//$count = count($unique);
//echo $count;

//get rosters by week
$rosterarray = array(); // Initialize to prevent undefined variable errors

if (file_exists($destination_folder.'/'.$year.$week.'.json')):
    $report_message = $year.$week.'roster -- file exsists || ';
    echo '<script>console.log("'.$year.$week.'roster - file exsists");</script>';
    echo $report_message;
    $get_roster = file_get_contents($destination_folder.'/'.$year.$week.'.json');
    $results = json_decode($get_roster, true);
    if ($results && isset($results['rosters']['franchise'])) {
        $rosterarray = $results['rosters']['franchise'];
    } else {
        echo '<script>console.log("Error: Invalid cached file format");</script>';
    }
else:
    $rosterresults = get_team_mfl_roster_by_week($year, $week);
    if ($rosterresults && isset($rosterresults['rosters']['franchise'])) {
        $rosterarray = $rosterresults['rosters']['franchise'];
        $encode = json_encode($rosterresults);
        file_put_contents("$destination_folder/$year$week.json", $encode);
        echo '<script>console.log("API call successful, data cached");</script>';
    } else {
        echo '<script>console.log("Error: API call failed or returned invalid data");</script>';
        echo '<div class="error">Failed to fetch roster data from API. Check console for details.</div>';
    }
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