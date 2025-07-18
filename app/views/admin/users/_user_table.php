<?php
/**
 * User Table Partial
 *
 * Usage:
 *   include '_user_table.php';
 *   Pass $users (array), $tableTitle (string), $roleType ('admin'|'agency'), $pagination (optional)
 */
if (!isset($users) || !is_array($users)) {
    echo '<div class="alert alert-danger">No users data provided.</div>';
    return;
}
if (!isset($tableTitle)) $tableTitle = 'Users';
if (!isset($roleType)) $roleType = 'agency';
?>
<div class="card <?php echo $roleType === 'admin' ? 'shadow-sm' : 'admin-card'; ?> mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0"><?php echo htmlspecialchars($tableTitle); ?></h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive w-100">
            <table class="table table-forest mb-0">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                        <tr class="<?php echo !$user['is_active'] ? ($roleType === 'admin' ? 'inactive-user' : 'user-inactive') : ''; ?>">
                            <td>
                                <div class="d-flex flex-column align-items-start">
                                    <span class="fw-medium text-forest">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                        <span class="badge ms-2 bg-<?php
                                            if ($user['role'] === 'focal') echo 'warning';
                                            elseif ($user['role'] === 'admin') echo 'primary';
                                            else echo 'info';
                                        ?> align-middle" style="font-size:0.85em; vertical-align:middle;">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </span>
                                    <span class="text-muted small mt-1"><?php echo htmlspecialchars($user['fullname'] ?? '-'); ?></span>
                                    <span class="text-muted small mt-1 d-flex align-items-center">
                                        <span class="user-email" data-email="<?php echo htmlspecialchars($user['email'] ?? '-'); ?>"><?php echo htmlspecialchars($user['email'] ?? '-'); ?></span>
                                        <button type="button" class="btn btn-link btn-copy-email p-0 ms-2" data-bs-toggle="tooltip" title="Copy email" tabindex="0">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <span class="copied-feedback" style="display:none;">Copied!</span>
                                    </span>
                                </div>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($roleType === 'admin'): ?>
                                    <?php if ($user['is_active']): ?>
                                        <span class="user-status active">Active</span>
                                    <?php else: ?>
                                        <span class="user-status inactive">Inactive</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (!isset($_SESSION)) session_start(); ?>
                                <?php if (!empty($user['user_id']) && (!isset($_SESSION['user_id']) || $user['user_id'] != $_SESSION['user_id'])): ?>
                                    <button class="btn btn-sm ms-2 toggle-active-btn" 
                                        data-user-id="<?php echo $user['user_id']; ?>"
                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                        data-status="<?php echo $user['is_active']; ?>"
                                        title="<?php echo $user['is_active'] ? 'Deactivate User' : 'Activate User'; ?>">
                                        <i class="fas fa-toggle-<?php echo $user['is_active'] ? 'on text-success' : 'off text-secondary'; ?>"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm d-inline-flex align-items-center justify-content-center">
                                    <a href="<?php echo APP_URL; ?>/app/views/admin/users/edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn <?php echo $roleType === 'admin' ? 'btn-forest-light' : 'btn-outline-primary'; ?>" title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (!empty($user['user_id']) && (!isset($_SESSION['user_id']) || $user['user_id'] != $_SESSION['user_id'])): ?>
                                        <a href="#" class="btn <?php echo $roleType === 'admin' ? 'btn-forest-light text-danger' : 'btn-outline-danger'; ?> delete-user-btn" 
                                            title="Delete User"
                                            data-user-id="<?php echo $user['user_id']; ?>"
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($pagination) && is_array($pagination) && $pagination['total_pages'] > 1): ?>
        <nav aria-label="User table pagination" class="mt-3">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item<?php echo $pagination['page'] <= 1 ? ' disabled' : ''; ?>">
                    <a class="page-link user-table-page-link" href="#" data-page="<?php echo $pagination['page'] - 1; ?>">&laquo; Prev</a>
                </li>
                <?php
                $maxPagesToShow = 5;
                $start = max(1, $pagination['page'] - floor($maxPagesToShow/2));
                $end = min($pagination['total_pages'], $start + $maxPagesToShow - 1);
                if ($end - $start < $maxPagesToShow - 1) {
                    $start = max(1, $end - $maxPagesToShow + 1);
                }
                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item<?php echo $i == $pagination['page'] ? ' active' : ''; ?>">
                        <a class="page-link user-table-page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item<?php echo $pagination['page'] >= $pagination['total_pages'] ? ' disabled' : ''; ?>">
                    <a class="page-link user-table-page-link" href="#" data-page="<?php echo $pagination['page'] + 1; ?>">Next &raquo;</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div> 