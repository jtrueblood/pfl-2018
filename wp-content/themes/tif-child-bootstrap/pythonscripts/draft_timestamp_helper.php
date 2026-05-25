<?php
/**
 * Helper function to get draft timestamp from MFL draft results JSON files
 * 
 * This function should be added to functions.php or included in players.php
 * to retrieve actual draft timestamps from MFL API data.
 * 
 * @param int $year The draft year
 * @param string $player_mfl_id The MFL player ID
 * @return string|null The draft timestamp in 'YYYY-MM-DD HH:MM:SS' format, or null if not found
 */
function get_mfl_draft_timestamp($year, $player_mfl_id) {
    $draft_file = get_stylesheet_directory() . '/mfl-drafts/' . $year . '_draft_results.json';
    
    // Check if file exists
    if (!file_exists($draft_file)) {
        return null;
    }
    
    try {
        // Read and decode JSON file
        $json_content = file_get_contents($draft_file);
        $draft_data = json_decode($json_content, true);
        
        // Navigate to draftPick array
        if (!isset($draft_data['draftResults']['draftUnit']['draftPick'])) {
            return null;
        }
        
        $draft_picks = $draft_data['draftResults']['draftUnit']['draftPick'];
        
        // Handle case where draftPick is a single object instead of array
        if (!is_array($draft_picks) || (isset($draft_picks['player']) && !isset($draft_picks[0]))) {
            $draft_picks = [$draft_picks];
        }
        
        // Search for the player's pick
        foreach ($draft_picks as $pick) {
            if (isset($pick['player']) && $pick['player'] == $player_mfl_id && isset($pick['timestamp'])) {
                // Convert Unix timestamp to MySQL datetime format
                $timestamp = intval($pick['timestamp']);
                return date('Y-m-d H:i:s', $timestamp);
            }
        }
        
    } catch (Exception $e) {
        // Silent fail - will fall back to default date
        return null;
    }
    
    return null;
}

/**
 * Get draft date for display (without time)
 * 
 * @param int $year The draft year
 * @param string $player_mfl_id The MFL player ID
 * @return string Draft date in 'YYYY-MM-DD' format
 */
function get_draft_date_for_player($year, $player_mfl_id) {
    $timestamp = get_mfl_draft_timestamp($year, $player_mfl_id);
    
    if ($timestamp) {
        return substr($timestamp, 0, 10); // Extract just the date part
    }
    
    // Default fallback dates
    $default_dates = array(
        1991 => '1991-08-01', 1992 => '1992-08-01', 1993 => '1993-08-01',
        1994 => '1994-08-01', 1995 => '1995-08-01', 1996 => '1996-08-01',
        1997 => '1997-08-01', 1998 => '1998-08-01', 1999 => '1999-08-01',
        2000 => '2000-08-01', 2001 => '2001-08-01', 2002 => '2002-08-01',
        2003 => '2003-08-01', 2004 => '2004-08-01', 2005 => '2005-08-01',
        2006 => '2006-08-01', 2007 => '2007-08-01', 2008 => '2008-08-01',
        2009 => '2009-08-01', 2010 => '2010-08-01', 2011 => '2011-08-01',
        2012 => '2012-08-01', 2013 => '2013-08-01', 2014 => '2014-08-01',
        2015 => '2015-08-01', 2016 => '2016-08-01', 2017 => '2017-08-01',
        2018 => '2018-08-01', 2019 => '2019-08-01', 2020 => '2020-08-01',
        2021 => '2021-08-01', 2022 => '2022-08-01', 2023 => '2023-08-01',
        2024 => '2024-08-01', 2025 => '2025-08-01'
    );
    
    return isset($default_dates[$year]) ? $default_dates[$year] : $year . '-08-01';
}
?>
