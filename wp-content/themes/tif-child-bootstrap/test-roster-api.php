<?php
/*
 * Test script to debug MFL roster API calls
 */

// Test parameters - you can modify these
$test_year = 2023; // Change to the year you're testing
$test_week = 1;    // Change to the week you're testing

echo "<h2>Testing MFL Roster API</h2>\n";
echo "<p>Testing Year: $test_year, Week: $test_week</p>\n";

// Function to test the API call with detailed debugging
function test_mfl_roster_api($year, $week) {
    echo "<h3>Step 1: Testing API Call</h3>\n";
    
    // Hardcoded league ID for testing - you may need to adjust this
    $test_league_id = '12345'; // Replace with actual league ID
    
    $url = "https://www48.myfantasyleague.com/$year/export?TYPE=rosters&L=$test_league_id&APIKEY=aRNp1sySvuWqx0CmO1HIZDYeFbox&W=$week&JSON=1";
    
    echo "<p><strong>API URL:</strong> $url</p>\n";
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30, // Increased timeout
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_VERBOSE => true, // Enable verbose output
        CURLOPT_STDERR => fopen('php://temp', 'rw+'), // Capture curl verbose output
        CURLOPT_HTTPHEADER => array(
            'Cookie: MFL_PW_SEQ=ah9q2M6Ss%2Bis3Q29; MFL_USER_ID=aRNp1sySvrvrmEDuagWePmY%3D'
        ),
    ));
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    
    // Get verbose output
    rewind(curl_getinfo($curl, CURLOPT_STDERR));
    $verboseLog = stream_get_contents(curl_getinfo($curl, CURLOPT_STDERR));
    
    curl_close($curl);
    
    echo "<h4>Response Details:</h4>\n";
    echo "<p><strong>HTTP Code:</strong> $httpCode</p>\n";
    
    if ($curlError) {
        echo "<p><strong>cURL Error:</strong> $curlError</p>\n";
    }
    
    echo "<p><strong>Response Length:</strong> " . strlen($response) . " characters</p>\n";
    
    if ($verboseLog) {
        echo "<details><summary><strong>Verbose cURL Log</strong></summary><pre>$verboseLog</pre></details>\n";
    }
    
    // Try to decode JSON
    $decoded = json_decode($response, true);
    $jsonError = json_last_error();
    
    if ($jsonError === JSON_ERROR_NONE) {
        echo "<p><strong>JSON Status:</strong> ✅ Valid JSON</p>\n";
        echo "<p><strong>Response Structure:</strong></p>\n";
        echo "<pre>" . print_r(array_keys($decoded), true) . "</pre>\n";
        
        if (isset($decoded['rosters'])) {
            echo "<p>✅ 'rosters' key found in response</p>\n";
            if (isset($decoded['rosters']['franchise'])) {
                echo "<p>✅ 'franchise' key found in rosters</p>\n";
                $franchiseCount = is_array($decoded['rosters']['franchise']) ? count($decoded['rosters']['franchise']) : 0;
                echo "<p><strong>Number of franchises:</strong> $franchiseCount</p>\n";
            } else {
                echo "<p>❌ 'franchise' key NOT found in rosters</p>\n";
            }
        } else {
            echo "<p>❌ 'rosters' key NOT found in response</p>\n";
        }
    } else {
        echo "<p><strong>JSON Status:</strong> ❌ Invalid JSON (Error: " . json_last_error_msg() . ")</p>\n";
    }
    
    // Show first 500 characters of response
    echo "<h4>Raw Response (first 500 chars):</h4>\n";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>\n";
    
    return $decoded;
}

// Test the original function logic
function test_original_logic($year, $week) {
    echo "<h3>Step 2: Testing Original Function Logic</h3>\n";
    
    // Load WordPress functions if not already loaded
    if (!function_exists('get_pfl_mfl_ids_season')) {
        echo "<p>❌ WordPress functions not loaded. Including functions.php...</p>\n";
        include_once('functions.php');
    }
    
    // Check if required functions exist
    $required_functions = ['get_pfl_mfl_ids_season', 'get_mfl_league_id'];
    foreach ($required_functions as $func) {
        if (function_exists($func)) {
            echo "<p>✅ Function '$func' exists</p>\n";
        } else {
            echo "<p>❌ Function '$func' does NOT exist</p>\n";
        }
    }
    
    // Test the functions if they exist
    if (function_exists('get_pfl_mfl_ids_season')) {
        try {
            $getseasonids = get_pfl_mfl_ids_season();
            echo "<p>✅ get_pfl_mfl_ids_season() executed successfully</p>\n";
            echo "<p><strong>Available years:</strong> " . implode(', ', array_keys($getseasonids)) . "</p>\n";
            
            if (isset($getseasonids[$year])) {
                echo "<p>✅ Year $year found in season IDs</p>\n";
            } else {
                echo "<p>❌ Year $year NOT found in season IDs</p>\n";
            }
        } catch (Exception $e) {
            echo "<p>❌ Error calling get_pfl_mfl_ids_season(): " . $e->getMessage() . "</p>\n";
        }
    }
    
    if (function_exists('get_mfl_league_id')) {
        try {
            $leagueid = get_mfl_league_id();
            echo "<p>✅ get_mfl_league_id() executed successfully</p>\n";
            echo "<p><strong>Available league years:</strong> " . implode(', ', array_keys($leagueid)) . "</p>\n";
            
            if (isset($leagueid[$year])) {
                echo "<p>✅ League ID for year $year: " . $leagueid[$year] . "</p>\n";
            } else {
                echo "<p>❌ League ID for year $year NOT found</p>\n";
            }
        } catch (Exception $e) {
            echo "<p>❌ Error calling get_mfl_league_id(): " . $e->getMessage() . "</p>\n";
        }
    }
}

// Check file system
function test_file_system($year, $week) {
    echo "<h3>Step 3: Testing File System</h3>\n";
    
    $destination_folder = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/tif-child-bootstrap/mfl-weekly-rosters';
    $file_path = $destination_folder.'/'.$year.$week.'.json';
    
    echo "<p><strong>Destination folder:</strong> $destination_folder</p>\n";
    echo "<p><strong>File path:</strong> $file_path</p>\n";
    
    if (is_dir($destination_folder)) {
        echo "<p>✅ Destination folder exists</p>\n";
        if (is_writable($destination_folder)) {
            echo "<p>✅ Destination folder is writable</p>\n";
        } else {
            echo "<p>❌ Destination folder is NOT writable</p>\n";
        }
    } else {
        echo "<p>❌ Destination folder does NOT exist</p>\n";
        echo "<p>Attempting to create folder...</p>\n";
        if (mkdir($destination_folder, 0755, true)) {
            echo "<p>✅ Folder created successfully</p>\n";
        } else {
            echo "<p>❌ Failed to create folder</p>\n";
        }
    }
    
    if (file_exists($file_path)) {
        echo "<p>✅ Cache file exists: $file_path</p>\n";
        $file_size = filesize($file_path);
        $file_date = date('Y-m-d H:i:s', filemtime($file_path));
        echo "<p><strong>File size:</strong> $file_size bytes</p>\n";
        echo "<p><strong>File date:</strong> $file_date</p>\n";
        
        // Check if file is valid JSON
        $file_content = file_get_contents($file_path);
        $json_data = json_decode($file_content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p>✅ Cache file contains valid JSON</p>\n";
        } else {
            echo "<p>❌ Cache file contains invalid JSON: " . json_last_error_msg() . "</p>\n";
        }
    } else {
        echo "<p>ℹ️ Cache file does not exist (will be created on first run)</p>\n";
    }
}

// Run all tests
test_mfl_roster_api($test_year, $test_week);
test_original_logic($test_year, $test_week);
test_file_system($test_year, $test_week);

echo "<h3>Instructions:</h3>\n";
echo "<ol>\n";
echo "<li>Check the API URL and HTTP response code above</li>\n";
echo "<li>Verify that your API key is still valid</li>\n";
echo "<li>Make sure the league ID is correct for the year</li>\n";
echo "<li>Ensure the WordPress functions are properly loaded</li>\n";
echo "<li>Check file permissions for the cache directory</li>\n";
echo "</ol>\n";

echo "<p><strong>Next steps:</strong> Update the test parameters at the top of this file with your actual year/week, then run: <code>php test-roster-api.php</code></p>\n";
?>
