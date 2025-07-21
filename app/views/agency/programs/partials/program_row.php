<?php
/**
 * Program Row Partial
 * Renders a single program row for tables
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
        'icon' => 'fas fa-hourglass-start'
    ],
    'on_track_for_year' => [
        'label' => 'On Track for Year', 
        'class' => 'warning',
        'icon' => 'fas fa-calendar-check'
    ],
    'monthly_target_achieved' => [
        'label' => 'Monthly Target Achieved', 
        'class' => 'success',
        'icon' => 'fas fa-check-circle'
    ],
    'severe_delay' => [
        'label' => 'Severe Delays', 
        'class' => 'danger',
        'icon' => 'fas fa-exclamation-triangle'
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
?>

<tr class="<?php echo $is_draft ? 'draft-program' : ''; ?>" 
    data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>">
    
    <!-- Program Information -->
    <td class="text-truncate program-name-col">
        <div class="fw-medium">
            <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                <?php if (!empty($program['program_number'])): ?>
                    <span class="badge bg-info me-2" title="Program Number">
                        <?php echo htmlspecialchars($program['program_number']); ?>
                    </span>
                <?php endif; ?>
                <?php echo htmlspecialchars($program['program_name']); ?>
            </span>
            <?php if ($is_draft): ?>
                <span class="draft-indicator" title="Draft"></span>
            <?php endif; ?>
        </div>
        <div class="small text-muted program-type-indicator">
            <i class="fas fa-<?php echo $is_assigned ? 'tasks' : 'folder-plus'; ?> me-1"></i>
            <?php echo $is_assigned ? 'Assigned' : 'Agency-Created'; ?>
        </div>
    </td>

    <!-- Initiative -->
    <td class="text-truncate initiative-col initiative-display" 
        data-initiative="<?php echo !empty($program['initiative_name']) ? htmlspecialchars($program['initiative_name']) : 'zzz_no_initiative'; ?>"
        data-initiative-id="<?php echo $program['initiative_id'] ?? '0'; ?>">
        <?php if (!empty($program['initiative_name'])): ?>
            <span class="badge bg-primary initiative-badge" title="Initiative">
                <i class="fas fa-lightbulb me-1"></i>
                <span class="initiative-badge-card" title="<?php 
                    echo !empty($program['initiative_number']) ? 
                        htmlspecialchars($program['initiative_number'] . ' - ' . $program['initiative_name']) : 
                        htmlspecialchars($program['initiative_name']); 
                ?>">
                    <?php 
                    echo !empty($program['initiative_number']) ? 
                        htmlspecialchars($program['initiative_number'] . ' - ' . $program['initiative_name']) : 
                        htmlspecialchars($program['initiative_name']); 
                    ?>
                </span>
            </span>
        <?php else: ?>
            <span class="text-muted small">
                <i class="fas fa-minus me-1"></i>Not Linked
            </span>
        <?php endif; ?>
    </td>

    <!-- Rating (if shown) -->
    <?php if ($show_rating): ?>
    <td data-rating="<?php echo $current_rating; ?>" 
        data-rating-order="<?php echo $rating_order[$current_rating] ?? 999; ?>">
        <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?> rating-badge" 
              title="<?php echo $rating_map[$current_rating]['label']; ?>">
            <i class="<?php echo $rating_map[$current_rating]['icon']; ?> me-1"></i>
            <?php echo $rating_map[$current_rating]['label']; ?>
        </span>
    </td>
    <?php endif; ?>

    <!-- Last Updated -->
    <td>
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
        <span <?php if ($date_iso) echo 'data-date="' . $date_iso . '"'; ?>>
            <?php echo $date_display; ?>
        </span>
    </td>

    <!-- Actions -->
    <td>
        <div class="btn-group btn-group-sm d-flex flex-nowrap" role="group" aria-label="Program actions">
            <!-- View Button -->
            <a href="program_details.php?id=<?php echo $program['program_id']; ?>" 
               class="btn btn-outline-secondary flex-fill" 
               title="View detailed program information including submissions, targets, and progress"
               data-bs-toggle="tooltip" 
               data-bs-placement="top">
                <i class="fas fa-eye"></i>
            </a>

            <?php 
            // Check if user can edit this program
            $can_edit = can_edit_program($program['program_id']);
            $can_delete = is_focal_user() || is_program_creator($program['program_id']);
            ?>

            <!-- Edit/More Actions Button -->
            <?php if ($can_edit): ?>
            <button type="button" class="btn btn-outline-secondary flex-fill more-actions-btn" 
                    data-program-id="<?php echo $program['program_id']; ?>"
                    data-program-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                    data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>"
                    title="Edit submission and program details"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <?php endif; ?>

            <!-- Delete Button -->
            <?php if ($can_delete): ?>
            <button type="button" class="btn btn-outline-danger flex-fill trigger-delete-modal" 
                    data-id="<?php echo $program['program_id']; ?>" 
                    data-name="<?php echo htmlspecialchars($program['program_name']); ?>" 
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top"
                    title="Delete this program and all its submissions">
                <i class="fas fa-trash"></i>
            </button>
            <?php endif; ?>
        </div>
    </td>
</tr>
