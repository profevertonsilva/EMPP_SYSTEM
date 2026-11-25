<?php
/*
 * Profile photo upload (POST /dashboard/administrator/upload-photo).
 *
 * The file is checked by its content, never by the name or the type the browser
 * sends: both are chosen by the client. The extension on disk comes from the
 * detected image type, so an upload can never be stored as .php (or anything
 * else the server would execute).
 */
$photoForm = '/dashboard/administrator/my-photo';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['profile_photo'])) {
    header("Location: $photoForm");
    exit;
}

$file = $_FILES['profile_photo'];
$maxBytes = 2 * 1024 * 1024; // same limit the form announces

if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    || !is_uploaded_file($file['tmp_name'])
    || $file['size'] > $maxBytes) {
    header("Location: $photoForm?success=2");
    exit;
}

// Allowed image types, detected from the file's bytes, and the extension each one gets
$allowed = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG => 'png',
    IMAGETYPE_WEBP => 'webp',
];
$info = @getimagesize($file['tmp_name']);
$extension = $info ? ($allowed[$info[2]] ?? null) : null;
if ($extension === null) {
    header("Location: $photoForm?success=2");
    exit;
}

$directoryDestination = ($_SESSION['log_type'] ?? '') === 'A'
    ? 'resources/dashboard/assets/img/administrator/'
    : 'resources/dashboard/assets/img/researcher/';
if (!is_dir($directoryDestination)) {
    mkdir($directoryDestination, 0755, true);
}

$filename = bin2hex(random_bytes(16)) . '.' . $extension;
if (!move_uploaded_file($file['tmp_name'], $directoryDestination . $filename)) {
    header("Location: $photoForm?success=0");
    exit;
}

$_SESSION['photo'] = $filename;
// The photo always belongs to the signed-in user (updateMyPhoto reads the session)
header('Location: /dashboard/administrator/update-foto/' . (int) $_SESSION['log_id']);
exit;
