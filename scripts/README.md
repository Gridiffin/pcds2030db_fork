# Audit Log System Scripts

This directory contains maintenance and testing scripts for the PCDS 2030 Dashboard audit logging system.

## Available Scripts

### 1. `audit_log_maintenance.php`
Handles archiving old audit logs and database maintenance.

**Usage:**
```bash
# Show audit log statistics
php audit_log_maintenance.php --stats

# Archive logs older than 2 years
php audit_log_maintenance.php --archive

# Clean up archived logs older than 5 years
php audit_log_maintenance.php --cleanup

# Show help
php audit_log_maintenance.php --help
```

**Features:**
- Archives old logs to separate table
- Optimizes database tables
- Provides detailed statistics
- Batch processing for large datasets
- Transaction safety

### 2. `audit_performance_test.php`
Tests the performance impact of audit logging operations.

**Usage:**
```bash
php audit_performance_test.php
```

**Tests:**
- Audit logging performance benchmarks
- Database query performance
- Index analysis
- Table statistics
- Memory usage analysis
- Concurrent logging simulation

### 3. `audit_test_suite.php`
Comprehensive test suite for all audit logging functionality.

**Usage:**
```bash
php audit_test_suite.php
```

**Test Coverage:**
- Basic audit logging functionality
- Helper function testing
- Data retrieval testing
- Database integrity checks
- Error scenario handling
- Integration testing
- Special character and large data handling

### 4. `audit_management.bat` (Windows)
Convenient batch script for running maintenance tasks.

**Usage:**
```cmd
# Show menu
audit_management.bat

# Run specific command
audit_management.bat stats
audit_management.bat test
audit_management.bat performance
audit_management.bat archive
audit_management.bat cleanup
```

## Recommended Maintenance Schedule

### Weekly
```bash
# Check system health
php audit_log_maintenance.php --stats
```

### Monthly
```bash
# Run comprehensive tests
php audit_test_suite.php

# Check performance
php audit_performance_test.php
```

### Quarterly
```bash
# Archive old logs
php audit_log_maintenance.php --archive
```

### Annually
```bash
# Clean up very old archived logs
php audit_log_maintenance.php --cleanup
```

## Performance Monitoring

Monitor these key metrics:
- **Table size**: Should not exceed 500MB without archiving
- **Query performance**: Most queries should complete under 100ms
- **Index usage**: Ensure all recommended indexes are present
- **Memory usage**: Check for memory leaks in high-volume scenarios

## Troubleshooting

### Common Issues

1. **Script fails with database error**
   - Check database connection settings
   - Verify user has necessary permissions
   - Ensure audit_logs table exists

2. **Performance tests show slow queries**
   - Run index analysis
   - Consider archiving old data
   - Review query optimization

3. **Archive process fails**
   - Check available disk space
   - Verify database permissions
   - Monitor memory usage during large batches

4. **Test suite failures**
   - Check error messages for specific issues
   - Verify database schema is up to date
   - Ensure proper permissions are set

### Emergency Procedures

**If audit table becomes too large:**
```bash
# Immediate archiving
php audit_log_maintenance.php --archive

# Check results
php audit_log_maintenance.php --stats
```

**If performance severely degrades:**
```bash
# Run performance analysis
php audit_performance_test.php

# Check for missing indexes
# Follow recommendations in output
```

**Before major system changes:**
```bash
# Full system test
php audit_test_suite.php

# Performance baseline
php audit_performance_test.php
```

## Script Requirements

- PHP 7.4 or higher
- MySQL/MariaDB access
- Sufficient memory for batch operations
- Write permissions for log files

## Configuration

Scripts use the same database configuration as the main application:
- Database settings from `app/config/config.php`
- Connection established via `app/lib/db_connect.php`

## Security Notes

- Scripts should only be run by authorized administrators
- Archive files contain sensitive audit data
- Ensure proper file permissions on script directory
- Never run cleanup operations without backup verification

## Support

For issues with these scripts:
1. Check the error logs
2. Verify database connectivity
3. Ensure proper permissions
4. Consult the main audit system documentation
