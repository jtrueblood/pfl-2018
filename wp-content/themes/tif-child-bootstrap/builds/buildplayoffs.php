<?php
/*
 * Template Name: Build Playoffs
 * Description: Master Build for generating single season playoff data as well as updating the single playoffall.txt file */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); ?>
<div class="add-to-top"></div>
<?php


// updated in 2017 to build 2016 data
$year = 2016;

$playoff_teams = array (
	1 => 'HAT',
	2 => 'SNR',
	3 => 'BUL',
	4 => 'SON'
	);

$theidsyntax = array('0110','0120','0130','0140','0210','0220','0230','0240','0310','0320','0330','0340','0410','0420','0430','0440','1110','1120','1130','1140','1210','1220','1230','1240','1310','1320','1330','1340','1410','1420','1430','1440'	
);

// create insert statement for 'playoffs' table

$printpl .= "INSERT INTO playoffs (id,year,week,playerid,points,team,versus)<br/>VALUES ";

$theweek = 15;
$e = 1;
foreach ($theidsyntax as $getid){
	
	$seed = substr($getid, 1, -2);
	$position = substr($getid, 2, 1);
	$team = $playoff_teams[$seed];
	if ($seed == 1){$vsseed = 4;}
	if ($seed == 2){$vsseed = 3;}
	if ($seed == 3){$vsseed = 2;}
	if ($seed == 4){$vsseed = 1;}
	$vs = $playoff_teams[$vsseed];
	
	$printpl .= "('".$year.$getid."',";
	$printpl .= "'".$year."',";
	if ($e > 16){
		$theweek = 16;
		$vs = null;
	}
	$printpl .= $theweek.",";
	$printpl .= "'',";
	$printpl .= "'',";
	$printpl .= "'".$team."',";
	$printpl .= "'".$vs."')";
	if ($getid !== end($theidsyntax)){
		$printpl .= ",";
	} else {
		$printpl .= ";";
	}
	$printpl .= "<br/>";
	
	$e++;
}


printr($printpl,0);





// build txt file from database
$playoffcache = '/wp-content/themes/tif-child-bootstrap/cache/playoffall.txt';

if (file_exists($playoffcache)){
	
	$playoffget = file_get_contents($playoffcache);
	$playoffdata = unserialize($playoffget);
	$playoffcount = count(array_keys($playoffdata));	

	
	echo '<pre>';
		echo 'playoffall.txt From Cache<br>';
		print_r($playoffdata);
	echo '</pre>';
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');
	$playoffquery = $mydb->get_results("select * from playoffs", ARRAY_N);
	
	$buildnew = array();
	foreach ($playoffquery as $revisequery){
		$buildnew[$revisequery[0]] = array($revisequery[0], $revisequery[1], $revisequery[2], $revisequery[3], $revisequery[4], $revisequery[5], $revisequery[6]);
	}
	
	$putplayoff = serialize($buildnew);
	file_put_contents($playoffcache, $putplayoff);
	

	echo '<pre>';
		echo '<pre>playoffall.txt From MySQL...';
		print_r($buildnew);
	echo '</pre>';
}




			


get_footer(); ?>