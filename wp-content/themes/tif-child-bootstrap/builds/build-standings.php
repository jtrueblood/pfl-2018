<?php
/*
 * Template Name: Build Standings
 * Description: Master Build for generating stand0000.txt each year'  */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 

$year = 2016;
$lid = 38954;

get_cache('mfl/mflteamids', 0);	
$mflteamids = $_SESSION['mfl/mflteamids'];

get_cache('teaminfo', 0);	
$teaminfo = $_SESSION['teaminfo'];

get_cache('standings/stand2014', 0);	
$stand2014 = $_SESSION['standings/stand2014'];

$standcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/standings/stand'.$year.'.txt';

$thedivisions = array(
	'PEP' => 'EGAD', 
	'WRZ' => 'EGAD', 
	'ETS' => 'EGAD', 
	'HAT' => 'EGAD',
	'SON' => 'EGAD',
	'CMN' => 'DGAS', 
	'BUL' => 'DGAS', 
	'TSG' => 'DGAS', 
	'SNR' => 'DGAS',
	'DST' => 'DGAS'
	);
	
// playoff teams 
$playoff_teams = array (
	'HAT' => 1,
	'SNR' => 2,
	'BUL' => 3,
	'SON' => 4
	);

// added this here since the API seems to be wrong.  Manually add in the arrays below...	
/*
$correctpts = array(
	'PEP' => 571, 
	'WRZ' => 579, 
	'ETS' => 571, 
	'MAX' => 477,
	'CMN' => 525, 
	'BUL' => 457, 
	'TSG' => 486, 
	'SNR' => 471,
	'PHR' => 476, 
	'SON' => 480, 
	'HAT' => 443, 
	'ATK' => 476
	);
*/	
	
// games back array
/*
$games_back = array(
	'PEP' => 0, 
	'WRZ' => 2, 
	'ETS' => 2, 
	'MAX' => 4,
	'CMN' => 0, 
	'BUL' => 1, 
	'TSG' => 1, 
	'SNR' => 3,
	'PHR' => 3, 
	'SON' => 0, 
	'HAT' => 1, 
	'ATK' => 3
);
*/

// pass week parameter all players by ID and Score	
$jsonstandings = file_get_contents('http://football24.myfantasyleague.com/'.$year.'/export?TYPE=leagueStandings&L='.$lid.'&W=14&JSON=1');
$getstandings = json_decode($jsonstandings, true);
$standfran = $getstandings['leagueStandings']['franchise'];

// printr($correctpts, 0);

$tablename = 'stand'.$year;

$printstandings .= "INSERT INTO ".$tablename." (id,year,playoff_seed,division,teamID,team,win,loss,winper,gb,pts,ppg,pts_agst,plus_min,div_win,div_loss,home_win,home_loss)<br/>VALUES ";

$r=1;
foreach ($standfran as $buildstand){
	$num = sprintf("%02d", $r);
	$id = 'std'.$year.$num; 
	$teamID = $mflteamids[$buildstand['id']]; 
	$team = $teaminfo[$teamID][0]; 
	$playoff_seed = $playoff_teams[$teamID]; // must add manually in array above or in table itself
	$division = $thedivisions[$teamID];
	$win = $buildstand['h2hw'];
	$loss = $buildstand['h2hl']; 
	$winper = number_format(($win / 14), 3); 
	$gb = $games_back[$teamID];				// must add manually in array above or in table itself
	//$pts = $buildstand['pf']; 			this seems to be wrong  -- not takking the week parameter and adding w15 + 
	$pts = $correctpts[$teamID]; 			// so this corrects the points with the manual array
	$ppg = number_format($pts/14, 1); 	
	$pts_agst = $buildstand['pa'];;
	$plus_min = $pts - $pts_agst; 		
	$div_win = $buildstand['divw'];
	$div_loss = $buildstand['divl'];
	$home_win = 0; 						// must add manually in array above or in table itself
	$home_loss = 0;
	
	$printstandings .= "('".$id."',";
	$printstandings .= "'".$year."',";
	$printstandings .= "'".$playoff_seed."',";
	$printstandings .= "'".$division."',";
	$printstandings .= "'".$teamID."',";
	$printstandings .= "'".$team."',";
	$printstandings .= $win.",";
	$printstandings .= $loss.",";
	$printstandings .= $winper.",";
	$printstandings .= $gb.",";
	$printstandings .= $pts.",";
	$printstandings .= $ppg.",";
	$printstandings .= $pts_agst.",";
	$printstandings .= $plus_min.",";
	$printstandings .= $div_win.",";
	$printstandings .= $div_loss.",";
	$printstandings .= $home_win.",";
	$printstandings .= $home_loss;
	$printstandings .= "),<br/>";
	
	$r++;

}

$printstandings  .= ";";
?>

<div class="add-to-top">
<?php
	
	
printr($printstandings, 0);


?>
</div>





<?php


/*
$deletecache = 0;
if ($deletecache == 1){
	fopen($standcache,"wa+");
}
*/

if (file_exists($standcache)){
	
	$standget = file_get_contents($standcache);
	$standdata = unserialize($standget);	
				
}  else {
	$mydb = new wpdb('root','root','pflmicro','localhost');
	$standquery = $mydb->get_results("select * from stand$year", ARRAY_N);
	
	foreach ($standquery as $revisequery){
		$buildnew[] = array(
			'id' => $revisequery[0], 
			'year' => $revisequery[1], 
			'playoff_seed' => $revisequery[2], 
			'division' => $revisequery[3],
			'teamID' => $revisequery[4], 
			'team' => $revisequery[5], 
			'win' => $revisequery[6],
			'loss' => $revisequery[7], 
			'winper' => $revisequery[8], 
			'gb' => $revisequery[9],
			'pts' => $revisequery[10], 
			'ppg' => $revisequery[11], 
			'pts_agst' => $revisequery[12],
			'plus_min' => $revisequery[13],
			'div_win' => $revisequery[14], 
			'div_loss' => $revisequery[15], 
			'home_win' => $revisequery[16],
			'home_loss' => $revisequery[17]);
	}
	
	$putstand = serialize($buildnew);
	file_put_contents($standcache, $putstand);
	
}




	
	
?>
<!--CONTENT CONTAINER-->
<div class="boxed">

	<div id="content-container">
	
		<div id="page-content">
		
			<div class="row">
		
<?php


echo '<div class="col-xs-8"><pre>';
	echo '<h5>File Exists -- stand'.$year.'.txt From Cache...</h5>';
	print_r($standdata);

echo '</pre></div>';


echo '<div class="col-xs-8"><pre>';
	echo '<h5>stand'.$year.'.txt Written From MySQL...</h5>';
	print_r($buildnew);
echo '</pre></div>';

?>

			</div>
		</div>
	</div>
</div>


<?php get_footer(); ?>