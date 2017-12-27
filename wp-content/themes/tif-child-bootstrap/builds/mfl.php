<?php
/*
 * Template Name: My Fantasy League
 * Description: Arrays from the MFL API boxcores by week
 * Need all player IDs in PFL database.  So rookies must be added before this works.
 */
 ?>


<?php get_header(); ?>


<?php 

$week = 1;
$year = 2015;
$lid = 47099;

get_cache('players', 0);	
$players = $_SESSION['players'];


	
// pass week parameter all players by ID and Score	
$jsonallplayer = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=players&L='.$mflleagueid.'&W='.$week.'&JSON=1');
$mflallplayers = json_decode($jsonallplayer, true);

// with 'Players' attribute pass single or multiple IDs	
$jsonplayer = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=players&L='.$lid.'&W='.$week.'&JSON=1&PLAYERS=8658,3291');
$mflplayers = json_decode($jsonplayer, true);
 
// information and news about player 
$jsonplayerprofile = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=playerProfile&L='.$lid.'&W=8&JSON=1&PLAYERS=8658');
$mflplayerprofile = json_decode($jsonplayerprofile, true); 
 
// players scores by week 
$jsonplayerscore = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=playerScores&L='.$lid.'&W='.$week.'&JSON=1');
$mflplayerscores = json_decode($jsonplayerscore, true);

// team results by week
$jsonweeklyresult = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=weeklyResults&L='.$lid.'&W='.$week.'&JSON=1');
$mflweeklyresult = json_decode($jsonweeklyresult, true);


// array for team IDs
$teamconvert = array('0001' => 'TSG', '0002' => 'ETS', '0003' => 'PEP', '0004' => 'WRZ', '0005' => 'DST', '0006' => 'SON', '0007' => 'SNR', '0008' => 'HAT', '0009' => 'CMN', '0010' => 'BUL');

?>
		
			
<?php
//	get all MFL player IDs

$newplayers = $mflallplayers['players']['player'];

foreach ($newplayers as $getplayers){
	$position = $getplayers['position'];
	if ($position == 'QB' OR $position == 'RB' OR $position == 'WR' OR $position == 'TE' OR $position == 'PK'){
		$themflplayers[] = array($getplayers['name'], $getplayers['id']);
	}
}


// reformat names from pfl players array
foreach ($players as $newname){
	$joinname = $newname[2].', '.$newname[1];
	$fixnames[] = array($joinname, $newname[0]);
}

// match ids from PFL and MFL and cache the file 
$linkidcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/mfl/linkidcache.txt';
if (file_exists($linkidcache)){
	
	$linkget = file_get_contents($linkidcache);
	$linkdata = unserialize($linkget);	
				
}  else {
	$u = 0;
foreach ($fixnames as $thefixed) {
	foreach ($themflplayers as $compress){
			$check0 = $compress[0];
			$check1 = strtr($check0, array('.' => ''));
			$check2 = $thefixed[0];
			if ($check1 == $check2){
				$linkids[$compress[1]] = array($check1, $compress[1], $thefixed[1]);
		}
	}
}

// manually add the few players that dont seem to work
	$manualplayers = array(11679 => array('Beckham Jr, Odell', 11679, '2015BeckWR' ));
	$alllinkids = $linkids + $manualplayers;
	
	
	$putlink = serialize($alllinkids);
	file_put_contents($linkidcache, $putlink);
	
}

// get schedule for the week 

$g = 0;
while ($g < 6){
	$teamget0 = $mflweeklyresult['weeklyResults']['matchup'][$g]['franchise'][0]['id'];
	$teamget1 = $mflweeklyresult['weeklyResults']['matchup'][$g]['franchise'][1]['id'];
	$scheduleget0[$teamget0] = $teamget1;
	$scheduleget1[$teamget1] = $teamget0;
	$g++;
}

$sched = array_merge($scheduleget0, $scheduleget1);

// identifies all players that played in the PFL in a given year and marks rookies if they do not currently exsist in the PFL database.

$s = 0;
while ($s < 6){
	$r = 0;
	while ($r < 2){
		$matchupfind = $mflweeklyresult['weeklyResults']['matchup'][$s]['franchise'][$r]['player'];
		$teamid = $mflweeklyresult['weeklyResults']['matchup'][$s]['franchise'][$r]['id'];
		foreach ($matchupfind as $matchupget){
			if ($matchupget['status'] == 'starter'){
				$mfild = $matchupget['id'];
				$versus = $sched[$teamid];
				$gameget[] = array($mfild, $matchupget['score'], $teamconvert[$teamid], $linkdata[$mfild][2], $teamconvert[$versus]);
			}
		}
		$r++;
	}
	$s++;
}




$boxcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/mfl/box'.$year.''.$week.'.txt';
if (file_exists($boxcache)){
	
	$boxget = file_get_contents($boxcache);
	$boxdata = unserialize($boxget);	
				
}  else {
	
	$l = 0;
foreach ($gameget as $box){
	if ($week >= 10){
		$weekid = $year.''.$week;
	} else {
		$weekid = $year.'0'.$week;
	}
	$points = $box[1];
	$team = $box[2];
	$playerid = $box[3];
	$vs = $box[4];
	$season = 0;
	$insertbox[$playerid] = array($weekid, $year, $week, $points, $team, $vs, $season);
	$l++;
}

	$putbox = serialize($insertbox);
	file_put_contents($boxcache, $putbox);
	
}


printr($linkdata, 0);


?>

<?php get_footer(); ?>