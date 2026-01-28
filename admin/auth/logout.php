<?php
/**
 * Admin Logout Handler
 */

require_once __DIR__ . '/../../includes/auth.php';

logout_user();

header('Location: ../index.php?success=You have been logged out successfully');
exit;
?>