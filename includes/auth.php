<?php
/**
 * Authentication Helper Functions
 * Session management and user authentication
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Check if user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

/**
 * Require authentication (redirect if not logged in)
 */
function require_auth()
{
    if (!is_logged_in()) {
        header('Location: ' . ADMIN_URL . '/index.php?error=Please login to continue');
        exit;
    }

    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        logout_user();
        header('Location: ' . ADMIN_URL . '/index.php?error=Session expired. Please login again');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

/**
 * Authenticate user credentials
 */
function authenticate_user($email, $password)
{
    global $conn;

    $email = sanitize_input($email);

    $query = "SELECT id, username, email, password, full_name, role, avatar FROM users WHERE email = ? AND status = 'active' LIMIT 1";
    $user = fetch_one($query, [$email], 's');

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_avatar'] = $user['avatar'];
        $_SESSION['last_activity'] = time();

        // Update last login
        $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
        execute_query($update_query, [$user['id']], 'i');

        return true;
    }

    return false;
}

/**
 * Logout user
 */
function logout_user()
{
    session_unset();
    session_destroy();
}

/**
 * Get current user data
 */
function get_auth_user()
{
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role'],
        'avatar' => $_SESSION['user_avatar']
    ];
}

/**
 * Check if user has specific role
 */
function has_role($role)
{
    return is_logged_in() && $_SESSION['user_role'] === $role;
}

/**
 * Generate CSRF Token
 */
function generate_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token)
{
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }

    // Check if token expired
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate URL-friendly slug
 */
function generate_slug($text)
{
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

/**
 * Calculate reading time
 */
function calculate_reading_time($content)
{
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Average reading speed: 200 words per minute
    return max(1, $reading_time);
}

/**
 * Format date for display
 */
function format_date($date, $format = 'M d, Y')
{
    return date($format, strtotime($date));
}

/**
 * Time ago format
 */
function time_ago($datetime)
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60)
        return 'Just now';
    if ($diff < 3600)
        return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400)
        return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800)
        return floor($diff / 86400) . ' days ago';

    return date('M d, Y', $timestamp);
}
?>