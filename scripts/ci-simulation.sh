#!/bin/bash

# Local GitHub Actions Simulation Script
# This script runs the same commands that GitHub Actions will execute

echo "🚀 Starting Local CI Simulation..."
echo "=================================="

# Check if we're in the right directory
if [ ! -f "package.json" ] || [ ! -f "composer.json" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

echo ""
echo "📦 Installing NPM dependencies..."
npm ci
if [ $? -ne 0 ]; then
    echo "❌ NPM installation failed"
    exit 1
fi

echo ""
echo "📦 Installing Composer dependencies..." 
composer install --prefer-dist --no-progress --optimize-autoloader
if [ $? -ne 0 ]; then
    echo "❌ Composer installation failed"
    exit 1
fi

echo ""
echo "🧪 Running PHPUnit backend tests..."
vendor/bin/phpunit --testdox
php_exit_code=$?

echo ""
echo "🧪 Running Jest frontend tests..."
npm test -- --passWithNoTests
jest_exit_code=$?

echo ""
echo "🏗️ Building production assets..."
npm run build
build_exit_code=$?

echo ""
echo "🔍 Running code quality checks..."

# PHP syntax check
echo "Checking PHP syntax..."
find app/ -name "*.php" -exec php -l {} \; > /dev/null 2>&1
php_syntax_exit_code=$?

# NPM security audit
echo "Running NPM security audit..."
npm audit --audit-level=moderate > /dev/null 2>&1
audit_exit_code=$?

echo ""
echo "📊 CI Simulation Results:"
echo "========================"

if [ $php_exit_code -eq 0 ]; then
    echo "✅ PHPUnit Tests: PASSED"
else
    echo "❌ PHPUnit Tests: FAILED"
fi

if [ $jest_exit_code -eq 0 ]; then
    echo "✅ Jest Tests: PASSED"
else
    echo "❌ Jest Tests: FAILED"
fi

if [ $build_exit_code -eq 0 ]; then
    echo "✅ Asset Build: PASSED"
else
    echo "❌ Asset Build: FAILED"
fi

if [ $php_syntax_exit_code -eq 0 ]; then
    echo "✅ PHP Syntax: PASSED"
else
    echo "❌ PHP Syntax: FAILED"
fi

if [ $audit_exit_code -eq 0 ]; then
    echo "✅ Security Audit: PASSED"
else
    echo "⚠️ Security Audit: WARNINGS (check manually)"
fi

# Overall result
overall_exit_code=$((php_exit_code + build_exit_code + php_syntax_exit_code))

echo ""
if [ $overall_exit_code -eq 0 ]; then
    echo "🎉 All critical checks PASSED! Ready for deployment."
    exit 0
else
    echo "❌ Some critical checks FAILED. Please fix before deploying."
    exit 1
fi
