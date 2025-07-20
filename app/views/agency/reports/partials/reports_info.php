<?php
/**
 * Reports Info Partial
 * Information about different report types
 */
?>

<div class="card reports-info shadow-sm">
    <div class="card-header">
        <h5 class="card-title">About Reports</h5>
    </div>
    <div class="card-body">
        <div class="reports-info-grid">
            <div class="reports-info-section">
                <div class="reports-info-icon program">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="reports-info-content">
                    <h6 class="reports-info-title">Program Reports</h6>
                    <p class="reports-info-description">
                        These reports contain detailed information about your specific programs including 
                        progress tracking, achievements, and targets for the selected reporting period.
                    </p>
                </div>
            </div>
            
            <div class="reports-info-section">
                <div class="reports-info-icon sector">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="reports-info-content">
                    <h6 class="reports-info-title">Sector Reports</h6>
                    <p class="reports-info-description">
                        These reports provide an overview of your sector's performance outcomes, 
                        aggregated data, and comparative analysis across different programs.
                    </p>
                </div>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">Key Features</h6>
                <ul class="reports-feature-list">
                    <li>
                        <i class="fas fa-download"></i>
                        Download reports in PDF or PowerPoint format
                    </li>
                    <li>
                        <i class="fas fa-eye"></i>
                        View reports directly in your browser
                    </li>
                    <li>
                        <i class="fas fa-calendar-alt"></i>
                        Access historical reports by period
                    </li>
                    <li>
                        <i class="fas fa-filter"></i>
                        Filter reports by type and period
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">Report Types</h6>
                <ul class="reports-feature-list">
                    <li>
                        <i class="fas fa-file-pdf"></i>
                        PDF documents for formal sharing
                    </li>
                    <li>
                        <i class="fas fa-file-powerpoint"></i>
                        PowerPoint presentations for meetings
                    </li>
                    <li>
                        <i class="fas fa-star"></i>
                        Recent reports are highlighted
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        Generated automatically by period
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="reports-alert info mt-3">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> Reports are generated automatically at the end of each reporting period. 
            If you don't see a report for a specific period, it may still be in generation or the period may not have concluded yet.
        </div>
    </div>
</div>
