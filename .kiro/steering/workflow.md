---
inclusion: always
---

# Development Workflow Guidelines

## Context Discovery
- Always read files from `docs/` folder before starting any task - this contains required project context
- Use `grepSearch` to gather comprehensive context across the codebase
- Reference `docs/system_context.md` for system architecture understanding
- Check `docs/bugs_tracker.md` for historical bug patterns and solutions

## Standard Workflow Process
Follow the **THINK → REASON → SUGGEST → ACT** methodology:

1. **THINK**: Analyze the request using docs context, create step-by-step plan
2. **REASON**: Evaluate plan with pros/cons, justify approach decisions  
3. **SUGGEST**: Propose best practice solution, wait for approval when needed
4. **ACT**: Execute the approved plan

## Task-Specific Workflows

### Bug Fixes
1. Analyze the bug and identify root cause
2. Check `docs/bugs_tracker.md` for similar historical issues
3. Apply existing fix patterns or document new bug entry
4. Implement solution following established patterns
5. Update bug tracker with resolution details

### Feature Implementation  
1. Assess impact on existing codebase using system context
2. Design implementation following project structure patterns
3. Reference `docs/project_structure_best_practices.md` and `docs/example_login_feature_workflow.md`
4. Suggest architectural improvements where applicable
5. Follow established file creation and import orders

### Code Refactoring
1. Analyze refactoring impact on overall system
2. Evaluate against established best practices
3. Suggest improvements aligned with project standards
4. Ensure consistency with existing architecture patterns

## Best Practice References
- Follow patterns in `docs/project_structure_best_practices.md`
- Use `docs/example_login_feature_workflow.md` as implementation template
- Maintain consistency with established file organization and import structures