<?php
/**
 * Template Name: Error Check
 * Description: A page template to identify inconsistencies in player and team data
 */

get_header(); ?>

<div id="container">
    <div id="content">

        <?php the_post(); ?>

        <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 class="entry-title"><?php the_title(); ?></h1>
            
            <div class="entry-content">
                <?php the_content(); ?>

                <style>
                    .error-check-section {
                        margin: 30px 0;
                        padding: 20px;
                        background: #f9f9f9;
                        border-left: 4px solid #dc3232;
                    }
                    .error-check-section.no-errors {
                        border-left-color: #46b450;
                    }
                    .error-check-section h2 {
                        margin-top: 0;
                        color: #333;
                    }
                    .error-list {
                        list-style: none;
                        padding: 0;
                    }
                    .error-list li {
                        padding: 10px;
                        margin: 5px 0;
                        background: white;
                        border-left: 3px solid #dc3232;
                    }
                    .error-list li a {
                        color: #0073aa;
                        text-decoration: none;
                    }
                    .error-list li a:hover {
                        text-decoration: underline;
                    }
                    .success-message {
                        color: #46b450;
                        font-weight: bold;
                    }
                    .error-count {
                        background: #dc3232;
                        color: white;
                        padding: 2px 8px;
                        border-radius: 3px;
                        font-size: 14px;
                        margin-left: 10px;
                    }
                    .stats-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 15px;
                        margin: 20px 0;
                    }
                    .stat-box {
                        padding: 15px;
                        background: white;
                        border: 1px solid #ddd;
                        text-align: center;
                    }
                    .stat-number {
                        font-size: 32px;
                        font-weight: bold;
                        color: #0073aa;
                    }
                    .stat-label {
                        color: #666;
                        margin-top: 5px;
                    }
                </style>

                <?php
                // Get all players and teams
                $players = get_posts(array(
                    'post_type' => 'player',
                    'posts_per_page' => -1,
                    'post_status' => 'publish'
                ));

                $teams = get_posts(array(
                    'post_type' => 'team',
                    'posts_per_page' => -1,
                    'post_status' => 'publish'
                ));

                // Initialize error tracking
                $errors = array(
                    'players_missing_team' => array(),
                    'players_with_invalid_team' => array(),
                    'players_missing_position' => array(),
                    'players_missing_number' => array(),
                    'duplicate_numbers_per_team' => array(),
                    'teams_without_players' => array(),
                    'missing_player_stats' => array(),
                    'invalid_stat_values' => array(),
                    'orphaned_relationships' => array()
                );

                // Get all team IDs for validation
                $valid_team_ids = array_map(function($team) {
                    return $team->ID;
                }, $teams);

                // Check player data
                foreach ($players as $player) {
                    $player_id = $player->ID;
                    $player_name = $player->post_title;
                    $player_link = get_edit_post_link($player_id);

                    // Check for team assignment
                    $player_team = get_field('team', $player_id);
                    if (empty($player_team)) {
                        $errors['players_missing_team'][] = array(
                            'name' => $player_name,
                            'link' => $player_link,
                            'id' => $player_id
                        );
                    } elseif (is_array($player_team) && !in_array($player_team['ID'], $valid_team_ids)) {
                        $errors['players_with_invalid_team'][] = array(
                            'name' => $player_name,
                            'link' => $player_link,
                            'id' => $player_id,
                            'team_id' => $player_team['ID']
                        );
                    } elseif (is_numeric($player_team) && !in_array($player_team, $valid_team_ids)) {
                        $errors['players_with_invalid_team'][] = array(
                            'name' => $player_name,
                            'link' => $player_link,
                            'id' => $player_id,
                            'team_id' => $player_team
                        );
                    }

                    // Check for position
                    $position = get_field('position', $player_id);
                    if (empty($position)) {
                        $errors['players_missing_position'][] = array(
                            'name' => $player_name,
                            'link' => $player_link
                        );
                    }

                    // Check for player number
                    $player_number = get_field('player_number', $player_id);
                    if (empty($player_number) && $player_number !== '0') {
                        $errors['players_missing_number'][] = array(
                            'name' => $player_name,
                            'link' => $player_link
                        );
                    }

                    // Track numbers per team for duplicate checking
                    if (!empty($player_team) && !empty($player_number)) {
                        $team_id = is_array($player_team) ? $player_team['ID'] : $player_team;
                        if (!isset($errors['duplicate_numbers_per_team'][$team_id])) {
                            $errors['duplicate_numbers_per_team'][$team_id] = array();
                        }
                        if (!isset($errors['duplicate_numbers_per_team'][$team_id][$player_number])) {
                            $errors['duplicate_numbers_per_team'][$team_id][$player_number] = array();
                        }
                        $errors['duplicate_numbers_per_team'][$team_id][$player_number][] = array(
                            'name' => $player_name,
                            'link' => $player_link
                        );
                    }

                    // Check for basic stats (customize field names as needed)
                    $height = get_field('height', $player_id);
                    $weight = get_field('weight', $player_id);
                    $year = get_field('year', $player_id);
                    
                    if (empty($height) && empty($weight) && empty($year)) {
                        $errors['missing_player_stats'][] = array(
                            'name' => $player_name,
                            'link' => $player_link
                        );
                    }
                }

                // Check for duplicate numbers per team
                $duplicate_numbers = array();
                foreach ($errors['duplicate_numbers_per_team'] as $team_id => $numbers) {
                    foreach ($numbers as $number => $players_with_number) {
                        if (count($players_with_number) > 1) {
                            $team_name = get_the_title($team_id);
                            $duplicate_numbers[] = array(
                                'team' => $team_name,
                                'number' => $number,
                                'players' => $players_with_number
                            );
                        }
                    }
                }
                $errors['duplicate_numbers_per_team'] = $duplicate_numbers;

                // Check for teams without players
                foreach ($teams as $team) {
                    $team_id = $team->ID;
                    $team_name = $team->post_title;
                    $team_link = get_edit_post_link($team_id);

                    // Find players for this team
                    $team_players = get_posts(array(
                        'post_type' => 'player',
                        'posts_per_page' => -1,
                        'meta_query' => array(
                            array(
                                'key' => 'team',
                                'value' => $team_id,
                                'compare' => '='
                            )
                        )
                    ));

                    if (empty($team_players)) {
                        $errors['teams_without_players'][] = array(
                            'name' => $team_name,
                            'link' => $team_link
                        );
                    }
                }

                // Calculate total errors
                $total_errors = count($errors['players_missing_team']) +
                                count($errors['players_with_invalid_team']) +
                                count($errors['players_missing_position']) +
                                count($errors['players_missing_number']) +
                                count($errors['duplicate_numbers_per_team']) +
                                count($errors['teams_without_players']) +
                                count($errors['missing_player_stats']);
                ?>

                <!-- Summary Statistics -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($players); ?></div>
                        <div class="stat-label">Total Players</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($teams); ?></div>
                        <div class="stat-label">Total Teams</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" style="color: <?php echo $total_errors > 0 ? '#dc3232' : '#46b450'; ?>">
                            <?php echo $total_errors; ?>
                        </div>
                        <div class="stat-label">Total Errors</div>
                    </div>
                </div>

                <!-- Players Missing Team Assignment -->
                <div class="error-check-section <?php echo empty($errors['players_missing_team']) ? 'no-errors' : ''; ?>">
                    <h2>
                        Players Missing Team Assignment
                        <?php if (!empty($errors['players_missing_team'])): ?>
                            <span class="error-count"><?php echo count($errors['players_missing_team']); ?></span>
                        <?php endif; ?>
                    </h2>
                    <?php if (empty($errors['players_missing_team'])): ?>
                        <p class="success-message">✓ All players have team assignments</p>
                    <?php else: ?>
                        <ul class="error-list">
                            <?php foreach ($errors['players_missing_team'] as $error): ?>
                                <li>
                                    <a href="<?php echo $error['link']; ?>" target="_blank">
                                        <?php echo esc_html($error['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Players with Invalid Team References -->
                <?php if (!empty($errors['players_with_invalid_team'])): ?>
                    <div class="error-check-section">
                        <h2>
                            Players with Invalid Team References
                            <span class="error-count"><?php echo count($errors['players_with_invalid_team']); ?></span>
                        </h2>
                        <ul class="error-list">
                            <?php foreach ($errors['players_with_invalid_team'] as $error): ?>
                                <li>
                                    <a href="<?php echo $error['link']; ?>" target="_blank">
                                        <?php echo esc_html($error['name']); ?>
                                    </a>
                                    <span style="color: #999;"> - References non-existent team ID: <?php echo $error['team_id']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Players Missing Position -->
                <div class="error-check-section <?php echo empty($errors['players_missing_position']) ? 'no-errors' : ''; ?>">
                    <h2>
                        Players Missing Position
                        <?php if (!empty($errors['players_missing_position'])): ?>
                            <span class="error-count"><?php echo count($errors['players_missing_position']); ?></span>
                        <?php endif; ?>
                    </h2>
                    <?php if (empty($errors['players_missing_position'])): ?>
                        <p class="success-message">✓ All players have positions assigned</p>
                    <?php else: ?>
                        <ul class="error-list">
                            <?php foreach ($errors['players_missing_position'] as $error): ?>
                                <li>
                                    <a href="<?php echo $error['link']; ?>" target="_blank">
                                        <?php echo esc_html($error['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Players Missing Jersey Number -->
                <div class="error-check-section <?php echo empty($errors['players_missing_number']) ? 'no-errors' : ''; ?>">
                    <h2>
                        Players Missing Jersey Number
                        <?php if (!empty($errors['players_missing_number'])): ?>
                            <span class="error-count"><?php echo count($errors['players_missing_number']); ?></span>
                        <?php endif; ?>
                    </h2>
                    <?php if (empty($errors['players_missing_number'])): ?>
                        <p class="success-message">✓ All players have jersey numbers</p>
                    <?php else: ?>
                        <ul class="error-list">
                            <?php foreach ($errors['players_missing_number'] as $error): ?>
                                <li>
                                    <a href="<?php echo $error['link']; ?>" target="_blank">
                                        <?php echo esc_html($error['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Duplicate Jersey Numbers Per Team -->
                <div class="error-check-section <?php echo empty($errors['duplicate_numbers_per_team']) ? 'no-errors' : ''; ?>">
                    <h2>
                        Duplicate Jersey Numbers Per Team
                        <?php if (!empty($errors['duplicate_numbers_per_team'])): ?>
                            <span class="error-count"><?php echo count($errors['duplicate_numbers_per_team']); ?></span>
                        <?php endif; ?>
                    </h2>
                    <?php if (empty($errors['duplicate_numbers_per_team'])): ?>
                        <p class="success-message">✓ No duplicate jersey numbers found</p>
                    <?php else: ?>
                        <ul class="error-list">
                            <?php foreach ($errors['duplicate_numbers_per_team'] as $duplicate): ?>
                                <li>
                                    <strong><?php echo esc_html($duplicate['team']); ?> - #<?php echo $duplicate['number']; ?></strong>
                                    <ul style="margin-top: 5px;">
                                        <?php foreach ($duplicate['players'] as $player): ?>
                                            <li>
                                                <a href="<?php echo $player['link']; ?>" target="_blank">
                                                    <?php echo esc_html($player['name']); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Teams Without Players -->
                <div class="error-check-section <?php echo empty($errors['teams_without_players']) ? 'no-errors' : ''; ?>">
                    <h2>
                        Teams Without Players
                        <?php if (!empty($errors['teams_without_players'])): ?>
                            <span class="error-count"><?php echo count($errors['teams_without_players']); ?></span>
                        <?php endif; ?>
                    </h2>
                    <?php if (empty($errors['teams_without_players'])): ?>
                        <p class="success-message">✓ All teams have players assigned</p>
                    <?php else: ?>
                        <ul class="error-list">
                            <?php foreach ($errors['teams_without_players'] as $error): ?>
                                <li>
                                    <a href="<?php echo $error['link']; ?>" target="_blank">
                                        <?php echo esc_html($error['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Players Missing Basic Stats -->
                <div class="error-check-section <?php echo empty($errors['missing_player_stats']) ? 'no-errors' : ''; ?>">
                    <h2>
                        Players Missing Basic Stats (Height/Weight/Year)
                        <?php if (!empty($errors['missing_player_stats'])): ?>
                            <span class="error-count"><?php echo count($errors['missing_player_stats']); ?></span>
                        <?php endif; ?>
                    </h2>
                    <?php if (empty($errors['missing_player_stats'])): ?>
                        <p class="success-message">✓ All players have basic stats</p>
                    <?php else: ?>
                        <ul class="error-list">
                            <?php foreach ($errors['missing_player_stats'] as $error): ?>
                                <li>
                                    <a href="<?php echo $error['link']; ?>" target="_blank">
                                        <?php echo esc_html($error['name']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <?php edit_post_link( __( 'Edit', 'tif-wordpress' ), '<span class="edit-link">', '</span>' ); ?>
            </div><!-- .entry-content -->
        </div><!-- #post-<?php the_ID(); ?> -->

    </div><!-- #content -->
    <?php get_sidebar(); ?>
</div><!-- #container -->

<?php get_footer(); ?>
