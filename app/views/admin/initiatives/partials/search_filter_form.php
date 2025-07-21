<form method="GET" action="" class="row g-3">
    <div class="col-md-6 col-sm-12">
        <label for="search" class="form-label">Search Initiatives</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="search" name="search" 
                   placeholder="Search by name, number, or description" 
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status" name="status">
            <option value="">All Status</option>
            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </div>
    <div class="col-md-3 col-sm-6 d-flex align-items-end">
        <button type="submit" class="btn btn-primary me-2">
            <i class="fas fa-search me-1"></i> Search
        </button>
        <a href="initiatives.php" class="btn btn-outline-secondary">
            <i class="fas fa-undo me-1"></i> Reset
        </a>
    </div>
</form> 