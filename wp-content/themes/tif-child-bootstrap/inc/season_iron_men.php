<div class="panel">
	<div class="panel-heading">
		<h3 class="panel-title">PFL Iron Men</h3>
	</div>
	<div class="panel-body">
		<?php
		$ironmen = get_iron_men($year);
		
		if(!empty($ironmen)) {
			// Sort by position then by name
			usort($ironmen, function($a, $b) {
				if($a['position'] == $b['position']) {
					return strcmp($a['last'], $b['last']);
				}
				$posorder = array('QB' => 0, 'RB' => 1, 'WR' => 2, 'PK' => 3);
				$apos = isset($posorder[$a['position']]) ? $posorder[$a['position']] : 4;
				$bpos = isset($posorder[$b['position']]) ? $posorder[$b['position']] : 4;
				return $apos - $bpos;
			});
			
			echo '<table class="table table-striped table-hover">';
			echo '<thead><tr><th>Player</th><th>Position</th><th>Team</th><th>Games</th></tr></thead>';
			echo '<tbody>';
			
			foreach($ironmen as $player) {
				echo '<tr>';
				echo '<td>'.$player['first'].' '.$player['last'].'</td>';
				echo '<td>'.$player['position'].'</td>';
				echo '<td>'.$teamlist[$player['team']].'</td>';
				echo '<td>'.$player['games'].'</td>';
				echo '</tr>';
			}
			
			echo '</tbody>';
			echo '</table>';
		} else {
			echo '<p class="text-center text-muted">No Iron Men this season</p>';
		}
		?>
	</div>
</div>
