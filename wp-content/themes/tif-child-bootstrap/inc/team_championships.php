<?php 

	
	foreach($champs as $key => $item){
		$arr_champs[$item['winner']][$key] = $item;
		}
	ksort($arr_champs, SORT_NUMERIC);
	
	//printr($arr_champs, 0);
	
	$teamchamps = $arr_champs[$teamid];
	
	if(isset($teamchamps)){ 	
		
		
	?>
<div class="champion-team hidden-xs hidden-sm">


		<?php 
		foreach ($teamchamps as $key => $val){

		
				echo '<div class="winner-winner">';
					
					echo '<h4>'.$val['year'].'</h4>';
					echo '<p class="text-thin">PFL<br>Champs</p>';
				
				echo '</div>';


		}
		//printr($arr_champs[$teamid], 0);
		?>


</div>
<?php } ?>