<?php
/*
 * Template Name: Build Awards
 * Description: Master Build for generating cached award files.  
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 



$awardscache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/awards.txt';

$mvpcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/mvp.txt';
$procache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/promvp.txt';
$ootycache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/ooty.txt';
$rotycache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/roty.txt';
$bowlcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/bowlmvp.txt';
$hallcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/hall.txt';

if (file_exists($awardscache)){
	
	$awardsget = file_get_contents($awardscache);
	$awardsdata = unserialize($awardsget);
	$awardscount = count(array_keys($awardsdata));	

	?><pre><?php
	echo 'awards.txt From Cache<br>';
	print_r($awardsdata);

	?></pre><?php
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');
	$awardsquery = $mydb->get_results("select * from awards", ARRAY_N);
	
	$buildnew = array();
	foreach ($awardsquery as $revisequery){
		$buildnew[$revisequery[0]] = array(
		"awardid" => $revisequery[0], 
		"award" => $revisequery[1], 
		"year" => $revisequery[2], 
		"first" => $revisequery[3], 
		"last" => $revisequery[4], 
		"team" => $revisequery[5],
		"position" => $revisequery[6],
		"owner" => $revisequery[7],
		"pid" => $revisequery[8],
		"gamepoints" => $revisequery[9]
		);
	}
	
/*
	?><pre><?php
	print_r($buildnew);
	?></pre><?php
*/
	
	ksort($buildnew);
	foreach ($buildnew as $key => $val) {
    	$newsort[] = $val;
	}
	
	$putawards = serialize($newsort);
	file_put_contents($awardscache, $putawards);

	?><pre><?php
	echo '<pre>awards.txt From MySQL...';
	print_r($newsort);
	?></pre><?php
}


if (file_exists($mvpcache)){
	
	$mvpget = file_get_contents($mvpcache);
	$mvpdata = unserialize($mvpget);
	$mvpcount = count(array_keys($mvpdata));	

	?><pre><?php
	echo 'mvp.txt From Cache<br>';
	?></pre><?php
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');	
	$mvpquery = $mydb->get_results("select * from awards where award = 'Most Valuable Player'", ARRAY_N);
	
	$buildmvp = array();
	foreach ($mvpquery as $revisequery){
		$buildmvp[$revisequery[2]] = array(
			"awardid" => $revisequery[0], 
			"award" => $revisequery[1], 
			"year" => $revisequery[2], 
			"first" => $revisequery[3], 
			"last" => $revisequery[4], 
			"team" => $revisequery[5],
			"position" => $revisequery[6],
			"owner" => $revisequery[7],
			"pid" => $revisequery[8],
			"gamepoints" => $revisequery[9]
		);
	}
	

	
	ksort($buildmvp);
	foreach ($buildmvp as $key => $val) {
    	$mvpsort[] = $val;
	}
	
	$putmvp = serialize($mvpsort);
	file_put_contents($mvpcache, $putmvp);


	?><pre><?php
	echo '<pre>mvp.txt From MySQL...';
	?></pre><?php
}

if (file_exists($procache)){
	
	$proget = file_get_contents($procache);
	$prodata = unserialize($proget);
	$procount = count(array_keys($prodata));	

	?><pre><?php
	echo 'promvp.txt From Cache<br>';
// 	print_r($prodata);
	?></pre><?php
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');	
	$proquery = $mydb->get_results("select * from awards where award = 'Pro Bowl MVP'", ARRAY_N);
	
	$buildpro = array();
	foreach ($proquery as $revisequery){
		$buildpro[$revisequery[2]] = array(
			"awardid" => $revisequery[0], 
			"award" => $revisequery[1], 
			"year" => $revisequery[2], 
			"first" => $revisequery[3], 
			"last" => $revisequery[4], 
			"team" => $revisequery[5],
			"position" => $revisequery[6],
			"owner" => $revisequery[7],
			"pid" => $revisequery[8],
			"gamepoints" => $revisequery[9]
		);
	}
	
	
	ksort($buildpro);
	foreach ($buildpro as $key => $val) {
    	$prosort[] = $val;
	}
	
	$putpro = serialize($prosort);
	file_put_contents($procache, $putpro);

	?><pre><?php
	echo 'promvp.txt From MySQL...';
	
	?></pre><?php
}


if (file_exists($bowlcache)){
	
	$bowlget = file_get_contents($bowlcache);
	$bowldata = unserialize($bowlget);
	$bowlcount = count(array_keys($bowldata));	

	?><pre><?php
	echo 'bowlmvp.txt From Cache<br>';
// 	print_r($bowldata);
	?></pre><?php
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');	
	$bowlquery = $mydb->get_results("select * from awards where award = 'Posse Bowl MVP'", ARRAY_N);
	
	$buildbowl = array();
	foreach ($bowlquery as $revisequery){
		$buildbowl[$revisequery[2]] = array(
			"awardid" => $revisequery[0], 
			"award" => $revisequery[1], 
			"year" => $revisequery[2], 
			"first" => $revisequery[3], 
			"last" => $revisequery[4], 
			"team" => $revisequery[5],
			"position" => $revisequery[6],
			"owner" => $revisequery[7],
			"pid" => $revisequery[8],
			"gamepoints" => $revisequery[9]
		);
	}
	
	
	ksort($buildbowl);
	foreach ($buildbowl as $key => $val) {
    	$bowlsort[] = $val;
	}
	
	$putbowl = serialize($bowlsort);
	file_put_contents($bowlcache, $putbowl);

	?><pre><?php
	echo 'bowlmvp.txt From MySQL...';
	
	?></pre><?php
}
		
		
if (file_exists($ootycache)){
	
	$ootyget = file_get_contents($ootycache);
	$ootydata = unserialize($ootyget);
	$ootycount = count(array_keys($ootydata));	

	?><pre><?php
	echo 'ootys.txt From Cache<br>';
// 	print_r($ootydata);
	?></pre><?php
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');	
	$ootyquery = $mydb->get_results("select * from awards where award = 'Owner of the Year'", ARRAY_N);
	
	$buildooty = array();
	foreach ($ootyquery as $revisequery){
		$buildooty[$revisequery[2]] = array(
			"awardid" => $revisequery[0], 
			"award" => $revisequery[1], 
			"year" => $revisequery[2], 
			"first" => $revisequery[3], 
			"last" => $revisequery[4], 
			"team" => $revisequery[5],
			"position" => $revisequery[6],
			"owner" => $revisequery[7],
			"pid" => $revisequery[8],
			"gamepoints" => $revisequery[9]
		);
	}
	
	
	ksort($buildooty);
	foreach ($buildooty as $key => $val) {
    	$ootysort[] = $val;
	}
	
	$putooty = serialize($ootysort);
	file_put_contents($ootycache, $putooty);

	?><pre><?php
	echo 'ooty.txt From MySQL...';
	
	?></pre><?php
}
		
if (file_exists($rotycache)){
	
	$rotyget = file_get_contents($rotycache);
	$rotydata = unserialize($rotyget);
	$rotycount = count(array_keys($rotydata));	

	?><pre><?php
	echo 'roty.txt From Cache<br>';
// 	print_r($rotydata);
	?></pre><?php
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');	
	$rotyquery = $mydb->get_results("select * from awards where award = 'Rookie of the Year'", ARRAY_N);
	
	$buildroty = array();
	foreach ($rotyquery as $revisequery){
		$buildroty[$revisequery[2]] = array(
			"awardid" => $revisequery[0], 
			"award" => $revisequery[1], 
			"year" => $revisequery[2], 
			"first" => $revisequery[3], 
			"last" => $revisequery[4], 
			"team" => $revisequery[5],
			"position" => $revisequery[6],
			"owner" => $revisequery[7],
			"pid" => $revisequery[8],
			"gamepoints" => $revisequery[9]
		);
	}
	
	
	ksort($buildroty);
	foreach ($buildroty as $key => $val) {
    	$rotysort[] = $val;
	}
	
	$putroty = serialize($rotysort);
	file_put_contents($rotycache, $putroty);

	?><pre><?php
	echo 'roty.txt From MySQL...';
	
	?></pre><?php
}

if (file_exists($hallcache)){
	
	$hallget = file_get_contents($hallcache);
	$halldata = unserialize($hallget);
	$hallcount = count(array_keys($halldata));	

	?><pre><?php
	echo 'hall.txt From Cache<br>';
// 	print_r($halldata);
	?></pre><?php
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');	
	$hallquery = $mydb->get_results("select * from awards where award = 'Hall of Fame Inductee'", ARRAY_N);
	
	$buildhall = array();
	foreach ($hallquery as $revisequery){
		$buildhall[$revisequery[2]] = array(
			"awardid" => $revisequery[0], 
			"award" => $revisequery[1], 
			"year" => $revisequery[2], 
			"first" => $revisequery[3], 
			"last" => $revisequery[4], 
			"team" => $revisequery[5],
			"position" => $revisequery[6],
			"owner" => $revisequery[7],
			"pid" => $revisequery[8],
			"gamepoints" => $revisequery[9]
		);
	}
	
	
	ksort($buildhall);
	foreach ($buildhall as $key => $val) {
    	$hallsort[] = $val;
	}
	
	$puthall = serialize($hallsort);
	file_put_contents($hallcache, $puthall);

	?><pre><?php
	echo 'hall.txt From MySQL...';
	
	?></pre><?php
}
			


get_footer(); ?>