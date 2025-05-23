# Missing is_agency() Function Fix

## Problem Description
The application is experiencing a fatal error in the agency dashboard due to an undefined function:
```
Fatal error: Uncaught Error: Call to undefined function is_agency() in D:\laragon\www\pcds2030_dashboard\app\views\agency\dashboard.php:23
```

This error occurs because the `is_agency()` function is missing from the current app structure. The function was previously defined in the deprecated project structure but wasn't migrated to the new structure.

## Solution Steps

- [x] Check dashboard.php to understand how the is_agency() function is used
- [x] Search the codebase for existing implementations of is_agency()
- [x] Review the session.php and other authentication-related files to understand the user role management
- [x] Create the app/lib/agencies/core.php file with the is_agency() function
- [x] Update the app/lib/agencies/index.php file to always include the core.php file
- [x] Include additional agency-related helper functions in the core.php file for consistency with the admin implementation

## Implementation Details

1. Created app/lib/agencies/core.php with:
   - is_agency() function to check if current user has the 'agency' role
   - require_agency() function for middleware-like protection
   - get_agency_id() function for easy access to the agency ID

2. Updated app/lib/agencies/index.php to:
   - Always include core.php instead of conditionally including it
   - Keep the inclusion of programs.php and commented placeholders for potential future files

## Testing

The fix should:
- Allow the agency dashboard to load without errors
- Properly redirect non-agency users to the login page
- Maintain consistency with the authentication pattern used throughout the application
