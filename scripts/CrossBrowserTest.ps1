# Cross-browser testing script for PCDS2030 Dashboard Forestry Theme
# PowerShell version

# Define paths to browsers - adjust these paths as needed for your Windows installation
$ChromePath = "C:\Program Files\Google\Chrome\Application\chrome.exe"
$FirefoxPath = "C:\Program Files\Mozilla Firefox\firefox.exe"
$EdgePath = "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

# Style guide URL (adjust to your environment)
$StyleGuideUrl = "http://localhost/pcds2030_dashboard/app/views/admin/style-guide.php"

# Function to test a browser
function Test-Browser {
    param (
        [string]$BrowserPath,
        [string]$BrowserName
    )
    
    Write-Host "Testing in $BrowserName..."
    if (Test-Path $BrowserPath) {
        Start-Process $BrowserPath $StyleGuideUrl
        Write-Host "$BrowserName started successfully." -ForegroundColor Green
    } else {
        Write-Host "$BrowserName not found at $BrowserPath. Skipping..." -ForegroundColor Yellow
    }
}

# Execute tests
Write-Host "Starting cross-browser testing for PCDS2030 Dashboard Forestry Theme" -ForegroundColor Cyan
Write-Host "==============================================================" -ForegroundColor Cyan
Write-Host "Opening Style Guide in multiple browsers..." -ForegroundColor Cyan
Write-Host ""

Test-Browser -BrowserPath $ChromePath -BrowserName "Chrome"
Test-Browser -BrowserPath $FirefoxPath -BrowserName "Firefox"
Test-Browser -BrowserPath $EdgePath -BrowserName "Edge"

Write-Host ""
Write-Host "Testing complete. Please check each browser for visual consistency and interactions." -ForegroundColor Cyan
Write-Host "Things to verify:" -ForegroundColor Cyan
Write-Host "- Color scheme consistency" -ForegroundColor White
Write-Host "- Component rendering" -ForegroundColor White
Write-Host "- Typography" -ForegroundColor White
Write-Host "- Responsive layout" -ForegroundColor White
Write-Host "- Interactive elements behavior" -ForegroundColor White
Write-Host ""
Write-Host "Note any issues and report them in the implementation plan." -ForegroundColor Cyan
