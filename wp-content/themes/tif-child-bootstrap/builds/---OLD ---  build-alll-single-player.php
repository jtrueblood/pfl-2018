<?php
/*
 * Template Name: Build All Single Player
 * Description: Master Build for generating all of the single player data sheets.
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

get_cache('playersid', 0);	
$playersid = $_SESSION['playersid'];

/*
?><pre><?php
print_r($playersid);
?></pre><?php
*/



foreach ($playersid as $buildthemall){

	$playerA = $buildthemall;
	
	$playercache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/playerdata/'.$playerA.'.txt';
	
	if (file_exists($playercache)){
		
		
		echo $playerA.'.txt exists</br>';
		
					
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
		
		echo $playerA.'.txt From MySQL...'.date("h:i:sa").'<br/>';
		
		}

}

			


get_footer(); ?>