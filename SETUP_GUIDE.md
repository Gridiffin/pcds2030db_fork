# PCDS 2030 Dashboard - Setup Guide for New Computer

## Quick Setup Instructions

### 1. XAMPP Setup
1. Install XAMPP and start Apache and MySQL services
2. Place the project folder in your XAMPP htdocs directory (e.g., `C:\xampp\htdocs\pcds2030_dashboard`)
3. Access the project via: `http://localhost/pcds2030_dashboard`

### 2. Database Setup
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database called `pcds2030_dashboard`
3. Import the database file: `app/database/pcds2030_dashboard.sql`

### 3. Configuration
The system now automatically detects the correct URL paths, but you can manually verify:
1. Open `app/config/config.php`
2. Check if `APP_URL` is being detected correctly
3. Update database credentials if needed (DB_HOST, DB_USER, DB_PASS)

### 4. Troubleshooting Asset Loading Issues

If CSS and JS files are not loading:

#### Step 1: Access the Debug Page
Visit: `http://localhost/pcds2030_dashboard/debug_assets.php`

This page will show you:
- Current server configuration
- Generated asset URLs
- File existence checks
- HTTP loading tests

#### Step 2: Common Issues and Solutions

**Issue: Assets return 404 errors**
- Solution: Check if the `assets/` folder exists and contains css/, js/, images/ subfolders
- Make sure file permissions allow web server access

**Issue: APP_URL is wrong**
- The system should auto-detect, but if it doesn't work:
- Manually set APP_URL in `app/config/config.php`
- Example: `define('APP_URL', 'http://localhost/pcds2030_dashboard');`

**Issue: Project is in a different folder**
- If your project is in `C:\xampp\htdocs\my-project\`, the URL should be:
- `http://localhost/my-project`

#### Step 3: Verify Directory Structure
Make sure your directory structure looks like this:
```
pcds2030_dashboard/
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── fonts/
├── app/
│   ├── config/
│   ├── lib/
│   └── views/
├── index.php
├── login.php
└── debug_assets.php (temporary)
```

### 5. Testing
1. Visit `http://localhost/pcds2030_dashboard/debug_assets.php` to verify all paths
2. Visit `http://localhost/pcds2030_dashboard/` to test the main application
3. Check that login page has proper styling

### 6. Clean Up
After everything is working:
1. Delete `debug_assets.php` file
2. Set `error_reporting` to 0 in config.php for production

## Common XAMPP Issues

### Port Conflicts
- If Apache won't start, change the port in XAMPP Control Panel
- Default: 80, try: 8080 or 8000
- Update APP_URL accordingly: `http://localhost:8080/pcds2030_dashboard`

### Virtual Hosts (Advanced)
For a custom domain like `pcds.local`:
1. Edit `C:\Windows\System32\drivers\etc\hosts`
2. Add: `127.0.0.1 pcds.local`
3. Configure XAMPP virtual hosts
4. Update APP_URL to match

## Support
If you continue having issues:
1. Check the debug_assets.php output
2. Verify all files were copied correctly
3. Check XAMPP error logs
4. Ensure database is properly imported and configured
