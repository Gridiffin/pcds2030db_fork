# Fix Outcome Delete Button and Edit Routing Implementation

**Date:** 2023-06-05  
**Status:** ✅ **IMPLEMENTED**

## Problem

Three issues were identified with the outcomes management page buttons:

1. **Delete Button Issue - Parameter Mismatch**: The delete button in manage_outcomes.php was passing `metric_id` parameter, but delete_outcome.php was only checking for `outcome_id` parameter.

2. **Edit Button Issue - Parameter Mismatch**: The edit button was routing correctly but edit_outcome.php was not processing the `metric_id` parameter, only looking for `outcome_id`.

3. **Delete Button Issue - SQL Query Error**: The delete query in delete_outcome.php was using the column name `outcome_id` which doesn't exist in the `sector_outcomes_data` table. The correct column name is `metric_id`.

## Solution

1. **Delete Button Parameter Fix**:
   - Updated delete_outcome.php to support both `metric_id` and `outcome_id` parameters
   - Added parameter fallback logic similar to unsubmit_outcome.php

2. **Edit Button Fix**:
   - Updated edit_outcome.php to accept and process `metric_id` parameter
   - Kept backward compatibility with `outcome_id` parameter
   - Fixed "Back" button to link to manage_outcomes.php instead of manage_metrics.php
   - Updated redirect when creating new outcomes to use `metric_id` parameter for consistency

3. **Delete SQL Query Fix**:
   - Updated the SQL query in delete_outcome.php to use `metric_id` instead of `outcome_id`
   - Fixed the "Unknown column 'outcome_id'" error by using the correct column name

## Files Changed

1. `app/views/admin/outcomes/delete_outcome.php`
   - Added support for both `metric_id` and `outcome_id` parameters
   - Fixed SQL query to use the correct column name (`metric_id` instead of `outcome_id`)

2. `app/views/admin/outcomes/edit_outcome.php`
   - Added support for `metric_id` parameter
   - Fixed back button URL
   - Updated redirect URL after creation to use `metric_id`

## Technical Implementation

### Delete Button Parameter Fix
```php
// Support both metric_id (new) and outcome_id (legacy) parameters
if (isset($_GET['metric_id']) && is_numeric($_GET['metric_id'])) {
    $outcome_id = (int) $_GET['metric_id'];
} else if (isset($_GET['outcome_id']) && is_numeric($_GET['outcome_id'])) {
    $outcome_id = (int) $_GET['outcome_id'];
} else {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: ' . APP_URL . '/app/views/admin/outcomes/manage_outcomes.php');
    exit;
}
```

### Delete SQL Query Fix
```php
// The table uses metric_id column, not outcome_id
$query_main = "DELETE FROM sector_outcomes_data WHERE metric_id = ?";
$stmt_main = $conn->prepare($query_main);
if (!$stmt_main) {
    throw new Exception("Prepare failed (main): (" . $conn->errno . ") " . $conn->error);
}
$stmt_main->bind_param("i", $outcome_id);
$stmt_main->execute();
```

### Edit Button Fix
```php
// Support both metric_id and outcome_id parameters for backward compatibility
$outcome_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 
             (isset($_GET['outcome_id']) ? intval($_GET['outcome_id']) : 
             (isset($_POST['outcome_id']) ? intval($_POST['outcome_id']) : 0));
```

## Notes

- The system inconsistently uses both `metric_id` and `outcome_id` to refer to the same entity.
- The database schema uses `metric_id` in the `sector_outcomes_data` table, not `outcome_id`.
- This implementation maintains backward compatibility with both parameter names.
- Long-term, the codebase should be standardized to use one consistent parameter name, preferably `metric_id` to match the database schema.
