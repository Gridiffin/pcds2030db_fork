/* 
* PCDS2030 Dashboard Forest Theme - Responsive Design Test
* Use this script to quickly test different viewport sizes in the browser console
*/

// Define viewport sizes to test
const viewportSizes = [
  { name: 'Mobile S', width: 320, height: 568 },
  { name: 'Mobile M', width: 375, height: 667 },
  { name: 'Mobile L', width: 425, height: 812 },
  { name: 'Tablet', width: 768, height: 1024 },
  { name: 'Laptop', width: 1024, height: 768 },
  { name: 'Laptop L', width: 1440, height: 900 },
  { name: 'Desktop', width: 1920, height: 1080 }
];

// Function to resize viewport for testing
function resizeViewport(width, height) {
  const currentWidth = window.innerWidth;
  const currentHeight = window.innerHeight;
  
  // Resize the window
  window.resizeTo(width, height);
  
  // Check if resize was successful
  if (window.innerWidth !== width || window.innerHeight !== height) {
    console.warn(`⚠️ Unable to resize window to ${width}x${height}. This may be due to browser restrictions.`);
  }
  
  return { width: window.innerWidth, height: window.innerHeight };
}

// Function to test all viewport sizes
function testAllViewports() {
  console.log('🌲 PCDS2030 Dashboard - Forest Theme Responsive Testing 🌲');
  console.log('============================================================');
  
  viewportSizes.forEach((size, index) => {
    console.log(`Testing viewport: ${size.name} (${size.width}x${size.height})`);
    const actual = resizeViewport(size.width, size.height);
    console.log(`Actual viewport: ${actual.width}x${actual.height}`);
    
    // Give time to observe and take notes on each size
    if (index < viewportSizes.length - 1) {
      console.log('Press any key to continue to next size...');
    }
  });
  
  console.log('============================================================');
  console.log('Testing complete! Check for any responsive design issues at each viewport size.');
}

// Instructions for manual testing
console.log(`
🌲 PCDS2030 Dashboard - Forest Theme Responsive Testing 🌲

To test responsive design in DevTools:
1. Open browser DevTools (F12 or right-click > Inspect)
2. Click on the Device Toggle button (Ctrl+Shift+M in Chrome)
3. Select different device presets or set custom dimensions
4. Check each page for layout issues

Or run testAllViewports() to cycle through preset sizes.

Key areas to check:
- Navigation menu collapse
- Card layouts
- Table responsiveness
- Form field widths
- Typography scaling
- Button placement
- Spacing consistency
`);
