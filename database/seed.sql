-- Aadzo Digital Blog Management System - Sample Data
-- Run this after schema.sql

-- Insert Admin User
-- Default Password: Admin@123 (Please change after first login)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `status`) VALUES
('admin', 'admin@adzodigital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Aadzo Admin', 'admin', 'active');
-- Note: Password is hashed version of 'Admin@123'

-- Insert Sample Categories
INSERT INTO `categories` (`name`, `slug`, `description`, `color`, `display_order`) VALUES
('Digital Marketing', 'digital-marketing', 'Latest trends and strategies in digital marketing', '#6366f1', 1),
('Web Development', 'web-development', 'Web design and development tips and tutorials', '#8b5cf6', 2),
('SEO', 'seo', 'Search engine optimization best practices', '#ec4899', 3),
('Social Media', 'social-media', 'Social media marketing strategies and tips', '#14b8a6', 4),
('Content Marketing', 'content-marketing', 'Creating engaging content that converts', '#f59e0b', 5),
('Branding', 'branding', 'Building strong brand identity', '#ef4444', 6);

-- Insert Sample Tags
INSERT INTO `tags` (`name`, `slug`) VALUES
('SEO Tips', 'seo-tips'),
('Google Analytics', 'google-analytics'),
('WordPress', 'wordpress'),
('React', 'react'),
('UI/UX', 'ui-ux'),
('E-commerce', 'e-commerce'),
('Content Strategy', 'content-strategy'),
('Social Media Marketing', 'social-media-marketing'),
('Email Marketing', 'email-marketing'),
('Lead Generation', 'lead-generation'),
('Mobile Optimization', 'mobile-optimization'),
('Video Marketing', 'video-marketing');

-- Insert Sample Blog Post
INSERT INTO `blogs` (`title`, `slug`, `excerpt`, `content`, `featured_image`, `author_id`, `category_id`, `status`, `reading_time`, `is_featured`, `published_at`) VALUES
(
  '10 Essential SEO Tips for Small Businesses in 2024',
  '10-essential-seo-tips-small-businesses-2024',
  'Discover the top SEO strategies that can help your small business rank higher on Google and attract more customers organically.',
  '<h2>Why SEO Matters for Small Businesses</h2>
<p>In today\'s digital landscape, having a strong online presence is crucial for small businesses. Search Engine Optimization (SEO) is one of the most cost-effective ways to increase visibility and attract potential customers.</p>

<h2>1. Optimize for Local Search</h2>
<p>Local SEO is essential for small businesses. Make sure to claim your Google Business Profile and keep your NAP (Name, Address, Phone) consistent across all platforms.</p>

<h2>2. Focus on Quality Content</h2>
<p>Create valuable, informative content that addresses your audience\'s pain points. Google rewards websites that provide genuine value to users.</p>

<h2>3. Mobile-First Optimization</h2>
<p>With over 60% of searches coming from mobile devices, ensure your website is fully responsive and loads quickly on all devices.</p>

<h2>4. Build Quality Backlinks</h2>
<p>Focus on earning backlinks from reputable websites in your industry. Quality matters more than quantity when it comes to link building.</p>

<h2>5. Improve Page Speed</h2>
<p>Site speed is a ranking factor. Compress images, minimize code, and use caching to ensure your pages load in under 3 seconds.</p>

<h2>6. Use Long-Tail Keywords</h2>
<p>Target specific, longer keyword phrases that have less competition but higher conversion rates.</p>

<h2>7. Optimize Your Meta Tags</h2>
<p>Write compelling meta titles and descriptions that include your target keywords and encourage clicks.</p>

<h2>8. Leverage Schema Markup</h2>
<p>Implement structured data to help search engines understand your content better and potentially earn rich snippets.</p>

<h2>9. Monitor Your Analytics</h2>
<p>Regularly check Google Analytics and Search Console to understand what\'s working and what needs improvement.</p>

<h2>10. Stay Updated with Algorithm Changes</h2>
<p>SEO is constantly evolving. Stay informed about Google algorithm updates and adjust your strategy accordingly.</p>

<h2>Conclusion</h2>
<p>Implementing these SEO tips can significantly improve your online visibility. Remember, SEO is a long-term strategy that requires patience and consistency.</p>',
  'uploads/blog-images/seo-tips-2024.jpg',
  1,
  3,
  'published',
  8,
  1,
  NOW()
);

-- Link tags to the sample blog post
INSERT INTO `blog_tags` (`blog_id`, `tag_id`) VALUES
(1, 1),  -- SEO Tips
(1, 2),  -- Google Analytics
(1, 10), -- Lead Generation
(1, 11); -- Mobile Optimization

-- Insert SEO Meta for sample blog
INSERT INTO `seo_meta` (`blog_id`, `meta_title`, `meta_description`, `meta_keywords`, `og_title`, `og_description`, `canonical_url`, `focus_keyword`) VALUES
(
  1,
  '10 Essential SEO Tips for Small Businesses in 2024 | Aadzo Digital',
  'Boost your small business with these proven SEO strategies. Learn how to rank higher on Google and attract more organic traffic in 2024.',
  'SEO tips, small business SEO, search engine optimization, Google ranking, local SEO',
  '10 SEO Tips Every Small Business Needs in 2024',
  'Master SEO for your small business with our expert guide. Increase visibility, drive traffic, and grow your customer base.',
  'https://adzodigital.com/blog/10-essential-seo-tips-small-businesses-2024',
  'SEO tips for small businesses'
);
