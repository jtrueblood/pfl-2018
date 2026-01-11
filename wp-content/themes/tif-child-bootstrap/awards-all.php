<?php
/*
 * Template Name: All Awards
 * Description: Consolidated page displaying all awards and notable events
 */
 ?>

<?php get_header(); ?>

<?php 
	// Get the list of seasons
	$theseasons = the_seasons();
	
	// Get champions data
	$champions = get_just_champions();
	
	// Get MVP data (keyed by year)
	$mvps = get_award('Most Valuable Player', 2);
	
	// Get Rookie of the Year data (keyed by year)
	$rotys = get_award('Rookie of the Year', 2);
	
	// Get Owner of the Year data (keyed by year)
	$ootys = get_award('Owner of the Year', 2);
	
	// Get Posse Bowl MVP data (keyed by year)
	$pbmvps = get_award('Posse Bowl MVP', 2);
	
	// Get Pro Bowl MVP data (keyed by year)
	$probowlmvps = get_award('Pro Bowl MVP', 2);
	
	// Get Pro Bowl details (winner by year)
	$getprobowl = $wpdb->get_results("select * from wp_probowl", ARRAY_N);
	foreach ($getprobowl as $value){
		$probowldetails[$value[1]] = array(
			'winner' => $value[2],	
			'egad' => $value[4],
			'dgas' => $value[5]
		);
	}
	
	// Get team IDs for display names
	$teamids = $_SESSION['teamids'];
?>

<div class="boxed">
		
		<!--CONTENT CONTAINER-->
		<div id="content-container">
			
			<div id="page-title">
				<?php while (have_posts()) : the_post(); ?>
					<h1 class="page-header text-bold"><?php the_title();?></h1>
				<?php endwhile; wp_reset_query(); ?>
			</div>
			
			<!--Page content-->
			<div id="page-content">
				
				<div class="col-xs-24">
					<?php
					$labels = array('Season', 'Champion', 'PFL MVP', 'Rookie of the Year', 'Owner of the Year', 'Posse Bowl MVP', 'Pro Bowl MVP', 'Pro Bowl Winner', 'Passing Title', 'Rushing Title', 'Receiving Title', 'Kicking Title', 'EGAD Winner', 'DGAS Winner', 'MGAC Winner', 'Highest PVQ');
					tablehead('All Awards by Season', $labels);
					
					foreach ($theseasons as $season) {
						echo '<tr>';
						echo '<td class="min-width bord-rgt">' . $season . '</td>';
						
						// Display champion if exists
						if (isset($champions[$season])) {
							$champion_id = $champions[$season];
							$champion_name = isset($teamids[$champion_id]) ? $teamids[$champion_id] : $champion_id;
							echo '<td class="bord-rgt"><strong>' . $champion_name . '</strong></td>';
						} else {
							echo '<td class="bord-rgt">—</td>';
						}
						
						// Display MVP if exists
						if (isset($mvps[$season])) {
							$mvp_first = $mvps[$season]['first'];
							$mvp_last = $mvps[$season]['last'];
							$mvp_team = $mvps[$season]['team'];
							echo '<td class="bord-rgt">' . $mvp_first . ' ' . $mvp_last . ', ' . $mvp_team . '</td>';
						} else {
							echo '<td class="bord-rgt">—</td>';
						}
						
						// Display Rookie of the Year if exists
						if (isset($rotys[$season])) {
							$roty_first = $rotys[$season]['first'];
							$roty_last = $rotys[$season]['last'];
							$roty_team = $rotys[$season]['team'];
							echo '<td class="bord-rgt">' . $roty_first . ' ' . $roty_last . ', ' . $roty_team . '</td>';
						} else {
							echo '<td class="bord-rgt">—</td>';
						}
						
						// Display Owner of the Year if exists
						if (isset($ootys[$season])) {
							$ooty_owner = $ootys[$season]['owner'];
							$ooty_team = $ootys[$season]['team'];
							echo '<td class="bord-rgt">' . $ooty_owner . ', ' . $ooty_team . '</td>';
						} else {
							echo '<td class="bord-rgt">—</td>';
						}
						
						// Display Posse Bowl MVP if exists
						if (isset($pbmvps[$season])) {
							$pbmvp_first = $pbmvps[$season]['first'];
							$pbmvp_last = $pbmvps[$season]['last'];
							$pbmvp_team = $pbmvps[$season]['team'];
							echo '<td class="bord-rgt">' . $pbmvp_first . ' ' . $pbmvp_last . ', ' . $pbmvp_team . '</td>';
						} else {
							echo '<td class="bord-rgt">—</td>';
						}
						
						// Display Pro Bowl MVP if exists
						if (isset($probowlmvps[$season])) {
							$probowlmvp_first = $probowlmvps[$season]['first'];
							$probowlmvp_last = $probowlmvps[$season]['last'];
							$probowlmvp_team = $probowlmvps[$season]['team'];
							echo '<td class="bord-rgt">' . $probowlmvp_first . ' ' . $probowlmvp_last . ', ' . $probowlmvp_team . '</td>';
						} else {
							echo '<td class="bord-rgt">—</td>';
						}
						
						// Display Pro Bowl Winner if exists
						if (isset($probowldetails[$season])) {
							$winner = $probowldetails[$season]['winner'];
							echo '<td class="bord-rgt">' . $winner . '</td>';
						} else {
							echo '<td class="bord-rgt">—</td>';
						}
						
						// Display Passing Title (QB Leader)
						$qb_leaders = get_position_leader($season, 'QB');
						if ($qb_leaders) {
							$qb_names = array();
							foreach ($qb_leaders as $leader) {
								if (count($qb_leaders) > 1) {
									// Condensed format: First initial + Last name
									$qb_names[] = substr($leader['first'], 0, 1) . '.' . $leader['last'] . ', ' . $leader['team'];
								} else {
									// Full name format
									$qb_names[] = $leader['first'] . ' ' . $leader['last'] . ', ' . $leader['team'];
								}
							}
							echo '<td>' . implode(' / ', $qb_names) . '</td>';
						} else {
							echo '<td>—</td>';
						}
						
						// Display Rushing Title (RB Leader)
						$rb_leaders = get_position_leader($season, 'RB');
						if ($rb_leaders) {
							$rb_names = array();
							foreach ($rb_leaders as $leader) {
								if (count($rb_leaders) > 1) {
									// Condensed format: First initial + Last name
									$rb_names[] = substr($leader['first'], 0, 1) . '.' . $leader['last'] . ', ' . $leader['team'];
								} else {
									// Full name format
									$rb_names[] = $leader['first'] . ' ' . $leader['last'] . ', ' . $leader['team'];
								}
							}
							echo '<td>' . implode(' / ', $rb_names) . '</td>';
						} else {
							echo '<td>—</td>';
						}
						
						// Display Receiving Title (WR Leader)
						$wr_leaders = get_position_leader($season, 'WR');
						if ($wr_leaders) {
							$wr_names = array();
							foreach ($wr_leaders as $leader) {
								if (count($wr_leaders) > 1) {
									// Condensed format: First initial + Last name
									$wr_names[] = substr($leader['first'], 0, 1) . '.' . $leader['last'] . ', ' . $leader['team'];
								} else {
									// Full name format
									$wr_names[] = $leader['first'] . ' ' . $leader['last'] . ', ' . $leader['team'];
								}
							}
							echo '<td>' . implode(' / ', $wr_names) . '</td>';
						} else {
							echo '<td>—</td>';
						}
						
						// Display Kicking Title (PK Leader)
						$pk_leaders = get_position_leader($season, 'PK');
						if ($pk_leaders) {
							$pk_names = array();
							foreach ($pk_leaders as $leader) {
								if (count($pk_leaders) > 1) {
									// Condensed format: First initial + Last name
									$pk_names[] = substr($leader['first'], 0, 1) . '.' . $leader['last'] . ', ' . $leader['team'];
								} else {
									// Full name format
									$pk_names[] = $leader['first'] . ' ' . $leader['last'] . ', ' . $leader['team'];
								}
							}
							echo '<td class="bord-rgt">' . implode(' / ', $pk_names) . '</td>';
						} else {
							echo '<td class="bord-rgt">—</td>';
						}
						
						// Get division winners
						$division_winners = get_division_winners($season);
						
						// Display EGAD Winner
						if (isset($division_winners['EGAD'])) {
							echo '<td>' . $division_winners['EGAD'] . '</td>';
						} else {
							echo '<td>—</td>';
						}
						
						// Display DGAS Winner
						if (isset($division_winners['DGAS'])) {
							echo '<td>' . $division_winners['DGAS'] . '</td>';
						} else {
							echo '<td>—</td>';
						}
						
						// Display MGAC Winner
						if (isset($division_winners['MGAC'])) {
							echo '<td class="bord-rgt">' . $division_winners['MGAC'] . '</td>';
						} else {
							echo '<td class="bord-rgt">—</td>';
						}
						
						// Display Highest PVQ Player
						$pvq_leaders = get_highest_pvq_player($season);
						if ($pvq_leaders) {
							$pvq_names = array();
							foreach ($pvq_leaders as $leader) {
								if (count($pvq_leaders) > 1) {
									// Condensed format: First initial + Last name
									$pvq_names[] = substr($leader['first'], 0, 1) . '.' . $leader['last'] . ', ' . $leader['team'];
								} else {
									// Full name format
									$pvq_names[] = $leader['first'] . ' ' . $leader['last'] . ', ' . $leader['team'];
								}
							}
							echo '<td>' . implode(' / ', $pvq_names) . '</td>';
						} else {
							echo '<td>—</td>';
						}
						
						echo '</tr>';
					}
					
					tablefoot('');
					?>
				</div>

			</div><!--End page content-->

		</div><!--END CONTENT CONTAINER-->


	<?php include_once('main-nav.php'); ?>
	<?php include_once('aside.php'); ?>

	</div>
</div> 

<?php session_destroy(); ?>
		
</div>
</div>


<?php get_footer(); ?>
