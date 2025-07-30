<?php
/**
 * Program Box Partial
 * Renders a single program as a horizontal box/card
 */

// Extract program data
$program = $program ?? [];
$show_rating = $show_rating ?? true;
$is_draft = isset($program['is_draft']) && $program['is_draft'] ? true : false;
$is_assigned = isset($program['is_assigned']) && $program['is_assigned'] ? true : false;

// Include rating helpers for status mapping
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';

// Use status directly from database (no conversion needed)
$current_status = isset($program['status']) ? $program['status'] : 'active';

// Map database status values to display labels, classes, and icons
$status_map = [
    'active' => [
        'label' => 'Active', 
        'class' => 'success',
        'icon' => 'fas fa-play-circle',
        'circle_class' => 'status-active'
    ],
    'on_hold' => [
        'label' => 'On Hold', 
        'class' => 'warning',
        'icon' => 'fas fa-pause-circle',
        'circle_class' => 'status-pending'
    ],
    'completed' => [
        'label' => 'Completed', 
        'class' => 'primary',
        'icon' => 'fas fa-check-circle',
        'circle_class' => 'status-completed'
    ],
    'delayed' => [
        'label' => 'Delayed', 
        'class' => 'danger',
        'icon' => 'fas fa-exclamation-triangle',
        'circle_class' => 'status-pending'
    ],
    'cancelled' => [
        'label' => 'Cancelled', 
        'class' => 'secondary',
        'icon' => 'fas fa-times-circle',
        'circle_class' => 'status-inactive'
    ]
];

// Set default if status is not in our map
if (!isset($status_map[$current_status])) {
    $current_status = 'active';
}

$status_order = [
    'completed' => 1,
    'active' => 2,
    'on_hold' => 3,
    'delayed' => 4,
    'cancelled' => 5
];

// Determine status indicator class
$status_class = 'status-template';
if ($is_draft) {
    $status_class = 'status-draft';
} elseif (isset($program['latest_submission_id']) && $program['latest_submission_id'] && !$is_draft) {
    $status_class = 'status-finalized';
}
?>

<div class="program-box <?php echo $is_draft ? 'draft-program' : ''; ?>" 
     data-status="<?php echo $current_status; ?>" 
     data-status-order="<?php echo $status_order[$current_status] ?? 999; ?>"
     data-initiative="<?php echo !empty($program['initiative_name']) ? htmlspecialchars($program['initiative_name']) : 'zzz_no_initiative'; ?>"
     data-initiative-id="<?php echo $program['initiative_id'] ?? '0'; ?>">
    
    <!-- Status indicator removed - using badges instead -->
    
    <div class="program-box-content">
        <!-- Program Header -->
        <div class="program-header">
            <div class="program-title-section">
                <?php if (!empty($program['program_number'])): ?>
                    <div class="program-number"><?php echo htmlspecialchars($program['program_number']); ?></div>
                <?php endif; ?>
                <div>
                    <a href="program_details.php?id=<?php echo $program['program_id']; ?>" 
                       class="program-name" 
                       title="<?php echo htmlspecialchars($program['program_name']); ?>">
                        <?php echo htmlspecialchars($program['program_name']); ?>
                    </a>
                    <?php if (!empty($program['description'])): ?>
                        <div class="program-description">
                            <?php echo htmlspecialchars($program['description']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Actions Section -->
            <div class="action-info">
                <button class="action-btn" onclick="toggleDropdown(this)">
                    <i class="fas fa-cog"></i>
                    Actions
                    <i class="fas fa-chevron-down ms-1"></i>
                </button>
                <div class="dropdown-menu-custom">
                    <!-- View Program (Always Available) -->
                    <a href="javascript:void(0);" 
                       onclick="closeDropdownAndNavigate('program_details.php?id=<?php echo $program['program_id']; ?>')"
                       class="dropdown-item-custom"
                       title="View detailed program information including submissions, targets, and progress">
                        <i class="fas fa-eye"></i>
                        View Program
                    </a>

                    <?php 
                    // Check if user can edit this program
                    $can_edit = can_edit_program($program['program_id']);
                    $can_delete = is_focal_user() || is_program_creator($program['program_id']);
                    
                    // Determine program state
                    $has_submission = isset($program['latest_submission_id']) && $program['latest_submission_id'];
                    $is_template = !$has_submission;
                    $program_state = $is_template ? 'template' : ($is_draft ? 'draft' : 'finalized');
                    ?>

                    <?php if ($has_submission): ?>
                        <!-- View Submission (Available when program has submissions) -->
                        <button type="button" 
                                class="dropdown-item-custom border-0 bg-transparent text-start w-100" 
                                onclick="closeDropdownAndOpenModal(<?php echo $program['program_id']; ?>)"
                                title="View submission details for different reporting periods">
                            <i class="fas fa-file-alt"></i>
                            View Submission
                        </button>
                    <?php endif; ?>

                    <?php
                    // Hide edit buttons for normal users if finalized
                    $is_focal = is_focal_user();
                    $from_finalized_table = $from_finalized_table ?? false;
                    // Hide edit/add buttons if rendering from finalized table
                    // For quick actions: hide edit program and add submission for finalized table
                    if ($can_edit && ($program_state !== 'finalized' || $is_focal) && !$from_finalized_table) :
                    ?>
                        <!-- Edit Program (Available for all states except finalized for normal users) -->
                        <?php if (!$from_finalized_table): ?>
                        <a href="javascript:void(0);" 
                           onclick="closeDropdownAndNavigate('edit_program.php?id=<?php echo $program['program_id']; ?>')"
                           class="dropdown-item-custom"
                           title="Modify program details, targets, and basic information">
                            <i class="fas fa-cog"></i>
                            Edit Program
                        </a>
                        <?php endif; ?>

                        <?php if ($program_state === 'template' && !$from_finalized_table): ?>
                            <!-- Add Submission (Template state only) -->
                            <a href="javascript:void(0);" 
                               onclick="closeDropdownAndNavigate('add_submission.php?program_id=<?php echo $program['program_id']; ?>')"
                               class="dropdown-item-custom"
                               title="Add a new submission for this program">
                                <i class="fas fa-plus"></i>
                                Add Submission
                            </a>
                        <?php elseif ($program_state !== 'template'): ?>
                            <!-- Edit Submission (Draft and Finalized states) -->
                            <?php if ($program_state !== 'finalized' || $is_focal): ?>
                            <a href="javascript:void(0);" 
                               onclick="closeDropdownAndNavigate('edit_submission.php?program_id=<?php echo $program['program_id']; ?>')"
                               class="dropdown-item-custom"
                               title="Edit the latest submission for this program">
                                <i class="fas fa-edit"></i>
                                Edit Submission
                            </a>
                            <?php endif; ?>

                            <?php if ($program_state === 'draft' && $is_focal): ?>
                                <!-- Review & Finalize Submission (Draft state only, focal users only) -->
                                <button type="button" 
                                        class="dropdown-item-custom border-0 bg-transparent text-start w-100" 
                                        onclick="closeDropdownAndOpenModal(<?php echo $program['program_id']; ?>)"
                                        title="Review full submission details and finalize">
                                    <i class="fas fa-check-circle text-success"></i>
                                    Review & Finalize
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php
                    // Add Unsubmit button for focal users on finalized submissions
                    if ($program_state === 'finalized' && $is_focal && isset($program['latest_submission_id'])): ?>
                        <button type="button"
                                class="dropdown-item-custom border-0 bg-transparent text-start w-100 text-danger"
                                onclick="unsubmitSubmission(<?php echo $program['latest_submission_id']; ?>, this)"
                                title="Return this finalized submission to draft status for further editing">
                            <i class="fas fa-undo"></i>
                            Unsubmit (Return to Draft)
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Program Meta Row -->
        <div class="program-meta-row">
            <!-- Initiative -->
            <div class="initiative-info">
                <?php if (!empty($program['initiative_name'])): ?>
                    <?php if (!empty($program['initiative_number'])): ?>
                        <div class="initiative-icon">
                            <i class="fas fa-lightbulb"></i>
                            <?php echo htmlspecialchars($program['initiative_number']); ?>
                            <div class="tooltip">
                                <?php echo htmlspecialchars($program['initiative_name']); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Fallback to full badge for initiatives without numbers -->
                        <span class="initiative-badge" title="Initiative">
                            <i class="fas fa-lightbulb me-1"></i>
                            <?php echo htmlspecialchars($program['initiative_name']); ?>
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-initiative">
                        <i class="fas fa-minus"></i>
                        Not Linked to Initiative
                    </div>
                <?php endif; ?>
            </div>

            <!-- Status -->
            <?php if ($show_rating): ?>
            <div class="status-info">
                <div class="status-circle <?php echo $status_map[$current_status]['circle_class']; ?>"></div>
                <span class="status-text"><?php echo $status_map[$current_status]['label']; ?></span>
            </div>
            <?php endif; ?>

            <!-- Last Updated -->
            <div class="date-info">
                <?php 
                $date_iso = '';
                if (isset($program['updated_at']) && $program['updated_at']) {
                    $date_iso = date('Y-m-d', strtotime($program['updated_at']));
                    $date_display = date('M j, Y g:i A', strtotime($program['updated_at']));
                } elseif (isset($program['created_at']) && $program['created_at']) {
                    $date_iso = date('Y-m-d', strtotime($program['created_at']));
                    $date_display = date('M j, Y g:i A', strtotime($program['created_at']));
                } else {
                    $date_display = 'Not set';
                }
                ?>
                <i class="fas fa-clock"></i>
                <span <?php if ($date_iso) echo 'data-date="' . $date_iso . '"'; ?>>
                    <?php echo $date_display; ?>
                </span>
            </div>

            <!-- Editors (placeholder for future implementation) -->
            <div class="editors-info">
                <span class="editors-label">Editors:</span>
                <span class="editors-all">All</span>
            </div>
        </div>

        <!-- Timeline (placeholder for future implementation) -->
        <?php if (false): // Placeholder - will be implemented later ?>
        <div class="timeline-info timeline-absolute">
            <i class="fas fa-calendar-alt"></i>
            <div class="timeline-dates">
                Jan 1, 2024
                <span class="timeline-separator">→</span>
                Dec 31, 2024
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
