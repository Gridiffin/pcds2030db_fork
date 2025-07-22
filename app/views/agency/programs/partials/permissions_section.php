<?php
/**
 * Permissions Section Partial
 * Contains the user permissions settings
 */
?>

<!-- User Permissions Section -->
<div class="card permissions-card">
    <div class="card-header">
        <h6 class="card-title mb-0">
            <i class="fas fa-users me-2"></i>
            User Permissions
        </h6>
    </div>
    <div class="card-body">
        <!-- Restrict Editors Toggle -->
        <div class="permissions-toggle">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" 
                       id="restrict_editors" name="restrict_editors"
                       <?php echo (isset($_POST['restrict_editors']) && $_POST['restrict_editors']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="restrict_editors">
                    <strong>Restrict editing to specific users</strong>
                </label>
            </div>
            <div class="form-text">
                <i class="fas fa-info-circle me-1"></i>
                By default, all agency users can edit. Enable this to limit editing to selected users only.
            </div>
        </div>

        <!-- User Selection (shown when restrictions are enabled) -->
        <div id="userSelectionSection" class="user-selection">
            <label class="user-selection-label">
                <i class="fas fa-user-edit me-1"></i>
                Select users who can edit this program:
            </label>
            
            <?php if (!empty($agency_users)): ?>
                <div class="user-checkboxes">
                    <?php foreach ($agency_users as $user): ?>
                        <div class="user-checkbox">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="assigned_editors[]" 
                                       value="<?php echo $user['user_id']; ?>"
                                       id="user_<?php echo $user['user_id']; ?>"
                                       <?php echo (isset($_POST['assigned_editors']) && in_array($user['user_id'], $_POST['assigned_editors'])) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="user_<?php echo $user['user_id']; ?>">
                                    <span class="user-fullname"><?php echo htmlspecialchars($user['fullname'] ?: $user['username']); ?></span>
                                    <span class="user-username"><?php echo htmlspecialchars($user['username']); ?></span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Select All / None buttons -->
                <div class="selection-buttons">
                    <button type="button" class="btn btn-outline-primary" data-action="select-all-users">
                        <i class="fas fa-check-double me-1"></i>Select All
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-action="select-no-users">
                        <i class="fas fa-times me-1"></i>Select None
                    </button>
                </div>
            <?php else: ?>
                <div class="no-users-message">
                    <i class="fas fa-info-circle me-2"></i>
                    No other users found in your agency.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 