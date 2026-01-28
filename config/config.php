<?php
/**
 * Site Configuration for Aadzo Digital
 */

// Site Settings
define('SITE_NAME', 'Aadzo Digital');
// Automatically detect the protocol (http/https) and domain
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
define('SITE_URL', $protocol . "://" . $domain);
define('SITE_EMAIL', 'info@adzodigital.com');

// Path Settings
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads/blog-images/');
define('UPLOAD_URL', SITE_URL . '/uploads/blog-images/');

// Admin Settings
define('ADMIN_URL', SITE_URL . '/admin');
define('SESSION_TIMEOUT', 3600); // 1 hour

// Pagination
define('BLOGS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Security
define('CSRF_TOKEN_EXPIRY', 3600);

// SEO Settings
define('DEFAULT_META_DESCRIPTION', 'Aadzo Digital - Your trusted partner for digital marketing, web development, and SEO services.');
define('DEFAULT_META_KEYWORDS', 'digital marketing, web development, SEO, social media, branding');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/error.log');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>