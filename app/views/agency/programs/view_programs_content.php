<?php
/**
 * View Programs Content - Agency Programs List Content
 * This file contains the main content for the view programs page
 */
?>

<!-- Toast Notification for Program Creation/Deletion -->
<?php if (!empty($message)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('<?= ucfirst($messageType) ?>', <?= json_encode($message) ?>, '<?= $messageType ?>');
        });
    </script>
<?php endif; ?>

<div class="content-wrapper">
    <div class="page-content">

        <!-- Create Program Button -->
        <div class="mb-3">
            <a href="<?php echo APP_URL; ?>/app/views/agency/programs/create_program.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Create New Program
            </a>
        </div>

        <!-- Programs with Draft Submissions Card -->
        <div class="card shadow-sm mb-4 w-100 draft-programs-card">
            <div class="card-header d-flex justify-content-between align-items-center bg-light border-start border-warning border-4">
                <h5 class="card-title view-programs-card-title m-0 d-flex align-items-center">
                    <i class="fas fa-edit text-warning me-2"></i>
                    Programs with Draft Submissions
                    <span class="badge bg-warning text-dark ms-2" title="These programs have draft submissions that can be edited">
                        <i class="fas fa-pencil-alt me-1"></i> Draft Submissions
                    </span>
                    <span class="badge bg-secondary ms-2" id="draft-count"><?php echo count($programs_with_drafts); ?></span>
                </h5>
            </div>
            
            <!-- Draft Programs Filters -->
            <?php 
            $filters = ['rating'];
            $filterPrefix = 'draft';
            require_once __DIR__ . '/partials/program_filters.php'; 
            ?>
            
            <div class="card-body pt-2 p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0" id="draftProgramsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable" data-sort="name">
                                    <i class="fas fa-project-diagram me-1"></i>Program Information 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="sortable initiative-display" data-sort="initiative">
                                    <i class="fas fa-lightbulb me-1"></i>Initiative 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="sortable" data-sort="rating">
                                    <i class="fas fa-chart-line me-1"></i>Progress Rating 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="sortable" data-sort="date">
                                    <i class="fas fa-clock me-1"></i>Last Updated 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-cog me-1"></i>Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($programs_with_drafts)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">No programs with draft submissions found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($programs_with_drafts as $program): 
                                    $show_rating = true;
                                    require __DIR__ . '/partials/program_row.php';
                                endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Programs with Finalized Submissions Card -->
        <div class="card shadow-sm mb-4 w-100 finalized-programs-card">
            <div class="card-header d-flex justify-content-between align-items-center bg-light border-start border-success border-4">
                <h5 class="card-title view-programs-card-title m-0 d-flex align-items-center">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Programs with Finalized Submissions
                    <span class="badge bg-success ms-2" title="These programs have finalized submissions">
                        <i class="fas fa-check me-1"></i> Finalized
                    </span>
                    <span class="badge bg-secondary ms-2" id="finalized-count"><?php echo count($programs_with_submissions); ?></span>
                </h5>
            </div>
            
            <!-- Finalized Programs Filters -->
            <?php 
            $filters = ['rating'];
            $filterPrefix = 'finalized';
            require_once __DIR__ . '/partials/program_filters.php'; 
            ?>
            
            <div class="card-body pt-2 p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0" id="finalizedProgramsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable" data-sort="name">
                                    <i class="fas fa-project-diagram me-1"></i>Program Information 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="sortable initiative-display" data-sort="initiative">
                                    <i class="fas fa-lightbulb me-1"></i>Initiative 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="sortable" data-sort="rating">
                                    <i class="fas fa-chart-line me-1"></i>Progress Rating 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="sortable" data-sort="date">
                                    <i class="fas fa-clock me-1"></i>Last Updated 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-cog me-1"></i>Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($programs_with_submissions)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">No programs with finalized submissions found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($programs_with_submissions as $program): 
                                    $show_rating = true;
                                    require __DIR__ . '/partials/program_row.php';
                                endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Programs Without Submissions Card -->
        <div class="card shadow-sm mb-4 w-100 empty-programs-card">
            <div class="card-header d-flex justify-content-between align-items-center bg-light border-start border-info border-4">
                <h5 class="card-title view-programs-card-title m-0 d-flex align-items-center text-white">
                    <i class="fas fa-folder-open text-white me-2" style="color: #fff !important;"></i>
                    Program Templates
                    <span class="badge bg-info ms-2" title="These programs are templates waiting for progress reports">
                        <i class="fas fa-file-alt me-1 text-white"></i> Ready for Reports
                    </span>
                    <span class="badge bg-secondary ms-2" id="empty-count"><?php echo count($programs_without_submissions); ?></span>
                </h5>
            </div>
            
            <!-- Empty Programs Filters -->
            <?php 
            $filters = []; // No rating filter for templates
            $filterPrefix = 'empty';
            require_once __DIR__ . '/partials/program_filters.php'; 
            ?>
            
            <div class="card-body pt-2 p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom mb-0" id="emptyProgramsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable" data-sort="name">
                                    <i class="fas fa-project-diagram me-1"></i>Program Information 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="sortable initiative-display" data-sort="initiative">
                                    <i class="fas fa-lightbulb me-1"></i>Initiative 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="sortable" data-sort="date">
                                    <i class="fas fa-clock me-1"></i>Created Date 
                                    <i class="fas fa-sort ms-1"></i>
                                </th>
                                <th class="text-end">
                                    <i class="fas fa-cog me-1"></i>Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($programs_without_submissions)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">No program templates found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($programs_without_submissions as $program): 
                                    $show_rating = false;
                                    require __DIR__ . '/partials/program_row.php';
                                endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <?php require_once __DIR__ . '/partials/delete_modal.php'; ?>

    </div>
</div>

<!-- JavaScript data and initialization -->
<script>
    // Make program data available to JavaScript
    window.allPrograms = <?php echo json_encode($programs); ?>;
    window.currentUserRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
    window.currentUserId = '<?php echo $_SESSION['user_id'] ?? ''; ?>';
</script>
