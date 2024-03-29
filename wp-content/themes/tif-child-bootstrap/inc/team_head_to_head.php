<?php 
	
//printr($thisteam, 0);

foreach ($thisteam as $key => $item){
	$head[$item['versus']][$key] = $item;
}

$pep_vs = $head['PEP'];
$ets_vs = $head['ETS'];
$wrz_vs = $head['WRZ'];
$rbs_vs = $head['RBS'];
$bst_vs = $head['BST'];

$cmn_vs = $head['CMN'];
$bul_vs = $head['BUL'];
$snr_vs = $head['SNR'];
$tsg_vs = $head['TSG'];

$son_vs = $head['SON'];
$phr_vs = $head['PHR'];
$hat_vs = $head['HAT'];
$atk_vs = $head['ATK'];

$dst_vs = $head['DST'];
$max_vs = $head['MAX'];

//printr($pep_vs, 0);

function get_head_to($arr){
	foreach ($arr as $key => $val){
		$result = $val['result'];
		if ($result >= 0){
			$win = 1;
		} else {
			$win = 0;
		}
		$record[] = $win;
		$points[] = $val['points'];
		$points_vs[] = $val['versus_pts'];
	}
	$c = count($record);
	$w = array_sum($record);
	$l = $c - $w;
	$p = array_sum($points);
	$vp = array_sum($points_vs);
	$per = number_format($w / $c, 3);
	
	$teamarr = array(
		'games' => $c,
		'win' => $w,
		'loss' => $l,
		'per' => $per,
		'points_for' => $p,
		'points_against' => $vp,
		'diff' => $p - $vp
	);
	return $teamarr;
}

if(isset($ets_vs)){
	$ets = get_head_to($ets_vs);
	$matrix_build['ETS'] = $ets['loss'].'-'.$ets['win'];
}
if(isset($pep_vs)){
	$pep = get_head_to($pep_vs);
    $matrix_build['PEP'] = $pep['loss'].'-'.$pep['win'];
}
if(isset($wrz_vs)){
	$wrz = get_head_to($wrz_vs);
    $matrix_build['WRZ'] = $wrz['loss'].'-'.$wrz['win'];
}
if(isset($cmn_vs)){
	$cmn = get_head_to($cmn_vs);
    $matrix_build['CMN'] = $cmn['loss'].'-'.$cmn['win'];
}
if(isset($bul_vs)){
	$bul = get_head_to($bul_vs);
    $matrix_build['BUL'] = $bul['loss'].'-'.$bul['win'];
}
if(isset($snr_vs)){
	$snr = get_head_to($snr_vs);
    $matrix_build['SNR'] = $snr['loss'].'-'.$snr['win'];
}
if(isset($tsg_vs)){
	$tsg = get_head_to($tsg_vs);
    $matrix_build['TSG'] = $tsg['loss'].'-'.$tsg['win'];
}
if(isset($rbs_vs)){
	$rbs = get_head_to($rbs_vs);
    $matrix_build['RBS'] = $rbs['loss'].'-'.$rbs['win'];
}
if(isset($bst_vs)){
	$bst = get_head_to($bst_vs);
    $matrix_build['BST'] = $bst['loss'].'-'.$bst['win'];
}
if(isset($son_vs)){
	$son = get_head_to($son_vs);
    $matrix_build['SON'] = $son['loss'].'-'.$son['win'];
}
if(isset($phr_vs)){
	$phr = get_head_to($phr_vs);
    $matrix_build['PHR'] = $phr['loss'].'-'.$phr['win'];
}
if(isset($hat_vs)){
	$hat = get_head_to($hat_vs);
    $matrix_build['HAT'] = $hat['loss'].'-'.$hat['win'];
}
if(isset($atk_vs)){
	$atk = get_head_to($atk_vs);
    $matrix_build['ATK'] = $atk['loss'].'-'.$atk['win'];
}
if(isset($max_vs)){
	$max = get_head_to($max_vs);
    $matrix_build['MAX'] = $max['loss'].'-'.$max['win'];
}
if(isset($dst_vs)){
	$dst = get_head_to($dst_vs);
    $matrix_build['DST'] = $dst['loss'].'-'.$dst['win'];
}

//printr($matrix_build, 0);

// the function below and $matrix_build var above are for storing head to head records in a table to be used on the 'head to head matrix' page.
//  The head to head tables on the team pages do not require the $matrix_build or the insert_head_table() function.
$getjson = json_encode($matrix_build);

function insert_head_table($team, $input){
    global $wpdb;
    $wpdb->query("delete from wp_head_matrix where teamid = '$team'");
    $wpdb->insert(
        'wp_head_matrix',
        array(
           'teamid' => $team,
            'headdata' => $input
        ),
        array(
            '%s','%s'
        )
    );

}

$put = insert_head_table($teamid, $getjson);



?>	
<div class="panel">
	<div class="panel-heading">
    	<h3 class="panel-title">Head to Head Record <small>(regular season)</small></h3>
	</div>
	<div class="panel-body text-center probowl">
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="text-center">Vs</th>
						<th class="text-center">G</th>
						<th class="text-center">Rec</th>
						<th class="text-center">%</th>
						<th class="text-center">Pt Dif</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th class="text-center">ETS</th>
						<th class="text-center"><?php echo $ets['games'];?></th>
						<th class="text-center"><?php echo $ets['win'].' - '.$ets['loss'];?></th>
						<th class="text-center"><?php echo $ets['per'];?></th>
						<th class="text-center"><?php echo $ets['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">PEP</th>
						<th class="text-center"><?php echo $pep['games'];?></th>
						<th class="text-center"><?php echo $pep['win'].' - '.$pep['loss'];?></th>
						<th class="text-center"><?php echo $pep['per'];?></th>
						<th class="text-center"><?php echo $pep['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">WRZ</th>
						<th class="text-center"><?php echo $wrz['games'];?></th>
						<th class="text-center"><?php echo $wrz['win'].' - '.$wrz['loss'];?></th>
						<th class="text-center"><?php echo $wrz['per'];?></th>
						<th class="text-center"><?php echo $wrz['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">CMN</th>
						<th class="text-center"><?php echo $cmn['games'];?></th>
						<th class="text-center"><?php echo $cmn['win'].' - '.$cmn['loss'];?></th>
						<th class="text-center"><?php echo $cmn['per'];?></th>
						<th class="text-center"><?php echo $cmn['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">BUL</th>
						<th class="text-center"><?php echo $bul['games'];?></th>
						<th class="text-center"><?php echo $bul['win'].' - '.$bul['loss'];?></th>
						<th class="text-center"><?php echo $bul['per'];?></th>
						<th class="text-center"><?php echo $bul['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">SNR</th>
						<th class="text-center"><?php echo $snr['games'];?></th>
						<th class="text-center"><?php echo $snr['win'].' - '.$snr['loss'];?></th>
						<th class="text-center"><?php echo $snr['per'];?></th>
						<th class="text-center"><?php echo $snr['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">TSG</th>
						<th class="text-center"><?php echo $tsg['games'];?></th>
						<th class="text-center"><?php echo $tsg['win'].' - '.$tsg['loss'];?></th>
						<th class="text-center"><?php echo $tsg['per'];?></th>
						<th class="text-center"><?php echo $tsg['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">RBS</th>
						<th class="text-center"><?php echo $rbs['games'];?></th>
						<th class="text-center"><?php echo $rbs['win'].' - '.$rbs['loss'];?></th>
						<th class="text-center"><?php echo $rbs['per'];?></th>
						<th class="text-center"><?php echo $rbs['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">BST</th>
						<th class="text-center"><?php echo $bst['games'];?></th>
						<th class="text-center"><?php echo $bst['win'].' - '.$bst['loss'];?></th>
						<th class="text-center"><?php echo $bst['per'];?></th>
						<th class="text-center"><?php echo $bst['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">SON</th>
						<th class="text-center"><?php echo $son['games'];?></th>
						<th class="text-center"><?php echo $son['win'].' - '.$son['loss'];?></th>
						<th class="text-center"><?php echo $son['per'];?></th>
						<th class="text-center"><?php echo $son['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">PHR</th>
						<th class="text-center"><?php echo $phr['games'];?></th>
						<th class="text-center"><?php echo $phr['win'].' - '.$phr['loss'];?></th>
						<th class="text-center"><?php echo $phr['per'];?></th>
						<th class="text-center"><?php echo $phr['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">HAT</th>
						<th class="text-center"><?php echo $hat['games'];?></th>
						<th class="text-center"><?php echo $hat['win'].' - '.$hat['loss'];?></th>
						<th class="text-center"><?php echo $hat['per'];?></th>
						<th class="text-center"><?php echo $hat['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">ATK</th>
						<th class="text-center"><?php echo $atk['games'];?></th>
						<th class="text-center"><?php echo $atk['win'].' - '.$atk['loss'];?></th>
						<th class="text-center"><?php echo $atk['per'];?></th>
						<th class="text-center"><?php echo $atk['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">MAX</th>
						<th class="text-center"><?php echo $max['games'];?></th>
						<th class="text-center"><?php echo $max['win'].' - '.$max['loss'];?></th>
						<th class="text-center"><?php echo $max['per'];?></th>
						<th class="text-center"><?php echo $max['diff'];?></th>
					</tr>
					<tr>
						<th class="text-center">DST</th>
						<th class="text-center"><?php echo $dst['games'];?></th>
						<th class="text-center"><?php echo $dst['win'].' - '.$dst['loss'];?></th>
						<th class="text-center"><?php echo $dst['per'];?></th>
						<th class="text-center"><?php echo $dst['diff'];?></th>
					</tr>
					
					
				</tbody>
			</table>
		</div>
		
	</div>
</div>
