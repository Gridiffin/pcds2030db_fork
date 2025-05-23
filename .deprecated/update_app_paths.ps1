# PowerShell script to update file include paths in the app directory
$rootDir = "d:\xampp\htdocs\pcds2030_dashboard\app"

# Function to recursively process files
function Process-Directory {
    param (
        [string]$directory
    )

    # Get all PHP files in the current directory
    $phpFiles = Get-ChildItem -Path $directory -Filter "*.php" -File

    foreach ($file in $phpFiles) {
        $content = Get-Content -Path $file.FullName -Raw

        # Replace include paths
        # Replace '../../config/config.php' with 'ROOT_PATH . "app/config/config.php"'
        $content = $content -replace "require_once '../../config/config\.php';", "require_once ROOT_PATH . 'app/config/config.php';"
        $content = $content -replace "require_once '../../includes/db_connect\.php';", "require_once ROOT_PATH . 'app/lib/db_connect.php';"
        $content = $content -replace "require_once '../../includes/session\.php';", "require_once ROOT_PATH . 'app/lib/session.php';"
        $content = $content -replace "require_once '../../includes/functions\.php';", "require_once ROOT_PATH . 'app/lib/functions.php';"
        $content = $content -replace "require_once '../../includes/agencies/index\.php';", "require_once ROOT_PATH . 'app/lib/agencies/index.php';"
        $content = $content -replace "require_once '../../includes/admins/index\.php';", "require_once ROOT_PATH . 'app/lib/admins/index.php';"
        $content = $content -replace "require_once '../../includes/admin_functions\.php';", "require_once ROOT_PATH . 'app/lib/admin_functions.php';"
        $content = $content -replace "require_once '../../includes/agency_functions\.php';", "require_once ROOT_PATH . 'app/lib/agency_functions.php';"
        $content = $content -replace "require_once '../../includes/rating_helpers\.php';", "require_once ROOT_PATH . 'app/lib/rating_helpers.php';"
        $content = $content -replace "require_once '../../includes/dashboard_header\.php';", "require_once ROOT_PATH . 'app/lib/dashboard_header.php';"
        $content = $content -replace "require_once '../../includes/period_selector\.php';", "require_once ROOT_PATH . 'app/lib/period_selector.php';"

        # Update paths to files in the same app structure but with new relative paths
        # (We keep relative paths for files within the app directory structure when appropriate)

        # Write the updated content back to the file
        Set-Content -Path $file.FullName -Value $content

        Write-Host "Updated: $($file.FullName)"
    }

    # Process subdirectories
    $subdirectories = Get-ChildItem -Path $directory -Directory
    foreach ($subdir in $subdirectories) {
        Process-Directory -directory $subdir.FullName
    }
}

# Start processing from the root directory
Process-Directory -directory $rootDir

Write-Host "Path update complete!"
