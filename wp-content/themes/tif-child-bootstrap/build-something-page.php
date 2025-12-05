<?php
/*
 * Template Name: Kicker Draft
 * Description: Stuff Goes Here
 */

// IDEAS:  1. Scorigami checking finction.  Pass game id to see if the game is a scorigami.  '2010ETSWRZ'.  Would need so save the event to a db 'wp_check_scorigami'
// Would also need to step through each week to save historical data, then make a function that checks it moving forward.
 ?>

<?php get_header();

$seasons = the_seasons();
$teams = get_teams();
$playerid = $_GET['id'];
$season = $_GET['season'];
$weeks = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14');
$weekids = the_weeks();


//printr($playersassoc, 0);

 ?>
<div class="boxed">
    <div style="background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 32px; margin: 32px auto; max-width: 1100px;">
    <h2>Average Points Against Per Game by Team and Season</h2>
    <div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Season</th>
                <th>Team Name</th>
                <th>Avg Points Against Per Game</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $all_standings = get_all_standings();
        $rows = [];

        foreach ($all_standings as $season => $teams) {
            if ($season == '1991') continue; // Skip 1991 season
            foreach ($teams as $team) {
                $teamname = isset($team['teamname']) ? $team['teamname'] : '';
                $ptsvs = isset($team['ptsvs']) ? $team['ptsvs'] : 0;
                $win = isset($team['win']) ? $team['win'] : 0;
                $loss = isset($team['loss']) ? $team['loss'] : 0;
                $games = $win + $loss;
                $avg_points_against = $games > 0 ? $ptsvs / $games : null;
                $rows[] = [
                    'season' => $season,
                    'teamname' => $teamname,
                    'avg' => $avg_points_against
                ];
            }
        }

        // Sort by avg ascending, treating null as highest
        usort($rows, function($a, $b) {
            if ($a['avg'] === null) return 1;
            if ($b['avg'] === null) return -1;
            return $a['avg'] <=> $b['avg'];
        });

        foreach ($rows as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['season']) . '</td>';
            echo '<td>' . htmlspecialchars($row['teamname']) . '</td>';
            echo '<td>' . ($row['avg'] !== null ? number_format($row['avg'], 2) : 'N/A') . '</td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
    </div>
    </div>

        <!--CONTENT CONTAINER-->
        <div id="content-container">

            <!--Page content-->


    </div><!--End page content-->

</div><!--END CONTENT CONTAINER-->


<?php include_once('main-nav.php'); ?>
<?php include_once('aside.php'); ?>

</div>

    <?php
    $jusplayerids = just_player_ids();
    $currentid = array_search($playerid, $jusplayerids);
    $nextplayer = $jusplayerids[$currentid + 1];
    $holeplayer = $jusplayerids[$currentid + 2];
    ?>

    <script>

        // DISABLE TO STOP AUTO RELOAD

        //setTimeout(function(){
        //	var reloadpage = '/build-something/?id=<?php //echo $nextplayer; ?>//';
        //    window.location.href = reloadpage;
        // }, 3000);

    </script>


    <script>
        var reloadpage = '/build-something/?id=<?php echo $nextplayer; ?>';
        console.log(reloadpage);
    </script>

<?php get_footer(); ?>