<?php
/**
 * Database Configuration for Aadzo Digital Blog System
 * GoDaddy Shared Hosting - MySQL Connection
 */

// Database credentials
require_once __DIR__ . '/../../db_secure.php';

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("Connection failed. Please contact the administrator.");
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Function to sanitize input
function sanitize_input($data)
{
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $conn->real_escape_string($data);
}

// Function to execute prepared statements safely
function execute_query($query, $params = [], $types = '')
{
    global $conn;

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Query preparation failed: " . $conn->error);
        return false;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $result = $stmt->execute();

    if (!$result) {
        error_log("Query execution failed: " . $stmt->error);
        return false;
    }

    return $stmt;
}

// Function to fetch all results
function fetch_all($query, $params = [], $types = '')
{
    $stmt = execute_query($query, $params, $types);
    if (!$stmt)
        return false;

    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $data;
}

// Function to fetch single row
function fetch_one($query, $params = [], $types = '')
{
    $stmt = execute_query($query, $params, $types);
    if (!$stmt)
        return false;

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    return $data;
}