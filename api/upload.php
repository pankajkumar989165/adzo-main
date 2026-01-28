<?php
/**
 * Image Upload Handler
 * Handles blog featured image and SEO image uploads
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Require authentication
require_auth();

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if (!isset($_FILES['image'])) {
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['image'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload failed with error code: ' . $file['error']);
    }

    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File size exceeds maximum allowed size of ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
    }

    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
        throw new Exception('Invalid file type. Only JPG, PNG, WebP, and GIF images are allowed');
    }

    // Create upload directory if it doesn't exist
    if (!file_exists(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'blog-' . time() . '-' . uniqid() . '.' . $extension;
    $filepath = UPLOAD_PATH . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to save uploaded file');
    }

    // Optimize image (optional - basic optimization)
    optimize_image($filepath, $mime_type);

    $url = UPLOAD_URL . $filename;

    $response = [
        'success' => true,
        'message' => 'Image uploaded successfully',
        'data' => [
            'filename' => $filename,
            'url' => $url,
            'path' => 'uploads/blog-images/' . $filename,
            'size' => filesize($filepath)
        ]
    ];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Upload Error: ' . $e->getMessage());
}

echo json_encode($response);
exit;

/**
 * Basic image optimization
 */
function optimize_image($filepath, $mime_type)
{
    $image = null;

    switch ($mime_type) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($filepath);
            if ($image) {
                imagejpeg($image, $filepath, 85); // 85% quality
            }
            break;

        case 'image/png':
            $image = @imagecreatefrompng($filepath);
            if ($image) {
                imagepng($image, $filepath, 8); // Compression level 8
            }
            break;

        case 'image/webp':
            $image = @imagecreatefromwebp($filepath);
            if ($image) {
                imagewebp($image, $filepath, 85);
            }
            break;
    }

    if ($image) {
        imagedestroy($image);
    }
}
?>