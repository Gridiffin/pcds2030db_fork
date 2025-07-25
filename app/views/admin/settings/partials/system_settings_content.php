<?php
// Include error alert partial if needed
if (!empty($controller->message)): ?>
    <?php include __DIR__ . '/../partials/error_alert.php'; ?>
<?php endif; ?>

<div class="container-fluid px-4 py-4">
    <!-- System Settings Card -->
    <div class="card admin-card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">General Settings</h5>
        </div>
        <div class="card-body">
            <form method="post">
                <!-- Outcome Creation Section -->
                <div class="mb-4">
                    <h6 class="mb-1">Outcome Creation</h6>
                    <p class="text-muted mb-2">
                        Allow or disallow the creation of new outcomes. When disabled, agencies and admins can only use existing 
                        outcome templates, ensuring consistent reporting structure across periods.
                    </p>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Important:</strong> Based on client requirements, outcomes are now managed with the following restrictions:
                        <ul class="mb-0 mt-2">
                            <li>Creation of new outcomes can be locked behind this admin toggle</li>
                            <li>Deletion of outcomes is no longer allowed to maintain historical consistency</li>
                            <li>Outcome history is tracked to support the workflow where agencies submit outcomes, 
                                admins generate reports, then unsubmit for the next period</li>
                        </ul>
                    </div>
                    <div class="form-check form-switch forest-switch mt-3">
                        <!-- Hidden field to ensure the form value is always sent -->
                        <input type="hidden" name="allow_outcome_creation" value="0">
                        <input type="checkbox" class="form-check-input" id="outcomeCreationToggle" name="allow_outcome_creation" value="1"
                            <?php echo $controller->allow_outcome_creation_enabled ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="outcomeCreationToggle">
                            <?php echo $controller->allow_outcome_creation_enabled ? 'Enabled - Outcome Creation Allowed' : 'Disabled - Outcome Creation Restricted'; ?>
                        </label>
                    </div>
                </div>

                <!-- Current Status Section -->
                <div class="mb-4">
                    <div class="system-status-card">
                        <div class="card-body">
                            <h6 class="card-title">Current Status</h6>
                            <span class="status-indicator <?php echo $controller->allow_outcome_creation_enabled ? 'status-success' : 'status-info'; ?> mb-2">
                                <?php echo $controller->allow_outcome_creation_enabled ? 'Enabled' : 'Disabled'; ?>
                            </span>
                            <p class="small text-secondary mb-0">
                                <?php echo $controller->allow_outcome_creation_enabled ? 'Outcome creation is allowed.' : 'Outcome creation is restricted.'; ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-forest alert-info">
                    <i class="fas fa-info-circle alert-icon"></i>
                    <strong>Note:</strong> Changing this setting will affect the entire system. All users may need to refresh their browsers to see the changes.
                </div>
                
                <button type="submit" class="btn btn-forest">
                    <i class="fas fa-save me-1"></i> Save Settings
                </button>
            </form>
        </div>
    </div>
    
    <!-- Other System Information Card -->
    <div class="card admin-card">
        <div class="card-header">
            <h5 class="card-title m-0">System Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <h6 class="text-forest mb-2">Application Version</h6>
                        <p class="h5 mb-0"><?php echo APP_VERSION; ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <h6 class="text-forest mb-2">PHP Version</h6>
                        <p class="h5 mb-0"><?php echo phpversion(); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <h6 class="text-forest mb-2">MySQL Version</h6>
                        <p class="h5 mb-0"><?php echo $conn->server_info; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-forest alert-info mt-3">
                <h6 class="text-forest"><i class="fas fa-flag me-2"></i>Implementation Scope</h6>
                <p class="mb-0">
                    <?php if ($controller->allow_outcome_creation_enabled): ?>
                        Outcome creation is allowed. Agencies and admins can create new outcomes.
                    <?php else: ?>
                        Outcome creation is restricted. Agencies and admins can only use existing outcome templates.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>
