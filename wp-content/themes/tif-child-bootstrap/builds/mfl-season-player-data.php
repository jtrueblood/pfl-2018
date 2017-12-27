<?php
/*
 * Template Name: MFL Season Player
 * Description: Data to export MFL Season data to import into main history database by playerID
 */
 ?>


<?php get_header(); ?>


<?php 


$year = 2016;
$lid = 38954;


get_cache('mfl/linkidcache', 0);	
$linkidcache = $_SESSION['mfl/linkidcache'];

get_cache('players', 0);	
$players = $_SESSION['players'];


// pass week parameter all players by ID and Score	
$jsonallplayer = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=players&L='.$mflleagueid.'&W='.$week.'&JSON=1');
$mflallplayers = json_decode($jsonallplayer, true);
 
// players scores by week 
$jsonplayerscore = file_get_contents('http://football25.myfantasyleague.com/'.$year.'/export?TYPE=playerScores&L='.$lid.'&W='.$week.'&JSON=1');
$mflplayerscores = json_decode($jsonplayerscore, true);


// array for team IDs
$teamconvert = array('0001' => 'TSG', '0002' => 'ETS', '0003' => 'PEP', '0004' => 'WRZ', '0005' => 'DST', '0006' => 'SON', '0007' => 'SNR', '0008' => 'HAT', '0009' => 'CMN', '0010' => 'BUL');

$mflteamids = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/mfl/mflteamids.txt';
if (file_exists($mflteamids)){	
	$teamidsget = file_get_contents($mflteamids);
} else {
	$putteamids = serialize($teamconvert);
	file_put_contents($mflteamids, $teamconvert);
}

echo '<h1>here</h1>';
print_r($teamidsget);

get_cache('mfl/mflteamids', 0);	
$mflteamids = $_SESSION['mfl/mflteamids'];
print_r($mflteamids);

// Get the players that played each week 
function getallstarters($theweek, $matchup, $franchise){
	$jsonweek = file_get_contents('http://football24.myfantasyleague.com/2016/export?TYPE=weeklyResults&L=38954&W='.$theweek.'&JSON=1');
	$theweek = json_decode($jsonweek, true);
	$starters = $theweek['weeklyResults']['matchup'][$matchup]['franchise'][$franchise]['starters'];
	return $starters;
}


$allstarterscache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/mfl/allstarters.txt';

if (file_exists($allstarterscache)){
	
	$starterget = file_get_contents($allstarterscache);
	

} else {

	$j = 1;
	while ($j < 15){
		$i = 0; 
		while ($i < 6){
			$printstarters .= getallstarters($j, $i, 0);
			$printstarters .= getallstarters($j, $i, 1);
			$i++; 
		}
		$j++;
	}
	
	file_put_contents($allstarterscache, $printstarters);
	
}

$csvstarters = array_map('str_getcsv', file($allstarterscache));

$thestarters = array_values(array_unique($csvstarters[0]));
$theveterans = array_keys($linkidcache);
 

// Check it against players to see if they are rookies in the PFL


foreach ($thestarters as $checkstarter){
	if (!in_array($checkstarter, $theveterans)){
		$therookies[] = $checkstarter;
	}
}


foreach ($therookies as $rookinfo){
	$getplayerinfo = get_player_name($year, $lid, $rookinfo);
	//printr($getplayerinfo, 0);

	$printrook = '';
	
	$name = $getplayerinfo['name'];
	$getname = explode(',', $name);
	$firstname = $getname[1];
	$lastname = $getname[0];
	$rookie = $year;
	$position = $getplayerinfo['position'];
	if ($position == 'TE'){
		$position = 'WR';
	}
	$result = substr($lastname, 0, 4);
	$pid = $year.''.$result.''.$position;

		
	$printrook .= "'".$pid."',";
	$printrook .= "'".ltrim($firstname)."',";
	$printrook .= "'".$lastname."',";
	$printrook .= "'".$position."',";
	$printrook .= $rookie;
	echo $printrook.'<br/>';
	

}

$themflplcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/mfl/mfl-every-player.txt';

if (file_exists($themflplcache)){

	echo 'mfl-every-player.txt exists<br/>';

} else {

	$themflplayers = $mflallplayers['players']['player'];
	
	foreach ($themflplayers as $getmflplayer){
		$mid = $getmflplayer['id'];
		$mname = $getmflplayer['name'];
		$nposition = $getmflplayer['position'];
		if ($position == 'TE'){
			$position = 'WR';
		}
		$nname = explode(',', $mname); 
		$nfirstname = ltrim($nname[1]);
		$nlastname = $nname[0];
		
		$buildmflplayer[$mid] = array($nfirstname,$nlastname,$nposition); 
		
	}
	
	$puteveryplayer = serialize($buildmflplayer);
	file_put_contents($themflplcache, $puteveryplayer);
	

}

$starterids = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/mfl/thestarters.txt';

if (file_exists($starterids)){

	echo 'thestarters.txt exists';

} else {
	
	$putthestarters = serialize($thestarters);
	file_put_contents($starterids, $putthestarters);
	

}


// printr($thestarters, 0);


?>

<?php get_footer(); ?>