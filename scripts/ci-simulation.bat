@echo off
REM Local GitHub Actions Simulation Script for Windows
REM This script runs the same commands that GitHub Actions will execute

echo 🚀 Starting Local CI Simulation...
echo ==================================

REM Check if we're in the right directory
if not exist "package.json" (
    echo ❌ Error: Please run this script from the project root directory
    exit /b 1
)

if not exist "composer.json" (
    echo ❌ Error: Please run this script from the project root directory  
    exit /b 1
)

echo.
echo 📦 Installing NPM dependencies...
call npm ci
if %errorlevel% neq 0 (
    echo ❌ NPM installation failed
    exit /b 1
)

echo.
echo 📦 Installing Composer dependencies...
call composer install --prefer-dist --no-progress --optimize-autoloader
if %errorlevel% neq 0 (
    echo ❌ Composer installation failed
    exit /b 1
)

echo.
echo 🧪 Running PHPUnit backend tests...
call vendor\bin\phpunit --testdox
set php_exit_code=%errorlevel%

echo.
echo 🧪 Running Jest frontend tests...
call npm test -- --passWithNoTests
set jest_exit_code=%errorlevel%

echo.
echo 🏗️ Building production assets...
call npm run build
set build_exit_code=%errorlevel%

echo.
echo 🔍 Running code quality checks...

echo Checking PHP syntax...
set php_syntax_exit_code=0
for /r app\ %%f in (*.php) do (
    php -l "%%f" >nul 2>&1
    if !errorlevel! neq 0 set php_syntax_exit_code=1
)

echo Running NPM security audit...
call npm audit --audit-level=moderate >nul 2>&1
set audit_exit_code=%errorlevel%

echo.
echo 📊 CI Simulation Results:
echo ========================

if %php_exit_code% equ 0 (
    echo ✅ PHPUnit Tests: PASSED
) else (
    echo ❌ PHPUnit Tests: FAILED
)

if %jest_exit_code% equ 0 (
    echo ✅ Jest Tests: PASSED
) else (
    echo ❌ Jest Tests: FAILED
)

if %build_exit_code% equ 0 (
    echo ✅ Asset Build: PASSED
) else (
    echo ❌ Asset Build: FAILED
)

if %php_syntax_exit_code% equ 0 (
    echo ✅ PHP Syntax: PASSED
) else (
    echo ❌ PHP Syntax: FAILED
)

if %audit_exit_code% equ 0 (
    echo ✅ Security Audit: PASSED
) else (
    echo ⚠️ Security Audit: WARNINGS (check manually)
)

REM Calculate overall result
set /a overall_exit_code=%php_exit_code% + %build_exit_code% + %php_syntax_exit_code%

echo.
if %overall_exit_code% equ 0 (
    echo 🎉 All critical checks PASSED! Ready for deployment.
    exit /b 0
) else (
    echo ❌ Some critical checks FAILED. Please fix before deploying.
    exit /b 1
)
