<?php
/**
 * Public Blogs API
 * Read-only access for frontend
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = $_GET['action'] ?? 'latest';
$response = ['success' => false, 'data' => []];

try {
    switch ($action) {
        case 'latest':
            // Get latest 3 published blogs
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;
            $query = "SELECT b.id, b.title, b.slug, b.excerpt, b.featured_image, b.published_at, b.reading_time, 
                      c.name as category_name, c.color as category_color 
                      FROM blogs b 
                      LEFT JOIN categories c ON b.category_id = c.id 
                      WHERE b.status = 'published' 
                      ORDER BY b.published_at DESC 
                      LIMIT ?";

            $blogs = fetch_all($query, [$limit], 'i');

            if ($blogs === false) {
                throw new Exception("Database query failed. Please check if tables exist.");
            }

            // Add full image URL
            foreach ($blogs as &$blog) {
                if ($blog['featured_image']) {
                    $blog['featured_image_url'] = SITE_URL . '/' . $blog['featured_image'];
                }
            }

            $response = ['success' => true, 'data' => $blogs];
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
