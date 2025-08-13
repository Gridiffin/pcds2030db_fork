<?php
/**
 * Admin Program Filters Partial
 * Provides filtering functionality for admin program views
 */

// Get unique agencies for filtering
$agencies_query = "SELECT DISTINCT a.agency_id, a.agency_name 
                   FROM agency a 
                   INNER JOIN programs p ON a.agency_id = p.agency_id 
                   WHERE p.is_deleted = 0 
                   ORDER BY a.agency_name";
$agencies_result = $conn->query($agencies_query);
$agencies = [];
while ($row = $agencies_result->fetch_assoc()) {
    $agencies[] = $row;
}

// Get unique initiatives for filtering
$initiatives_query = "SELECT DISTINCT i.initiative_id, i.initiative_name, i.initiative_number 
                       FROM initiatives i 
                       INNER JOIN programs p ON i.initiative_id = p.initiative_id 
                       WHERE p.is_deleted = 0 
                       ORDER BY i.initiative_name";
$initiatives_result = $conn->query($initiatives_query);
$initiatives = [];
while ($row = $initiatives_result->fetch_assoc()) {
    $initiatives[] = $row;
}
?>

<div class="filters-section border-bottom">
    <div class="p-3">
        <div class="row g-3">
            <!-- Agency Filter -->
            <?php if (in_array('agency', $filters)): ?>
            <div class="col-md-3">
                <label for="<?php echo $filterPrefix; ?>-agency-filter" class="form-label small">Agency</label>
                <select class="form-select form-select-sm" id="<?php echo $filterPrefix; ?>-agency-filter" onchange="filterPrograms('<?php echo $filterPrefix; ?>')">
                    <option value="">All Agencies</option>
                    <?php foreach ($agencies as $agency): ?>
                        <option value="<?php echo $agency['agency_id']; ?>">
                            <?php echo htmlspecialchars($agency['agency_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Initiative Filter -->
            <?php if (in_array('initiative', $filters)): ?>
            <div class="col-md-3">
                <label for="<?php echo $filterPrefix; ?>-initiative-filter" class="form-label small">Initiative</label>
                <select class="form-select form-select-sm" id="<?php echo $filterPrefix; ?>-initiative-filter" onchange="filterPrograms('<?php echo $filterPrefix; ?>')">
                    <option value="">All Initiatives</option>
                    <?php foreach ($initiatives as $initiative): ?>
                        <option value="<?php echo $initiative['initiative_id']; ?>">
                            <?php if (!empty($initiative['initiative_number'])): ?>
                                <?php echo htmlspecialchars($initiative['initiative_number']); ?> - 
                            <?php endif; ?>
                            <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Status Filter -->
            <?php if (in_array('status', $filters)): ?>
            <div class="col-md-3">
                <label for="<?php echo $filterPrefix; ?>-status-filter" class="form-label small">Progress Status</label>
                <select class="form-select form-select-sm" id="<?php echo $filterPrefix; ?>-status-filter" onchange="filterPrograms('<?php echo $filterPrefix; ?>')">
                    <option value="">All Statuses</option>
                    <option value="monthly_target_achieved">Monthly Target Achieved</option>
                    <option value="on_track_for_year">On Track for Year</option>
                    <option value="severe_delay">Severe Delays</option>
                    <option value="not_started">Not Started</option>
                </select>
            </div>
            <?php endif; ?>

            <!-- Search -->
            <div class="col-md-3">
                <label for="<?php echo $filterPrefix; ?>-search" class="form-label small">Search Programs</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="<?php echo $filterPrefix; ?>-search" 
                           placeholder="Search by name..." onkeyup="filterPrograms('<?php echo $filterPrefix; ?>')">
                    <button class="btn btn-outline-secondary" type="button" onclick="clearSearch('<?php echo $filterPrefix; ?>')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Filter Summary -->
        <div class="row mt-2">
            <div class="col-12">
                <div class="filter-summary small text-muted d-flex align-items-center">
                    <span id="<?php echo $filterPrefix; ?>-filter-summary">Showing all programs</span>
                    <button type="button" class="btn btn-link btn-sm ms-2 p-0" onclick="clearAllFilters('<?php echo $filterPrefix; ?>')" style="display: none;" id="<?php echo $filterPrefix; ?>-clear-filters">
                        Clear all filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>