<?php
/**
 * Tags Management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_auth();
$user = get_auth_user();

$tags = fetch_all("SELECT t.*, COUNT(bt.blog_id) as blog_count 
                  FROM tags t 
                  LEFT JOIN blog_tags bt ON t.id = bt.tag_id 
                  GROUP BY t.id 
                  ORDER BY t.name ASC");

$page_title = 'Tags';
include __DIR__ . '/includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <h2>Tags</h2>
        <button onclick="showAddModal()" class="btn btn-primary btn-sm">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Add Tag
        </button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Usage</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tags as $tag): ?>
                <tr>
                    <td><strong>
                            <?php echo htmlspecialchars($tag['name']); ?>
                        </strong></td>
                    <td><code><?php echo htmlspecialchars($tag['slug']); ?></code></td>
                    <td>
                        <?php echo $tag['blog_count']; ?> posts
                    </td>
                    <td style="text-align: right;">
                        <button onclick='editTag(<?php echo json_encode($tag); ?>)'
                            class="btn btn-sm btn-secondary">Edit</button>
                        <button
                            onclick="deleteTag(<?php echo $tag['id']; ?>, '<?php echo htmlspecialchars($tag['name']); ?>')"
                            class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="tagModal"
    style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div
        style="background: var(--bg-secondary); padding: var(--spacing-xl); border-radius: var(--radius-lg); width: 90%; max-width: 500px; border: 1px solid var(--glass-border);">
        <h3 id="modalTitle" style="margin-bottom: var(--spacing-lg);">Add Tag</h3>
        <form id="tagForm">
            <input type="hidden" id="tagId" name="id">
            <div class="form-group">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" required style="padding-left: 1rem;">
            </div>
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required style="padding-left: 1rem;">
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
        document.getElementById('modalTitle').textContent = 'Add Tag';
        document.getElementById('tagForm').reset();
        document.getElementById('tagId').value = '';
        document.getElementById('tagModal').style.display = 'flex';
    }

    function editTag(tag) {
        document.getElementById('modalTitle').textContent = 'Edit Tag';
        document.getElementById('tagId').value = tag.id;
        document.getElementById('name').value = tag.name;
        document.getElementById('slug').value = tag.slug;
        document.getElementById('tagModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('tagModal').style.display = 'none';
    }

    document.getElementById('tagForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const id = formData.get('id');
        formData.append('action', id ? 'update' : 'create');

        try {
            const response = await fetch('../api/tags.php', {
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

    async function deleteTag(id, name) {
        if (!confirm(`Delete tag "${name}"?`)) return;

        try {
            const response = await fetch(`../api/tags.php?action=delete&id=${id}`, {
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