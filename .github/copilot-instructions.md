# PCDS2030 Dashboard Copilot Instructions

## Project Overview
Production forestry sector management system for Sarawak Ministry of Natural Resources serving three agencies: **STIDC** (agency_id=1), **SFC** (agency_id=2), and **FDS** (agency_id=3). Replaces Excel-based quarterly reporting with automated web dashboard and PowerPoint generation using PHP, MySQL, Bootstrap 5, and Chart.js.

## Critical Architecture Patterns

### Multi-Level Permission System
**Understand this hierarchy to avoid breaking access control:**
- **admin**: Full cross-agency access (`$_SESSION['role'] === 'admin'`)
- **focal**: Cross-agency oversight (`$_SESSION['role'] === 'focal'`)  
- **agency**: Single agency access (`$_SESSION['role'] === 'agency'`)

**CRITICAL BUG PREVENTION:** Always use `$_SESSION['agency_id']` (not `user_id`) for agency operations. The session mismatch (`user_id` ≠ `agency_id`) has caused infinite loading bugs throughout the codebase.

```php
// ❌ Wrong - causes infinite loading
$agency_id = $_SESSION['user_id'];

// ✅ Correct - use agency_id for database queries  
$agency_id = $_SESSION['agency_id'] ?? null;
```

### Database Permission Tables
- `program_agency_assignments`: Agency-level roles (owner/editor/viewer)
- `program_user_assignments`: User-level restrictions within agencies
- **Avoid circular dependencies** between `can_edit_program()` and `can_edit_program_with_user_restrictions()`

**Permission Functions (Recursion-Protected):**
```php
get_user_program_role($program_id, $agency_id)  // Base permission check
can_edit_program_agency_level($program_id)      // Agency-only (no user restrictions)  
can_edit_program($program_id)                   // Full permission check with user restrictions
```

### Period-Based Data Architecture
All program data is tied to `reporting_periods`:
- Programs → Submissions → Targets (by period)
- Status tracking: `is_draft=1` vs `is_submitted=1`
- Historical data access via `period_id` parameter

```php
// Get current open period
$current_period = get_current_period();

// Period-based queries
"SELECT * FROM program_submissions WHERE period_id = ? AND program_id = ?"
```

## Essential File Structure

```
app/lib/
├── agencies/
│   ├── core.php              # is_agency(), get_agency_id() - session management
│   ├── program_agency_assignments.php  # Permission system (recursion-protected)
│   └── program_user_assignments.php    # User-level restrictions
├── functions.php             # validate_login(), auto_manage_periods()
├── session.php              # is_logged_in(), user session validation
└── audit_log.php           # Comprehensive audit system

app/views/agency/
├── dashboard/dashboard.php   # Main agency dashboard with Chart.js
├── programs/                # Program CRUD operations
└── outcomes/               # Flexible JSON data management

assets/js/agency/
├── view_programs.js         # Pagination with infinite scroll protection
└── program_details.js      # Dynamic target management
```

## Database Schema Essentials

**Core Tables:**
- `users`: role enum('admin','agency','focal'), **agency_id** foreign key  
- `programs`: Linked to initiatives, agency ownership with soft delete
- `program_submissions`: Period-based data entry (draft/submitted states)
- `program_targets`: Dynamic target management within submissions  
- `outcomes`: JSON-based flexible data storage (graph/kpi types)
- `audit_logs` + `audit_field_changes`: Complete activity tracking with IP logging

**JSON Content Storage Pattern:**
```json
// program_submissions.content_json
{
  "target": "Export 50,000 m³ timber",
  "achievement": "38,000 m³ exported", 
  "status_text": "On track for Q4 target",
  "custom_metrics": {"volume": 38000, "value": 1500000}
}
```

**Key Relationships:**
```sql
programs.agency_id → agency.agency_id
program_submissions.period_id → reporting_periods.period_id  
program_agency_assignments: Many-to-many programs ↔ agencies with roles
```

## Development Workflows

### Adding New Features
1. **Always create** `.github/implementations/feature_name.md` first
2. **Scan for related code** using `grep_search` before coding
3. **Check permissions** - does the feature need agency/focal/admin access?
4. **Use DBCode extension** for database operations
5. **Update audit logging** for any data changes

### Debugging Session Issues
Common problem: `$_SESSION['user_id']` used instead of `$_SESSION['agency_id']`
```php
// ❌ Wrong - causes infinite loading
$agency_id = $_SESSION['user_id'];

// ✅ Correct - use agency_id for database queries  
$agency_id = $_SESSION['agency_id'] ?? null;
```

### Working with Periods
```php
// Get current open period
$current_period = get_current_period();

// Period-based queries
"SELECT * FROM program_submissions WHERE period_id = ? AND program_id = ?"
```

## Project-Specific Conventions

### Permission Functions (Recursion-Protected)
```php
get_user_program_role($program_id, $agency_id)  // Base permission check
can_edit_program_agency_level($program_id)      // Agency-only (no user restrictions)  
can_edit_program($program_id)                   // Full permission check
```

### AJAX Endpoints Pattern
```
app/ajax/                    # Shared utilities
app/views/agency/ajax/       # Agency-specific endpoints  
```

### CSS Architecture
- **Base styles**: `assets/css/main.css` (centralized imports)
- **Component styles**: Import into main.css, never link directly
- **Bootstrap 5** foundation with custom Chart.js themes

### JSON Data Patterns
Outcomes use flexible JSON storage:
```json
{
  "rows": [{"month": "January", "2024": 1000, "2025": 1200}],
  "columns": ["2024", "2025"] 
}
```

## Integration Points

### cPanel Hosting Constraints
- **No shell access** - scripts in `/scripts/` for maintenance
- **File permissions** - uploads to `/uploads/programs/`
- **Path resolution** - Use `PROJECT_ROOT_PATH` constant

### Chart.js Integration
Dashboard uses Chart.js with period filtering:
```javascript
loadChartData(period_id);  // AJAX refresh without page reload
```

### Audit System
**Every data change must log:**
```php
log_audit('update_program', $details, $program_id);
log_field_changes($old_data, $new_data, $audit_log_id);
```

## Common Pitfalls

1. **Session Mismatches**: Using `user_id` for agency operations
2. **Circular Dependencies**: Permission functions calling each other
3. **Missing Audit Logs**: Data changes without proper logging  
4. **Period Context**: Forgetting period_id in queries
5. **File Paths**: Relative paths breaking in different contexts

## Testing Strategy
- **Simple test files**: Create `debug_*.php` for isolated testing
- **Session verification**: Check `verify_agency_fix.php` pattern
- **Permission testing**: Test with different user roles
- **Clean up**: Remove debug files after verification
