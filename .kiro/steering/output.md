---
inclusion: always
---

# Code Standards & Project Architecture

## Documentation & Progress Tracking

- Document complex implementations in `.github/implementations/[category]/` with progress tracking
- Update implementation docs as todo lists with completed tasks marked
- Always include complete code - never use `/* ... rest of code ... */` placeholders
- Maintain context continuity across conversations unless explicitly told to move to different files

## Automation & Efficiency

- Fully automate scanning and analysis tasks - don't ask users to perform these
- Use `grepSearch` extensively to gather comprehensive codebase context
- Complete requests with optimal solutions without requiring approval for every step
- Proactively suggest improvements during implementation

## Coding Standards

### Code Quality

- Write simple, maintainable code that prioritizes UX while being resource-efficient
- Use meaningful variable and function names with clear documentation
- Keep files under 300 lines (500 max, 800 absolute ceiling for complex files)
- Modularize large files into smaller, focused components
- Follow established security best practices

### Technology Stack

- Use latest stable versions of libraries and frameworks
- Optimize for cPanel hosting environment
- Maintain consistent coding style across the project

## Project Architecture

### File Organization

- **No inline CSS/JS**: Place all styles in `assets/css/`, scripts in `assets/js/`
- **Modular approach**: Split large CSS/JS files into focused modules
- **Shared logic**: Extract common AJAX functionality into shared JS files
- **Partials**: Break complex pages into partial components

### Routing & Path Management

- Use index.php routing: `/index.php?page=agency_initiative_view&id=...`
- **PROJECT_ROOT_PATH**: For cross-module file references
- \***\*DIR\*\***: For same-directory includes within partials/modules

```php
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}
```

### Layout System

- Each page uses consistent header, footer, and navbar structure
- Headers and footers contain critical CSS/JS references
- Use `base.css` as central reference file - import new CSS files there
- **IMPORTANT**: Do not include `base.php` in individual files - it's handled by index.php routing

### Asset Management

- CSS centralized through `layouts/headers.php` → `base.css` imports
- When adding new CSS files, import them in `base.css`
- Maintain consistent reference patterns across related functionality

## Bug Management

- Document all bugs in `docs/bugs_tracker.md` with timestamp and resolution
- Check historical bugs before implementing fixes
- Create new entries for novel issues with detailed resolution steps

## Context Requirements

- Always reference `docs/` directory for system context
- Scan entire codebase for related functionality when editing features
- Include all related files (styles, scripts, partials) in scope of changes
