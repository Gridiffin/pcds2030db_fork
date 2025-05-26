# Outcome Management Restrictions Implementation

**Date:** 2025-05-26  
**Status:** ✅ **COMPLETED**

## Requirements

Based on client feedback, outcomes should be consistent throughout reports, with only the data changing over years. This requires changes to the outcome management system:

1. **Lock Outcome Creation Behind Admin Toggle**
   - Create a new system setting to control outcome creation functionality
   - Only admins will be able to enable/disable this feature
   - When disabled, hide/disable "Create New Outcome" buttons

2. **Remove Delete Functionality**
   - Remove delete buttons from outcome management pages
   - Focus initially on admin-side interfaces

3. **Implement Outcome History Tracking**
   - Track changes to outcome structures
   - Support the existing workflow:
     - Agency edits outcomes → Save & Submit (locks editing)
     - Admin generates report using finalized version
     - Next reporting period → Admin unsubmits → Agency can edit again

## Implementation Tasks

### 1. System Settings for Outcome Creation Toggle

- [x] Create a new entry in the settings table for "allow_outcome_creation"
- [x] Create UI for toggling this setting in the admin settings page
- [x] Update settings retrieval functions to include this new setting

### 2. Remove Delete Buttons & Restrict Outcome Creation

- [x] Remove delete buttons from admin outcome management pages
- [x] Modify "Create New Outcome" button visibility based on the system setting
- [x] Update JavaScript handlers to check for the setting value

### 3. Implement Outcome History Tracking

- [x] Design database schema for outcome version history
- [x] Create version tracking functionality when outcomes are edited
- [x] Implement UI to view outcome history/changes
- [x] Ensure versioning captures the workflow stages

## Implementation Details

### Database Schema
Created a new table for outcome history tracking:
```sql
CREATE TABLE outcome_history (
  history_id INT PRIMARY KEY AUTO_INCREMENT,
  outcome_record_id INT NOT NULL,
  metric_id INT NOT NULL,
  data_json LONGTEXT NOT NULL,
  action_type ENUM('create', 'edit', 'submit', 'unsubmit') NOT NULL,
  status ENUM('draft', 'submitted') NOT NULL,
  changed_by INT NOT NULL,
  change_description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (outcome_record_id) REFERENCES sector_outcomes_data(id),
  FOREIGN KEY (changed_by) REFERENCES users(user_id)
)
```

### Implemented Files and Changes
1. **Config and Settings**
   - Added `ALLOW_OUTCOME_CREATION` constant to `config.php`
   - Implemented toggle in system settings page with documentation
   - Created `get_outcome_creation_setting()` and `update_outcome_creation_setting()` functions

2. **History Tracking**
   - Created `record_outcome_history()` and `get_outcome_history()` functions
   - Updated `save_outcome_json.php` to record history when outcomes are edited
   - Updated `edit_outcome.php` to record history when metadata changes
   - Updated `unsubmit_outcome.php` to record history when outcomes are unsubmitted

3. **UI Changes**
   - Added outcome history viewer page (`outcome_history.php`)
   - Added API endpoint to get history data (`get_outcome_history_data.php`)
   - Added history button to outcome management and view pages
   - Added information alerts about restrictions in both system settings and outcome management pages
   - Hidden create buttons based on system setting

## Completion Notes
All requirements have been successfully implemented. The system now maintains consistent outcome structures across reporting periods while providing a complete history of changes. Admins have control over outcome creation through a system toggle, and the deletion functionality has been removed to preserve data integrity.

Future enhancements could include:
- Ability to restore a previous version of an outcome
- More detailed comparison between versions
- Enhanced filtering and search in the history view
