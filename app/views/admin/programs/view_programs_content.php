<?php
/**
 * Admin View Programs Content - Programs List Content
 * This file contains the main content for the admin view programs page
 */
?>

<main>
    <div class="container-fluid">
        <!-- Toast Notification -->
        <?php if (!empty($message)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast('<?= ucfirst($messageType) ?>', <?= json_encode($message) ?>, '<?= $messageType ?>');
                });
            </script>
        <?php endif; ?>

        <!-- Main Content Card -->
        <div class="content-card shadow-sm">
            <div class="card-body p-0">
                <div class="p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0 d-flex align-items-center">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Finalized Programs Across All Agencies
                            <span class="badge bg-success ms-2" title="These programs have finalized submissions">
                                <i class="fas fa-check me-1"></i> Finalized
                            </span>
                            <span class="badge bg-secondary ms-2" id="finalized-count"><?php echo count($programs_with_submissions); ?></span>
                        </h5>
                        
                        <!-- Admin Actions -->
                        <div class="admin-actions">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshPrograms()" title="Refresh programs list">
                                    <i class="fas fa-sync-alt me-1"></i> Refresh
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="exportPrograms()" title="Export programs data">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Programs Filters -->
                <?php 
                $filters = ['agency', 'initiative', 'status'];
                $filterPrefix = 'admin';
                require_once __DIR__ . '/partials/admin_program_filters.php'; 
                ?>
                
                <div class="p-4">
                    <div class="programs-container" id="adminProgramsContainer">
                        <?php if (empty($programs_with_submissions)): ?>
                            <div class="programs-empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <div class="empty-title">No Finalized Programs Found</div>
                                <div class="empty-description">No finalized programs found across all agencies.</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($programs_with_submissions as $program): 
                                $show_rating = true;
                                $show_agency = true; // Admin view shows agency information
                                require __DIR__ . '/partials/admin_program_row.php';
                            endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination for Programs -->
                    <div class="mt-4" id="adminProgramsPagination"></div>
                    <div class="text-muted small mt-2" id="adminProgramsCounter"></div>
                </div>
            </div>
        </div>

        <!-- JavaScript data and initialization -->
        <script>
            // Make program data available to JavaScript
            window.allPrograms = <?php echo json_encode($programs); ?>;
            window.currentUserRole = 'admin';
            window.currentUserId = '<?php echo $_SESSION['user_id'] ?? ''; ?>';
            
            // Admin-specific functions
            function refreshPrograms() {
                location.reload();
            }
            
            function exportPrograms() {
                // Placeholder for export functionality
                showToast('Info', 'Export functionality will be implemented soon.', 'info');
            }
        </script>
    </div>
</main>