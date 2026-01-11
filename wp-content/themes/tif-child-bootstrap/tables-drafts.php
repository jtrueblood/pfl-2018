<?php
/*
 * Template Name: Tables Drafts Page
 * Description: Page for displaying draft-related tables
 */
 ?>

<?php get_header(); ?>

<!--CONTENT CONTAINER-->
<div class="boxed">

<!--CONTENT CONTAINER-->
<!--===================================================-->
<div id="content-container">


	<!--Page content-->
	<!--===================================================-->
	<div id="page-content">
		
		<div class="row">
			
			<div class="col-xs-24">
				<h4>Draft Data & Tables</h4>
				<p><a href="/tables">View All Tables</a> | <a href="/tables-players">Player Tables</a> | <a href="/tables-teams">Team Tables</a> | <a href="/tables-postseason">Postseason Tables</a> | <a href="/tables-other">Other Tables</a></p>
				<hr>
			</div>
			
			<!-- Best Pick by Season -->
			<div class="col-xs-24 col-sm-12 col-md-8">
				<?php
				global $wpdb;
				
				// Get best pick from wp_drafts_pick_value table (pre-calculated)
				// This table already has the value score calculated
				$best_picks_query = $wpdb->get_results("
					SELECT 
						pv.year as season,
						pv.playerid,
						pv.playername,
						pv.team,
						pv.round,
						pv.picknum as pick,
						pv.valuescore,
						al.points
					FROM wp_drafts_pick_value pv
					LEFT JOIN wp_allleaders al 
						ON pv.playerid = al.pid
					WHERE pv.valuescore > 0
					ORDER BY pv.year DESC, pv.valuescore DESC
				", ARRAY_A);
				
				// Group by season and get the best pick value score for each
				$best_by_season = array();
				foreach ($best_picks_query as $pick) {
					$season = $pick['season'];
					if (!isset($best_by_season[$season])) {
						$best_by_season[$season] = $pick;
					}
				}
				
				// Sort by season ascending (oldest first)
				ksort($best_by_season);
				
				$labels = array('Year', 'Player', 'Team', 'Rd', 'Pick', 'Value');
				tablehead('Best Pick by Season', $labels);
				
				$printbestpick = '';
				foreach ($best_by_season as $season => $pick) {
					$player_name = $pick['playername'];
					$round = ltrim($pick['round'], '0');
					$picknum = ltrim($pick['pick'], '0');
					$value_score = number_format($pick['valuescore'], 1);
					
					$printbestpick .= '<tr>';
					$printbestpick .= '<td>' . $season . '</td>';
					$printbestpick .= '<td>' . $player_name . '</td>';
					$printbestpick .= '<td>' . $pick['team'] . '</td>';
					$printbestpick .= '<td class="text-center">' . $round . '</td>';
					$printbestpick .= '<td class="text-center">' . $picknum . '</td>';
					$printbestpick .= '<td class="text-right">' . $value_score . '</td>';
					$printbestpick .= '</tr>';
				}
				
				echo $printbestpick;
				
				tablefoot('Best value pick = highest (career points / draft pick value) ratio');
				?>
			</div>
			
			<!-- Top 25 Draft Values -->
			<div class="col-xs-24 col-sm-12 col-md-8">
				<?php
				global $wpdb;
				
				// Get top 25 players by draft value score
				$top_values_query = $wpdb->get_results("
					SELECT 
						pv.year,
						pv.playerid,
						pv.playername,
						pv.team,
						pv.round,
						pv.picknum as pick,
						pv.valuescore,
						al.points
					FROM wp_drafts_pick_value pv
					LEFT JOIN wp_allleaders al 
						ON pv.playerid = al.pid
					WHERE pv.valuescore > 0
					ORDER BY pv.valuescore DESC
					LIMIT 25
				", ARRAY_A);
				
				$labels = array('Rk', 'Player', 'Year', 'Team', 'Rd', 'Pick', 'Value');
				tablehead('Top 25 Draft Values', $labels);
				
				$printtopvalues = '';
				$rank = 1;
				foreach ($top_values_query as $pick) {
					$player_name = $pick['playername'];
					$round = ltrim($pick['round'], '0');
					$picknum = ltrim($pick['pick'], '0');
					$value_score = number_format($pick['valuescore'], 1);
					
					$printtopvalues .= '<tr>';
					$printtopvalues .= '<td class="text-center">' . $rank . '</td>';
					$printtopvalues .= '<td>' . $player_name . '</td>';
					$printtopvalues .= '<td class="text-center">' . $pick['year'] . '</td>';
					$printtopvalues .= '<td>' . $pick['team'] . '</td>';
					$printtopvalues .= '<td class="text-center">' . $round . '</td>';
					$printtopvalues .= '<td class="text-center">' . $picknum . '</td>';
					$printtopvalues .= '<td class="text-right">' . $value_score . '</td>';
					$printtopvalues .= '</tr>';
					
					$rank++;
				}
				
				echo $printtopvalues;
				
				tablefoot('All-time best value picks across all drafts');
				?>
			</div>
			
			<!-- Late Round Gems (Round 5+) -->
			<div class="col-xs-24 col-sm-12 col-md-8">
				<?php
				global $wpdb;
				
				// Get top 25 value scores from round 5 or later
				$late_round_query = $wpdb->get_results("
					SELECT 
						pv.year,
						pv.playerid,
						pv.playername,
						pv.team,
						pv.round,
						pv.picknum as pick,
						pv.valuescore,
						al.points
					FROM wp_drafts_pick_value pv
					LEFT JOIN wp_allleaders al 
						ON pv.playerid = al.pid
					WHERE pv.valuescore > 0
						AND CAST(pv.round AS UNSIGNED) >= 5
					ORDER BY pv.valuescore DESC
					LIMIT 25
				", ARRAY_A);
				
				$labels = array('Rk', 'Player', 'Year', 'Team', 'Rd', 'Pick', 'Value');
				tablehead('Late Round Gems (Rd 5+)', $labels);
				
				$printlateround = '';
				$rank = 1;
				foreach ($late_round_query as $pick) {
					$player_name = $pick['playername'];
					$round = ltrim($pick['round'], '0');
					$picknum = ltrim($pick['pick'], '0');
					$value_score = number_format($pick['valuescore'], 1);
					
					$printlateround .= '<tr>';
					$printlateround .= '<td class="text-center">' . $rank . '</td>';
					$printlateround .= '<td>' . $player_name . '</td>';
					$printlateround .= '<td class="text-center">' . $pick['year'] . '</td>';
					$printlateround .= '<td>' . $pick['team'] . '</td>';
					$printlateround .= '<td class="text-center">' . $round . '</td>';
					$printlateround .= '<td class="text-center">' . $picknum . '</td>';
					$printlateround .= '<td class="text-right">' . $value_score . '</td>';
					$printlateround .= '</tr>';
					
					$rank++;
				}
				
				echo $printlateround;
				
				tablefoot('Best value picks from round 5 or later');
				?>
			</div>
			
			<!-- First Round Busts -->
			<div class="col-xs-24 col-sm-12 col-md-8">
				<?php
				global $wpdb;
				
				// Get picks 1-5 with zero value score from round 1 only
				$zero_values_query = $wpdb->get_results("
					SELECT 
						pv.year,
						pv.playerid,
						pv.playername,
						pv.team,
						pv.round,
						pv.picknum as pick,
						pv.valuescore
					FROM wp_drafts_pick_value pv
					WHERE pv.valuescore = 0
						AND pv.round = '01'
						AND pv.picknum BETWEEN '01' AND '05'
					ORDER BY pv.picknum ASC, pv.year DESC
				", ARRAY_A);
				
				$labels = array('Year', 'Player', 'Team', 'Pick');
				tablehead('First Round Busts (' . count($zero_values_query) . ')', $labels);
				
				$printzerovalues = '';
				foreach ($zero_values_query as $pick) {
					$player_name = $pick['playername'];
					$picknum = ltrim($pick['pick'], '0');
					
					$printzerovalues .= '<tr>';
					$printzerovalues .= '<td>' . $pick['year'] . '</td>';
					$printzerovalues .= '<td>' . $player_name . '</td>';
					$printzerovalues .= '<td>' . $pick['team'] . '</td>';
					$printzerovalues .= '<td class="text-center">' . $picknum . '</td>';
					$printzerovalues .= '</tr>';
				}
				
				echo $printzerovalues;
				
				tablefoot('Players who never scored a point for the team that selected them with these picks');
				?>
			</div>
			
			<!-- Teams by Average Draft Pick Value -->
			<div class="col-xs-24 col-sm-12 col-md-8">
				<?php
				global $wpdb;
				
				// Get teams ranked by average draft pick value
				$teams_value_query = $wpdb->get_results("
					SELECT 
						pv.team,
						t.team as team_name,
						COUNT(*) as num_picks,
						ROUND(AVG(pv.valuescore), 1) as avg_value,
						ROUND(SUM(pv.valuescore), 1) as total_value
					FROM wp_drafts_pick_value pv
					LEFT JOIN wp_teams t ON pv.team = t.team_int
					GROUP BY pv.team, t.team
					ORDER BY avg_value DESC
				", ARRAY_A);
				
				$labels = array('Rk', 'Team', 'Picks', 'Avg Value', 'Total');
				tablehead('Teams by Average Draft Value', $labels);
				
				$printteams = '';
				$rank = 1;
				foreach ($teams_value_query as $row) {
					$team_name = $row['team_name'] ? $row['team_name'] : $row['team'];
					$num_picks = $row['num_picks'];
					$avg_value = number_format($row['avg_value'], 1);
					$total_value = number_format($row['total_value'], 1);
					
					$printteams .= '<tr>';
					$printteams .= '<td class="text-center">' . $rank . '</td>';
					$printteams .= '<td>' . $team_name . '</td>';
					$printteams .= '<td class="text-center">' . $num_picks . '</td>';
					$printteams .= '<td class="text-right"><strong>' . $avg_value . '</strong></td>';
					$printteams .= '<td class="text-right">' . $total_value . '</td>';
					$printteams .= '</tr>';
					
					$rank++;
				}
				
				echo $printteams;
				
			tablefoot('Teams ranked by average value score of their draft picks');
				?>
			</div>

	</div>
	
	<div class="row">
		<div class="col-xs-24">
			<hr>
		</div>
		
		<!-- Ideal Draft - Best Pick at Each Position -->
		<div class="col-xs-24 col-sm-12 col-md-8">
			<?php
			global $wpdb;
			
			// Get all picks for each overall pick number (1-70), ordered by value
			// We'll process in PHP to handle duplicate player logic
			$all_picks_query = $wpdb->get_results("
				SELECT 
					pv.picknum as overall_pick,
					pv.year,
					pv.playername,
					pv.playerid,
					d.pos as position,
					pv.team,
					pv.valuescore
				FROM wp_drafts_pick_value pv
				LEFT JOIN wp_drafts d ON pv.playerid = d.playerid AND pv.year = d.year
				WHERE CAST(pv.picknum AS UNSIGNED) <= 70
				ORDER BY CAST(pv.picknum AS UNSIGNED), pv.valuescore DESC
			", ARRAY_A);
			
			// Group picks by pick number
			$picks_by_number = array();
			foreach ($all_picks_query as $pick) {
				$picknum = $pick['overall_pick'];
				if (!isset($picks_by_number[$picknum])) {
					$picks_by_number[$picknum] = array();
				}
				$picks_by_number[$picknum][] = $pick;
			}
			
			// Build ideal draft, skipping duplicate players
			$drafted_players = array();
			$ideal_draft_query = array();
			
			for ($i = 1; $i <= 70; $i++) {
				if (!isset($picks_by_number[$i])) continue;
				
				$skipped = false;
				foreach ($picks_by_number[$i] as $pick) {
					$player_key = $pick['playerid'];
					if (!in_array($player_key, $drafted_players)) {
						$pick['skipped'] = $skipped;
						$ideal_draft_query[] = $pick;
						$drafted_players[] = $player_key;
						break;
					} else {
						$skipped = true;
					}
				}
			}
			
			$labels = array('Pick', 'Year', 'Player', 'Pos', 'Team', 'Value');
			tablehead('The Ideal Draft (Best Pick Ever at Each Position)', $labels);
			
			$printidealdraft = '';
			foreach ($ideal_draft_query as $pick) {
				$overall = $pick['overall_pick'];
				$year = $pick['year'];
				$player = $pick['playername'];
				$position = $pick['position'];
				$team = $pick['team'];
				$value = number_format($pick['valuescore'], 1);
				$skipped_marker = $pick['skipped'] ? ' *' : '';
				
				$printidealdraft .= '<tr>';
				$printidealdraft .= '<td class="text-center">' . $overall . $skipped_marker . '</td>';
				$printidealdraft .= '<td class="text-center">' . $year . '</td>';
				$printidealdraft .= '<td>' . $player . '</td>';
				$printidealdraft .= '<td class="text-center">' . $position . '</td>';
				$printidealdraft .= '<td class="text-center">' . $team . '</td>';
				$printidealdraft .= '<td class="text-right">' . $value . '</td>';
				$printidealdraft .= '</tr>';
			}
			
			echo $printidealdraft;
			
			tablefoot('Shows the best player ever selected at each pick position across all PFL drafts. * = Top player already drafted, showing next best');
			?>
		</div>

	</div>
	
	<div class="row">
		<div class="col-xs-24">
			<hr>
			<h4>Draft Pick Protection Rates</h4>
		</div>
		
		<!-- Overall Protection Rate -->
		<div class="col-xs-24 col-sm-8">
			<?php
			global $wpdb;
			
			// Calculate overall protection rate
			$overall_query = $wpdb->get_row("
				SELECT 
					COUNT(DISTINCT d.playerid, d.year) as total_picks,
					COUNT(DISTINCT p.playerid) as protected_picks
				FROM wp_drafts d
				LEFT JOIN wp_protections p
					ON d.playerid = p.playerid 
					AND d.team = p.team
					AND p.year = d.year + 1
				WHERE d.playerid IS NOT NULL 
					AND d.playerid != ''
			", ARRAY_A);
			
			$total = $overall_query['total_picks'];
			$protected = $overall_query['protected_picks'];
			$percentage = $total > 0 ? round(($protected / $total) * 100, 1) : 0;
			?>
			<div class="panel">
				<div class="panel-heading">
					<h3 class="panel-title">Overall Protection Rate</h3>
				</div>
				<div class="panel-body text-center">
					<div style="font-size: 48px; font-weight: bold; color: #3498db;"><?php echo number_format($percentage, 1); ?>%</div>
					<p class="text-muted"><?php echo number_format($protected); ?> of <?php echo number_format($total); ?> picks protected</p>
					<p class="text-small">Percentage of draft picks protected by their team the following season</p>
				</div>
			</div>
		</div>
		
		<!-- Protection Rate by Round -->
		<div class="col-xs-24 col-sm-8">
			<?php
			global $wpdb;
			
			// Calculate protection rate by round
			$by_round_query = $wpdb->get_results("
				SELECT 
					d.round,
					COUNT(DISTINCT d.playerid, d.year) as total_picks,
					COUNT(DISTINCT p.playerid) as protected_picks
				FROM wp_drafts d
				LEFT JOIN wp_protections p
					ON d.playerid = p.playerid 
					AND d.team = p.team
					AND p.year = d.year + 1
				WHERE d.playerid IS NOT NULL 
					AND d.playerid != ''
				GROUP BY d.round
				ORDER BY CAST(d.round AS UNSIGNED)
			", ARRAY_A);
			
			$labels = array('Round', 'Protected', 'Total', 'Rate');
			tablehead('Protection Rate by Round', $labels);
			
			$printbyround = '';
			foreach ($by_round_query as $row) {
				$round = ltrim($row['round'], '0');
				$total = $row['total_picks'];
				$protected = $row['protected_picks'];
				$rate = $total > 0 ? round(($protected / $total) * 100, 1) : 0;
				
				$printbyround .= '<tr>';
				$printbyround .= '<td class="text-center">' . $round . '</td>';
				$printbyround .= '<td class="text-center">' . $protected . '</td>';
				$printbyround .= '<td class="text-center">' . $total . '</td>';
				$printbyround .= '<td class="text-center"><strong>' . $rate . '%</strong></td>';
				$printbyround .= '</tr>';
			}
			
			echo $printbyround;
			
			tablefoot('How often picks from each round are protected the following year');
			?>
		</div>
		
		<!-- Protection Rate by Year -->
		<div class="col-xs-24 col-sm-8">
			<?php
			global $wpdb;
			
			// Calculate protection rate by year
			$by_year_query = $wpdb->get_results("
				SELECT 
					d.year,
					COUNT(DISTINCT d.playerid) as total_picks,
					COUNT(DISTINCT p.playerid) as protected_picks
				FROM wp_drafts d
				LEFT JOIN wp_protections p
					ON d.playerid = p.playerid 
					AND d.team = p.team
					AND p.year = d.year + 1
				WHERE d.playerid IS NOT NULL 
					AND d.playerid != ''
				GROUP BY d.year
				ORDER BY d.year ASC
			", ARRAY_A);
			
			$labels = array('Year', 'Protected', 'Total', 'Rate');
			tablehead('Protection Rate by Year', $labels);
			
			$printbyyear = '';
			foreach ($by_year_query as $row) {
				$year = $row['year'];
				$total = $row['total_picks'];
				$protected = $row['protected_picks'];
				$rate = $total > 0 ? round(($protected / $total) * 100, 1) : 0;
				
				$printbyyear .= '<tr>';
				$printbyyear .= '<td>' . $year . '</td>';
				$printbyyear .= '<td class="text-center">' . $protected . '</td>';
				$printbyyear .= '<td class="text-center">' . $total . '</td>';
				$printbyyear .= '<td class="text-center"><strong>' . $rate . '%</strong></td>';
				$printbyyear .= '</tr>';
			}
			
			echo $printbyyear;
			
			tablefoot('Annual protection rates for draft picks');
			?>
		</div>

	</div>
	<!--===================================================-->
	<!--End page content-->
</div>
<!--===================================================-->
<!--END CONTENT CONTAINER-->
		
</div>

		
<?php include_once('main-nav.php'); ?>
		
</div>
</div>


<?php get_footer(); ?>
