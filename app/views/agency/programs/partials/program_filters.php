<?php
/**
 * Program Filters Partial
 * Reusable filter section for program tables
 */

// Extract filter configuration
$filters = $filters ?? [];
$tableId = $tableId ?? '';
$filterPrefix = $filterPrefix ?? '';
?>

<div class="card-body pb-0">
    <div class="row g-3">
        <!-- Search Filter -->
        <div class="col-md-4 col-sm-12">
            <label for="<?php echo $filterPrefix; ?>ProgramSearch" class="form-label">Search</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="<?php echo $filterPrefix; ?>ProgramSearch" 
                       placeholder="Search by program name or number">
            </div>
        </div>

        <!-- Status Filter (if applicable) -->
        <?php if (in_array('status', $filters)): ?>
        <div class="col-md-2 col-sm-6">
            <label for="<?php echo $filterPrefix; ?>StatusFilter" class="form-label">Status</label>
            <select class="form-select" id="<?php echo $filterPrefix; ?>StatusFilter">
                <option value="">All Status</option>
                <option value="monthly_target_achieved">Monthly Target Achieved</option>
                <option value="on_track_for_year">On Track for Year</option>
                <option value="severe_delay">Severe Delays</option>
                <option value="not_started">Not Started</option>
            </select>
        </div>
        <?php endif; ?>

        <!-- Initiative Filter -->
        <div class="col-md-3 col-sm-6">
            <label for="<?php echo $filterPrefix; ?>InitiativeFilter" class="form-label">Initiative</label>
            <select class="form-select" id="<?php echo $filterPrefix; ?>InitiativeFilter">
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
            <button id="reset<?php echo ucfirst($filterPrefix); ?>Filters" class="btn btn-outline-secondary w-100">
                <i class="fas fa-undo me-1"></i> Reset
            </button>
        </div>
    </div>
    
    <!-- Filter Badges -->
    <div id="<?php echo $filterPrefix; ?>FilterBadges" class="filter-badges mt-2"></div>
</div>
