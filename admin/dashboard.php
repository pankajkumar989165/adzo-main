<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Require authentication
require_auth();

$user = get_auth_user();

// Get statistics
$stats = [
    'total_blogs' => fetch_one("SELECT COUNT(*) as count FROM blogs")['count'],
    'published_blogs' => fetch_one("SELECT COUNT(*) as count FROM blogs WHERE status = 'published'")['count'],
    'total_views' => fetch_one("SELECT SUM(views) as total FROM blogs")['total'] ?? 0,
    'categories' => fetch_one("SELECT COUNT(*) as count FROM categories")['count'],
    'tags' => fetch_one("SELECT COUNT(*) as count FROM tags")['count']
];

// Get recent blogs
$recent_blogs = fetch_all("SELECT b.*, c.name as category_name 
                          FROM blogs b 
                          LEFT JOIN categories c ON b.category_id = c.id 
                          ORDER BY b.created_at DESC LIMIT 5");

$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                    <path fill-rule="evenodd"
                        d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <h3>
                <?php echo $stats['total_blogs']; ?>
            </h3>
            <p class="stat-label">Total Blog Posts</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <h3>
                <?php echo $stats['published_blogs']; ?>
            </h3>
            <p class="stat-label">Published Blogs</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path fill-rule="evenodd"
                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                        clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <h3>
                <?php echo number_format($stats['total_views']); ?>
            </h3>
            <p class="stat-label">Total Views</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                </svg>
            </div>
        </div>
        <div class="stat-value">
            <h3>
                <?php echo $stats['categories']; ?>
            </h3>
            <p class="stat-label">Categories</p>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h2>Recent Blog Posts</h2>
        <a href="blog-editor.php" class="btn btn-primary btn-sm">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            <span>New Post</span>
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Views</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent_blogs)): ?>
                <tr>
                    <td colspan="6" class="text-center">No blog posts yet. Create your first post!</td>
                </tr>
            <?php else: ?>
                <?php foreach ($recent_blogs as $blog): ?>
                    <tr>
                        <td><strong>
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </strong></td>
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
                            <?php echo time_ago($blog['created_at']); ?>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="blog-editor.php?id=<?php echo $blog['id']; ?>"
                                    class="btn btn-sm btn-secondary">Edit</a>
                                <a href="../blog/<?php echo $blog['slug']; ?>" target="_blank"
                                    class="btn btn-sm btn-secondary">View</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>