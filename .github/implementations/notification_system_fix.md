# Notification System Fix - Agency-Wide Notifications

## Problem Identified ✅

**Issue:** The notification system had a critical flaw where agency users would **NOT receive notifications** for important program actions if no specific users were assigned as "editors" or "viewers" in the `program_user_assignments` table.

**Root Cause:** When a program creator doesn't restrict editing (`restrict_editors = 0`), no users get assigned to the program, which means the notification queries return **zero results**.

**Impact:** Agency members missed critical updates about:
- Program edits
- Submission creation/edits
- Submission finalization
- Program deletions

## Solution Implemented ✅

### **New Notification Logic:**

The notification system now uses **smart targeting** based on the `restrict_editors` flag:

```php
// Determine who to notify based on program restrictions
if ($program['restrict_editors']) {
    // If editing is restricted, notify only assigned users
    $assigned_users_query = "SELECT DISTINCT user_id FROM program_user_assignments 
                           WHERE program_id = ? AND user_id != ? AND role IN ('editor', 'viewer') AND is_active = 1";
    // ... notify assigned users only
} else {
    // If editing is not restricted, notify all agency users (excluding action creator)
    $agency_users_query = "SELECT user_id FROM users WHERE agency_id = ? AND user_id != ? AND is_active = 1";
    // ... notify all agency users
}
```

### **Functions Updated:**

1. ✅ `notify_program_edited()` - Now notifies all agency users when `restrict_editors = 0`
2. ✅ `notify_submission_created()` - Now notifies all agency users when `restrict_editors = 0`
3. ✅ `notify_submission_edited()` - Now notifies all agency users when `restrict_editors = 0`
4. ✅ `notify_submission_finalized()` - Now notifies all agency users when `restrict_editors = 0`
5. ✅ `notify_program_deleted()` - Now notifies all agency users when `restrict_editors = 0`
6. ✅ `notify_submission_deleted()` - Now notifies all agency users when `restrict_editors = 0`

### **Notification Recipients by Action:**

#### **When `restrict_editors = 0` (Default - No Restrictions):**
- **All agency users** (excluding action creator)
- **Focal users** (always notified, regardless of restrictions)
- **All admin users** (always notified)

#### **When `restrict_editors = 1` (Restricted Editing):**
- **Only assigned users** (editors/viewers from `program_user_assignments`)
- **Focal users** (always notified, regardless of restrictions)
- **All admin users** (always notified)

## Benefits of This Fix ✅

1. **No More Silent Updates:** Agency members will always be notified of important changes
2. **Flexible Targeting:** System adapts based on program restrictions
3. **Consistent Behavior:** All notification functions follow the same logic
4. **Backward Compatible:** Existing restricted programs continue to work as before
5. **Better Communication:** Ensures transparency within agencies

## Testing Scenarios ✅

### **Scenario 1: Unrestricted Program (`restrict_editors = 0`)**
- **Action:** User creates/edits submission
- **Expected:** All agency users get notified
- **Result:** ✅ Fixed - Now works correctly

### **Scenario 2: Restricted Program (`restrict_editors = 1`)**
- **Action:** User creates/edits submission
- **Expected:** Only assigned users get notified
- **Result:** ✅ Maintained - Existing behavior preserved

### **Scenario 3: Mixed Agency**
- **Action:** Any program action
- **Expected:** Focal users always notified, admins always notified
- **Result:** ✅ Maintained - Existing behavior preserved

## Files Modified ✅

- `app/lib/notifications_core.php` - Updated all notification functions

## Implementation Status ✅

**Status:** **COMPLETED** - All notification functions updated and tested

**Impact:** This fix ensures that agency users will **always receive notifications** for important program actions, regardless of whether specific users are assigned to programs.

---

## Key Takeaway

**Before:** If no users were assigned to a program, nobody got notified → **Communication gap**

**After:** All agency users get notified by default → **Full transparency**

This fix addresses the fundamental issue you identified and ensures the notification system works as expected for all scenarios. 

## Toast Notification Spam Fix (2024-06-13)

**Problem:** Toast notifications for unread notifications would pop up on every page load, causing annoyance and overriding important toasts.

**Solution:**
- The notification system now tracks the initial load and will **not show toasts for unread notifications on the first page load**.
- Toasts will only be shown for new notifications that arrive after the page is loaded (e.g., via polling or push).
- This prevents repeated toast spam and ensures only new, relevant notifications are shown as toasts.

**File updated:**
- `assets/js/components/notification-system.js`

**How it works:**
- On first load, the notification system sets a flag (`_initialLoad = true`).
- When notifications are updated, toasts are only shown if `_initialLoad` is `false` (i.e., after the first update).
- This ensures toasts are only shown for new notifications, not for all unread notifications on every refresh.

**Result:**
- No more repeated toast spam for unread notifications.
- Important toasts (like "program created") are no longer overridden by old unread notifications.
- UX is much improved for all users.

---

## Session Message Spam Fix (2024-06-13)

**Problem:** Notification messages were being stored in session and displayed as toasts on every page load, causing persistent toast spam.

**Solution:**
- Added comprehensive checks to prevent notification-related messages from being stored in session.
- Added filtering in the view to prevent notification messages from being displayed as toasts.
- Added a safe session message function that prevents notification messages from being stored.

**Files updated:**
- `app/views/agency/programs/view_programs.php` - Added comprehensive session cleanup
- `app/views/agency/programs/view_programs_content.php` - Added notification message filtering
- `app/views/agency/programs/add_submission.php` - Added comprehensive session cleanup
- `app/views/agency/programs/partials/add_submission_content.php` - Added notification message filtering
- `app/views/agency/programs/create_program.php` - Added notification message filtering
- `app/views/agency/programs/partials/edit_program_content.php` - Added notification message filtering
- `app/views/admin/programs/view_programs_content.php` - Added notification message filtering
- `app/views/admin/programs/add_submission.php` - Added notification message filtering
- `app/views/admin/programs/partials/programs_content.php` - Added notification message filtering
- `app/lib/agencies/notifications.php` - Added documentation about not storing in session
- `app/lib/functions.php` - Added `set_session_message()` function

**How it works:**
- The system now checks for notification-related keywords before storing messages in session.
- Notification messages are filtered out and not displayed as toasts.
- Session cleanup is more comprehensive to prevent persistent messages.

**Result:**
- No more persistent session messages causing toast spam.
- Notification messages are properly handled through the notification system only.
- Clean separation between session messages and notification messages.

--- 

---

## Dropdown Button Fix (2024-06-13)

**Problem:** The "View Submission" and "Review & Finalize" buttons in the program dropdown menu were causing 404 errors because they tried to use AJAX endpoints that had URL construction issues.

**Solution:**
- Created a proper submission selection modal that matches the one in program details
- Fixed the AJAX URL construction to use the correct base URL
- Made the dropdown buttons open the same modal as the program details page

**Files updated:**
- `app/views/agency/programs/partials/program_row.php` - Changed dropdown buttons to use `openSubmissionModal()`
- `app/views/agency/programs/view_programs_content.php` - Added submission modal and JavaScript functions

**How it works:**
- "View Submission" dropdown button now calls `openSubmissionModal(programId)`
- "Review & Finalize" dropdown button now calls `openSubmissionModal(programId)`
- The modal loads submission data via AJAX using the correct URL construction
- Users can select a submission period and navigate to view it

**Result:**
- No more 404 errors from dropdown buttons
- Exact same modal experience as the program details page
- Proper URL construction that works in all environments

--- 

---

## Dropdown Auto-Close Improvement (2024-06-13)

**Problem:** When users selected options from the dropdown menu, the dropdown remained open, creating visual clutter and potentially interfering with modals and other UI elements.

**Solution:**
- Added automatic dropdown closing functionality when any dropdown option is selected
- Created `closeDropdownAndOpenModal()` function for modal-triggering options
- Created `closeDropdownAndNavigate()` function for navigation options
- Updated all dropdown items to use these functions

**Files updated:**
- `app/views/agency/programs/partials/program_row.php` - Updated all dropdown items to use auto-close functions
- `app/views/agency/programs/view_programs_content.php` - Added dropdown closing JavaScript functions

**How it works:**
- **View Program**: Closes dropdown and navigates to program details
- **View Submission**: Closes dropdown and opens submission modal
- **Edit Program**: Closes dropdown and navigates to edit page
- **Add Submission**: Closes dropdown and navigates to add submission page
- **Edit Submission**: Closes dropdown and navigates to edit submission page
- **Review & Finalize**: Closes dropdown and opens submission modal

**Result:**
- Cleaner UI with no lingering dropdowns
- Better space management for modals and other elements
- Improved user experience with automatic cleanup

--- 