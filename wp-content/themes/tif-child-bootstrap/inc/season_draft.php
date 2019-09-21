<div class="panel">
	<div class="panel-body">

		<div id="seasondraft">
		<?php
			
			$draft = get_drafts_by_year($year); 
			//printr($draft, 0);	
			
			$labels = array('', 'Pick', 'Team', 'Selection', 'Orig' );	
			tablehead($year.' Draft', $labels);	
			
			foreach ($draft as $key => $value){
				
				if($setround != $value['round']){	
					$draftprint .='<tr><td class="min-width text-center">'.$value['round'].'</td>';
				} else  {
					$draftprint .='<tr><td class="min-width text-center"></td>';
				}
				$draftprint .='<td class="min-width text-center">'.ltrim($value['pick'], 0).'</td>';
				$draftprint .='<td class="min-width">'.$teamlist[$value['acteam']].'</td>';
				$draftprint .='<td>'.$value['playerfirst'].' '.$value['playerlast'].'</td>';
				if($value['orteam'] != $value['acteam']){
					$draftprint .='<td class="min-width text-left">'.$value['orteam'].'</td></tr>';
				} else {
					$draftprint .='<td class="min-width"></td></tr>';
				}
				$setround = $value['round'];
			}
			echo '<tr></tr>';
			
			echo $draftprint;
			
			tablefoot('<i class="Footer Label');		
			?>
		</div>
	
	</div>
</div>