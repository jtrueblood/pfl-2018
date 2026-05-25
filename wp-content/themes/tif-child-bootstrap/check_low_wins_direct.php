<?php
/**
 * Check for teams with fewer than 10 wins in any season
 * Direct MySQL connection
 */

$db_host = 'localhost';
$db_name = 'local';
$db_user = 'root';
$db_pass = 'root';
$socket = glob($_SERVER['HOME'] . '/Library/Application Support/Local/run/*/mysql/mysqld.sock')[0];

try {
    $pdo = new PDO("mysql:unix_socket=$socket;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $teams_under_10_wins = array();
    
    // Check from 1991 to current year
    $current_year = date('Y');
    for ($season = 1991; $season <= $current_year; $season++) {
        $table_name = "wp_stand" . $season;
        
        // Check if table exists
        $check_table = $pdo->query("SHOW TABLES LIKE '$table_name'");
        
        if ($check_table->rowCount() > 0) {
            $stmt = $pdo->prepare("SELECT * FROM $table_name WHERE win < 10");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
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
    
    // Display results
    echo "<h2>Teams with fewer than 10 wins:</h2>\n";
    
    if (empty($teams_under_10_wins)) {
        echo "<p>No teams found with fewer than 10 wins in any season.</p>\n";
    } else {
        echo "<p>Found " . count($teams_under_10_wins) . " team season(s) with fewer than 10 wins:</p>\n";
        echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
        echo "<tr><th>Season</th><th>Team</th><th>Division</th><th>Record</th><th>Win %</th></tr>\n";
        
        foreach ($teams_under_10_wins as $team) {
            $win_pct = number_format($team['wins'] / 14, 3);
            echo "<tr>";
            echo "<td>{$team['season']}</td>";
            echo "<td>" . strtoupper($team['team']) . "</td>";
            echo "<td>{$team['division']}</td>";
            echo "<td>{$team['wins']}-{$team['losses']}</td>";
            echo "<td>{$win_pct}</td>";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
