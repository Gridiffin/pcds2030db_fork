// Minimal JS for Edit Program Status Management
// Assumes Bootstrap 5 is loaded

document.addEventListener('DOMContentLoaded', function() {
    const programId = window.programId;
    const apiUrl = window.APP_URL + '/app/api/program_status.php';

    // Elements
    const statusBadge = document.getElementById('program-status-badge');
    const editBtn = document.getElementById('edit-status-btn');
    const historyBtn = document.getElementById('view-status-history-btn');
    const editModal = document.getElementById('editStatusModal');
    const historyModal = document.getElementById('statusHistoryModal');
    const editModalBody = document.getElementById('edit-status-modal-body');
    const historyModalBody = document.getElementById('status-history-modal-body');

    // Fetch and display current status
    function loadStatus() {
        fetch(`${apiUrl}?action=status&program_id=${programId}`)
            .then(res => res.json())
            .then(data => {
                renderStatus(data);
            });
    }

    function renderStatus(data) {
        if (!statusBadge) return;
        let status = data.status || 'active';
        // Map status to label, class, and icon (should match PHP helper)
        const statusMap = {
            'active':    { label: 'Active',    class: 'success',    icon: 'fas fa-play-circle' },
            'on_hold':   { label: 'On Hold',   class: 'warning',    icon: 'fas fa-pause-circle' },
            'completed': { label: 'Completed', class: 'primary',    icon: 'fas fa-check-circle' },
            'delayed':   { label: 'Delayed',   class: 'danger',     icon: 'fas fa-exclamation-triangle' },
            'cancelled': { label: 'Cancelled', class: 'secondary',  icon: 'fas fa-times-circle' }
        };
        const info = statusMap[status] || { label: status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' '), class: 'secondary', icon: 'fas fa-question-circle' };
        statusBadge.innerHTML = `<i class='${info.icon} me-1'></i>${info.label}`;
        statusBadge.className = `badge status-badge bg-${info.class} py-2 px-3`;
    }

    // Open status edit modal
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            fetch(`${apiUrl}?action=status&program_id=${programId}`)
                .then(res => res.json())
                .then(data => {
                    renderEditForm(data);
                    new bootstrap.Modal(editModal).show();
                });
        });
    }

    function renderEditForm(data) {
        let status = data.status || 'active';
        let hold = data.hold_point || {};
        const options = [
            { value: 'active', label: 'Active' },
            { value: 'on_hold', label: 'On Hold' },
            { value: 'completed', label: 'Completed' },
            { value: 'delayed', label: 'Delayed' },
            { value: 'cancelled', label: 'Cancelled' }
        ];
        let html = `<form id='edit-status-form'>
            <div class='mb-3'>
                <label for='status-select' class='form-label'>Status</label>
                <select class='form-select' id='status-select' name='status'>
                    ${options.map(opt => `<option value='${opt.value}' ${opt.value === status ? 'selected' : ''}>${opt.label}</option>`).join('')}
                </select>
            </div>`;
        // Hold fields (initially rendered if status is 'on_hold')
        let holdFields = `<div id='hold-point-fields'>
            <div class='mb-3'>
                <label for='hold-reason' class='form-label'>Hold Reason</label>
                <input type='text' class='form-control' id='hold-reason' name='reason' value='${hold.reason || ''}' required />
            </div>
            <div class='mb-3'>
                <label for='hold-remarks' class='form-label'>Hold Point Remarks</label>
                <textarea class='form-control' id='hold-remarks' name='remarks' rows='2'></textarea>
            </div>
        </div>`;
        if (status === 'on_hold') {
            html += holdFields;
        }
        html += `<button type='submit' class='btn btn-primary mt-2'>Save</button>
        </form>`;
        editModalBody.innerHTML = html;

        // Move hold fields above the Save button dynamically
        function showHoldFields() {
            let form = document.getElementById('edit-status-form');
            let saveBtn = form.querySelector("button[type='submit']");
            let holdDiv = document.getElementById('hold-point-fields');
            if (!holdDiv) {
                // Insert holdFields above the Save button
                saveBtn.insertAdjacentHTML('beforebegin', holdFields);
                holdDiv = document.getElementById('hold-point-fields');
            } else {
                holdDiv.style.display = '';
            }
            // Enable and require the hold reason input
            let reasonInput = document.getElementById('hold-reason');
            if (reasonInput) {
                reasonInput.disabled = false;
                reasonInput.required = true;
            }
        }
        function hideHoldFields() {
            let holdDiv = document.getElementById('hold-point-fields');
            if (holdDiv) {
                holdDiv.style.display = 'none';
                // Disable and unrequire the hold reason input
                let reasonInput = document.getElementById('hold-reason');
                if (reasonInput) {
                    reasonInput.disabled = true;
                    reasonInput.required = false;
                }
            }
        }
        // Show/hide hold fields on status change
        const statusSelect = document.getElementById('status-select');
        statusSelect.addEventListener('change', (e) => {
            if (e.target.value === 'on_hold') {
                showHoldFields();
            } else {
                hideHoldFields();
            }
        });
        // Initial state
        if (status === 'on_hold') {
            showHoldFields();
        } else {
            hideHoldFields();
        }
        // Submit handler
        document.getElementById('edit-status-form').addEventListener('submit', function(e) {
            e.preventDefault();
            submitStatusForm();
        });
    }

    function submitStatusForm() {
        const form = document.getElementById('edit-status-form');
        const formData = new FormData(form);
        formData.append('action', 'set_status');
        formData.append('program_id', programId);
        fetch(apiUrl, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadStatus();
                bootstrap.Modal.getInstance(editModal).hide();
            } else {
                alert(data.error || 'Failed to update status.');
            }
        });
    }

    // Open status history modal
    if (historyBtn) {
        historyBtn.addEventListener('click', function() {
            fetch(`${apiUrl}?action=status_history&program_id=${programId}`)
                .then(res => res.json())
                .then(data => {
                    renderHistory(data);
                    new bootstrap.Modal(historyModal).show();
                });
        });
    }

    function renderHistory(data) {
        let html = '<h6>Status Changes</h6><ul class="list-group mb-3">';
        (data.status_history || []).forEach(item => {
            html += `<li class="list-group-item"><b>${item.status}</b> by User #${item.changed_by} <span class="text-muted">(${item.changed_at})</span> ${item.remarks ? ' - ' + item.remarks : ''}</li>`;
        });
        html += '</ul><h6>Hold Points</h6><ul class="list-group">';
        (data.hold_points || []).forEach(item => {
            html += `<li class="list-group-item"><b>${item.reason}</b> (${item.created_at})${item.ended_at ? ' - Ended: ' + item.ended_at : ''} ${item.remarks ? ' - ' + item.remarks : ''}</li>`;
        });
        html += '</ul>';
        historyModalBody.innerHTML = html;
    }

    // Initial load
    loadStatus();
}); 