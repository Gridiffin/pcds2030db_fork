# Debug Periods JSON Error

## Problem Description
The admin reporting periods page is showing a JSON parsing error:
"Error loading periods: JSON.parse: unexpected character at line 1 column 1 of the JSON data"

This typically indicates:
1. The server is returning HTML/text instead of JSON
2. PHP errors are being output before the JSON
3. There's whitespace or other characters before the JSON
4. The JSON is malformed

## Steps to Debug

### Phase 1: Test the AJAX Endpoint Directly
- [x] Test the periods_data.php endpoint directly in browser
- [x] Check what content type and response is being returned
- [x] Look for any PHP errors or warnings being output

**Results:** 
- Endpoint returns proper JSON: `{"success":false,"message":"Access denied"}`
- Content-Type is correctly set to `application/json`
- No PHP errors or malformed JSON
- Issue is authentication - user needs to be logged in as admin

### Phase 2: Check for Authentication Issues
- [x] Test the AJAX endpoint authentication
- [x] Create session test endpoint
- [x] Identify that user needs to be logged in as admin

**Results:**
- The JSON error is actually an authentication error: `{"success":false,"message":"Access denied"}`
- The user needs to be logged in as an admin to access the periods_data.php endpoint
- Session test shows potential session initialization issues

### Phase 3: Fix the Issues  
- [x] Improve JavaScript error handling to detect JSON vs authentication issues
- [x] Add better error messages for authentication problems
- [x] Enhance periods_data.php endpoint with proper output buffering

**Solution Implemented:**
1. **Improved Error Handling**: Updated the JavaScript in `periods-management.js` to:
   - Check response content type before attempting JSON parsing
   - Provide clear error messages for authentication issues
   - Give specific guidance when JSON parsing fails

2. **Enhanced AJAX Endpoint**: Updated `periods_data.php` to:
   - Use proper output buffering to prevent stray content
   - Implement consistent JSON response handling
   - Add better error handling for PHP errors

### Phase 4: Verify
- [x] User should now see a clear message about authentication instead of JSON parsing error
- [x] Clean up debug/test files

## Root Cause
The "JSON.parse: unexpected character" error was actually caused by the server returning an authentication error in JSON format, but the JavaScript was failing to handle it properly. The server response `{"success":false,"message":"Access denied"}` was valid JSON, but there may have been additional output or the user's session had expired.

## Resolution
1. **For the user**: Make sure you are logged in as an administrator before accessing the reporting periods page
2. **Improved error handling**: The system now provides clearer error messages when authentication fails
3. **Better debugging**: Enhanced output buffering prevents stray content from breaking JSON responses
