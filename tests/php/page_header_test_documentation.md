# Page Header Component Tests

This document provides instructions for running and interpreting the page header component tests.

## Test Overview

The page header component tests are designed to verify that the component meets the requirements specified in the design document. The tests are divided into two categories:

1. **Configuration Tests**: These tests verify that the page header component correctly handles different configuration options.
2. **Responsive Tests**: These tests verify that the page header component behaves correctly across different viewport sizes.

## Requirements Being Tested

### Configuration Tests (Task 4.1)

- **2.1**: Configure title and subtitle
- **2.2**: Configure breadcrumb trail
- **2.3**: Backward compatibility
- **2.4**: Sensible defaults

### Responsive Tests (Task 4.2)

- **3.1**: Responsive behavior on different screen sizes
- **3.2**: Consistent styling
- **3.3**: Adequate spacing and visual hierarchy
- **3.4**: Sufficient contrast for readability

## Running the Tests

### Method 1: Run All Tests

1. Navigate to the tests directory in your browser:
   ```
   http://localhost/your-project-path/tests/php/run_page_header_tests.php
   ```

2. This will display a page with tabs for each test category.
3. Click on each tab to view the test results.
4. Record your observations in the test summary form at the bottom of the page.

### Method 2: Run Individual Tests

You can also run each test individually:

1. For configuration tests:
   ```
   http://localhost/your-project-path/tests/php/page_header_test.php
   ```

2. For responsive tests:
   ```
   http://localhost/your-project-path/tests/php/page_header_responsive_test.php
   ```

## Test Cases

### Configuration Tests

The configuration tests include the following test cases:

1. **Basic Title Only**: Tests the component with only a title.
2. **Title and Subtitle**: Tests the component with both title and subtitle.
3. **Title with Breadcrumb**: Tests the component with title and breadcrumb.
4. **Complete Configuration**: Tests the component with all configuration options.
5. **Legacy Configuration**: Tests the component with legacy configuration format.
6. **Theme Variants**: Tests the component with different theme variants.
7. **Hidden Elements**: Tests the component with hidden subtitle and breadcrumb.
8. **Custom Title Tag**: Tests the component with a custom title tag.

### Responsive Tests

The responsive tests include the following test cases:

1. **Standard Header**: Tests a standard header configuration across different viewport sizes.
2. **Long Title and Subtitle**: Tests how the component handles long text content across different viewport sizes.
3. **Dark Theme**: Tests the dark theme variant across different viewport sizes.

## Viewport Sizes

The responsive tests check the component at the following viewport sizes:

- **Mobile**: 320px
- **Small Mobile**: 375px
- **Large Mobile**: 425px
- **Tablet**: 768px
- **Laptop**: 1024px
- **Desktop**: 1440px

## Interpreting Test Results

### Configuration Tests

For each configuration test case, the test will display:

- The test name
- Whether the test passed or failed
- The expected vs. actual results (if the test failed)
- The rendered output

A test passes if the rendered output matches the expected configuration.

### Responsive Tests

For each responsive test case and viewport size, you should record:

- Whether the test passed or failed
- Any notes or observations

A test passes if the component is readable and properly formatted at the specified viewport size.

## Generating a Test Report

After running all tests, you can generate a test report by:

1. Recording your observations in the test summary form.
2. Clicking the "Save Summary" button.
3. The test summary will be displayed in JSON format.

## Troubleshooting

If you encounter any issues running the tests:

1. Ensure that the page header component files are correctly located:
   - `app/views/layouts/page_header.php`
   - `assets/css/layout/page_header.css`

2. Ensure that the base layout file correctly includes the page header component:
   - `app/views/layouts/base.php`

3. Check that the necessary CSS files are being loaded.

4. Verify that the test files have the correct paths to the component files.

## Next Steps

After completing the tests:

1. Document any issues or bugs found.
2. Make any necessary adjustments to the page header component.
3. Re-run the tests to verify that the issues have been resolved.
4. Update the task status in the task list.