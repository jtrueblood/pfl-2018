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
		<div class="row">
			<div class="col-xs-24 col-sm-6">
			<?php 
//				$r = '05';
//				$p = 'QB';

            $rounds = array('01','02','03','04','05','06','07');
            $positions = array('QB','RB','WR','PK');

            foreach($positions as $p):
            foreach($rounds as $r):

				$first_pks = get_position_draft_value($r, $p); 
				
				foreach($first_pks as $pks){
					$byseason[$pks['pid']] = array(
					'data' => get_player_season_stats($pks['pid'], $pks['year']),
					'rank' => get_player_season_rank ($pks['pid'], $pks['year'])
					);
				}
				
				$i = 0;
				foreach ($byseason as $key => $value){
					if ($value['data']['points']):
						
						$summary[$key] = array(
							'points' => $value['data']['points'],
							'games' => $value['data']['games'],	
							'ppg' => $value['data']['ppg'],
							'high' => $value['data']['high'],
							'rank' => $value['rank']
						);
					
						$countrank[] = $value['rank'];
						$i++;
					endif;
				}
				
				$ct = array_sum($countrank);
				$avgrank = $ct / $i;
				        echo '<h3>Round '.$r.' - '.$p.'</h3>';
				        echo '<p>Players drafted by round and position.</p>';
				        echo '<p>Average Rank for that Season: '.$avgrank.'</p>';
                    $avgrank = 0;
                    endforeach;
                endforeach;
				//printr($summary, 0);
				
			?>
			</div>
		</div>				
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