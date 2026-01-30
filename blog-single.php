<?php
/**
 * Single Blog Post Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$slug = $_GET['slug'] ?? '';
$preview = isset($_GET['preview']) && is_logged_in();

if (empty($slug)) {
    header('Location: ' . SITE_URL . '/blog.php');
    exit;
}

// Prepare query
$status_check = $preview ? "" : "AND b.status = 'published'";
$query = "SELECT b.*, c.name as category_name, c.slug as category_slug, c.color as category_color, u.full_name as author_name,
          sm.meta_title, sm.meta_description, sm.meta_keywords, sm.og_image, sm.canonical_url, sm.og_title, sm.og_description
          FROM blogs b 
          LEFT JOIN categories c ON b.category_id = c.id 
          LEFT JOIN users u ON b.author_id = u.id 
          LEFT JOIN seo_meta sm ON b.id = sm.blog_id
          WHERE b.slug = ? $status_check";

$blog = fetch_one($query, [$slug], 's');

if (!$blog) {
    // Show 404 page
    http_response_code(404);
    include __DIR__ . '/404.php'; // You might need to create this later
    exit;
}

// Get tags
$tags = fetch_all("SELECT t.* FROM tags t 
                  INNER JOIN blog_tags bt ON t.id = bt.tag_id 
                  WHERE bt.blog_id = ?", [$blog['id']], 'i');

// Increment views (naive implementation - could be improved with cookie check)
if (!$preview) {
    execute_query("UPDATE blogs SET views = views + 1 WHERE id = ?", [$blog['id']], 'i');
}

// Get related posts
$related_posts = fetch_all("SELECT b.*, c.name as category_name 
                           FROM blogs b 
                           LEFT JOIN categories c ON b.category_id = c.id 
                           WHERE b.category_id = ? AND b.id != ? AND b.status = 'published'
                           ORDER BY RAND() LIMIT 3",
    [$blog['category_id'], $blog['id']],
    'ii'
);

// SEO Setup
$page_title = $blog['meta_title'] ?: $blog['title'] . ' - ' . SITE_NAME;
$meta_description = $blog['meta_description'] ?: $blog['excerpt'];
$meta_keywords = $blog['meta_keywords'] ?: '';
$canonical_url = $blog['canonical_url'] ?: SITE_URL . '/blog/' . $blog['slug'];
$og_image = $blog['og_image'] ? (strpos($blog['og_image'], 'http') === 0 ? $blog['og_image'] : SITE_URL . '/' . $blog['og_image']) : ($blog['featured_image'] ? SITE_URL . '/' . $blog['featured_image'] : '');
$og_type = 'article';

$json_ld = [
    "@context" => "https://schema.org",
    "@type" => "BlogPosting",
    "headline" => $blog['title'],
    "image" => $og_image,
    "author" => [
        "@type" => "Person",
        "name" => $blog['author_name']
    ],
    "publisher" => [
        "@type" => "Organization",
        "name" => SITE_NAME,
        "logo" => [
            "@type" => "ImageObject",
            "url" => SITE_URL . "/assets/img/logo.png"
        ]
    ],
    "datePublished" => date('c', strtotime($blog['published_at'])),
    "dateModified" => date('c', strtotime($blog['updated_at']))
];

$extra_head_content = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/blog-style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script type="application/ld+json">' . json_encode($json_ld) . '</script>';

require_once __DIR__ . '/includes/header.php';
?>
<!-- Single Post Header Override -->
<!-- header removed -->

<main class="single-post-container">
    <article>
        <header class="post-header">
            <?php if ($blog['category_name']): ?>
                <a href="<?php echo SITE_URL; ?>/blog.php?category=<?php echo $blog['category_slug']; ?>"
                    class="category-badge"
                    style="background: <?php echo $blog['category_color']; ?>; position: relative; display: inline-block; margin-bottom: 1.5rem; top: 0; left: 0;">
                    <?php echo htmlspecialchars($blog['category_name']); ?>
                </a>
            <?php endif; ?>

            <h1 class="post-title">
                <?php echo htmlspecialchars($blog['title']); ?>
            </h1>

            <div class="post-meta">
                <span>By
                    <?php echo htmlspecialchars($blog['author_name']); ?>
                </span>
                <span>•</span>
                <span>
                    <?php echo format_date($blog['published_at']); ?>
                </span>
                <span>•</span>
                <span>
                    <?php echo $blog['reading_time']; ?> min read
                </span>
            </div>

            <?php if (!empty($tags)): ?>
                <div class="post-tags">
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?php echo SITE_URL; ?>/blog.php?search=<?php echo urlencode($tag['name']); ?>" class="tag">#
                            <?php echo htmlspecialchars($tag['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </header>

        <?php if ($blog['featured_image']): ?>
            <div class="featured-image-container">
                <img src="<?php echo SITE_URL . '/' . $blog['featured_image']; ?>"
                    alt="<?php echo htmlspecialchars($blog['title']); ?>">
            </div>
        <?php endif; ?>

        <div class="post-content">
            <?php echo $blog['content']; ?>
        </div>

        <div class="share-section">
            <h3>Share this article</h3>
            <div class="share-buttons">
                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($blog['title']); ?>&url=<?php echo urlencode($canonical_url); ?>"
                    target="_blank" class="share-btn share-twitter">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                    </svg>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($canonical_url); ?>"
                    target="_blank" class="share-btn share-facebook">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.791-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($canonical_url); ?>&title=<?php echo urlencode($blog['title']); ?>"
                    target="_blank" class="share-btn share-linkedin">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                    </svg>
                </a>
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($blog['title'] . ' ' . $canonical_url); ?>"
                    target="_blank" class="share-btn share-whatsapp">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                    </svg>
                </a>
            </div>
        </div>

        <?php if (!empty($related_posts)): ?>
            <div class="related-posts" style="margin-top: 4rem;">
                <h3 style="margin-bottom: 2rem; font-size: 1.5rem;">You might also like</h3>
                <div class="blog-grid" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
                    <?php foreach ($related_posts as $post): ?>
                        <article class="blog-card">
                            <?php if ($post['featured_image']): ?>
                                <div class="blog-card-image" style="padding-top: 55%;">
                                    <img src="<?php echo SITE_URL . '/' . $post['featured_image']; ?>"
                                        alt="<?php echo htmlspecialchars($post['title']); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="blog-card-content" style="padding: 1.25rem;">
                                <div class="blog-meta" style="font-size: 0.8rem;">
                                    <span>
                                        <?php echo format_date($post['published_at']); ?>
                                    </span>
                                </div>
                                <h2 style="font-size: 1.1rem; margin-bottom: 0.5rem;">
                                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo $post['slug']; ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h2>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </article>
</main>

<footer class="blog-footer">
    <div class="container">
        <p>&copy;
            <?php echo date('Y'); ?>
            <?php echo SITE_NAME; ?>. All rights reserved.
        </p>
    </div>
</footer>

<script src="<?php echo SITE_URL; ?>/assets/js/blog-script.js"></script>
</body>

</html>