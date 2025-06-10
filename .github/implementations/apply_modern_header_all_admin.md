# Apply Modern Header to All Admin Pages

## Summary
Following our successful implementation of the modern green header component on the dashboard and outcomes pages, we need to apply this same consistent header style to all remaining admin pages for a unified user experience.

## Implementation Plan

### Phase 1: Discovery and Analysis
- [ ] **Task 1.1**: Find all admin PHP files in views/admin folder
- [ ] **Task 1.2**: Analyze current header implementations in each file
- [ ] **Task 1.3**: Categorize files by header type (dashboard vs content pages)
- [ ] **Task 1.4**: Identify files that need green vs white variants

### Phase 2: Systematic Migration
- [ ] **Task 2.1**: Update user management pages (add_user.php, edit_user.php, manage_users.php)
- [ ] **Task 2.2**: Update program management pages (assign_programs.php, programs.php, etc.)
- [ ] **Task 2.3**: Update settings pages (system_settings.php, audit_log.php, etc.)
- [ ] **Task 2.4**: Update reports pages (generate_reports.php)
- [ ] **Task 2.5**: Update remaining outcome pages (create_outcome.php, edit_outcome.php, etc.)
- [ ] **Task 2.6**: Update metrics pages (manage_metrics.php)
- [ ] **Task 2.7**: Update period management pages

### Phase 3: Validation and Cleanup
- [ ] **Task 3.1**: Test all updated pages for consistency
- [ ] **Task 3.2**: Remove old header implementations where applicable
- [ ] **Task 3.3**: Update documentation

## Header Strategy

### Green Variant (Dashboard-style pages)
- Main landing pages
- Overview/summary pages
- Primary navigation pages

### White Variant (Content/Form pages)
- CRUD operations (Create, Edit, Delete)
- Form-heavy pages
- Detail/view pages
- Management interfaces

## File Categories Identified

### User Management
- manage_users.php (white - management interface)
- add_user.php (white - form page)
- edit_user.php (white - form page)

### Program Management  
- programs.php (green - main overview)
- assign_programs.php (white - management interface)
- view_program.php (white - detail page)
- edit_program.php (white - form page)

### Settings & System
- system_settings.php (green - main settings)
- audit_log.php (white - data view)
- reporting_periods.php / manage_periods.php (white - management)

### Reports
- generate_reports.php (green - main reports page)

### Outcomes (remaining)
- create_outcome.php (white - form)
- edit_outcome.php (white - form)
- view_outcome.php (white - detail)
- outcome_history.php (white - data view)

### Metrics
- manage_metrics.php (white - management interface)

---

**Implementation Approach**: 
1. Systematic file-by-file update
2. Maintain consistent header patterns
3. Preserve existing functionality
4. Test each update before proceeding
