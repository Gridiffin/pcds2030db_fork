<?php
/**
 * Chart Display Partial
 * Displays chart visualization of outcome data
 */
?>

<div class="chart-container">
    <div class="chart-header">
        <div>
            <h4 class="chart-title">Data Visualization</h4>
            <p class="chart-subtitle">Interactive chart view of outcome data</p>
        </div>
        <div class="chart-controls">
            <div class="chart-type-selector">
                <button type="button" class="chart-type-btn active" data-type="line">
                    <i class="fas fa-chart-line"></i> Line
                </button>
                <button type="button" class="chart-type-btn" data-type="bar">
                    <i class="fas fa-chart-bar"></i> Bar
                </button>
                <button type="button" class="chart-type-btn" data-type="pie">
                    <i class="fas fa-chart-pie"></i> Pie
                </button>
            </div>
        </div>
    </div>
    
    <div class="chart-content">
        <div class="chart-canvas-container">
            <canvas id="metricChart" class="chart-canvas"></canvas>
        </div>
        
        <div class="chart-actions">
            <button type="button" class="chart-control-btn" id="exportChart">
                <i class="fas fa-download"></i> Export Chart
            </button>
            <button type="button" class="chart-control-btn" id="exportData">
                <i class="fas fa-file-csv"></i> Export Data
            </button>
        </div>
    </div>
</div>
