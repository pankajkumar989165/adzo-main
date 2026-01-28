<?php
/**
 * Blog Listing Page - All Published Blogs
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = BLOGS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$category_slug = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$where_clauses = ["b.status = 'published'"];
$params = [];
$types = '';

if (!empty($category_slug)) {
    $where_clauses[] = "c.slug = ?";
    $params[] = $category_slug;
    $types .= 's';
}

if (!empty($search)) {
    $where_clauses[] = "(b.title LIKE ? OR b.content LIKE ?)";
    $search_term = "%{$search}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

$where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM blogs b LEFT JOIN categories c ON b.category_id = c.id $where_sql";
$count_stmt = execute_query($count_query, $params, $types);

if ($count_stmt === false) {
    die("Error: Could not fetch blogs. Please ensure you have imported the database schema. (Check error logs for details)");
}

$count_result = $count_stmt->get_result()->fetch_assoc();
$total = $count_result['total'];
$total_pages = ceil($total / $per_page);

// Get blogs
$query = "SELECT b.*, c.name as category_name, c.slug as category_slug, c.color as category_color, u.full_name as author_name 
          FROM blogs b 
          LEFT JOIN categories c ON b.category_id = c.id 
          LEFT JOIN users u ON b.author_id = u.id 
          $where_sql 
          ORDER BY b.published_at DESC, b.created_at DESC 
          LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$blogs = fetch_all($query, $params, $types);

// Get all categories for sidebar
$categories = fetch_all("SELECT c.*, COUNT(b.id) as blog_count 
                        FROM categories c 
                        LEFT JOIN blogs b ON c.id = b.category_id AND b.status = 'published'
                        GROUP BY c.id 
                        ORDER BY c.name ASC");

// Get popular tags
$popular_tags = fetch_all("SELECT t.*, COUNT(bt.blog_id) as usage_count 
                           FROM tags t 
                           INNER JOIN blog_tags bt ON t.id = bt.tag_id 
                           INNER JOIN blogs b ON bt.blog_id = b.id AND b.status = 'published'
                           GROUP BY t.id 
                           ORDER BY usage_count DESC 
                           LIMIT 20");

$current_category = null;
if (!empty($category_slug)) {
    $current_category = fetch_one("SELECT * FROM categories WHERE slug = ?", [$category_slug], 's');
}

$page_title = $current_category ? $current_category['name'] . ' - Blog' : 'Blog';
$meta_description = $current_category ? $current_category['description'] : 'Explore our latest articles on digital marketing, web development, SEO, and more.';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/blog-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
</head>

<body>
    <header class="blog-header">
        <div class="container">
            <nav class="navbar">
                <a href="<?php echo SITE_URL; ?>" class="logo">Aadzo Digital</a>
                <div class="nav-links">
                    <a href="<?php echo SITE_URL; ?>">Home</a>
                    <a href="<?php echo SITE_URL; ?>/blog.php" class="active">Blog</a>
                    <a href="<?php echo SITE_URL; ?>/pages/services.html">Services</a>
                    <a href="<?php echo SITE_URL; ?>/#contact">Contact</a>
                </div>
            </nav>
        </div>
    </header>

    <section class="blog-hero">
        <div class="container">
            <h1><?php echo $current_category ? htmlspecialchars($current_category['name']) : 'Our Blog'; ?></h1>
            <p><?php echo $current_category ? htmlspecialchars($current_category['description']) : 'Insights, tips, and strategies for digital success'; ?>
            </p>

            <div class="search-box">
                <form method="GET" action="blog.php">
                    <?php if ($category_slug): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_slug); ?>">
                    <?php endif; ?>
                    <input type="text" name="search" placeholder="Search articles..."
                        value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="blog-section">
        <div class="container">
            <div class="blog-layout">
                <main class="blog-main">
                    <?php if (empty($blogs)): ?>
                        <div class="no-results">
                            <svg width="64" height="64" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            <h3>No blogs found</h3>
                            <p>Try adjusting your search or filter to find what you're looking for.</p>
                            <a href="blog.php" class="btn-primary">View All Blogs</a>
                        </div>
                    <?php else: ?>
                        <div class="blog-grid">
                            <?php foreach ($blogs as $blog): ?>
                                <article class="blog-card">
                                    <?php if ($blog['featured_image']): ?>
                                        <div class="blog-card-image">
                                            <img src="<?php echo SITE_URL . '/' . $blog['featured_image']; ?>"
                                                alt="<?php echo htmlspecialchars($blog['title']); ?>">
                                            <?php if ($blog['category_name']): ?>
                                                <span class="category-badge" style="background: <?php echo $blog['category_color']; ?>">
                                                    <?php echo htmlspecialchars($blog['category_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="blog-card-content">
                                        <div class="blog-meta">
                                            <span><?php echo format_date($blog['published_at'] ?? $blog['created_at']); ?></span>
                                            <span>•</span>
                                            <span><?php echo $blog['reading_time']; ?> min read</span>
                                        </div>
                                        <h2>
                                            <a href="<?php echo SITE_URL; ?>/blog/<?php echo $blog['slug']; ?>">
                                                <?php echo htmlspecialchars($blog['title']); ?>
                                            </a>
                                        </h2>
                                        <p><?php echo htmlspecialchars($blog['excerpt']); ?></p>
                                        <a href="<?php echo SITE_URL; ?>/blog/<?php echo $blog['slug']; ?>" class="read-more">
                                            Read More →
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo ($page - 1); ?>&category=<?php echo $category_slug; ?>&search=<?php echo urlencode($search); ?>"
                                        class="page-link">Previous</a>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&category=<?php echo $category_slug; ?>&search=<?php echo urlencode($search); ?>"
                                        class="page-link <?php echo ($i === $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo ($page + 1); ?>&category=<?php echo $category_slug; ?>&search=<?php echo urlencode($search); ?>"
                                        class="page-link">Next</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </main>

                <aside class="blog-sidebar">
                    <div class="sidebar-widget">
                        <h3>Categories</h3>
                        <ul class="category-list">
                            <li>
                                <a href="blog.php" class="<?php echo empty($category_slug) ? 'active' : ''; ?>">
                                    All Categories
                                    <span class="count"><?php echo $total; ?></span>
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a href="?category=<?php echo $cat['slug']; ?>"
                                        class="<?php echo ($category_slug === $cat['slug']) ? 'active' : ''; ?>">
                                        <span class="cat-dot" style="background: <?php echo $cat['color']; ?>"></span>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                        <span class="count"><?php echo $cat['blog_count']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="sidebar-widget">
                        <h3>Popular Tags</h3>
                        <div class="tag-cloud">
                            <?php foreach ($popular_tags as $tag): ?>
                                <a href="?search=<?php echo urlencode($tag['name']); ?>" class="tag">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <footer class="blog-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>