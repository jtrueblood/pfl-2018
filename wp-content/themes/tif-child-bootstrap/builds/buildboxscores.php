<?php
/*
 * Template Name: Build Boxscores
 * Description: Master Build for generating player boxscores.
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	$season = date("Y");
	$firstyear = 1991;
	$currentyear = 2016;
// 	$week = '199809';
	
	
	get_header(); 
	
	get_cache('allplayerdata', 0);	
	$allplayerdata = $_SESSION['allplayerdata'];
	
	get_cache('overtime', 0);	
	$theot = $_SESSION['overtime'];
	
	get_cache('boxscores/201514box', 0);	
	$samplebox = $_SESSION['boxscores/201514box'];
	
	get_cache('allweekids', 0);	
	$allweekids = $_SESSION['allweekids'];


    printr($samplebox, 0);
	

foreach ($allweekids as $theweeklyid){	
	$week = $theweeklyid;
	$boxcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/boxscores/'.$week.'box.txt';	
	$theweeksots = array($theot[$week.'01'],$theot[$week.'02'],$theot[$week.'03'],$theot[$week.'04']);
	$flatovertime = array_flatten($theweeksots);

	if (file_exists($boxcache)){
		
		echo '<h5>'.$week.'.txt returned from Cache</h5>';
		
	
	} else {		
		$r = 0;	
		foreach ($allplayerdata as $theweek){
			foreach ($theweek as $theplayer){
				$playerA = $theplayer[6];
				
				if ($theplayer[0] == $week){
					
					
					$overtime = 0;
					foreach($flatovertime as $gettheovertimes){
						if ($gettheovertimes == $playerA){
							$overtime = 1;
						}
					}
					
					$position = substr($theplayer[6], -2);
					$theboxscore[] = array($playerA,$theplayer[4],$theplayer[3],$position,$overtime);					
				}
			}
			
		}
		
/*
		?><pre><?php
		print_r($theboxscore);
		?></pre><?php
*/
		
		$put_theboxscore = serialize($theboxscore);
		file_put_contents($boxcache, $put_theboxscore);
		echo '<h5>'.$week.'.txt built in america from mysql with loving care.......</h5>';
		$put_theboxscore = null;
		$theboxscore = null;
		
	}
}	
?>



<!--	
	<pre><?php
	print_r($theot);
	?></pre>
	

	<pre><?php
	print_r($allplayerdata);
	?></pre>

	<pre><?php
	print_r($samplebox);
	?></pre>
-->	
	

		

	
<?php session_destroy(); ?>			
</div><!--End page content-->
</div><!--END CONTENT CONTAINER-->
</div>
		
<?php get_footer(); ?>