<?php
/*
* Template Name: Build Teams
* Description: Master Build for generating team files 'WRZ.txt' and associated versus file 'WRZversus.txt'
*/
?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 


$theyear = 2014;


function build_team($teamid){
	$cache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/team/'.$teamid.'.txt';
	
	if (file_exists($cache)){
		
		$get = file_get_contents($cache);
		$data = unserialize($get);
		$count = count(array_keys($data));	
	
		echo '<pre>';
			echo $teamid.'.txt From Cache<br>';
// 			print_r($data);
		echo '</pre>';
					
	}  else {
		$mydb = new wpdb('root','root','pflmicro','localhost');
		$query = $mydb->get_results("select * from $teamid", ARRAY_N);
		
		$buildnew = array();
		foreach ($query as $revisequery){
			$buildnew[$revisequery[0]] = array('id' => $revisequery[0], 'season' => $revisequery[1], 'week' => $revisequery[2], 'points' => $revisequery[5], 'versus' => $revisequery[3], 'vspts' => '', 'result' => '', 'venue' => $revisequery[4]);
		}
		
		$put = serialize($buildnew);
		file_put_contents($cache, $put);
		
	
		echo '<pre>';
			echo $teamid.'.txt From MySQL...';
// 			print_r($buildnew);
		echo '</pre>';
	}
}

// CO7 + 1
build_team('RBS');
build_team('WRZ');
build_team('PEP');
build_team('ETS');
build_team('SNR');
build_team('BUL');
build_team('TSG');
build_team('CMN');
build_team('TSG');

// Joah
build_team('BST');

// Exp 1
build_team('PHR');
build_team('SON');

// Exp 2
build_team('ATK');
build_team('HAT');

// Matt
build_team('MAX');

// Merger of ATK and PHR
build_team('DST');



function add_versus($teamvar){
		$mycache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/team/'.$teamvar.'.txt';
		$myget = file_get_contents($mycache);
		$mydata = unserialize($myget);

	foreach ($mydata as $getversus){
		$teamplayed = strtoupper($getversus['versus']);
		$weekid = $getversus['id'];
		
		$teamcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/team/'.$teamplayed.'.txt';
	
		if (file_exists($teamcache)){
			$teamget = file_get_contents($teamcache);
			$teamdata = unserialize($teamget);
		} else {
			echo $teamvar.'.txt file does not exsist';
		}
		
		$mydata[$weekid]['vspts'] =  $teamdata[$weekid]['points'];	
		
		$mypoints = $mydata[$weekid]['points'];
		$thierpoints = $teamdata[$weekid]['points'];
		
		if ($mypoints < $thierpoints){
			$mydata[$weekid]['result'] = 1;
		} else {
			$mydata[$weekid]['result'] = 0;
		}
		
	}
	
	$finalcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/team/'.$teamvar.'_f.txt';
	$finalput = serialize($mydata);
	file_put_contents($finalcache, $finalput);
	
	echo '<pre>';
		echo $teamvar.'_f.txt built from'.$teamvar.'.txt';
// 		print_r($mydata);
	echo '</pre>';
	
	
	
// var_dump($teamvspoints);

	
}

// All of the single team files 'WRZ.txt' must be built first from the 'build_teams' function.  Then thoes cached files are used to extend $mydata to include vs points and result.  Win = 1, Loss = 0

// CO7 + 1
add_versus('RBS');
add_versus('WRZ');
add_versus('PEP');
add_versus('ETS');
add_versus('SNR');
add_versus('BUL');
add_versus('TSG');
add_versus('CMN');
add_versus('TSG');

// Joah
add_versus('BST');

// Exp 1
add_versus('PHR');
add_versus('SON');

// Exp 2
add_versus('ATK');
add_versus('HAT');

// Matt
add_versus('MAX');

add_versus('DST');
	
			


get_footer(); ?>