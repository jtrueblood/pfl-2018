<?php

if (isset($value['traded'])){
								
	$counttrades = count($value['traded']);  
	for ($i = 0; $i < $counttrades; $i++) {  
		$tradedto = $teamids[$value['traded'][$i]['traded_to_team']];
		$tradedfrom = $teamids[$value['traded'][$i]['traded_from_team']];
		$when = $value['traded'][$i]['when'];
		$alongwith_players = $value['traded'][$i]['received_players'];
		$alongwith_picks = $value['traded'][$i]['received_picks'];
		$sent_players = $value['traded'][$i]['sent_players'];
		$sent_picks = $value['traded'][$i]['sent_picks'];
		$notes = $value['traded'][$i]['notes'];
		
		$a_picks = implode( ", ", $alongwith_picks);
		$s_picks = implode( ", ", $sent_picks);
		
		$alongwith_format = array();
		foreach ($alongwith_players as $playerf){		
			$trim = ltrim($playerf);
			if($playerf != ''){
				$alongwith_format[] = substr($players[$trim][0], 0, 1).'.'.$players[$trim][1];
			}
		}
		
		$sent_format = array();
		foreach ($sent_players as $playern){
			$trim = ltrim($playern);
			if($playern != ''){
				$sent_format[] = substr($players[$trim][0], 0, 1).'.'.$players[$trim][1];
			}	
		}
		
		//printr($sent_format, 0);
		
		?>
								
 <div class="timeline-entry">
	 
    <div class="timeline-label no-label">
        <p class="protected-by"><span class="text-bold">
            Traded to <?php echo $tradedto; ?></span> during the <?php echo $when; ?></p>
        <p class="protected-by"><span class="text-bold"><?php echo $value['traded'][$i]['traded_to_team'];?></span> &mdash; Get <span class="text-bold"><?php echo implode( ", ", $alongwith_format).' '.$a_picks; ?></span> </p>
		<p class="protected-by"><span class="text-bold"><?php echo $value['traded'][$i]['traded_from_team'];?></span> &mdash; Get <span class="text-bold"><?php echo implode( ", ", $sent_format).' '.$s_picks;?>  </span>
        </p>
         <p class="protected-by"><?php echo $notes; ?> </p>
    </div>
</div>

<?php } // end trades for loop
} //  end trades if   ?>