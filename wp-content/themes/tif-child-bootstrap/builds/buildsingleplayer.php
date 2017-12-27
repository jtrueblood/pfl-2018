<?php
/*
 * Template Name: Build Single Player
 * Description: Master Build for generating single player data.
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 

?>
<!--
<p>Player ID: <input type="text" name="playerid" /></p>
<input type="submit" value="Update Player" />
<p></p>
-->
<?php

/*
get_cache('team/ETS_f', 0);	
$ets = $_SESSION['team/ETS_f'];
print_r($ets);
*/

$playerA = '1994FaulRB';


$playercache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/playerdata/'.$playerA.'.txt';

if (file_exists($playercache)){
	
	$playerget = file_get_contents($playercache);
	$playerdata = unserialize($playerget);
	$playercount = count(array_keys($playerdata));	

	echo $playerA.'.txt From Cache<br>';
	echo '<pre>';
		print_r($playerdata);
	echo '</pre>';
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');
	$playerquery = $mydb->get_results("select * from $playerA", ARRAY_N);
	
	
	$buildnew = array();
	foreach ($playerquery as $revisequery){
		$weekid = $revisequery[0];
		$vsteam = $revisequery[5];
		
		get_cache('team/'.$vsteam.'_f', 0);	
		$playing = $_SESSION['team/'.$vsteam.'_f'];
		$result = $playing[$weekid]['result'];
		$venue = $playing[$weekid]['venue'];
		if ($venue == 'A'){
			$newvenue = 'H';
		}
		if ($venue == 'H'){
			$newvenue = 'A';
		}
		
		$buildnew[] = array($revisequery[0], $revisequery[1], $revisequery[2], $revisequery[3], $revisequery[4], $revisequery[5], $playerA, $result, $newvenue);
	}
	
	$putplayer = serialize($buildnew);
	file_put_contents($playercache, $putplayer);
	
	echo '<pre>'.$playerA.'.txt From MySQL...';
	echo '<pre>';
		print_r($buildnew);
	echo '</pre>';
}



			


get_footer(); ?>