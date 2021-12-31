<?php
/*
 * Template Name: Build Postseason Boxscores
 * Description: Master Build for generating player boxscores.
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	$firstyear = 1991;
	$year = 20;
	
	
	
	get_header(); 
	
	get_cache('champions', 0);	
	$champions = $_SESSION['champions'];
	
	get_cache('probowl', 0);	
	$probowl = $_SESSION['probowl'];
	
	get_cache('postseasonbox/2014plbox', 0);	
	$pl_sample = $_SESSION['postseasonbox/2014plbox'];
	
	get_cache('postseasonbox/2014pbbox', 0);	
	$pb_sample = $_SESSION['postseasonbox/2014pbbox'];
	
	get_cache('postseasonbox/2014probox', 0);	
	$pro_sample = $_SESSION['postseasonbox/2014probox'];
	
	// open mysql
	$mydb = new wpdb('root','eur0TRASH','pflmicro','173.194.240.57');

// build champions table 
$championscache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/champions.txt';

if (file_exists($championscache)){

	$championsget = file_get_contents($championscache);
	$champions = unserialize($championsget);

	echo '<pre>champions from cache...</pre>';
	//printr($champions, 0);

} else {
		
	$query = $mydb->get_results("select * from champions", ARRAY_N);
	
	$buildnew = array();
	foreach ($query as $revisequery){
		$buildnew[$revisequery[0]] = array(
		$revisequery[0], 
		$revisequery[1], 
		$revisequery[2], 
		$revisequery[3], 
		$revisequery[4], 
		$revisequery[5],
		$revisequery[6],
		$revisequery[7],
		$revisequery[8]
		);
	}

	$championsput = serialize($buildnew);
	file_put_contents($championscache, $championsput);

	echo '<pre>champions table from MySQL...</pre>';
	//printr($buildnew, 0);

}
	
// playoff build	
$playoffcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/postseasonbox/'.$year.'plbox.txt';
if (file_exists($playoffcache)){
	
	$playoffget = file_get_contents($playoffcache);
	$playoffs = unserialize($playoffget);
	
	echo '<pre>'.$year.'plbox from cache...</pre>';
	//printr($playoffs, 0);

} else {
		
	$query = $mydb->get_results("select * from playoffs where YEAR = $year && WEEK = 15", ARRAY_N);
	
	$buildnew = array();
	foreach ($query as $revisequery){
		$buildnew[] = array(
		$revisequery[0], 
		$revisequery[1], 
		$revisequery[2], 
		$revisequery[3], 
		$revisequery[4], 
		$revisequery[5],
		$revisequery[6]
		);
	}

	$playoffput = serialize($buildnew);
	file_put_contents($playoffcache, $playoffput);

	echo '<pre>playoffs table from MySQL...</pre>';
	//printr($buildnew, 0);

}



// posse bowl build
$possebowlcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/postseasonbox/'.$year.'pbbox.txt';
if (file_exists($possebowlcache)){
	
	$possebowlget = file_get_contents($possebowlcache);
	$possebowl = unserialize($possebowlget);
	
	echo '<pre>'.$year.'pbbox from cache...</pre>';
	//printr($possebowl, 0);

} else {
		
	$query = $mydb->get_results("select * from playoffs where YEAR = $year && WEEK = 16", ARRAY_N);
	
	$buildnew = array();
	foreach ($query as $revisequery){
		$buildnew[] = array(
		$revisequery[0], 
		$revisequery[1], 
		$revisequery[2], 
		$revisequery[3], 
		$revisequery[4], 
		$revisequery[5],
		$revisequery[6]
		);
	}

	$possebowlput = serialize($buildnew);
	file_put_contents($possebowlcache, $possebowlput);

	echo '<pre>possebowl table from MySQL...</pre>';
	//printr($buildnew, 0);

}

// probowl results build 
$progamecache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/probowl.txt';

if (file_exists($progamecache)){

	$progameget = file_get_contents($progamecache);
	$progame = unserialize($progameget);

	echo '<pre>probowl from cache...</pre>';
	//printr($champions, 0);

} else {
		
	$query = $mydb->get_results("select * from probowl", ARRAY_N);
	
	$buildnew = array();
	foreach ($query as $revisequery){
		$buildnew[] = array(
		$revisequery[0], 
		$revisequery[1], 
		$revisequery[2], 
		$revisequery[3], 
		$revisequery[4], 
		$revisequery[5],
		$revisequery[6],
		$revisequery[7],
		$revisequery[8]
		);
	}

	$progameput = serialize($buildnew);
	file_put_contents($progamecache, $progameput);

	echo '<pre>probowl from MySQL...</pre>';
	//printr($buildnew, 0);

}


// pro bowl boxscore build

$probowlcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/postseasonbox/'.$year.'probox.txt';
if (file_exists($probowlcache)){
	
	$probowlget = file_get_contents($probowlcache);
	$probowl = unserialize($probowlget);
	
	echo '<pre>'.$year.'probox from cache...</pre>';
	//printr($probowl, 0);

} else {
		
	$query = $mydb->get_results("select * from probowlbox where YEAR = $year", ARRAY_N);
	
	$buildnew = array();
	foreach ($query as $revisequery){
		$buildnew[] = array(
		$revisequery[0], 
		$revisequery[1], 
		$revisequery[2], 
		$revisequery[3], 
		$revisequery[4], 
		$revisequery[5],
		$revisequery[6],
		$revisequery[7],
		$revisequery[8]
		);
	}

	$probowlput = serialize($buildnew);
	file_put_contents($probowlcache, $probowlput);

	echo '<pre>probowl table from MySQL...</pre>';
	//printr($buildnew, 0);

}

// get a single text file that has all of the pro bowl selecttions in a [year] (player) format

$allprocache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/postseasonbox/allpro.txt';
if (file_exists($allprocache)){
	
	$allproget = file_get_contents($allprocache);
	$allpro = unserialize($allproget);
	
	echo '<pre>allpro from cache...</pre>';
	printr($allpro, 0);
	
} else {
		
	$query = $mydb->get_results("select * from probowlbox", ARRAY_N);
	
	$buildnew = array();
	foreach ($query as $revisequery){
		$buildnew[] = array($revisequery[1] => $revisequery[5]);
	}

	$allproput = serialize($buildnew);
	file_put_contents($allprocache, $allproput);

	echo '<pre>allpro table from MySQL...</pre>';
	//printr($buildnew, 0);

}



?>
		

	
<?php session_destroy(); ?>			


		
<?php get_footer(); ?>