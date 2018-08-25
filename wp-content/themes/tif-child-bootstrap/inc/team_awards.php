<?php  
//printr($arr_taward, 0);	
$mvp = 	$arr_taward['Most Valuable Player'];
$rook = $arr_taward['Rookie of the Year'];	
$pbmvp = $arr_taward['Posse Bowl MVP'];
$pro = $arr_taward['Pro Bowl MVP'];
$ooty = $arr_taward['Owner of the Year'];

?>
<div class="panel">
	<div class="panel-heading">
    	<h3 class="panel-title">PFL Awards</h3>
	</div>
	<div class="panel-body text-center">
		
		<?php 
		if(isset($mvp)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Most Valuable Player</h4>';
		
					foreach ($mvp as $key => $val){
						echo '<h5><span class="text-bold">'.$val['year'].'</span> - '.$val['first'].' '.$val['last'].'</h5>';				
					}
					
				echo '</div>';
			echo '</div>';		
		}
		
		if(isset($rook)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Rookie of the Year</h4>';
		
					foreach ($rook as $key => $val){
						echo '<h5><span class="text-bold">'.$val['year'].'</span> - '.$val['first'].' '.$val['last'].'</h5>';				
					}
					
				echo '</div>';
			echo '</div>';		
		}
		
		if(isset($pbmvp)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Posse Bowl MVP</h4>';
		
					foreach ($pbmvp as $key => $val){
						echo '<h5><span class="text-bold">'.$val['year'].'</span> - '.$val['first'].' '.$val['last'].'</h5>';				
					}
					
				echo '</div>';
			echo '</div>';		
		}
		
		if(isset($pro)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Pro Bowl MVP</h4>';
		
					foreach ($pro as $key => $val){
						echo '<h5><span class="text-bold">'.$val['year'].'</span> - '.$val['first'].' '.$val['last'].'</h5>';				
					}
					
				echo '</div>';
			echo '</div>';		
		}
		
		if(isset($ooty)){
			echo '<div class="row awards-team">';
				echo '<div class="col-xs-24">';
					echo '<h4>Owner of the Year</h4>';
		
					foreach ($ooty as $key => $val){
						echo '<h5><span class="text-bold">'.$val['year'].'</span> - OOTY</h5>';				
					}
					
				echo '</div>';
			echo '</div>';		
		}
		
		

		?>
		
	</div>
</div>

