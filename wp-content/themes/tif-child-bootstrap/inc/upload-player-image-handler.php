<?php
/**
 * AJAX handler: receive a player image upload and run upload_player_image.py
 * POST params: player_id, image (file)
 */

// Only allow logged-in users with valid nonce
if (!is_user_logged_in()) {
    wp_send_json_error(['message' => 'Not authorized.'], 403);
}
if (!check_ajax_referer('upload_player_image', 'nonce', false)) {
    wp_send_json_error(['message' => 'Invalid nonce.'], 403);
}

$player_id = sanitize_text_field($_POST['player_id'] ?? '');

if (empty($player_id)) {
    wp_send_json_error(['message' => 'Player ID is required.']);
}

if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $err = $_FILES['image']['error'] ?? 'No file received';
    wp_send_json_error(['message' => "File upload error: {$err}"]);
}

$file     = $_FILES['image'];
$allowed  = ['image/jpeg', 'image/jpg', 'image/webp'];
$mime     = mime_content_type($file['tmp_name']);

if (!in_array($mime, $allowed)) {
    wp_send_json_error(['message' => "Unsupported file type: {$mime}. Use JPG or WebP."]);
}

// Check image height >= 400px
$info = getimagesize($file['tmp_name']);
if (!$info) {
    wp_send_json_error(['message' => 'Could not read image dimensions.']);
}
[$img_w, $img_h] = $info;
if ($img_h < 400) {
    wp_send_json_error(['message' => "Image is only {$img_h}px tall. Minimum 400px required."]);
}

// Save upload to a temp file with the correct extension
$ext      = $mime === 'image/webp' ? 'webp' : 'jpg';
$tmp_path = sys_get_temp_dir() . '/' . uniqid('pfl_upload_') . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $tmp_path)) {
    wp_send_json_error(['message' => 'Failed to save uploaded file.']);
}

// Path to the Python script
$script = escapeshellarg(
    dirname(__DIR__) . '/pythonscripts/upload_player_image.py'
);
$tmp_escaped    = escapeshellarg($tmp_path);
$player_escaped = escapeshellarg($player_id);

$cmd = "arch -arm64 /usr/local/bin/python3 {$script} --file {$tmp_escaped} --player {$player_escaped} --no-normalize 2>&1";
exec($cmd, $output, $exit_code);

// Clean up temp file
@unlink($tmp_path);

$output_text = implode("\n", array_filter($output, function($line) {
    return !preg_match('/^(I0|W0|INFO:)/', $line);
}));

if ($exit_code === 0) {
    wp_send_json_success([
        'message' => $output_text,
        'player'  => $player_id,
    ]);
} else {
    wp_send_json_error([
        'message' => $output_text ?: 'Script failed with no output.',
    ]);
}
