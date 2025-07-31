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

        <!-- Unified Filters Section -->
        <div class="content-card shadow-sm mb-3">
            <div class="card-body pb-0">
                <div class="row g-3">
                    <!-- Search Filter -->
                    <div class="col-md-4 col-sm-12">
                        <label for="globalProgramSearch" class="form-label">Search Programs</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="globalProgramSearch" 
                                   placeholder="Search by program name or number">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2 col-sm-6">
                        <label for="globalStatusFilter" class="form-label">Status</label>
                        <select class="form-select" id="globalStatusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                            <option value="delayed">Delayed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <!-- Initiative Filter -->
                    <div class="col-md-3 col-sm-6">
                        <label for="globalInitiativeFilter" class="form-label">Initiative</label>
                        <select class="form-select" id="globalInitiativeFilter">
                            <option value="">All Initiatives</option>
                            <option value="no-initiative">Not Linked to Initiative</option>
                            <?php foreach ($active_initiatives as $initiative): ?>
                                <option value="<?php echo $initiative['initiative_id']; ?>">
                                    <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Reset Button -->
                    <div class="col-md-1 col-sm-12 d-flex align-items-end">
                        <button id="resetGlobalFilters" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-undo me-1"></i> Reset
                        </button>
                    </div>
                </div>
                
                <!-- Filter Badges -->
                <div id="globalFilterBadges" class="filter-badges mt-2"></div>
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
                    <span class="simple-badge" id="draft-count"><?php echo count($programs_with_drafts); ?></span>
                </button>
                <button class="nav-link <?php echo $active_tab === 'finalized' ? 'active' : ''; ?>" id="finalized-tab" data-bs-toggle="tab" data-bs-target="#finalized-programs" type="button" role="tab" aria-controls="finalized-programs" aria-selected="<?php echo $active_tab === 'finalized' ? 'true' : 'false'; ?>">
                    Finalized Submissions
                    <span class="simple-badge" id="finalized-count"><?php echo count($programs_with_submissions); ?></span>
                </button>
                <button class="nav-link <?php echo $active_tab === 'templates' ? 'active' : ''; ?>" id="templates-tab" data-bs-toggle="tab" data-bs-target="#template-programs" type="button" role="tab" aria-controls="template-programs" aria-selected="<?php echo $active_tab === 'templates' ? 'true' : 'false'; ?>">
                    Program Templates
                    <span class="simple-badge" id="empty-count"><?php echo count($programs_without_submissions); ?></span>
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
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title m-0 d-flex align-items-center">
                                    <i class="fas fa-edit text-warning me-2"></i>
                                    Programs with Draft Submissions
                                    <span class="badge bg-warning text-dark ms-2" title="These programs have draft submissions that can be edited">
                                        <i class="fas fa-pencil-alt me-1"></i> Draft Submissions
                                    </span>
                                    <span class="badge bg-secondary ms-2" id="draft-count"><?php echo count($programs_with_drafts); ?></span>
                                </h5>
                                
                                <?php if (is_focal_user()): ?>
                                <div class="focal-actions">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="FinalizationTutorial.open()" title="Learn how to finalize submissions">
                                        <i class="fas fa-graduation-cap me-1"></i> How to Finalize
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
            
                        
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
                                        $from_finalized_table = true;
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
        
        <!-- Finalization Tutorial Modal (Focal Users Only) -->
        <?php require_once __DIR__ . '/partials/finalization_tutorial_modal.php'; ?>
        
        <!-- Quick Finalize Modal (Focal Users Only) -->
        <?php require_once __DIR__ . '/partials/quick_finalize_modal.php'; ?>
        
        <!-- Simple Finalize Modal (New Implementation) -->
        <?php require_once __DIR__ . '/partials/simple_finalize_modal.php'; ?>
        
        <!-- JavaScript data and initialization -->
        <script>
            // Make program data available to JavaScript
            window.allPrograms = <?php echo json_encode($programs); ?>;
            window.currentUserRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
            window.currentUserId = '<?php echo $_SESSION['user_id'] ?? ''; ?>';
        </script>
    </div>
</main>

<!-- Submission Selection Modal -->
<div class="modal fade" id="submissionSelectionModal" tabindex="-1" aria-labelledby="submissionSelectionModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submissionSelectionModalLabel">Select Submission to View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Choose which submission period you want to view:</p>
                <div id="submissionListContainer">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading submissions...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
// Get the correct base path from PHP
const basePath = '<?php echo APP_URL; ?>';

// Function to close dropdown and open submission modal
function closeDropdownAndOpenModal(programId) {
    // Close all open custom dropdowns
    const dropdowns = document.querySelectorAll('.dropdown-menu-custom.show');
    dropdowns.forEach(dropdown => {
        dropdown.classList.remove('show');
    });
    
    // Remove dropdown-active class from all program boxes
    const programBoxes = document.querySelectorAll('.program-box.dropdown-active');
    programBoxes.forEach(box => {
        box.classList.remove('dropdown-active');
    });
    
    // Small delay to ensure dropdown is closed before opening modal
    setTimeout(() => {
        openSubmissionModal(programId);
    }, 100);
}

// Function to close dropdown and navigate
function closeDropdownAndNavigate(url) {
    // Close all open custom dropdowns
    const dropdowns = document.querySelectorAll('.dropdown-menu-custom.show');
    dropdowns.forEach(dropdown => {
        dropdown.classList.remove('show');
    });
    
    // Remove dropdown-active class from all program boxes
    const programBoxes = document.querySelectorAll('.program-box.dropdown-active');
    programBoxes.forEach(box => {
        box.classList.remove('dropdown-active');
    });
    
    // Navigate to the URL
    window.location.href = url;
}

// Function to open submission modal and load submissions
function openSubmissionModal(programId) {
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('submissionSelectionModal'));
    modal.show();
    
    // Load submissions for this program
    loadSubmissionsForModal(programId);
}

// Function to load submissions via AJAX
async function loadSubmissionsForModal(programId) {
    const container = document.getElementById('submissionListContainer');
    
    try {
        // Use the base path from PHP
        const apiUrl = `${basePath}/app/ajax/get_program_submissions.php`;
        
        console.log('API URL:', apiUrl); // Debug log
        
        const response = await fetch(`${apiUrl}?program_id=${programId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Failed to load submissions');
        }
        
        const submissions = data.submissions || [];
        
        console.log('Submissions data:', submissions); // Debug log
        
        if (submissions.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted p-4">
                    <i class="fas fa-folder-open fa-2x mb-2"></i>
                    <p>No submissions found for this program.</p>
                    <small>Create your first submission to see it listed here.</small>
                </div>
            `;
            return;
        }
        
        // Render submissions list
        const html = submissions.map(submission => {
            console.log('Processing submission:', submission); // Debug log for each submission
            
            const statusClass = submission.is_draft ? 'warning' : 'success';
            const statusText = submission.is_draft ? 'Draft' : 'Finalized';
            const periodId = submission.period_id || submission.reporting_period_id;
            
            console.log('Status class:', statusClass, 'Status text:', statusText); // Debug log
            
            return `
                <div class="list-group-item list-group-item-action submission-option" 
                     onclick="navigateToSubmission(${programId}, ${periodId})"
                     data-submission-id="${submission.submission_id || ''}"
                     data-period-id="${periodId}"
                     style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                ${submission.period_display || `Period ${periodId}`}
                            </h6>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                ${submission.submitted_by_name || 'Unknown'}
                                <i class="fas fa-clock ms-2 me-1"></i>
                                ${submission.formatted_date || ''}
                            </small>
                        </div>
                        <div>
                            <span class="badge bg-${statusClass}">
                                ${statusText}
                            </span>
                            <i class="fas fa-chevron-right ms-2 text-muted"></i>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        console.log('Generated HTML:', html); // Debug log
        
        container.innerHTML = `
            <div class="list-group">
                ${html}
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Click on any period above to view that submission's details. 
                    All submission periods for this program are shown.
                </small>
            </div>
        `;
        
        // Force a small delay to ensure the DOM is updated
        setTimeout(() => {
            console.log('Modal content updated successfully');
        }, 100);
        
    } catch (error) {
        console.error('Error loading submissions:', error);
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error loading submissions: ${error.message}
            </div>
        `;
    }
}

// Function to navigate to submission
function navigateToSubmission(programId, periodId) {
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('submissionSelectionModal'));
    modal.hide();
    
    // Navigate to the submission using the base path from PHP
    const submissionUrl = `${basePath}/app/views/agency/programs/edit_submission.php?program_id=${programId}&period_id=${periodId}`;
    
    window.location.href = submissionUrl;
}
</script>
