<?php
/**
 * WordPress Media Upload Helper
 * This script handles uploading images to WordPress media library
 */

// Suppress all warnings and notices to ensure clean JSON output
error_reporting(0);
@ini_set('display_errors', '0');

// Get MySQL socket from command line if provided
$options = getopt('', ['url:', 'title:', 'filename:', 'socket:']);
if (!empty($options['socket'])) {
    define('DB_HOST', 'localhost:' . $options['socket']);
}

// Start output buffering to catch any stray output
ob_start();

// Load WordPress
require_once(__DIR__ . '/../../../../wp-load.php');

// Load required WordPress admin files
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

// Check if this is a CLI request
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

if (empty($options['url'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Missing image URL']);
    exit(1);
}

$image_url = $options['url'];
$title = isset($options['title']) ? $options['title'] : '';
$desired_filename = isset($options['filename']) ? $options['filename'] : '';

// Download the image to a temporary file
$tmp_file = download_url($image_url);

if (is_wp_error($tmp_file)) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to download image: ' . $tmp_file->get_error_message()
    ]);
    exit(1);
}

// Determine file extension
$file_type = wp_check_filetype_and_ext($tmp_file, $image_url);
$extension = $file_type['ext'];

if (empty($extension)) {
    // Try to get extension from URL
    $parsed_url = parse_url($image_url);
    $path = $parsed_url['path'] ?? '';
    $extension = pathinfo($path, PATHINFO_EXTENSION);
}

// Set filename
if (!empty($desired_filename)) {
    // If filename doesn't have extension, add it
    if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $desired_filename)) {
        $desired_filename .= '.' . $extension;
    }
    $filename = $desired_filename;
} else {
    $filename = basename($image_url);
}

// Check if a file with this name already exists in the media library
$upload_dir = wp_upload_dir();
$existing_attachments = get_posts(array(
    'post_type'      => 'attachment',
    'post_status'    => 'inherit',
    'posts_per_page' => -1,
    'meta_query'     => array(
        array(
            'key'     => '_wp_attached_file',
            'value'   => $filename,
            'compare' => 'LIKE'
        )
    )
));

// If an attachment with the same filename exists, delete it
if (!empty($existing_attachments)) {
    foreach ($existing_attachments as $existing) {
        $existing_file = get_attached_file($existing->ID);
        $existing_filename = basename($existing_file);
        
        // Only delete if the filename matches exactly
        if ($existing_filename === $filename) {
            // Delete the attachment and its files
            wp_delete_attachment($existing->ID, true);
        }
    }
}

// Prepare file array for media_handle_sideload
$file_array = array(
    'name'     => $filename,
    'tmp_name' => $tmp_file,
);

// Import the file into the media library
$attachment_id = media_handle_sideload($file_array, 0, $title);

// Check for errors
if (is_wp_error($attachment_id)) {
    @unlink($tmp_file);
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to import to media library: ' . $attachment_id->get_error_message()
    ]);
    exit(1);
}

// Update the post_name (slug) to match the title exactly (without extension)
// This ensures get_attachment_url_by_slug() can find it
if (!empty($title)) {
    $slug = sanitize_title($title);
    wp_update_post(array(
        'ID' => $attachment_id,
        'post_name' => $slug
    ));
}

// Get the attachment URL
$attachment_url = wp_get_attachment_url($attachment_id);
$attachment_path = get_attached_file($attachment_id);

// Clear any buffered output
ob_end_clean();

// Send clean JSON response
echo json_encode([
    'success' => true,
    'message' => 'Image successfully uploaded to WordPress media library',
    'attachment_id' => $attachment_id,
    'attachment_url' => $attachment_url,
    'attachment_path' => $attachment_path,
    'filename' => basename($attachment_path)
]);

exit(0);
