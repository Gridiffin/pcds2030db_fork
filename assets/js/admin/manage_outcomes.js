// assets/js/admin/manage_outcomes.js
// JS logic for Admin Outcomes Management Page

document.addEventListener('DOMContentLoaded', function() {
    // Refresh button logic
    const refreshBtn = document.getElementById('refreshPage');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => window.location.reload());
    }

    // Modal and AJAX logic for outcome editing
    const editModal = document.getElementById('editOutcomeDetailModal');
    const editForm = document.getElementById('editOutcomeDetailForm');
    const saveBtn = document.getElementById('saveOutcomeDetailBtn');
    const itemsContainer = document.getElementById('editItemsContainer');
    let currentOutcomeId = null;

    // Open modal and load outcome details
    function openEditModal(outcomeId) {
        currentOutcomeId = outcomeId;
        fetch(`/app/ajax/admin_outcomes.php?id=${outcomeId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Populate modal fields (for now, just title/description)
                    itemsContainer.innerHTML = `
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" id="editOutcomeTitle" value="${data.data.title || ''}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="editOutcomeDescription" rows="3" required>${data.data.description || ''}</textarea>
                        </div>
                    `;
                    // Show modal
                    if (window.bootstrap && editModal) {
                        const modal = window.bootstrap.Modal.getOrCreateInstance(editModal);
                        modal.show();
                    }
                } else {
                    showError(data.error || 'Failed to load outcome details');
                }
            })
            .catch(() => showError('Failed to load outcome details'));
    }

    // Save changes
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            const title = document.getElementById('editOutcomeTitle').value.trim();
            const description = document.getElementById('editOutcomeDescription').value.trim();
            if (!title || !description) {
                showError('Title and description are required');
                return;
            }
            fetch('/app/ajax/admin_outcomes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: currentOutcomeId, title, description })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Outcome updated successfully');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showError(data.error || 'Failed to update outcome');
                }
            })
            .catch(() => showError('Failed to update outcome'));
        });
    }

    // Utility functions for showing messages
    function showError(msg) {
        const errorDiv = document.getElementById('errorContainer');
        if (errorDiv) {
            errorDiv.textContent = msg;
            errorDiv.style.display = 'block';
        }
    }
    function showSuccess(msg) {
        const successDiv = document.getElementById('successContainer');
        if (successDiv) {
            successDiv.textContent = msg;
            successDiv.style.display = 'block';
        }
    }

    // Attach edit button event listeners
    document.querySelectorAll('a.btn-outline-warning[title="Edit Outcome"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(btn.href, window.location.origin);
            const id = url.searchParams.get('id');
            if (id) openEditModal(id);
        });
    });
}); 