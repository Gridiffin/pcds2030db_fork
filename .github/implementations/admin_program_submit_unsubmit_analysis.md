# Admin Program List - Submit/Unsubmit Button Analysis

## Overview
This document analyzes the implementation of submit/unsubmit functionality in the admin side of the program list.

## Current Implementation Status

### ✅ YES - The functionality is implemented

The admin program list (`app/views/admin/programs/programs.php`) includes both **Unsubmit** and **Resubmit** buttons in the actions column.

## Implementation Details

### 1. Location of Buttons
The buttons are located in the actions column of the programs table, specifically:
- **Line 217-238** in `programs.php`
- They appear below the primary action buttons (View, Edit, Delete)

### 2. Button Logic
The buttons are conditionally displayed based on the submission status:

```php
<?php if (isset($program['submission_id'])): // Ensure there is a submission record ?>
    <?php if (!empty($program['is_draft'])): ?>
        <!-- Show Resubmit button for draft submissions -->
        <a href="resubmit.php?program_id=<?php echo $program['program_id']; ?>&period_id=<?php echo $period_id; ?>" 
           class="btn btn-outline-success btn-sm w-100" 
           title="Resubmit Program for this Period">
            <i class="fas fa-check-circle"></i> Resubmit
        </a>
    <?php elseif (isset($program['status']) && $program['status'] !== null): ?>
        <!-- Show Unsubmit button for submitted programs -->
        <a href="unsubmit.php?program_id=<?php echo $program['program_id']; ?>&period_id=<?php echo $period_id; ?>" 
           class="btn btn-outline-warning btn-sm btn-unsubmit w-100" 
           title="Unsubmit Program for this Period">
            <i class="fas fa-undo"></i> Unsubmit
        </a>
    <?php endif; ?>
<?php endif; ?>
```

### 3. Button Behavior

#### Unsubmit Button (`unsubmit.php`)
- **Purpose**: Reverts a submitted program back to draft status
- **Action**: Sets `is_draft = 1` and `status = 'not-started'`
- **Confirmation**: Shows JavaScript confirm dialog before action
- **Access**: Admin only
- **Visual**: Orange/warning colored button with undo icon

#### Resubmit Button (`resubmit.php`)
- **Purpose**: Marks a draft submission as officially submitted
- **Action**: Sets `is_draft = 0` and updates timestamps
- **Confirmation**: Shows JavaScript confirm dialog before action
- **Access**: Admin only
- **Visual**: Green/success colored button with check-circle icon

### 4. Security Features
- Admin authentication check at the beginning of each file
- Input validation for program_id and period_id
- Session-based error/success messages
- Action logging (if log_action function exists)

### 5. User Experience
- Clear visual distinction between buttons (color coding)
- Confirmation dialogs prevent accidental actions
- Descriptive tooltips on hover
- Success/error messages after action completion
- Automatic redirect back to programs list with period context maintained

## File Structure
```
app/views/admin/programs/
├── programs.php        # Main program list with buttons
├── unsubmit.php       # Handler for unsubmitting
└── resubmit.php       # Handler for resubmitting
```

## Conclusion
The submit/unsubmit functionality is fully implemented in the admin program list with proper security, user experience considerations, and clear visual indicators for different states.