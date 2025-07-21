/**
 * Target interactions and display logic
 */

import { calculateTargetStats } from './logic.js';

/**
 * Initialize target interaction handlers
 */
export function initializeTargetInteractions() {
    // Update target statistics display
    updateTargetStatistics();
    
    // Handle target card interactions
    initializeTargetCards();
    
    // Handle target status updates (if needed for future functionality)
    initializeTargetStatusHandlers();
}

/**
 * Update target statistics display
 */
function updateTargetStatistics() {
    const targetsContainer = document.querySelector('.targets-container');
    if (!targetsContainer) return;
    
    const targetCards = targetsContainer.querySelectorAll('.target-card');
    const targets = Array.from(targetCards).map(card => {
        return {
            status_indicator: card.dataset.status || 'unknown'
        };
    });
    
    const stats = calculateTargetStats(targets);
    updateStatsDisplay(stats);
}

/**
 * Update statistics display in the UI
 * @param {Object} stats - Target statistics
 */
function updateStatsDisplay(stats) {
    const statsContainer = document.querySelector('.target-stats');
    if (!statsContainer) return;
    
    // Update individual stat counters
    const statElements = {
        total: statsContainer.querySelector('[data-stat="total"]'),
        on_track: statsContainer.querySelector('[data-stat="on_track"]'),
        at_risk: statsContainer.querySelector('[data-stat="at_risk"]'),
        behind: statsContainer.querySelector('[data-stat="behind"]'),
        completed: statsContainer.querySelector('[data-stat="completed"]')
    };
    
    Object.keys(statElements).forEach(key => {
        const element = statElements[key];
        if (element) {
            element.textContent = stats[key] || 0;
        }
    });
}

/**
 * Initialize target card interactions
 */
function initializeTargetCards() {
    const targetCards = document.querySelectorAll('.target-card');
    
    targetCards.forEach(card => {
        // Add hover effects
        card.addEventListener('mouseenter', handleTargetCardHover);
        card.addEventListener('mouseleave', handleTargetCardLeave);
        
        // Add click handlers for future expansion
        card.addEventListener('click', handleTargetCardClick);
    });
}

/**
 * Handle target card hover
 * @param {Event} event - Mouse enter event
 */
function handleTargetCardHover(event) {
    const card = event.currentTarget;
    card.style.transform = 'translateY(-2px)';
    card.style.boxShadow = '0 0.5rem 1rem rgba(40, 167, 69, 0.2)';
}

/**
 * Handle target card leave
 * @param {Event} event - Mouse leave event
 */
function handleTargetCardLeave(event) {
    const card = event.currentTarget;
    card.style.transform = '';
    card.style.boxShadow = '';
}

/**
 * Handle target card click
 * @param {Event} event - Click event
 */
function handleTargetCardClick(event) {
    const card = event.currentTarget;
    const targetId = card.dataset.targetId;
    
    if (targetId) {
        console.log('Target card clicked:', targetId);
        // Future: Could expand target details or show edit modal
    }
}

/**
 * Initialize target status handlers for future functionality
 */
function initializeTargetStatusHandlers() {
    const statusBadges = document.querySelectorAll('.target-status');
    
    statusBadges.forEach(badge => {
        // Add tooltip or click handler for status info
        badge.addEventListener('click', handleStatusClick);
    });
}

/**
 * Handle status badge click
 * @param {Event} event - Click event
 */
function handleStatusClick(event) {
    event.stopPropagation();
    const badge = event.currentTarget;
    const status = badge.dataset.status;
    
    console.log('Status clicked:', status);
    // Future: Could show status change options or detailed info
}
