<?php
require_once('/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-load.php');

global $wpdb;

echo "Checking DST vs HAT 2025 Week 16 game...\n\n";

// Get DST's data for this game
$dst_data = $wpdb->get_results("
    SELECT id, playerid, points, team, versus, result 
    FROM wp_playoffs 
    WHERE team='DST' AND versus='HAT' AND year=2025 AND week=16
", ARRAY_A);

// Get HAT's data for this game
$hat_data = $wpdb->get_results("
    SELECT id, playerid, points, team, versus, result 
    FROM wp_playoffs 
    WHERE team='HAT' AND versus='DST' AND year=2025 AND week=16
", ARRAY_A);

echo "DST players in 2025 Week 16:\n";
$dst_total = 0;
foreach ($dst_data as $row) {
    echo "  ID: {$row['id']}, Player: {$row['playerid']}, Points: {$row['points']}, Result: {$row['result']}\n";
    $dst_total += $row['points'];
}
echo "DST Total: $dst_total\n\n";

echo "HAT players in 2025 Week 16:\n";
$hat_total = 0;
foreach ($hat_data as $row) {
    echo "  ID: {$row['id']}, Player: {$row['playerid']}, Points: {$row['points']}, Result: {$row['result']}\n";
    $hat_total += $row['points'];
}
echo "HAT Total: $hat_total\n\n";

// Determine who actually won
if ($dst_total > $hat_total) {
    echo "Winner: DST (by " . ($dst_total - $hat_total) . " points)\n";
    $dst_should_be = 1;
    $hat_should_be = 0;
} else {
    echo "Winner: HAT (by " . ($hat_total - $dst_total) . " points)\n";
    $dst_should_be = 0;
    $hat_should_be = 1;
}

echo "\nChecking for incorrect result values...\n";
$needs_fix = false;

foreach ($dst_data as $row) {
    if ($row['result'] != $dst_should_be) {
        echo "  DST player {$row['playerid']} (ID: {$row['id']}) has result={$row['result']}, should be {$dst_should_be}\n";
        $needs_fix = true;
    }
}

foreach ($hat_data as $row) {
    if ($row['result'] != $hat_should_be) {
        echo "  HAT player {$row['playerid']} (ID: {$row['id']}) has result={$row['result']}, should be {$hat_should_be}\n";
        $needs_fix = true;
    }
}

if ($needs_fix) {
    echo "\nFIXING incorrect result values...\n";
    
    // Fix DST players
    $updated = $wpdb->query($wpdb->prepare("
        UPDATE wp_playoffs 
        SET result = %d 
        WHERE team='DST' AND versus='HAT' AND year=2025 AND week=16
    ", $dst_should_be));
    echo "Updated $updated DST player records to result=$dst_should_be\n";
    
    // Fix HAT players
    $updated = $wpdb->query($wpdb->prepare("
        UPDATE wp_playoffs 
        SET result = %d 
        WHERE team='HAT' AND versus='DST' AND year=2025 AND week=16
    ", $hat_should_be));
    echo "Updated $updated HAT player records to result=$hat_should_be\n";
    
    echo "\nFix complete! Please refresh the team pages.\n";
} else {
    echo "\nNo fixes needed - all result values are correct.\n";
}
