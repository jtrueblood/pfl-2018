<?php
/*
 * Template Name: Draft Strategy Import 2024
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
?>

<style>
    .mystyles {
        color: blue;
        font-weight: bold;
    }
</style>

<?php
foreach($positions as $key => $pos):
    $file = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/draft-fantasypros/2024/FantasyPros_2024_Draft_ALL_Rankings.csv';
    if (!empty($file)) {
        $csv = array_map('str_getcsv', file($file));
        array_splice($csv, 0, 1); # remove column header and blank row
        $draftlist = $csv;
    }
endforeach;

foreach($draftlist as $key => $value):
    $position = substr($value[4], 0, 2);
    $returndraft[$position][$value[0]] = array(
        'rank' => $value[0],
        'tiers' => $value[1],
        'name' => $value[2],
        'team' => $value[3],
        'position' => $position,
        'best' => $value[5],
        'worst' => $value[6],
        'avg' => $value[7],
        'stddev' => $value[8],
        'vsadp' => $value[9]
    );
    $justrank[$value[2]] = $value[0];
endforeach;

// Download the rookie file as often as you like using the same manner above.
// This is in the Rookie Dynasty Section
// This is used to append the 'QB1, RB1, ect.' values in the lists so you can see who is a rookie and where they are ranked.

// Download this to add Dynasty ranking, Age and Bye week values.
// This is the Dynasty All File
// You probably only need to do this file once a season, but you could do it more for updated fantasy values

$dynasty = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/draft-fantasypros/2024/FantasyPros_2024_Dynasty_ALL_Rankings.csv';
if (!empty($dynasty)) {
    $csv = array_map('str_getcsv', file($dynasty));
    array_splice($csv, 0, 1); # remove column header and blank row
    $dynastyprojection = $csv;
}
foreach ($dynastyprojection as $key => $value):
    $position = substr($value[4], 0, 2);
    $dynastylist[$position][$value[0]] = array(
        'rank' => $value[0],
        'tiers' => $value[1],
        'name' => $value[2],
        'team' => $value[3],
        'position' => $position,
        'age' => $value[5],
        'bye' => $value[6],
        'best' => $value[7],
        'worst' => $value[8],
        'avg' => $value[9]
    );
endforeach;


$rookies = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/draft-fantasypros/2024/FantasyPros_2024_Rookies_ALL_Rankings.csv';
if (!empty($rookies)) {
    $csv = array_map('str_getcsv', file($rookies));
    array_splice($csv, 0, 1); # remove column header and blank row
    $rookielist = $csv;
}

foreach($rookielist as $key => $value):
    $position = substr($value[3], 0, 2);
    $rooks[$value[0]] = array(
        'rank' => $value[0],
        'name' => $value[1],
        'team' => $value[2],
        'position' => $position,
    );
endforeach;

// This is a guess in May.  Will change by actual draft date.
$protections = array(
    'ETS' => array('Josh Allen','Jahmyr Gibbs','A.J. Brown'),
    'PEP' => array('Tua Tagovailoa','Kenneth Walker III','CeeDee Lamb'),
    'HAT' => array('Jared Goff','Joe Mixon','Amon-Ra St. Brown'),
    'WRZ' => array('C.J. Stroud','Saquon Barkley','Puka Nacua'),
    'BST' => array('Justin Herbert','Derrick Henry','Travis Kelce'),
    'BUL' => array('Lamar Jackson','Breece Hall','Justin Jefferson'),
    'CMN' => array('Joe Burrow','Bijan Robinson','Deebo Samuel Sr.'),
    'DST' => array('Jalen Hurts','Kyren Williams','Tyreek Hill'),
    'SNR' => array('Patrick Mahomes II','Travis Etienne Jr.',"Ja'Marr Chase"),
    'TSG' => array('Jordan Love','Christian McCaffrey','Davante Adams')
);

function check_for_protection($player){
    global $protections;
    $protected = false;
    foreach($protections as $key => $value):
        if(in_array($player,$value)):
            $protected = ' -- '.$key;
        endif;
    endforeach;
    return $protected;
}

function team_draft_average_tendancy($thepos, $theteam, $theseason) {
    $drafts = get_drafts();
    foreach ($drafts as $key => $value):
        $i = 0;
        $position = $value['position'];
        $team = $value['acteam'];
        $season = $value['season'];
        $round = ltrim($value['round'], '0');
        $pick = $value['pick'];
        if ($position == $thepos && $team == $theteam && $season >= $theseason):
            $selections[$season][$round] = $pick;
        endif;
    endforeach;

    if($selections):
        foreach ($selections as $key => $value):
            $rounds[$key][key($value)] = array_shift(array_values($value));
        endforeach;
    endif;

    if($rounds):
        foreach ($rounds as $key => $value):
            $k = key($value);
            $roundsonly[$key] = $k;
            $picksonly[$key] = $value[$k];
            $formatpicks[$key] = $k.'.'.$value[$k];
        endforeach;
    endif;

    asort($formatpicks);


    $avground = array_sum($roundsonly) / count($roundsonly);
    $avgpick = array_sum($picksonly) / count($picksonly);

    $summary = array(
        'average_pick' => number_format($avground, 1),
        'highest_pick' => min($formatpicks),
        'lowest_pick' => max($formatpicks)
    );
    return $summary;
}

// this var can be for any year as the start point, but the most recent 20 years makes the most sense
$var_year = 2000;

$qb_tendency = array(
    'BST' => team_draft_average_tendancy('QB', 'BST', $var_year),
    'ETS' => team_draft_average_tendancy('QB', 'ETS', $var_year),
    'HAT' => team_draft_average_tendancy('QB', 'HAT', $var_year),
    'PEP' => team_draft_average_tendancy('QB', 'PEP', $var_year),
    'WRZ' => team_draft_average_tendancy('QB', 'WRZ', $var_year),
    'BUL' => team_draft_average_tendancy('QB', 'BUL', $var_year),
    'CMN' => team_draft_average_tendancy('QB', 'CMN', $var_year),
    'DST' => team_draft_average_tendancy('QB', 'DST', $var_year),
    'SNR' => team_draft_average_tendancy('QB', 'SNR', $var_year),
    'TSG' => team_draft_average_tendancy('QB', 'TSG', $var_year)
);

$first_to_take_qb_avg = array(
    'BST' => $qb_tendency['BST']['average_pick'],
    'ETS' => $qb_tendency['ETS']['average_pick'],
    'HAT' => $qb_tendency['HAT']['average_pick'],
    'PEP' => $qb_tendency['PEP']['average_pick'],
    'WRZ' => $qb_tendency['WRZ']['average_pick'],
    'BUL' => $qb_tendency['BUL']['average_pick'],
    'CMN' => $qb_tendency['CMN']['average_pick'],
    'DST' => $qb_tendency['DST']['average_pick'],
    'SNR' => $qb_tendency['SNR']['average_pick'],
    'TSG' => $qb_tendency['TSG']['average_pick']
);
asort($first_to_take_qb_avg);

$first_take_of_qb = array(
    'BST' => $qb_tendency['BST']['highest_pick'],
    'ETS' => $qb_tendency['ETS']['highest_pick'],
    'HAT' => $qb_tendency['HAT']['highest_pick'],
    'PEP' => $qb_tendency['PEP']['highest_pick'],
    'WRZ' => $qb_tendency['WRZ']['highest_pick'],
    'BUL' => $qb_tendency['BUL']['highest_pick'],
    'CMN' => $qb_tendency['CMN']['highest_pick'],
    'DST' => $qb_tendency['DST']['highest_pick'],
    'SNR' => $qb_tendency['SNR']['highest_pick'],
    'TSG' => $qb_tendency['TSG']['highest_pick']
);
asort($first_take_of_qb);

$rb_tendency = array(
    'BST' => team_draft_average_tendancy('RB', 'BST', $var_year),
    'ETS' => team_draft_average_tendancy('RB', 'ETS', $var_year),
    'HAT' => team_draft_average_tendancy('RB', 'HAT', $var_year),
    'PEP' => team_draft_average_tendancy('RB', 'PEP', $var_year),
    'WRZ' => team_draft_average_tendancy('RB', 'WRZ', $var_year),
    'BUL' => team_draft_average_tendancy('RB', 'BUL', $var_year),
    'CMN' => team_draft_average_tendancy('RB', 'CMN', $var_year),
    'DST' => team_draft_average_tendancy('RB', 'DST', $var_year),
    'SNR' => team_draft_average_tendancy('RB', 'SNR', $var_year),
    'TSG' => team_draft_average_tendancy('RB', 'TSG', $var_year)
);

$first_to_take_rb_avg = array(
    'BST' => $rb_tendency['BST']['average_pick'],
    'ETS' => $rb_tendency['ETS']['average_pick'],
    'HAT' => $rb_tendency['HAT']['average_pick'],
    'PEP' => $rb_tendency['PEP']['average_pick'],
    'WRZ' => $rb_tendency['WRZ']['average_pick'],
    'BUL' => $rb_tendency['BUL']['average_pick'],
    'CMN' => $rb_tendency['CMN']['average_pick'],
    'DST' => $rb_tendency['DST']['average_pick'],
    'SNR' => $rb_tendency['SNR']['average_pick'],
    'TSG' => $rb_tendency['TSG']['average_pick']
);
asort($first_to_take_rb_avg);

$first_take_of_rb = array(
    'BST' => $rb_tendency['BST']['highest_pick'],
    'ETS' => $rb_tendency['ETS']['highest_pick'],
    'HAT' => $rb_tendency['HAT']['highest_pick'],
    'PEP' => $rb_tendency['PEP']['highest_pick'],
    'WRZ' => $rb_tendency['WRZ']['highest_pick'],
    'BUL' => $rb_tendency['BUL']['highest_pick'],
    'CMN' => $rb_tendency['CMN']['highest_pick'],
    'DST' => $rb_tendency['DST']['highest_pick'],
    'SNR' => $rb_tendency['SNR']['highest_pick'],
    'TSG' => $rb_tendency['TSG']['highest_pick']
);
asort($first_take_of_rb);

$wr_tendency = array(
    'BST' => team_draft_average_tendancy('WR', 'BST', $var_year),
    'ETS' => team_draft_average_tendancy('WR', 'ETS', $var_year),
    'HAT' => team_draft_average_tendancy('WR', 'HAT', $var_year),
    'PEP' => team_draft_average_tendancy('WR', 'PEP', $var_year),
    'WRZ' => team_draft_average_tendancy('WR', 'WRZ', $var_year),
    'BUL' => team_draft_average_tendancy('WR', 'BUL', $var_year),
    'CMN' => team_draft_average_tendancy('WR', 'CMN', $var_year),
    'DST' => team_draft_average_tendancy('WR', 'DST', $var_year),
    'SNR' => team_draft_average_tendancy('WR', 'SNR', $var_year),
    'TSG' => team_draft_average_tendancy('WR', 'TSG', $var_year)
);

$first_to_take_wr_avg = array(
    'BST' => $wr_tendency['BST']['average_pick'],
    'ETS' => $wr_tendency['ETS']['average_pick'],
    'HAT' => $wr_tendency['HAT']['average_pick'],
    'PEP' => $wr_tendency['PEP']['average_pick'],
    'WRZ' => $wr_tendency['WRZ']['average_pick'],
    'BUL' => $wr_tendency['BUL']['average_pick'],
    'CMN' => $wr_tendency['CMN']['average_pick'],
    'DST' => $wr_tendency['DST']['average_pick'],
    'SNR' => $wr_tendency['SNR']['average_pick'],
    'TSG' => $wr_tendency['TSG']['average_pick']
);
asort($first_to_take_wr_avg);

$first_take_of_wr = array(
    'BST' => $wr_tendency['BST']['highest_pick'],
    'ETS' => $wr_tendency['ETS']['highest_pick'],
    'HAT' => $wr_tendency['HAT']['highest_pick'],
    'PEP' => $wr_tendency['PEP']['highest_pick'],
    'WRZ' => $wr_tendency['WRZ']['highest_pick'],
    'BUL' => $wr_tendency['BUL']['highest_pick'],
    'CMN' => $wr_tendency['CMN']['highest_pick'],
    'DST' => $wr_tendency['DST']['highest_pick'],
    'SNR' => $wr_tendency['SNR']['highest_pick'],
    'TSG' => $wr_tendency['TSG']['highest_pick']
);
asort($first_take_of_wr);

$pk_tendency = array(
    'BST' => team_draft_average_tendancy('PK', 'BST', $var_year),
    'ETS' => team_draft_average_tendancy('PK', 'ETS', $var_year),
    'HAT' => team_draft_average_tendancy('PK', 'HAT', $var_year),
    'PEP' => team_draft_average_tendancy('PK', 'PEP', $var_year),
    'WRZ' => team_draft_average_tendancy('PK', 'WRZ', $var_year),
    'BUL' => team_draft_average_tendancy('PK', 'BUL', $var_year),
    'CMN' => team_draft_average_tendancy('PK', 'CMN', $var_year),
    'DST' => team_draft_average_tendancy('PK', 'DST', $var_year),
    'SNR' => team_draft_average_tendancy('PK', 'SNR', $var_year),
    'TSG' => team_draft_average_tendancy('PK', 'TSG', $var_year)
);

$first_to_take_pk_avg = array(
    'BST' => $pk_tendency['BST']['average_pick'],
    'ETS' => $pk_tendency['ETS']['average_pick'],
    'HAT' => $pk_tendency['HAT']['average_pick'],
    'PEP' => $pk_tendency['PEP']['average_pick'],
    'WRZ' => $pk_tendency['WRZ']['average_pick'],
    'BUL' => $pk_tendency['BUL']['average_pick'],
    'CMN' => $pk_tendency['CMN']['average_pick'],
    'DST' => $pk_tendency['DST']['average_pick'],
    'SNR' => $pk_tendency['SNR']['average_pick'],
    'TSG' => $pk_tendency['TSG']['average_pick']
);
asort($first_to_take_pk_avg);

$first_take_of_pk = array(
    'BST' => $pk_tendency['BST']['highest_pick'],
    'ETS' => $pk_tendency['ETS']['highest_pick'],
    'HAT' => $pk_tendency['HAT']['highest_pick'],
    'PEP' => $pk_tendency['PEP']['highest_pick'],
    'WRZ' => $pk_tendency['WRZ']['highest_pick'],
    'BUL' => $pk_tendency['BUL']['highest_pick'],
    'CMN' => $pk_tendency['CMN']['highest_pick'],
    'DST' => $pk_tendency['DST']['highest_pick'],
    'SNR' => $pk_tendency['SNR']['highest_pick'],
    'TSG' => $pk_tendency['TSG']['highest_pick']
);
asort($first_take_of_pk);

//$pep_kicker = team_draft_average_tendancy('PK', 'PEP', 2010);
//printr($selections, 0);
//printr($pep_kicker, 0);

foreach($protections as $team => $players):
    foreach($players as $key => $value):
        $playersforvalue[$team][$justrank[$value]] = $value;
    endforeach;
endforeach;

function team_values($teamid){
    global $playersforvalue;
    global $allposranks;
    foreach ($playersforvalue[$teamid] as $key => $value):
        $posrank = $allposranks[$value];
        echo '<p>' . $value . ' (' . $posrank . ')</p>';
        $addit[] = $posrank;
    endforeach;
    echo '<p><strong>Total Value: ' . array_sum($addit) . '</strong></p>';
}

//$curl = curl_init();
//
//$year = 2024;
//$lid = 38954;
//curl_setopt_array($curl, array(
//    CURLOPT_URL => 'https://www58.myfantasyleague.com/'.$year.'/export?TYPE=draftResults&L='.$lid.'&APIKEY=aRNp1sySvuWrx0GmO1HIZDYeFbox&JSON=1',
//    CURLOPT_RETURNTRANSFER => true,
//    CURLOPT_ENCODING => '',
//    CURLOPT_MAXREDIRS => 10,
//    CURLOPT_TIMEOUT => 0,
//    CURLOPT_FOLLOWLOCATION => true,
//    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//    CURLOPT_CUSTOMREQUEST => 'POST',
//    CURLOPT_HTTPHEADER => array(
//        'Cookie: MFL_PW_SEQ=ah9q2M6Ss%2Bis3Q29; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY='
//    ),
//));
//
//$response = curl_exec($curl);
//$err = curl_error($curl);
//
//curl_close($curl);

//if ($err) {
//    echo "cURL Error #:" . $err;
//} else {
//    echo 'WORKED';
//}

$mfldraft = json_decode($response, true);
$mflpicks = $mfldraft['draftResults']['draftUnit']['draftPick'];

//printr($mflpicks, 0);

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
					<div class="col-xs-24 col-sm-12 col-md-4">
						<div class="panel panel-primary">
                            <div class="panel-body">
                            <h4>Projections Pages</h4>
                            <a href="https://www.fantasypros.com/nfl/rankings/consensus-cheatsheets.php" target="_blank">Consensus Draft</a><br>
                            <a href="https://www.fantasypros.com/nfl/rankings/dynasty-overall.php" target="_blank">Dynasty</a><br>
                            <a href="https://www.fantasypros.com/nfl/rankings/dynasty-rookies-overall.php" target="_blank">Rookies</a><br>
                            </div>
                        </div>
					</div>
                </div>
                <!-- THE ROW -->
                <div class="row">
                    <?php echo '<h3>Since '.$var_year.'</h3>'; ?>
                    <!-- INFO PANELS -->
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Average QB First Pick</h4>
                                <?php
                                    printr($first_to_take_qb_avg,0);
                                ?>
                                <h4>Highest Instance of QB Pick</h4>
                                <?php
                                    printr($first_take_of_qb,0);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Average RB First Pick</h4>
                                <?php
                                printr($first_to_take_rb_avg,0);
                                ?>
                                <h4>Highest Instance of RB Pick</h4>
                                <?php
                                printr($first_take_of_rb,0);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Average WR First Pick</h4>
                                <?php
                                printr($first_to_take_wr_avg,0);
                                ?>
                                <h4>Highest Instance of WR Pick</h4>
                                <?php
                                printr($first_take_of_wr,0);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-6">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Average PK First Pick</h4>
                                <?php
                                printr($first_to_take_pk_avg,0);
                                ?>
                                <h4>Highest Instance of PK Pick</h4>
                                <?php
                                printr($first_take_of_pk,0);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-24 col-sm-12 col-md-5">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Consensus Draft QB</h4>
                                <?php
                                foreach ($returndraft['QB'] as $key => $value):
                                    if($value['rank'] <= 200):
                                        $qblist[$value['rank']] = $value;
                                    endif;
                                endforeach;

                                $i = 1;
                                foreach ($qblist as $key => $value):
                                    $qb_position_rank[$value['name']] = $i;
                                    $protected = check_for_protection($value['name']);
                                    $styleclass = $protected ? 'mystyles' : '';
                                    echo '<p class="'.$styleclass.'">'.$i.'. '.$value['name'].', '.$value['team'].' ('.$key.')'.$protected.'</p>';
                                    $i++;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-5">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Consensus Draft RB</h4>
                                <?php
                                foreach ($returndraft['RB'] as $key => $value):
                                    if($value['rank'] <= 160):
                                        $rblist[$value['rank']] = $value;
                                    endif;
                                endforeach;

                                $i = 1;
                                foreach ($rblist as $key => $value):
                                    $rb_position_rank[$value['name']] = $i;
                                    $protected = check_for_protection($value['name']);
                                    $styleclass = $protected ? 'mystyles' : '';
                                    echo '<p class="'.$styleclass.'">'.$i.'. '.$value['name'].', '.$value['team'].' ('.$key.')'.$protected.'</p>';
                                    $i++;
                                endforeach;
                                //printr($qblist, 0);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-5">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Consensus Draft WR</h4>
                                <?php
                                foreach ($returndraft['WR'] as $key => $value):
                                    if($value['rank'] <= 95):
                                        $wrlist[$value['rank']] = $value;
                                    endif;
                                endforeach;
                                foreach ($returndraft['TE'] as $key => $value):
                                    if($value['rank'] <= 95):
                                        $telist[$value['rank']] = $value;
                                    endif;
                                endforeach;

                                $reclist = array_merge($wrlist, $telist);

                                $i = 1;
                                foreach ($reclist as $key => $value):
                                    $rec_position_rank[$value['name']] = $i;
                                    $protected = check_for_protection($value['name']);
                                    $styleclass = $protected ? 'mystyles' : '';
                                    echo '<p class="'.$styleclass.'">'.$i.'. '.$value['name'].', '.$value['team'].' ('.$key.')'.$protected.'</p>';
                                    $i++;
                                endforeach;
                                //printr($qblist, 0);
                                $allposranks = array_merge($qb_position_rank, $rb_position_rank, $rec_position_rank);
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-5">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Consensus Draft PK</h4>
                                <?php
                                $keys = ['K1', 'K2', 'K3', 'K4', 'K5', 'K6', 'K7', 'K8'];
                                foreach ($keys as $key):
                                    foreach ($returndraft[$key] as $value):
                                        $pklist[$value['rank']] = $value;
                                    endforeach;
                                endforeach;

                                $i = 1;
                                foreach ($pklist as $key => $value):
                                    $protected = check_for_protection($value['name']);
                                    $styleclass = $protected ? 'mystyles' : '';
                                    echo '<p class="'.$styleclass.'">'.$i.'. '.$value['name'].', '.$value['team'].' ('.$key.')'.$protected.'</p>';
                                    $i++;
                                endforeach;
                                //printr($qblist, 0);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-24 col-sm-12 col-md-5">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Dynasty QB</h4>
                                <?php
                                foreach ($dynastylist['QB'] as $key => $value):
                                    if($value['rank'] <= 200):
                                        $qb_dyn[$value['rank']] = $value;
                                    endif;
                                endforeach;

                                $i = 1;
                                foreach ($qb_dyn as $key => $value):
                                    $protected = check_for_protection($value['name']);
                                    $styleclass = $protected ? 'mystyles' : '';
                                    echo '<p class="'.$styleclass.'">'.$i.'. '.$value['name'].', '.$value['team'].' ('.$key.')'.$protected.'</p>';
                                    $i++;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-5">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Dynasty RB</h4>
                                <?php
                                foreach ($dynastylist['RB'] as $key => $value):
                                    if($value['rank'] <= 150):
                                        $rb_dyn[$value['rank']] = $value;
                                    endif;
                                endforeach;

                                $i = 1;
                                foreach ($rb_dyn as $key => $value):
                                    $protected = check_for_protection($value['name']);
                                    $styleclass = $protected ? 'mystyles' : '';
                                    echo '<p class="'.$styleclass.'">'.$i.'. '.$value['name'].', '.$value['team'].' ('.$key.')'.$protected.'</p>';
                                    $i++;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-5">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Dynasty WR</h4>
                                <?php
                                foreach ($dynastylist['WR'] as $key => $value):
                                    if($value['rank'] <= 100):
                                        $wr_dyn[$value['rank']] = $value;
                                    endif;
                                endforeach;

                                $i = 1;
                                foreach ($wr_dyn as $key => $value):
                                    $protected = check_for_protection($value['name']);
                                    $styleclass = $protected ? 'mystyles' : '';
                                    echo '<p class="'.$styleclass.'">'.$i.'. '.$value['name'].', '.$value['team'].' ('.$key.')'.$protected.'</p>';
                                    $i++;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-5">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>Rookies Only</h4>
                                    <?php
                                    //printr($rooks, 0);
                                    $i = 1;
                                    foreach($rooks as $key => $value):
                                        $protected = check_for_protection($value['name']);
                                        $styleclass = $protected ? 'mystyles' : '';
                                        if($i <= 20):
                                            echo '<p class="'.$styleclass.'">'.$i.'. '.$value['name'].', '.$value['team'].' ('.$value['position'].')</p>';
                                        endif;
                                        $i++;
                                    endforeach;
                                    ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- TEAM VALUES EGAD -->
                <div class="row">
                    <div class="col-xs-24 col-sm-12 col-md-4">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>BST Team Values</h4>
                                <?php team_values('BST'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-4">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>ETS Team Values</h4>
                                <?php team_values('ETS'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-4">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>HAT Team Values</h4>
                                <?php team_values('HAT'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-4">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>PEP Team Values</h4>
                                <?php team_values('PEP'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-24 col-sm-12 col-md-4">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4>WRZ Team Values</h4>
                                <?php team_values('WRZ'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- TEAM VALUES DGAS -->
                <div class="row">
                <div class="col-xs-24 col-sm-12 col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-body">
                            <h4>BUL Team Values</h4>
                            <?php team_values('BUL'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-24 col-sm-12 col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-body">
                            <h4>CMN Team Values</h4>
                            <?php team_values('CMN'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-24 col-sm-12 col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-body">
                            <h4>DST Team Values</h4>
                            <?php team_values('DST'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-24 col-sm-12 col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-body">
                            <h4>SNR Team Values</h4>
                            <?php team_values('SNR'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-24 col-sm-12 col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-body">
                            <h4>TSG Team Values</h4>
                            <?php team_values('TSG'); ?>
                        </div>
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