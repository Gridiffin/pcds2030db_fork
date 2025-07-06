# System Settings Analysis

## Overview
Analysis of the system settings file (`app/views/admin/settings/system_settings.php`) against the latest database schema to identify compatibility issues and potential improvements.

## Current Implementation Analysis

### ✅ What's Working Well

1. **Settings Storage Method**: The system uses PHP constants in `config.php` rather than database tables, which is actually a good approach for:
   - Performance (no database queries needed)
   - Simplicity (direct file access)
   - Version control friendly
   - Easy deployment

2. **Settings Managed**:
   - `MULTI_SECTOR_ENABLED` - Controls multi-sector vs forestry-only mode
   - `ALLOW_OUTCOME_CREATION` - Controls whether new outcomes can be created

3. **Security**: Proper admin authentication checks before allowing settings changes

4. **Audit Logging**: All settings changes are properly logged in the audit system

### 🔍 Database Schema Compatibility

#### Current Database Tables (from `database_schema.php`):
- `agency`
- `audit_logs` 
- `initiatives`
- `notifications`
- `outcomes_details`
- `programs`
- `program_attachments`
- `program_outcome_links`
- `program_submissions`
- `reporting_periods`
- `reports`
- `targets`
- `users`

#### Missing Tables:
- **No `system_settings` table exists** - This is actually correct since settings are stored in config.php

### ⚠️ Potential Issues & Recommendations

#### 1. Settings Persistence
- **Issue**: Settings are stored in PHP constants which require file system write access
- **Risk**: May not work in all hosting environments (especially shared hosting)
- **Recommendation**: Consider adding a database-backed settings table as fallback

#### 2. Settings Scope
- **Current**: Only 2 settings managed
- **Missing**: Could benefit from additional system settings like:
  - Email notification settings
  - Report generation settings
  - User session timeout
  - File upload limits
  - Backup settings

#### 3. Settings Validation
- **Current**: Basic boolean validation
- **Missing**: No validation for setting dependencies or business rules

## Recommended Improvements

### 1. Additional Settings to Add

#### File Upload Settings
- **Max File Size**: Currently hardcoded to 10MB in multiple places
- **Allowed File Types**: Currently hardcoded in `program_attachments.php`
- **Max Attachments Per Program**: Currently hardcoded to 10
- **Upload Directory**: Configure upload paths

#### Session & Security Settings
- **Session Timeout**: Currently using PHP default (24 minutes)
- **Login Attempts Limit**: Prevent brute force attacks
- **Password Policy**: Minimum length, complexity requirements
- **Account Lockout Duration**: After failed login attempts

#### Report Generation Settings
- **Default Report Format**: PDF/PPTX preference
- **Report Retention Period**: How long to keep old reports
- **Auto-Generate Reports**: Enable/disable automatic report generation
- **Report Template Settings**: Default templates and styling

#### Notification Settings
- **Email Notifications**: Enable/disable email alerts
- **Notification Types**: Which events trigger notifications
- **Notification Frequency**: Daily/weekly digest options

#### System Performance Settings
- **Cache Duration**: How long to cache data
- **Pagination Limits**: Default items per page
- **Export Limits**: Maximum records for exports
- **Background Job Settings**: For report generation

#### Audit & Logging Settings
- **Audit Log Retention**: How long to keep audit logs
- **Log Level**: Debug/Info/Warning/Error
- **Sensitive Data Masking**: What to hide in logs

### 2. Enhanced Settings Management
- Add more granular settings
- Implement settings categories
- Add settings validation
- Add settings backup/restore functionality

### 3. Settings API
- Create RESTful API for settings management
- Add settings caching
- Implement settings change notifications

### 4. Database Settings Table (Optional Enhancement)
```sql
CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('boolean','string','integer','json') NOT NULL DEFAULT 'string',
  `description` text,
  `updated_by` int NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`),
  KEY `idx_updated_by` (`updated_by`),
  CONSTRAINT `fk_settings_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```

## Conclusion

The current system settings implementation is **compatible with the database schema** and follows good practices:

✅ **Compatible**: No database schema conflicts
✅ **Secure**: Proper authentication and audit logging
✅ **Functional**: Settings work as intended
✅ **Maintainable**: Clean, well-documented code

The file-based approach is actually appropriate for this use case, but could be enhanced with additional settings and optional database backup.

## Tasks

- [x] Analyze current system settings implementation
- [x] Compare against database schema
- [x] Identify compatibility issues
- [x] Document findings and recommendations
- [x] Identify additional settings that could be added
- [ ] (Optional) Implement database settings table
- [ ] (Optional) Add file upload settings (max size, allowed types, etc.)
- [ ] (Optional) Add session & security settings (timeout, login attempts, etc.)
- [ ] (Optional) Add report generation settings (format, retention, etc.)
- [ ] (Optional) Add notification settings (email, frequency, etc.)
- [ ] (Optional) Add system performance settings (cache, pagination, etc.)
- [ ] (Optional) Add audit & logging settings (retention, log level, etc.)
- [ ] (Optional) Enhance settings validation and dependencies 