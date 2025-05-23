# PowerShell script to update file include paths in the ajax directory

# Define the project root directory
$rootDir = "d:\xampp\htdocs\pcds2030_dashboard\new structure"
$ajaxDir = "$rootDir\app\ajax"
$apiDir = "$rootDir\app\api"

# Function to update files
function Update-IncludePaths {
    param (
        [string]$file
    )
    
    Write-Host "Updating file: $file"
    $content = Get-Content -Path $file -Raw
    
    # Update include paths
    $content = $content -replace "require_once '../includes/", "require_once '../lib/"
    $content = $content -replace "require_once '\.\.\/includes\/", "require_once '../lib/"
    
    # Write the updated content back to the file
    Set-Content -Path $file -Value $content
}

# Process AJAX files
$ajaxFiles = Get-ChildItem -Path $ajaxDir -Filter "*.php" -File
foreach ($file in $ajaxFiles) {
    Update-IncludePaths -file $file.FullName
}

# Process API files
$apiFiles = Get-ChildItem -Path $apiDir -Filter "*.php" -File
foreach ($file in $apiFiles) {
    Update-IncludePaths -file $file.FullName
}

Write-Host "Path update complete!"
