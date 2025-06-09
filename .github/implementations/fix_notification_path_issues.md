# Fix Notification System Path Issues

## Problem Description
Two critical path-related issues in the notification system:
1. **Header Include Error**: `all_notifications.php` cannot find `../layouts/header.php` 
2. **Action URL Redirect Error**: Individual notification links redirect to wrong directories

## Root Cause Analysis
- File path references are incorrect relative to the current file location
- Notification action URLs may be using relative paths instead of absolute paths
- Need to verify the actual file structure and correct path references

## Implementation Tasks

### Phase 1: Analyze Current File Structure ✅
- [ ] Examine the actual location of `all_notifications.php`
- [ ] Check the correct path to `layouts/header.php` 
- [ ] Verify notification action URL generation in database
- [ ] Check navigation dropdown notification link paths

### Phase 2: Fix Header Include Path
- [ ] Correct the require_once path in `all_notifications.php`
- [ ] Update footer include path if needed
- [ ] Test page loading after fix

### Phase 3: Fix Notification Action URLs 
- [ ] Examine how action URLs are generated during notification creation
- [ ] Fix action URL paths to use absolute URLs
- [ ] Update existing notification URLs in database if needed
- [ ] Test individual notification links from dropdown

### Phase 4: Verify Navigation Links
- [ ] Check "View all notifications" link in dropdown
- [ ] Ensure proper URL generation using APP_URL
- [ ] Test complete notification flow

### Phase 5: Testing & Validation
- [ ] Test notification dropdown functionality
- [ ] Test "View all notifications" page loading
- [ ] Test individual notification click actions
- [ ] Verify all paths work correctly

## Expected Outcome
- ✅ All notifications page loads without errors
- ✅ Individual notification links work correctly  
- ✅ Navigation between notification views functions properly
- ✅ No more path-related warnings or fatal errors

## Files to Modify
- `app/views/agency/users/all_notifications.php` - Fix header/footer includes
- `app/views/layouts/agency_nav.php` - Fix dropdown notification links
- `app/views/admin/programs/assign_programs.php` - Fix action URL generation
- Database records (if needed) - Update existing malformed action URLs
