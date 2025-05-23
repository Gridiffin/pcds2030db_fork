# PowerShell script to update file include paths in the app directory

# Define the project root directory
$rootDir = "d:\xampp\htdocs\pcds2030_dashboard"
$appDir = "$rootDir\app"

# Check if the ROOT_PATH constant is defined in each file
function Add-RootPathDefinition {
    param (
        [string]$content
    )
    
    # Check if the file already has ROOT_PATH definition or uses it
    if ($content -match "ROOT_PATH" -and !($content -match "define\('ROOT_PATH'")) {
        # Add definition if not present but ROOT_PATH is used
        $rootPathDef = @"
<?php
// Define ROOT_PATH if not already defined
if (!defined('ROOT_PATH')) {
    if (defined('PROJECT_ROOT_PATH')) {
        define('ROOT_PATH', PROJECT_ROOT_PATH);
    } else {
        define('ROOT_PATH', dirname(dirname(dirname(__FILE__))) . '/');
    }
}

"@
        # Add after the opening PHP tag
        if ($content -match "^<\?php") {
            $content = $content -replace "^<\?php", $rootPathDef
        } else {
            $content = $rootPathDef + $content
        }
    }
    
    return $content
}

# Function to recursively process files
function Process-Directory {
    param (
        [string]$directory
    )

    Write-Host "Processing directory: $directory"
    
    # Get all PHP files in the current directory
    $phpFiles = Get-ChildItem -Path $directory -Filter "*.php" -File

    foreach ($file in $phpFiles) {
        Write-Host "Checking file: $($file.FullName)"
        $content = Get-Content -Path $file.FullName -Raw
        $originalContent = $content
        
        # Add ROOT_PATH definition if needed
        $content = Add-RootPathDefinition -content $content
        
        # Replace include paths for files that moved to app/
        # 1. Replace config references
        $content = $content -replace "require_once '\.\.\/\.\.\/(config\/config\.php)';", "require_once ROOT_PATH . 'app/`$1';"
        $content = $content -replace "require_once '\.\.\/\.\.\/(includes\/.*?\.php)';", "require_once ROOT_PATH . 'app/lib/`$((`$1 -replace 'includes/', ''))';"
        
        # 2. Handle special cases for admin and agencies folders
        $content = $content -replace "require_once '\.\.\/\.\.\/(includes\/admins\/.*?\.php)';", "require_once ROOT_PATH . 'app/lib/admins/`$((`$1 -replace 'includes/admins/', ''))';"
        $content = $content -replace "require_once '\.\.\/\.\.\/(includes\/agencies\/.*?\.php)';", "require_once ROOT_PATH . 'app/lib/agencies/`$((`$1 -replace 'includes/agencies/', ''))';"
        
        # 3. Update direct references to includes directory
        $content = $content -replace "require_once '(includes\/.*?\.php)';", "require_once ROOT_PATH . 'app/lib/`$((`$1 -replace 'includes/', ''))';"
        
        # 4. Update URL references
        $content = $content -replace "APP_URL \. '\/views\/", "APP_URL . '/app/views/"
        $content = $content -replace "header\('Location: \.\./\.\./login\.php'\);", "header('Location: ' . APP_URL . '/login.php');"
        $content = $content -replace "header\('Location: \.\.\/\.\.\/index\.php'\);", "header('Location: ' . APP_URL . '/index.php');"
        
        # Only write the file if changes were made
        if ($content -ne $originalContent) {
            Write-Host "Updating: $($file.FullName)"
            Set-Content -Path $file.FullName -Value $content
        }
    }

    # Process subdirectories
    $subdirectories = Get-ChildItem -Path $directory -Directory
    foreach ($subdir in $subdirectories) {
        Process-Directory -directory $subdir.FullName
    }
}

# Start processing from the app directory
Process-Directory -directory $appDir

Write-Host "Path update complete!"