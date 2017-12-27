<?php
/*
 * Template Name: Build Leaders
 * Description: Master Build for generating total leader array and individual season leaders
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 

?>
<?php while (have_posts()) : the_post(); ?>
<div class="boxed">
	<div id="content-container">
		<div class="col-xs-24 eq-box-sm">
			<div class="panel panel-bordered panel-dark">
				<div id="page-title">
					<h2><?php the_title();?></h2>
				</div>
				<div id="page-content">
					<h5><?php the_content();?></h5>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endwhile; wp_reset_query(); ?>	


<!-- Make the required arrays and cached files availible on the page -->
<?php 
$season = date("Y");
$buildyear = 2015;

	
get_cache('teaminfo', 0);
$teaminfo = $_SESSION['teaminfo'];

get_cache('playersassoc', 0);	
$players = $_SESSION['playersassoc'];

get_cache('players', 0);	
$playerind = $_SESSION['players'];

get_cache('playersid', 0);	
$playersid = $_SESSION['playersid'];

get_cache('allplayerdata', 0);	
$allplayerdata = $_SESSION['allplayerdata'];


get_cache('mfl/linkidcache', 0);	
$linkidcache = $_SESSION['mfl/linkidcache'];

$compressedplayer = array();


// total number of points scored by all teams
// $mergeforpoints = array_sum($drilleddown);
	
	
//After all data is added to database and the case files required (called above) are built, detete the 'alltimeleaders_*.txt' files from ../rankyears and then reload /build-leaders-data/ page four times with $thepos var changed to QB,RB,WR & PK.

$thepos = 'PK';	

$leaderscache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/rankyears/alltimeleaders_'.$thepos.'.txt';


if (file_exists($leaderscache)){
	
	$leadersget = file_get_contents($leaderscache);
	$leadersdata = unserialize($leadersget);
	$leaderscount = count(array_keys($leadersdata));	

	//printr($leadersdata, 0);
				
	}  else {
	
	foreach ($allplayerdata as $justplayer){
		foreach ($justplayer as $drilleddown){
			$compressedplayer[] = array($drilleddown[6] => $drilleddown[3]);
		}
	}

	
	
	// build array of lifetime points [1991AikmQB] => 495 
	foreach ($compressedplayer as $k=>$subArray) {
	  foreach ($subArray as $id=>$value) {
	    $total_lifetime_points[$id]+=$value;
	  }
	}
	
	
	//count all games -- requires updated individual player cache files
	
	
	foreach ($compressedplayer as $k=>$subArray) {
	  foreach ($subArray as $id=>$value) {
		$position = substr($id, -2);
		if ($position == $thepos){
	    	$points[$id]+=$value;
	    }
	  }
	}
	arsort($points);
	$values = array_values($points);
	$keys = array_keys($points);	
	
	$playercount = array();
	foreach ($keys as $countplayers){
		$playercache = get_stylesheet_directory_uri() . '/cache/playerdata/'.$countplayers.'.txt';
		$playerget = file_get_contents($playercache);
		$playerdata = unserialize($playerget);
		$thecount = count($playerdata);
		$playercount[] = $thecount;
	}
	
	foreach ($linkidcache as $gettheactive){
		$activia[$gettheactive[2]] = 1; 
	}
	
	$i = 0; 

	foreach ($points as $leaders){
		$thekey = $keys[$i];
		$leadersbuild[] = array($thekey, $values[$i], $players[$thekey][0], $players[$thekey][1], $players[$thekey][2], $playercount[$i], $activia[$thekey]);
		$i++;			
	}
	
	$put = serialize($leadersbuild);
	file_put_contents($leaderscache, $put);
	
	printr($leadersbuild, 0);
	
		
	}

// just by year and position




$w = 1;
while ($w <= 14){
	foreach ($allplayerdata as $player){
		foreach ($player as $game){
			$playerid = $game[6];
			$weekyear = $game[2];
			$pointsyear = $game[3];
			$seasonyear = $game[1];
			if ($seasonyear == $buildyear){
				if ($w == $weekyear){
					$allseasonal[$playerid] = $pointsyear;
				} 	
			}
		}	
	}
	$storeweeks[$w] = $allseasonal;
    $w++;
    $allseasonal = array();
}

$sumArray = array();

foreach ($storeweeks as $k=>$subArray) {
  foreach ($subArray as $id=>$value) {
    $sumArray[$id]+=$value;
  }
}

arsort($sumArray);


foreach ($sumArray as $key => $value){
	$posich = substr($key, -2);
	$byposich[$key] = array($value, $posich);
}

$positions = array('QB','RB','WR','PK');

foreach ($byposich as $key => $values){
	foreach ($positions as $check){
		if ($values[1] == $check){
			${$check.$buildyear}[$key] = $values[0];
		}	
	}
}
$qbcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/rankyears/yearleaders/QB'.$buildyear.'.txt';
if (!file_exists($qbcache)){
	$put = serialize(${'QB'.$buildyear});
	file_put_contents($qbcache, $put);
}
$rbcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/rankyears/yearleaders/RB'.$buildyear.'.txt';
if (!file_exists($rbcache)){
	$put = serialize(${'RB'.$buildyear});
	file_put_contents($rbcache, $put);
}
$wrcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/rankyears/yearleaders/WR'.$buildyear.'.txt';
if (!file_exists($wrcache)){
	$put = serialize(${'WR'.$buildyear});
	file_put_contents($wrcache, $put);
}
$pkcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/rankyears/yearleaders/PK'.$buildyear.'.txt';
if (!file_exists($pkcache)){
	$put = serialize(${'PK'.$buildyear});
	file_put_contents($pkcache, $put);
}


get_cache('theyears', 0);	
$theyears = $_SESSION['theyears'];

	
foreach ($theyears as $getyear){	
	get_cache('rankyears/yearleaders/QB'.$getyear, 0);	
	${'QB'.$getyear} = $_SESSION['rankyears/yearleaders/QB'.$getyear];
	get_cache('rankyears/yearleaders/RB'.$getyear, 0);	
	${'RB'.$getyear} = $_SESSION['rankyears/yearleaders/RB'.$getyear];
	get_cache('rankyears/yearleaders/WR'.$getyear, 0);	
	${'WR'.$getyear} = $_SESSION['rankyears/yearleaders/WR'.$getyear];
	get_cache('rankyears/yearleaders/PK'.$getyear, 0);	
	${'PK'.$getyear} = $_SESSION['rankyears/yearleaders/PK'.$getyear];
}	


foreach ($theyears as $gettheyear){
	reset(${'QB'.$gettheyear});
	$first_key = key(${'QB'.$gettheyear});
	$topqb[$gettheyear] = $first_key; 
	
	reset(${'RB'.$gettheyear});
	$first_key = key(${'RB'.$gettheyear});
	$toprb[$gettheyear] = $first_key; 
	
	reset(${'WR'.$gettheyear});
	$first_key = key(${'WR'.$gettheyear});
	$topwr[$gettheyear] = $first_key; 
	
	reset(${'PK'.$gettheyear});
	$first_key = key(${'PK'.$gettheyear});
	$toppk[$gettheyear] = $first_key; 
}

/*
printr($topqb,0);
printr($toprb,0);
printr($topwr,0);
printr($toppk,0);
*/

$topqbcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/rankyears/QBtop.txt';
if (!file_exists($topqbcache)){
	$put = serialize($topqb);
	file_put_contents($topqbcache, $put);
}
$toprbcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/rankyears/RBtop.txt';
if (!file_exists($toprbcache)){
	$put = serialize($toprb);
	file_put_contents($toprbcache, $put);
}
$topwrcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/rankyears/WRtop.txt';
if (!file_exists($topwrcache)){
	$put = serialize($topwr);
	file_put_contents($topwrcache, $put);
}
$toppkcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/rankyears/PKtop.txt';
if (!file_exists($toppkcache)){
	$put = serialize($toppk);
	file_put_contents($toppkcache, $put);
}
	
// calculate and build Player Qalue Quotient (PVQ)!!
	
$q = 0;
$r = 1991;
while ($r < $season){
	foreach ($positions as $getpos){
		$value = array_sum(${$getpos.$r});
		${'sum'.$r}[$getpos] = $value;
	}	
	$sumAllYears[$r] = ${'sum'.$r};
	$r++;
}	

// the array below is all seasons where the total points scored by each position are added together
//printr($sumAllYears, 0);

// then we figure out the high values for each season and build an array of that

$s = 1991;
foreach ($sumAllYears as $setquot){
	$yearhigh = max($setquot);
	$highs[$s] = $yearhigh;
	$s++;
}

// then set he highs as 1 and everyother one a ratio of that

$t = 1991;
foreach ($sumAllYears as $setval){
	$u = 0;
	foreach ($setval as $multiplyval){
		$multiplier[$u] =  $highs[$t] / $multiplyval;
		$thepvqs[$t] = $multiplier;
		$u++;
	}
	$t++;
}

$buildyear = 2015;
$qb = $QB2015;
$rb = $RB2015;
$wr = $WR2015;
$pk = $PK2015;

// multiply each players score by the value
//  use $buildyear and $qb, $rb, $wr, $pk at the top for year var

foreach ($qb as $key => $each){
	$val = $thepvqs[$buildyear][0];
	$pvq_qb[$key] = round(($each * $val),1);
}
foreach ($rb as $key => $each){
	$val = $thepvqs[$buildyear][1];
	$pvq_rb[$key] = round(($each * $val),1);
}
foreach ($wr as $key => $each){
	$val = $thepvqs[$buildyear][2];
	$pvq_wr[$key] = round(($each * $val),1);
}
foreach ($pk as $key => $each){
	$val = $thepvqs[$buildyear][3];
	$pvq_pk[$key] = round(($each * $val),1);
}

$combined = array_merge($pvq_qb, $pvq_rb, $pvq_wr, $pvq_pk);
arsort($combined);

$pvqcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/rankyears/pvq/pvq'.$buildyear.'.txt';
if (!file_exists($pvqcache)){
	$put = serialize($combined);
	file_put_contents($pvqcache, $put);
}




get_footer(); ?>