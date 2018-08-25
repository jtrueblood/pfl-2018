<?php $probowlteam = probowl_teams_player($teamid);
//printr($probowlteam, 0);			
//printr($seasons, 0);

if(isset($probowlteam)){ 

foreach($probowlteam as $key => $item){
   $arr_probowl[$item['year']][$key] = $item;
}
ksort($arr_probowl, SORT_NUMERIC);

?>	
<div class="panel">
	<div class="panel-heading">
    	<h3 class="panel-title">Pro Bowl Selections</h3>
	</div>
	<div class="panel-body text-center probowl">
		
		<?php 
			foreach ($arr_probowl as $year => $value){

				echo '<div class="row pro-rows">';
					echo '<div class="col-xs-24 col-sm-6">';
						echo '<h5>'.$year.'</h5>';
					echo '</div>';
					echo '<div class="col-xs-24 col-sm-16">';
						foreach ($value as $key => $val){
							$name = get_player_name($val['playerid']);
							echo '<p>'.$name['first'].' '.$name['last'].' - '.$name['pos'].'</p>';
						}
					echo '</div>';
				echo '</div>';
			}
		?>
	</div>
</div>
<?php } ?>