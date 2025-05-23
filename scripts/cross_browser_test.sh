#!/bin/bash
# Simple browser testing script for PCDS2030 Dashboard Forestry Theme
# This script opens the style guide in different browsers for testing

# Define paths to browsers based on your environment
CHROME_PATH="C:/Program Files/Google/Chrome/Application/chrome.exe"
FIREFOX_PATH="C:/Program Files/Mozilla Firefox/firefox.exe"
EDGE_PATH="C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe"

# Style guide URL (adjust to your environment)
STYLE_GUIDE_URL="http://localhost/pcds2030_dashboard/app/views/admin/style-guide.php"

# Function to test a browser
test_browser() {
  local browser_path=$1
  local browser_name=$2
  
  echo "Testing in $browser_name..."
  if [ -f "$browser_path" ]; then
    "$browser_path" "$STYLE_GUIDE_URL" &
    echo "$browser_name started successfully."
  else
    echo "$browser_name not found at $browser_path. Skipping..."
  fi
}

# Execute tests
echo "Starting cross-browser testing for PCDS2030 Dashboard Forestry Theme"
echo "=============================================================="
echo "Opening Style Guide in multiple browsers..."
echo ""

test_browser "$CHROME_PATH" "Chrome"
test_browser "$FIREFOX_PATH" "Firefox"
test_browser "$EDGE_PATH" "Edge"

echo ""
echo "Testing complete. Please check each browser for visual consistency and interactions."
echo "Things to verify:"
echo "- Color scheme consistency"
echo "- Component rendering"
echo "- Typography"
echo "- Responsive layout"
echo "- Interactive elements behavior"
echo ""
echo "Note any issues and report them in the implementation plan."
