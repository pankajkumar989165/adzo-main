<?php
/**
 * Blog Editor - Create & Edit Blog Posts
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Require authentication
require_auth();

$user = get_auth_user();
$blog_id = $_GET['id'] ?? null;
$blog = null;
$blog_tags = [];

// If editing, load blog data
if ($blog_id) {
    $blog = fetch_one("SELECT b.*, sm.meta_title, sm.meta_description, sm.meta_keywords, sm.og_image, sm.canonical_url
                       FROM blogs b 
                       LEFT JOIN seo_meta sm ON b.id = sm.blog_id
                       WHERE b.id = ?", [$blog_id], 'i');
    
    if ($blog) {
        $blog_tags = fetch_all("SELECT tag_id FROM blog_tags WHERE blog_id = ?", [$blog_id], 'i');
        $blog_tags = array_column($blog_tags, 'tag_id');
    }
}

// Get categories and tags
$categories = fetch_all("SELECT * FROM categories ORDER BY name ASC");
$tags = fetch_all("SELECT * FROM tags ORDER BY name ASC");

$page_title = $blog_id ? 'Edit Blog Post' : 'Create New Blog Post';
include __DIR__ . '/includes/header.php';
?>

<style>
    .editor-container {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: var(--spacing-xl);
    }
    
    .editor-main {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-lg);
        padding: var(--spacing-xl);
    }
    
    .editor-sidebar {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-lg);
    }
    
    .sidebar-panel {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
    }
    
    .sidebar-panel h3 {
        font-size: 1rem;
        margin-bottom: var(--spacing-md);
        color: var(--primary-light);
    }
    
    .image-preview {
        width: 100%;
        aspect-ratio: 16/9;
        background: rgba(15, 23, 42, 0.5);
        border: 2px dashed var(--glass-border);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: var(--spacing-md);
        overflow: hidden;
        position: relative;
    }
    
    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .image-preview-placeholder {
        text-align: center;
        color: var(--text-muted);
    }
    
    .remove-image {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        background: var(--error);
        color: white;
        border: none;
        padding: 0.5rem;
        border-radius: var(--radius-md);
        cursor: pointer;
        opacity: 0;
        transition: opacity var(--transition-fast);
    }
    
    .image-preview:hover .remove-image {
        opacity: 1;
    }
    
    .char-count {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }
    
    .char-count.warning {
        color: var(--warning);
    }
    
    .char-count.error {
        color: var(--error);
    }
    
    .tag-select {
        display: flex;
        flex-wrap: wrap;
        gap: var(--spacing-sm);
        margin-top: var(--spacing-sm);
    }
    
    .tag-checkbox {
        display: none;
    }
    
    .tag-label {
        padding: 0.5rem 0.75rem;
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        cursor: pointer;
        transition: all var(--transition-fast);
    }
    
    .tag-checkbox:checked + .tag-label {
        background: var(--primary);
        border-color: var(--primary);
        color: white;
    }
    
    @media (max-width: 1024px) {
        .editor-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<form id="blogForm" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?php echo $blog_id ? 'update' : 'create'; ?>">
    <input type="hidden" name="id" value="<?php echo $blog_id ?? ''; ?>">
    <input type="hidden" name="featured_image" id="featuredImagePath" value="<?php echo $blog['featured_image'] ?? ''; ?>">
    <input type="hidden" name="og_image" id="ogImagePath" value="<?php echo $blog['og_image'] ?? ''; ?>">
    
    <div class="editor-container">
        <div class="editor-main">
            <div class="form-group">
                <label for="title">Blog Title *</label>
                <input type="text" id="title" name="title" required 
                       placeholder="Enter blog title..."
                       value="<?php echo htmlspecialchars($blog['title'] ?? ''); ?>"
                       style="padding-left: 1rem;">
            </div>
            
            <div class="form-group">
                <label for="slug">URL Slug *</label>
                <input type="text" id="slug" name="slug" required 
                       placeholder="url-friendly-slug"
                       value="<?php echo htmlspecialchars($blog['slug'] ?? ''); ?>"
                       style="padding-left: 1rem;">
                <small style="color: var(--text-muted);">URL: <?php echo SITE_URL; ?>/blog/<span id="slugPreview"><?php echo $blog['slug'] ?? 'your-slug'; ?></span></small>
            </div>
            
            <div class="form-group">
                <label for="excerpt">Excerpt</label>
                <textarea id="excerpt" name="excerpt" rows="3" 
                          placeholder="Brief summary of your blog post..."
                          style="padding-left: 1rem;"><?php echo htmlspecialchars($blog['excerpt'] ?? ''); ?></textarea>
                <div class="char-count" id="excerptCount">0 / 250 characters</div>
            </div>
            
            <div class="form-group">
                <label for="content">Content *</label>
                <textarea id="content" name="content"><?php echo htmlspecialchars($blog['content'] ?? ''); ?></textarea>
            </div>
            
            <!-- SEO Section -->
            <div style="margin-top: var(--spacing-xl); padding-top: var(--spacing-xl); border-top: 1px solid var(--glass-border);">
                <h3 style="margin-bottom: var(--spacing-lg); color: var(--primary-light);">
                    <svg style="width: 20px; height: 20px; display: inline; vertical-align: middle;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                    </svg>
                    SEO Settings
                </h3>
                
                <div class="form-group">
                    <label for="meta_title">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" 
                           placeholder="SEO-optimized title (60-70 chars)"
                           value="<?php echo htmlspecialchars($blog['meta_title'] ?? ''); ?>"
                           style="padding-left: 1rem;">
                    <div class="char-count" id="metaTitleCount">0 / 70 characters</div>
                </div>
                
                <div class="form-group">
                    <label for="meta_description">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" rows="3"
                              placeholder="Compelling description for search results (150-160 chars)"
                              style="padding-left: 1rem;"><?php echo htmlspecialchars($blog['meta_description'] ?? ''); ?></textarea>
                    <div class="char-count" id="metaDescCount">0 / 160 characters</div>
                </div>
                
                <div class="form-group">
                    <label for="meta_keywords">Meta Keywords</label>
                    <input type="text" id="meta_keywords" name="meta_keywords" 
                           placeholder="keyword1, keyword2, keyword3"
                           value="<?php echo htmlspecialchars($blog['meta_keywords'] ?? ''); ?>"
                           style="padding-left: 1rem;">
                </div>
                
                <div class="form-group">
                    <label for="canonical_url">Canonical URL</label>
                    <input type="url" id="canonical_url" name="canonical_url" 
                           placeholder="https://adzodigital.com/blog/your-post"
                           value="<?php echo htmlspecialchars($blog['canonical_url'] ?? ''); ?>"
                           style="padding-left: 1rem;">
                </div>
            </div>
        </div>
        
        <div class="editor-sidebar">
            <!-- Publish Panel -->
            <div class="sidebar-panel">
                <h3>Publish</h3>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="draft" <?php echo (!$blog || $blog['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo ($blog && $blog['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_featured" value="1" <?php echo ($blog && $blog['is_featured']) ? 'checked' : ''; ?>>
                        Featured Post
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <?php echo $blog_id ? 'Update Post' : 'Publish Post'; ?>
                </button>
                <a href="blogs.php" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">Cancel</a>
            </div>
            
            <!-- Category Panel -->
            <div class="sidebar-panel">
                <h3>Category</h3>
                <select id="category_id" name="category_id" style="padding: 0.75rem; padding-left: 1rem;">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo ($blog && $blog['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Tags Panel -->
            <div class="sidebar-panel">
                <h3>Tags</h3>
                <div class="tag-select">
                    <?php foreach ($tags as $tag): ?>
                        <div>
                            <input type="checkbox" 
                                   class="tag-checkbox" 
                                   id="tag_<?php echo $tag['id']; ?>" 
                                   name="tags[]" 
                                   value="<?php echo $tag['id']; ?>"
                                   <?php echo in_array($tag['id'], $blog_tags) ? 'checked' : ''; ?>>
                            <label class="tag-label" for="tag_<?php echo $tag['id']; ?>">
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Featured Image Panel -->
            <div class="sidebar-panel">
                <h3>Featured Image</h3>
                <div class="image-preview" id="imagePreview">
                    <?php if (!empty($blog['featured_image'])): ?>
                        <img src="<?php echo SITE_URL . '/' . $blog['featured_image']; ?>" alt="Preview">
                        <button type="button" class="remove-image" onclick="removeImage()">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    <?php else: ?>
                        <div class="image-preview-placeholder">
                            <svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                            </svg>
                            <p>No image selected</p>
                        </div>
                    <?php endif; ?>
                </div>
                <input type="file" id="imageUpload" accept="image/*" style="display: none;" onchange="uploadImage(this)">
                <button type="button" class="btn btn-secondary btn-block" onclick="document.getElementById('imageUpload').click();">
                    Upload Image
                </button>
            </div>
        </div>
    </div>
</form>

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/4.20.1/standard/ckeditor.js"></script>
<script>
    // Initialize CKEditor
    CKEDITOR.replace('content', {
        height: 500,
        removeButtons: 'Save,NewPage,Preview,Print,Templates',
        removePlugins: 'exportpdf'
    });
    
    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function() {
        if (!document.getElementById('slug').value || document.getElementById('slug').dataset.auto !== 'false') {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
            document.getElementById('slug').value = slug;
            document.getElementById('slugPreview').textContent = slug || 'your-slug';
        }
    });
    
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.auto = 'false';
        document.getElementById('slugPreview').textContent = this.value || 'your-slug';
    });
    
    // Character counters
    function updateCharCount(inputId, countId, max, warning = 0) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(countId);
        
        input.addEventListener('input', function() {
            const length = this.value.length;
            counter.textContent = `${length} / ${max} characters`;
            
            counter.classList.remove('warning', 'error');
            if (length > max) {
                counter.classList.add('error');
            } else if (length > warning) {
                counter.classList.add('warning');
            }
        });
        
        // Trigger on load
        input.dispatchEvent(new Event('input'));
    }
    
    updateCharCount('excerpt', 'excerptCount', 250, 200);
    updateCharCount('meta_title', 'metaTitleCount', 70, 60);
    updateCharCount('meta_description', 'metaDescCount', 160, 150);
    
    // Image upload
    async function uploadImage(input) {
        if (!input.files || !input.files[0]) return;
        
        const formData = new FormData();
        formData.append('image', input.files[0]);
        
        try {
            const response = await fetch('../api/upload.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('featuredImagePath').value = result.data.path;
                document.getElementById('imagePreview').innerHTML = `
                    <img src="${result.data.url}" alt="Preview">
                    <button type="button" class="remove-image" onclick="removeImage()">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                `;
                alert('Image uploaded successfully!');
            } else {
                alert('Upload failed: ' + result.message);
            }
        } catch (error) {
            alert('Upload error: ' + error.message);
        }
    }
    
    function removeImage() {
        document.getElementById('featuredImagePath').value = '';
        document.getElementById('imagePreview').innerHTML = `
            <div class="image-preview-placeholder">
                <svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                </svg>
                <p>No image selected</p>
            </div>
        `;
    }
    
    // Form submission
    document.getElementById('blogForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Get CKEditor content
        const content = CKEDITOR.instances.content.getData();
        if (!content.trim()) {
            alert('Please enter blog content');
            return;
        }
        
        const formData = new FormData(this);
        formData.set('content', content);
        
        try {
            const response = await fetch('../api/blogs.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                window.location.href = 'blogs.php';
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error submitting form: ' + error.message);
        }
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
