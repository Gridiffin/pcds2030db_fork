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
        
        
        
        
    } else {
        document.getElementById('status_grid_here').innerHTML = 
            '<div class="status-grid-error">No initiative ID provided.</div>';
    }
});
</script>
