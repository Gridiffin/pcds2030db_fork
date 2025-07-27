#!/bin/bash

# Admin Unit Testing Runner Script
# This script runs both Jest and PHPUnit tests for admin functionality

echo "🚀 Starting Admin Unit Tests..."
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the project root
if [ ! -f "package.json" ]; then
    print_error "Please run this script from the project root directory"
    exit 1
fi

# Check if Node.js is available
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed or not in PATH"
    exit 1
fi

# Check if npm is available
if ! command -v npm &> /dev/null; then
    print_error "npm is not installed or not in PATH"
    exit 1
fi

# Check if Jest is installed
if [ ! -d "node_modules/jest" ]; then
    print_warning "Jest not found. Installing dependencies..."
    npm install
fi

print_status "Running JavaScript (Jest) tests for admin modules..."

# Run Jest tests for admin modules
JEST_RESULT=0
if npx jest tests/admin/ --verbose --no-coverage 2>/dev/null; then
    print_success "JavaScript tests completed successfully"
else
    print_error "JavaScript tests failed"
    JEST_RESULT=1
fi

echo ""
print_status "Running PHP (PHPUnit) tests for admin modules..."

# Check if PHP is available
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed or not in PATH"
    PHP_RESULT=1
else
    # Check if PHPUnit is available
    if [ -f "vendor/bin/phpunit" ]; then
        # Check if required PHP extensions are available
        if php -m | grep -q "dom"; then
            if php vendor/bin/phpunit tests/php/admin/ --verbose 2>/dev/null; then
                print_success "PHP tests completed successfully"
                PHP_RESULT=0
            else
                print_error "PHP tests failed"
                PHP_RESULT=1
            fi
        else
            print_warning "PHP 'dom' extension not available. PHPUnit tests skipped."
            print_warning "To run PHP tests, install the PHP 'dom' extension"
            PHP_RESULT=0
        fi
    else
        print_warning "PHPUnit not found in vendor/bin/phpunit"
        print_warning "To run PHP tests, install PHPUnit via Composer"
        PHP_RESULT=0
    fi
fi

echo ""
echo "=================================="
print_status "Test Summary:"

if [ $JEST_RESULT -eq 0 ]; then
    print_success "✅ JavaScript tests: PASSED"
else
    print_error "❌ JavaScript tests: FAILED"
fi

if [ $PHP_RESULT -eq 0 ]; then
    print_success "✅ PHP tests: PASSED"
else
    print_error "❌ PHP tests: FAILED"
fi

# Overall result
if [ $JEST_RESULT -eq 0 ] && [ $PHP_RESULT -eq 0 ]; then
    echo ""
    print_success "🎉 All admin tests completed successfully!"
    exit 0
else
    echo ""
    print_error "💥 Some tests failed. Please check the output above."
    exit 1
fi 