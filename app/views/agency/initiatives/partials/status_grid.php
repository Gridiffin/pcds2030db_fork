<?php
/**
 * Status Grid Partial
 * Shows the initiative status grid chart section
 */
?>
<!-- Status Grid Chart Section -->
<div class="container-fluid mb-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-chart-line me-2"></i>Initiative Status Grid
            </h5>
        </div>
        <div class="card-body p-0">
            <div id="status_grid_here">
                <div class="status-grid-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading status grid...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Grid Component -->
<script src="<?php echo asset_url('js', 'components/status-grid.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get initiative ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const initiativeId = urlParams.get('id');
    
    if (initiativeId) {
        // Initialize StatusGrid component with status grid data API
        const apiUrl = "<?php echo rtrim(BASE_URL, '/'); ?>/app/api/simple_gantt_data.php?initiative_id=" + initiativeId;
        const statusGrid = new StatusGrid('status_grid_here', apiUrl);
        
        // Store reference globally for debugging
        window.statusGrid = statusGrid;
        
        // Add a test function to check status data
        window.testStatusData = function() {
            console.log('=== STATUS DATA TEST ===');
            const data = statusGrid.data;
            if (!data || !data.programs) {
                console.log('No data or programs found');
                return;
            }
            
            data.programs.forEach((program, pi) => {
                console.log(`\nProgram ${pi}: ${program.program_name}`);
                if (!program.targets || program.targets.length === 0) {
                    console.log('  No targets');
                    return;
                }
                
                program.targets.forEach((target, ti) => {
                    console.log(`  Target ${ti}: ${target.target_text}`);
                    console.log(`    status_by_period:`, target.status_by_period);
                    
                    if (target.status_by_period) {
                        Object.entries(target.status_by_period).forEach(([periodId, status]) => {
                            console.log(`      Period ${periodId}: "${status}"`);
                        });
                    }
                });
            });
            
            console.log('\nTimeline periods_map:', data.timeline?.periods_map);
        };
        
        // Run the test after a short delay
        setTimeout(() => {
            window.testStatusData();
        }, 1000);
    } else {
        document.getElementById('status_grid_here').innerHTML = 
            '<div class="status-grid-error">No initiative ID provided.</div>';
    }
});
</script>
