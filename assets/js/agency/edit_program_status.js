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
        let label = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
        statusBadge.textContent = label;
        statusBadge.className = 'status-badge status-' + status;
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
            </div>
            <div class='mb-3'>
                <label for='status-remarks' class='form-label'>Remarks (optional)</label>
                <textarea class='form-control' id='status-remarks' name='remarks' rows='2'></textarea>
            </div>`;
        // Only show hold fields if status is 'on_hold'
        if (status === 'on_hold') {
            html += `<div id='hold-point-fields'>
                <div class='mb-3'>
                    <label for='hold-reason' class='form-label'>Hold Reason</label>
                    <input type='text' class='form-control' id='hold-reason' name='reason' value='${hold.reason || ''}' required />
                </div>
                <div class='mb-3'>
                    <label for='hold-remarks' class='form-label'>Hold Remarks (optional)</label>
                    <textarea class='form-control' id='hold-remarks' name='hold_remarks' rows='2'>${hold.remarks || ''}</textarea>
                </div>
            </div>`;
        }
        html += `<button type='submit' class='btn btn-primary'>Save</button>
        </form>`;
        editModalBody.innerHTML = html;
        // Show/hide hold fields on status change
        const statusSelect = document.getElementById('status-select');
        statusSelect.addEventListener('change', (e) => {
            const holdFields = document.getElementById('hold-point-fields');
            if (e.target.value === 'on_hold') {
                if (!holdFields) {
                    const div = document.createElement('div');
                    div.id = 'hold-point-fields';
                    div.innerHTML = `<div class='mb-3'><label for='hold-reason' class='form-label'>Hold Reason</label><input type='text' class='form-control' id='hold-reason' name='reason' required /></div><div class='mb-3'><label for='hold-remarks' class='form-label'>Hold Remarks (optional)</label><textarea class='form-control' id='hold-remarks' name='hold_remarks' rows='2'></textarea></div>`;
                    statusSelect.parentNode.parentNode.appendChild(div);
                } else {
                    holdFields.style.display = '';
                }
            } else if (holdFields) {
                holdFields.style.display = 'none';
            }
        });
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