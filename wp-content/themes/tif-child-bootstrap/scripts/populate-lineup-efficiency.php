<?php
/**
 * populate-lineup-efficiency.php
 *
 * One-time backfill of wp_lineup_efficiency for all regular-season weeks 2011–present.
 * Safe to re-run: uses INSERT … ON DUPLICATE KEY UPDATE so existing rows are just refreshed.
 *
 * Usage (from the WP document root):
 *   php wp-content/themes/tif-child-bootstrap/scripts/populate-lineup-efficiency.php
 *
 * Optional args:
 *   --year=2023            only process one season
 *   --week=7               only process one week (requires --year)
 *   --start=2011           first year to process (default 2011)
 *   --end=2025             last year to process (default current year)
 *   --dry-run              print what would be done without writing
 */

// ── Bootstrap WordPress ───────────────────────────────────────────────────────
define('DOING_CRON', true); // suppress output buffering / redirects

$wp_root = dirname(__FILE__, 5); // scripts/ → tif-child-bootstrap/ → themes/ → wp-content/ → public/
if (!file_exists($wp_root . '/wp-load.php')) {
    $wp_root = dirname(__FILE__, 4);
}
if (!file_exists($wp_root . '/wp-load.php')) {
    die("ERROR: Cannot find wp-load.php. Run this script from the WordPress root or adjust the path.\n");
}

// Fake minimal server vars so plugins don't crash on missing HTTP context.
$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST']   ?? 'pfl-data.local';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';

// ── Local by Flywheel: fix DB_HOST for CLI use ────────────────────────────────
// Local Lightning runs MySQL on a non-standard port (e.g. 10014), not 3306.
// Web requests route through Local's internal proxy, but CLI PHP hits localhost
// directly and fails. We detect the correct port from Local's sites.json and
// pre-define DB_HOST so wp-config.php's define() becomes a harmless no-op.
if (PHP_SAPI === 'cli') {
    $home       = $_SERVER['HOME'] ?? '';
    $sites_file = $home . '/Library/Application Support/Local/sites.json';
    if ($home && file_exists($sites_file)) {
        $sites = json_decode(file_get_contents($sites_file), true) ?: [];
        foreach ($sites as $site) {
            if (!is_array($site)) continue;
            $site_public = realpath(rtrim($site['path'] ?? '', '/') . '/app/public');
            if ($site_public && $site_public === realpath($wp_root)) {
                $mysql_port = $site['services']['mysql']['ports']['MYSQL'][0] ?? null;
                if ($mysql_port) {
                    define('DB_HOST', '127.0.0.1:' . $mysql_port);
                }
                break;
            }
        }
    }
}

require $wp_root . '/wp-load.php';
// Suppress PHP 8.x deprecations in older WP/plugin code (e.g. WP_HTTP get_class(),
// undefined HTTP_HOST) that are harmless in CLI context.
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

if (!function_exists('pfl_compute_lineup_efficiency_for_week')) {
    die("ERROR: PFL functions not loaded. Make sure the tif-child-bootstrap theme is active.\n");
}

// ── Parse CLI args ────────────────────────────────────────────────────────────
$opts = getopt('', ['year:', 'week:', 'start:', 'end:', 'dry-run', 'debug']);
$dry_run    = isset($opts['dry-run']);
$debug      = isset($opts['debug']);
$only_year  = isset($opts['year'])  ? (int) $opts['year']  : 0;
$only_week  = isset($opts['week'])  ? (int) $opts['week']  : 0;
$year_start = isset($opts['start']) ? (int) $opts['start'] : 2011;
$year_end   = isset($opts['end'])   ? (int) $opts['end']   : (int) date('Y');

if ($only_year) { $year_start = $year_start = $only_year; $year_end = $only_year; }

// ── Debug mode: trace bench data for ETS 2011 week 1 ─────────────────────────
if ($debug) {
    global $wpdb;
    $espn = pfl_get_week_espn_player_stats(2011, 1);
    echo "ESPN stats: " . count($espn) . " players\n\n";

    $bench_raw = get_the_bench(2011, 1, 'ETS');
    echo "ETS bench players with ESPN scores:\n";
    $ets_starters = ['2004BreeQB']; // known starter
    foreach (($bench_raw ?: []) as $status => $players) {
        foreach ($players as $pid => $info) {
            if (!$pid || in_array($pid, $ets_starters)) continue;
            $pos = strtoupper($info['position'] ?? '');
            $full = $wpdb->get_var($wpdb->prepare(
                "SELECT CONCAT(playerFirst,' ',playerLast) FROM wp_players WHERE p_id = %s LIMIT 1", $pid
            ));
            $pts = pfl_calc_bench_score($full ?: ($info['name'] ?? ''), $pos, 2011, $espn);
            echo "  [{$status}] {$pos} {$pid} ({$full}) → " . var_export($pts, true) . " pts\n";
        }
    }
    exit(0);
}

// Flush output after every echo so progress appears in real time.
ob_implicit_flush(true);
if (ob_get_level()) ob_end_flush();

// ── Ensure table exists ───────────────────────────────────────────────────────
pfl_ensure_lineup_efficiency_table();

$total_years = $year_end - $year_start + 1;
echo "PFL Lineup Efficiency — backfill {$year_start}–{$year_end}" . ($dry_run ? " [DRY RUN]" : "") . "\n";
echo str_repeat('─', 60) . "\n";

$grand_written = 0;
$grand_skipped = 0;
$t_start = microtime(true);

// ── Main loop ─────────────────────────────────────────────────────────────────
for ($year = $year_start; $year <= $year_end; $year++) {

    // Determine which weeks had games this year
    global $wpdb;
    $weeks = array_map('intval', $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT week FROM wp_team_WRZ WHERE season = %d AND week BETWEEN 1 AND 14 ORDER BY week ASC",
        $year
    )));

    if (empty($weeks)) {
        echo "  {$year}: no regular-season data found, skipping.\n";
        continue;
    }

    $week_count  = count($weeks);
    $year_written = 0;
    $year_skipped = 0;
    $year_start_t = microtime(true);

    echo "\n{$year}  ({$week_count} weeks)\n";

    foreach ($weeks as $week) {
        if ($only_week && $week !== $only_week) continue;

        if ($dry_run) {
            echo "  [dry] would process {$year} week {$week}\n";
            continue;
        }

        $week_start = microtime(true);
        echo "  w" . str_pad($week, 2) . "  fetching ESPN stats ...";

        $result = pfl_compute_lineup_efficiency_for_week($year, $week);

        $week_elapsed = round(microtime(true) - $week_start, 1);
        $year_written += $result['written'];
        $year_skipped += $result['skipped'];

        $status = "{$result['written']} rows written";
        if ($result['skipped'] > 0) $status .= ", {$result['skipped']} skipped";
        echo "\r  w" . str_pad($week, 2) . "  " . str_pad($status, 30) . "  [{$week_elapsed}s]\n";

        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $err) {
                echo "       WARN: {$err}\n";
            }
        }
    }

    if (!$dry_run) {
        $year_elapsed = round(microtime(true) - $year_start_t, 1);
        $elapsed      = round(microtime(true) - $t_start, 1);
        echo "  ── {$year} done: {$year_written} rows, {$year_skipped} skipped  [{$year_elapsed}s / {$elapsed}s total]\n";
    }

    $grand_written += $year_written;
    $grand_skipped += $year_skipped;
}

// ── Summary ───────────────────────────────────────────────────────────────────
$elapsed = round(microtime(true) - $t_start, 1);
echo "\n" . str_repeat('─', 60) . "\n";
if ($dry_run) {
    echo "Dry run complete. No data written.\n";
} else {
    echo "Done. {$grand_written} rows written, {$grand_skipped} teams skipped. Total time: {$elapsed}s\n";
}
