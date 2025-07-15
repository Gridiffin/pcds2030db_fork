# Simplify User Permission Logic

## Problem Analysis

The current user permission logic is confusing because:

1. **"viewer" role is redundant** - Agency users can already view programs owned by their agency
2. **"editor" role is only meaningful when restrictions are enabled**
3. **UI is confusing** - Asks "who should be the editor" instead of "what role should each user have"
4. **Logic is complex** - Multiple layers of permissions that are hard to understand

## Current Logic Issues

### Current Permission Flow:
1. Agency ownership grants base access to all agency users
2. If `restrict_editors` is enabled, only assigned users can edit
3. "viewer" role allows viewing (but they already can view by default)
4. "editor" role allows editing (only when restrictions are enabled)

### Problems:
- **Redundant viewer role**: Agency users can already view their programs
- **Confusing UI**: Shows "Select editors" instead of "Assign user roles"
- **Complex logic**: Multiple permission layers that are hard to follow

## Proposed Solution

### New User Role Assignment Logic:

1. **List all agency users**: Show all users in the agency (except focal and owner)
2. **Role assignment**: For each user, assign either "Editor" or "Viewer" role for that specific program
3. **Clear UI**: Dropdown for each user to select their role: "No Access", "Viewer", or "Editor"
4. **Permission-based access**: Users can only access programs based on their assigned role

### New Permission Flow:
```
Can View Program?
├── Is user admin/focal? → YES (always)
├── Is user assigned as viewer? → YES
└── Is user assigned as editor? → YES

Can Edit Program?
├── Is user admin/focal? → YES (always)
└── Is user assigned as editor? → YES (only assigned editors)
```

### UI Changes:
- **Show all agency users** (except focal and owner) in the user assignment section
- **Role dropdown**: For each user, select "No Access", "Viewer", or "Editor"
- **Clear labeling**: "Assign User Roles" with dropdowns for each user
- **Better UX**: "What role should this user have for this program?"

## Implementation Plan

### Phase 1: Update Permission Logic ✅
- [x] Update `can_view_program()` to check for assigned roles (editor or viewer)
- [x] Update `can_edit_program()` to check for "editor" role
- [x] Update `assign_user_to_program()` to accept both 'editor' and 'viewer' roles
- [x] Update `get_user_program_user_role()` to return both roles

### Phase 2: Update Database
- [ ] Keep `program_user_assignments` table with both 'editor' and 'viewer' roles
- [ ] Ensure ENUM constraint allows both roles

### Phase 3: Update UI ✅
- [x] Change from checkboxes to role dropdowns for each user
- [x] Show all agency users in the assignment list
- [x] Update labels and help text
- [x] Improve user experience with clearer messaging

### Phase 4: Update Functions ✅
- [x] Update `get_assignable_users_for_program()` to show all agency users
- [x] Update `assign_user_to_program()` to accept both roles
- [x] Update form processing to handle `user_roles` array
- [x] Remove old JavaScript functions

## Benefits

1. **Clear role assignment**: Each user has an explicit role for each program
2. **Better UX**: Dropdown interface makes role assignment clear and intuitive
3. **Flexible permissions**: Can assign different roles to different users for the same program
4. **Consistent behavior**: Users can only access programs based on their assigned role

## Files to Update

### Core Permission Files:
- `app/lib/agencies/program_permissions.php`
- `app/views/agency/programs/edit_program.php`
- `app/views/admin/programs/edit_program.php`

### Database:
- `program_user_assignments` table schema
- Any migration scripts

### UI/UX:
- Update labels and messaging
- Improve user assignment interface
- Add better help text and explanations 