<?php
/*
 * Template Name: Get MFL Player 
 * Description: Extract season player data for individual players from MFL  */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 


get_cache('mfl/thestarters', 0);	
$thestarters = $_SESSION['mfl/thestarters'];

$year = 2016;
$week = 11; // change this to 1 to 14 and reload page to build all of the week needed starters
$lid = 38954;
// $mflid = 8657;

$id = $_GET['id'];	
$mflid = $thestarters[$id];


// imports all team cache pages via a function and make one big array
$pep = getallteamcache ('PEP');
$wrz = getallteamcache ('WRZ');
$ets = getallteamcache ('ETS');
$son = getallteamcache ('SON');
$hat = getallteamcache ('HAT');
$cmn = getallteamcache ('CMN');
$bul = getallteamcache ('BUL');
$snr = getallteamcache ('SNR');
$tsg = getallteamcache ('TSG');
$dst = getallteamcache ('DST');

$allteams = array('DST' => $dst, 'PEP' => $pep, 'WRZ' => $wrz, 'ETS' => $ets, 'SON' => $son, 'HAT' => $hat, 'CMN' => $cmn, 'BUL' => $bul, 'SNR' => $snr, 'TSG' => $tsg);

get_cache('mfl/mflteamids', 0);	
$mflteamids = $_SESSION['mfl/mflteamids'];

get_cache('teaminfo', 0);	
$teaminfo = $_SESSION['teaminfo'];


get_cache('mfl/linkidcache', 0);	
$linkidcache = $_SESSION['mfl/linkidcache'];

get_cache('players', 0);	
$players = $_SESSION['players'];

$standcache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/standings/stand'.$year.'.txt';
$formalname = $linkidcache[$mflid][0];
$pflid = $linkidcache[$mflid][2];




	
// get scores for that player, for each week (reguardless of starter or not) 	
$jsonplayerscores = file_get_contents('http://football24.myfantasyleague.com/'.$year.'/export?TYPE=playerScores&L='.$lid.'&W='.$week.'&JSON=1&PLAYERS='.$mflid.'');
$playerscores = json_decode($jsonplayerscores, true);	

$jsonweekresults = file_get_contents('http://football24.myfantasyleague.com/'.$year.'/export?TYPE=weeklyResults&L='.$lid.'&W='.$week.'&JSON=1');
$weekresults = json_decode($jsonweekresults, true);	
	
$teamstarterscache = '/Library/WebServer/possefootball-2015/wp-content/themes/tif-child-bootstrap/cache/mfl/teamstarters-'.$year.'-'.$week.'.txt';

if (!file_exists($teamstarterscache)){
	
	$j = $week; 
	$i = 0; 
	while ($i < 5){ // 5 is number of games per week
		$idtop = getallteamids($j, $i, 0);
		$idbottom = getallteamids($j, $i, 1);
		$printstarters[$idtop] .= getallstarter($j, $i, 0);
		$printstarters[$idbottom] .= getallstarter($j, $i, 1);
		$i++; 
		foreach ($printstarters as $key => $value){
			$newarray[$key] = array_filter(explode(',', $value)); 
		}
	}
	$j++;


	$putteamstarters = serialize($newarray);
	file_put_contents($teamstarterscache, $putteamstarters);	
}	


/*  comment this out when building the txt files for each week.  then uncomment it to bring in the cached files and merge them into one array. */
$f = 1;
while ($f <= 14){
	get_cache('mfl/teamstarters-'.$year.'-'.$f.'', 0);	
	$getcache[$f] = $_SESSION['mfl/teamstarters-'.$year.'-'.$f.''];
	$f++;
}
/* end comment out section */

foreach ($getcache as $key => $starters){
	foreach ($starters as $subkey => $subvalue){
		foreach ($subvalue as $get){
			if ($mflid == $get){
				$playerweek[$key] = $subkey;
			}
		}
	}
}

$simpleplayerscore = $playerscores['playerScores']['playerScore'];
foreach ($simpleplayerscore as $baseplayerscore){
	$plweek = $baseplayerscore['week'];
	$plscore = $baseplayerscore['score'];
	$cleanscore[$plweek] = $plscore; 
}


foreach ($playerweek as $key => $value){
	$longweek = sprintf("%02d", $key);
	$weekid = $year.$longweek;
	$teamint = $mflteamids[$value];
	$versus = $allteams[$teamint][$weekid]['versus'];
	$compile[] = array($weekid, $year, $key, $cleanscore[$key], $teamint, $versus, 0 );
}

// create new table for player if they don't exsit

$mydb = new wpdb('root','root','pflmicro','localhost');
$query = $mydb->get_results("select * from $pflid", ARRAY_N);
// var_dump($query);
if(empty($query)){
	$mydb->query($mydb->prepare ("create TABLE $pflid like 1991AikmQB", ARRAY_N));
} 
	

$printpl .= "INSERT INTO $pflid (week_id,year,week,points,team,versus,season)<br/>VALUES ";
foreach ($compile as $insert){
	$plweekid = $insert[0];
	$plyear = $insert[1];
	$plweek = $insert[2];
	$plpoints = $insert[3];
	$plteam = $insert[4];
	$plversus = $insert[5];

	$printpl .= "('".$plweekid."',"; 
	$printpl .= "'".$plyear."',";
	$printpl .= "'".$plweek."',";
	$printpl .= "'".$plpoints."',";
	$printpl .= "'".$plteam."',";
	$printpl .= "'".$plversus."',";
	$printpl .= "0 )";
	if ($insert !== end($compile)){
		$printpl .= ",";
	} else {
		$printpl .= ";";
	}
	$printpl .= "<br/>";
}

	
?>
<!--CONTENT CONTAINER-->
<div class="boxed">

	<div id="content-container">
	
		<div id="page-content">
		
			<div class="row">
				
				<div class="col-sm-6">
				
				
					<!--Profile Widget-->
					<!--===================================================-->
					<div class="panel widget">
						<div class="widget-header bg-primary"></div>
						<div class="widget-body text-center">
							<img alt="Profile Picture" class="widget-img img-circle img-border-light" src="<?php echo get_stylesheet_directory_uri();?>/img/players/<?php echo $pflid; ?>.jpg">
							<h4 class="mar-no"><?php echo $formalname;?></h4>
							<p class="text-muted mar-btm"><?php echo $pflid;?> / <?php echo $mflid;?></p>
					
						    <button id="nextplayerbtn" class="btn btn-default btn-hover-warning">Next Player</button><br/>
							<p><?php echo $printpl; ?></p>
							
						<h4>Players who scored in <?php echo $year;?></h4>
						<p> Does not include OT</p>
						<?php		
						printr($thestarters, 0);	
						?>	

						</div>
						
					</div>
					<!--===================================================-->
				
				</div>
				
				<div class="col-sm-8">						
					<h4 class="mar-no"><?php echo $year;?> Data from MFL API</h4>
					<?php printr($compile, 0); ?>
					
				</div>
				
				<div class="col-sm-8">	
					<h4 class="mar-no">Career Data from Database</h4>
					<?php printr($query, 0); ?>
					
				</div>
				

			</div>
			
			
		
		</div>
	</div>
</div>


<?php get_footer(); ?>