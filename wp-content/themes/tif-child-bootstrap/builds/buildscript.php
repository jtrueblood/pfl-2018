<?php
/*
 * Template Name: Build Script
 * Description: Master Build Script Page
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	$season = date("Y");
	$firstyear = 1991;
	$currentyear = 2015;
	$allWeeksZero = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14");
	get_header(); 

					
	// First make array of all seasons
	
	function allseasons(){	
		$cache_theyears = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/theyears.txt';
		if (file_exists($cache_theyears)){
			$get_theyears = file_get_contents($cache_theyears);
			$data_theyears = unserialize($get_theyears);
			$number_keys = count(array_keys($data_theyears));
		
			echo '<h4><span class="text-bold">theyears.txt</span> is in cache</h4>';
						
		} else {
		   		$arr_theyear = array();
				for($y = 1991; $y <= $currentyear; $y++){
					$arr_theyear[] = $y;
				}
			echo '<h4><span class="text-bold">theyears.txt</span> is built from Array</h4>';
			$put_theyears = serialize($arr_theyear);
			file_put_contents($cache_theyears, $put_theyears);
		}
	}

	
	get_cache('theyears', 0);	
	$theyears = $_SESSION['theyears'];
	
	$allweekids = array();
	
	foreach($theyears as $eachyear){
		foreach($allWeeksZero as $eachweek){
			$allweekids[] = $eachyear.$eachweek;
		}
	}
	
	$cache_allweekids = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/allweekids.txt';
	if (file_exists($cache_allweekids)){
		$get_allweekids = file_get_contents($cache_allweekids);
		$data_allweekids = unserialize($get_allweekids);
		$number_keys = count(array_keys($data_allweekids));
	
		echo '<h4><span class="text-bold">allweekids.txt</span> is in cache</h4>';
					
	} else {
	   	
		echo '<h4><span class="text-bold">allweekids.txt</span> is built from Array</h4>';
		$put_allweekids = serialize($allweekids);
		file_put_contents($cache_allweekids, $put_allweekids);
	}
	
	
		
	?>
	
		
<?php get_footer(); ?>