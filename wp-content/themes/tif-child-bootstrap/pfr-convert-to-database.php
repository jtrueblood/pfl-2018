<?php
/*
 * Template Name: PFR Player Score From Raw
 * Description: Used to check Pro Football Refernce raw player data to help fill in gaps where we know the score of the position, but don't know the player.
 */
 ?>

<?php

$yeartwenty = get_season_leaders(2020);
$yeartwentyone = get_season_leaders(2021);

$playerid = $_GET['id'];

$playerposition = substr($playerid, -2);

$playerdata = get_player_career_stats($playerid);

$jusplayerids = just_player_ids();
$currentid = array_search($playerid, $jusplayerids);
$nextplayer = $jusplayerids[$currentid + 1];
$holeplayer = $jusplayerids[$currentid + 2];

$getjson = get_pfr_json($playerid);
$getjsona = get_pfr_json($playerid.'_a');

if($getjson AND $getjsona ):
    $both = array_replace($getjson, $getjsona);
else:
    if($getjson):
        $both = $getjson;
    endif;
    if($getjsona):
        $both = $getjsona;
    endif;
endif;

$playerdeets = get_player_data($playerid);

foreach ($playerdeets as $key => $value):

    if($playerposition != 'PK'){
        $nflscore = pos_score_converter($value['year'], $both[$key]['pass_yds'], $both[$key]['pass_td'], $both[$key]['rush_yds'], $both[$key]['rush_td'], $both[$key]['pass_int'], $both[$key]['rec_yds'], $both[$key]['rec_td']);
    } else {
        $nflscore = pk_score_converter($value['year'], $both[$key]['xpm'], $both[$key]['fgm']);
    }

    $passyds = ($both[$key]['pass_yds'] > 0 ? $both[$key]['pass_yds'] : 0);
    $passtds = ($both[$key]['pass_td'] > 0 ? $both[$key]['pass_td'] : 0);
    $passint = ($both[$key]['pass_int'] > 0 ? $both[$key]['pass_int'] : 0);
    $rushyds = ($both[$key]['rush_yds'] > 0 ? $both[$key]['rush_yds'] : 0);
    $rushtd = ($both[$key]['rush_td'] > 0 ? $both[$key]['rush_td'] : 0);
    $recyds = ($both[$key]['rec_yds'] > 0 ? $both[$key]['rec_yds'] : 0);
    $rectd = ($both[$key]['rec_td'] > 0 ? $both[$key]['rec_td'] : 0);
    $xpm = ($both[$key]['xpm'] > 0 ? $both[$key]['xpm'] : 0);
    $xpa = ($both[$key]['xpa'] > 0 ? $both[$key]['xpa'] : 0);
    $fgm = ($both[$key]['fgm'] > 0 ? $both[$key]['fgm'] : 0);
    $fga = ($both[$key]['fga'] > 0 ? $both[$key]['fga'] : 0);

    $newexpanded[$key] = array(
        'weekids' => $value['weekids'],
        'year' => $value['year'],
        'week' => $value['week'],
        'points' => $value['points'],
        'team' => $value['team'],
        'versus' => $value['versus'],
        'playerid' => $value['playerid'],
        'win_loss' => $value['win_loss'],
        'home_away' => $value['home_away'],
        'location' => $value['location'],
        'cum_points' => $value['cum_points'],
        'cum_wins' => $value['cum_wins'],
        'game_date' => $both[$key]['game_date'],
        'nflteam' => $both[$key]['team'],
        'game_location' => $both[$key]['game_location'],
        'opp' => $both[$key]['opp'],
        'pass_yds' => $passyds,
        'pass_td' => $passtds,
        'pass_int' =>  $passint,
        'rush_yds' => $rushyds,
        'rush_td' => $rushtd,
        'rec_yds' => $recyds,
        'rec_td' => $rectd,
        'xpm' => $xpm,
        'xpa' => $xpa,
        'fgm' => $fgm,
        'fga' => $fga,
        'nflscore' => $nflscore,
        'scorediff' => $value['points'] - $nflscore
    );
endforeach;

// in functions file
$insert = insert_stat_columns($playerid);

$insertplay = insert_player_stats($playerid, $newexpanded);

?>

<?php get_header(); ?>

<div class="boxed">
			
			<!--CONTENT CONTAINER-->
			<div id="content-container">
				
				<div id="page-title">
					<?php while (have_posts()) : the_post(); ?>
						<h1 class="page-header text-bold"></h1>
					<?php endwhile; wp_reset_query(); ?>	
				</div>
				
				<!--Page content-->
				<div id="page-content">
					
					<div class="panel panel-bordered panel-light">
						<div class="panel-heading">
							<h3 class="panel-title">Title</h3>
						</div>
						<div class="panel-body">
                            <h4><?php echo 'At Bat: '.$playerid; ?></h4>
                            <h4><?php echo 'On Deck: '. $nextplayer; ?></h4>
                            <h4><?php echo 'In Hole: '. $holeplayer; ?></h4>
                            <?php printr($playerdata, 0);?>
                            <?php printr($newexpanded, 0); ?>
						</div>
								
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

    <script>

        // DISABLE TO STOP AUTO RELOAD

        //setTimeout(function(){
        //	var scrapeclick = '/insert-pfr-json-into-database/?id=<?php //echo $nextplayer; ?>//';
        //    window.location.href = scrapeclick;
        // }, 4000);

    </script>


    <script>
        var scrapeclick = '/insert-pfr-json-into-database/?id=<?php echo $nextplayer; ?>';
        console.log(scrapeclick);
    </script>


<?php get_footer(); ?>