<?php
/*
 * Template Name: Build Drafts 
 * Description: Master Build for generating a master 'drafts.txt'  */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 
$year = 2017;
$lid = 38954;
$apikey = 'aRNp1sySvuKox1emO1HIZDYeFbox';

$allplayers = get_players_assoc();
$theplayers = get_players_index();

// check this each year to make sure they don't change
// get team info from API
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

//printr($theplayers, 1);




// this is something that should be replaced as a table in the future...
get_cache('mfl/linkidcache', 0);	
$linkidcache = $_SESSION['mfl/linkidcache'];

//printr($linkidcache, 0);

/*
get_cache('mfl/mfl-every-player', 0);	
$everyplayer = $_SESSION['mfl/mfl-every-player'];
*/


// get actual draft report for the year from MFL
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://www58.myfantasyleague.com/$year/export?TYPE=draftResults&L=$lid&APIKEY=$apikey&JSON=1",
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

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo "curl Worked";
}

$mfldraft = json_decode($response, true);

// pass week parameter all players by ID and Score	
//$jsonallplayer = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=players&L='.$mflleagueid.'&W='.$week.'&JSON=1');
$jsonallplayer = 'http://www58.myfantasyleague.com/'.$year.'/export?TYPE=players&DETAILS=&SINCE=&PLAYERS=&JSON=1';
$mflallplayers = json_decode($jsonallplayer, true);

$draftcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/drafts.txt';

/*
$get = get_mfl_player_details('13128');
printr($get, 0);
*/

?>
<!--CONTENT CONTAINER-->
<div class="boxed">

	<div id="content-container">
	
		<div id="page-content">
			
			<div class="row">
				
				
<?php
$draftpick = $mfldraft['draftResults']['draftUnit']['draftPick'];	
$draftorder = rtrim($mfldraft['draftResults']['draftUnit']['round1DraftOrder'], ',');

$getorder = explode(",",$draftorder);
 
printr($mflteamids, 0);	

/*
printr($linkidcache, 0);	
printr($draftpick, 0);

*/

$rightround = array('01','02','03','04','05','06','07','08','09','10');
$r = 1;
$s = 0;


$draftinsert .= "INSERT INTO drafts (id, year, round, roundnum, picknum, pickord, team, playerfirst, playerlast, pos, playerid, tradeid)<br/>VALUES ";

foreach ($draftpick as $builddraft){
	$getid = $builddraft['player'];
	$playerinformation = get_mfl_player_details($getid);
	$pflid = $linkidcache[$getid];
	
	$explodename = explode(",", $playerinformation['name'], 2);
	
	if ($playerinformation['position'] == 'TE'){
		$pos = 'WR';
	} else {
		$pos = $playerinformation['position'];
	}
	
	//printr($playerinformation, 0);
	
	$team = $teamids[$builddraft['franchise']];
	$playerfirst = $explodename[1];
	$playerlast = $explodename[0];
	$justfour = substr($playerlast, 0, 4);
	$position = $pos;
	$buildid = $year.$justfour.$position;
	$tradeid = '';
	$id = $year.$round.$pick.$overall;
	
	if(!empty($pflid)){
		$playerid = $pflid[2];
	} else {
		$playerid = $buildid;
	}
	$round = $builddraft['round'];
	$pick = $builddraft['pick'];
	$overall = sprintf("%02d", $r);
	$getfirst = $builddraft['pick'];
	
	$order = $teamids[$getorder[$s]];
	
	
	

	$r++;
	$s++;
	if ($s == 10){
		$s = 0;
	}
	
	$draftinsert .= "('".$id."',";
	$draftinsert .= "'".$year."',";
	$draftinsert .= "'".$round."',";
	$draftinsert .= "'".$pick."',";
	$draftinsert .= "'".$overall."',";
	$draftinsert .= "'".$order."',";
	$draftinsert .= "'".$team."',";
	$draftinsert .= "'".$playerfirst."',";
	$draftinsert .= "'".$playerlast."',";
	$draftinsert .= "'".$position."',";
	$draftinsert .= "'".$playerid."',";
	$draftinsert .= "'".$tradeid."'),</br>";
}

$draftinsert .= ';';

printr($draftinsert, 0);


?>
			
			
			
			</div>
		
			<div class="row">
		
<?php


echo '<div class="col-xs-8"><pre>';
	echo '<h5>File Exists -- drafts.txt From Cache...</h5>';
	print_r($draftdata);
echo '</pre></div>';


echo '<div class="col-xs-8"><pre>';
	echo '<h5>drafts.txt Written From MySQL...</h5>';
	print_r($buildnew);
echo '</pre></div>';

?>

			</div>
		</div>
	</div>
</div>


<?php get_footer(); ?>