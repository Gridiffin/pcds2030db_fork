<?php
/**
 * Program Form Partial
 * Contains the main program information form fields
 */
?>

<!-- Program Name -->
<div class="form-group">
    <label for="program_name" class="form-label">
        Program Name <span class="required-field">*</span>
    </label>
    <input type="text" 
           class="form-control" 
           id="program_name" 
           name="program_name" 
           required
           placeholder="Enter the program name"
           value="<?php echo htmlspecialchars($_POST['program_name'] ?? ''); ?>">
    <div class="form-text">
        <i class="fas fa-info-circle me-1"></i>
        This will be the main identifier for your program
    </div>
</div>

<!-- Initiative Selection -->
<div class="form-group">
    <label for="initiative_id" class="form-label">
        Link to Initiative
        <span class="badge bg-secondary ms-1">Optional</span>
    </label>
    <select class="form-select" id="initiative_id" name="initiative_id">
        <option value="">Select an initiative (optional)</option>
        <?php foreach ($active_initiatives as $initiative): ?>
            <option value="<?php echo $initiative['initiative_id']; ?>"
                    <?php echo (isset($_POST['initiative_id']) && $_POST['initiative_id'] == $initiative['initiative_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($initiative['initiative_name']); ?>
                <?php if ($initiative['initiative_number']): ?>
                    (<?php echo htmlspecialchars($initiative['initiative_number']); ?>)
                <?php endif; ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="form-text">
        <i class="fas fa-lightbulb me-1"></i>
        Link this program to a strategic initiative for better organization and reporting
    </div>
</div>

<!-- Program Number -->
<div class="form-group">
    <label for="program_number" class="form-label">
        Program Number
    </label>
    <input type="text" 
           class="form-control" 
           id="program_number" 
           name="program_number" 
           placeholder="Select initiative first"
           disabled
           pattern="[\w.]+"
           title="Program number can contain letters, numbers, and dots"
           value="<?php echo htmlspecialchars($_POST['program_number'] ?? ''); ?>">
    <div class="form-text">
        <i class="fas fa-info-circle me-1"></i>
        <span id="number-help-text">Select an initiative to enable program numbering</span>
    </div>
    <div id="final-number-display" class="program-number-preview" style="display: none;">
        <small class="text-muted">Final number will be: <span id="final-number-preview"></span></small>
    </div>
    <div id="number-validation" class="validation-feedback">
        <small id="validation-message"></small>
    </div>
</div>

<!-- Brief Description -->
<div class="form-group">
    <label for="brief_description" class="form-label">Brief Description</label>
    <textarea class="form-control" 
              id="brief_description" 
              name="brief_description"
              rows="3"
              placeholder="Provide a short summary of the program"><?php echo htmlspecialchars($_POST['brief_description'] ?? ''); ?></textarea>
    <div class="form-text">
        <i class="fas fa-info-circle me-1"></i>
        A brief overview to help identify this program
    </div>
</div> 