<?php
/*
 * Template Name: MFL 2017 Season Player Data
 * Description: MFL 2017 Season Player Data to export MFL Season data to import into main history database by playerID
 */
 ?>


<?php get_header(); ?>


<?php 
$temmmmm = get_team_results_expanded('ETS');
printr($temmmmm, 0);
	
$year = 2017;
$week = 14;
$lid = 38954;
$apikey = 'aRNp1sySvuKox1emO1HIZDYeFbox';

$mflplayer = 10276;
$pflplayer = '2011IngrRB';

$teammflid = array(
	'0001' => 'TSG',
	'0002' => 'ETS',
	'0003' => 'PEP',
	'0004' => 'WRZ',
	'0005' => 'DST',
	'0006' => 'SON',
	'0007' => 'SNR',
	'0008' => 'HAT',
	'0009' => 'CMN',
	'0010' => 'BUL'	
);

// get weekly starters


function set_week_mfl_trans() {
  $transient = get_transient( 'mfl_team_scores_'.$week );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
  
  	global $year;
  	global $week;
  	global $lid;
  	global $apikey;
  	global $teammflid;
  	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://www58.myfantasyleague.com/$year/export?TYPE=weeklyResults&L=$lid&APIKEY=$apikey&W=$week&JSON=1",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "Cache-Control: no-cache",
	    "Postman-Token: 12d23246-b5e0-73c0-4b72-2d5c0ea6d955"
	  ),
	));
	
	$weekly = curl_exec($curl);
	$err = curl_error($curl);
	
	curl_close($curl);
	
	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  echo "curl Worked";
	}
	
	$weekinfoinfo = json_decode($weekly, true);
	$results = $weekinfoinfo['weeklyResults']['matchup'];
	
	foreach ($results as $franchise){
		$game = $franchise['franchise'];
			foreach ($game as $starters){
				//$games[$starters['id']] = $starters['starters'];
				$player = $starters['player'];
				$teamid = $starters['id'];
					foreach ($player as $boxes){
						if ($boxes['status'] == 'starter'){
							$games[$teamid][$boxes['id']] = $boxes['score'];
						}
					}
			
			}
	}
	
	// change mfl ids to PFL
	foreach ($games as $key => $value){
		$newgames[$teammflid[$key]] = $value;
	}

	
    set_transient( 'mfl_team_scores_'.$week, $newgames, '' );
    return $newgames;
  }

}
$newgames = set_week_mfl_trans();		
	
// printr($newgames, 0);
// printr($results, 0);


$i = 1;

while ($i < 15){
	$allweeks[$i] = get_transient( 'mfl_team_scores_'.$i );
	$i++;
}

$j = 1;

foreach ($allweeks as $weeks){
	foreach ($weeks as $key => $team){
		if (isset($team[$mflplayer])){
			$store[$j] = array(
			$key => $team[$mflplayer]
			);
		}
	}
	$j++;
}

foreach ($store as $key => $value){
	
	$pad = sprintf("%02d", $key);
	$theid = $year.$pad;
	
	foreach ($value as $subkey => $subvalue){
		$teamsub = $subkey;
		$pointssub = $subvalue;
		$getteam = get_team_results($teamsub);
		$versus = $getteam[$theid]['versus'];
	}
	$forinsert[] = array(
		$theid, $year, $key, $pointssub, $teamsub, $versus, ''
	);
}

printr($forinsert, 0);

echo 'INSERT INTO '.$pflplayer.' (week_id, year, week, points, team, versus, season)<br>';
echo ' VALUES ';
foreach ($forinsert as $insert){
	$sep = ', ';
	
	if ($insert === end($forinsert)) {
        $sep = '; ';
    }
	
	echo '("'.$insert[0].'",'.$insert[1].','.$insert[2].','.$insert[3].',"'.$insert[4].'","'.$insert[5].'", 0)'.$sep.'<br> ';
}

$expanded = get_team_results_expanded($pflplayer);

?>

<?php get_footer(); ?>