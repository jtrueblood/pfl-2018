<?php 
	$yearleaders = get_season_leaders($year_sel);
	foreach ($yearleaders as $leader){
		foreach ($allWeeksZero as $weeks){
			$yearleaderids[$year_sel.$weeks][$leader['playerid']] = get_one_player_week ($leader['playerid'], $year_sel.$weeks);
		}
		
	}
	
	foreach ($yearleaderids as $k=>$subArray) {
	  if ($k <= $weekvar){	
		  foreach ($subArray as $id=>$value) {
			if($value > 0):  
		    	$sumLeaderYear[$id]+=$value;
		    endif;
		  }
	  }
	}
	
	foreach ($sumLeaderYear as $key => $value){
		$pos = substr($key, -2);
		$leadersyearposition[$pos][$key] = $value;
		
	}
	

?>


<div class="col-xs-24 col-md-6">
	
	<?php
		$qb_lead_to_week = $leadersyearposition['QB']; 	
		arsort($qb_lead_to_week);
		//printr($qb_lead_to_week, 0); 
		
		$labels = array('Player', 'Team', 'Pts');	
		tablehead('Quarterbacks', $labels);	
		
		$a = 1;
		foreach ($qb_lead_to_week as $key => $value){
			$n = get_player_name($key);
			$get_t = get_player_teams_season($key);
			$t = $get_t[$year_sel];
			if(is_array($t)){
				$s = implode(' / ', $t);
			}
			if ($a <= 10){
				$qb_lead .='<tr><td>'.$a.'. <span class="">'.$n['first'].' '.$n['last'].'</span></td>';
				$qb_lead .='<td class="min-width">'.$s.'</td>';
				$qb_lead .='<td class="min-width text-right">'.$value.'</td></tr>';
			}
			$a++;	
		}
		
		echo $qb_lead;

		
		tablefoot('Top 10 QBs');	
	?>
			
</div>

<div class="col-xs-24 col-md-6">
	
	<?php
		$rb_lead_to_week = $leadersyearposition['RB']; 	
		arsort($rb_lead_to_week);
		//printr($qb_lead_to_week, 0); 
		
		$labels = array('Player', 'Team', 'Pts');	
		tablehead('Runningbacks', $labels);	
		
		$a = 1;
		foreach ($rb_lead_to_week as $key => $value){
			$n = get_player_name($key);
			$get_t = get_player_teams_season($key);
			$t = $get_t[$year_sel];
			if(is_array($t)){
				$s = implode(' / ', $t);
			}
			if ($a <= 10){
				$rb_lead .='<tr><td>'.$a.'. <span class="">'.$n['first'].' '.$n['last'].'</span></td>';
				$rb_lead .='<td class="min-width">'.$s.'</td>';
				$rb_lead .='<td class="min-width text-right">'.$value.'</td></tr>';
			}
			$a++;	
		}
		
		echo $rb_lead;

		
		tablefoot('Top 10 RBs');	
	?>
			
</div>

<div class="col-xs-24 col-md-6">
	
	<?php
		$wr_lead_to_week = $leadersyearposition['WR']; 	
		arsort($wr_lead_to_week);
		//printr($qb_lead_to_week, 0); 
		
		$labels = array('Player', 'Team', 'Pts');	
		tablehead('Receivers', $labels);	
		
		$a = 1;
		foreach ($wr_lead_to_week as $key => $value){
			$n = get_player_name($key);
			$get_t = get_player_teams_season($key);
			$t = $get_t[$year_sel];
			if(is_array($t)){
				$s = implode(' / ', $t);
			}
			if ($a <= 10){
				$wr_lead .='<tr><td>'.$a.'. <span class="">'.$n['first'].' '.$n['last'].'</span></td>';
				$wr_lead .='<td class="min-width">'.$s.'</td>';
				$wr_lead .='<td class="min-width text-right">'.$value.'</td></tr>';
			}
			$a++;	
		}
		
		echo $wr_lead;

		
		tablefoot('Top 10 WRs');	
	?>
			
</div>

<div class="col-xs-24 col-md-6">
		
	<?php
		$pk_lead_to_week = $leadersyearposition['PK']; 	
		arsort($pk_lead_to_week);
		
		$labels = array('Player', 'Team', 'Pts');	
		tablehead('Kickers', $labels);	
		
		$a = 1;
		foreach ($pk_lead_to_week as $key => $value){
			$n = get_player_name($key);
			$get_t = get_player_teams_season($key);
			$t = $get_t[$year_sel];
			if(is_array($t)){
				$s = implode(' / ', $t);
			}
			if ($a <= 10){
				$pk_lead .='<tr><td>'.$a.'. <span class="">'.$n['first'].' '.$n['last'].'</span></td>';
				$pk_lead .='<td class="min-width">'.$s.'</td>';
				$pk_lead .='<td class="min-width text-right">'.$value.'</td></tr>';
			}
			$a++;	
		}
		
		echo $pk_lead;

		
		tablefoot('Top 10 PKs');	
	?>
			
</div>
