# Local GitHub Actions Testing Script for Windows
# This script simulates the key parts of our GitHub Actions workflows

Write-Host "PCDS2030 Dashboard CI Simulation" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan

# Test 1: PHP Syntax Check
Write-Host ""
Write-Host "1. Testing PHP Syntax Check..." -ForegroundColor Yellow
$ErrorCount = 0
$PhpFiles = Get-ChildItem -Path "app\" -Filter "*.php" -Recurse -ErrorAction SilentlyContinue

foreach ($file in $PhpFiles) {
    $result = php -l $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "ERROR: Syntax error in: $($file.FullName)" -ForegroundColor Red
        Write-Host $result -ForegroundColor Red
        $ErrorCount++
    }
}

if ($ErrorCount -eq 0) {
    Write-Host "SUCCESS: All PHP files have valid syntax" -ForegroundColor Green
} else {
    Write-Host "ERROR: Found $ErrorCount PHP syntax errors" -ForegroundColor Red
}

# Test 2: Jest Tests (Quick)
Write-Host ""
Write-Host "2. Testing Jest Configuration..." -ForegroundColor Yellow
if (Test-Path "package.json") {
    if (Get-Command npm -ErrorAction SilentlyContinue) {
        Write-Host "Running Jest tests..." -ForegroundColor Blue
        npm test 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "SUCCESS: Jest tests passed" -ForegroundColor Green
        } else {
            Write-Host "WARNING: Some Jest tests failed" -ForegroundColor Yellow
        }
    } else {
        Write-Host "WARNING: NPM not found, skipping..." -ForegroundColor Yellow
    }
} else {
    Write-Host "WARNING: package.json not found" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "CI simulation completed!" -ForegroundColor Cyan
Write-Host "=========================" -ForegroundColor Cyan
