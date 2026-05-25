<?php
/**
 * Check for teams with fewer than 10 wins in any season
 */

// Load WordPress
$wp_load = dirname(__FILE__) . '/../../../../wp-load.php';
require_once($wp_load);

global $wpdb;

// Get all seasons
function the_seasons_local(){
    $year = date('Y');
    $o = 1991;
    while ($o <= $year){
        $theseasons[] = $o;
        $o++;
    }
    return $theseasons;
}

$seasons = the_seasons_local();
$teams_under_10_wins = array();

foreach ($seasons as $season) {
    $table_name = "stand" . $season;
    
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

// Display results
echo "<h2>Teams with fewer than 10 wins:</h2>\n";

if (empty($teams_under_10_wins)) {
    echo "<p>No teams found with fewer than 10 wins in any season.</p>\n";
} else {
    echo "<p>Found " . count($teams_under_10_wins) . " team season(s) with fewer than 10 wins:</p>\n";
    echo "<table border='1' cellpadding='5'>\n";
    echo "<tr><th>Season</th><th>Team</th><th>Division</th><th>Wins</th><th>Losses</th></tr>\n";
    
    foreach ($teams_under_10_wins as $team) {
        echo "<tr>";
        echo "<td>{$team['season']}</td>";
        echo "<td>{$team['team']}</td>";
        echo "<td>{$team['division']}</td>";
        echo "<td>{$team['wins']}</td>";
        echo "<td>{$team['losses']}</td>";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
}
?>
