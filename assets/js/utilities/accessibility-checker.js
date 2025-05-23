/**
 * Forest Theme Accessibility Checker
 * This script checks the page for common accessibility issues
 * Paste into browser console on any page to run tests
 */

(function() {
    console.log('%c🌲 Forest Theme Accessibility Checker 🌲', 'color:#537D5D; font-size:16px; font-weight:bold;');
    console.log('%cChecking page for accessibility issues...', 'color:#73946B');
    
    const results = {
        errors: [],
        warnings: [],
        passed: []
    };
    
    // Test 1: Check for alt text on images
    function checkImagesAltText() {
        const images = document.querySelectorAll('img');
        const imagesWithoutAlt = Array.from(images).filter(img => !img.hasAttribute('alt'));
        
        if (imagesWithoutAlt.length > 0) {
            results.errors.push({
                test: 'Images missing alt text',
                count: imagesWithoutAlt.length,
                elements: imagesWithoutAlt,
                info: 'All images should have alt text for screen readers'
            });
        } else {
            results.passed.push('All images have alt text');
        }
    }
    
    // Test 2: Check for proper heading hierarchy
    function checkHeadingHierarchy() {
        const headings = Array.from(document.querySelectorAll('h1, h2, h3, h4, h5, h6'));
        let previousLevel = 0;
        const skippedLevels = [];
        
        headings.forEach(heading => {
            const currentLevel = parseInt(heading.tagName[1]);
            
            if (previousLevel === 0) {
                if (currentLevel !== 1) {
                    skippedLevels.push({
                        element: heading,
                        expected: 'h1',
                        found: heading.tagName.toLowerCase()
                    });
                }
            } else if (currentLevel > previousLevel && currentLevel !== previousLevel + 1) {
                skippedLevels.push({
                    element: heading,
                    expected: `h${previousLevel + 1}`,
                    found: heading.tagName.toLowerCase()
                });
            }
            
            previousLevel = currentLevel;
        });
        
        if (skippedLevels.length > 0) {
            results.errors.push({
                test: 'Heading hierarchy issues',
                count: skippedLevels.length,
                elements: skippedLevels,
                info: 'Headings should not skip levels (e.g., h1 to h3)'
            });
        } else {
            results.passed.push('Heading hierarchy is correct');
        }
    }
    
    // Test 3: Check contrast of text against background
    function checkContrastRatio() {
        // This is a simplified check - a complete check would require a more complex algorithm
        // to calculate actual contrast ratios
        const forestMediumElements = Array.from(document.querySelectorAll('*')).filter(el => {
            const style = window.getComputedStyle(el);
            const color = style.color;
            return color === 'rgb(115, 148, 107)' && style.fontSize.replace('px', '') < 18;
        });
        
        if (forestMediumElements.length > 0) {
            results.warnings.push({
                test: 'Potential low contrast with Forest Medium color',
                count: forestMediumElements.length,
                elements: forestMediumElements,
                info: 'Forest Medium color may not have sufficient contrast for small text'
            });
        }
    }
    
    // Test 4: Check for keyboard accessibility
    function checkKeyboardAccessibility() {
        const interactiveElements = Array.from(document.querySelectorAll('a, button, [role="button"], input, select, textarea'));
        const inaccessibleElements = interactiveElements.filter(el => {
            const tabIndex = el.getAttribute('tabindex');
            return tabIndex === '-1' && !el.hasAttribute('aria-hidden');
        });
        
        if (inaccessibleElements.length > 0) {
            results.errors.push({
                test: 'Interactive elements not keyboard accessible',
                count: inaccessibleElements.length,
                elements: inaccessibleElements,
                info: 'Interactive elements should be keyboard accessible'
            });
        } else {
            results.passed.push('All interactive elements are keyboard accessible');
        }
    }
    
    // Test 5: Check for form labels
    function checkFormLabels() {
        const formInputs = Array.from(document.querySelectorAll('input, select, textarea')).filter(input => {
            return input.type !== 'hidden' && input.type !== 'submit' && input.type !== 'button';
        });
        
        const inputsWithoutLabels = formInputs.filter(input => {
            const id = input.getAttribute('id');
            if (!id) return true;
            
            const hasLabel = document.querySelector(`label[for="${id}"]`);
            const hasAriaLabel = input.hasAttribute('aria-label');
            const hasAriaLabelledBy = input.hasAttribute('aria-labelledby');
            
            return !hasLabel && !hasAriaLabel && !hasAriaLabelledBy;
        });
        
        if (inputsWithoutLabels.length > 0) {
            results.errors.push({
                test: 'Form inputs missing labels',
                count: inputsWithoutLabels.length,
                elements: inputsWithoutLabels,
                info: 'All form inputs should have associated labels'
            });
        } else {
            results.passed.push('All form inputs have associated labels');
        }
    }
    
    // Run all tests
    checkImagesAltText();
    checkHeadingHierarchy();
    checkContrastRatio();
    checkKeyboardAccessibility();
    checkFormLabels();
    
    // Output results
    console.log('%c📊 Accessibility Test Results:', 'color:#537D5D; font-weight:bold;');
    
    console.log('%c✅ PASSED:', 'color:green; font-weight:bold;');
    results.passed.forEach(test => console.log(`  - ${test}`));
    
    if (results.warnings.length > 0) {
        console.log('%c⚠️ WARNINGS:', 'color:orange; font-weight:bold;');
        results.warnings.forEach(warning => {
            console.log(`  - ${warning.test} (${warning.count} instances)`);
            console.log(`    Info: ${warning.info}`);
            console.log('%c    Elements:', 'font-weight:bold;');
            console.log(warning.elements);
        });
    }
    
    if (results.errors.length > 0) {
        console.log('%c❌ ERRORS:', 'color:red; font-weight:bold;');
        results.errors.forEach(error => {
            console.log(`  - ${error.test} (${error.count} instances)`);
            console.log(`    Info: ${error.info}`);
            console.log('%c    Elements:', 'font-weight:bold;');
            console.log(error.elements);
        });
    }
    
    // Summary
    const totalIssues = results.errors.length + results.warnings.length;
    if (totalIssues === 0) {
        console.log('%c🎉 No accessibility issues detected!', 'color:green; font-weight:bold;');
    } else {
        console.log(`%c🔍 Found ${results.errors.length} errors and ${results.warnings.length} warnings`, 'color:#73946B; font-weight:bold;');
    }
    
    console.log('%c📝 Note: This is a basic check. For comprehensive testing, use tools like axe, WAVE, or Lighthouse.', 'font-style:italic;');
    
    // Return results object for further inspection
    return results;
})();
