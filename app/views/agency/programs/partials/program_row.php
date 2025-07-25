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

// Use rating directly from database (no conversion needed)
$current_rating = isset($program['rating']) ? $program['rating'] : 'not_started';

// Map database rating values to display labels, classes, and icons
$rating_map = [
    'not_started' => [
        'label' => 'Not Started', 
        'class' => 'secondary',
        'icon' => 'fas fa-hourglass-start',
        'circle_class' => 'status-inactive'
    ],
    'on_track_for_year' => [
        'label' => 'On Track for Year', 
        'class' => 'warning',
        'icon' => 'fas fa-calendar-check',
        'circle_class' => 'status-pending'
    ],
    'monthly_target_achieved' => [
        'label' => 'Monthly Target Achieved', 
        'class' => 'success',
        'icon' => 'fas fa-check-circle',
        'circle_class' => 'status-completed'
    ],
    'severe_delay' => [
        'label' => 'Severe Delays', 
        'class' => 'danger',
        'icon' => 'fas fa-exclamation-triangle',
        'circle_class' => 'status-pending'
    ]
];

// Set default if rating is not in our map
if (!isset($rating_map[$current_rating])) {
    $current_rating = 'not_started';
}

$rating_order = [
    'monthly_target_achieved' => 1,
    'on_track_for_year' => 2,
    'severe_delay' => 3,
    'not_started' => 4
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
     data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>"
     data-rating="<?php echo $current_rating; ?>" 
     data-rating-order="<?php echo $rating_order[$current_rating] ?? 999; ?>"
     data-initiative="<?php echo !empty($program['initiative_name']) ? htmlspecialchars($program['initiative_name']) : 'zzz_no_initiative'; ?>"
     data-initiative-id="<?php echo $program['initiative_id'] ?? '0'; ?>">
    
    <!-- Status Indicator -->
    <div class="status-indicator <?php echo $status_class; ?>"></div>
    
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
                    <a href="program_details.php?id=<?php echo $program['program_id']; ?>" 
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

                    <?php if ($can_edit): ?>
                        <!-- Edit Program (Available for all states) -->
                        <a href="edit_program.php?id=<?php echo $program['program_id']; ?>" 
                           class="dropdown-item-custom"
                           title="Modify program details, targets, and basic information">
                            <i class="fas fa-cog"></i>
                            Edit Program
                        </a>

                        <?php if ($program_state === 'template'): ?>
                            <!-- Add Submission (Template state only) -->
                            <a href="add_submission.php?program_id=<?php echo $program['program_id']; ?>" 
                               class="dropdown-item-custom"
                               title="Add a new submission for this program">
                                <i class="fas fa-plus"></i>
                                Add Submission
                            </a>
                        <?php else: ?>
                            <!-- Edit Submission (Draft and Finalized states) -->
                            <a href="edit_submission.php?program_id=<?php echo $program['program_id']; ?>" 
                               class="dropdown-item-custom"
                               title="Edit the latest submission for this program">
                                <i class="fas fa-edit"></i>
                                Edit Submission
                            </a>
                        <?php endif; ?>
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

            <!-- Status/Rating -->
            <?php if ($show_rating): ?>
            <div class="status-info">
                <div class="status-circle <?php echo $rating_map[$current_rating]['circle_class']; ?>"></div>
                <span class="status-text"><?php echo $rating_map[$current_rating]['label']; ?></span>
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
