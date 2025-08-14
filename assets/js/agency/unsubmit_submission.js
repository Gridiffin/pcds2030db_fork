// Unsubmit finalized submission (focal users only)
function unsubmitSubmission(submissionId, btn) {
    // Close any open program dropdowns to avoid z-index overlap
    try {
        document.querySelectorAll('.dropdown-menu-custom.show').forEach(function(menu) {
            menu.classList.remove('show');
            const box = menu.closest('.program-box');
            if (box) box.classList.remove('dropdown-active');
        });
        document.body.classList.remove('dropdown-open');
    } catch (e) { /* noop */ }

    // Show a Bootstrap confirmation modal instead of window.confirm
    showUnsubmitConfirmModal(() => performUnsubmit(submissionId, btn));
}

function performUnsubmit(submissionId, btn) {
    // Debug: Check if APP_URL is defined
    console.log('APP_URL:', window.APP_URL);

    // Get the base URL for AJAX requests
    let appUrl = window.APP_URL;
    if (!appUrl) {
        // Fallback: Try to get from document location
        const currentPath = window.location.pathname;
        // Remove the current page path to get the base URL
        const basePath = currentPath.replace(/\/app\/views\/.*$/, '');
        appUrl = window.location.origin + basePath;
        console.log('Fallback APP_URL:', appUrl);
    }

    if (!appUrl) {
        if (typeof showToast === 'function') {
            showToast('Error', 'Configuration error: APP_URL not defined', 'danger');
        }
        return;
    }

    if (btn) btn.disabled = true;
    fetch(appUrl + '/app/ajax/unsubmit_submission.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'submission_id=' + encodeURIComponent(submissionId)
    })
    .then(res => {
        console.log('Response status:', res.status);
        console.log('Response headers:', res.headers);
        return res.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showUnsubmitSuccessModal('Submission Returned to Draft', () => {
                window.location.reload();
            });
        } else {
            let errorMsg = data.error || 'Failed to unsubmit.';
            if (data.debug) {
                console.log('Debug info:', data.debug);
                errorMsg += ' (Check console for details)';
            }
            if (typeof showToast === 'function') {
                showToast('Error', errorMsg, 'danger');
            }
            if (btn) btn.disabled = false;
        }
    })
    .catch((error) => {
        console.error('Fetch error:', error);
        if (typeof showToast === 'function') {
            showToast('Error', 'Network error: ' + error.message, 'danger');
        }
        if (btn) btn.disabled = false;
    });
}

// Show a Bootstrap confirmation modal before unsubmit
function showUnsubmitConfirmModal(onConfirm) {
    try {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="modal fade" tabindex="-1" id="unsubmitConfirmModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-undo me-2 text-warning"></i>Return Submission to Draft?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This will move the finalized submission back to Draft. You can edit it again afterwards.
                            </div>
                            <p class="mb-0">Are you sure you want to continue?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-warning" id="confirmUnsubmitBtn"><i class="fas fa-undo me-1"></i>Unsubmit</button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(wrapper);
        const modalEl = wrapper.querySelector('#unsubmitConfirmModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        modalEl.querySelector('#confirmUnsubmitBtn').addEventListener('click', () => {
            modal.hide();
            setTimeout(() => onConfirm && onConfirm(), 200);
        });
    } catch (e) {
        // Fallback to simple confirm
        if (window.confirm('Are you sure you want to return this finalized submission to draft status?')) {
            onConfirm && onConfirm();
        }
    }
}

// Success modal after unsubmit
function showUnsubmitSuccessModal(title, onDone) {
    try {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div class="modal fade" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body text-center py-4">
                            <i class="fas fa-undo text-warning" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">${title}</h5>
                            <p class="text-muted">Reloading the page...</p>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(wrapper);
        const modalEl = wrapper.querySelector('.modal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        setTimeout(() => {
            if (typeof onDone === 'function') onDone();
        }, 1500);
    } catch (e) {
        if (typeof onDone === 'function') onDone();
    }
}

// Ensure the function is available globally
window.unsubmitSubmission = unsubmitSubmission;

// Debug: Log when script is loaded
console.log('Unsubmit submission script loaded');
console.log('Current APP_URL:', window.APP_URL);
