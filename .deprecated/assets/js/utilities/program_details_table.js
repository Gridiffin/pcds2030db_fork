/**
 * Program Details Table Enhancement
 * Ensures text wrapping works properly in all browsers
 */
document.addEventListener('DOMContentLoaded', function() {
    // Find target and achievement cells
    const targetCells = document.querySelectorAll('.target-cell');
    const achievementCells = document.querySelectorAll('.achievement-cell');
    
    // Adjust table layout if needed
    const targetTable = document.querySelector('.targets-table table');
    if (targetTable) {
        targetTable.style.tableLayout = 'fixed';
        targetTable.style.width = '100%';
    }
    
    // Ensure content wraps properly
    const allCells = [...targetCells, ...achievementCells];
    allCells.forEach(cell => {
        cell.style.whiteSpace = 'normal';
        cell.style.wordWrap = 'break-word';
        cell.style.wordBreak = 'normal';
        cell.style.overflowWrap = 'break-word';
        
        // For IE compatibility
        if (cell.querySelector('.target-content, .achievement-description')) {
            const content = cell.querySelector('.target-content, .achievement-description');
            content.style.wordBreak = 'break-word';
        }
    });
    
    // Disable horizontal scrolling on the table container
    const tableResponsive = document.querySelector('.targets-container .table-responsive');
    if (tableResponsive) {
        tableResponsive.style.overflowX = 'visible';
    }
});
