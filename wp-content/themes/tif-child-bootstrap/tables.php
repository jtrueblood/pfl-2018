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
$currentyear = date('Y');

while ($firstyear <= $currentyear){
	$theyears[] = $firstyear;
	$firstyear++;
}

//printr($theyears, 0);

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

$allteams = array('ETS' => $ETS, 'PEP' => $PEP, 'WRZ' => $WRZ, 'CMN' => $CMN, 'BUL' => $BUL, 'SNR' => $SNR, 'TSG' => $TSG, 'SON' => $SON, 'HAT' => $HAT, 'DST' => $DST, 'ATK' => $ATK, 'PHR' => $PHR, 'MAX' => $MAX, 'RBS' => $RBS, 'BST' => $BST);


$champions = $wpdb->get_results("select * from wp_champions", ARRAY_N);



$playersassoc = get_players_assoc();

//printr($PEP, 1);

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
			$pos = substr($value['playerid'], -2);
			if($pos == 'PK'){
				if($value['points'] > 12){
					$pktoppoints[$value['playerid'].$value['weekids']] = $value['points'];
				}
			}
		}
	}
}

/*

usort($toppoints, function($a, $b) {
    return $a['points'] - $b['points'];
});
*/

// get playoff data by players
$playoffs = get_postseason();					
foreach($playoffs as $key => $item){
   $arr_playoff[$item['playerid']][$key] = $item;
}
ksort($arr_playoff, SORT_NUMERIC);

foreach ($arr_playoff as $key => $player){
	$points = array();
	foreach ($player as $item){
		$points[] = $item['score'];
		$new_playoff_array[$key] = array_sum($points);
	}
}


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

foreach ($theyears as $year){
	$get = get_standings($year);
	//$store[$year] = $get;
	foreach ($get as $val){
		$games = $val['divwin'] + $val['divloss'];
		if ($games > 0){
			$winpert = $val['divwin'] / $games;
			$allstandings[$val['teamid'].$year] = array(
			'divwin' => $val['divwin'],
			'divloss' => $val['divloss'],
			'div' => $val['division'],
			'winper' => number_format($winpert, 3)
			);
		}
		$allteampoints[$year][$val['teamid']] = $val['pts'];
	}
}

uasort($allstandings, function($a, $b) {
    return $b['winper'] <=> $a['winper'];
});

// get team point winners
foreach ($allteampoints as $year => $value){
	arsort($value);
	$teamint0 = key($value);
	$teamint1 = array_keys($value)[1];
	rsort($value);
	$points0 = $value[0];
	$points1 = $value[1];

	$teampointleaders[$year] = array(
		'team' => $teamint0,
		'points' => $points0,
		'secondteam' => $teamint1,
		'secondpoints' => $points1
	);
}

//printr($teampointleaders, 1);

//printr($allstandings, 1);
$rookieyears = get_player_rookie_years();

foreach ($rookieyears as $key => $value){
	$grab = get_player_season_stats($key, $value);
	if(isset($grab)){
		$rookieseason[$key] = array(
			'points' => $grab['points'],
			'games' => $grab['games'],
			'high' => $grab['high'],
			'teams' => array_unique($grab['teams'])	
		);
	}
}

uasort($rookieseason, function($a, $b) {
    return $b['points'] <=> $a['points'];
});

//printr($rookieseason, 1);

// build arrays needed to display team points and win related tables
function totalteampoints($TEAM){
	foreach ($TEAM as $getinfo){
		if($getinfo[9] >= 0){
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


$allteamget = array('ETS' => $getETS, 'PEP' => $getPEP, 'WRZ' => $getWRZ, 'CMN' => $getCMN, 'BUL' => $getBUL, 'SNR' => $getSNR, 'TSG' => $getTSG, 'SON' => $getSON, 'HAT' => $getHAT, 'DST' => $getDST, 'ATK' => $getATK, 'PHR' => $getPHR, 'MAX' => $getMAX, 'RBS' => $getRBS, 'BST' => $getBST);

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

//printr($allwins, 1);

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

// game streaks
$streaks = $wpdb->get_results("select pid, gamestreak from wp_allleaders", ARRAY_N);
foreach ($streaks as $value){
	$player_streaks[$value[0]] = $value[1];
}

arsort($player_streaks);

//printr($player_streaks, 0);

$playerids = just_player_ids();


foreach($playerids as $pl){
	$player_stats = get_player_career_stats($pl);
	$topseasonscores[$pl] = $player_stats['yeartotal']; 
}

foreach($topseasonscores as $key => $item){
	$pos = substr($key, -2);
	if(isset($item)){
		foreach($item as $subkey => $value){
			if($pos == 'QB'){
				if($value > 100){
					$highseas_qb[$key.$subkey] = $value;
				}
			}
			if($pos == 'RB'){
				if($value > 100){
					$highseas_rb[$key.$subkey] = $value;
				}
			}
			if($pos == 'WR'){
				if($value > 100){
					$highseas_wr[$key.$subkey] = $value;
				}
			}
			if($pos == 'PK'){
				if($value > 75){
					$highseas_pk[$key.$subkey] = $value;
				}
			}
		}
	}
}

arsort($highseas_qb);
arsort($highseas_rb);
arsort($highseas_wr);
arsort($highseas_pk);

foreach ($playersassoc as $key => $value){
	$get = get_player_career_stats($key);
	$ppgseason[$key] = $get['ppgbyseason'];
	$ppggames[$key] = $get['gamesbyseason'];
}


foreach ($ppgseason as $key => $value){
	if(isset($value)){
		foreach ($value as $year => $ppg){
			$checknumgames = $ppggames[$key][$year];
			if($checknumgames >= 8){
				$totalppggame[$key.$year] = $ppg;
			}
		}
	}
}

arsort($totalppggame);


//printr($totalppggame, 1);

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
				<p>This page requires the use of some transients.  If you see an error try loading a leaders by season page.</p>
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
			
			<div class="col-xs-24 col-sm-12 col-md-6">
			
			<?php 
				$teampoints = get_team_points('SNR');

				foreach ($theteams as $team){
					$teamjustpts[$team] = get_team_points($team);
				}
								
				foreach ($theteams as $t){
					$store = array();
					foreach ($teamjustpts[$t] as $key => $value){
							
							if($value >= 50){
								$store[] = $key;
								$j++;
								$consec[$t][$key] = $j;		
							} else {
								$j = 0;
								$consec[$t][$key] = $j;	
							}
							$count[$t] = count($store);			
					}
					
				}	
				arsort($count, SORT_NUMERIC);

				
				$labels = array('Team', 'Count');	
				tablehead('Number of 50+ Point Games', $labels);	
				
				$b = 1;
				foreach ($count as $key => $value){
					
						$fiftyprint .='<tr><td>'.$b.'. '.$teamids[$key].'</td>';
						$fiftyprint .='<td class="min-width text-right">'.$value.'</td></tr>';
						$b++;
				}
				
				echo $fiftyprint;

				
				tablefoot('Regular Season Only');	

			?>
			
			</div>
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				
				<?php 
					foreach ($consec as $key => $value){
						$conseccount[$key] = max($value);
					}
					arsort($conseccount, SORT_NUMERIC);
					//printr($conseccount, 0);
				
					$labels = array('Team', 'Count');	
					tablehead('Consecutive 50+ Point Games', $labels);	
					
					$b = 1;
					foreach ($conseccount as $key => $value){
						
							$fiftyconprint .='<tr><td>'.$b.'. '.$teamids[$key].'</td>';
							$fiftyconprint .='<td class="min-width text-right">'.$value.'</td></tr>';
							$b++;
					}
					
					echo $fiftyconprint;
	
					
					tablefoot('Regular Season Only');	
					
				?>
				
			</div>
			
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				
				<?php 
					$labels = array('Year', 'Team', 'Rec', 'W%', 'Div');	
					tablehead('Best Division Record', $labels);	
				
					$n = 0;
					foreach ($allstandings as $key => $value){
						$teamo = substr($key, 0, 3);
						$yearo = substr($key, 3, 4); 
							$printdivs .='<tr><td>'.$yearo.'</td>';
							$printdivs .='<td>'.$teamids[$teamo].'</td>';
							$printdivs .='<td>'.$value['divwin'].'-'.$value['divloss'].'</td>';
							$printdivs .='<td>'.$value['winper'].'</td>';
							$printdivs .='<td>'.$value['div'].'</td></tr>';
						$n++;
						if($n == 15){
							break;
						}
					}

					echo $printdivs;
						
					tablefoot('Top 15 Regular Season');	
					
				?>
				
			</div>
			
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				
				<?php 
					//printr($teampointleaders, 0);
					
					$labels = array('Year', 'Team', 'Points');	
					tablehead('Team Point Winners', $labels);	
				
					foreach ($teampointleaders as $key => $value){
							$printteapte .='<tr><td>'.$key.'</td>';
							if($value['points'] != $value['secondpoints'] ){
								$printteapte .='<td>'.$teamids[$value['team']].'</td>';
								$printteapte .='<td>'.$value['points'].'</td></tr>';
							} else {
								$printteapte .='<td>'.$teamids[$value['team']].' / '.$teamids[$value['secondteam']].'</td>';
								$printteapte .='<td>'.$value['points'].'</td></tr>';
							}
					}

					echo $printteapte;
						
					tablefoot('');	
					
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
					$postprint .='<td class="min-width text-center">'.$value.' / '.number_format($opps, 1).'</td>';
					$postprint .='<td class="min-width text-right">'.number_format($plperc, 3).'</td></tr>';
					$b++;
					
				}
				
				echo $postprint;
				
				tablefoot('');	
				?>	
				
			</div>
			
			<div class="col-xs-24 col-sm-12 col-md-6">
			<?php 
				foreach ($theteams as $team){
					$teampost = get_team_postseason_by_game($team); 
					
					if(isset($teampost)){
						$w = 0; $l = 0;
						foreach ($teampost as $key => $value){
							if ($value == 0){
								$l++;
							} 
							if ($value == 1){
								$w++;
							}
						}
						
						
						$posseasongames = count($teampost);
						
							$postseasonwins = array_sum($teampost);
							$avg = number_format(($postseasonwins / $posseasongames), 3);
						
						$allposty[] = array(
							'team' => $team,
							'wins' => $w,
							'loss' => $l,
							'games' => $posseasongames,
							'avg' => $avg
						);
					
						}	else {
							$allposty[] = array(
								'team' => $team,
								'wins' => 0,
								'loss' => 0,
								'games' => 0,
								'avg' => 0
							);
						}
					} 
					
					foreach ($allposty as $key => $value){
						$posty[$value['avg'].$value['team']] = $value;
					}
					krsort($posty);
				
				//printr($posty, 0);
				
				$labels = array('Team', 'Record', 'Win Per');	
				tablehead('Postseason Winning Percentage', $labels);	
				
				foreach ($posty as $key => $value){
					if($value['games'] != 0){
						$record = $value['wins'].' - '.$value['loss'];
					
						$psprint .='<tr><td>'.$teamlist[$value['team']].'</td>';
						$psprint .='<td class="text-center">'.$record.'</td>';
						$psprint .='<td class="text-center">'.$value['avg'].'</td></tr>';
							
					}

				}
				
				echo $psprint;
				
				tablefoot('');	
				
			?>
			</div>
			
			
			<!-- NEW SECTION -->
			<div class="col-xs-24">
				<h4>Player Data - Regular Season</h4>
				<hr>
			
			<!-- QB Game Scores -->	
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				arsort($toppoints);
				
				$labels = array('Player', 'Points');	
				tablehead('QB Individual Game Scores', $labels);	
				
				foreach ($toppoints as $key => $value){
					
					$highplayer = substr($key , 0, -6);
					$highpos = substr($highplayer, -2);
					$highweek= substr($key , -2);
					$highyear = substr($key , -6, -2);
					
					$name = get_player_name($highplayer);
					
					if($highpos == 'QB'){
						$qbtoppointsprint .='<tr><td>'.$name['first'].' '.$name['last'].' / '.$highweek.', '.$highyear.'</td>';
						$qbtoppointsprint .='<td class="min-width text-right">'.$value.'</td></tr>';
					}	
				}
				
				echo $qbtoppointsprint;
				
				tablefoot('26 Points or More');	
				
				?>	

			</div>
			
			<!-- RB Game Scores -->	
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 

				
				$labels = array('Player', 'Points');	
				tablehead('RB Individual Game Scores', $labels);	
				
				foreach ($toppoints as $key => $value){
					
					$highplayer = substr($key , 0, -6);
					$highpos = substr($highplayer, -2);
					$highweek= substr($key , -2);
					$highyear = substr($key , -6, -2);
					
					$name = get_player_name($highplayer);
					if($value > 28){
						if($highpos == 'RB'){
							$rbtoppointsprint .='<tr><td>'.$name['first'].' '.$name['last'].' / '.$highweek.', '.$highyear.'</td>';
							$rbtoppointsprint .='<td class="min-width text-right">'.$value.'</td></tr>';
						}	
					}
				}
				
				echo $rbtoppointsprint;
				
				tablefoot('29 Points or More');	
				
				?>	

			</div>
			
			<!-- WR Game Scores -->	
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 

				$labels = array('Player', 'Points');	
				tablehead('WR Individual Game Scores', $labels);	
				
				foreach ($toppoints as $key => $value){
					
					$highplayer = substr($key , 0, -6);
					$highpos = substr($highplayer, -2);
					$highweek= substr($key , -2);
					$highyear = substr($key , -6, -2);
					
					$name = get_player_name($highplayer);
					
					if($highpos == 'WR'){
						$wrtoppointsprint .='<tr><td>'.$name['first'].' '.$name['last'].' / '.$highweek.', '.$highyear.'</td>';
						$wrtoppointsprint .='<td class="min-width text-right">'.$value.'</td></tr>';
					}	
				}
				
				echo $wrtoppointsprint;
				
				tablefoot('26 Points or More');	
				
				?>	

			</div>
			
			<!-- PK Game Scores -->	
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				arsort($pktoppoints);
				
				$labels = array('Player', 'Points');	
				tablehead('PK Individual Game Scores', $labels);	
				
				foreach ($pktoppoints as $key => $value){
					
					$highplayer = substr($key , 0, -6);
					$highpos = substr($highplayer, -2);
					$highweek= substr($key , -2);
					$highyear = substr($key , -6, -2);
					
					$name = get_player_name($highplayer);
					
					if($highpos == 'PK'){
						$pktoppointsprint .='<tr><td>'.$name['first'].' '.$name['last'].' / '.$highweek.', '.$highyear.'</td>';
						$pktoppointsprint .='<td class="min-width text-right">'.$value.'</td></tr>';
					}	
				}
				
				echo $pktoppointsprint;
				
				tablefoot('13 Points or More');	
				
				?>	

			</div>
			
			</div>
			
			
			<div class="col-xs-24">
			
				<!-- QB Season Scores -->	
				<div class="col-xs-24 col-sm-12 col-md-6">
					<?php 
					
					$labels = array('Player', 'Points');	
					tablehead('QB Single Season Score', $labels);	
					
					$w = 0;
					foreach ($highseas_qb as $key => $value){
						
						$rplayer = substr($key , 0, -4);
						$rpos = substr($highplayer, -2);
						$ryear = substr($key , -4);
						
						$name = get_player_name($rplayer);
						
							$seas_qb_print .='<tr><td>'.$name['first'].' '.$name['last'].', '.$ryear.'</td>';
							$seas_qb_print .='<td class="min-width text-right">'.$value.'</td></tr>';
					
						if($w == 20){
							break;
						}
						
						$w++;
					}

					echo $seas_qb_print;
					
					tablefoot('Top 20 Seasons');	
					
					?>	
	
				</div>	
				
				<!-- RB Season Scores -->	
				<div class="col-xs-24 col-sm-12 col-md-6">
					<?php 
					
					$labels = array('Player', 'Points');	
					tablehead('RB Single Season Score', $labels);	
					
					$w = 0;
					foreach ($highseas_rb as $key => $value){
						
						$rplayer = substr($key , 0, -4);
						$rpos = substr($highplayer, -2);
						$ryear = substr($key , -4);
						
						$name = get_player_name($rplayer);
						
							$seas_rb_print .='<tr><td>'.$name['first'].' '.$name['last'].', '.$ryear.'</td>';
							$seas_rb_print .='<td class="min-width text-right">'.$value.'</td></tr>';
					
						if($w == 20){
							break;
						}
						
						$w++;
					}

					echo $seas_rb_print;
					
					tablefoot('Top 20 Seasons');	
					
					?>	
	
				</div>	
				
				
				<!-- WR Season Scores -->	
				<div class="col-xs-24 col-sm-12 col-md-6">
					<?php 
					
					$labels = array('Player', 'Points');	
					tablehead('WR Single Season Score', $labels);	
					
					$w = 0;
					foreach ($highseas_wr as $key => $value){
						
						$rplayer = substr($key , 0, -4);
						$rpos = substr($highplayer, -2);
						$ryear = substr($key , -4);
						
						$name = get_player_name($rplayer);
						
							$seas_wr_print .='<tr><td>'.$name['first'].' '.$name['last'].', '.$ryear.'</td>';
							$seas_wr_print .='<td class="min-width text-right">'.$value.'</td></tr>';
					
						if($w == 20){
							break;
						}
						
						$w++;
					}

					echo $seas_wr_print;
					
					tablefoot('Top 20 Seasons');	
					
					?>	
	
				</div>
				
				
				<!-- PK Season Scores -->	
				<div class="col-xs-24 col-sm-12 col-md-6">
					<?php 
					
					$labels = array('Player', 'Points');	
					tablehead('PK Single Season Score', $labels);	
					
					$w = 0;
					foreach ($highseas_pk as $key => $value){
						
						$rplayer = substr($key , 0, -4);
						$rpos = substr($highplayer, -2);
						$ryear = substr($key , -4);
						
						$name = get_player_name($rplayer);
						
							$seas_pk_print .='<tr><td>'.$name['first'].' '.$name['last'].', '.$ryear.'</td>';
							$seas_pk_print .='<td class="min-width text-right">'.$value.'</td></tr>';
					
						if($w == 20){
							break;
						}
						
						$w++;
					}

					echo $seas_pk_print;
					
					tablefoot('Top 20 Seasons');	
					
					?>	
	
				</div>		
				
				
						
			</div>
			
			
			<div class="col-xs-24">
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				
				$labels = array('Player', 'Points');	
				tablehead('Top Individual Game Scores', $labels);	
				
				foreach ($toppoints as $key => $value){
					
					$highplayer = substr($key , 0, -6);
					$highpos = substr($highplayer, -2);
					$highweek= substr($key , -2);
					$highyear = substr($key , -6, -2);
					
					$name = get_player_name($highplayer);
					if($value >= 30){
						$toppointsprint .='<tr><td>'.$name['first'].' '.$name['last'].' / '.$highweek.', '.$highyear.'</td>';
						$toppointsprint .='<td class="min-width text-right">'.$value.'</td></tr>';	
					}
				}
				
				echo $toppointsprint;
				
				tablefoot('');	
			
				
				?>	

			</div>
			
			<!-- CONSECUTIVE GAME STREAKS -->
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php 
				$labels = array('Player', 'Games');	
				tablehead('Consecutive Games Streak', $labels);	
				
				
				$b = 1;
				
				
					foreach ($player_streaks as $key => $value){
						
						$n = get_player_name($key);
						
						$streakprint .='<tr><td>'.$n['first'].' '.$n['last'].'</td>';
						$streakprint .='<td class="min-width text-center">'.$value.'</td>';
						
						if($b == 20){
							break;
						}
						
						$b++;
						
						
					}
				
				
				echo $streakprint;
				
				tablefoot('Games in a row where a player did not miss an appearance due to benching, injury or suspension.');	
				?>	
				
			</div>
			
			<div class="col-xs-24 col-sm-12 col-md-6">
				<?php
			$labels = array('Player', 'Season', 'PPG');	
				tablehead('Highest PPG in a Single Season', $labels);	
				
				
				$b = 1;
				
					foreach ($totalppggame as $key => $value){
						
						$pid = substr($key, 0, -4);
						$sea = substr($key, -4);
						
						$n = get_player_name($pid);
						
						$pppprint .='<tr><td>'.$n['first'].' '.$n['last'].'</td>'; 
						$pppprint .='<td>'.$sea.'</td>'; 
						$pppprint .='<td class="min-width text-center">'.number_format($value, 1).'</td></tr>';
						
						if($b == 20){
							break;
						}
						
						$b++;
						
						
					}
				
				
				echo $pppprint;
				
				tablefoot('Minimum 8 Games Played');		
				
			
			?>
			</div>
			
			<div class="row">
			</div>
			
			<div class="col-xs-24 col-sm-12 col-md-8">
				<?php
				$ro = get_award_rookie();	
					
				$labels = array('Player', 'Season', 'Points', 'Games', 'PPG', 'Teams', 'ROTY');	
				tablehead('Best Rookie Seasons', $labels);	
				
				
				$b = 1;
				
					foreach ($rookieseason as $key => $value){
						
						
						
						$awardcheck = '';
						$sea = substr($key, 0, 4);
						
						if($ro[$key] == $sea){
							$checkrook = '<i class="fa fa-circle"></i>';
						} else {
							$checkrook = '';
						} 
						
						$n = get_player_name($key);
						$rookppg = $value['points'] / $value['games'];
						
						$rookprint .='<tr><td>'.$n['first'].' '.$n['last'].'</td>'; 
						$rookprint .='<td class="min-width text-center">'.$sea.'</td>'; 
						$rookprint .='<td class="min-width text-center">'.$value['points'].'</td>';
						$rookprint .='<td class="min-width text-center">'.$value['games'].'</td>';
						$rookprint .='<td class="min-width text-center">'.number_format($rookppg, 1).'</td>';
						$rookprint .='<td class="min-width text-center">'.$value['teams'][0].'</td>';
						$rookprint .='<td class="min-width text-center">'.$checkrook.'</td></tr>';
						
						if($b == 20){
							break;
						}
						
						$b++;			
						
					}

				echo $rookprint;
				
				tablefoot('<i class="fa fa-circle"></i> = PFL Rookie of the Year');		
				
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
					
						if ($value >= 25){
							$topplayptsprint .='<tr><td>'.$name['first'].' '.$name['last'].'</td>';
							$topplayptsprint .='<td>'.$highweek.', '.$highyear.'</td>';
							$topplayptsprint .='<td class="min-width text-right">'.$value.'</td></tr>';
						}
	
				}
				
				echo $topplayptsprint;
				
				tablefoot('-- Score of 25+');	
			
				
				?>	
				
				</div>
				
				
				<div class="col-xs-24 col-sm-12 col-md-6">
				<?php
					
					arsort($new_playoff_array);
					
					//printr($new_playoff_array, 0); 

					$labels = array('Player','Total Points');	
					tablehead('Post Season Individual Career Points', $labels);	
					
					
					$c = 1;
					foreach ($new_playoff_array as $key => $value){
						
						$name = get_player_name($key);
						
						$plpostprint .='<tr><td>'.$name['first'].' '.$name['last'].'</td>';
						$plpostprint .='<td class="text-right">'.$value.'</td></tr>';
						
						if($c == 20){
							break;
						}
						
						$c++;
						
					}
					
					echo $plpostprint;
					
					tablefoot('-- Top 20 Players');	
				

				
				?>	
				
				</div>

				
		
		</div>
		
		
		<?php
		$number_ones = get_number_ones();
		foreach ($number_ones as $key => $value){			
			$season = substr($value['id'], -4);
			$player_number_ones[$key] = array(
				'season' => $season,
				'points' => $value['points'],
				'team' => $value['teams']
			);
		}
	
		$seasons = the_seasons();
			foreach ($seasons as $value){
				if (empty($pointarray[$value])){
					$theval = 0; 
				} else {
					$theval = $pointarray[$value];
				}
				$playerchartpts[$value] = $theval;
			}
			
			// get avarage arrays
			
			foreach($number_ones as $key => $item){
			   $arr_t_ones[$item['pos']][$key] = $item;
			}
			
			$qb_for_chart = $arr_t_ones['QB'];
			$rb_for_chart = $arr_t_ones['RB'];
			$wr_for_chart = $arr_t_ones['WR'];
			$pk_for_chart = $arr_t_ones['PK'];
			
			foreach ($qb_for_chart as $value){
				$qb_chart_data[$value['year']] = array(
					'high' => $value['points'],
					'avg' => $value['avg']	
				);
			}

			foreach ($rb_for_chart as $value){
				$rb_chart_data[$value['year']] = array(
					'high' => $value['points'],
					'avg' => $value['avg']	
				);
			}

			foreach ($wr_for_chart as $value){
				$wr_chart_data[$value['year']] = array(
					'high' => $value['points'],
					'avg' => $value['avg']	
				);
			}

			foreach ($pk_for_chart as $value){
				$pk_chart_data[$value['year']] = array(
					'high' => $value['points'],
					'avg' => $value['avg']	
				);
			}

			
			//printr($player_number_ones, 0);
		?>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			Highcharts.chart('playerhighchart', {
			title: {
			        text: 'Season Points & Average by Position'
			    },
			    xAxis: {
			        categories: [<?php 
				        foreach ($playerchartpts as $key => $value){
					        echo $key.',';
				        } 
				    ?>]
			    },
			    labels: {
			        items: [{
			            html: '',
			            style: {
			                top: '18px',
			                color: (Highcharts.theme && Highcharts.theme.textColor) || 'black'
			            }
			        }]
			    },
			    series: [{
			        type: 'spline',
			        name: 'QB Average',
			        color: '#cadeef',
			        data: [<?php 
				        foreach ($qb_chart_data as $key => $value){
					        echo $value['avg'].',';
				        } 
				    ?>],
			        marker: {
			            lineWidth: 2,
			            lineColor: '#cadeef',
			            fillColor: 'white'
			        }
			    },
			    	{
			        type: 'spline',
			        name: 'QB High Value',
			        color: '#3f88c5',
			        data: [<?php 
				        foreach ($qb_chart_data as $key => $value){
					        echo $value['high'].',';
				        } 
				    ?>],
			        marker: {
			            lineWidth: 2,
			            lineColor: '#3f88c5',
			            fillColor: 'white'
			        }
			    },
			    {
			        type: 'spline',
			        name: 'RB Average',
			        color: '#f2b9b9',
			        data: [<?php 
				        foreach ($rb_chart_data as $key => $value){
					        echo $value['avg'].',';
				        } 
				    ?>],
			        marker: {
			            lineWidth: 2,
			            lineColor: '#f2b9b9',
			            fillColor: 'white'
			        }
			    },
			    	{
			        type: 'spline',
			        name: 'RB High Value',
			        color: '#d00000',
			        data: [<?php 
				        foreach ($rb_chart_data as $key => $value){
					        echo $value['high'].',';
				        } 
				    ?>],
			        marker: {
			            lineWidth: 2,
			            lineColor: '#d00000',
			            fillColor: 'white'
			        }
			    },
			   {
			        type: 'spline',
			        name: 'WR Average',
			        color: '#dcede3',
			        data: [<?php 
				        foreach ($wr_chart_data as $key => $value){
					        echo $value['avg'].',';
				        } 
				    ?>],
			        marker: {
			            lineWidth: 2,
			            lineColor: '#dcede3',
			            fillColor: 'white'
			        }
			    },
			    	{
			        type: 'spline',
			        name: 'WR High Value',
			        color: '#82c09a',
			        data: [<?php 
				        foreach ($wr_chart_data as $key => $value){
					        echo $value['high'].',';
				        } 
				    ?>],
			        marker: {
			            lineWidth: 2,
			            lineColor: '#82c09a',
			            fillColor: 'white'
			        }
			    },
			   {
			        type: 'spline',
			        name: 'PK Average',
			        color: '#ffecbb',
			        data: [<?php 
				        foreach ($pk_chart_data as $key => $value){
					        echo $value['avg'].',';
				        } 
				    ?>],
			        marker: {
			            lineWidth: 2,
			            lineColor: '#ffecbb',
			            fillColor: 'white'
			        }
			    },
			    	{
			        type: 'spline',
			        name: 'PK High Value',
			        color: '#ffba08',
			        data: [<?php 
				        foreach ($pk_chart_data as $key => $value){
					        echo $value['high'].',';
				        } 
				    ?>],
			        marker: {
			            lineWidth: 2,
			            lineColor: '#ffba08',
			            fillColor: 'white'
			        }
			    }]
			});	
		});	
		</script>
		
		<div class="col-xs-24">
		<div class="panel hidden-xs">
			<div id="playerhighchart"></div> 
		</div>
		
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