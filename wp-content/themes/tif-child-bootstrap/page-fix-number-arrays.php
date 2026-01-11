<?php
/**
 * Template Name: Fix Number Arrays
 * Description: Fix players with number in 'number' column but not in 'numberarray'
 */

get_header();

// Check if user is logged in and is an administrator
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    echo '<div class="boxed"><div id="content-container"><div id="page-content">';
    echo '<h1>Access Denied</h1>';
    echo '<p>You must be logged in as an administrator to access this page.</p>';
    echo '</div></div></div>';
    get_footer();
    exit;
}
?>

<div class="boxed">
    <div id="content-container">
        <div id="page-title">
            <h1 class="page-header text-bold">Fix Player Number Arrays</h1>
        </div>
        
        <div id="page-content">
            <style>
                .success { color: green; font-weight: bold; }
                .error { color: red; font-weight: bold; }
                .info { color: blue; font-weight: bold; }
                .player-block { 
                    background: #f5f5f5; 
                    padding: 15px; 
                    margin: 15px 0; 
                    border-left: 4px solid #0073aa; 
                }
                .confirm-box {
                    background: #fff3cd; 
                    border: 1px solid #ffc107; 
                    padding: 20px; 
                    margin: 20px 0;
                    border-radius: 5px;
                }
                .btn-primary {
                    background: #0073aa; 
                    color: white; 
                    padding: 10px 20px; 
                    text-decoration: none; 
                    border-radius: 3px;
                    display: inline-block;
                    margin-right: 10px;
                }
                .btn-secondary {
                    background: #666;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 3px;
                    display: inline-block;
                }
            </style>

<?php

global $wpdb;

// Get all players - using correct column names: playerFirst, playerLast
$all_players = $wpdb->get_results(
    "SELECT p_id, playerFirst, playerLast, number, numberarray FROM wp_players",
    ARRAY_A
);

// Filter using EXACT logic from error-check.php
// We want: numberarray is empty/null AND number has a value (including 0)
// Note: 0 is a valid jersey number
$players_to_fix = array();
foreach ($all_players as $player) {
    $number = $player['number'];
    $number_array = json_decode($player['numberarray'], true);
    
    // Check if player has a number (including 0) but numberarray is empty/null
    $has_number = ($number !== null && $number !== '');
    $has_number_array = !empty($number_array) && $player['numberarray'] !== 'null';
    
    if (!$has_number_array && $has_number) {
        $players_to_fix[] = $player;
    }
}

if (empty($players_to_fix)) {
    echo '<p class="success">✓ No players found with number in "number" column but missing numberarray!</p>';
    echo '<p><a href="' . home_url('/error-check') . '" class="btn-primary">Back to Error Check</a></p>';
    echo '</div></div>';
    include_once('main-nav.php');
    include_once('aside.php');
    echo '</div>';
    get_footer();
    exit;
}

echo '<p class="info">Found ' . count($players_to_fix) . ' players to fix.</p>';

// Confirm before proceeding
if (!isset($_GET['confirm'])) {
    echo '<div class="confirm-box">';
    echo '<h2>⚠️ Confirmation Required</h2>';
    echo '<p>This will update the numberarray for ' . count($players_to_fix) . ' players.</p>';
    echo '<p><strong>Players to be updated:</strong></p>';
    echo '<ul>';
    foreach ($players_to_fix as $player) {
        echo '<li>' . $player['playerFirst'] . ' ' . $player['playerLast'] . ' (' . $player['p_id'] . ') - Number: ' . $player['number'] . '</li>';
    }
    echo '</ul>';
    echo '<p><a href="?confirm=yes" class="btn-primary">Yes, Update These Players</a>';
    echo '<a href="' . home_url('/error-check') . '" class="btn-secondary">Cancel</a></p>';
    echo '</div>';
    echo '</div></div>';
    include_once('main-nav.php');
    include_once('aside.php');
    echo '</div>';
    get_footer();
    exit;
}

// Process the updates
$updated_count = 0;
$error_count = 0;

foreach ($players_to_fix as $player) {
    $player_id = $player['p_id'];
    $player_name = $player['playerFirst'] . ' ' . $player['playerLast'];
    $number = $player['number'];
    
    echo '<div class="player-block">';
    echo '<h3>' . esc_html($player_name) . ' (' . $player_id . ')</h3>';
    echo '<p>Number: ' . $number . '</p>';
    
    // Get all years this player played from wp_rosters
    $years_played = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT year FROM wp_rosters WHERE pid = %s ORDER BY year",
            $player_id
        )
    );
    
    if (empty($years_played)) {
        echo '<p class="error">⚠️ No years found in wp_rosters for this player. Skipping.</p>';
        $error_count++;
        echo '</div>';
        continue;
    }
    
    // Build the numberarray JSON
    $number_array = array();
    foreach ($years_played as $year) {
        $number_array[$year] = $number;
    }
    
    $number_array_json = json_encode($number_array);
    
    echo '<p>Years played: ' . implode(', ', $years_played) . '</p>';
    echo '<p>New numberarray: <code>' . esc_html($number_array_json) . '</code></p>';
    
    // Update the database
    $result = $wpdb->update(
        'wp_players',
        array('numberarray' => $number_array_json),
        array('p_id' => $player_id),
        array('%s'),
        array('%s')
    );
    
    if ($result !== false) {
        echo '<p class="success">✓ Updated successfully!</p>';
        $updated_count++;
    } else {
        echo '<p class="error">✗ Failed to update. Error: ' . $wpdb->last_error . '</p>';
        $error_count++;
    }
    
    echo '</div>';
}

// Summary
echo '<hr>';
echo '<h2>Summary</h2>';
echo '<p class="success">Successfully updated: ' . $updated_count . ' players</p>';
if ($error_count > 0) {
    echo '<p class="error">Errors: ' . $error_count . '</p>';
}
echo '<p><a href="' . home_url('/error-check') . '" class="btn-primary">Return to Error Check</a></p>';
?>

        </div>
    </div>
    
    <?php include_once('main-nav.php'); ?>
    <?php include_once('aside.php'); ?>
</div>

<?php get_footer(); ?>
