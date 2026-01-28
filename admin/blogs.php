<?php
/**
 * All Blogs - Admin Blog List
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Require authentication
require_auth();
$user = get_auth_user();

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

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

if (!empty($category_filter)) {
    $where_clauses[] = "b.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}

if (!empty($status_filter)) {
    $where_clauses[] = "b.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM blogs b $where_sql";
$count_stmt = execute_query($count_query, $params, $types);
$count_result = $count_stmt->get_result()->fetch_assoc();
$total = $count_result['total'];
$total_pages = ceil($total / $per_page);

// Get blogs
$query = "SELECT b.*, c.name as category_name, u.full_name as author_name 
          FROM blogs b 
          LEFT JOIN categories c ON b.category_id = c.id 
          LEFT JOIN users u ON b.author_id = u.id 
          $where_sql 
          ORDER BY b.created_at DESC 
          LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$blogs = fetch_all($query, $params, $types);

$categories = fetch_all("SELECT * FROM categories ORDER BY name ASC");

$page_title = 'All Blogs';
include __DIR__ . '/includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <h2>All Blog Posts (
            <?php echo $total; ?>)
        </h2>
        <a href="blog-editor.php" class="btn btn-primary btn-sm">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            New Post
        </a>
    </div>

    <!-- Filters -->
    <div style="padding: var(--spacing-lg); border-bottom: 1px solid var(--glass-border);">
        <form method="GET" class="flex gap-3" style="align-items: flex-end;">
            <div class="form-group" style="flex: 1; margin: 0;">
                <label for="search" style="font-size: 0.875rem;">Search</label>
                <input type="text" id="search" name="search" placeholder="Search blogs..."
                    value="<?php echo htmlspecialchars($search); ?>" style="padding: 0.5rem 1rem;">
            </div>
            <div class="form-group" style="width: 200px; margin: 0;">
                <label for="category" style="font-size: 0.875rem;">Category</label>
                <select id="category" name="category" style="padding: 0.5rem 1rem;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="width: 150px; margin: 0;">
                <label for="status" style="font-size: 0.875rem;">Status</label>
                <select id="status" name="status" style="padding: 0.5rem 1rem;">
                    <option value="">All Status</option>
                    <option value="published" <?php echo ($status_filter === 'published') ? 'selected' : ''; ?>>Published
                    </option>
                    <option value="draft" <?php echo ($status_filter === 'draft') ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <?php if (!empty($search) || !empty($category_filter) || !empty($status_filter)): ?>
                <a href="blogs.php" class="btn btn-secondary btn-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Views</th>
                <th>Date</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($blogs)): ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: var(--spacing-xl);">
                        <p style="color: var(--text-muted); margin-bottom: var(--spacing-md);">No blog posts found</p>
                        <a href="blog-editor.php" class="btn btn-primary btn-sm">Create Your First Post</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($blogs as $blog): ?>
                    <tr>
                        <td>
                            <strong>
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </strong>
                            <?php if ($blog['is_featured']): ?>
                                <span class="badge badge-info" style="margin-left: 0.5rem;">Featured</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($blog['category_name'] ?? 'Uncategorized'); ?>
                        </td>
                        <td>
                            <?php if ($blog['status'] === 'published'): ?>
                                <span class="badge badge-success">Published</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo number_format($blog['views']); ?>
                        </td>
                        <td>
                            <?php echo format_date($blog['created_at'], 'M d, Y'); ?>
                        </td>
                        <td style="text-align: right;">
                            <div class="flex gap-2" style="justify-content: flex-end;">
                                <a href="blog-editor.php?id=<?php echo $blog['id']; ?>"
                                    class="btn btn-sm btn-secondary">Edit</a>
                                <a href="<?php echo SITE_URL; ?>/blog/<?php echo $blog['slug']; ?>" target="_blank"
                                    class="btn btn-sm btn-secondary">View</a>
                                <button
                                    onclick="deleteBlog(<?php echo $blog['id']; ?>, '<?php echo htmlspecialchars($blog['title']); ?>')"
                                    class="btn btn-sm btn-danger">Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div
            style="padding: var(--spacing-lg); border-top: 1px solid var(--glass-border); display: flex; justify-content: center; gap: 0.5rem;">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>"
                    class="btn btn-sm btn-secondary">Previous</a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>"
                    class="btn btn-sm <?php echo ($i === $page) ? 'btn-primary' : 'btn-secondary'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>"
                    class="btn btn-sm btn-secondary">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    async function deleteBlog(id, title) {
        if (!confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            const response = await fetch('../api/blogs.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error deleting blog: ' + error.message);
        }
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>