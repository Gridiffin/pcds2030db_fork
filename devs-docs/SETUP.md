# Windows Development Setup Guide

Complete step-by-step guide for setting up the PCDS2030 Dashboard development environment on Windows.

## 📋 Table of Contents
1. [System Requirements](#system-requirements)
2. [Step 1: Install Prerequisites](#step-1-install-prerequisites)
3. [Step 2: Clone Repository](#step-2-clone-repository)
4. [Step 3: Database Setup](#step-3-database-setup)
5. [Step 4: Environment Configuration](#step-4-environment-configuration)
6. [Step 5: Install Dependencies](#step-5-install-dependencies)
7. [Step 6: Build Assets](#step-6-build-assets)
8. [Step 7: Verify Installation](#step-7-verify-installation)
9. [Common Issues & Solutions](#common-issues--solutions)

## System Requirements

### Minimum Requirements
- **OS**: Windows 10 (Build 1903+) or Windows 11
- **RAM**: 8GB minimum, 16GB recommended
- **Storage**: 2GB free space for development tools + project
- **Internet**: Required for downloading dependencies

### Required Software Versions
- **PHP**: 8.1 or 8.2 (with extensions: mysqli, pdo_mysql, json, curl)
- **MySQL**: 8.0+
- **Node.js**: 18.x or 20.x LTS
- **Apache**: 2.4+ (included with Laragon/XAMPP)

## Step 1: Install Prerequisites

### 1.1 Install Laragon (Recommended)

**Why Laragon?** Optimized for Windows, lightweight, and includes all required components.

1. **Download Laragon Full**
   - Visit: https://laragon.org/download/index.html
   - Download "Laragon - Full" (includes PHP 8.2, MySQL 8.0, Apache 2.4)
   - File size: ~180MB

2. **Install Laragon**
   ```
   - Run laragon-wamp.exe as Administrator
   - Install to default path: C:\laragon
   - Complete installation and start Laragon
   - Verify services are running (Apache, MySQL)
   ```

3. **Configure PHP**
   ```
   - Right-click Laragon tray icon
   - Go to PHP > Version > Select PHP 8.1 or 8.2
   - Go to PHP > Extensions > Enable: mysqli, pdo_mysql, curl
   - Restart services
   ```

### 1.2 Alternative: XAMPP Installation

If you prefer XAMPP:

1. **Download XAMPP**
   - Visit: https://www.apachefriends.org/download.html
   - Download XAMPP for Windows (PHP 8.1 or 8.2)

2. **Install XAMPP**
   ```
   - Run installer as Administrator
   - Install to C:\xampp
   - Start Apache and MySQL services
   - Access phpMyAdmin at http://localhost/phpmyadmin
   ```

### 1.3 Install Node.js

1. **Download Node.js LTS**
   - Visit: https://nodejs.org/en/download/
   - Download "Windows Installer (.msi)" - LTS version
   - Choose 64-bit for modern systems

2. **Install Node.js**
   ```
   - Run the .msi installer as Administrator
   - Follow installation wizard
   - Ensure "Add to PATH" is checked
   - Restart command prompt/PowerShell
   ```

3. **Verify Installation**
   ```cmd
   node --version    # Should show v18.x.x or v20.x.x
   npm --version     # Should show compatible npm version
   ```

### 1.4 Install Git for Windows

1. **Download Git**
   - Visit: https://git-scm.com/download/win
   - Download latest version

2. **Install Git**
   ```
   - Run installer as Administrator
   - Use default settings
   - Select "Use Git from the Windows Command Prompt"
   - Select "Checkout Windows-style, commit Unix-style line endings"
   ```

## Step 2: Clone Repository

### 2.1 Choose Installation Directory

**For Laragon users:**
```cmd
cd C:\laragon\www
```

**For XAMPP users:**
```cmd
cd C:\xampp\htdocs
```

### 2.2 Clone the Repository

```cmd
# Clone the repository
git clone https://github.com/Gridiffin/pcds2030db_fork.git

# Navigate to project directory
cd pcds2030_dashboard_fork

# Verify files are present
dir
```

**Expected Output:**
```
Directory should contain: app/, assets/, docs/, tests/, package.json, composer.json, index.php
```

## Step 3: Database Setup

### 3.1 Create Database

**Using phpMyAdmin (Recommended):**

1. **Access phpMyAdmin**
   - **Laragon**: http://localhost/phpmyadmin OR access HeidiSQL from app tray in your taskbar
   - **XAMPP**: http://localhost/phpmyadmin

2. **Create Database**
   ```sql
   -- Click "New" in left sidebar
   -- Database name: pcds2030_db
   -- Collation: utf8mb4_general_ci
   -- Click "Create"
   ```

### 3.2 Import Database Schema

1. **Import SQL File**
   ```
   - In phpMyAdmin, select "pcds2030_db"
   - Click "Import" tab
   - Click "Choose File"
   - Select: app/database/currentpcds2030db.sql
   - Click "Go"
   ```

2. **Verify Import**
   ```sql
   -- Should see tables like:
   - agency_groups
   - programs
   - program_submissions
   - reporting_periods
   - users
   - outcomes_data
   - audit_logs
   ```

### 3.3 Alternative: Command Line Import

```cmd
# Navigate to project directory
cd C:\laragon\www\pcds2030_dashboard_fork

# Import database (adjust path for XAMPP)
C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe -u root -p pcds2030_db < app/database/currentpcds2030db.sql
```

## Step 4: Environment Configuration

### 4.1 Configure Database Connection

The system uses dynamic configuration based on your environment. For local development, edit:

```php
// File: app/config/config.php
// Local development settings (around line 25-30):

define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Default for Laragon/XAMPP
define('DB_PASS', '');               // Empty for default setup
define('DB_NAME', 'pcds2030_db');
```

### 4.2 Configure Web Server

**For Laragon:**
- Project automatically available at: `http://pcds2030-dashboard-fork.test`
- Or: `http://localhost/pcds2030_dashboard_fork`

**For XAMPP:**
- Access via: `http://localhost/pcds2030_dashboard_fork`

### 4.3 Set PHP Configuration

Create or edit `.htaccess` in project root:
```apache
# Enable rewrite engine
RewriteEngine On

# PHP settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
```

## Step 5: Install Dependencies

### 5.1 Install PHP Dependencies

```cmd
# Navigate to project directory
cd C:\laragon\www\pcds2030_dashboard_fork

# Install Composer (if not installed)
# Download from: https://getcomposer.org/download/

# Install PHP dependencies
composer install
```

**Expected Output:**
```
Installing dependencies from lock file
Verifying lock file contents can be installed on this platform.
Package operations: X installs, 0 updates, 0 removals
```

### 5.2 Install Node.js Dependencies

```cmd
# Install Node.js packages
npm install
```

**Expected Output:**
```
added XXX packages, and audited XXX packages in XXs
```

**If you encounter errors:**
```cmd
# Clear npm cache and retry
npm cache clean --force
npm install
```

## Step 6: Build Assets

### 6.1 Build Development Assets

```cmd
# Build assets for development
npm run dev
```

### 6.2 Build Production Assets

```cmd
# Build optimized production assets
npm run build
```

**Expected Output:**
```
vite v7.x.x building for production...
✓ built in XXXms
```

### 6.3 Verify Asset Generation

Check that these directories contain files:
```
assets/css/   - Compiled CSS files
assets/js/    - Compiled JavaScript files
```

## Step 7: Verify Installation

### 7.1 Test Database Connection

Create a test file: `test_connection.php`
```php
<?php
// Test database connection
require_once 'app/config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    echo "✅ Database connection successful!<br>";
    
    // Test table existence
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Found " . count($tables) . " database tables<br>";
    
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
```

### 7.2 Access Application

1. **Open Browser**
   - **Laragon**: http://pcds2030-dashboard-fork.test
   - **XAMPP**: http://localhost/pcds2030_dashboard_fork

2. **Expected Result**
   - Login page should display
   - No PHP errors visible
   - CSS and JavaScript should load correctly

### 7.3 Test Login (Optional)

Default test credentials (if seeded):
```
Username: admin@example.com
Password: [check database users table]
```

### 7.4 Run Tests

```cmd
# Run JavaScript tests
npm test

# Run PHP tests
vendor/bin/phpunit

# Expected: All tests should pass
```

## Common Issues & Solutions

### Issue 1: Port Conflicts

**Problem**: Apache won't start (Port 80 in use)

**Solution**:
```
1. Stop IIS if running: net stop iisadmin
2. Check port usage: netstat -an | find "80"
3. Change Apache port in httpd.conf: Listen 8080
4. Access via: http://localhost:8080/pcds2030_dashboard_fork
```

### Issue 2: MySQL Won't Start

**Problem**: MySQL service fails to start

**Solution**:
```
1. Check Windows Services for existing MySQL
2. Stop conflicting MySQL services
3. In Laragon: Right-click > MySQL > Start
4. Check error logs in C:\laragon\bin\mysql\data\
```

### Issue 3: PHP Extension Missing

**Problem**: mysqli or pdo_mysql not found

**Solution**:
```
1. Edit php.ini (Laragon: C:\laragon\bin\php\php-8.x\php.ini)
2. Uncomment these lines:
   extension=mysqli
   extension=pdo_mysql
3. Restart Apache
```

### Issue 4: Composer Not Found

**Problem**: 'composer' is not recognized

**Solution**:
```
1. Download Composer: https://getcomposer.org/download/
2. Run Composer-Setup.exe as Administrator
3. Restart command prompt
4. Verify: composer --version
```

### Issue 5: Node.js Path Issues

**Problem**: 'npm' is not recognized

**Solution**:
```
1. Reinstall Node.js with "Add to PATH" checked
2. Or manually add to PATH:
   - C:\Program Files\nodejs\
3. Restart command prompt
4. Verify: node --version
```

### Issue 6: Vite Build Errors

**Problem**: npm run build fails

**Solution**:
```cmd
# Clear node modules and reinstall
rmdir /s node_modules
del package-lock.json
npm install
npm run build
```

### Issue 7: Database Import Fails

**Problem**: SQL import errors

**Solution**:
```
1. Check MySQL max_allowed_packet:
   SET GLOBAL max_allowed_packet=100*1024*1024;
2. Import in smaller chunks
3. Check for character encoding issues
4. Use command line import instead of phpMyAdmin
```

### Issue 8: Permission Errors

**Problem**: File/folder permission denied

**Solution**:
```
1. Run command prompt as Administrator
2. Set folder permissions:
   icacls "C:\laragon\www\pcds2030_dashboard_fork" /grant Users:F /T
3. Or move project to user directory
```

## 🎯 Next Steps

After successful setup:

1. **Read Architecture Documentation**: [ARCHITECTURE.md](ARCHITECTURE.md)
2. **Understand File Structure**: [FILE-STRUCTURE.md](FILE-STRUCTURE.md)
3. **Review API Documentation**: [API.md](API.md)
4. **Set up Testing Environment**: [TESTING.md](TESTING.md)

## 🆘 Getting Help

If you encounter issues not covered here:

1. **Check existing documentation** in the `docs/` folder
2. **Review system logs** in Laragon/XAMPP error logs
3. **Verify all prerequisites** are properly installed
4. **Test each component individually** (PHP, MySQL, Node.js)

---

**Environment Verified ✅**: Your development environment should now be ready for PCDS2030 Dashboard development!