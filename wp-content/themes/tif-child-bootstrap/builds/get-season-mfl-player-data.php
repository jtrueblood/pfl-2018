<?php
/*
 * Template Name: Get Season MFL Player Data 
 * Description: Extract season player data for individual players from MFL  */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header(); 


/*
get_cache('mfl/thestarters', 0);	
$thestarters = $_SESSION['mfl/thestarters'];
*/

$year = 2017;
$week = 1; // change this to 1 to 14 and reload page to build all of the week needed starters
$lid = 38954;
$mflid = $_GET['id'];
$mflid_str = strval($mflid);

$id = $_GET['id'];	
//$mflid = $thestarters[$id];

$allteams = array('DST' => $dst, 'PEP' => $pep, 'WRZ' => $wrz, 'ETS' => $ets, 'SON' => $son, 'HAT' => $hat, 'CMN' => $cmn, 'BUL' => $bul, 'SNR' => $snr, 'TSG' => $tsg);
$mflteamids = array('0005' => 'DST', '0003' => 'PEP', '0004' => 'WRZ', '0002' => 'ETS', '0006' => 'SON', '0008' => 'HAT', '0009' => 'CMN', '0010' => 'BUL', '0007' => 'SNR', '0001' => 'TSG');

$weeks = array('1','2','3','4','5','6','7','8','9','10','11','12','13','14');
$weeks_2dig = array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14');

//printr($weeks, 0);


//. NEED TO REPLACE THESE TRANSIENTS WITH WPDB OPTION DATA ...............................................
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


// get json about player from MFL scores for that player, for each week (reguardless of starter or not) 	


$jsonplayerprofile = file_get_contents('http://www58.myfantasyleague.com/'.$year.'/export?TYPE=playerProfile&P='.$mflid.'&JSON=1');
$playerprofile = json_decode($jsonplayerprofile , true);


echo '<hr>';


//echo '<h2>Player Season Data</h2>';
//printr($playerscores, 0);


// this pulls all of the 'starters' out of the matchups so we can identify team and if player was a starter that week	

// THIS IS NOW COMMENTED OUT BECAUSE THE TRANSIENTS HAVE BEEN BUILT.  NEXT SEASON UNCOMMENT, CHANGE THE $YEAR and BUILD THE ARRAY EACH WEEK

/*
$jsonweekresults = file_get_contents('http://www58.myfantasyleague.com/'.$year.'/export?TYPE=weeklyResults&L=38954&APIKEY=aRNp1sySvuKox1emO1HIZDYeFbox&W='.$week.'&JSON=1');
$weekresults = json_decode($jsonweekresults, true);	
$matchups = $weekresults['weeklyResults'];

echo 'starters';

$get1 = rtrim($matchups['matchup'][0]['franchise'][0]['starters'],",");
$get2 = rtrim($matchups['matchup'][0]['franchise'][1]['starters'],",");
$get3 = rtrim($matchups['matchup'][1]['franchise'][0]['starters'],",");
$get4 = rtrim($matchups['matchup'][1]['franchise'][1]['starters'],",");
$get5 = rtrim($matchups['matchup'][2]['franchise'][0]['starters'],",");
$get6 = rtrim($matchups['matchup'][2]['franchise'][1]['starters'],",");
$get7 = rtrim($matchups['matchup'][3]['franchise'][0]['starters'],",");
$get8 = rtrim($matchups['matchup'][3]['franchise'][1]['starters'],",");
$get9 = rtrim($matchups['matchup'][4]['franchise'][0]['starters'],",");
$get10 = rtrim($matchups['matchup'][4]['franchise'][1]['starters'],",");

$starter1 = str_getcsv($get1);
$starter2 = str_getcsv($get2);
$starter3 = str_getcsv($get3);
$starter4 = str_getcsv($get4);
$starter5 = str_getcsv($get5);
$starter6 = str_getcsv($get6);
$starter7 = str_getcsv($get7);
$starter8 = str_getcsv($get8);
$starter9 = str_getcsv($get9);
$starter10 = str_getcsv($get10);

$t1 = $matchups['matchup'][0]['franchise'][0]['id'];
$t2 = $matchups['matchup'][0]['franchise'][1]['id'];
$t3 = $matchups['matchup'][1]['franchise'][0]['id'];
$t4 = $matchups['matchup'][1]['franchise'][1]['id'];
$t5 = $matchups['matchup'][2]['franchise'][0]['id'];
$t6 = $matchups['matchup'][2]['franchise'][1]['id'];
$t7 = $matchups['matchup'][3]['franchise'][0]['id'];
$t8 = $matchups['matchup'][3]['franchise'][1]['id'];
$t9 = $matchups['matchup'][4]['franchise'][0]['id'];
$t10 = $matchups['matchup'][4]['franchise'][1]['id'];

$s1 = $matchups['matchup'][0]['franchise'][0]['score'];
$s2 = $matchups['matchup'][0]['franchise'][1]['score'];
$s3 = $matchups['matchup'][1]['franchise'][0]['score'];
$s4 = $matchups['matchup'][1]['franchise'][1]['score'];
$s5 = $matchups['matchup'][2]['franchise'][0]['score'];
$s6 = $matchups['matchup'][2]['franchise'][1]['score'];
$s7 = $matchups['matchup'][3]['franchise'][0]['score'];
$s8 = $matchups['matchup'][3]['franchise'][1]['score'];
$s9 = $matchups['matchup'][4]['franchise'][0]['score'];
$s10 = $matchups['matchup'][4]['franchise'][1]['score'];

$home1 = $matchups['matchup'][0]['franchise'][0]['isHome'];
$home2 = $matchups['matchup'][0]['franchise'][1]['isHome'];
$home3 = $matchups['matchup'][1]['franchise'][0]['isHome'];
$home4 = $matchups['matchup'][1]['franchise'][1]['isHome'];
$home5 = $matchups['matchup'][2]['franchise'][0]['isHome'];
$home6 = $matchups['matchup'][2]['franchise'][1]['isHome'];
$home7 = $matchups['matchup'][3]['franchise'][0]['isHome'];
$home8 = $matchups['matchup'][3]['franchise'][1]['isHome'];
$home9 = $matchups['matchup'][4]['franchise'][0]['isHome'];
$home10 = $matchups['matchup'][4]['franchise'][1]['isHome'];

$op1 = $matchups['matchup'][0]['franchise'][0]['opt_pts'];
$op2 = $matchups['matchup'][0]['franchise'][1]['opt_pts'];
$op3 = $matchups['matchup'][1]['franchise'][0]['opt_pts'];
$op4 = $matchups['matchup'][1]['franchise'][1]['opt_pts'];
$op5 = $matchups['matchup'][2]['franchise'][0]['opt_pts'];
$op6 = $matchups['matchup'][2]['franchise'][1]['opt_pts'];
$op7 = $matchups['matchup'][3]['franchise'][0]['opt_pts'];
$op8 = $matchups['matchup'][3]['franchise'][1]['opt_pts'];
$op9 = $matchups['matchup'][4]['franchise'][0]['opt_pts'];
$op10 = $matchups['matchup'][4]['franchise'][1]['opt_pts'];

$r1 = $matchups['matchup'][0]['franchise'][0]['result'];
$r2 = $matchups['matchup'][0]['franchise'][1]['result'];
$r3 = $matchups['matchup'][1]['franchise'][0]['result'];
$r4 = $matchups['matchup'][1]['franchise'][1]['result'];
$r5 = $matchups['matchup'][2]['franchise'][0]['result'];
$r6 = $matchups['matchup'][2]['franchise'][1]['result'];
$r7 = $matchups['matchup'][3]['franchise'][0]['result'];
$r8 = $matchups['matchup'][3]['franchise'][1]['result'];
$r9 = $matchups['matchup'][4]['franchise'][0]['result'];
$r10 = $matchups['matchup'][4]['franchise'][1]['result'];

$weekstarters = array(
	$t1 => array( 
		'starters' => array (
			'QB' => $starter1[0],
			'RB' => $starter1[1],
			'WR' => $starter1[2],
			'PK' => $starter1[3],
		), 
		'team' => $mflteamids[$t1], 
		'team_score' => $s1, 
		'versus' => $mflteamids[$t2], 
		'vs_score' => $s2, 
		'result' => $r1, 
		'isHome' => $home1
	),
	$t2 => array( 
		'starters' => array (
			'QB' => $starter2[0],
			'RB' => $starter2[1],
			'WR' => $starter2[2],
			'PK' => $starter2[3],
		), 
		'team' => $mflteamids[$t2], 
		'team_score' => $s2, 
		'versus' => $mflteamids[$t1], 
		'vs_score' => $s1, 
		'result' => $r2, 
		'isHome' => $home2
	),
	$t3 => array( 
		'starters' => array (
			'QB' => $starter3[0],
			'RB' => $starter3[1],
			'WR' => $starter3[2],
			'PK' => $starter3[3],
		), 
		'team' => $mflteamids[$t3], 
		'team_score' => $s3, 
		'versus' => $mflteamids[$t4], 
		'vs_score' => $s4, 
		'result' => $r3, 
		'isHome' => $home3
	),
	$t4 => array( 
		'starters' => array (
			'QB' => $starter4[0],
			'RB' => $starter4[1],
			'WR' => $starter4[2],
			'PK' => $starter4[3],
		), 
		'team' => $mflteamids[$t4], 
		'team_score' => $s4, 
		'versus' => $mflteamids[$t3], 
		'vs_score' => $s3, 
		'result' => $r4, 
		'isHome' => $home4
	),
	$t5 => array( 
		'starters' => array (
			'QB' => $starter5[0],
			'RB' => $starter5[1],
			'WR' => $starter5[2],
			'PK' => $starter5[3],
		), 
		'team' => $mflteamids[$t5], 
		'team_score' => $s5, 
		'versus' => $mflteamids[$t6], 
		'vs_score' => $s6, 
		'result' => $r5, 
		'isHome' => $home5
	),
	$t6 => array( 
		'starters' => array (
			'QB' => $starter6[0],
			'RB' => $starter6[1],
			'WR' => $starter6[2],
			'PK' => $starter6[3],
		), 
		'team' => $mflteamids[$t6], 
		'team_score' => $s6, 
		'versus' => $mflteamids[$t5], 
		'vs_score' => $s5, 
		'result' => $r6, 
		'isHome' => $home6
	),
	$t7 => array( 
		'starters' => array (
			'QB' => $starter7[0],
			'RB' => $starter7[1],
			'WR' => $starter7[2],
			'PK' => $starter7[3],
		), 
		'team' => $mflteamids[$t7], 
		'team_score' => $s7, 
		'versus' => $mflteamids[$t8], 
		'vs_score' => $s8, 
		'result' => $r7, 
		'isHome' => $home7
	),
	$t8 => array( 
		'starters' => array (
			'QB' => $starter8[0],
			'RB' => $starter8[1],
			'WR' => $starter8[2],
			'PK' => $starter8[3],
		), 
		'team' => $mflteamids[$t8], 
		'team_score' => $s8, 
		'versus' => $mflteamids[$t7], 
		'vs_score' => $s7, 
		'result' => $r8, 
		'isHome' => $home8
	),
	$t9 => array( 
		'starters' => array (
			'QB' => $starter9[0],
			'RB' => $starter9[1],
			'WR' => $starter9[2],
			'PK' => $starter9[3],
		), 
		'team' => $mflteamids[$t9], 
		'team_score' => $s9, 
		'versus' => $mflteamids[$t10], 
		'vs_score' => $s10, 
		'result' => $r9, 
		'isHome' => $home9
	),
	$t10 => array( 
		'starters' => array (
			'QB' => $starter10[0],
			'RB' => $starter10[1],
			'WR' => $starter10[2],
			'PK' => $starter10[3],
		), 
		'team' => $mflteamids[$t10], 
		'team_score' => $s10, 
		'versus' => $mflteamids[$t9], 
		'vs_score' => $s9, 
		'result' => $r10, 
		'isHome' => $home10
	)
);



// set weekstarters array as a transient
function weekstarters_trans($aweek, $ayear) {
  global $weekstarters;
  global $week;
  global $year;
  $transient = get_transient( 'weekstarters_trans'.$aweek.'_'.$ayear );
  if( ! empty( $transient ) ) {
    return $transient;
  } else {
    set_transient( 'weekstarters_trans_'.$aweek.'_'.$ayear, $weekstarters, YEAR_IN_SECONDS );
    return $weekstarters;
  }
}

/*
$gettransient = weekstarters_trans($week, $year);

echo 'starters';
printr($gettransient, 0);
*/

// COMMENTED OUT SECTION THAT BUILDS THE TRANSIENTS NEEDED FOR INSERT ENDS HERE


$week_data_1 = get_transient( 'weekstarters_trans_1_2017' );
$week_data_2 = get_transient( 'weekstarters_trans_2_2017' );
$week_data_3 = get_transient( 'weekstarters_trans_3_2017' );
$week_data_4 = get_transient( 'weekstarters_trans_4_2017' );
$week_data_5 = get_transient( 'weekstarters_trans_5_2017' );
$week_data_6 = get_transient( 'weekstarters_trans_6_2017' );
$week_data_7 = get_transient( 'weekstarters_trans_7_2017' );
$week_data_8 = get_transient( 'weekstarters_trans_8_2017' );
$week_data_9 = get_transient( 'weekstarters_trans_9_2017' );
$week_data_10 = get_transient( 'weekstarters_trans_10_2017' );
$week_data_11 = get_transient( 'weekstarters_trans_11_2017' );
$week_data_12 = get_transient( 'weekstarters_trans_12_2017' );
$week_data_13 = get_transient( 'weekstarters_trans_13_2017' );
$week_data_14 = get_transient( 'weekstarters_trans_14_2017' );


$allstarters = array(
	'week1' => array_column($week_data_1, 'starters'),
	'week2' => array_column($week_data_2, 'starters'),
	'week3' => array_column($week_data_3, 'starters'),
	'week4' => array_column($week_data_4, 'starters'),
	'week5' => array_column($week_data_5, 'starters'),
	'week6' => array_column($week_data_6, 'starters'),
	'week7' => array_column($week_data_7, 'starters'),
	'week8' => array_column($week_data_8, 'starters'),
	'week9' => array_column($week_data_9, 'starters'),
	'week10' => array_column($week_data_10, 'starters'),
	'week11' => array_column($week_data_11, 'starters'),
	'week12' => array_column($week_data_12, 'starters'),
	'week13' => array_column($week_data_13, 'starters'),
	'week14' => array_column($week_data_14, 'starters')
);

// resort week data indexed by player id

$e = 1;
while($e < 15){
	foreach (${'week_data_'.$e} as $key => $value){
			${'week_data_by_player_id_'.$e}[$value['starters']['QB']] = array(
				'week' => $e,
				'position' => 'QB',
				'team' => $value['team'],
				'team_score' => $value['team_score'],	
				'versus' => $value['versus'],
				'vs_score' => $value['vs_score'],
				'result' => $value['result'],
				'isHome' => $value['isHome']
			);
			${'week_data_by_player_id_'.$e}[$value['starters']['RB']] = array(
				'week' => $e,
				'position' => 'RB',
				'team' => $value['team'],
				'team_score' => $value['team_score'],	
				'versus' => $value['versus'],
				'vs_score' => $value['vs_score'],
				'result' => $value['result'],
				'isHome' => $value['isHome']
			);
			${'week_data_by_player_id_'.$e}[$value['starters']['WR']] = array(
				'week' => $e,
				'position' => 'WR',
				'team' => $value['team'],
				'team_score' => $value['team_score'],	
				'versus' => $value['versus'],
				'vs_score' => $value['vs_score'],
				'result' => $value['result'],
				'isHome' => $value['isHome']
			);
			${'week_data_by_player_id_'.$e}[$value['starters']['PK']] = array(
				'week' => $e,
				'position' => 'PK',
				'team' => $value['team'],
				'team_score' => $value['team_score'],	
				'versus' => $value['versus'],
				'vs_score' => $value['vs_score'],
				'result' => $value['result'],
				'isHome' => $value['isHome']
			);
	}
	$e++;
}

//printr($week_data_by_player_id_1, 0);

$weekstore = array();
foreach ($allstarters as $key => $week){
		foreach ($week as $pos => $theid){
			foreach ($theid as $myid => $player){
// 				echo $player.'<br>';
			if ($player == $mflid_str){
				$weekstore[$key][] = 1;
			} else {
				$weekstore[$key][] = 0;
			}
		}	
	}
}

foreach ($weekstore as $key => $week){
	$bytheweek[] = array_sum($week);
}

//printr($week_data_1, 0);


$jsonplayerscores = file_get_contents('http://www58.myfantasyleague.com/'.$year.'/export?TYPE=playerScores&L='.$lid.'&W=&YEAR=&PLAYERS='.$mflid.'&POSITION=&STATUS=&RULES=&COUNT=&JSON=1');
$playerscores = json_decode($jsonplayerscores, true);	

$playerscore = $playerscores['playerScores']['playerScore'];


// combine the player weekly results from API with the bytheweek to see if they started
$i = 0;
foreach ($playerscore as $scoreweek){
	if ($bytheweek[$i] == 1){
		$combined[$scoreweek['week']] = array(
			'id' => $scoreweek['id'],
			'score' => $scoreweek['score'],
			'starter' => $bytheweek[$i]
			
		);
	}
	$i++;
}


// printr($combined, 0);

foreach ($combined as $weekkey => $value){
	$mfl_player_array[$weekkey] = array(
		'score' => $value['score'],
		'player_data' =>  ${'week_data_by_player_id_'.$weekkey}[$value['id']]	
	);
}

//printr($mfl_player_array, 0);

// create new table for player if they don't exsit


$mydb = new wpdb('root','root','pflmicro','localhost');
$query = $mydb->get_results("select * from $pflid", ARRAY_N);
//var_dump($query);
if(empty($query)){
	$mydb->query($mydb->prepare ("create TABLE $pflid like 1991AikmQB", ARRAY_N));
} 

if(!empty($pflid)){                 
 	$wpdb->update(
		'players',
		array('mflid' => $mflid),
		array('p_id' => $pflid),
		array( '%s'),
		array( '%s')
 	);
} 

foreach ($mfl_player_array as $key => $value){
	$compile[] = array(
		$year.''.$weeks_2dig[$value['player_data']['week']],
		$year,
		$value['player_data']['week'],
		$value['score'],
		$value['player_data']['team'],
		$value['player_data']['versus']
	);
} 

//printr($compile, 0);


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
							<?php printr($playerprofile, 0); ?>
					
							<button id="nextplayerbtn" class="btn btn-default btn-hover-warning">Next Player</button><br/>
							
							
						<h4>Players who scored in <?php echo $year;?></h4>
						<p> Does not include OT</p>
						<?php 
							
							foreach ($allstarters as $values){
								foreach ($values as $players){
									$juststarterids[] = $players['QB'];
									$juststarterids[] = $players['RB'];
									$juststarterids[] = $players['WR'];
									$juststarterids[] = $players['PK'];
								}
							}
							$unique_starters = array_unique($juststarterids);
							foreach ($unique_starters as $starters){
								$starter_list[] = $starters;
							}
							printr($starter_list, 0); 
						?>

						</div>
						
					</div>
					<!--===================================================-->
				
				</div>
				
				<div class="col-sm-8">						
					<h4 class="mar-no"><?php echo $year;?> Data from MFL API</h4>
					<?php 
						printr($printpl, 0);
						printr($mfl_player_array, 0); 
					?>
					
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