<?php
require_once('/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-load.php');

global $wpdb;

echo "DST vs HAT Postseason Games:\n";
echo "===========================\n\n";

$results = $wpdb->get_results("
    SELECT year, week, team, versus, result 
    FROM wp_playoffs 
    WHERE (team='DST' AND versus='HAT') OR (team='HAT' AND versus='DST') 
    ORDER BY year, week
", ARRAY_A);

foreach ($results as $row) {
    echo "Year: {$row['year']}, Week: {$row['week']}, Team: {$row['team']}, Versus: {$row['versus']}, Result: {$row['result']}\n";
}

echo "\n\nDST's perspective:\n";
$dst_games = $wpdb->get_results("
    SELECT year, week, team, versus, result 
    FROM wp_playoffs 
    WHERE team='DST' AND versus='HAT'
    ORDER BY year, week
", ARRAY_A);

foreach ($dst_games as $row) {
    $outcome = ($row['result'] == 1) ? 'WIN' : 'LOSS';
    echo "Year: {$row['year']}, Week: {$row['week']} - {$outcome}\n";
}

echo "\n\nHAT's perspective:\n";
$hat_games = $wpdb->get_results("
    SELECT year, week, team, versus, result 
    FROM wp_playoffs 
    WHERE team='HAT' AND versus='DST'
    ORDER BY year, week
", ARRAY_A);

foreach ($hat_games as $row) {
    $outcome = ($row['result'] == 1) ? 'WIN' : 'LOSS';
    echo "Year: {$row['year']}, Week: {$row['week']} - {$outcome}\n";
}
