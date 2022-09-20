<?php
/*
 * Template Name: Build Drafts 
 * Description: Master Build for inserting annual draft from MFL into Database.  Was update in Aug 2018.  No cache files used. */


	
get_header(); 

// EACH SEASON AFTER THE DRAFT FOLLOW THESE STEPS  --------------------------------------------
// Starting in 2020 the API Validation for MFL now requires auth of the user agent.  The setup is completed for 2020.  May have to repeat in 2021.  


// STEPS

// 1. Check the 3 vars immediatly below.  You will need to update the year and origorder variable each season.  Somtimes lid changes
// 2. Authenticate in the browser with this url (Change Year) : https://api.myfantasyleague.com/2022/login?USERNAME=jtrueblood&PASSWORD=eur0TR@SH!&XML=1
// 2a.  You may need to go into Postman and setup a new browser autorization cookie
// 3. Run /builds/build-drafts
// 4. Check the draft list visually.  Also check the array of IDs at the bottom of the page and enter these into the 'Create New Player' area on the homepage to build the new player profiles.
// 5. Uncomment the section that will insert the draft into wp_drafts.sql table.  Reload the page.
// 6. Recomment that section.  Save and close.

// Auth URL = https://api.myfantasyleague.com/2022/login?USERNAME=jtrueblood&PASSWORD=eur0TR@SH!&XML=1

$year = 2022;
$lid = 38954;
// NO LONGER NEEDED AS OF 2020 -- $apikey = 'aRNp1sySvuKmx1qmO1HIZDYeFbox';
// Looks like you could pass an API Key instead of the user agent (not totally sure?) the api key for 2020 appears to be &APIKEY=aRNp1sySvuWvx0WmO1HIZDYeFbox

$origorder = array('BST','SNR','HAT','BUL','CMN','WRZ','DST','TSG','PEP','ETS');
// --------------------------------------------

// get actual draft report for the year from MFL
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://www58.myfantasyleague.com/2022/export?TYPE=draftResults&L=38954&JSON=1',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_HTTPHEADER => array(
        'Cookie: MFL_PW_SEQ=ah9q2M6Ss%2Bis3Q29; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D'
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo 'WORKED';
}

$mfldraft = json_decode($response, true);
$mflpicks = $mfldraft['draftResults']['draftUnit']['draftPick'];

$teamnames = teamid_mfl_to_name();

//printr($mflpicks[0], 0);
//printr($getorder, 0);

$countofpicks = count($mflpicks);

$pickarray = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10');

$conv_ids = playerid_mfl_to_pfl();
$assoc = get_players_assoc();

$o = 0; $i = 1; 
foreach ($mflpicks as $value){
	$roundpick = $pickarray[$o];
	$comment = $value['comments'];
	$round = $value['round'];
	$picknum = sprintf('%02d', $i);
	$roundnum = $value['pick'];
	$team = $teamnames[$value['franchise']];
	$playerid = $value['player'];
	
	if($origorder[$o] != $team){
		$tradeid = 1;
	} else {
		$tradeid = 0;
	}
	
	$getid = $conv_ids[$playerid];
	$playerinfo = $assoc[$getid];
	
	$firstn = $playerinfo[0];
	$lastn = $playerinfo[1];
	$posn = $playerinfo[2];
	
	if(isset($getid)){
		$id = $getid;	
	} else {
		$id = $playerid;
		$newplayers[] = $playerid;
	}
	

	if($playerid == '----'){
		$id = 'No Pick';
		$firstn = 'No Pick';
		$lastn = 'No Pick';
		$posn = 'No Pick';
	}
	// FOR 2019 - Check the 'No Pick' logic.  It doesn't insert picks into D.  Likly an issue with the mysql type format
	
	$draftinsert[] = array(
		'id' 			=> $year.$round.$roundnum.$picknum,
		'year' 			=> $year,
		'round'			=> $round,
		'roundnum'		=> $roundnum,
		'picknum'		=> $picknum,
		'pickord'		=> $origorder[$o],
		'team' 			=> $team,
		'playerfirst' 	=> $firstn,
		'playerlast' 	=> $lastn,
		'pos' 			=> $posn,
		'playerid' 		=> $id,
		'tradeid' 		=> $tradeid,
		'comment'		=> $comment 
	);

	$o++; $i++;
	
	if ($o == 10){ $o = 0; }
	 
}

printr($draftinsert, 0);
printr($newplayers, 0);

// uncomment and reload to insert info into wp_drafts once the array looks good



foreach($draftinsert as $arr){

	$wpdb->insert(
		 'wp_drafts',
	     array(
			'id' 			=> $arr['id'],
			'year' 			=> $arr['year'],
			'round'			=> $arr['round'],
			'roundnum'		=> $arr['roundnum'],
			'picknum'		=> $arr['picknum'],
			'pickord'		=> $arr['pickord'],
			'team' 			=> $arr['team'],
			'playerfirst' 	=> $arr['playerfirst'],
			'playerlast' 	=> $arr['playerlast'],
			'pos' 			=> $arr['pos'],
			'playerid' 		=> $arr['playerid'],
			'tradeid' 		=> $arr['tradeid']
	     ),
		 array(
			'%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d'
		 )
	);
}



// pass week parameter all players by ID and Score	
//$jsonallplayer = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=players&L='.$mflleagueid.'&W='.$week.'&JSON=1');

get_footer(); ?>