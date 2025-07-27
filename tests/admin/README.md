# Admin Unit Testing

This directory contains comprehensive unit tests for the admin-side functionality of the PCDS2030 Dashboard system.

## 📁 Test Structure

```
tests/admin/
├── dashboardLogic.test.js      # Admin dashboard functionality tests
├── usersLogic.test.js          # Admin users management tests
├── programsLogic.test.js       # Admin programs management tests
└── README.md                   # This file

tests/php/admin/
├── AdminCoreTest.php           # Core admin functions tests
├── AdminUsersTest.php          # Admin users management tests
└── [future test files...]
```

## 🧪 Test Coverage

### JavaScript Tests (Jest)

#### 1. Dashboard Logic Tests (`dashboardLogic.test.js`)
- **Page Refresh Functionality**: Tests refresh button behavior and loading states
- **Submissions Refresh**: Tests AJAX refresh with loading indicators
- **No Active Period Handling**: Tests notification display when no active period exists
- **Stat Card Initialization**: Tests dashboard card setup
- **Error Handling**: Tests graceful handling of missing elements
- **Alert Auto-dismiss**: Tests automatic alert dismissal functionality

#### 2. Users Management Tests (`usersLogic.test.js`)
- **Modal Management**: Tests show/hide modal functionality
- **Password Toggle**: Tests password visibility toggle
- **Form Validation**: Tests required fields, password strength, email format
- **Role-based Validation**: Tests agency role requiring agency_id
- **Form Submission**: Tests form submission with validation
- **Error Handling**: Tests missing elements gracefully

#### 3. Programs Management Tests (`programsLogic.test.js`)
- **Program Filtering**: Tests search and rating filters
- **Program Sorting**: Tests ascending/descending sort functionality
- **Program Deletion**: Tests deletion confirmation dialogs
- **Filter Reset**: Tests filter clearing functionality
- **Filter Badges**: Tests active filter badge display
- **HTML Escaping**: Tests XSS prevention
- **Toast Notifications**: Tests user feedback messages
- **Loading Overlay**: Tests loading state management
- **Error Handling**: Tests missing elements gracefully

### PHP Tests (PHPUnit)

#### 1. Core Admin Functions (`AdminCoreTest.php`)
- **Admin Authentication**: Tests `is_admin()` function with various session states
- **Permission Checking**: Tests `check_admin_permission()` function
- **Session Validation**: Tests edge cases and invalid session data
- **Function Availability**: Tests function existence and callability
- **Return Types**: Tests proper return type validation

#### 2. Users Management (`AdminUsersTest.php`)
- **Agency Management**: Tests `get_all_agencies()` function
- **User Management**: Tests CRUD operations for users
- **Validation Logic**: Tests password, email, and role-based validation
- **Database Operations**: Tests database interactions with mocking
- **Error Handling**: Tests various error conditions

## 🚀 Running Tests

### Quick Start
```bash
# Run all admin tests
./scripts/run_admin_tests.sh

# Run only JavaScript tests
npx jest tests/admin/ --verbose

# Run only PHP tests (requires PHP dom extension)
php vendor/bin/phpunit tests/php/admin/ --verbose
```

### Individual Test Files
```bash
# Run specific JavaScript test file
npx jest tests/admin/dashboardLogic.test.js --verbose

# Run specific PHP test file
php vendor/bin/phpunit tests/php/admin/AdminCoreTest.php --verbose
```

## 🔧 Test Setup

### JavaScript Testing (Jest)
- **Environment**: jsdom for DOM simulation
- **Mocks**: CSS imports, global functions, Bootstrap components
- **Timers**: Fake timers for async operations
- **Coverage**: HTML and LCOV reports available

### PHP Testing (PHPUnit)
- **Environment**: Isolated test environment
- **Mocks**: Database connections, prepared statements, result sets
- **Sessions**: Session management for authentication testing
- **Dependencies**: Requires PHP 'dom' extension

## 📊 Test Statistics

- **Total JavaScript Tests**: 47 test cases
- **Total PHP Tests**: 33 test cases
- **Total Test Cases**: 80 test cases
- **Coverage Areas**: Core admin functions, user management, program management, dashboard functionality

## 🎯 Test Quality Standards

### JavaScript Tests
- ✅ Mock external dependencies (CSS, global functions, DOM)
- ✅ Test user interactions and form validation
- ✅ Test error handling and edge cases
- ✅ Use descriptive test names and proper assertions
- ✅ Test both success and failure scenarios

### PHP Tests
- ✅ Mock database connections for isolated testing
- ✅ Test input validation and sanitization
- ✅ Test authentication and authorization logic
- ✅ Use proper setUp() and tearDown() methods
- ✅ Test edge cases and error conditions

## 🚧 Known Issues

### PHPUnit Setup
- **Issue**: PHPUnit requires "dom" extension
- **Status**: Tests written but require proper PHP environment setup
- **Workaround**: Tests are ready to run when environment is configured

### Jest Testing
- **Issue**: Some Chart.js integration may need additional mocking
- **Status**: Core functionality tested, advanced features may need enhancement
- **Workaround**: Current tests cover main functionality

## 📋 Future Enhancements

### Phase 1 Remaining
- [ ] Admin Dashboard Charts Tests
- [ ] Admin Dashboard AJAX Tests
- [ ] Admin Programs Validation Tests
- [ ] Admin Programs AJAX Tests
- [ ] Admin Programs Delete Tests

### Phase 2 Remaining
- [ ] Admin Periods Management Tests
- [ ] Admin Settings Management Tests
- [ ] Admin Statistics Tests
- [ ] Admin Outcomes Tests

### Phase 3 Remaining
- [ ] Admin Audit Tests
- [ ] Admin Reports Tests
- [ ] Integration Tests
- [ ] Performance Tests

## 📝 Best Practices

### Writing New Tests
1. **Follow Naming Convention**: Use descriptive test names
2. **Test One Thing**: Each test should test one specific functionality
3. **Mock Dependencies**: Mock external dependencies to isolate tests
4. **Test Edge Cases**: Include tests for error conditions and edge cases
5. **Use Proper Assertions**: Use appropriate assertion methods
6. **Document Complex Tests**: Add comments for complex test logic

### Maintaining Tests
1. **Keep Tests Updated**: Update tests when functionality changes
2. **Run Tests Regularly**: Run tests before committing changes
3. **Monitor Coverage**: Maintain good test coverage
4. **Refactor When Needed**: Refactor tests to improve maintainability

## 🔍 Debugging Tests

### JavaScript Tests
```bash
# Run tests with detailed output
npx jest tests/admin/ --verbose --no-coverage

# Run specific test with debugging
npx jest tests/admin/dashboardLogic.test.js --verbose --detectOpenHandles
```

### PHP Tests
```bash
# Run tests with detailed output
php vendor/bin/phpunit tests/php/admin/ --verbose --debug

# Run specific test class
php vendor/bin/phpunit tests/php/admin/AdminCoreTest.php --verbose
```

## 📚 Additional Resources

- [Jest Documentation](https://jestjs.io/docs/getting-started)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Project Testing Guidelines](../README.md)
- [Implementation Plan](../../.github/implementations/admin_unit_testing.md) 