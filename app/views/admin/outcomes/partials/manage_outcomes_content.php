<div class="container-fluid px-4 py-4">
    <?php if (!empty($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Error:</strong> <?= htmlspecialchars($_SESSION['error_message']) ?>
    </div>
    <?php unset($_SESSION['error_message']); endif; ?>
    
    <?php if (!$allow_outcome_creation): ?>
    <!-- Outcome Creation Notice -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Important:</strong> 
        Creation of new outcomes has been disabled by the administrator. This ensures outcomes remain consistent across reporting periods.
        Outcome history is now tracked, and existing outcomes cannot be deleted to maintain data integrity.
        <a href="<?php echo APP_URL; ?>/app/views/admin/settings/system_settings.php" class="alert-link">
            <i class="fas fa-cog ms-1"></i> Manage settings
        </a>
    </div>
    <?php endif; ?>

    <!-- Outcomes Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-list-alt me-2"></i>Outcomes
            </h5>
            <span class="badge bg-light text-primary">
                <?= count($outcomes) ?> Items
            </span>
        </div>
        <div class="card-body">
            <?php if (!empty($outcomes)): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-hover border">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Last Updated</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($outcomes as $outcome): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($outcome['title']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($outcome['description']) ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y g:i A', strtotime($outcome['updated_at'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="view_outcome.php?id=<?= $outcome['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (isset($outcome['type']) && $outcome['type'] === 'kpi'): ?>
                                                <a href="edit_kpi.php?id=<?= $outcome['id'] ?>" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="Edit KPI Outcome">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="edit_outcome.php?id=<?= $outcome['id'] ?>" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="Edit Outcome">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No outcomes found.</p>
                </div>
            <?php endif; ?>
            <div id="errorContainer" class="alert alert-danger" style="display: none;"></div>
            <div id="successContainer" class="alert alert-success" style="display: none;"></div>
        </div>
    </div>

    <!-- Guidelines Section -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title m-0">
                <i class="fas fa-info-circle me-2"></i>Admin Guidelines for Outcomes Management
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-users me-2 text-primary"></i>Cross-Sector Management</h6>
                        <p class="small mb-1">As an admin, you can view and manage outcomes across all sectors and agencies.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">All outcomes from every sector are displayed here.</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-edit me-2 text-success"></i>Direct Editing</h6>
                        <p class="small mb-1">You can directly edit outcome data and structure for any agency.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Use this to help agencies correct or update their outcomes.</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-history me-2 text-info"></i>Change Tracking</h6>
                        <p class="small mb-1">View comprehensive history of changes made to any outcome.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Click the history icon to see all modifications.</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="h-100 p-3 border rounded bg-light-subtle">
                        <h6><i class="fas fa-cog me-2 text-danger"></i>System Settings</h6>
                        <p class="small mb-1">Control system-wide outcome creation permissions and other settings.</p>
                        <div class="alert alert-light py-2 px-3 mb-0 small">Manage global outcome policies from system settings.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Outcome Detail Modal -->
<div class="modal fade" id="editOutcomeDetailModal" tabindex="-1" aria-labelledby="editOutcomeDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editOutcomeDetailModalLabel">Edit Outcome Detail</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editOutcomeDetailForm">
          <div id="editItemsContainer"></div>
          <!-- Removed Add Item button as it is unused -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveOutcomeDetailBtn">Save Changes</button>
      </div>
    </div>
  </div>
</div>
