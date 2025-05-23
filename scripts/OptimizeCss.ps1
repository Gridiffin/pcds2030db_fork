# Forest Theme CSS Optimization Script
# This script helps optimize CSS files for production use

Write-Host "PCDS2030 Dashboard - Forest Theme CSS Optimization" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

# Define the CSS directory
$cssDir = "d:\laragon\www\pcds2030_dashboard\assets\css"
$tempDir = "d:\laragon\www\pcds2030_dashboard\assets\css\temp"
$backupDir = "d:\laragon\www\pcds2030_dashboard\assets\css\backup"

# Check if Node.js is installed (required for optimization tools)
try {
    $nodeVersion = node --version
    Write-Host "Node.js detected: $nodeVersion" -ForegroundColor Green
} catch {
    Write-Host "Node.js not found. Please install Node.js to use this optimization script." -ForegroundColor Red
    Write-Host "Download from: https://nodejs.org/" -ForegroundColor Red
    exit
}

# Create directories if they don't exist
if (-not (Test-Path $tempDir)) {
    New-Item -ItemType Directory -Path $tempDir | Out-Null
}

if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir | Out-Null
}

# Backup original CSS files
Write-Host "Backing up original CSS files..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFolder = Join-Path $backupDir "backup_$timestamp"
New-Item -ItemType Directory -Path $backupFolder | Out-Null

Get-ChildItem -Path $cssDir -Filter "*.css" -Recurse | ForEach-Object {
    $relativePath = $_.FullName.Substring($cssDir.Length + 1)
    $backupFilePath = Join-Path $backupFolder $relativePath
    
    # Create directory structure in backup folder if needed
    $backupFileDir = Split-Path $backupFilePath -Parent
    if (-not (Test-Path $backupFileDir)) {
        New-Item -ItemType Directory -Path $backupFileDir | Out-Null
    }
    
    Copy-Item $_.FullName -Destination $backupFilePath
}

Write-Host "Backup completed: $backupFolder" -ForegroundColor Green

# Install required npm packages if needed
Write-Host "Checking for required NPM packages..." -ForegroundColor Yellow
npm list -g clean-css-cli > $null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Installing clean-css-cli..." -ForegroundColor Yellow
    npm install -g clean-css-cli
}

# Copy and optimize all CSS files
Write-Host "Optimizing CSS files..." -ForegroundColor Yellow
$totalSavings = 0
$fileCount = 0

Get-ChildItem -Path $cssDir -Filter "*.css" -Recurse | ForEach-Object {
    if ($_.FullName -notlike "*\backup\*" -and $_.FullName -notlike "*\temp\*" -and $_.FullName -notlike "*\min\*") {
        $fileCount++
        $originalSize = $_.Length
        $outputPath = $_.FullName
        $tempOutputPath = Join-Path $tempDir $_.Name
        
        # Run cleancss optimization
        cleancss -o $tempOutputPath $outputPath
        
        if (Test-Path $tempOutputPath) {
            $optimizedSize = (Get-Item $tempOutputPath).Length
            $savings = $originalSize - $optimizedSize
            $savingsPercent = if ($originalSize -gt 0) { [math]::Round(($savings / $originalSize) * 100, 1) } else { 0 }
            
            # Replace original with optimized version
            Copy-Item $tempOutputPath -Destination $outputPath -Force
            Remove-Item $tempOutputPath
            
            $totalSavings += $savings
            
            Write-Host "Optimized: $($_.Name) - Saved $savingsPercent% ($savings bytes)" -ForegroundColor Green
        } else {
            Write-Host "Failed to optimize: $($_.Name)" -ForegroundColor Red
        }
    }
}

# Clean up
Remove-Item $tempDir -Recurse

# Final report
Write-Host ""
Write-Host "Optimization Complete!" -ForegroundColor Cyan
Write-Host "------------------" -ForegroundColor Cyan
Write-Host "Files processed: $fileCount" -ForegroundColor White
Write-Host "Total bytes saved: $totalSavings bytes" -ForegroundColor White
Write-Host "Backup location: $backupFolder" -ForegroundColor White
Write-Host ""
Write-Host "Note: Original files have been replaced with optimized versions." -ForegroundColor Yellow
Write-Host "You can restore from backup if needed." -ForegroundColor Yellow
