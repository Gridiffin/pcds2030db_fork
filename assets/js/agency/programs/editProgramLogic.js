/**
 * Edit Program Logic
 *
 * Contains the client-side logic for the agency edit program page.
 * Handles form interactions, dynamic field updates, and status management.
 */

// --- Helper Functions ---

/**
 * Shows a toast notification.
 * Assumes a showToast function is globally available.
 * @param {string} title - The title of the toast.
 * @param {string} message - The message body of the toast.
 * @param {string} type - The type of toast (e.g., 'success', 'danger').
 */
function showToast(title, message, type) {
    if (window.showToast) {
        window.showToast(title, message, type);
    } else {
        console.warn('showToast function not found. Implement or include a toast library.');
        alert(`${title}: ${message}`);
    }
}

// --- Status Management ---

function initStatusManagement(programId, apiUrl) {
    const statusBadge = document.getElementById('program-status-badge');
    const editBtn = document.getElementById('edit-status-btn');
    const historyBtn = document.getElementById('view-status-history-btn');
    const editModalEl = document.getElementById('editStatusModal');
    const historyModalEl = document.getElementById('statusHistoryModal');
    const editModalBody = document.getElementById('edit-status-modal-body');
    const historyModalBody = document.getElementById('status-history-modal-body');
    const holdSection = document.getElementById('holdPointManagementSection');

    if (!editBtn) return; // Don't initialize if controls aren't present

    const editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
    const historyModal = historyModalEl ? new bootstrap.Modal(historyModalEl) : null;

    const statusMap = {
        'active': { label: 'Active', class: 'success', icon: 'fas fa-play-circle' },
        'on_hold': { label: 'On Hold', class: 'warning', icon: 'fas fa-pause-circle' },
        'completed': { label: 'Completed', class: 'primary', icon: 'fas fa-check-circle' },
        'delayed': { label: 'Delayed', class: 'danger', icon: 'fas fa-exclamation-triangle' },
        'cancelled': { label: 'Cancelled', class: 'secondary', icon: 'fas fa-times-circle' }
    };

    function renderStatus(data) {
        if (holdSection) {
            if (data.status === 'on_hold' && data.hold_point) {
                holdSection.style.display = '';
                document.getElementById('hold_reason').value = data.hold_point.reason || '';
                document.getElementById('hold_remarks').value = data.hold_point.remarks || '';
            } else {
                holdSection.style.display = 'none';
            }
        }

        if (!statusBadge) return;
        const status = data.status || 'active';
        const info = statusMap[status] || { label: status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' '), class: 'secondary', icon: 'fas fa-question-circle' };
        statusBadge.innerHTML = `<i class='${info.icon} me-1'></i>${info.label}`;
        statusBadge.className = `badge status-badge bg-${info.class} py-2 px-3`;
    }

    function loadStatus() {
        fetch(`${apiUrl}?action=status&program_id=${programId}`)
            .then(res => res.json())
            .then(renderStatus)
            .catch(err => console.error('Failed to load status:', err));
    }

    editBtn.addEventListener('click', () => {
        fetch(`${apiUrl}?action=status&program_id=${programId}`)
            .then(res => res.json())
            .then(data => {
                renderEditForm(data);
                if (editModal) editModal.show();
            });
    });

    historyBtn.addEventListener('click', () => {
        fetch(`${apiUrl}?action=status_history&program_id=${programId}`)
            .then(res => res.json())
            .then(data => {
                renderHistory(data);
                if (historyModal) historyModal.show();
            });
    });
    
    function renderEditForm(data) {
        const status = data.status || 'active';
        const hold = data.hold_point || {};
        const options = Object.keys(statusMap).map(key => ({ value: key, label: statusMap[key].label }));

        let formHtml = `
            <form id='edit-status-form'>
                <div class='mb-3'>
                    <label for='status-select' class='form-label'>Status</label>
                    <select class='form-select' id='status-select' name='status'>
                        ${options.map(opt => `<option value='${opt.value}' ${opt.value === status ? 'selected' : ''}>${opt.label}</option>`).join('')}
                    </select>
                </div>
                <div id='hold-point-fields' style='display: ${status === 'on_hold' ? 'block' : 'none'};'>
                    <div class='mb-3'>
                        <label for='hold-reason' class='form-label'>Hold Reason</label>
                        <input type='text' class='form-control' id='hold-reason' name='reason' value='${hold.reason || ''}' ${status === 'on_hold' ? 'required' : ''} />
                    </div>
                    <div class='mb-3'>
                        <label for='hold-remarks' class='form-label'>Hold Point Remarks</label>
                        <textarea class='form-control' id='hold-remarks' name='hold_remarks' rows='2'>${hold.remarks || ''}</textarea>
                    </div>
                </div>
                <button type='submit' class='btn btn-primary mt-2'>Save</button>
            </form>`;
        editModalBody.innerHTML = formHtml;

        const statusSelect = editModalBody.querySelector('#status-select');
        const holdFieldsDiv = editModalBody.querySelector('#hold-point-fields');
        const reasonInput = editModalBody.querySelector('#hold-reason');

        statusSelect.addEventListener('change', (e) => {
            if (e.target.value === 'on_hold') {
                holdFieldsDiv.style.display = 'block';
                reasonInput.required = true;
            } else {
                holdFieldsDiv.style.display = 'none';
                reasonInput.required = false;
            }
        });

        editModalBody.querySelector('#edit-status-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'set_status');
            formData.append('program_id', programId);
            
            fetch(apiUrl, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadStatus();
                        if (editModal) editModal.hide();
                    } else {
                        // No action needed for this case
                    }
                });
        });
    }

    function renderHistory(data) {
        let historyHtml = '<h6>Status Changes</h6><ul class="list-group mb-3">';
        (data.status_history || []).forEach(item => {
            historyHtml += `<li class="list-group-item"><b>${item.status}</b> by User #${item.changed_by} <span class="text-muted">(${item.changed_at})</span> ${item.remarks ? ' - ' + item.remarks : ''}</li>`;
        });
        historyHtml += '</ul><h6>Hold Points</h6><ul class="list-group">';
        (data.hold_points || []).forEach(item => {
            historyHtml += `<li class="list-group-item"><b>${item.reason}</b> (${item.created_at})${item.ended_at ? ' - Ended: ' + item.ended_at : ''} ${item.remarks ? ' - ' + item.remarks : ''}</li>`;
        });
        historyHtml += '</ul>';
        historyModalBody.innerHTML = historyHtml;
    }

    // --- Hold Point Management on Main Page ---
    const updateHoldBtn = document.getElementById('updateHoldPointBtn');
    const endHoldBtn = document.getElementById('endHoldPointBtn');

    if (updateHoldBtn) {
        updateHoldBtn.addEventListener('click', function() {
            const reason = document.getElementById('hold_reason').value;
            if (!reason.trim()) {
                showToast('Validation Error', 'Hold reason is required.', 'warning');
                return;
            }
            const remarks = document.getElementById('hold_remarks').value;
            const fd = new FormData();
            fd.append('action', 'hold_point');
            fd.append('program_id', programId);
            fd.append('reason', reason);
            fd.append('hold_remarks', remarks);
            
            this.disabled = true;
            fetch(apiUrl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadStatus();
                        showToast('Success', 'Hold point updated.', 'success');
                    } else {
                        showToast('Error', data.error || 'Update failed.', 'danger');
                    }
                })
                .finally(() => this.disabled = false);
        });
    }

    if (endHoldBtn) {
        endHoldBtn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to end this hold point? The program status will become "Active".')) return;
            const fd = new FormData();
            fd.append('action', 'end_hold_point');
            fd.append('program_id', programId);

            this.disabled = true;
            fetch(apiUrl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadStatus();
                        showToast('Success', 'Hold point ended.', 'success');
                    } else {
                        showToast('Error', data.error || 'Failed to end hold point.', 'danger');
                    }
                })
                .finally(() => this.disabled = false);
        });
    }

    loadStatus(); // Initial load
}

// --- Form Logic ---

function initFormLogic() {
    const initiativeSelect = document.getElementById('initiative_id');
    const programNumberInput = document.getElementById('program_number');
    const numberHelpText = document.getElementById('number-help-text');
    const finalNumberDisplay = document.getElementById('final-number-display');
    const finalNumberPreview = document.getElementById('final-number-preview');
    const validationDiv = document.getElementById('number-validation');
    const validationMessage = document.getElementById('validation-message');
    const restrictEditorsToggle = document.getElementById('restrict_editors');
    const userSelectionSection = document.getElementById('userSelectionSection');

    function updateNumberFieldState() {
        const selectedInitiative = initiativeSelect.value;
        if (selectedInitiative) {
            programNumberInput.disabled = false;
            programNumberInput.placeholder = 'Enter program number';
            if(numberHelpText) numberHelpText.textContent = 'Enter a number or leave blank for auto-generation';
            if(finalNumberDisplay) finalNumberDisplay.style.display = 'block';
            updateFinalNumberPreview();
        } else {
            programNumberInput.disabled = true;
            programNumberInput.placeholder = 'Select initiative first';
            if(numberHelpText) numberHelpText.textContent = 'Select an initiative to enable program numbering';
            if(finalNumberDisplay) finalNumberDisplay.style.display = 'none';
        }
    }

    function updateFinalNumberPreview() {
        const currentNumber = programNumberInput.value.trim();
        if(finalNumberPreview) finalNumberPreview.textContent = currentNumber || 'Will be generated automatically';
    }

    if (initiativeSelect) {
        initiativeSelect.addEventListener('change', updateNumberFieldState);
    }

    if (programNumberInput) {
        programNumberInput.addEventListener('input', () => {
            const number = programNumberInput.value.trim();
            if (number) {
                if (/^[a-zA-Z0-9.]+$/.test(number)) {
                    if(validationDiv) validationDiv.style.display = 'block';
                    if(validationMessage) {
                        validationMessage.className = 'text-success';
                        validationMessage.textContent = 'Valid format';
                    }
                } else {
                    if(validationDiv) validationDiv.style.display = 'block';
                    if(validationMessage) {
                        validationMessage.className = 'text-danger';
                        validationMessage.textContent = 'Invalid format. Use only letters, numbers, and dots.';
                    }
                }
            } else {
                if(validationDiv) validationDiv.style.display = 'none';
            }
            updateFinalNumberPreview();
        });
    }
    
    if (restrictEditorsToggle) {
        restrictEditorsToggle.addEventListener('change', () => {
            if(userSelectionSection) userSelectionSection.style.display = restrictEditorsToggle.checked ? 'block' : 'none';
        });
    }

    // Initial state setup
    updateNumberFieldState();
    if(userSelectionSection && restrictEditorsToggle) {
         userSelectionSection.style.display = restrictEditorsToggle.checked ? 'block' : 'none';
    }
}


// --- Main Initialization ---

export function initEditProgram() {
    const { programId, APP_URL } = window.PCDS_VARS || {};

    if (!programId || !APP_URL) {
        console.error('PCDS_VARS (programId, APP_URL) not found on window object.');
        return;
    }
    
            const apiUrl = `${window.APP_URL}/app/api/program_status.php`;

    initFormLogic();
    initStatusManagement(programId, apiUrl);
} 