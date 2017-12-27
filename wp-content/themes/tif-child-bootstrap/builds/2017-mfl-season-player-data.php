<?php
/*
 * Template Name: MFL 2017 Season Player Data
 * Description: MFL 2017 Season Player Data to export MFL Season data to import into main history database by playerID
 */
 ?>


<?php get_header(); ?>


<?php 
$year = 2017;
// $week = 1;
$lid = 38954;
$apikey = 'aRNp1sySvuKox1emO1HIZDYeFbox';


// get team ids 
/*
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://www58.myfantasyleague.com/$year/export?TYPE=league&L=$lid&APIKEY=$apikey&JSON=1",
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

$mflteaminfo = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo "curl Worked";
}

$teaminfo = json_decode($mflteaminfo, true);
$franchises = $teaminfo['league']['franchises']['franchise'];
foreach ($franchises as $key => $value){
	$teamids[$value['id']] = $value['abbrev'];
}

printr($teamids, 0);
*/


http://www58.myfantasyleague.com/2017/export?TYPE=playerScores&L=38954&W=1&YEAR=2017&PLAYERS=&POSITION=&STATUS=starter&RULES=&COUNT=&JSON=1


// get weekly starters
$week = 1;

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
	



printr($games, 0);
// printr($results, 0);





?>

<?php get_footer(); ?>