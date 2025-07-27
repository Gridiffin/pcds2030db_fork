<?php
/**
 * Admin Program Box Partial
 * Renders a single program as a horizontal box/card for admin view
 * Based on agency programs/partials/program_row.php
 */

// Extract program data
$program = $program ?? [];
$show_rating = $show_rating ?? true;

// Determine program type (assigned or custom)
$is_assigned = isset($program['is_assigned']) && $program['is_assigned'] ? true : false;

// Use rating directly from database (no conversion needed)
$current_rating = isset($program['rating']) ? $program['rating'] : 'not_started';

// Map database rating values to display labels, classes, and icons
$rating_map = [
    'not_started' => [
        'label' => 'Not Started', 
        'class' => 'secondary',
        'icon' => 'fas fa-hourglass-start',
        'circle_class' => 'admin-status-inactive'
    ],
    'on_track_for_year' => [
        'label' => 'On Track for Year', 
        'class' => 'warning',
        'icon' => 'fas fa-calendar-check',
        'circle_class' => 'admin-status-pending'
    ],
    'monthly_target_achieved' => [
        'label' => 'Monthly Target Achieved', 
        'class' => 'success',
        'icon' => 'fas fa-check-circle',
        'circle_class' => 'admin-status-completed'
    ],
    'severe_delay' => [
        'label' => 'Severe Delays', 
        'class' => 'danger',
        'icon' => 'fas fa-exclamation-triangle',
        'circle_class' => 'admin-status-pending'
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
$status_class = 'admin-status-finalized';
?>

<div class="admin-program-box" 
     data-status="<?php echo $current_rating; ?>" 
     data-status-order="<?php echo $rating_order[$current_rating] ?? 999; ?>"
     data-initiative="<?php echo !empty($program['initiative_name']) ? htmlspecialchars($program['initiative_name']) : 'zzz_no_initiative'; ?>"
     data-initiative-id="<?php echo $program['initiative_id'] ?? '0'; ?>"
     data-agency-id="<?php echo $program['agency_id'] ?? '0'; ?>"
     data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>">
    
    
    <div class="admin-program-box-content">
        <!-- Program Header -->
        <div class="admin-program-header">
            <div class="admin-program-title-section">
                <?php if (!empty($program['program_number'])): ?>
                    <div class="admin-program-number"><?php echo htmlspecialchars($program['program_number']); ?></div>
                <?php endif; ?>
                <div>
                    <a href="program_details.php?id=<?php echo $program['program_id']; ?>" 
                       class="admin-program-name" 
                       title="<?php echo htmlspecialchars($program['program_name']); ?>">
                        <?php echo htmlspecialchars($program['program_name']); ?>
                    </a>
                    <?php if (!empty($program['description'])): ?>
                        <div class="admin-program-description">
                            <?php echo htmlspecialchars($program['description']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Actions Section -->
            <div class="admin-action-info">
                <button class="admin-action-btn" onclick="toggleAdminDropdown(this)">
                    <i class="fas fa-cog"></i>
                    Actions
                    <i class="fas fa-chevron-down ms-1"></i>
                </button>
                <div class="admin-dropdown-menu-custom">
                    <!-- View Program (Always Available) -->
                    <a href="program_details.php?id=<?php echo $program['program_id']; ?>" 
                       class="admin-dropdown-item-custom"
                       title="View detailed program information including submissions, targets, and progress">
                        <i class="fas fa-eye"></i>
                        View Program Details
                    </a>

                    <!-- View Submission -->
                    <a href="view_submissions.php?program_id=<?php echo $program['program_id']; ?>" 
                       class="admin-dropdown-item-custom"
                       title="View submission details for different reporting periods">
                        <i class="fas fa-file-alt"></i>
                        View Submissions
                    </a>

                    <!-- Edit Program -->
                    <a href="edit_program.php?id=<?php echo $program['program_id']; ?>" 
                       class="admin-dropdown-item-custom"
                       title="Modify program details, targets, and basic information">
                        <i class="fas fa-cog"></i>
                        Edit Program
                    </a>

                    <!-- Edit Submission -->
                    <a href="edit_submission.php?program_id=<?php echo $program['program_id']; ?>" 
                       class="admin-dropdown-item-custom"
                       title="Edit the latest submission for this program">
                        <i class="fas fa-edit"></i>
                        Edit Submission
                    </a>


                </div>
            </div>
        </div>

        <!-- Program Meta Row -->
        <div class="admin-program-meta-row">
            <!-- Agency -->
            <div class="admin-agency-info">
                <div class="admin-agency-icon">
                    <?php 
                    // Get first 3 characters of agency name as icon
                    $agency_short = strtoupper(substr($program['agency_name'] ?? 'UNK', 0, 3));
                    echo $agency_short;
                    ?>
                </div>
                <span><?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown Agency'); ?></span>
            </div>

            <!-- Initiative -->
            <div class="admin-initiative-info">
                <?php if (!empty($program['initiative_name'])): ?>
                    <?php if (!empty($program['initiative_number'])): ?>
                        <div class="admin-initiative-icon">
                            <i class="fas fa-lightbulb"></i>
                            <?php echo htmlspecialchars($program['initiative_number']); ?>
                            <div class="admin-tooltip">
                                <?php echo htmlspecialchars($program['initiative_name']); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Fallback to full badge for initiatives without numbers -->
                        <span class="admin-initiative-badge" title="Initiative">
                            <i class="fas fa-lightbulb me-1"></i>
                            <?php echo htmlspecialchars($program['initiative_name']); ?>
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="admin-no-initiative">
                        <i class="fas fa-minus"></i>
                        Not Linked to Initiative
                    </div>
                <?php endif; ?>
            </div>

            <!-- Status -->
            <?php if ($show_rating): ?>
            <div class="admin-status-info">
                <div class="admin-status-circle <?php echo $rating_map[$current_rating]['circle_class']; ?>"></div>
                <span class="admin-status-text"><?php echo $rating_map[$current_rating]['label']; ?></span>
            </div>
            <?php endif; ?>


            <!-- Last Updated -->
            <div class="admin-date-info">
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

            <!-- Submitted By -->
            <?php if (!empty($program['submitted_by_name'])): ?>
            <div class="admin-users-info">
                <span class="admin-users-label">Submitted by:</span>
                <span><?php echo htmlspecialchars($program['submitted_by_name']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>