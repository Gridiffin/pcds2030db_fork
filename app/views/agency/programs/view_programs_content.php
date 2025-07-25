<?php
/**
 * View Programs Content - Agency Programs List Content
 * This file contains the main content for the view programs page
 */
?>

<main>
    <div class="container-fluid">
        <!-- Toast Notification for Program Creation/Deletion -->
        <?php if (!empty($message)): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast('<?= ucfirst($messageType) ?>', <?= json_encode($message) ?>, '<?= $messageType ?>');
                });
            </script>
        <?php endif; ?>
        
        <!-- Show success message when redirected from program creation -->
        <?php if ($show_created_message): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast('Success', 'Program created successfully! Your new program is now available in the Templates section.', 'success');
                });
            </script>
        <?php endif; ?>

        <!-- Prominent Create Program Section -->
        <div class="create-program-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-2 text-dark">Ready to start a new program?</h4>
                    <p class="text-muted mb-0">Create a new program to begin tracking your initiatives.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="<?php echo APP_URL; ?>/app/views/agency/programs/create_program.php" class="btn btn-success btn-lg create-program-cta">
                        <i class="fas fa-plus-circle me-2"></i> Create New Program
                    </a>
                </div>
            </div>
        </div>

        <!-- Tab Navigation - Pill Design -->
        <div class="pill-tabs-container forest-theme mb-3">
            <nav class="nav-tabs-pill" id="programTabs" role="tablist">
                <?php
                // Determine which tab should be active based on URL parameter
                $active_tab = $_GET['tab'] ?? 'draft';
                ?>
                <button class="nav-link <?php echo $active_tab === 'draft' ? 'active' : ''; ?>" id="draft-tab" data-bs-toggle="tab" data-bs-target="#draft-programs" type="button" role="tab" aria-controls="draft-programs" aria-selected="<?php echo $active_tab === 'draft' ? 'true' : 'false'; ?>">
                    Draft Submissions
                    <span class="simple-badge"><?php echo count($programs_with_drafts); ?></span>
                </button>
                <button class="nav-link <?php echo $active_tab === 'finalized' ? 'active' : ''; ?>" id="finalized-tab" data-bs-toggle="tab" data-bs-target="#finalized-programs" type="button" role="tab" aria-controls="finalized-programs" aria-selected="<?php echo $active_tab === 'finalized' ? 'true' : 'false'; ?>">
                    Finalized Submissions
                    <span class="simple-badge"><?php echo count($programs_with_submissions); ?></span>
                </button>
                <button class="nav-link <?php echo $active_tab === 'templates' ? 'active' : ''; ?>" id="templates-tab" data-bs-toggle="tab" data-bs-target="#template-programs" type="button" role="tab" aria-controls="template-programs" aria-selected="<?php echo $active_tab === 'templates' ? 'true' : 'false'; ?>">
                    Program Templates
                    <span class="simple-badge"><?php echo count($programs_without_submissions); ?></span>
                </button>
            </nav>
        </div>
        
        <!-- Tab Content - Separate Card -->
        <div class="content-card shadow-sm">
            <div class="tab-content" id="programTabsContent">

                <!-- Draft Programs Tab Pane -->
                <div class="tab-pane fade <?php echo $active_tab === 'draft' ? 'show active' : ''; ?>" id="draft-programs" role="tabpanel" aria-labelledby="draft-tab">
                    <div class="card-body p-0">
                        <div class="p-4 border-bottom">
                            <h5 class="card-title m-0 d-flex align-items-center">
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
                        
                        <div class="p-4">
                            <div class="programs-container" id="draftProgramsContainer">
                                <?php if (empty($programs_with_drafts)): ?>
                                    <div class="programs-empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-folder-open"></i>
                                        </div>
                                        <div class="empty-title">No Draft Programs Found</div>
                                        <div class="empty-description">No programs with draft submissions found.</div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($programs_with_drafts as $program): 
                                        $show_rating = true;
                                        require __DIR__ . '/partials/program_row.php';
                                    endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Pagination for Draft Programs -->
                            <div class="mt-4" id="draftProgramsPagination"></div>
                            <div class="text-muted small mt-2" id="draftProgramsCounter"></div>
                        </div>
                    </div>
                </div>

                <!-- Finalized Programs Tab Pane -->
                <div class="tab-pane fade <?php echo $active_tab === 'finalized' ? 'show active' : ''; ?>" id="finalized-programs" role="tabpanel" aria-labelledby="finalized-tab">
                    <div class="card-body p-0">
                        <div class="p-4 border-bottom">
                            <h5 class="card-title m-0 d-flex align-items-center">
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
                        
                        <div class="p-4">
                            <div class="programs-container" id="finalizedProgramsContainer">
                                <?php if (empty($programs_with_submissions)): ?>
                                    <div class="programs-empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="empty-title">No Finalized Programs Found</div>
                                        <div class="empty-description">No programs with finalized submissions found.</div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($programs_with_submissions as $program): 
                                        $show_rating = true;
                                        require __DIR__ . '/partials/program_row.php';
                                    endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Pagination for Finalized Programs -->
                            <div class="mt-4" id="finalizedProgramsPagination"></div>
                            <div class="text-muted small mt-2" id="finalizedProgramsCounter"></div>
                        </div>
                    </div>
                </div>

                <!-- Template Programs Tab Pane -->
                <div class="tab-pane fade <?php echo $active_tab === 'templates' ? 'show active' : ''; ?>" id="template-programs" role="tabpanel" aria-labelledby="templates-tab">
                    <div class="card-body p-0">
                        <div class="p-4 border-bottom">
                            <h5 class="card-title m-0 d-flex align-items-center">
                                <i class="fas fa-folder-open text-info me-2"></i>
                                Program Templates
                                <span class="badge bg-info ms-2" title="These programs are templates waiting for progress reports">
                                    <i class="fas fa-file-alt me-1"></i> Ready for Reports
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
                        
                        <div class="p-4">
                            <div class="programs-container" id="emptyProgramsContainer">
                                <?php if (empty($programs_without_submissions)): ?>
                                    <div class="programs-empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div class="empty-title">No Program Templates Found</div>
                                        <div class="empty-description">No program templates found.</div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($programs_without_submissions as $program): 
                                        $show_rating = false;
                                        require __DIR__ . '/partials/program_row.php';
                                    endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Pagination for Empty Programs -->
                            <div class="mt-4" id="emptyProgramsPagination"></div>
                            <div class="text-muted small mt-2" id="emptyProgramsCounter"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <?php require_once __DIR__ . '/partials/delete_modal.php'; ?>

        <!-- JavaScript data and initialization -->
        <script>
            // Make program data available to JavaScript
            window.allPrograms = <?php echo json_encode($programs); ?>;
            window.currentUserRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
            window.currentUserId = '<?php echo $_SESSION['user_id'] ?? ''; ?>';
        </script>
    </div>
</main>
