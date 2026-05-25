<?php
/**
 * One-time script to build the Sidebar Navigation menu from the existing structure
 * 
 * To run this script:
 * 1. Go to WordPress Admin
 * 2. Navigate to this URL: /wp-content/themes/tif-child-bootstrap/build-sidebar-menu.php
 * Or run from command line: wp eval-file build-sidebar-menu.php
 */

// Load WordPress
require_once('../../../../../wp-load.php');

// Check if user is logged in and is an administrator
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('You must be logged in as an administrator to run this script.');
}

echo '<h1>Building Sidebar Navigation Menu</h1>';

// Delete existing menu if it exists
$existing_menu = wp_get_nav_menu_object('Sidebar Navigation');
if ($existing_menu) {
    wp_delete_nav_menu($existing_menu->term_id);
    echo '<p>Deleted existing Sidebar Navigation menu.</p>';
}

// Create new menu
$menu_id = wp_create_nav_menu('Sidebar Navigation');
echo '<p>Created new menu with ID: ' . $menu_id . '</p>';

// Define the menu structure based on main-nav.php
$menu_structure = array(
    'Awards' => array(
        'url' => '#',
        'children' => array(
            'Hall of Fame' => '/hall-of-fame',
            'Most Valuable Player' => '/mvp',
            'Rookie of the Year' => '/rookie',
            'Posse Bowl MVP' => '/posse-bowl-mvp',
            'Pro Bowl MVP' => '/pro-bowl-mvp',
            'All Awards' => '/all-awards'
        )
    ),
    'Players' => array(
        'url' => '#',
        'children' => array(
            'Individual Players' => '/player/?id=1998MannQB',
            'Career Leaders' => '/leaders',
            'Leaders By Season' => '/leaders-season/?id=2025',
            'Supercards' => '/supercards/'
        )
    ),
    'Seasons' => array(
        'url' => '#',
        'children' => array(
            'Seasons' => '/seasons/?id=2025',
            'Drafts by Year' => '/drafts/?id=2025',
            'Standings By Year' => '/standings/?id=2025',
            'Playoff Brackets' => '/playoff-brackets',
            'Team Rosters' => '/team-rosters/?season=2025'
        )
    ),
    'Teams' => array(
        'url' => '#',
        'children' => array(
            'Teams' => '/teams/?id=ETS',
            'Eras' => '/eras/?id=ETS',
            'Protections By Team' => '/protections-team/?Y=&TEAM='
        )
    ),
    'Games' => array(
        'url' => '#',
        'children' => array(
            'Weekly Results' => '/results?Y=2025&W=01',
            'All Schedules' => '/schedules',
            'Grandslams' => '/grandslams',
            'Home and Away' => '/home-and-away',
            'The Playoffs' => '/playoffs',
            'The Posse Bowl' => '/champions',
            'The Pro Bowl' => '/pro-bowl'
        )
    ),
    'Table Data' => array(
        'url' => '#',
        'children' => array(
            'Tables - Players' => '/tables-players',
            'Tables - Teams' => '/tables-teams',
            'Tables - Postseason' => '/tables-postseason',
            'Tables - NFL' => '/tables-nfl',
            'Tables - Drafts' => '/tables-drafts',
            'Tables - Scoring Title' => '/scoring-title-pages',
            'Tables - Other' => '/tables-other'
        )
    ),
    'Resources' => array(
        'url' => '#',
        'children' => array(
            'Players by NFL Team' => '/nfl-team-page',
            'Timeline' => '/timeline',
            'HOF Eligibility' => '/hall-eligible-players',
            'Head to Head Matrix' => '/head-to-head',
            'Trades' => '/trades',
            'Trade Analyzer' => '/trade-analyzer?TRADE=130',
            'Playoff Probability' => '/playoff-probability',
            'Draft Research' => '/research',
            'Unis & Helmets' => '/uniforms',
            'Number Ones' => '/number-ones',
            'Mr Irrelevant' => '/mr-irrelevant',
            'Kicker Drafts' => '/kicker-draft/?draft_year=2025/',
            'Scorigami' => '/scorigami/?W=202501',
            'Position Difference' => '/position-difference',
            'Colleges' => '/colleges',
            'Error Check' => '/error-check'
        )
    )
);

// Function to add menu items
function add_menu_items($menu_id, $menu_structure, $parent_id = 0) {
    $position = 0;
    
    foreach ($menu_structure as $title => $data) {
        $position++;
        
        // Add parent item
        $item_data = array(
            'menu-item-title' => $title,
            'menu-item-url' => is_array($data) ? $data['url'] : $data,
            'menu-item-status' => 'publish',
            'menu-item-parent-id' => $parent_id,
            'menu-item-position' => $position
        );
        
        $item_id = wp_update_nav_menu_item($menu_id, 0, $item_data);
        
        echo '<p>Added: ' . $title . ' (ID: ' . $item_id . ')</p>';
        
        // Add children if they exist
        if (is_array($data) && isset($data['children'])) {
            add_menu_items($menu_id, $data['children'], $item_id);
        }
    }
}

// Build the menu
add_menu_items($menu_id, $menu_structure);

// Assign menu to location
$locations = get_theme_mod('nav_menu_locations');
$locations['sidebar_navigation'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);

echo '<h2>Menu Creation Complete!</h2>';
echo '<p>The Sidebar Navigation menu has been created and assigned to the sidebar_navigation location.</p>';
echo '<p><a href="' . admin_url('nav-menus.php?action=edit&menu=' . $menu_id) . '">Edit Menu in WordPress Admin</a></p>';
echo '<p><a href="' . home_url() . '">View Site</a></p>';
