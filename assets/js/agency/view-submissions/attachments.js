/**
 * Attachment handling and display logic
 */

import { formatFileSize } from './logic.js';

/**
 * Initialize attachment handlers
 */
export function initializeAttachmentHandlers() {
    // Handle attachment downloads
    initializeDownloadHandlers();
    
    // Handle attachment previews (if applicable)
    initializePreviewHandlers();
    
    // Update file size displays
    updateFileSizeDisplays();
}

/**
 * Initialize download handlers for attachments
 */
function initializeDownloadHandlers() {
    const downloadLinks = document.querySelectorAll('[data-action="download-attachment"]');
    
    downloadLinks.forEach(link => {
        link.addEventListener('click', handleAttachmentDownload);
    });
}

/**
 * Handle attachment download
 * @param {Event} event - Click event
 */
function handleAttachmentDownload(event) {
    const link = event.currentTarget;
    const attachmentId = link.dataset.attachmentId;
    const fileName = link.dataset.fileName;
    
    if (!attachmentId) {
        console.warn('No attachment ID found for download');
        return;
    }
    
    // Add loading state
    showDownloadLoading(link);
    
    // Track download
    console.log('Downloading attachment:', fileName || attachmentId);
    
    // Allow default link behavior to proceed
    // The loading state will be cleared when page unloads or user returns
}

/**
 * Initialize preview handlers for supported file types
 */
function initializePreviewHandlers() {
    const previewLinks = document.querySelectorAll('[data-action="preview-attachment"]');
    
    previewLinks.forEach(link => {
        link.addEventListener('click', handleAttachmentPreview);
    });
}

/**
 * Handle attachment preview
 * @param {Event} event - Click event
 */
function handleAttachmentPreview(event) {
    event.preventDefault();
    
    const link = event.currentTarget;
    const attachmentUrl = link.href;
    const fileName = link.dataset.fileName;
    const fileType = getFileType(fileName);
    
    if (canPreview(fileType)) {
        openPreviewModal(attachmentUrl, fileName, fileType);
    } else {
        // Fallback to download
        window.open(attachmentUrl, '_blank');
    }
}

/**
 * Update file size displays
 */
function updateFileSizeDisplays() {
    const sizeElements = document.querySelectorAll('[data-file-size]');
    
    sizeElements.forEach(element => {
        const bytes = parseInt(element.dataset.fileSize);
        if (bytes) {
            element.textContent = formatFileSize(bytes);
        }
    });
}

/**
 * Show loading state on download link
 * @param {HTMLElement} link - Download link element
 */
function showDownloadLoading(link) {
    const icon = link.querySelector('i');
    if (icon) {
        icon.className = 'fas fa-spinner fa-spin';
    }
    
    link.style.opacity = '0.7';
    link.style.pointerEvents = 'none';
}

/**
 * Get file type from filename
 * @param {string} fileName - File name
 * @returns {string} - File extension/type
 */
function getFileType(fileName) {
    if (!fileName) return '';
    
    const extension = fileName.split('.').pop().toLowerCase();
    return extension;
}

/**
 * Check if file type can be previewed
 * @param {string} fileType - File type/extension
 * @returns {boolean} - Whether file can be previewed
 */
function canPreview(fileType) {
    const previewableTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'md'];
    return previewableTypes.includes(fileType);
}

/**
 * Open preview modal for attachment
 * @param {string} url - Attachment URL
 * @param {string} fileName - File name
 * @param {string} fileType - File type
 */
function openPreviewModal(url, fileName, fileType) {
    // Create or show preview modal
    let modal = document.getElementById('attachmentPreviewModal');
    
    if (!modal) {
        modal = createPreviewModal();
        document.body.appendChild(modal);
    }
    
    // Update modal content
    updatePreviewModal(modal, url, fileName, fileType);
    
    // Show modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

/**
 * Create preview modal element
 * @returns {HTMLElement} - Modal element
 */
function createPreviewModal() {
    const modal = document.createElement('div');
    modal.id = 'attachmentPreviewModal';
    modal.className = 'modal fade';
    modal.setAttribute('tabindex', '-1');
    modal.setAttribute('aria-hidden', 'true');
    
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file me-2"></i>
                        <span class="file-name">Preview</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="preview-container text-center">
                        <div class="loading-spinner">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Loading preview...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-primary download-btn" target="_blank">
                        <i class="fas fa-download me-2"></i>Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    return modal;
}

/**
 * Update preview modal content
 * @param {HTMLElement} modal - Modal element
 * @param {string} url - Attachment URL
 * @param {string} fileName - File name
 * @param {string} fileType - File type
 */
function updatePreviewModal(modal, url, fileName, fileType) {
    const fileNameElement = modal.querySelector('.file-name');
    const previewContainer = modal.querySelector('.preview-container');
    const downloadBtn = modal.querySelector('.download-btn');
    
    // Update file name
    fileNameElement.textContent = fileName;
    
    // Update download link
    downloadBtn.href = url;
    
    // Create preview content based on file type
    let previewContent = '';
    
    switch (fileType) {
        case 'pdf':
            previewContent = `<iframe src="${url}" style="width: 100%; height: 500px; border: none;"></iframe>`;
            break;
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            previewContent = `<img src="${url}" class="img-fluid" alt="${fileName}" style="max-height: 500px;">`;
            break;
        default:
            previewContent = `
                <div class="text-center py-4">
                    <i class="fas fa-file fa-3x text-muted mb-3"></i>
                    <p>Preview not available for this file type.</p>
                    <p class="text-muted">Click download to view the file.</p>
                </div>
            `;
    }
    
    previewContainer.innerHTML = previewContent;
}
