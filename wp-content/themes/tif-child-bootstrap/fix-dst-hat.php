<?php
/*
 * Template Name: Fix DST HAT 2025
 * Description: Fix incorrect result field for DST vs HAT 2025 Week 16
 */

get_header();

global $wpdb;

echo '<div class="boxed"><div id="content-container"><div id="page-content"><div class="row"><div class="col-xs-24">';

echo "<h2>Checking DST vs HAT 2025 Week 16 game...</h2>";

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

echo "<h3>DST players in 2025 Week 16:</h3><pre>";
$dst_total = 0;
foreach ($dst_data as $row) {
    echo "  ID: {$row['id']}, Player: {$row['playerid']}, Points: {$row['points']}, Result: {$row['result']}\n";
    $dst_total += $row['points'];
}
echo "DST Total: $dst_total</pre>";

echo "<h3>HAT players in 2025 Week 16:</h3><pre>";
$hat_total = 0;
foreach ($hat_data as $row) {
    echo "  ID: {$row['id']}, Player: {$row['playerid']}, Points: {$row['points']}, Result: {$row['result']}\n";
    $hat_total += $row['points'];
}
echo "HAT Total: $hat_total</pre>";

// Determine who actually won
if ($dst_total > $hat_total) {
    echo "<p><strong>Winner: DST (by " . ($dst_total - $hat_total) . " points)</strong></p>";
    $dst_should_be = 1;
    $hat_should_be = 0;
} else {
    echo "<p><strong>Winner: HAT (by " . ($hat_total - $dst_total) . " points)</strong></p>";
    $dst_should_be = 0;
    $hat_should_be = 1;
}

echo "<h3>Checking for incorrect result values...</h3><pre>";
$needs_fix = false;

foreach ($dst_data as $row) {
    if ($row['result'] != $dst_should_be) {
        echo "  ❌ DST player {$row['playerid']} (ID: {$row['id']}) has result={$row['result']}, should be {$dst_should_be}\n";
        $needs_fix = true;
    }
}

foreach ($hat_data as $row) {
    if ($row['result'] != $hat_should_be) {
        echo "  ❌ HAT player {$row['playerid']} (ID: {$row['id']}) has result={$row['result']}, should be {$hat_should_be}\n";
        $needs_fix = true;
    }
}
echo "</pre>";

if ($needs_fix && isset($_GET['fix']) && $_GET['fix'] == 'yes') {
    echo "<h3>FIXING incorrect result values...</h3><pre>";
    
    // Fix DST players
    $updated = $wpdb->query($wpdb->prepare("
        UPDATE wp_playoffs 
        SET result = %d 
        WHERE team='DST' AND versus='HAT' AND year=2025 AND week=16
    ", $dst_should_be));
    echo "✓ Updated $updated DST player records to result=$dst_should_be\n";
    
    // Fix HAT players  
    $updated = $wpdb->query($wpdb->prepare("
        UPDATE wp_playoffs 
        SET result = %d 
        WHERE team='HAT' AND versus='DST' AND year=2025 AND week=16
    ", $hat_should_be));
    echo "✓ Updated $updated HAT player records to result=$hat_should_be\n";
    
    echo "</pre><p><strong>✓ Fix complete! Please refresh the <a href='/teams/?id=DST'>DST page</a> and <a href='/teams/?id=HAT'>HAT page</a>.</strong></p>";
} else if ($needs_fix) {
    echo "<p><a href='?fix=yes' class='button button-primary'>Click here to fix the incorrect result values</a></p>";
} else {
    echo "<p><strong>✓ No fixes needed - all result values are correct.</strong></p>";
}

echo '</div></div></div></div></div>';

get_footer();
