/**
 * Shared Modal Components
 * Provides reusable modal functionality across admin pages
 */

/**
 * Creates a confirmation modal with customizable content
 * @param {Object} config - Modal configuration
 * @param {string} config.id - Modal ID
 * @param {string} config.title - Modal title
 * @param {string} config.message - Modal message
 * @param {string} config.confirmText - Confirm button text
 * @param {string} config.confirmClass - Confirm button CSS class
 * @param {Function} config.onConfirm - Callback for confirm button
 * @param {string} config.cancelText - Cancel button text (default: "Cancel")
 * @returns {HTMLElement} Modal element
 */
function createConfirmationModal(config) {
    const {
        id,
        title,
        message,
        confirmText = 'Confirm',
        confirmClass = 'btn-primary',
        onConfirm,
        cancelText = 'Cancel'
    } = config;

    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = id;
    modal.tabIndex = -1;
    modal.setAttribute('aria-labelledby', `${id}Label`);
    modal.setAttribute('aria-hidden', 'true');

    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="${id}Label">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="modal-message">${message}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${cancelText}</button>
                    <button type="button" class="btn ${confirmClass} confirm-action">${confirmText}</button>
                </div>
            </div>
        </div>
    `;

    // Add event listener for confirm button
    const confirmButton = modal.querySelector('.confirm-action');
    confirmButton.addEventListener('click', () => {
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    });

    return modal;
}

/**
 * Creates a delete confirmation modal
 * @param {Object} config - Modal configuration
 * @param {string} config.id - Modal ID
 * @param {string} config.itemType - Type of item being deleted (e.g., "program", "user")
 * @param {Function} config.onConfirm - Callback for confirm deletion
 * @returns {HTMLElement} Modal element
 */
function createDeleteModal(config) {
    const { id, itemType, onConfirm } = config;

    return createConfirmationModal({
        id: id,
        title: `Delete ${itemType}`,
        message: `Are you sure you want to delete this ${itemType.toLowerCase()}? This action cannot be undone.`,
        confirmText: 'Delete',
        confirmClass: 'btn-danger',
        onConfirm: onConfirm,
        cancelText: 'Cancel'
    });
}

/**
 * Creates an actions modal with customizable action buttons
 * @param {Object} config - Modal configuration
 * @param {string} config.id - Modal ID
 * @param {string} config.title - Modal title
 * @param {Array} config.actions - Array of action objects
 * @returns {HTMLElement} Modal element
 */
function createActionsModal(config) {
    const { id, title, actions = [] } = config;

    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = id;
    modal.tabIndex = -1;
    modal.setAttribute('aria-labelledby', `${id}Label`);
    modal.setAttribute('aria-hidden', 'true');

    const actionsHtml = actions.map(action => {
        const { text, icon, class: btnClass = 'btn-outline-primary', onClick, href, separator } = action;
        
        if (separator) {
            return '<hr>';
        }

        const iconHtml = icon ? `<i class="${icon} me-2"></i>` : '';
        
        if (href) {
            return `<a href="${href}" class="btn ${btnClass}">${iconHtml}${text}</a>`;
        } else {
            return `<button type="button" class="btn ${btnClass}" data-action="${text.toLowerCase().replace(/\s+/g, '-')}">${iconHtml}${text}</button>`;
        }
    }).join('');

    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="${id}Label">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="item-info mb-3">
                        <h6 class="item-name-display"></h6>
                        <small class="text-muted item-type-display"></small>
                    </div>
                    <div class="d-grid gap-2">
                        ${actionsHtml}
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add event listeners for action buttons
    modal.addEventListener('click', (event) => {
        const actionButton = event.target.closest('[data-action]');
        if (actionButton) {
            const actionName = actionButton.dataset.action;
            const action = actions.find(a => a.text.toLowerCase().replace(/\s+/g, '-') === actionName);
            if (action && typeof action.onClick === 'function') {
                action.onClick();
            }
        }
    });

    return modal;
}

/**
 * Shows a modal with the given ID
 * @param {string} modalId - The ID of the modal to show
 * @param {Object} data - Optional data to populate modal fields
 */
function showModal(modalId, data = {}) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with ID '${modalId}' not found`);
        return;
    }

    // Populate modal fields if data is provided
    if (data.name) {
        const nameDisplay = modal.querySelector('.item-name-display, .program-name-display');
        if (nameDisplay) nameDisplay.textContent = data.name;
    }

    if (data.type) {
        const typeDisplay = modal.querySelector('.item-type-display, .program-type-display');
        if (typeDisplay) typeDisplay.textContent = data.type;
    }

    if (data.message) {
        const messageDisplay = modal.querySelector('.modal-message');
        if (messageDisplay) messageDisplay.textContent = data.message;
    }

    // Show the modal
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}

/**
 * Hides a modal with the given ID
 * @param {string} modalId - The ID of the modal to hide
 */
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
        }
    }
}

/**
 * Creates and shows a toast notification
 * @param {Object} config - Toast configuration
 * @param {string} config.message - Toast message
 * @param {string} config.type - Toast type ('success', 'error', 'warning', 'info')
 * @param {number} config.duration - Auto-hide duration in milliseconds (default: 5000)
 */
function showToast(config) {
    const { message, type = 'info', duration = 5000 } = config;

    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1080';
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = `toast-${Date.now()}`;
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type === 'error' ? 'danger' : type}`;
    toast.id = toastId;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toast);

    // Show toast
    const toastInstance = new bootstrap.Toast(toast, {
        autohide: true,
        delay: duration
    });
    toastInstance.show();

    // Remove toast element after hiding
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Export functions for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        createConfirmationModal,
        createDeleteModal,
        createActionsModal,
        showModal,
        hideModal,
        showToast
    };
} else {
    // Make functions globally available
    window.SharedModals = {
        createConfirmationModal,
        createDeleteModal,
        createActionsModal,
        showModal,
        hideModal,
        showToast
    };
}