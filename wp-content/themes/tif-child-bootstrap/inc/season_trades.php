<?php
// Display trades that occurred during the current season
$alltrades = get_trades();

if(!empty($alltrades)) {
	// Filter trades by year
	$season_trades = array_filter($alltrades, function($trade) use ($year) {
		return $trade['year'] == $year;
	});
	
	if(!empty($season_trades)) {
		// Sort by trade timing
		$order = array('Preseason' => 1, 'Draft' => 2, 'Postseason' => 3);
		usort($season_trades, function($a, $b) use ($order) {
			$a_order = isset($order[$a['when']]) ? $order[$a['when']] : 999;
			$b_order = isset($order[$b['when']]) ? $order[$b['when']] : 999;
			return $a_order - $b_order;
		});
		?>
		<div class="panel widget">
			<div class="panel-heading">
				<h3 class="panel-title">Season Trades</h3>
			</div>
			<div class="panel-body">
				<div style="display: flex; flex-wrap: wrap; gap: 10px;">
					<?php foreach($season_trades as $trade) { 
						$players1 = explode(',', $trade['players1']);
						$picks1 = explode(',', $trade['picks1']);
						$players2 = explode(',', $trade['players2']);
						$picks2 = explode(',', $trade['picks2']);
						?>
						<div style="flex: 0 0 calc(50% - 5px); padding: 10px; border: 1px solid #eee; background: #f9f9f9;">
							<h5 style="margin: 0 0 8px 0; font-size: 13px;"><?php echo $trade['when']; ?> Trade</h5>
							
							<div style="font-size: 12px;">
								<p style="margin: 0 0 5px 0;"><b><?php echo $teamlist[$trade['team1']]; ?></b> gets:</p>
								<?php 
								if(!empty($players2[0])) {
									foreach($players2 as $player) {
										if(!empty($player)) {
											$playername = get_player_name($player);
											echo $playername['first'].' '.$playername['last'].'<br>';
										}
									}
								}
								if(!empty($picks2[0])) {
									foreach($picks2 as $pick) {
										if(!empty($pick)) {
											echo format_draft_pick($pick);
										}
									}
								}
								?><p style="margin: 5px 0 0 0;"><b><?php echo $teamlist[$trade['team2']]; ?></b> gets:</p>
								<?php 
								if(!empty($players1[0])) {
									foreach($players1 as $player) {
										if(!empty($player)) {
											$playername = get_player_name($player);
											echo $playername['first'].' '.$playername['last'].'<br>';
										}
									}
								}
								if(!empty($picks1[0])) {
									foreach($picks1 as $pick) {
										if(!empty($pick)) {
											echo format_draft_pick($pick);
										}
									}
								}
								?></div>
							
							<?php if(!empty($trade['notes'])) { ?>
								<p style="margin: 10px 0 0 0; font-size: 9px; font-style: italic;">Note: <?php echo $trade['notes']; ?></p>
							<?php } ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
	}
}
?>
