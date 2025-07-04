# Agency Group References Progress Tracker

Below are all references to `agency_group` and related columns in the codebase. Use the checkboxes to track progress as each reference is refactored or removed.

- [x] app/views/admin/users/edit_user.php:66 `$agency_groups = get_all_agency_groups($conn);`
- [x] app/views/admin/users/edit_user.php:183 `<label for="agency_group_id" class="form-label">Agency Group</label>`
- [x] app/views/admin/users/edit_user.php:184 `<select class="form-select" id="agency_group_id" name="agency_group_id">`
- [x] app/views/admin/users/edit_user.php:186 `<?php foreach($agency_groups as $group): ?>`
- [x] app/views/admin/users/edit_user.php:187 `<option value="<?php echo $group['agency_group_id']; ?>" <?php echo isset($user['agency_group_id']) && $user['agency_group_id'] == $group['agency_group_id'] ? 'selected' : ''; ?>>`
- [x] app/views/admin/users/edit_user.php:248 `const agencyGroupId = document.getElementById('agency_group_id'); // Add agency_group_id`
- [x] app/views/admin/users/edit_user.php:257 `// agency_group_id is optional, so no required attribute here`
- [x] app/views/admin/users/edit_user.php:262 `// agency_group_id remains optional`

- [x] app/views/admin/users/add_user.php:47 `$agency_groups = get_all_agency_groups($conn);`
- [x] app/views/admin/users/add_user.php:168 `<label for="agency_group_id" class="form-label">Agency Group</label>`
- [x] app/views/admin/users/add_user.php:169 `<select class="form-select" id="agency_group_id" name="agency_group_id">`
- [x] app/views/admin/users/add_user.php:171 `<?php foreach($agency_groups as $group): ?>`
- [x] app/views/admin/users/add_user.php:172 `<option value="<?php echo $group['agency_group_id']; ?>"><?php echo htmlspecialchars($group['group_name']); ?></option>`
- [x] app/views/admin/users/add_user.php:209 `const agencyGroupId = document.getElementById('agency_group_id');`
- [x] app/views/admin/users/add_user.php:217 `const agencyGroups = <?php echo json_encode($agency_groups); ?>;`
- [x] app/views/admin/users/add_user.php:229 `const option = new Option(group.group_name, group.agency_group_id);`

- [x] app/lib/admins/users.php:16 `function get_all_agency_groups(mysqli $conn): array {`
- [x] app/lib/admins/users.php:17 `$agency_groups = [];`
- [x] app/lib/admins/users.php:19 `$sql = "SELECT `agency_group_id`, `group_name`, `sector_id` FROM `agency_group` ORDER BY `group_name` ASC";`
- [x] app/lib/admins/users.php:27 `$agency_groups[] = $row;`
- [x] app/lib/admins/users.php:30 `return $agency_groups;`
- [x] app/lib/admins/users.php:44 `LEFT JOIN agency_group ag ON u.agency_group_id = ag.agency_group_id`
- [x] app/lib/admins/users.php:75 `$required_fields[] = 'agency_group_id';`
- [x] app/lib/admins/users.php:123 `$agency_group_id = null;`
- [x] app/lib/admins/users.php:128 `$agency_group_id = intval($data['agency_group_id']);`
- [x] app/lib/admins/users.php:140 `$group_check = "SELECT agency_group_id FROM agency_group WHERE agency_group_id = ?";`
- [x] app/lib/admins/users.php:142 `$stmt->bind_param("i", $agency_group_id);`
- [x] app/lib/admins/users.php:151 `$query = "INSERT INTO users (username, password, agency_name, role, sector_id, agency_group_id, is_active, created_at)`
- [x] app/lib/admins/users.php:155 `$stmt->bind_param("ssssiis", $username, $hashed_password, $agency_name, $role, $sector_id, $agency_group_id, $is_active);`
- [x] app/lib/admins/users.php:212 `$required_fields[] = 'agency_group_id';`
- [x] app/lib/admins/users.php:326 `// Handle agency_group_id if provided`
- [x] app/lib/admins/users.php:327 `if (isset($data['agency_group_id'])) {`
- [x] app/lib/admins/users.php:328 `$agency_group_id = !empty($data['agency_group_id']) ? intval($data['agency_group_id']) : null;`
- [x] app/lib/admins/users.php:329 `$update_fields[] = "agency_group_id = ?";`
- [x] app/lib/admins/users.php:330 `$bind_params[] = $agency_group_id;`
- [x] app/lib/admins/users.php:333 `if ($agency_group_id) {`
- [x] app/lib/admins/users.php:334 `$group_check = "SELECT agency_group_id FROM agency_group WHERE agency_group_id = ?";`
- [x] app/lib/admins/users.php:336 `$stmt->bind_param("i", $agency_group_id);`
- [x] app/lib/admins/users.php:490 `LEFT JOIN agency_group ag ON u.agency_group_id = ag.agency_group_id`

- [ ] app/views/agency/programs/view_programs.php:52 `$agency_group_id = $_SESSION['agency_group_id'];`
- [ ] app/views/agency/programs/view_programs.php:54 `if ($agency_group_id !== null) {        $query = "SELECT p.*, ... WHERE u.agency_group_id = ? ...";`
- [ ] app/views/agency/programs/view_programs.php:75 `WHERE u.agency_group_id = ?`
- [ ] app/views/agency/programs/view_programs.php:78 `$stmt->bind_param("i", $agency_group_id);`
- [ ] app/views/agency/programs/view_programs.php:87 `$agency_group_id = $_SESSION['agency_group_id'] ?? null;`
- [ ] app/views/agency/programs/view_programs.php:89 `if ($agency_group_id !== null) {`
- [ ] app/views/agency/programs/view_programs.php:112 `WHERE u.agency_group_id = ?`
- [ ] app/views/agency/programs/view_programs.php:115 `$stmt->bind_param("i", $agency_group_id);`

- [ ] app/lib/agencies/programs.php:93 `$agency_group_id = $user ? $user['agency_group_id'] : null;`
- [ ] app/lib/agencies/programs.php:113 `$query = "INSERT INTO programs (program_name, program_number, sector_id, owner_agency_id, agency_group, is_assigned, content_json, created_at)`
- [ ] app/lib/agencies/programs.php:116 `$stmt->bind_param("ssiiis", $program_name, $program_number, $sector_id, $user_id, $agency_group_id, $content_json);`
- [ ] app/lib/agencies/programs.php:118 `$query = "INSERT INTO programs (program_name, program_number, sector_id, owner_agency_id, agency_group, is_assigned, created_at)`
- [ ] app/lib/agencies/programs.php:121 `$stmt->bind_param("ssiii", $program_name, $program_number, $sector_id, $user_id, $agency_group_id);`
- [ ] app/lib/agencies/programs.php:198 `$agency_group_id = $user ? $user['agency_group_id'] : null;`
- [ ] app/lib/agencies/programs.php:202 `$stmt = $conn->prepare("INSERT INTO programs (program_name, program_number, start_date, end_date, owner_agency_id, agency_group, sector_id, initiative_id, is_assigned, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");`
- [ ] app/lib/agencies/programs.php:203 `$stmt->bind_param("ssssiiii", $program_name, $program_number, $start_date, $end_date, $user_id, $agency_group_id, $sector_id, $initiative_id);` 