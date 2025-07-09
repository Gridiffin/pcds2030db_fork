# Refactor: Use Only programs.rating for All Rating Logic

## Problem
- The system previously supported per-submission ratings and flexible content_json storage, but the new requirement is to use only the programs.rating field for all rating logic.
- All references to content_json, JSON_EXTRACT, and per-submission ratings must be removed or refactored.

## Affected Files (Initial Sweep)
- app/views/agency/programs/update_program.php
- app/views/agency/programs/program_details.php
- app/views/agency/initiatives/view_initiative.php
- app/views/agency/ajax/submit_program.php
- app/views/admin/programs/view_program.php
- app/views/admin/programs/edit_program.php
- app/views/admin/programs/edit_program_backup.php
- app/views/admin/programs/assign_programs.php
- app/lib/numbering_helpers.php
- app/lib/admins/statistics.php
- app/lib/agencies/statistics.php

## Refactor Plan
- [ ] 1. Remove all logic and queries referencing content_json, JSON_EXTRACT, or per-submission ratings in the above files.
- [ ] 2. Refactor all rating logic to use only programs.rating.
- [ ] 3. Remove or refactor any UI/backend logic that expects a rating per submission/period.
- [ ] 4. Update documentation and comments to reflect the new approach.
- [ ] 5. Test all user-facing modules (dashboard, program views, submission, reporting) to ensure ratings display and logic work as expected.

## Progress Checklist
- [ ] update_program.php
- [ ] program_details.php
- [ ] view_initiative.php
- [ ] submit_program.php
- [ ] view_program.php (admin)
- [ ] edit_program.php (admin)
- [ ] edit_program_backup.php (admin)
- [ ] assign_programs.php (admin)
- [ ] numbering_helpers.php
- [ ] admins/statistics.php
- [ ] agencies/statistics.php

---

*Mark each file as complete as the refactor progresses. Add any additional files discovered during the sweep.* 