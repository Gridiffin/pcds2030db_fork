<?php
// Ensure the necessary variables are defined to prevent errors when the partial is loaded without the controller.
?>
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
    <div class="card-body pb-0">
        <div class="row g-3">
            <div class="col-md-3 col-sm-12">
                <label for="draftProgramSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="draftProgramSearch" placeholder="Search by program name or number">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="draftRatingFilter" class="form-label">Rating</label>
                <select class="form-select" id="draftRatingFilter">
                    <option value="">All Ratings</option>
                    <option value="target-achieved">Monthly Target Achieved</option>
                    <option value="on-track-yearly">On Track for Year</option>
                    <option value="severe-delay">Severe Delays</option>
                    <option value="not-started">Not Started</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="draftTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="draftTypeFilter">
                    <option value="">All Types</option>
                    <option value="assigned">Assigned</option>
                    <option value="created">Agency-Created Programs</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="draftAgencyFilter" class="form-label">Agency</label>
                <select class="form-select" id="draftAgencyFilter">
                    <option value="">All Agencies</option>
                    <?php foreach ($agencies as $agency): ?>
                        <option value="<?php echo $agency['agency_id']; ?>">
                            <?php echo htmlspecialchars($agency['agency_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="draftInitiativeFilter" class="form-label">Initiative</label>
                <select class="form-select" id="draftInitiativeFilter">
                    <option value="">All Initiatives</option>
                    <option value="no-initiative">Not Linked to Initiative</option>
                    <?php foreach ($active_initiatives as $initiative): ?>
                        <option value="<?php echo $initiative['initiative_id']; ?>">
                            <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 col-sm-12 d-flex align-items-end">
                <button id="resetDraftFilters" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
        <div id="draftFilterBadges" class="filter-badges mt-2"></div>
    </div>
    
    <div class="card-body pt-2 p-0">
        <div class="table-responsive">            <table class="table table-hover table-custom mb-0" id="draftProgramsTable">
                <thead class="table-light">
                    <tr>
                        <th class="sortable" data-sort="name">
                            <i class="fas fa-project-diagram me-1"></i>Program Information 
                            <i class="fas fa-sort ms-1"></i>
                        </th>
                        <th class="sortable" data-sort="agency">
                            <i class="fas fa-building me-1"></i>Agency 
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
                            <td colspan="6" class="text-center py-4">No programs with draft submissions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php
                        foreach ($programs_with_drafts as $program): 
                            // Determine program type (assigned or custom)
                            $is_assigned = isset($program['is_assigned']) && $program['is_assigned'] ? true : false;
                            
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
                            
                            // Check if this is a draft
                            $is_draft = isset($program['is_draft']) && $program['is_draft'] ? true : false;
                        ?>
                            <tr class="<?php echo $is_draft ? 'draft-program' : ''; ?>"
                                data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>"
                                data-agency-id="<?php echo $program['agency_id'] ?? ''; ?>">
                                <!-- Draft programs program info column -->
                                <td class="text-truncate program-name-col">
                                    <div class="fw-medium">
                                        <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                            <?php if (!empty($program['program_number'])): ?>
                                                <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
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
                                <!-- Agency column -->
                                <td class="text-truncate agency-col" data-agency="<?php echo htmlspecialchars($program['agency_name'] ?? ''); ?>">
                                    <span class="badge bg-primary agency-badge" title="Agency">
                                        <i class="fas fa-building me-1"></i>
                                        <?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown'); ?>
                                    </span>
                                </td>
                                <!-- Initiative column -->
                                <td class="text-truncate initiative-col" 
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
                                <!-- Rating column -->
                                <td data-rating="<?php echo $current_rating; ?>" data-rating-order="<?php 
                                    $rating_order = [
                                        'monthly_target_achieved' => 1,
                                        'on_track_for_year' => 2,
                                        'severe_delay' => 3,
                                        'not_started' => 4
                                    ];
                                    echo $rating_order[$current_rating] ?? 999;
                                ?>">
                                    <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?> rating-badge" 
                                          title="<?php echo $rating_map[$current_rating]['label']; ?>">
                                        <i class="<?php echo $rating_map[$current_rating]['icon']; ?> me-1"></i>
                                        <?php echo $rating_map[$current_rating]['label']; ?>
                                    </span>
                                </td>
                                <!-- Date column -->
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
                                    <span <?php if ($date_iso) echo 'data-date="' . $date_iso . '"'; ?>><?php echo $date_display; ?></span>
                                </td>
                                <!-- Actions column -->
                                <td>
                                    <div class="btn-group btn-group-sm d-flex flex-nowrap" role="group" aria-label="Program actions">
                                        <a href="view_program.php?id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-outline-secondary flex-fill" 
                                           title="View detailed program information including submissions, targets, and progress"
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary flex-fill more-actions-btn" 
                                                data-program-id="<?php echo $program['program_id']; ?>"
                                                data-program-name="<?php echo htmlspecialchars($program['program_name']); ?>"
                                                data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>"
                                                title="Edit submission and program details"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger flex-fill delete-program-btn" 
                                                data-id="<?php echo $program['program_id']; ?>" 
                                                data-name="<?php echo htmlspecialchars($program['program_name']); ?>" 
                                                title="Permanently delete this program and all its data"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 