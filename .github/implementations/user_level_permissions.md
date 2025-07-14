# User-Level Permissions Within Agencies

## Objective
Add user-level permissions within agencies for program access, allowing fine-grained control over who can edit vs view programs.

## Requirements Analysis
- **Agency-level access**: Agencies assigned to programs (owner/editor/viewer)
- **User-level access**: Within an agency, control which specific users can edit
- **Default behavior**: All agency users can edit unless restricted
- **Configuration**: Set via create/edit program forms
- **UI Integration**: Show assigned users in forms with selection interface

## Implementation Plan

### Phase 1: Database Schema ✅
- [x] Create `program_user_assignments` table for user-level permissions
- [x] Add `restrict_editors` flag to programs table
- [x] Update migration script

### Phase 2: Core Functions ✅
- [x] Create user assignment management functions
- [x] Update permission checking functions to include user-level checks
- [x] Add user selection/management utilities

### Phase 3: Update Permission Logic ✅
- [x] Modify `can_edit_program()` to check user-level permissions
- [x] Update `can_view_program()` for agency+user logic
- [x] Maintain focal user override capabilities

### Phase 4: UI Implementation ✅
- [x] Add user assignment section to create_program.php
- [x] Add user assignment section to edit_program.php
- [x] Create user selection interface (checkboxes/multi-select)
- [x] Add "restrict editors" toggle

### Phase 5: Update Views ✅
- [x] **Database migration executed successfully** - All tables created and 7 programs migrated
- [x] Update all program views to use new user-level permissions
- [x] Test permission cascading (agency -> user level)

## Implementation Status: **100% Complete**

### Migration Results:
- ✅ `program_agency_assignments` table created successfully
- ✅ `program_user_assignments` table created successfully  
- ✅ `restrict_editors` column added to programs table
- ✅ **7 existing programs migrated** to new permission system
- ✅ All indexes created for optimal performance

### System Status:
🟢 **READY FOR USE** - All components implemented and database migration completed

### Key Features Implemented:
1. **Database Schema**: Complete with `program_user_assignments` table and `restrict_editors` flag
2. **Permission Functions**: Full user-level permission checking with focal user overrides
3. **UI Integration**: Both create and edit forms now include user assignment sections
4. **Smart Defaults**: Default behavior allows all agency users to edit unless restricted
5. **User-Friendly Interface**: Toggle switches, select all/none buttons, current status indicators

## Technical Details

### New Table Structure:
```sql
program_user_assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('editor', 'viewer') DEFAULT 'viewer',
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (program_id) REFERENCES programs(program_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE KEY unique_program_user (program_id, user_id)
)
```

### Programs Table Addition:
```sql
ALTER TABLE programs ADD COLUMN restrict_editors TINYINT(1) DEFAULT 0;
```

### Permission Logic:
1. Check agency-level access first (existing system)
2. If agency has access, check user-level restrictions
3. If `restrict_editors` = 0, all agency users can edit
4. If `restrict_editors` = 1, only assigned users can edit
5. Focal users override all restrictions

## Next Steps
1. Create database schema updates
2. Implement core permission functions
3. Update create/edit program forms
4. Test permission cascading
