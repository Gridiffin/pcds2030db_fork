# Audit Log System Documentation

## Overview
The PCDS 2030 Dashboard includes a comprehensive audit logging system that tracks all user activities and system events for security, compliance, and troubleshooting purposes.

## Audit Event Types

### Authentication Events
- **login_success** - Successful user login
- **login_failure** - Failed login attempt
- **logout** - User logout
- **session_expired** - Session timeout

### User Management Events
- **user_created** - New user account created
- **user_updated** - User account modified
- **user_deleted** - User account deleted
- **user_status_changed** - User status (active/inactive) changed
- **user_role_changed** - User role/permissions modified

### Program Management Events
- **program_created** - New program created
- **program_updated** - Program details modified
- **program_deleted** - Program removed
- **program_status_changed** - Program status changed

### Outcome Management Events
- **outcome_created** - New outcome record created
- **outcome_updated** - Outcome record modified
- **outcome_deleted** - Outcome record removed
- **outcome_submitted** - Outcome submitted for review
- **outcome_approved** - Outcome approved by admin

### Period Management Events
- **period_created** - New reporting period created
- **period_updated** - Period details modified
- **period_deleted** - Period removed
- **period_status_changed** - Period status (active/inactive) changed

### Data Export/Import Events
- **data_exported** - Data exported (CSV, PDF, PowerPoint)
- **data_imported** - Data imported into system
- **report_generated** - Report generated
- **report_downloaded** - Report downloaded

### System Configuration Events
- **settings_updated** - System settings modified
- **multi_sector_toggled** - Multi-sector mode enabled/disabled
- **outcome_settings_changed** - Outcome creation settings modified

### Security Events
- **unauthorized_access** - Attempted access to restricted resource
- **permission_denied** - Action denied due to insufficient permissions
- **suspicious_activity** - Potentially malicious activity detected

## Usage Guidelines

### For Developers

#### Basic Logging
```php
// Include the audit log library
require_once 'app/lib/audit_log.php';

// Log a successful action
log_audit_action('program_created', 'Program ID: 123, Name: Forest Conservation');

// Log a failed action
log_audit_action('login_failure', 'Invalid credentials for user: john@example.com', 'failure');
```

#### Advanced Logging with Helper Functions
```php
// Login attempts
log_login_attempt('john@example.com', true);  // successful
log_login_attempt('hacker@evil.com', false); // failed

// User logout
log_user_logout();

// Data operations
log_data_operation('create', 'program', 123, 'New forest conservation program created');

// Exports
log_export_action('csv', 'programs', 'Programs exported to CSV');
```

#### Error Handling
```php
// Always check if logging was successful for critical operations
if (!log_audit_action('critical_operation', $details)) {
    error_log("Critical: Failed to log audit action for critical_operation");
}
```

### For Administrators

#### Viewing Audit Logs
1. Navigate to **Admin Panel** → **Audit Logs**
2. Use filters to narrow down results:
   - Date range
   - User
   - Action type
   - Status (success/failure)
3. Click on any log entry to view detailed information

#### Exporting Audit Logs
1. Set desired filters
2. Click **Export** button
3. Choose format (CSV recommended for analysis)
4. Download will start automatically

#### Monitoring Security Events
- Regularly review failed login attempts
- Monitor unauthorized access attempts
- Watch for suspicious patterns in user activity
- Check for unusual data export activities

## Security Considerations

### Data Protection
- **Passwords are NEVER logged** - Only login attempts are recorded
- **Sensitive personal data is sanitized** before logging
- **IP addresses are captured** for security analysis
- **Session information is tracked** for accountability

### Access Control
- Only administrators can view audit logs
- Audit logs cannot be modified or deleted by regular users
- All audit log access is itself logged
- Export functionality requires admin privileges

### Data Retention
- Audit logs are retained for compliance requirements
- Automatic archiving after 2 years (configurable)
- Critical security events are preserved indefinitely

## Performance Considerations

### Database Optimization
- Indexes on frequently queried columns (`created_at`, `user_id`, `action`)
- Partitioning by date for large datasets
- Regular cleanup of old logs based on retention policy

### Logging Best Practices
- Batch logging for high-volume operations
- Avoid logging in tight loops
- Use appropriate detail levels (don't over-log)
- Monitor audit table size regularly

## Troubleshooting

### Common Issues

#### Logging Failures
```php
// Check if database connection is available
if (!$conn) {
    error_log("Audit logging failed: No database connection");
}

// Verify audit_logs table exists
// Check error logs for SQL errors
```

#### Performance Issues
- Review audit table size: `SELECT COUNT(*) FROM audit_logs`
- Check for missing indexes
- Consider archiving old logs
- Monitor slow query logs

#### Missing Logs
- Verify audit logging is enabled in all relevant functions
- Check for PHP errors that might prevent logging
- Ensure proper permissions on log files

### Database Maintenance
```sql
-- Check audit table size
SELECT 
    COUNT(*) as total_logs,
    MIN(created_at) as oldest_log,
    MAX(created_at) as newest_log
FROM audit_logs;

-- Archive old logs (example for logs older than 2 years)
CREATE TABLE audit_logs_archive AS 
SELECT * FROM audit_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);

-- Clean up after archiving
DELETE FROM audit_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR);
```

## Compliance and Reporting

### Audit Reports
The system provides several pre-built audit reports:
- User activity summary
- Failed login attempts
- Data access patterns
- System configuration changes
- Export/download activities

### Compliance Features
- Immutable audit trail
- Comprehensive activity tracking
- Detailed timestamps with timezone information
- User attribution for all actions
- Export capabilities for compliance officers

## Integration Examples

### Adding Audit Logging to New Features
```php
// Example: Adding audit logging to a new feature
function create_new_feature($data) {
    try {
        // Perform the operation
        $result = perform_operation($data);
        
        // Log successful operation
        log_audit_action(
            'feature_created',
            'Feature ID: ' . $result['id'] . ', Type: ' . $data['type'],
            'success'
        );
        
        return $result;
    } catch (Exception $e) {
        // Log failed operation
        log_audit_action(
            'feature_creation_failed',
            'Error: ' . $e->getMessage(),
            'failure'
        );
        
        throw $e;
    }
}
```

### Custom Audit Helpers
```php
// Create custom helpers for specific domains
function log_financial_transaction($transaction_id, $amount, $type) {
    $details = sprintf(
        'Transaction ID: %s, Amount: %.2f, Type: %s',
        $transaction_id,
        $amount,
        $type
    );
    
    return log_audit_action('financial_transaction', $details);
}
```

## Maintenance Schedule

### Daily
- Monitor failed login attempts
- Review security-related audit events
- Check system performance metrics

### Weekly
- Generate user activity reports
- Review data export activities
- Analyze system usage patterns

### Monthly
- Archive old audit logs
- Update documentation if needed
- Review and update retention policies

### Quarterly
- Performance optimization review
- Security audit of logging system
- Compliance reporting
