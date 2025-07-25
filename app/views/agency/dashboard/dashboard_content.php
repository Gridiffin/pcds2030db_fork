<?php
/**
 * Dashboard Content
 * 
 * Main content file for the agency dashboard using base.php layout
 */

// This file is included by base.php as the $contentFile
?>

<!-- Main Content -->
<main>
    <div class="container-fluid">
        <!-- Initiatives Section -->
        <?php require_once __DIR__ . '/partials/initiatives_section.php'; ?>
        
        <!-- Programs Section -->
        <?php require_once __DIR__ . '/partials/programs_section.php'; ?>
    </div>
</main>

<!-- Pass chart data to JavaScript -->
<script>
    // Initialize chart with data
    window.programRatingChartData = {
        labels: <?php echo json_encode($chartData['labels']); ?>,
        data: <?php echo json_encode($chartData['data']); ?>
    };
    
    // Pass current period ID for AJAX requests
    window.currentPeriodId = <?php echo json_encode($period_id); ?>;
    
    
</script>
