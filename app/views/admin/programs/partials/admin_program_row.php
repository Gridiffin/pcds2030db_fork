<?php
/**
 * Admin Program Row Partial
 * Renders a single program as a horizontal box/card for admin view
 * Based on agency program_row.php but adapted for admin perspective
 */

// Extract program data
$program = $program ?? [];
$show_rating = $show_rating ?? true;
$show_agency = $show_agency ?? true;
$is_draft = false; // Admin only sees finalized submissions

// Include rating helpers for status mapping
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';

// Use rating directly from database
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

// Admin sees finalized programs only
$status_class = 'status-finalized';
?>

<div class="program-box admin-program" 
     data-status="<?php echo $current_rating; ?>" 
     data-status-order="<?php echo $rating_order[$current_rating] ?? 999; ?>"
     data-initiative="<?php echo !empty($program['initiative_name']) ? htmlspecialchars($program['initiative_name']) : 'zzz_no_initiative'; ?>"
     data-initiative-id="<?php echo $program['initiative_id'] ?? '0'; ?>"
     data-agency-id="<?php echo $program['agency_id'] ?? '0'; ?>"
     data-agency-name="<?php echo !empty($program['agency_name']) ? htmlspecialchars($program['agency_name']) : ''; ?>">
    
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
                        View Program Details
                    </a>

                    <!-- View Submissions -->
                    <a href="view_submissions.php?program_id=<?php echo $program['program_id']; ?>" 
                       class="dropdown-item-custom"
                       title="View all submission details for this program">
                        <i class="fas fa-file-alt"></i>
                        View Submissions
                    </a>

                    <!-- Edit Program (Admin can edit all programs) -->
                    <a href="edit_program.php?id=<?php echo $program['program_id']; ?>" 
                       class="dropdown-item-custom"
                       title="Modify program details, targets, and basic information">
                        <i class="fas fa-cog"></i>
                        Edit Program
                    </a>

                    <!-- Edit This Submission -->
                    <a href="edit_submission.php?program_id=<?php echo $program['program_id']; ?>&period_id=<?php echo $program['period_id']; ?>" 
                       class="dropdown-item-custom"
                       title="Edit this submission">
                        <i class="fas fa-edit"></i>
                        Edit This Submission
                    </a>

                    <!-- Edit Any Submission -->
                    <a href="edit_submission.php?program_id=<?php echo $program['program_id']; ?>" 
                       class="dropdown-item-custom"
                       title="Select reporting period to edit">
                        <i class="fas fa-calendar-alt"></i>
                        Edit Any Period
                    </a>

                    <!-- Generate Reports -->
                    <a href="../reports/generate_reports.php?program_id=<?php echo $program['program_id']; ?>" 
                       class="dropdown-item-custom"
                       title="Generate reports for this program">
                        <i class="fas fa-chart-bar"></i>
                        Generate Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Program Meta Row -->
        <div class="program-meta-row">
            <!-- Agency Information (Admin-specific) -->
            <?php if ($show_agency): ?>
            <div class="agency-info">
                <div class="agency-badge" title="Agency: <?php echo htmlspecialchars($program['agency_name']); ?>">
                    <i class="fas fa-building me-1"></i>
                    <?php if (!empty($program['agency_acronym'])): ?>
                        <?php echo htmlspecialchars($program['agency_acronym']); ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars($program['agency_name']); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

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
                <div class="status-circle <?php echo $rating_map[$current_rating]['circle_class']; ?>"></div>
                <span class="status-text"><?php echo $rating_map[$current_rating]['label']; ?></span>
            </div>
            <?php endif; ?>

            <!-- Finalization Info -->
            <div class="finalization-info">
                <?php if (!empty($program['finalized_by_name']) && !empty($program['finalized_at'])): ?>
                    <i class="fas fa-user-check text-success"></i>
                    <span title="Finalized by <?php echo htmlspecialchars($program['finalized_by_name']); ?> on <?php echo date('M j, Y g:i A', strtotime($program['finalized_at'])); ?>">
                        <?php echo htmlspecialchars($program['finalized_by_name']); ?>
                        <small class="text-muted">(<?php echo date('M j, Y', strtotime($program['finalized_at'])); ?>)</small>
                    </span>
                <?php else: ?>
                    <i class="fas fa-question-circle text-muted"></i>
                    <span class="text-muted">Finalization info unavailable</span>
                <?php endif; ?>
            </div>

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
        </div>
    </div>
</div>