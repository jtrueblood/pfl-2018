<?php
/*
 * Template Name: Build All Player Data
 * Description: Master Build for generating an array of all of the players career stats.  Requires that individual player boxscores are created first.  Individual player boxcores are dependent on 'players' and 'playersid' are updated first.
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
	
	get_cache('allplayerdata', 0);	
	$allplayerdata = $_SESSION['allplayerdata'];
	
?>
	
<?php
$playerdatacache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/allplayerdata.txt';

if (file_exists($playerdatacache)){
	
	echo '<h2>allplayerdata.txt returned from Cache</h2>';
	echo '<pre>';
		print_r($allplayerdata);
	echo '</pre>';
	

} else {

		
	foreach ($playersid as $file){
		$playercache = get_stylesheet_directory_uri() . '/cache/playerdata/'.$file.'.txt';
		$playerget = file_get_contents($playercache);
		$playerdata = unserialize($playerget);
		
		$buildplayerdata[] = $playerdata;
		
	}
	
	$put_theplayerdata = serialize($buildplayerdata);
	file_put_contents($playerdatacache, $put_theplayerdata);
	
	echo '<h2>Build player data returned from built text files</h2>';		
	?><pre><?php
	print_r($buildplayerdata);
	?></pre><?php
}		
			

get_footer(); ?>