<?php
/*
 * Template Name: Fantasy Data
 * Description: Arrays from the Fantasy Data API
 */
 ?>


<?php get_header(); ?>

<?php

$apikey = '610b08432214484cbb360777ed373371';
$date = '11-4-2015';
// $playerid = '4314';


/*  Pull IDs from Fantasy Data */

$idcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/fantasydata/fantasydataids.txt';
$playerscache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/fantasydata/fantasydataplayers.txt';
$justidcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/fantasydata/justfdid.txt';			

if (file_exists($idcache)){
	
	echo 'FILES EXISTS';
	
} else {

$fantasyplayers = file_get_contents('https://api.fantasydata.net/nfl/v2/json/DailyFantasyPlayers/'.$date.'?key='.$apikey.'');
$fd_players = json_decode($fantasyplayers, true);
// objectToArray($fd_players);

foreach ($fd_players as $value){
	$theid = $value['PlayerID'];
	$thename = $value['Name'];
	$explodename = explode(' ', $thename, 2);
	$newname = strtok($explodename[1],' ').', '.$explodename[0];
	$thepos = $value['Position'];
	if ($thepos == 'QB' or $thepos == 'RB' or $thepos == 'WR' or $thepos == 'PK'){
		$idarray[$newname] = $theid;
		$fdarray[] = array($theid, $newname, $thepos); 
		$justfdid[] = $theid;
	}
} 



$idcacheput = serialize($idarray);
file_put_contents($idcache, $idcacheput);

$fdarrayput = serialize($fdarray);
file_put_contents($playerscache, $fdarrayput);	

$justput = serialize($justfdid);
file_put_contents($justidcache, $justput);

}

get_cache('fantasydata/justfdid', 0);	
$justfdid = $_SESSION['fantasydata/justfdid'];



/* Manually added Players for guys that don't cleanly link in the logic about -- example (Tight Ends, Matthew = Matt, Jr.)
Use Fantasy Data API Keys -- can pull ids from API documentation or from Daily Fantasy Sports Rankings	
	
*/
$manualplayers = array(15048, 11756, 16389, 14986);
$allplayers = array_merge($justfdid, $manualplayers);


/* Pull the Player Details and Cache from Fantasy Data */

foreach ($allplayers as $theplayer){
	
	$detailscache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/fantasydata/'.$theplayer.'.txt';			
	if (file_exists($detailscache)){
	
	$detailsget = file_get_contents($detailscache);
	$detailsdata = unserialize($detailsget);
	
	echo $theplayer.'.txt exists<br/>';
	
				
	}  else  {
		
	$fantasydetails = file_get_contents('https://api.fantasydata.net/nfl/v2/json/Player/'.$theplayer.'?key='.$apikey.'');
	$fd_details = json_decode($fantasydetails, true);
		
	$detailsput = serialize($fd_details);
	file_put_contents($detailscache, $detailsput);
	
	echo $theplayer.'.txt file built from FD API<br/>';	
	
	
	}

}


?>


<?php get_footer(); ?>
