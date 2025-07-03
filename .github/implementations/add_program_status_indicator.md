# Add Program Status Indicator Selection

## Problem Description

The user wants to add a status indicator selection above the "Put Program on Hold" checkbox in the program update form. This will allow users to set the program's overall status (not-started, in-progress, completed) which will be stored in the database.

## Database Analysis

The `programs` table already has a `status_indicator` column:

- **Type**: SET('not-started','in-progress','completed')
- **Allows**: Single selection from predefined values
- **Current**: Not being used in the form

## Implementation Plan

### Step 1: Add Backend Processing

- [x] Add POST data extraction for `status_indicator`
- [x] Include in database update query

### Step 2: Add Frontend Form Field

- [x] Add a select dropdown for status indicator
- [x] Position it above the hold point checkbox
- [x] Add proper styling and labels
- [x] Pre-populate from database

### Step 3: Update Form Validation

- [ ] Add validation for status indicator field
- [ ] Ensure proper form submission handling

### Step 4: Test Implementation

- [ ] Test saving different status values
- [ ] Verify database updates correctly
- [ ] Test form pre-population

## Implementation Details

### Status Options

- `not-started` - Program has not begun
- `in-progress` - Program is currently active
- `completed` - Program has been finished

### UI Design

- Select dropdown with clear labels
- Icon indicators for each status
- Positioned in "Program Status" section above hold point
- Consistent styling with existing form elements

## Files to Modify

1. `app/views/agency/programs/update_program.php` - Add form field and backend processing

## Implementation Details

### Backend Processing Added

```php
// Line ~410: POST data extraction
$status_indicator = !empty($_POST['status_indicator']) ? $_POST['status_indicator'] : null;

// Line ~596: Database update
$update_fields[] = "status_indicator = ?";
$update_params[] = $status_indicator;
$param_types .= 's';
```

### Frontend Form Field Added

```html
<!-- Status Indicator Selection -->
<div class="mb-4">
  <label for="status_indicator" class="form-label fw-medium">
    <i class="fas fa-flag me-2"></i>
    Overall Program Status
  </label>
  <select class="form-select" id="status_indicator" name="status_indicator">
    <option value="">Select Status...</option>
    <option value="not-started">🕒 Not Started</option>
    <option value="in-progress">▶️ In Progress</option>
    <option value="completed">✅ Completed</option>
  </select>
  <div class="form-text">
    <i class="fas fa-info-circle me-1"></i>
    Set the overall status to track the program's progress at a high level.
  </div>
</div>
```

### Form Location

- **Section**: Program Status (after start/end dates, before hold point checkbox)
- **Card**: Basic Information card
- **Position**: Above "Put Program on Hold" checkbox

## Testing Instructions

1. Navigate to a program update page
2. Look for "Overall Program Status" dropdown in the Program Status section
3. Select a status and save the program
4. Verify the status is saved to the database
5. Reload the page and confirm the status is pre-selected
