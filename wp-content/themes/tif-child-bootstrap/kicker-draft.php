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
    <?php
    // Get draft_year from query parameter, default to 2024 if not set
    $draft_year = isset($_GET['draft_year']) ? $_GET['draft_year'] : '2024';

    // Get only kickers drafted in selected year from wp_drafts
    $drafts_year = get_drafts_by_year($draft_year);
    $kickers_year = array_filter($drafts_year, function($draft) {
        return isset($draft['position']) && strtoupper($draft['position']) === 'PK';
    });
    ?>
    <h2>Kickers Drafted in <?php echo htmlspecialchars($draft_year); ?></h2>
    <!-- Add a draft_year selector form -->
    <form method="get" class="form-inline" style="margin-bottom: 15px;">
        <label for="draft_year">Year:</label>
        <input type="number" name="draft_year" id="draft_year" value="<?php echo htmlspecialchars($draft_year); ?>" min="1991" max="2025" class="form-control" style="width: 100px; margin-right: 10px;">
        <button type="submit" class="btn btn-primary">Go</button>
    </form>
    <div class="row">
    <div class="col-xs-24">
    <div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Round</th>
                <th>Pick</th>
                <th>Overall</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Team</th>
                <th>Player ID</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($kickers_year as $kicker): ?>
            <?php
            $first = $kicker['playerfirst'];
            $last = $kicker['playerlast'];
            if (empty($first) || empty($last)) {
                $name = get_player_name($kicker['playerid']);
                $first = $name['first'];
                $last = $name['last'];
            }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($kicker['round']); ?></td>
                <td><?php echo htmlspecialchars($kicker['pick']); ?></td>
                <td><?php echo htmlspecialchars($kicker['overall']); ?></td>
                <td><?php echo htmlspecialchars($first); ?></td>
                <td><?php echo htmlspecialchars($last); ?></td>
                <td><?php echo htmlspecialchars($kicker['acteam']); ?></td>
                <td><?php echo htmlspecialchars($kicker['playerid']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    </div>
    </div>

    <!-- Kicker Points Leaders Table -->
    <?php
    // Get kicker points leaders for the selected year
    $season_leaders = get_season_leaders($draft_year);
    $kicker_leaders = array_filter($season_leaders, function($leader) {
        return isset($leader['position']) && strtoupper($leader['position']) === 'PK';
    });
    // Sort by points descending
    usort($kicker_leaders, function($a, $b) {
        if ($a['points'] == $b['points']) return 0;
        return ($a['points'] < $b['points']) ? 1 : -1;
    });
    ?>
    <h2>Kicker Points Leaders in <?php echo htmlspecialchars($draft_year); ?></h2>
    <div class="row">
    <div class="col-xs-24">
    <div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Rank</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Player ID</th>
                <th>Points</th>
                <th>Games</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $rank = 1;
        foreach($kicker_leaders as $leader): ?>
            <?php
            $first = '';
            $last = '';
            foreach($kickers_year as $kicker) {
                if($kicker['playerid'] == $leader['playerid']) {
                    $first = $kicker['playerfirst'];
                    $last = $kicker['playerlast'];
                    break;
                }
            }
            if (empty($first) || empty($last)) {
                $name = get_player_name($leader['playerid']);
                $first = $name['first'];
                $last = $name['last'];
            }
            ?>
            <tr>
                <td><?php echo $rank++; ?></td>
                <td><?php echo htmlspecialchars($first); ?></td>
                <td><?php echo htmlspecialchars($last); ?></td>
                <td><?php echo htmlspecialchars($leader['playerid']); ?></td>
                <td><?php echo htmlspecialchars($leader['points']); ?></td>
                <td><?php echo htmlspecialchars($leader['games']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    </div>
    </div>

    <!-- Drafted Kickers Who Are Points Leaders Table -->
    <?php
    // Build a lookup of drafted kicker playerids
    $drafted_kicker_ids = array();
    foreach($kickers_year as $kicker) {
        $drafted_kicker_ids[$kicker['playerid']] = $kicker;
    }
    // Find kickers who are both drafted and points leaders
    $drafted_leader_kickers = array();
    foreach($kicker_leaders as $leader) {
        if(isset($drafted_kicker_ids[$leader['playerid']])) {
            $draft_info = $drafted_kicker_ids[$leader['playerid']];
            $drafted_leader_kickers[] = array(
                'round' => $draft_info['round'],
                'pick' => $draft_info['pick'],
                'overall' => $draft_info['overall'],
                'first' => $draft_info['playerfirst'],
                'last' => $draft_info['playerlast'],
                'team' => $draft_info['acteam'],
                'playerid' => $leader['playerid'],
                'points' => $leader['points'],
                'games' => $leader['games']
            );
        }
    }
    ?>
    <h2>Drafted Kickers Who Are Points Leaders in <?php echo htmlspecialchars($draft_year); ?></h2>
    <div class="row">
    <div class="col-xs-24">
    <div class="table-responsive">
    <?php if(count($drafted_leader_kickers) > 0): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Round</th>
                <th>Pick</th>
                <th>Overall</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Team</th>
                <th>Player ID</th>
                <th>Points</th>
                <th>Games</th>
                <th>Positional Rank</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Build a lookup for positional rank by playerid from $kicker_leaders
        $positional_ranks = array();
        $rank = 1;
        foreach($kicker_leaders as $leader) {
            $positional_ranks[$leader['playerid']] = $rank++;
        }
        foreach($drafted_leader_kickers as $row): ?>
            <?php
            $first = $row['first'];
            $last = $row['last'];
            if (empty($first) || empty($last)) {
                $name = get_player_name($row['playerid']);
                $first = $name['first'];
                $last = $name['last'];
            }
            $highlight = '';
            if ($row['round'] == '01') {
                $highlight = ' style="font-weight:bold; color:red;"';
            } elseif ($row['round'] == '02') {
                $highlight = ' style="font-weight:bold; color:orange;"';
            }
            $pos_rank = isset($positional_ranks[$row['playerid']]) ? $positional_ranks[$row['playerid']] : '';
            ?>
            <tr<?php echo $highlight; ?>>
                <td><?php echo htmlspecialchars($row['round']); ?></td>
                <td><?php echo htmlspecialchars($row['pick']); ?></td>
                <td><?php echo htmlspecialchars($row['overall']); ?></td>
                <td><?php echo htmlspecialchars($first); ?></td>
                <td><?php echo htmlspecialchars($last); ?></td>
                <td><?php echo htmlspecialchars($row['team']); ?></td>
                <td><?php echo htmlspecialchars($row['playerid']); ?></td>
                <td><?php echo htmlspecialchars($row['points']); ?></td>
                <td><?php echo htmlspecialchars($row['games']); ?></td>
                <td><?php echo htmlspecialchars($pos_rank); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No drafted kickers are points leaders for <?php echo htmlspecialchars($draft_year); ?>.</p>
    <?php endif; ?>
    </div>
    </div>
    </div>
    </div>
<!--END WHITE WRAPPER-->
</div><!--END CONTENT CONTAINER-->


<?php include_once('main-nav.php'); ?>
<?php include_once('aside.php'); ?>

</div><!--END BOXED-->

<?php
$jusplayerids = just_player_ids();
$currentid = array_search($playerid, $jusplayerids);
$nextplayer = $jusplayerids[$currentid + 1];
$holeplayer = $jusplayerids[$currentid + 2];
?>

<?php get_footer(); ?>
