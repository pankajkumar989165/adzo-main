<?php
/**
 * Blog API - CRUD Operations
 * Handles all blog-related operations
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Require authentication for all API calls
require_auth();

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    switch ($action) {
        case 'list':
            $response = list_blogs();
            break;

        case 'get':
            $response = get_blog();
            break;

        case 'create':
            $response = create_blog();
            break;

        case 'update':
            $response = update_blog();
            break;

        case 'delete':
            $response = delete_blog();
            break;

        case 'toggle_status':
            $response = toggle_blog_status();
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Blog API Error: ' . $e->getMessage());
}

echo json_encode($response);
exit;

/**
 * List all blogs with pagination and filtering
 */
function list_blogs()
{
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $per_page;

    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? '';
    $order_by = $_GET['order_by'] ?? 'created_at';
    $order_dir = $_GET['order_dir'] ?? 'DESC';

    $where_clauses = [];
    $params = [];
    $types = '';

    if (!empty($search)) {
        $where_clauses[] = "(b.title LIKE ? OR b.content LIKE ?)";
        $search_term = "%{$search}%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'ss';
    }

    if (!empty($category)) {
        $where_clauses[] = "b.category_id = ?";
        $params[] = $category;
        $types .= 'i';
    }

    if (!empty($status)) {
        $where_clauses[] = "b.status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM blogs b $where_sql";
    $count_stmt = execute_query($count_query, $params, $types);
    $count_result = $count_stmt->get_result()->fetch_assoc();
    $total = $count_result['total'];

    // Get blogs
    $query = "SELECT b.*, c.name as category_name, u.full_name as author_name 
              FROM blogs b 
              LEFT JOIN categories c ON b.category_id = c.id 
              LEFT JOIN users u ON b.author_id = u.id 
              $where_sql 
              ORDER BY b.$order_by $order_dir 
              LIMIT ? OFFSET ?";

    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';

    $blogs = fetch_all($query, $params, $types);

    return [
        'success' => true,
        'data' => [
            'blogs' => $blogs,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => ceil($total / $per_page)
            ]
        ]
    ];
}

/**
 * Get single blog by ID
 */
function get_blog()
{
    $id = $_GET['id'] ?? 0;

    if (empty($id)) {
        return ['success' => false, 'message' => 'Blog ID is required'];
    }

    $query = "SELECT b.*, c.name as category_name, u.full_name as author_name,
              sm.meta_title, sm.meta_description, sm.meta_keywords, sm.og_image, sm.canonical_url
              FROM blogs b 
              LEFT JOIN categories c ON b.category_id = c.id 
              LEFT JOIN users u ON b.author_id = u.id 
              LEFT JOIN seo_meta sm ON b.id = sm.blog_id
              WHERE b.id = ?";

    $blog = fetch_one($query, [$id], 'i');

    if (!$blog) {
        return ['success' => false, 'message' => 'Blog not found'];
    }

    // Get tags
    $tags_query = "SELECT t.* FROM tags t 
                   INNER JOIN blog_tags bt ON t.id = bt.tag_id 
                   WHERE bt.blog_id = ?";
    $blog['tags'] = fetch_all($tags_query, [$id], 'i');

    return ['success' => true, 'data' => $blog];
}

/**
 * Create new blog
 */
function create_blog()
{
    global $conn;

    $title = sanitize_input($_POST['title'] ?? '');
    $slug = !empty($_POST['slug']) ? sanitize_input($_POST['slug']) : generate_slug($title);
    $excerpt = sanitize_input($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? ''; // Don't sanitize HTML content
    $category_id = intval($_POST['category_id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? 'draft');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $featured_image = sanitize_input($_POST['featured_image'] ?? '');
    $author_id = $_SESSION['user_id'];

    // SEO fields
    $meta_title = sanitize_input($_POST['meta_title'] ?? '');
    $meta_description = sanitize_input($_POST['meta_description'] ?? '');
    $meta_keywords = sanitize_input($_POST['meta_keywords'] ?? '');
    $og_image = sanitize_input($_POST['og_image'] ?? '');
    $canonical_url = sanitize_input($_POST['canonical_url'] ?? '');

    $tags = $_POST['tags'] ?? [];

    if (empty($title) || empty($content)) {
        return ['success' => false, 'message' => 'Title and content are required'];
    }

    // Check if slug exists
    $slug_check = fetch_one("SELECT id FROM blogs WHERE slug = ?", [$slug], 's');
    if ($slug_check) {
        $slug = $slug . '-' . time();
    }

    $reading_time = calculate_reading_time($content);
    $published_at = ($status === 'published') ? 'NOW()' : 'NULL';

    // Insert blog
    $query = "INSERT INTO blogs (title, slug, excerpt, content, featured_image, author_id, category_id, status, is_featured, reading_time, published_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, " . ($status === 'published' ? 'NOW()' : 'NULL') . ")";

    $stmt = execute_query($query, [
        $title,
        $slug,
        $excerpt,
        $content,
        $featured_image,
        $author_id,
        $category_id,
        $status,
        $is_featured,
        $reading_time
    ], 'sssssiisii');

    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to create blog'];
    }

    $blog_id = $conn->insert_id;

    // Insert SEO meta
    if (!empty($meta_title) || !empty($meta_description)) {
        $seo_query = "INSERT INTO seo_meta (blog_id, meta_title, meta_description, meta_keywords, og_image, canonical_url) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        execute_query($seo_query, [$blog_id, $meta_title, $meta_description, $meta_keywords, $og_image, $canonical_url], 'isssss');
    }

    // Insert tags
    if (!empty($tags)) {
        foreach ($tags as $tag_id) {
            execute_query("INSERT INTO blog_tags (blog_id, tag_id) VALUES (?, ?)", [$blog_id, intval($tag_id)], 'ii');
        }
    }

    return ['success' => true, 'message' => 'Blog created successfully', 'data' => ['id' => $blog_id]];
}

/**
 * Update existing blog
 */
function update_blog()
{
    global $conn;

    $id = intval($_POST['id'] ?? 0);
    $title = sanitize_input($_POST['title'] ?? '');
    $slug = sanitize_input($_POST['slug'] ?? '');
    $excerpt = sanitize_input($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $category_id = intval($_POST['category_id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? 'draft');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $featured_image = sanitize_input($_POST['featured_image'] ?? '');

    // SEO fields
    $meta_title = sanitize_input($_POST['meta_title'] ?? '');
    $meta_description = sanitize_input($_POST['meta_description'] ?? '');
    $meta_keywords = sanitize_input($_POST['meta_keywords'] ?? '');
    $og_image = sanitize_input($_POST['og_image'] ?? '');
    $canonical_url = sanitize_input($_POST['canonical_url'] ?? '');

    $tags = $_POST['tags'] ?? [];

    if (empty($id) || empty($title) || empty($content)) {
        return ['success' => false, 'message' => 'Invalid request'];
    }

    $reading_time = calculate_reading_time($content);

    // Check if status changed to published
    $current_blog = fetch_one("SELECT status FROM blogs WHERE id = ?", [$id], 'i');
    $published_at_sql = '';
    if ($current_blog['status'] !== 'published' && $status === 'published') {
        $published_at_sql = ", published_at = NOW()";
    }

    // Update blog
    $query = "UPDATE blogs SET 
              title = ?, slug = ?, excerpt = ?, content = ?, 
              featured_image = ?, category_id = ?, status = ?, 
              is_featured = ?, reading_time = ?
              $published_at_sql
              WHERE id = ?";

    $stmt = execute_query($query, [
        $title,
        $slug,
        $excerpt,
        $content,
        $featured_image,
        $category_id,
        $status,
        $is_featured,
        $reading_time,
        $id
    ], 'sssssisiii');

    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to update blog'];
    }

    // Update or insert SEO meta
    $existing_seo = fetch_one("SELECT id FROM seo_meta WHERE blog_id = ?", [$id], 'i');

    if ($existing_seo) {
        $seo_query = "UPDATE seo_meta SET meta_title = ?, meta_description = ?, meta_keywords = ?, og_image = ?, canonical_url = ? WHERE blog_id = ?";
        execute_query($seo_query, [$meta_title, $meta_description, $meta_keywords, $og_image, $canonical_url, $id], 'sssssi');
    } else {
        $seo_query = "INSERT INTO seo_meta (blog_id, meta_title, meta_description, meta_keywords, og_image, canonical_url) VALUES (?, ?, ?, ?, ?, ?)";
        execute_query($seo_query, [$id, $meta_title, $meta_description, $meta_keywords, $og_image, $canonical_url], 'isssss');
    }

    // Update tags
    execute_query("DELETE FROM blog_tags WHERE blog_id = ?", [$id], 'i');
    if (!empty($tags)) {
        foreach ($tags as $tag_id) {
            execute_query("INSERT INTO blog_tags (blog_id, tag_id) VALUES (?, ?)", [$id, intval($tag_id)], 'ii');
        }
    }

    return ['success' => true, 'message' => 'Blog updated successfully'];
}

/**
 * Delete blog
 */
function delete_blog()
{
    $id = intval($_POST['id'] ?? $_GET['id'] ?? 0);

    if (empty($id)) {
        return ['success' => false, 'message' => 'Blog ID is required'];
    }

    $stmt = execute_query("DELETE FROM blogs WHERE id = ?", [$id], 'i');

    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to delete blog'];
    }

    return ['success' => true, 'message' => 'Blog deleted successfully'];
}

/**
 * Toggle blog status
 */
function toggle_blog_status()
{
    $id = intval($_POST['id'] ?? 0);

    if (empty($id)) {
        return ['success' => false, 'message' => 'Blog ID is required'];
    }

    $blog = fetch_one("SELECT status FROM blogs WHERE id = ?", [$id], 'i');
    $new_status = ($blog['status'] === 'published') ? 'draft' : 'published';

    $published_at_sql = ($new_status === 'published') ? ', published_at = NOW()' : '';

    $stmt = execute_query("UPDATE blogs SET status = ? $published_at_sql WHERE id = ?", [$new_status, $id], 'si');

    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to update status'];
    }

    return ['success' => true, 'message' => 'Status updated successfully', 'data' => ['status' => $new_status]];
}
?>