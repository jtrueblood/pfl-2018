<?php
/*
 * Template Name: Tables Page
 * Description: Page for displaying a whole bunch of tables
 */
 ?>

<!-- necessary cache fies are pulled in via the 'pointsleader' function in functions.php -->

<?php get_header(); 

$teamlist = array(
	'RBS' => 'Red Barons',
	'ETS' => 'Euro-Trashers',
	'PEP' => 'Peppers',
	'WRZ' => 'Space Warriorz',
	'CMN' => 'C-Men',
	'BUL' => 'Raging Bulls',
	'SNR' => 'Sixty Niners',
	'TSG' => 'Tsongas',
	'BST' => 'Booty Bustas',
	'MAX' => 'Mad Max',
	'PHR' => 'Paraphernalia',
	'SON' => 'Rising Son',
	'ATK' => 'Melmac Attack',
	'HAT' => 'Jimmys Hats',
	'DST' => 'Destruction'	
);

$theteams = array('MAX', 'ETS', 'PEP', 'WRZ', 'SON', 'PHR', 'ATK', 'HAT', 'CMN', 'BUL', 'SNR', 'TSG', 'RBS', 'BST','DST');
$firstyear = 1991;
$currentyear = date("Y");

while ($firstyear < $currentyear){
	$theyears[] = $firstyear;
	$firstyear++;
}



$mydb = new wpdb('root','root','local','localhost');
	
$RBS = $wpdb->get_results("select * from wp_team_RBS", ARRAY_N);
$ETS = $wpdb->get_results("select * from wp_team_ETS", ARRAY_N);
$PEP = $wpdb->get_results("select * from wp_team_PEP", ARRAY_N);
$WRZ = $wpdb->get_results("select * from wp_team_WRZ", ARRAY_N);
$CMN = $wpdb->get_results("select * from wp_team_CMN", ARRAY_N);
$BUL = $wpdb->get_results("select * from wp_team_BUL", ARRAY_N);
$SNR = $wpdb->get_results("select * from wp_team_SNR", ARRAY_N);
$TSG = $wpdb->get_results("select * from wp_team_TSG", ARRAY_N);
$BST = $wpdb->get_results("select * from wp_team_BST", ARRAY_N);
$MAX = $wpdb->get_results("select * from wp_team_MAX", ARRAY_N);
$PHR = $wpdb->get_results("select * from wp_team_PHR", ARRAY_N);
$SON = $wpdb->get_results("select * from wp_team_SON", ARRAY_N);
$ATK = $wpdb->get_results("select * from wp_team_ATK", ARRAY_N);
$HAT = $wpdb->get_results("select * from wp_team_HAT", ARRAY_N);
$DST = $wpdb->get_results("select * from wp_team_DST", ARRAY_N);

$allteams = array('ETS' => $ETS, 'PEP' => $PEP, 'WRZ' => $WRZ, 'CMN' => $CMN, 'BUL' => $BUL, 'SNR' => $SNR, 'TSG' => $TSG, 'SON' => $SON, 'HAT' => $HAT, 'DST' => $DST, 'ATK' => $ATK, 'PHR' => $PHR, 'MAX' => $MAX);


$champions = $wpdb->get_results("select * from wp_champions", ARRAY_N);



$playersassoc = get_players_assoc();



/*
$me = get_player_name('2004BreeQB');
printr($me, 0);
die();
*/


function allplayerdata_tables_trans() {
	$transient = get_transient( 'allplayerdata_table_trans' );
	if( ! empty( $transient ) ) {
    	return $transient;
  	} else {
	  	global $playersassoc;
	  	foreach ($playersassoc as $key => $value){
	  		$allplayerdata[$key] = get_player_data($key);
		}
	    set_transient( 'allplayerdata_table_trans', $allplayerdata, 129600 );
	    return $set;
    }
}

$allplayerdata = allplayerdata_tables_trans();


foreach ($allplayerdata as $weeks){
	if(!empty($weeks)){
			foreach ($weeks as $key => $value){
				if($value['points'] > 25){
				$toppoints[$value['playerid'].$value['weekids']] = $value['points'];
			}
		}
	}
}
/*

usort($toppoints, function($a, $b) {
    return $a['points'] - $b['points'];
});
*/




function local_all_team_data_trans() {
	$transient = get_transient( 'local_all_team_data_trans' );
	if( ! empty( $transient ) ) {
    	return $transient;
  	} else {
		global $allteams;
		set_transient( 'local_all_team_data_trans', $allteams, '' );
		return $set;
  	}
}

$all_team_transient = local_all_team_data_trans();


//printr($all_team_transient, 0);

//printr($teamarray, 0);

/*
$k = 0;
foreach ($theyears as $getstandings){
	echo $getstandings;
	get_cache('standings/stand'.$getstandings, 0);
	$stand[$getstandings] = $_SESSION['standings/stand'.$getstandings];
}
*/

// build arrays needed to display team points and win related tables
function totalteampoints($TEAM){
	foreach ($TEAM as $getinfo){
		if($getinfo[9] > 0){
			$winloss = 1;
		} else {
			$winloss = 0;
		}
		$info[] = array($getinfo[4], $winloss, $getinfo[6]);
	}
	
	
	foreach ($info as $flatten){
		$ppoints[] = $flatten[0];
		$pwins[] = $flatten[1];
		$pvs[] = $flatten[2];
	}
		
	$points = array_sum($ppoints);
	$wins = array_sum($pwins);
	$games = count($pwins);
	$loss = $games - $wins;
	$ppg = number_format(($points / $games), 1);
	$winper = $wins / $games;
	$versus = array_sum($pvs);
	$seasons = ($games / 14);
	$vs = $versus / $seasons;
	$dif = $points - $versus;
	
	return array($points, $wins, $games, $ppg, $winper, $vs, $seasons, $dif);
}

$getRBS = totalteampoints($RBS);
$getBST = totalteampoints($BST);

$getPEP = totalteampoints($PEP);
$getETS = totalteampoints($ETS);
$getMAX = totalteampoints($MAX);
$getWRZ = totalteampoints($WRZ);

$getSON = totalteampoints($SON);
$getPHR = totalteampoints($PHR);
$getHAT = totalteampoints($HAT);
$getATK = totalteampoints($ATK);

$getCMN = totalteampoints($CMN);
$getBUL = totalteampoints($BUL);
$getSNR = totalteampoints($SNR);
$getTSG = totalteampoints($TSG);

$getDST = totalteampoints($DST);


$allteamget = array('ETS' => $getETS, 'PEP' => $getPEP, 'WRZ' => $getWRZ, 'CMN' => $getCMN, 'BUL' => $getBUL, 'SNR' => $getSNR, 'TSG' => $getTSG, 'SON' => $getSON, 'HAT' => $getHAT, 'DST' => $getDST, 'ATK' => $getATK, 'PHR' => $getPHR, 'MAX' => $getMAX);

/*
printr($CMN, 0);
die();
*/

// Pull values from 'allteams' 
foreach ($allteamget as $key => $value){
	$allpoints[$key] = $value[0];
	$allwins[$key] = $value[1];
	$allgames[$key] = $value[2];
	$allppg[$key] = $value[3];
	$allwinper[$key] = $value[4];
	$allvs[$key] = $value[5];
	$allseasons[$key] = $value[6];
	$alldif[$key] = $value[7];
}

/*
printr($allpoints, 0);
die();
*/

arsort($allpoints);
arsort($allwins);
arsort($allgames);
arsort($allppg);
arsort($allwinper);
asort($allvs);
asort($alldif);

foreach ($allteams as $key => $value){
	foreach ($value as $differ){
		$gamediffs[$key.$differ[0].$differ[5]] = $differ[9];
	}
}

arsort($gamediffs);



/*
printr($gamediffs, 0);
die();
*/





// arrays for individual seasons and games
function teamhighs($TEAM){
	foreach ($TEAM as $getinfo){
		$theseason = $getinfo[1];
		$high[] = array($theseason => $getinfo[4]);
	}
	
	$sumArray = array();
	foreach ($high as $k=>$subArray) {
		foreach ($subArray as $id=>$value) {
			$sumArray[$id]+=$value;
		}
	}
					
	return $sumArray;
}

$highRBS = teamhighs($RBS);
$highBST = teamhighs($BST);

$highPEP = teamhighs($PEP);
$highETS = teamhighs($ETS);
$highMAX = teamhighs($MAX);
$highWRZ = teamhighs($WRZ);

$highSON = teamhighs($SON);
$highPHR = teamhighs($PHR);
$highHAT = teamhighs($HAT);
$highATK = teamhighs($ATK);

$highCMN = teamhighs($CMN);
$highBUL = teamhighs($BUL);
$highSNR = teamhighs($SNR);
$highTSG = teamhighs($TSG);

$highDST = teamhighs($DST);

$allhighs = array('RBS' => $highRBS, 'BST' => $highBST, 'ETS' => $highETS, 'PEP' => $highPEP, 'WRZ' => $highWRZ, 'MAX' => $highMAX, 'SON' => $highSON, 'PHR' => $highPHR, 'HAT' => $highHAT, 'ATK' => $highATK, 'CMN' => $highCMN, 'SNR' => $highSNR, 'BUL' => $highBUL, 'TSG' => $highTSG, 'DST' => $highDST);

foreach ($allhighs as $teamkey => $gethigh){
	$teamid = $teamkey;
	foreach ($gethigh as $key => $value){
		$superhigh[] = array('teamid' => $teamkey, 'year' => $key, 'points' => $value); 
		$sorthigh[$teamkey.''.$key] = $value;
	}
}

arsort($sorthigh);




function bestweek($TEAM, $myteam){
	foreach ($TEAM as $key => $value){
		$bestweek[$myteam.''.$value[0]] = $value[4];
	}
	return $bestweek;
}

$bestRBS = bestweek($RBS, 'RBS');
$bestBST = bestweek($BST, 'BST');

$bestETS = bestweek($ETS, 'ETS');
$bestPEP = bestweek($PEP, 'PEP');
$bestWRZ = bestweek($WRZ, 'WRZ');
$bestMAX = bestweek($MAX, 'MAX');

$bestHAT = bestweek($HAT, 'HAT');
$bestATK = bestweek($ATK, 'ATK');
$bestPHR = bestweek($PHR, 'PHR');
$bestSON = bestweek($SON, 'SON');

$bestBUL = bestweek($BUL, 'BUL');
$bestCMN = bestweek($CMN, 'CMN');
$bestSNR = bestweek($SNR, 'SNR');
$bestTSG = bestweek($TSG, 'TSG');

$bestDST = bestweek($DST, 'DST');


$allbest = array('RBS' => $bestRBS, 'BST' => $bestBST, 'ETS' => $bestETS, 'PEP' => $bestPEP, 'WRZ' => $bestWRZ, 'MAX' => $bestMAX, 'SON' => $bestSON, 'PHR' => $bestPHR, 'HAT' => $bestHAT, 'ATK' => $bestATK, 'CMN' => $bestCMN, 'SNR' => $bestSNR, 'BUL' => $bestBUL, 'TSG' => $bestTSG, 'DST' => $bestDST);

$flatallbest = array_flatten($allbest);
arsort($flatallbest);

foreach ($champions as $value){
	$cha[] = $value[2];
	$app[] = array($value[2], $value[5]);
}


$championsort = array_count_values($cha);
arsort($championsort);

$flatapps = array_flatten($app);
$appsort = array_count_values($flatapps);
arsort($appsort );


// get all games from week 15 only
$playoffs = $wpdb->get_results("select * from wp_playoffs where week = 15", ARRAY_N);

$t = 0;
foreach ($playoffs as $value){
	if ($t % 4 == 0){
		$playoffteams[$value[5]][$value[1]] = $value[6];
		$playoffteams_count[$value[5]][] = 1;
	}
	$t++;
}

foreach ($playoffteams_count as $key => $value){
	$playoffsort[$key] = array_count_values($value);
}
arsort($playoffsort);

foreach ($playoffsort as $key => $value){
	$playoffsort_flat[$key] = $value[1];
}


$postseasonhighs = $wpdb->get_results("select * from wp_playoffs where points > 20 ", ARRAY_N);
// get players high scores for playoff games
foreach ($postseasonhighs as $value){
	$playoff_player_highs[$value[3].$value[1].$value[2]] = $value[4];
}
arsort($playoff_player_highs);


//printr($playoff_player_highs, 1);


?>


			
<!--CONTENT CONTAINER-->
<div class="boxed">

<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">


	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">
		
		<div class="row">
			
			<div class="col-xs-24">
				<h4>Team Data - Regular Season</h4>
				<hr>
			</div>
			
			<div class="col-xs-24 col-sm-12 col-md-6">
	
				<?php 
				$pointlabels = array('Team', 'Points');	
				tablehead('Points', $pointlabels);	
				
				$a = 1;
				foreach ($allpoints as $key => $thepoints){
					$printpoints .='<tr><td>'.$a.'. '.$teamids[$key].'</td>';
					$printpoints .='<td class="min-width text-right">'.number_format($thepoints).'</td></tr>';
				$a++;
				}
				echo $printpoints;

				tablefoot('');?>								
								
			</div>
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				$labels = array('Team', 'PPG');	
				tablehead('Points Per Game', $labels);	
				
				$a = 1;
				foreach ($allppg as $key => $value){
					$ppgprint .='<tr><td>'.$a.'. '.$teamids[$key].'</td>';
					$ppgprint .='<td class="min-width text-right">'.number_format($value, 1).'</td></tr>';
					$a++;
				}
				echo $ppgprint;
				
				tablefoot('');	
				?>	
			</div>
			
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
// 				printr($allwins, 0);	
					
				$pointlabels = array('Team', 'Wins');	
				tablehead('Wins', $pointlabels);	
				
				$a = 1;
				foreach ($allwins as $key => $thewins){
					$printwins .='<tr><td>'.$a.'. '.$teamids[$key].'</td>';
					$printwins .='<td class="min-width text-right">'.number_format($thewins).'</td></tr>';
					$a++;
				}
				echo $printwins;
				
				tablefoot('');	
				?>	
			</div>
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				$labels = array('Team', 'Win %');	
				tablehead('Winning Percentage', $labels);	
				
				$a = 1;
				foreach ($allwinper as $key => $value){
					$print .='<tr><td>'.$a.'. '.$teamids[$key].'</td>';
					$print .='<td class="min-width text-right">'.number_format($value, 3).'</td></tr>';
					$a++;
				}
				echo $print;
				
				tablefoot('');	
				?>	
			</div>
			
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				$labels = array('Team', 'PTS Allowed');	
				tablehead('Defense - Average Season Points Against', $labels);	
				
				$a = 1;
				foreach ($allvs as $key => $value){
					if ($allseasons[$key] >= 4){
						$vsprint .='<tr><td>'.$a.'. '.$teamids[$key].'</td>';
						$vsprint .='<td class="min-width text-right">'.number_format($value, 1).'</td></tr>';
						$a++;
					}
				}
				
				echo $vsprint;
				
				tablefoot('-- Min 4 seasons played');	
				?>	
				
			</div>
			
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				$labels = array('Team / Year', 'Points');	
				tablehead('Highest Single Season Point Totals', $labels);	
				
				$b = 1;
				foreach ($sorthigh as $key => $value){
					$hteam = substr($key , 0, -4);
					$hyear = substr($key , -4);
					if ($b <= 15){
						$shighprint .='<tr><td>'.$b.'. '.$teamids[$hteam].' / '.$hyear.'</td>';
						$shighprint .='<td class="min-width text-right">'.$value.'</td></tr>';
						$b++;
					}
				}
				
				echo $shighprint;
				
				tablefoot('-- Top 15 Teams');	
				?>	
				
			</div>
			
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				$labels = array('Team / Week', 'Points');	
				tablehead('Highest Single Game Team Score', $labels);	
				
				$b = 1;
				foreach ($flatallbest as $key => $value){
					$flteam = substr($key , 0, -6);
					$flyear = substr($key , 3, -2);
					$flweek = substr($key , -2);
					if ($b <= 15){
						$flatprint .='<tr><td>'.$b.'. '.$teamids[$flteam].' / '.$flyear.', '.$flweek.'</td>';
						$flatprint .='<td class="min-width text-right">'.$value.'</td></tr>';
						$b++;
					}
				}
				
				echo $flatprint;
				
				tablefoot('-- Top 15 Games');	
				?>	
				
			</div>
			
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				$labels = array('Team / Week', 'Margin');	
				tablehead('Largest Margin of Victory', $labels);	
				
				$b = 1;
				foreach ($gamediffs as $key => $value){
					$flteam = substr($key , 0, -9);
					$flyear = substr($key , 3, -5);
					$flweek = substr($key , 7, -3);
					$flversus = substr($key , -3);
					if ($b <= 15){
						$blowoutprint .='<tr><td>'.$b.'. '.$teamids[$flteam].' / '.$flyear.', '.$flweek.' over '.$flversus.'</td>';
						$blowoutprint .='<td class="min-width text-right">'.$value.'</td></tr>';
					}
					$b++;	
				}
				
				echo $blowoutprint;

				
				tablefoot('-- Top 15 Games');	
				?>	
				
			</div>
			
			
			

			<!-- NEW SECTION -->
			<div class="col-xs-24">
				<h4>Team Data - Post Season</h4>
				<hr>
			</div>
			
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				$labels = array('Team', 'Count');	
				tablehead('Total PFL Championships', $labels);	
				
				$b = 1;
				foreach ($championsort as $key => $value){
		
					$champprint .='<tr><td>'.$teamlist[$key].'</td>';
					$champprint .='<td class="min-width text-right">'.$value.'</td></tr>';
					$b++;
					
				}
				
				echo $champprint;
				
				tablefoot('');	
				?>	
				
			</div>
			
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				$labels = array('Team', 'Count');	
				tablehead('Posse Bowl Appearances', $labels);	
				
				$b = 1;
				foreach ($appsort as $key => $value){
		
					$pbappprint .='<tr><td>'.$teamlist[$key].'</td>';
					$pbappprint .='<td class="min-width text-right">'.$value.'</td></tr>';
					$b++;
					
				}
				
				echo $pbappprint;
				
				tablefoot('');	
				?>	
				
			</div>
			
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				$labels = array('Team', 'Count/Opps', 'Percent');	
				tablehead('Post Season Appearances', $labels);	
				
				
				$b = 1;
				foreach ($playoffsort_flat as $key => $value){
					$opps = ${'get'.$key}[6];
					$plperc = $value / $opps;
					
					$postprint .='<tr><td>'.$teamlist[$key].'</td>';
					$postprint .='<td class="min-width text-center">'.$value.' / '.$opps.'</td>';
					$postprint .='<td class="min-width text-right">'.number_format($plperc, 3).'</td></tr>';
					$b++;
					
				}
				
				echo $postprint;
				
				tablefoot('');	
				?>	
				
			</div>
			
			
			
			<!-- NEW SECTION -->
			<div class="col-xs-24">
				<h4>Player Data - Regular Season</h4>
				<hr>
				
				
				<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				arsort($toppoints);
				
				
				
				$labels = array('Player', 'Points');	
				tablehead('Top Individual Game Scores', $labels);	
				
//				$b = 1;
				foreach ($toppoints as $key => $value){
					
					$highplayer = substr($key , 0, -6);
					$highpos = substr($highplayer, -2);
					$highweek= substr($key , -2);
					$highyear = substr($key , -6, -2);
					
					$name = get_player_name($highplayer);
					
// 					if ($highpos == 'RB'){
						if ($value >= 30){
							$toppointsprint .='<tr><td>'.$name['first'].' '.$name['last'].' / '.$highweek.', '.$highyear.'</td>';
							$toppointsprint .='<td class="min-width text-right">'.$value.'</td></tr>';
						}
// 					}
//					$b++;
					
				}
				
				echo $toppointsprint;
				
				tablefoot('-- Score of 30+');	
			
				
				?>	
				
			
			</div>
			
			
			
			<!-- NEW SECTION -->
			<div class="col-xs-24">
				<h4>Player Data - Post Season</h4>
				<hr>
			</div>
	
				<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 

				$labels = array('Player', 'Season', 'Points');	
				tablehead('Top Individual Playoff Scores', $labels);	
				
				
				foreach ($playoff_player_highs as $key => $value){
					
					$highguy = substr($key , 0, -6);
					//$highpos = substr($highguy, -2, -1);
					$getweek = substr($key , -2);
					if($getweek == 15){
						$highweek = 'Playoffs';
					} else {
						$highweek = 'Posse Bowl';	
					}
					
					$highyear = substr($key , -6, -2);
					
					$name = get_player_name($highguy);
					
						if ($value >= 20){
							$topplayptsprint .='<tr><td>'.$name['first'].' '.$name['last'].'</td>';
							$topplayptsprint .='<td>'.$highweek.', '.$highyear.'</td>';
							$topplayptsprint .='<td class="min-width text-right">'.$value.'</td></tr>';
						}
					
				}
				
				echo $topplayptsprint;
				
				tablefoot('-- Score of 20+');	
			
				
				?>	
				
	
		
		</div>
		</div>
	</div>
	<!--===================================================-->
	<!--End page content-->


</div>
<!--===================================================-->
<!--END CONTENT CONTAINER-->
		
</div>

			
<?php include_once('main-nav.php'); ?>


<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>