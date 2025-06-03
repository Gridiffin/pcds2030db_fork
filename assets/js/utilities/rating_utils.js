/**
 * Rating Utilities
 * 
 * Shared functions for handling program ratings in the frontend.
 */

/**
 * Initialize rating pill selection behavior
 */
function initRatingPills() {
    const ratingPills = document.querySelectorAll('.rating-pill');
    const ratingInput = document.getElementById('rating');
    
    if (ratingPills.length && ratingInput) {
        ratingPills.forEach(pill => {
            pill.addEventListener('click', function() {
                // Remove active class from all pills
                ratingPills.forEach(p => p.classList.remove('active'));
                
                // Add active class to clicked pill
                this.classList.add('active');
                
                // Update hidden input
                ratingInput.value = this.getAttribute('data-rating');
            });
        });
    }
}

/**
 * Get color class for a rating value
 * @param {string} rating - Rating value
 * @returns {string} CSS color class
 */
function getRatingColorClass(rating) {
    switch (rating) {
        case 'target-achieved':
        case 'completed':
            return 'success'; // Green for Monthly Target Achieved
        case 'delayed':
        case 'severe-delay':
            return 'danger';  // Red for Delayed
        case 'on-track':
        case 'on-track-yearly':
            return 'warning'; // Yellow for Still on Track for the Year
        case 'not-started':
        default:
            return 'secondary'; // Gray for Not Started
    }
}

/**
 * Get icon class for a rating value
 * @param {string} rating - Rating value
 * @returns {string} FontAwesome icon class
 */
function getRatingIconClass(rating) {
    switch (rating) {
        case 'target-achieved':
        case 'completed':
            return 'fas fa-check-circle'; // Success icon
        case 'on-track':
        case 'on-track-yearly':
            return 'fas fa-calendar-check'; // Calendar check icon
        case 'delayed':
            return 'fas fa-exclamation-triangle'; // Warning icon
        case 'severe-delay':
            return 'fas fa-exclamation-circle'; // Stronger warning icon
        case 'not-started': 
        default:
            return 'fas fa-hourglass-start'; // Not started icon
    }
}

/**
 * Create a rating badge element
 * @param {string} rating - Rating value
 * @returns {HTMLElement} Badge element
 */
function createRatingBadge(rating) {
    const badge = document.createElement('span');
    badge.className = `badge bg-${getRatingColorClass(rating)}`;
    badge.textContent = rating.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
    return badge;
}

/**
 * Create a rich rating badge with icon
 * @param {string} rating - Rating value
 * @returns {HTMLElement} Badge element with icon
 */
function createRichRatingBadge(rating) {
    const badge = document.createElement('span');
    badge.className = `badge bg-${getRatingColorClass(rating)}`;
    
    const icon = document.createElement('i');
    icon.className = `${getRatingIconClass(rating)} me-1`;
    
    badge.appendChild(icon);
    badge.appendChild(document.createTextNode(
        rating.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())
    ));
    
    return badge;
}

// For backward compatibility - use function declarations to avoid redeclaration errors
if (typeof initStatusPills === 'undefined') {
    function initStatusPills() {
        return initRatingPills();
    }
}

if (typeof getStatusColorClass === 'undefined') {
    function getStatusColorClass(status) {
        return getRatingColorClass(status);
    }
}

if (typeof getStatusIconClass === 'undefined') {
    function getStatusIconClass(status) {
        return getRatingIconClass(status);
    }
}

if (typeof createStatusBadge === 'undefined') {
    function createStatusBadge(status) {
        return createRatingBadge(status);
    }
}

if (typeof createRichStatusBadge === 'undefined') {
    function createRichStatusBadge(status) {
        return createRichRatingBadge(status);
    }
}

// Initialize on document load if auto-init is needed
document.addEventListener('DOMContentLoaded', function() {
    // Auto-initialize rating pills if data-auto-init attribute exists
    if (document.querySelector('.rating-pills[data-auto-init]') || document.querySelector('.status-pills[data-auto-init]')) {
        initRatingPills();
    }
});
