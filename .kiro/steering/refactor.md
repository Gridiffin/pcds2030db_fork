---
inclusion: always
---

# Module Refactoring Standards

Standards for refactoring PHP/JavaScript modules with Vite bundling in this project.

## Core Principles

**CRITICAL**: Never remove functions/sections during refactoring - only reorganize and modularize.

### File Structure Standards
```
app/views/[section]/[module].php          # Main view
app/views/[section]/partials/             # View partials  
assets/css/[section]/[module].css         # Main CSS (imports subfiles)
assets/js/[section]/[module].js           # DOM/event handling
assets/js/[section]/[module]Logic.js      # Pure logic functions
app/ajax/ or app/api/                     # AJAX endpoints (JSON only)
tests/[section]/[module]Logic.test.js     # Unit tests
```

### Architecture Patterns

**Data Flow**: Controller/Handler → Model/Helper → View → Assets
- Database operations only in `lib/` (models/helpers)
- Views display data only - no business logic
- AJAX endpoints return JSON only, never HTML

**Asset Management**:
- CSS: Use `@import` for modularization, preserve all existing styles
- JS: Separate pure logic from DOM manipulation, use ES modules
- Vite: Bundle per module/page with proper entry points

**Layout System**:
- Use base layouts for asset injection: `$cssBundle`, `$jsBundle`, `$contentFile`
- Main views: Include layout directly, don't set `$contentFile = __FILE__`
- Partials: Set `$contentFile` to partial path for injection

## Vite Configuration Template
```js
export default defineConfig({
  build: {
    rollupOptions: {
      input: {
        [module]: 'assets/js/[section]/[module].js'
      },
      output: {
        entryFileNames: 'js/[name].bundle.js',
        assetFileNames: 'css/[name].bundle.css'
      }
    },
    outDir: 'dist'
  }
});
```

## JavaScript Module Pattern
```js
// [module]Logic.js - Pure functions
export function validateInput(value) { /* logic */ }

// [module].js - DOM/Events + CSS import
import '../../css/[section]/[module].css';
import { validateInput } from './[module]Logic.js';
```

## Common Issues & Solutions

### Asset Loading Problems
- **404 Errors**: Update all asset references after moving files
- **Missing Styles**: Ensure HTML classes match CSS, preserve all original styles during modularization
- **Vite/ESM Issues**: Use ES module syntax, load scripts with `type="module"`

### PHP Integration Issues  
- **Session Handling**: Call `session_start()` before any output
- **Routing**: Use centralized routing (`index.php?page=...`) with access control
- **Headers Already Sent**: Handle redirects before any output
- **Role Logic**: Account for all valid user roles (admin, agency, focal)

### JavaScript Issues
- **AJAX Paths**: Use dynamic base paths, not hardcoded `/app/api/...`
- **Event Listeners**: Ensure DOM elements exist before attaching listeners
- **Validation**: Support both usernames and emails where applicable

## Path Management
- **PROJECT_ROOT_PATH**: For cross-module file references
- **__DIR__**: For same-directory includes within partials/modules
- **Dynamic Paths**: Essential for AJAX requests and asset loading

## Testing & QA
- Test with all user roles after changes
- Verify all features work in browser
- Run unit tests: `npm test`
- Document bugs in `docs/bugs_tracker.md`

## Security Best Practices
- Input validation and output escaping
- Centralized access control
- Session security
- AJAX endpoint protection