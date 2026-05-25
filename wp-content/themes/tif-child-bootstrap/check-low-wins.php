<?php
/*
 * Template Name: Check Low Wins
 * Description: Check for teams with fewer than 10 wins
 */
?>

<?php get_header(); ?>

<?php

global $wpdb;

$seasons = the_seasons();
$teams_under_10_wins = array();

foreach ($seasons as $season) {
    $table_name = "wp_stand" . $season;
    
    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'");
    
    if ($table_exists) {
        $results = $wpdb->get_results("SELECT * FROM {$table_name} WHERE win < 10", ARRAY_A);
        
        if (!empty($results)) {
            foreach ($results as $row) {
                $teams_under_10_wins[] = array(
                    'season' => $season,
                    'team' => $row['teamname'],
                    'wins' => $row['win'],
                    'losses' => $row['loss'],
                    'division' => $row['division']
                );
            }
        }
    }
}

?>

<div id="page-content">
    <div id="content-container">
        <div class="col-xs-24">
            <div class="panel">
                <div class="panel-body">
                    <h2>Teams with fewer than 10 wins:</h2>
                    
                    <?php if (empty($teams_under_10_wins)): ?>
                        <p>No teams found with fewer than 10 wins in any season.</p>
                    <?php else: ?>
                        <p>Found <?php echo count($teams_under_10_wins); ?> team season(s) with fewer than 10 wins:</p>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Season</th>
                                    <th>Team</th>
                                    <th>Division</th>
                                    <th>Record</th>
                                    <th>Win %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teams_under_10_wins as $team): ?>
                                    <?php $win_pct = number_format($team['wins'] / 14, 3); ?>
                                    <tr>
                                        <td><?php echo $team['season']; ?></td>
                                        <td><?php echo strtoupper($team['team']); ?></td>
                                        <td><?php echo $team['division']; ?></td>
                                        <td><?php echo $team['wins'] . '-' . $team['losses']; ?></td>
                                        <td><?php echo $win_pct; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
