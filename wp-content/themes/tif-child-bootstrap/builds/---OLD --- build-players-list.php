<?php
/*
 * Template Name: Build Players List
 * Description: Build players.txt and just playersid.txt
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header();			

get_cache('players', 0);	
$players = $_SESSION['players'];


$playercache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/players.txt';
$playerassoc = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/playersassoc.txt';

if (file_exists($playercache)){
	
	$playerget = file_get_contents($playercache);
	$playerdata = unserialize($playerget);
	$playercount = count(array_keys($playerdata));	
// 	printr($playerdata, 0);
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');
	$query = $mydb->get_results("select * from players", ARRAY_N);
	
	foreach ($query as $revisequery){
		$buildnew[] = array(
			$revisequery[0], 
			$revisequery[1], 
			$revisequery[2], 
			$revisequery[3],
			$revisequery[4]);	
	}
	
	$putplayer = serialize($buildnew);
	file_put_contents($playercache, $putplayer);
	
	foreach ($query as $revisequery){
		$buildassoc[$revisequery[0]] = array(
			$revisequery[1], 
			$revisequery[2], 
			$revisequery[3],
			$revisequery[4]);
	}
	
	$putplayerassoc = serialize($buildassoc);
	file_put_contents($playerassoc, $putplayerassoc);
	
}


get_cache('playersid', 0);	
$playersid = $_SESSION['playersid'];

$playeridcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/playersid.txt';

if (file_exists($playeridcache)){
	
	$playeridget = file_get_contents($playeridcache);
	$playeriddata = unserialize($playeridget);
	$playeridcount = count(array_keys($playeriddata));	
	printr($playeriddata, 0);
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');	
	$query = $mydb->get_results("select * from players", ARRAY_N);
	
	foreach ($query as $revisequery){
		$buildnewid[] = $revisequery[0];		
	}
	
	$putplayerid = serialize($buildnewid);
	file_put_contents($playeridcache, $putplayerid);
	
}


//  this builds 'player details' which will suppliment college, age, height, etc. for players who don't have it.

$detailscache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/playerdetails.txt';
if (file_exists($detailscache)){
	
	$detailsget = file_get_contents($detailscache);
	$details = unserialize($detailsget);
	
	echo '<pre>details.txt from cache...</pre>';
	printr($details, 0);

} else {
	
	$mydb = new wpdb('root','root','pflmicro','localhost');	
	$detailsquery = $mydb->get_results("select * from playerdetails", ARRAY_N);
	
	$buildnew = array();
	foreach ($detailsquery as $revisequery){
		$buildnew[$revisequery[0]] = array(
		'College' => $revisequery[1], 
		'Height' => $revisequery[2], 
		'Weight' => $revisequery[3], 
		'Age' => $revisequery[4], 
		'Number' => $revisequery[5]
		);
	}

	$detailsput = serialize($buildnew);
	file_put_contents($detailscache, $detailsput);

	echo '<pre>details table from MySQL...</pre>';
	printr($buildnew, 0);

	
}



/*
'p_id' => $revisequery[0], 
'playerFirst' => $revisequery[1], 
'playerLast' => $revisequery[2], 
'position' => $revisequery[3],
'rookie' => $revisequery[4]);
*/

get_footer(); ?>