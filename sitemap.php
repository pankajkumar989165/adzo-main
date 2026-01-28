<?php
/**
 * XML Sitemap Generator
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/xml; charset=utf-8');

// Get all published blogs
$blogs = fetch_all("SELECT slug, updated_at FROM blogs WHERE status = 'published' ORDER BY updated_at DESC");

// Get all categories
$categories = fetch_all("SELECT slug FROM categories");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Static Pages -->
    <url>
        <loc>
            <?php echo SITE_URL; ?>/
        </loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>
            <?php echo SITE_URL; ?>/blog.php
        </loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>
            <?php echo SITE_URL; ?>/services.php
        </loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Categories -->
    <?php foreach ($categories as $cat): ?>
        <url>
            <loc>
                <?php echo SITE_URL; ?>/blog.php?category=
                <?php echo $cat['slug']; ?>
            </loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    <?php endforeach; ?>

    <!-- Blog Posts -->
    <?php foreach ($blogs as $blog): ?>
        <url>
            <loc>
                <?php echo SITE_URL; ?>/blog/
                <?php echo $blog['slug']; ?>
            </loc>
            <lastmod>
                <?php echo date('Y-m-d', strtotime($blog['updated_at'])); ?>
            </lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    <?php endforeach; ?>
</urlset>