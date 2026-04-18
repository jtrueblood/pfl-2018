<?php
/**
 * Check for kicker attempt errors (XPA/FGA = 0 when XPM/FGM > 0)
 * For years 1991-2000 using wp_stathead data
 * 
 * @return array List of kickers with missing attempt data
 */
function check_kicker_attempt_errors() {
    global $wpdb;
    
    $errors = array();
    
    // Get all kickers from 1991-2000
    $kickers = $wpdb->get_results("
        SELECT p_id, playerFirst, playerLast
        FROM wp_players
        WHERE p_id LIKE '%PK'
        AND SUBSTRING(p_id, 1, 4) BETWEEN '1991' AND '2000'
        ORDER BY playerLast, playerFirst
    ", ARRAY_A);
    
    foreach ($kickers as $kicker) {
        $p_id = $kicker['p_id'];
        $player_name = $kicker['playerFirst'] . ' ' . $kicker['playerLast'];
        $player_link = home_url('/player/?id=' . $p_id);
        
        // Check if player table exists
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $p_id
        ));
        
        if (!$table_exists) continue;
        
        // Query for records with missing attempts (1991-2000 only)
        $query = "
            SELECT year, week, week_id, xpm, xpa, fgm, fga
            FROM `{$p_id}`
            WHERE year BETWEEN 1991 AND 2000
            AND ((xpm > 0 AND (xpa IS NULL OR xpa = 0)) 
                 OR (fgm > 0 AND (fga IS NULL OR fga = 0)))
            ORDER BY year, week
        ";
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        if (!empty($results)) {
            foreach ($results as $row) {
                // Get player's PFL team for this year
                $player_team = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT team FROM wp_rosters WHERE pid = %s AND year = %d",
                        $p_id,
                        $row['year']
                    )
                );
                
                $errors[] = array(
                    'player_name' => $player_name,
                    'player_id' => $p_id,
                    'player_link' => $player_link,
                    'player_team' => $player_team,
                    'year' => $row['year'],
                    'week' => $row['week'],
                    'xpm' => $row['xpm'],
                    'xpa' => $row['xpa'],
                    'fgm' => $row['fgm'],
                    'fga' => $row['fga']
                );
            }
        }
    }
    
    return $errors;
}
