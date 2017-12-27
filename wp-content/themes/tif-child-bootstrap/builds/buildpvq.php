<?php
/*
 * Template Name: Build PVQ
 * Description: Master Build for generating Player Value Quotent.  Requires that leaderbuild be up to date first
 */
 ?>

<!-- Make the required arrays and cached files availible on the page -->
<?php 
	
get_header();			

get_cache('rankyears/alltimeleaders_QB', 0);	
$leadersqb = $_SESSION['rankyears/alltimeleaders_QB'];
get_cache('rankyears/alltimeleaders_RB', 0);	
$leadersrb = $_SESSION['rankyears/alltimeleaders_RB'];
get_cache('rankyears/alltimeleaders_WR', 0);	
$leaderswr = $_SESSION['rankyears/alltimeleaders_WR'];
get_cache('rankyears/alltimeleaders_PK', 0);	
$leaderspk = $_SESSION['rankyears/alltimeleaders_PK'];
get_cache('allplayerdata', 0);	
	
$players = $_SESSION['players'];

foreach ($leadersqb as $gettots){
	$arrqb[] = $gettots[1]; 
}	
$qbsum = array_sum($arrqb);

foreach ($leadersrb as $gettots){
	$arrrb[] = $gettots[1]; 
}	
$rbsum = array_sum($arrrb);

foreach ($leaderswr as $gettots){
	$arrwr[] = $gettots[1]; 
}	
$wrsum = array_sum($arrwr);

foreach ($leaderspk as $gettots){
	$arrpk[] = $gettots[1]; 
}	
$pksum = array_sum($arrpk);

$alltotal = $qbsum + $rbsum + $wrsum + $pksum;
$totals_by_pos = array($qbsum, $rbsum, $wrsum, $pksum);


$position = array('QB','RB','WR','PK');

foreach ($totals_by_pos as $pospvq){
	$pvq[] = $alltotal / $pospvq;
}

$highpos = min($pvq);

$j = 0;
foreach ($pvq as $printpvq){
	$pvqval[$position[$j]] = $highpos / $printpvq; 
	$j++;
}
$QBval = $pvq[0];
$RBval = $pvq[1];
$WRval = $pvq[2];
$PKval = $pvq[3];



function applypvq($data, $val){
	foreach ($data as $applypvq){
		$points = $applypvq[1];
		$pvqsort = $points * $val;
		$pvqarray[] = array($applypvq[0],$applypvq[2],$applypvq[3],$applypvq[4], $pvqsort);
	}
	printr($pvqarray);
}

applypvq($leadersrb, $RBval);


get_footer(); ?>