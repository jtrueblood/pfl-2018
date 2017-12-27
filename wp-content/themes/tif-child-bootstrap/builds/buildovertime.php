<?php
/*
 * Template Name: Build Overtime
 * Description: Master Build for generating overtime.txt file */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); ?>
<div class="add-to-top"></div>
<?php

$firstyear = 1991;
$year = 2015;
$allWeeksZero = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14");

while ($year >= $firstyear){
	$theyears[] = $firstyear;
	$firstyear++;	
}



$cache_theyears = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/theyears.txt';
if (file_exists($cache_theyears)){
	$get_theyears = file_get_contents($cache_theyears);
	$data_theyears = unserialize($get_theyears);

	echo '<pre>theyears.txt is in cache</pre>';
	printr($data_theyears, 0);
				
} else {
		$y = 1991; 
		while($y <= $year){
			$arr_theyear[] = $y;
			$y++;
		}
	echo '<pre>theyears.txt is built from Array</pre>';
	printr($arr_theyear, 0);
	$put_theyears = serialize($arr_theyear);
	file_put_contents($cache_theyears, $put_theyears);
}




get_cache('allweekids', 0);	
$allweekids = $_SESSION['allweekids'];



$weekidcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/allweekids.txt';
if (file_exists($weekidcache)){
	echo '<pre>Week Ids exist.</pre>';
	printr($allweekids, 0);
} else {
	foreach ($theyears as $build){
		foreach ($allWeeksZero as $subbuild){
			$allweeksare[] =  $build.$subbuild;
		}
	}
	$weekput = serialize($allweeksare);
	file_put_contents($weekidcache, $weekput);
	echo '<pre>Built week IDs</pre>';
}




$otcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/overtime.txt';
if (file_exists($otcache)){
	
	$otget = file_get_contents($otcache);
	$overtime = unserialize($otget);
	
	echo '<pre>overtime.txt from cache...</pre>';
	printr($overtime, 0);

} else {
	
	$mydb = new wpdb('root','root','pflmicro','localhost');
	$otquery = $mydb->get_results("select * from overtime", ARRAY_N);
	
	$buildnew = array();
	foreach ($otquery as $revisequery){
		$buildnew[$revisequery[0]] = array(
		$revisequery[0], 
		$revisequery[1], 
		$revisequery[2], 
		$revisequery[3], 
		$revisequery[4], 
		$revisequery[5],
		$revisequery[6],
		$revisequery[7],
		$revisequery[8],
		$revisequery[9],
		$revisequery[10],
		$revisequery[11],
		$revisequery[12]
		);
	}

	$otput = serialize($buildnew);
	file_put_contents($otcache, $otput);

	echo '<pre>overtime table from MySQL...</pre>';
	printr($buildnew, 0);

	
}

?>
<!--CONTENT CONTAINER-->
<div class="boxed">
	<div id="content-container">
		<div id="page-content">
			<div class="row">
				
				<div class="col-sm-6">
				
				</div>
				
			</div>
		</div>
	</div>
</div>

<?php
	
	
			


get_footer(); ?>