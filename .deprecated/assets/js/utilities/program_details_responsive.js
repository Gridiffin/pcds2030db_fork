/**
 * Creates mobile-friendly version of the targets table
 * This script generates a div-based layout for better text wrapping on small screens
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the program details page
    const targetsTable = document.querySelector('.targets-table');
    if (!targetsTable) return;
    
    // Create mobile friendly display
    createMobileFriendlyDisplay();
    
    // Handle window resize
    window.addEventListener('resize', handleWindowResize);
    
    // Initial check
    handleWindowResize();
    
    /**
     * Creates the mobile-friendly div-based layout
     */
    function createMobileFriendlyDisplay() {
        // Get the targets container
        const targetsContainer = document.querySelector('.targets-container');
        if (!targetsContainer) return;
        
        // Create a mobile display container
        const mobileDisplay = document.createElement('div');
        mobileDisplay.className = 'mobile-targets-display';
        mobileDisplay.style.display = 'none'; // Hidden by default
        
        // Get all target rows
        const targetRows = document.querySelectorAll('.program-target-row');
        
        // Process each row
        targetRows.forEach((row, index) => {
            // Extract the data
            const targetCell = row.querySelector('.target-cell');
            const achievementCell = row.querySelector('.achievement-cell');
            
            if (!targetCell || !achievementCell) return;
            
            // Create mobile item
            const mobileItem = document.createElement('div');
            mobileItem.className = 'mobile-target-item';
            
            // Target section
            const targetSection = document.createElement('div');
            targetSection.className = 'mobile-target-section';
            
            const targetLabel = document.createElement('div');
            targetLabel.className = 'mobile-target-label';
            targetLabel.innerHTML = '<i class="fas fa-bullseye me-2"></i>Program Target';
            
            const targetContent = document.createElement('div');
            targetContent.className = 'mobile-target-content';
            targetContent.innerHTML = targetCell.innerHTML;
            
            targetSection.appendChild(targetLabel);
            targetSection.appendChild(targetContent);
            
            // Achievement section
            const achievementSection = document.createElement('div');
            achievementSection.className = 'mobile-achievement-section';
            
            const achievementLabel = document.createElement('div');
            achievementLabel.className = 'mobile-achievement-label';
            achievementLabel.innerHTML = '<i class="fas fa-chart-line me-2"></i>Status & Achievements';
            
            const achievementContent = document.createElement('div');
            achievementContent.className = 'mobile-achievement-content';
            achievementContent.innerHTML = achievementCell.innerHTML;
            
            achievementSection.appendChild(achievementLabel);
            achievementSection.appendChild(achievementContent);
            
            // Add sections to mobile item
            mobileItem.appendChild(targetSection);
            mobileItem.appendChild(achievementSection);
            
            // Add to mobile display
            mobileDisplay.appendChild(mobileItem);
        });
        
        // Add mobile display to container
        targetsContainer.appendChild(mobileDisplay);
    }
    
    /**
     * Handles window resize to toggle between table and div display
     */
    function handleWindowResize() {
        const windowWidth = window.innerWidth;
        const tableCells = document.querySelectorAll('.target-cell, .achievement-cell');
        const tableDisplay = document.querySelector('.targets-table .table-responsive');
        const mobileDisplay = document.querySelector('.mobile-targets-display');
        
        if (!tableDisplay || !mobileDisplay) return;
        
        // Check if any text is overflowing
        let isOverflowing = false;
        let isMobileSize = windowWidth < 768;
        
        // Add extra long text class for better wrapping
        tableCells.forEach(cell => {
            // Force word wrap on all cells
            cell.style.wordBreak = 'break-word';
            cell.style.whiteSpace = 'normal';
            
            // Check if content is wider than cell
            const content = cell.querySelector('.target-content, .achievement-description');
            if (content && content.scrollWidth > cell.clientWidth) {
                isOverflowing = true;
            }
        });
        
        // Switch to mobile view if on small screen or if text is overflowing
        if (isMobileSize || isOverflowing) {
            tableDisplay.style.display = 'none';
            mobileDisplay.style.display = 'block';
        } else {
            tableDisplay.style.display = 'block';
            mobileDisplay.style.display = 'none';
        }
    }
});
