/**
 * Program History JavaScript
 * Handles the program edit history feature interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize history tooltips
    initHistoryTooltips();
    
    // Initialize history panels
    initHistoryPanels();
    
    // Handle targets history display
    initTargetsHistory();
    
    // Initialize field history toggles
    initFieldHistoryToggles();
    
    // Initialize Load More functionality
    initLoadMoreHistory();
});

/**
 * Initialize tooltip functionality for history indicators
 */
function initHistoryTooltips() {
    const historyIndicators = document.querySelectorAll('.history-indicator');
    
    historyIndicators.forEach(indicator => {
        indicator.addEventListener('mouseenter', function(e) {
            const tooltipId = this.getAttribute('data-tooltip');
            const tooltip = document.getElementById(tooltipId);
            
            if (tooltip) {
                // Position tooltip
                const rect = this.getBoundingClientRect();
                tooltip.style.top = `${rect.bottom + window.scrollY + 5}px`;
                tooltip.style.left = `${rect.left + window.scrollX - 100}px`;
                
                // Show tooltip
                tooltip.classList.add('show');
            }
        });
        
        indicator.addEventListener('mouseleave', function(e) {
            const tooltipId = this.getAttribute('data-tooltip');
            const tooltip = document.getElementById(tooltipId);
            
            if (tooltip) {
                // Hide tooltip after a small delay (to handle gap between tooltip and indicator)
                setTimeout(() => {
                    if (!tooltip.matches(':hover')) {
                        tooltip.classList.remove('show');
                    }
                }, 300);
            }
        });
    });
    
    // Handle tooltip hover
    document.querySelectorAll('.history-tooltip').forEach(tooltip => {
        tooltip.addEventListener('mouseleave', function() {
            this.classList.remove('show');
        });
    });
}

/**
 * Initialize collapsible history panels
 */
function initHistoryPanels() {
    const historyToggleBtns = document.querySelectorAll('.history-toggle-btn');
    
    historyToggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const panelId = this.getAttribute('data-target');
            const panel = document.getElementById(panelId);
            
            if (panel) {
                const isVisible = panel.style.display !== 'none';
                panel.style.display = isVisible ? 'none' : 'block';
                this.innerHTML = isVisible ? '<i class="fas fa-history"></i> Show History' : '<i class="fas fa-times"></i> Hide History';
            }
        });
    });
}

/**
 * Initialize targets history display functionality
 */
function initTargetsHistory() {
    const targetHistoryBtns = document.querySelectorAll('.target-history-btn');
    
    targetHistoryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const historyContainer = document.getElementById(targetId);
            
            if (historyContainer) {
                const isVisible = historyContainer.style.display !== 'none';
                historyContainer.style.display = isVisible ? 'none' : 'block';
                this.innerHTML = isVisible ? '<i class="fas fa-history"></i>' : '<i class="fas fa-times"></i>';
            }
        });
    });
}

/**
 * Toggle field history visibility
 * @param {string} fieldId ID of the field's history container to toggle
 */
function toggleFieldHistory(fieldId) {
    const historyContainer = document.getElementById(fieldId);
    if (historyContainer) {
        const isVisible = historyContainer.style.display !== 'none';
        historyContainer.style.display = isVisible ? 'none' : 'block';
        
        // Update the toggle button text/icon if it exists
        const toggleBtn = document.querySelector(`[data-history-target="${fieldId}"]`);
        if (toggleBtn) {
            toggleBtn.innerHTML = isVisible ? 
                '<i class="fas fa-history"></i> Show History' : 
                '<i class="fas fa-times"></i> Hide History';
        }
    }
}

/**
 * Initialize field history toggling
 */
function initFieldHistoryToggles() {
    document.querySelectorAll('.field-history-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-history-target');
            toggleFieldHistory(targetId);
        });
    });
}

/**
 * Initialize Load More functionality for field history
 */
function initLoadMoreHistory() {
    console.log('Initializing Load More History functionality');
    
    document.addEventListener('click', function(e) {
        console.log('Click detected on:', e.target);
        if (e.target.classList.contains('load-more-history')) {
            console.log('Load More button clicked!');
            handleLoadMoreHistory(e.target);
        }
    });
}

/**
 * Handle Load More button click for field history
 * @param {HTMLElement} button The Load More button that was clicked
 */
async function handleLoadMoreHistory(button) {
    console.log('handleLoadMoreHistory called with button:', button);
    
    const fieldName = button.getAttribute('data-field');
    const programId = button.getAttribute('data-program-id');
    const periodId = button.getAttribute('data-period-id');
    const currentOffset = parseInt(button.getAttribute('data-offset'));
    const totalCount = parseInt(button.getAttribute('data-total'));
    
    console.log('Data attributes:', {
        fieldName, programId, periodId, currentOffset, totalCount
    });
    
    if (!fieldName || !programId || !periodId) {
        console.error('Missing required data attributes for Load More');
        return;
    }
    
    // Show loading spinner
    const spinner = button.parentElement.querySelector('.load-more-spinner');
    const container = button.parentElement;
    
    button.style.display = 'none';
    if (spinner) {
        spinner.classList.remove('d-none');
    }
    
    try {
        // Send AJAX request - get the APP_URL from the page
        const appUrl = window.APP_URL || document.body.getAttribute('data-app-url') || '';
        console.log('APP_URL:', window.APP_URL);
        console.log('Document body data-app-url:', document.body.getAttribute('data-app-url'));
        
        // Construct the AJAX URL with proper fallback
        let ajaxUrl;
        if (appUrl) {
            ajaxUrl = `${appUrl}/app/ajax/get_field_history.php`;
        } else {
            // Fallback to relative path
            ajaxUrl = '../../../ajax/get_field_history.php';
        }
        
        console.log('Making AJAX request to:', ajaxUrl);
        console.log('Request data:', {
            program_id: programId,
            period_id: periodId,
            field_name: fieldName,
            offset: currentOffset,
            limit: 5
        });
        
        const response = await fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                program_id: programId,
                period_id: periodId,
                field_name: fieldName,
                offset: currentOffset,
                limit: 5
            })
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success && data.entries) {
            // Find the history list to append to
            const historyList = container.closest('.history-complete').querySelector('.history-list');
            
            if (historyList) {
                // Append new history items
                data.entries.forEach(entry => {
                    const listItem = createHistoryListItem(entry, fieldName);
                    listItem.classList.add('newly-loaded');
                    historyList.appendChild(listItem);
                    
                    // Remove animation class after animation completes
                    setTimeout(() => {
                        listItem.classList.remove('newly-loaded');
                    }, 400);
                });
                
                // Update button state
                if (data.has_more) {
                    // Update offset and remaining count
                    const newOffset = data.next_offset;
                    const remainingCount = data.total_count - newOffset;
                    
                    button.setAttribute('data-offset', newOffset);
                    button.innerHTML = `<i class="fas fa-chevron-down me-1"></i>Load More (${remainingCount} remaining)`;
                    button.style.display = 'inline-block';
                } else {
                    // No more items, remove the Load More container
                    container.style.display = 'none';
                }
            }
        } else {
            throw new Error(data.error || 'Failed to load history');
        }
        
    } catch (error) {
        console.error('Error loading more history:', error);
        
        // Show error message
        const errorMsg = document.createElement('div');
        errorMsg.className = 'alert alert-danger alert-sm mt-2';
        errorMsg.textContent = 'Failed to load more history. Please try again.';
        container.appendChild(errorMsg);
        
        // Hide error after 5 seconds
        setTimeout(() => {
            errorMsg.remove();
        }, 5000);
        
        // Show button again
        button.style.display = 'inline-block';
    } finally {
        // Hide loading spinner
        if (spinner) {
            spinner.classList.add('d-none');
        }
    }
}

/**
 * Create a history list item element from entry data
 * @param {Object} entry History entry data
 * @param {string} fieldName Field name for special formatting
 * @returns {HTMLElement} List item element
 */
function createHistoryListItem(entry, fieldName) {
    const li = document.createElement('li');
    li.className = 'history-list-item';
    
    const valueDiv = document.createElement('div');
    valueDiv.className = 'history-list-value';
    
    // Handle special formatting for targets field
    if (fieldName === 'targets' && Array.isArray(entry.value)) {
        let content = '';
        entry.value.forEach((target, index) => {
            content += `<strong>Target ${index + 1}:</strong> ${escapeHtml(target.target_text || target.text || '')}<br>`;
        });
        valueDiv.innerHTML = content;
    } else {
        valueDiv.textContent = entry.value;
    }
    
    const metaDiv = document.createElement('div');
    metaDiv.className = 'history-list-meta';
    metaDiv.textContent = entry.formatted_date;
    
    // Add draft/final badge if applicable
    if (entry.submission_id && entry.submission_id > 0) {
        const badge = document.createElement('span');
        badge.className = entry.is_draft ? 'history-draft-badge' : 'history-final-badge';
        badge.textContent = entry.is_draft ? 'Draft' : 'Final';
        metaDiv.appendChild(document.createTextNode(' '));
        metaDiv.appendChild(badge);
    }
    
    li.appendChild(valueDiv);
    li.appendChild(metaDiv);
    
    return li;
}

/**
 * Escape HTML characters for safe display
 * @param {string} text Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
