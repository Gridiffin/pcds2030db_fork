@echo off
REM Audit Log System Management Script
REM This batch file provides easy access to audit log maintenance tasks

echo =====================================
echo   PCDS 2030 Audit Log Management
echo =====================================
echo.

if "%1"=="" goto menu
if "%1"=="stats" goto stats
if "%1"=="test" goto test
if "%1"=="performance" goto performance
if "%1"=="archive" goto archive
if "%1"=="cleanup" goto cleanup
goto usage

:menu
echo Available commands:
echo.
echo 1. stats      - Show audit log statistics
echo 2. test       - Run comprehensive test suite
echo 3. performance - Run performance tests
echo 4. archive    - Archive old audit logs
echo 5. cleanup    - Clean up very old archived logs
echo.
echo Usage: audit_management.bat [command]
echo Example: audit_management.bat stats
echo.
goto end

:stats
echo Running audit log statistics...
php scripts\audit_log_maintenance.php --stats
goto end

:test
echo Running comprehensive test suite...
php scripts\audit_test_suite.php
goto end

:performance
echo Running performance tests...
php scripts\audit_performance_test.php
goto end

:archive
echo Running log archiving...
php scripts\audit_log_maintenance.php --archive
goto end

:cleanup
echo Running cleanup of old archived logs...
php scripts\audit_log_maintenance.php --cleanup
goto end

:usage
echo Invalid command: %1
echo.
echo Available commands: stats, test, performance, archive, cleanup
echo Usage: audit_management.bat [command]
goto end

:end
pause
