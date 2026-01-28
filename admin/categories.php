<?php
/**
 * Categories Management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_auth();
$user = get_auth_user();

$categories = fetch_all("SELECT c.*, COUNT(b.id) as blog_count 
                        FROM categories c 
                        LEFT JOIN blogs b ON c.id = b.category_id 
                        GROUP BY c.id 
                        ORDER BY c.display_order ASC, c.name ASC");

$page_title = 'Categories';
include __DIR__ . '/includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <h2>Categories</h2>
        <button onclick="showAddModal()" class="btn btn-primary btn-sm">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Add Category
        </button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Blogs</th>
                <th>Color</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><strong>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </strong></td>
                    <td><code><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                    <td>
                        <?php echo $cat['blog_count']; ?> posts
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div
                                style="width: 24px; height: 24px; background: <?php echo $cat['color']; ?>; border-radius: 4px;">
                            </div>
                            <code><?php echo $cat['color']; ?></code>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <button onclick='editCategory(<?php echo json_encode($cat); ?>)'
                            class="btn btn-sm btn-secondary">Edit</button>
                        <button
                            onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name']); ?>')"
                            class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="categoryModal"
    style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div
        style="background: var(--bg-secondary); padding: var(--spacing-xl); border-radius: var(--radius-lg); width: 90%; max-width: 500px; border: 1px solid var(--glass-border);">
        <h3 id="modalTitle" style="margin-bottom: var(--spacing-lg);">Add Category</h3>
        <form id="categoryForm">
            <input type="hidden" id="categoryId" name="id">
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" required style="padding-left: 1rem;">
            </div>
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required style="padding-left: 1rem;">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" style="padding-left: 1rem;"></textarea>
            </div>
            <div class="form-group">
                <label for="color">Color</label>
                <input type="color" id="color" name="color" value="#6366f1" style="height: 50px; padding: 0.5rem;">
            </div>
            <div class="flex gap-2" style="margin-top: var(--spacing-lg);">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showAddModal() {
        document.getElementById('modalTitle').textContent = 'Add Category';
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryModal').style.display = 'flex';
    }

    function editCategory(cat) {
        document.getElementById('modalTitle').textContent = 'Edit Category';
        document.getElementById('categoryId').value = cat.id;
        document.getElementById('name').value = cat.name;
        document.getElementById('slug').value = cat.slug;
        document.getElementById('description').value = cat.description || '';
        document.getElementById('color').value = cat.color;
        document.getElementById('categoryModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('categoryModal').style.display = 'none';
    }

    document.getElementById('categoryForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const id = formData.get('id');
        formData.append('action', id ? 'update' : 'create');

        try {
            const response = await fetch('../api/categories.php', {
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
            alert('Error: ' + error.message);
        }
    });

    async function deleteCategory(id, name) {
        if (!confirm(`Delete category "${name}"?`)) return;

        try {
            const response = await fetch(`../api/categories.php?action=delete&id=${id}`, {
                method: 'GET'
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }

    // Auto-generate slug
    document.getElementById('name').addEventListener('input', function () {
        const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        document.getElementById('slug').value = slug;
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>