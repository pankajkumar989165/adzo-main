/**
 * Admin Panel JavaScript
 */

// Auto-save functionality (optional - can be enhanced)
let autoSaveTimer = null;

function startAutoSave(formId, saveCallback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            saveCallback();
        }, 30000); // Auto-save after 30 seconds of inactivity
    });
}

// Toast notifications
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 300ms ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Confirm before leaving page with unsaved changes
let hasUnsavedChanges = false;

function trackUnsavedChanges(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    form.addEventListener('input', function() {
        hasUnsavedChanges = true;
    });
    
    form.addEventListener('submit', function() {
        hasUnsavedChanges = false;
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return e.returnValue;
        }
    });
}

// Image preview helper
function previewImage(input, previewElementId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const preview = document.getElementById(previewElementId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Sidebar toggle for mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

// Initialize tooltips (if needed)
document.addEventListener('DOMContentLoaded', function() {
    // Add any initialization code here
    console.log('Admin panel loaded successfully');
});
