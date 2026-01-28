<?php
/**
 * Password Reset Script
 * Upload and visit this file to reset admin password
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$password = 'Admin@123';
$hash = password_hash($password, PASSWORD_DEFAULT);

// Update commands
$sql = "UPDATE users SET password = ? WHERE email = 'admin@adzodigital.com'";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $hash);

    if ($stmt->execute()) {
        echo "<h1 style='color: green'>✅ Password Updated Successfully</h1>";
        echo "<p>Admin user 'admin@adzodigital.com' password set to: <strong>$password</strong></p>";
        echo "<p>New Hash: $hash</p>";
    } else {
        echo "<h1 style='color: red'>❌ Update Failed</h1>";
        echo "<p>" . $stmt->error . "</p>";
    }
} else {
    echo "Prepare failed: " . $conn->error;
}
?>