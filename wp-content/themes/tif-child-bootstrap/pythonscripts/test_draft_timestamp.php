<?php
// Test script for get_mfl_draft_timestamp function
require_once('/Users/jamietrueblood/Local Sites/posse-football-league/app/public/wp-content/themes/tif-child-bootstrap/functions.php');

// Test cases based on actual data from JSON files
$test_cases = [
    ['year' => 2025, 'player_id' => '17042', 'expected' => '1755972079'],
    ['year' => 2025, 'player_id' => '16214', 'expected' => '1755972165'],
    ['year' => 2021, 'player_id' => '14803', 'expected' => '1598808497'],
    ['year' => 2021, 'player_id' => '14777', 'expected' => '1598808971'],
    ['year' => 2021, 'player_id' => '12620', 'expected' => '1598809899'],
];

echo "Testing get_mfl_draft_timestamp function:\n";
echo "==========================================\n\n";

foreach ($test_cases as $test) {
    $result = get_mfl_draft_timestamp($test['year'], $test['player_id']);
    $status = ($result == $test['expected']) ? '✓ PASS' : '✗ FAIL';
    
    echo "Year {$test['year']}, Player {$test['player_id']}: $status\n";
    echo "  Expected: {$test['expected']}\n";
    echo "  Got:      " . ($result ? $result : 'null') . "\n";
    
    if ($result) {
        echo "  Date:     " . date('Y-m-d H:i:s', $result) . "\n";
    }
    echo "\n";
}

// Test non-existent player
echo "Testing non-existent player:\n";
$result = get_mfl_draft_timestamp(2025, '99999');
echo "  Result: " . ($result ? $result : 'null (expected)') . "\n\n";

// Test year without JSON file
echo "Testing year without JSON file:\n";
$result = get_mfl_draft_timestamp(2010, '12345');
echo "  Result: " . ($result ? $result : 'null (expected)') . "\n";
