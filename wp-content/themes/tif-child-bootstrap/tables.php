<?php
/*
 * Template Name: Tables Page
 * Description: Page for displaying a whole bunch of tables
 */
 ?>

<!-- necessary cache fies are pulled in via the 'pointsleader' function in functions.php -->

<?php get_header(); 


//cache all team files
$theteams = array('MAX', 'ETS', 'PEP', 'WRZ', 'SON', 'PHR', 'ATK', 'HAT', 'CMN', 'BUL', 'SNR', 'TSG', 'RBS', 'BST','DST');
$firstyear = 1991;
$currentyear = date("Y");

while ($firstyear < $currentyear){
	$theyears[] = $firstyear;
	$firstyear++;
}


$i = 0;
foreach ($theteams as $getteams){
	get_cache('team/'.$getteams.'_f', 0);
	${$getteams}[] = $_SESSION['team/'.$getteams.'_f'];
	$teamarray[] = ${$getteams}['team_'.$i.'_f'];
}

$k = 0;
foreach ($theyears as $getstandings){
	echo $getstandings;
	get_cache('standings/stand'.$getstandings, 0);
	$stand[$getstandings] = $_SESSION['standings/stand'.$getstandings];
}

// build arrays needed to display team points and win related tables
function totalteampoints($TEAM){
	foreach ($TEAM[0] as $getinfo){
		$info[] = array($getinfo['points'], $getinfo['result'], $getinfo['vspts']);
	}
	
	foreach ($info as $flatten){
		$ppoints[] = $flatten[0];
		$ploss[] = $flatten[1];
		$pvs[] = $flatten[2];
	}
		
	$points = array_sum($ppoints);
	$loss = array_sum($ploss);
	$games = count($ploss);
	$wins = $games - $loss;
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


$allteams = array('RBS' => $getRBS, 'BST' => $getBST, 'ETS' => $getETS, 'PEP' => $getPEP, 'WRZ' => $getWRZ, 'MAX' => $getMAX, 'SON' => $getSON, 'PHR' => $getPHR, 'HAT' => $getHAT, 'ATK' => $getATK, 'CMN' => $getCMN, 'SNR' => $getSNR, 'BUL' => $getBUL, 'TSG' => $getTSG, 'DST' => $getDST);

// Pull values from 'allteams' 
foreach ($allteams as $key => $value){
	$allpoints[$key] = $value[0];
	$allwins[$key] = $value[1];
	$allgames[$key] = $value[2];
	$allppg[$key] = $value[3];
	$allwinper[$key] = $value[4];
	$allvs[$key] = $value[5];
	$allseasons[$key] = $value[6];
	$alldif[$key] = $value[7];
}
arsort($allpoints);
arsort($allwins);
arsort($allgames);
arsort($allppg);
arsort($allwinper);
asort($allvs);
asort($alldif);


// arrays for individual seasons and games
function teamhighs($TEAM){
	foreach ($TEAM[0] as $getinfo){
		$theseason = $getinfo['season'];
		$high[] = array($theseason => $getinfo['points']);
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
	foreach ($TEAM[0] as $key => $value){
		$bestweek[$myteam.''.$value['id']] = $value['points'];
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

$bestDST = bestweek($TSG, 'DST');


$allbest = array('RBS' => $bestRBS, 'BST' => $bestBST, 'ETS' => $bestETS, 'PEP' => $bestPEP, 'WRZ' => $bestWRZ, 'MAX' => $bestMAX, 'SON' => $bestSON, 'PHR' => $bestPHR, 'HAT' => $bestHAT, 'ATK' => $bestATK, 'CMN' => $bestCMN, 'SNR' => $bestSNR, 'BUL' => $bestBUL, 'TSG' => $bestTSG, 'DST' => $bestDST);


$flatallbest = array_flatten($allbest);
arsort($flatallbest);

function topscoreyear($yyyear){
	$arraycheck = $stand[$yyyear];
	foreach ($arraycheck as $topscore){
		$topid = $topscore['teamid'];
		echo $topid;
		$toppts = $topscore['pts']; 
		$gettopbyyear[$topid] = $toppts;	
	}

	return $gettopbyyear;
}

$stand1991 = topscoreyear(1991);

printr($stand1991, 1);	
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