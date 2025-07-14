# Program Agency Assignments Feature

## Overview
Implement a system where multiple agencies can be assigned to programs with different access levels (edit vs view permissions).

## Database Design

### New Table: `program_agency_assignments`
```sql
CREATE TABLE program_agency_assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT NOT NULL,
    agency_id INT NOT NULL,
    role ENUM('owner', 'editor', 'viewer') NOT NULL DEFAULT 'viewer',
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (program_id) REFERENCES programs(program_id),
    FOREIGN KEY (agency_id) REFERENCES agency(agency_id),
    FOREIGN KEY (assigned_by) REFERENCES users(user_id),
    UNIQUE KEY unique_program_agency (program_id, agency_id)
);
```

### Role Definitions
- **owner**: Full edit access, can assign other agencies, can delete program
- **editor**: Can edit submissions, targets, and attachments
- **viewer**: Read-only access to program and submissions

## Implementation Steps

### Phase 1: Database Setup
- [ ] Create migration script for new table
- [ ] Migrate existing programs to use agency assignments
- [ ] Set original agency_id as 'owner' role

### Phase 2: Backend Functions
- [ ] Create functions to check agency permissions
- [ ] Update get_program_details() to include agency assignments
- [ ] Create functions for managing agency assignments

### Phase 3: Frontend Updates
- [ ] Update ownership logic in all program views
- [ ] Add agency assignment management interface
- [ ] Update permission checks across all submission pages

### Phase 4: Admin Interface
- [ ] Create admin page for managing program assignments
- [ ] Bulk assignment features
- [ ] Assignment history and audit logs

## Permission Logic

### For Programs:
```php
function get_user_program_role($program_id, $user_agency_id) {
    // Check program_agency_assignments table
    // Return: 'owner', 'editor', 'viewer', or false
}

function can_edit_program($program_id, $user_agency_id) {
    $role = get_user_program_role($program_id, $user_agency_id);
    return in_array($role, ['owner', 'editor']);
}

function can_view_program($program_id, $user_agency_id) {
    $role = get_user_program_role($program_id, $user_agency_id);
    return in_array($role, ['owner', 'editor', 'viewer']);
}
```

### For Submissions:
- Inherit permissions from program assignments
- Same agency role applies to all submissions within that program

## Benefits
1. **Collaborative Programs**: Multiple agencies can work on shared initiatives
2. **Granular Permissions**: Different access levels for different agencies
3. **Audit Trail**: Track who assigned which agency to which program
4. **Scalability**: Easy to add/remove agency access without affecting program structure
5. **Reporting**: Better visibility into cross-agency collaborations

## Migration Strategy
1. Create new table
2. Populate with existing program owners (agency_id → owner role)
3. Update all permission checks to use new system
4. Gradually migrate views to use new permission logic
5. Add assignment management interface for admins

## Files to Update
- `app/lib/agencies/programs.php` - Core permission functions
- All program view files - Update $is_owner logic
- Admin interfaces - Add assignment management
- Database migration script

## Next Steps
1. Create the database table
2. Write core permission functions
3. Update view_submissions.php to use new permission system
4. Test with existing data
