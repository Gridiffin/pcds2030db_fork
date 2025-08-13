<?php
?>
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
    
    <div class="card-body pb-0">
        <div class="row g-3">
            <div class="col-md-3 col-sm-12">
                <label for="emptyProgramSearch" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="emptyProgramSearch" placeholder="Search by program name or number">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="emptyTypeFilter" class="form-label">Program Type</label>
                <select class="form-select" id="emptyTypeFilter">
                    <option value="">All Types</option>
                    <option value="assigned">Assigned</option>
                    <option value="created">Agency-Created Programs</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="emptyAgencyFilter" class="form-label">Agency</label>
                <select class="form-select" id="emptyAgencyFilter">
                    <option value="">All Agencies</option>
                    <?php foreach ($agencies as $agency): ?>
                        <option value="<?php echo $agency['agency_id']; ?>">
                            <?php echo htmlspecialchars($agency['agency_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="emptyInitiativeFilter" class="form-label">Initiative</label>
                <select class="form-select" id="emptyInitiativeFilter">
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
                <button id="resetEmptyFilters" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-undo me-1"></i> Reset
                </button>
            </div>
        </div>
        <div id="emptyFilterBadges" class="filter-badges mt-2"></div>
    </div>
    
    <div class="card-body pt-2 p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom mb-0" id="emptyProgramsTable">
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
                            <td colspan="5" class="text-center py-4">No program templates found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($programs_without_submissions as $program): 
                            // Determine program type (assigned or custom)
                            $is_assigned = isset($program['is_assigned']) && $program['is_assigned'] ? true : false;
                        ?>
                            <tr data-program-type="<?php echo $is_assigned ? 'assigned' : 'created'; ?>" data-agency-id="<?php echo $program['agency_id']; ?>">
                                <!-- Program Information -->
                                <td class="text-truncate program-name-col">
                                    <div class="fw-medium">
                                        <span class="program-name" title="<?php echo htmlspecialchars($program['program_name']); ?>">
                                            <?php if (!empty($program['program_number'])): ?>
                                                <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                        </span>
                                        <span class="badge bg-info ms-2" title="Program template - ready for progress reports">
                                            <i class="fas fa-file-alt me-1"></i> Template
                                        </span>
                                    </div>
                                    <div class="small text-muted program-type-indicator">
                                        <i class="fas fa-<?php echo $is_assigned ? 'tasks' : 'folder-plus'; ?> me-1"></i>
                                        <?php echo $is_assigned ? 'Assigned' : 'Agency-Created'; ?>
                                    </div>
                                </td>
                                <!-- Agency -->
                                <td>
                                    <span class="badge bg-info agency-badge" title="Agency">
                                        <i class="fas fa-building me-1"></i>
                                        <?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown'); ?>
                                    </span>
                                </td>
                                <!-- Initiative -->
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
                                <!-- Created Date -->
                                <td>
                                    <?php 
                                    $date_iso = '';
                                    if (isset($program['created_at']) && $program['created_at']) {
                                        $date_iso = date('Y-m-d', strtotime($program['created_at']));
                                        $date_display = date('M j, Y g:i A', strtotime($program['created_at']));
                                    } else {
                                        $date_display = 'Not set';
                                    }
                                    ?>
                                    <span <?php if ($date_iso) echo 'data-date="' . $date_iso . '"'; ?>><?php echo $date_display; ?></span>
                                </td>
                                <!-- Admin Actions -->
                                <td>
                                    <div class="btn-group btn-group-sm d-flex flex-nowrap" role="group" aria-label="Admin actions">
                                        <a href="program_details.php?id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-outline-secondary flex-fill" 
                                           title="View detailed program information"
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