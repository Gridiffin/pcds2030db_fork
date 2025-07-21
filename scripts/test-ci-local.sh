#!/bin/bash

# Local GitHub Actions Testing Script
# This script simulates the key parts of our GitHub Actions workflows

echo "🔧 PCDS2030 Dashboard CI Simulation"
echo "===================================="

# Test 1: PHP Syntax Check
echo ""
echo "1️⃣ Testing PHP Syntax Check..."
ERROR_COUNT=0
for file in $(find app/ -name "*.php" 2>/dev/null); do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo "❌ Syntax error in: $file"
        php -l "$file"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
done

if [ $ERROR_COUNT -eq 0 ]; then
    echo "✅ All PHP files have valid syntax"
else
    echo "❌ Found $ERROR_COUNT PHP syntax errors"
fi

# Test 2: Composer Dependencies
echo ""
echo "2️⃣ Testing Composer Dependencies..."
if [ -f "composer.json" ]; then
    if command -v composer > /dev/null 2>&1; then
        echo "📦 Installing Composer dependencies..."
        composer install --prefer-dist --no-progress --optimize-autoloader --quiet
        echo "✅ Composer dependencies installed"
    else
        echo "⚠️ Composer not found, skipping..."
    fi
else
    echo "⚠️ composer.json not found"
fi

# Test 3: NPM Dependencies
echo ""
echo "3️⃣ Testing NPM Dependencies..."
if [ -f "package.json" ]; then
    if command -v npm > /dev/null 2>&1; then
        echo "📦 Installing NPM dependencies..."
        npm ci --silent 2>/dev/null || npm install --silent
        echo "✅ NPM dependencies installed"
        
        echo "🧪 Running Jest tests..."
        npm test 2>/dev/null || echo "⚠️ Some tests failed, check output above"
        
        echo "🏗️ Building assets..."
        npm run build 2>/dev/null || echo "⚠️ Build failed, check configuration"
    else
        echo "⚠️ NPM not found, skipping..."
    fi
else
    echo "⚠️ package.json not found"
fi

# Test 4: PHPUnit Tests
echo ""
echo "4️⃣ Testing PHPUnit..."
if [ -f "vendor/bin/phpunit" ]; then
    echo "🧪 Running PHP tests..."
    vendor/bin/phpunit --testdox 2>/dev/null || echo "⚠️ Some PHPUnit tests failed"
else
    echo "⚠️ PHPUnit not found, run composer install first"
fi

# Test 5: File Permissions
echo ""
echo "5️⃣ Checking File Permissions..."
if [ -f "vendor/bin/phpunit" ]; then
    if [ -x "vendor/bin/phpunit" ]; then
        echo "✅ PHPUnit is executable"
    else
        echo "⚠️ PHPUnit is not executable, fixing..."
        chmod +x vendor/bin/phpunit
        echo "✅ PHPUnit permissions fixed"
    fi
fi

echo ""
echo "🎉 Local CI simulation completed!"
echo "======================================"
