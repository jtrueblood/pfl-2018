<?php
/*
 * Template Name: Research Page
 * Description: Research for ETS team needs.
 */
 ?>


<?php get_header(); ?>

<!-- SET GLOBAL PLAYER VAR -->
<?php 
	
// $playerid = '2011SproRB';
/*
$teamid = $_GET['id'];
$year = date("Y");
$team_all_ids = get_teams();
$seasons = the_seasons();
$players = get_players_assoc();
$champs = get_champions();
$thisteam = get_team_results('wp_team_'.$teamid);
*/

function get_position_draft_value($round, $position){
	global $wpdb;
	$query = $wpdb->get_results("SELECT * FROM wp_drafts WHERE round = '$round' && pos = '$position'" );
	foreach ($query as $getval){
		$value[] = array(
			'pid' => $getval->playerid,
			'year' => $getval->year
		);
	}
	return $value;	
}


//$player = get_player_data('2004BreeQB');
//$player = get_raw_player_data_team('2004BreeQB', $teamid);
?>
<!--CONTENT CONTAINER-->
<div class="boxed">
	

<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">
	<!-- Championship banners -->

	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">
		<style>
			.draft-analysis-table {
				width: 100%;
				max-width: 700px;
				margin: 20px 0;
				border-collapse: collapse;
				background: #fff;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			}
			.draft-analysis-table th,
			.draft-analysis-table td {
				padding: 10px 12px;
				text-align: center;
				border: 1px solid #ddd;
			}
			.draft-analysis-table th {
				background: #f5f5f5;
				font-weight: bold;
				color: #333;
				font-size: 13px;
			}
			.draft-analysis-table th.round-col {
				background: #333;
				color: #fff;
			}
			.draft-analysis-table td {
				position: relative;
				font-size: 14px;
				color: #0066cc;
				font-weight: 600;
			}
			.draft-analysis-table tr:hover td {
				background: #f9f9f9;
			}
			.rank-badge {
				position: absolute;
				top: 2px;
				right: 2px;
				background: #f0f0f0;
				border-radius: 50%;
				width: 18px;
				height: 18px;
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 10px;
				font-weight: bold;
				color: #666;
			}
			.rank-badge.rank-1 {
				background: #4CAF50;
				color: #fff;
			}
			.rank-badge.rank-2 {
				background: #8BC34A;
				color: #fff;
			}
			.rank-badge.rank-3 {
				background: #FFC107;
				color: #fff;
			}
			.rank-badge.rank-4 {
				background: #FF9800;
				color: #fff;
			}
			.page-header {
				text-align: center;
				margin-bottom: 10px;
			}
			.page-header h1 {
				margin-bottom: 8px;
				font-size: 24px;
			}
			.page-header p {
				color: #666;
				font-size: 14px;
				margin: 0;
			}
			.round1-analysis {
				max-width: 1100px;
				margin: 40px 0;
				padding: 30px;
				background: #f9f9f9;
				border: 2px solid #ddd;
				border-radius: 8px;
			}
			.round1-analysis h2 {
				margin-top: 0;
				color: #333;
				border-bottom: 3px solid #4CAF50;
				padding-bottom: 10px;
				margin-bottom: 20px;
			}
			.round1-analysis h3 {
				color: #555;
				margin-top: 25px;
				margin-bottom: 12px;
			}
			.stats-table {
				width: 100%;
				max-width: 800px;
				margin: 20px 0;
				border-collapse: collapse;
				background: #fff;
			}
			.stats-table th,
			.stats-table td {
				padding: 12px;
				text-align: center;
				border: 1px solid #ddd;
			}
			.stats-table th {
				background: #333;
				color: #fff;
				font-weight: bold;
			}
			.stats-table tr:hover td {
				background: #f5f5f5;
			}
			.stats-table .best-value {
				background: #e8f5e9;
				font-weight: bold;
			}
			.conclusion-box {
				background: #e3f2fd;
				padding: 20px;
				border-left: 4px solid #2196F3;
				margin: 20px 0;
				border-radius: 4px;
			}
			.conclusion-box strong {
				color: #1976D2;
				font-size: 18px;
			}
			.round1-analysis ul {
				margin: 15px 0;
				padding-left: 20px;
			}
			.round1-analysis li {
				margin: 8px 0;
				line-height: 1.6;
			}
		</style>
		
		<div class="row">
			<div class="col-xs-24">
				<div class="page-header">
					<h1>Draft Position Analysis</h1>
					<p>Average season rank for players drafted by position and round. Lower numbers = better performance.</p>
				</div>
			</div>
		</div>
		
		<?php 
            $rounds = array('01','02','03','04','05','06','07');
            $positions = array('QB','RB','WR','PK');
			$position_names = array(
				'QB' => 'Quarterback',
				'RB' => 'Running Back',
				'WR' => 'Wide Receiver',
				'PK' => 'Kicker'
			);
			
			// First, collect all data
			$draft_data = array();
			foreach($rounds as $r) {
				foreach($positions as $p) {
					$byseason = array();
					$countrank = array();
					
					$first_pks = get_position_draft_value($r, $p);
					
					if ($first_pks) {
						foreach($first_pks as $pks){
							$byseason[$pks['pid']] = array(
								'data' => get_player_season_stats($pks['pid'], $pks['year']),
								'rank' => get_player_season_rank ($pks['pid'], $pks['year'])
							);
						}
						
						$i = 0;
						foreach ($byseason as $key => $value){
							if ($value['data']['points']):
								$countrank[] = $value['rank'];
								$i++;
							endif;
						}
						
						if ($i > 0) {
							$ct = array_sum($countrank);
							$draft_data[$r][$p] = number_format($ct / $i, 1);
						} else {
							$draft_data[$r][$p] = 'N/A';
						}
					} else {
						$draft_data[$r][$p] = 'N/A';
					}
				}
			}
			
			// Calculate rankings for each round
			$rankings = array();
			foreach($rounds as $r) {
				$round_ranks = array();
				foreach($positions as $p) {
					if ($draft_data[$r][$p] !== 'N/A') {
						$round_ranks[$p] = floatval(str_replace(',', '', $draft_data[$r][$p]));
					}
				}
				asort($round_ranks);
				$rank = 1;
				foreach($round_ranks as $pos => $val) {
					$rankings[$r][$pos] = $rank;
					$rank++;
				}
			}
		?>
		
		<div class="row">
			<div class="col-xs-24">
				<table class="draft-analysis-table">
					<thead>
						<tr>
							<th class="round-col">Round</th>
							<th>QB</th>
							<th>RB</th>
							<th>WR</th>
							<th>PK</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($rounds as $r): ?>
						<tr>
							<td style="background: #f5f5f5; font-weight: bold; color: #333;"><?php echo intval($r); ?></td>
							<?php foreach($positions as $p): ?>
							<td>
								<?php if (isset($rankings[$r][$p])): ?>
									<span class="rank-badge rank-<?php echo $rankings[$r][$p]; ?>"><?php echo $rankings[$r][$p]; ?></span>
								<?php endif; ?>
								<?php echo $draft_data[$r][$p]; ?>
							</td>
							<?php endforeach; ?>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		
		<!-- Round 1 Position Analysis -->
		<div class="row">
			<div class="col-xs-24 col-md-12">
				<div class="round1-analysis">
					<h2>Round 1 Position Value Analysis</h2>
					<p style="font-size: 15px; margin-bottom: 25px;">Historical analysis of all Round 1 draft picks to determine which position provides the best value.</p>
					
					<h3>Historical Performance by Position (Round 1 Only)</h3>
					<table class="stats-table">
						<thead>
							<tr>
								<th>Position</th>
								<th>Avg Career Points</th>
								<th>Avg Pick Value Score</th>
								<th>Protection Rate</th>
								<th>Sample Size</th>
							</tr>
						</thead>
						<tbody>
							<tr class="best-value">
								<td><strong>QB</strong></td>
								<td><strong>173.5</strong></td>
								<td><strong>596.8</strong></td>
								<td>36.3%</td>
								<td>91 picks</td>
							</tr>
							<tr>
								<td>RB</td>
								<td>114.8</td>
								<td>518.2</td>
								<td>26.5%</td>
								<td>136 picks</td>
							</tr>
							<tr>
								<td>WR</td>
								<td>103.0</td>
								<td>288.9</td>
								<td>27.7%</td>
								<td>94 picks</td>
							</tr>
						</tbody>
					</table>
					
					<div class="conclusion-box">
						<strong>Conclusion: YES, Quarterbacks Provide the Best Value in Round 1</strong>
						<p style="margin-top: 15px; margin-bottom: 0;">Quarterbacks drafted in Round 1 significantly outperform other positions across all key metrics.</p>
					</div>
					
					<h3>Why QBs Excel in Round 1</h3>
					<ul>
						<li><strong>51% more career points than RBs</strong> (173.5 vs 114.8)</li>
						<li><strong>68% more career points than WRs</strong> (173.5 vs 103.0)</li>
						<li><strong>More than double the Pick Value Score of WRs</strong> (596.8 vs 288.9)</li>
						<li><strong>Higher protection rate</strong> (36.3% vs ~27%), indicating teams keep QBs longer</li>
						<li><strong>Higher ceiling:</strong> Elite QBs like Patrick Mahomes (1,413 pts), Matt Ryan (1,329 pts), and Cam Newton (1,093 pts) provide franchise-level production</li>
						<li><strong>Longer careers and more consistent scoring</strong> compared to RBs and WRs</li>
					</ul>
					
					<h3>Top Historical Round 1 Picks by Position</h3>
					<div style="display: flex; gap: 20px; flex-wrap: wrap;">
						<div style="flex: 1; min-width: 250px;">
							<h4 style="color: #4CAF50; margin-bottom: 10px;">QB</h4>
							<ol style="margin: 0; padding-left: 20px; font-size: 14px;">
								<li>Patrick Mahomes (2018) - 1,413 pts</li>
								<li>Matt Ryan (2010) - 1,329 pts</li>
								<li>Cam Newton (2011) - 1,093 pts</li>
								<li>Steve Young (1992) - 1,050 pts</li>
								<li>Daunte Culpepper (2000) - 946 pts</li>
							</ol>
						</div>
						<div style="flex: 1; min-width: 250px;">
							<h4 style="color: #FF9800; margin-bottom: 10px;">RB</h4>
							<ol style="margin: 0; padding-left: 20px; font-size: 14px;">
								<li>Edgerrin James (1999) - 1,257 pts</li>
								<li>Barry Sanders (1991) - 1,173 pts</li>
								<li>Lesean McCoy (2010) - 1,037 pts</li>
								<li>Saquon Barkley (2018) - 884 pts</li>
								<li>Christian McCaffrey (2017) - 875 pts</li>
							</ol>
						</div>
						<div style="flex: 1; min-width: 250px;">
							<h4 style="color: #2196F3; margin-bottom: 10px;">WR</h4>
							<ol style="margin: 0; padding-left: 20px; font-size: 14px;">
								<li>Calvin Johnson (2008) - 722 pts</li>
								<li>Tyreek Hill (2019) - 702 pts</li>
								<li>Davante Adams (2018) - 671 pts</li>
								<li>Antonio Brown (2014) - 606 pts</li>
								<li>Tim Brown (1994) - 549 pts</li>
							</ol>
						</div>
					</div>
					
					<div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-top: 30px; border-radius: 4px;">
						<strong style="color: #856404;">Strategic Recommendation:</strong>
						<p style="margin: 10px 0 0 0; color: #856404;">If you have an early Round 1 pick and need a QB, <strong>take the QB</strong>. The historical data strongly supports this as the best value play, with QBs averaging 50-70% more career value than other positions drafted in Round 1.</p>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Rounds 2-7 Analysis -->
		<?php
		$rounds_data = [
			'2' => [
				'title' => 'Round 2',
				'best' => 'RB',
				'conclusion' => 'Running backs provide the best value in Round 2, averaging 69.8 career points with a 76.2 pick value score.',
				'data' => [
					'RB' => ['career' => 69.8, 'value' => 76.2, 'protect' => 20.0, 'picks' => 90],
					'PK' => ['career' => 59.4, 'value' => 33.5, 'protect' => 1.3, 'picks' => 79],
					'QB' => ['career' => 52.3, 'value' => 45.4, 'protect' => 12.3, 'picks' => 81],
					'WR' => ['career' => 49.6, 'value' => 46.9, 'protect' => 10.8, 'picks' => 102]
				],
				'note' => 'Marshall Faulk (1,485 pts) and Emmitt Smith (1,043 pts) demonstrate the elite potential of Round 2 RBs.'
			],
			'3' => [
				'title' => 'Round 3',
				'best' => 'PK',
				'conclusion' => 'Kickers emerge as the best value in Round 3, with consistent 61.5 career points. WRs are the best skill position at 43.9 points.',
				'data' => [
					'PK' => ['career' => 61.5, 'value' => 23.6, 'protect' => 2.9, 'picks' => 104],
					'QB' => ['career' => 51.8, 'value' => 23.3, 'protect' => 10.4, 'picks' => 67],
					'WR' => ['career' => 43.9, 'value' => 24.0, 'protect' => 12.8, 'picks' => 109],
					'RB' => ['career' => 29.7, 'value' => 18.7, 'protect' => 9.6, 'picks' => 73]
				],
				'note' => 'Josh Allen (1,104 pts) as a Round 3 QB shows elite upside, but WRs offer more consistent value.'
			],
			'4' => [
				'title' => 'Round 4',
				'best' => 'PK',
				'conclusion' => 'Kickers remain valuable (46.4 pts). Among skill positions, QBs (40.4 pts) and WRs (32.6 pts) lead RBs (17.7 pts).',
				'data' => [
					'PK' => ['career' => 46.4, 'value' => 13.3, 'protect' => 2.5, 'picks' => 80],
					'QB' => ['career' => 40.4, 'value' => 18.6, 'protect' => 5.6, 'picks' => 89],
					'WR' => ['career' => 32.6, 'value' => 15.5, 'protect' => 9.4, 'picks' => 96],
					'RB' => ['career' => 17.7, 'value' => 10.9, 'protect' => 3.4, 'picks' => 87]
				],
				'note' => 'RB value drops significantly. Prioritize QB/WR or secure your kicker.'
			],
			'5' => [
				'title' => 'Round 5',
				'best' => 'PK',
				'conclusion' => 'Kickers (29.1 pts) still provide value. QBs (24.8 pts) edge out WRs (22.4 pts) and RBs (14.7 pts).',
				'data' => [
					'PK' => ['career' => 29.1, 'value' => 7.1, 'protect' => 0.0, 'picks' => 87],
					'QB' => ['career' => 24.8, 'value' => 12.4, 'protect' => 5.9, 'picks' => 68],
					'WR' => ['career' => 22.4, 'value' => 9.3, 'protect' => 8.7, 'picks' => 103],
					'RB' => ['career' => 14.7, 'value' => 7.3, 'protect' => 5.2, 'picks' => 96]
				],
				'note' => 'Value drops across all positions. Target remaining needs and backup depth.'
			],
			'6' => [
				'title' => 'Round 6',
				'best' => 'PK',
				'conclusion' => 'Kickers (25.6 pts) lead. RBs (19.4 pts) slightly edge QBs (19.6 pts), but both offer depth value.',
				'data' => [
					'PK' => ['career' => 25.6, 'value' => 6.2, 'protect' => 2.9, 'picks' => 70],
					'QB' => ['career' => 19.6, 'value' => 5.4, 'protect' => 7.8, 'picks' => 51],
					'RB' => ['career' => 19.4, 'value' => 8.5, 'protect' => 4.1, 'picks' => 73],
					'WR' => ['career' => 10.1, 'value' => 4.3, 'protect' => 3.7, 'picks' => 82]
				],
				'note' => 'Late-round depth picks. WR value drops significantly.'
			],
			'7' => [
				'title' => 'Round 7',
				'best' => 'RB',
				'conclusion' => 'Surprisingly, RBs (24.0 pts, 26.7 value score) provide the best value in the final round.',
				'data' => [
					'RB' => ['career' => 24.0, 'value' => 26.7, 'protect' => 5.6, 'picks' => 54],
					'QB' => ['career' => 20.2, 'value' => 7.2, 'protect' => 7.0, 'picks' => 43],
					'PK' => ['career' => 13.3, 'value' => 2.9, 'protect' => 0.0, 'picks' => 59],
					'WR' => ['career' => 4.9, 'value' => 2.8, 'protect' => 2.7, 'picks' => 75]
				],
				'note' => 'Late-round lottery tickets. RBs offer the best upside for depth pieces.'
			]
		];
		
		// Display rounds 2-7
		foreach ($rounds_data as $round_num => $round_info):
		?>
		<div class="row">
			<div class="col-xs-24 col-md-12">
				<div class="round1-analysis">
					<h2><?php echo $round_info['title']; ?> Position Value Analysis</h2>
					
					<table class="stats-table">
						<thead>
							<tr>
								<th>Position</th>
								<th>Avg Career Points</th>
								<th>Avg Pick Value Score</th>
								<th>Protection Rate</th>
								<th>Sample Size</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($round_info['data'] as $pos => $stats): ?>
							<tr<?php echo ($pos === $round_info['best']) ? ' class="best-value"' : ''; ?>>
								<td><?php echo ($pos === $round_info['best']) ? "<strong>$pos</strong>" : $pos; ?></td>
								<td><?php echo ($pos === $round_info['best']) ? "<strong>{$stats['career']}</strong>" : $stats['career']; ?></td>
								<td><?php echo ($pos === $round_info['best']) ? "<strong>{$stats['value']}</strong>" : $stats['value']; ?></td>
								<td><?php echo $stats['protect']; ?>%</td>
								<td><?php echo $stats['picks']; ?> picks</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					
					<div class="conclusion-box">
						<strong>Conclusion:</strong>
						<p style="margin-top: 10px; margin-bottom: 0;"><?php echo $round_info['conclusion']; ?></p>
					</div>
					
					<p style="font-style: italic; color: #666; margin-top: 15px; margin-bottom: 0;"><strong>Note:</strong> <?php echo $round_info['note']; ?></p>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<!--===================================================-->
	<!--End page content-->


</div>
<!--===================================================-->
<!--END CONTENT CONTAINER-->
<?php include_once('main-nav.php'); ?>		
</div>

			
</div>



<?php get_footer(); ?>