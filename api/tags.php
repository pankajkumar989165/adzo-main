<?php
/**
 * Tags API - CRUD Operations
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    switch ($action) {
        case 'list':
            $tags = fetch_all("SELECT t.*, COUNT(bt.blog_id) as usage_count 
                              FROM tags t 
                              LEFT JOIN blog_tags bt ON t.id = bt.tag_id 
                              GROUP BY t.id 
                              ORDER BY t.name ASC");
            $response = ['success' => true, 'data' => $tags];
            break;

        case 'autocomplete':
            $search = sanitize_input($_GET['q'] ?? '');
            $query = "SELECT * FROM tags WHERE name LIKE ? LIMIT 10";
            $tags = fetch_all($query, ["%{$search}%"], 's');
            $response = ['success' => true, 'data' => $tags];
            break;

        case 'create':
            require_auth();
            $name = sanitize_input($_POST['name'] ?? '');
            $slug = !empty($_POST['slug']) ? sanitize_input($_POST['slug']) : generate_slug($name);

            if (empty($name)) {
                $response = ['success' => false, 'message' => 'Tag name is required'];
                break;
            }

            // Check if tag already exists
            $existing = fetch_one("SELECT id FROM tags WHERE slug = ?", [$slug], 's');
            if ($existing) {
                $response = ['success' => false, 'message' => 'Tag already exists', 'data' => $existing];
                break;
            }

            $stmt = execute_query(
                "INSERT INTO tags (name, slug) VALUES (?, ?)",
                [$name, $slug],
                'ss'
            );

            if ($stmt) {
                global $conn;
                $response = ['success' => true, 'message' => 'Tag created successfully', 'data' => ['id' => $conn->insert_id]];
            } else {
                $response = ['success' => false, 'message' => 'Failed to create tag'];
            }
            break;

        case 'update':
            require_auth();
            $id = intval($_POST['id'] ?? 0);
            $name = sanitize_input($_POST['name'] ?? '');
            $slug = sanitize_input($_POST['slug'] ?? '');

            if (empty($id) || empty($name)) {
                $response = ['success' => false, 'message' => 'Invalid request'];
                break;
            }

            $stmt = execute_query(
                "UPDATE tags SET name = ?, slug = ? WHERE id = ?",
                [$name, $slug, $id],
                'ssi'
            );

            $response = $stmt ? ['success' => true, 'message' => 'Tag updated successfully'] : ['success' => false, 'message' => 'Failed to update tag'];
            break;

        case 'delete':
            require_auth();
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Tag ID is required'];
                break;
            }

            $stmt = execute_query("DELETE FROM tags WHERE id = ?", [$id], 'i');
            $response = $stmt ? ['success' => true, 'message' => 'Tag deleted successfully'] : ['success' => false, 'message' => 'Failed to delete tag'];
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Tags API Error: ' . $e->getMessage());
}

echo json_encode($response);
exit;
?>