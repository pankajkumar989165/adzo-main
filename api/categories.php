<?php
/**
 * Categories API - CRUD Operations
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
            $categories = fetch_all("SELECT * FROM categories ORDER BY display_order ASC, name ASC");
            $response = ['success' => true, 'data' => $categories];
            break;

        case 'get':
            require_auth();
            $id = $_GET['id'] ?? 0;
            $category = fetch_one("SELECT * FROM categories WHERE id = ?", [$id], 'i');
            $response = $category ? ['success' => true, 'data' => $category] : ['success' => false, 'message' => 'Category not found'];
            break;

        case 'create':
            require_auth();
            $name = sanitize_input($_POST['name'] ?? '');
            $slug = !empty($_POST['slug']) ? sanitize_input($_POST['slug']) : generate_slug($name);
            $description = sanitize_input($_POST['description'] ?? '');
            $color = sanitize_input($_POST['color'] ?? '#6366f1');

            if (empty($name)) {
                $response = ['success' => false, 'message' => 'Category name is required'];
                break;
            }

            $stmt = execute_query(
                "INSERT INTO categories (name, slug, description, color) VALUES (?, ?, ?, ?)",
                [$name, $slug, $description, $color],
                'ssss'
            );

            $response = $stmt ? ['success' => true, 'message' => 'Category created successfully'] : ['success' => false, 'message' => 'Failed to create category'];
            break;

        case 'update':
            require_auth();
            $id = intval($_POST['id'] ?? 0);
            $name = sanitize_input($_POST['name'] ?? '');
            $slug = sanitize_input($_POST['slug'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            $color = sanitize_input($_POST['color'] ?? '#6366f1');

            if (empty($id) || empty($name)) {
                $response = ['success' => false, 'message' => 'Invalid request'];
                break;
            }

            $stmt = execute_query(
                "UPDATE categories SET name = ?, slug = ?, description = ?, color = ? WHERE id = ?",
                [$name, $slug, $description, $color, $id],
                'ssssi'
            );

            $response = $stmt ? ['success' => true, 'message' => 'Category updated successfully'] : ['success' => false, 'message' => 'Failed to update category'];
            break;

        case 'delete':
            require_auth();
            $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Category ID is required'];
                break;
            }

            // Check if category has blogs
            $count = fetch_one("SELECT COUNT(*) as total FROM blogs WHERE category_id = ?", [$id], 'i');
            if ($count['total'] > 0) {
                $response = ['success' => false, 'message' => 'Cannot delete category with existing blogs'];
                break;
            }

            $stmt = execute_query("DELETE FROM categories WHERE id = ?", [$id], 'i');
            $response = $stmt ? ['success' => true, 'message' => 'Category deleted successfully'] : ['success' => false, 'message' => 'Failed to delete category'];
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Categories API Error: ' . $e->getMessage());
}

echo json_encode($response);
exit;
?>