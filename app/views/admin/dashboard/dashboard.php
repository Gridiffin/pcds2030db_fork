<?php
/**
 * Admin Dashboard
 * 
 * Main interface for admin users.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Admin Dashboard';

// Get current reporting period
$current_period = get_current_reporting_period();

// Add period_id handling for historical views
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);
$viewing_period = $period_id ? get_reporting_period($period_id) : $current_period;

// Get data for the dashboard
$submission_stats = get_period_submission_stats($period_id);
$sector_data = get_sector_data_for_period($period_id);
$recent_submissions = get_recent_submissions($period_id, 5);

// Get both assigned and agency-created programs for display
$assigned_programs = get_admin_programs_list($period_id, ['is_assigned' => true]);
$agency_programs = get_admin_programs_list($period_id, ['is_assigned' => false]);

// Count assigned and agency-created programs
$assigned_count = count($assigned_programs);
$agency_count = count($agency_programs);

// Additional scripts
$additionalScripts = [
    asset_url('js/admin', 'dashboard_charts.js'),
    asset_url('js/admin', 'dashboard.js'),
    asset_url('js', 'period_selector.js')
];

// Include header
require_once '../../layouts/header.php';

// Set up the dashboard header variables
$title = "Admin Dashboard";
$subtitle = "System overview and management";
$headerStyle = 'standard-blue'; // Updated to use standardized blue variant
$headerClass = ''; // Removed homepage-header class as it's no longer needed
$actions = [
    [
        'url' => '#',
        'id' => 'refreshPage',
        'text' => 'Refresh Data',
        'icon' => 'fas fa-sync-alt',
        'class' => 'btn-light' // White outline button on blue background
    ]
];

// Include the dashboard header component
require_once ROOT_PATH . 'app/lib/dashboard_header.php';

// Pass hasActivePeriod to JavaScript
$hasActivePeriod = isset($current_period) && !empty($current_period);
?>

<script>
const hasActivePeriod = <?php echo $hasActivePeriod ? 'true' : 'false'; ?>;
</script>

<!-- Dashboard Content -->
<main class="flex-fill">
    <section class="section">
        <div class="container-fluid">
            <!-- Period Selector Component -->
            <?php require_once ROOT_PATH . 'app/lib/period_selector.php'; ?>

        <!-- Quick Actions Section - Optimized for high-value admin actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0 text-white"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row justify-content-center text-center g-4">
                            <?php
                            // Get current period status for contextual actions
                            $periodOpen = isset($current_period) && isset($current_period['status']) && $current_period['status'] === 'open';
                            $periodId = $current_period['period_id'] ?? 0;
                            ?>                            <div class="col-lg-3 col-md-4 col-6">
                                <a href="<?php echo view_url('admin', 'programs/assign_programs.php'); ?>" class="btn btn-outline-success w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn border-success">
                                    <i class="fas fa-tasks fa-2x"></i>
                                    <span class="mt-2">Assign Programs</span>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-4 col-6">
                                <a href="<?php echo view_url('admin', 'periods/reporting_periods.php'); ?>" class="btn <?php echo $periodOpen ? 'btn-outline-danger' : 'btn-outline-success'; ?> w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn <?php echo $periodOpen ? 'border-danger' : 'border-success'; ?>">
                                    <i class="fas fa-calendar-alt fa-2x"></i>
                                    <span class="mt-2"><?php echo $periodOpen ? 'Manage Periods' : 'Manage Periods'; ?></span>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-4 col-6">
                                <a href="<?php echo view_url('admin', 'reports/generate_reports.php'); ?>" class="btn btn-outline-primary w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn border-primary">
                                    <i class="fas fa-file-powerpoint fa-2x"></i>
                                    <span class="mt-2">Generate Reports</span>
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-4 col-6">
                                <a href="<?php echo view_url('admin', 'users/add_user.php'); ?>" class="btn btn-outline-info w-100 d-flex flex-column align-items-center justify-content-center quick-action-btn border-info">
                                    <i class="fas fa-user-plus fa-2x"></i>
                                    <span class="mt-2">Add New User</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div data-period-content="stats_section">
            <div class="row">
                <!-- Agencies Reporting Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card primary">
                        <div class="card-body">
                            <div class="icon-container">
                                <i class="fas fa-users stat-icon"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-title">Agencies Reporting</div>
                                <div class="stat-value">
                                    <?php echo $submission_stats['agencies_reported'] ?? 0; ?>/<?php echo $submission_stats['total_agencies'] ?? 0; ?>
                                </div>
                                <div class="stat-subtitle">
                                    <i class="fas fa-check me-1"></i>
                                    <?php echo $submission_stats['agencies_reported'] ?? 0; ?> Agencies Reported
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Programs On Track Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card warning">
                        <div class="card-body">
                            <div class="icon-container">
                                <i class="fas fa-calendar-check stat-icon"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-title">Programs On Track</div>
                                <div class="stat-value">
                                    <?php echo $submission_stats['on_track_programs'] ?? 0; ?>
                                </div>
                                <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
                                <div class="stat-subtitle">
                                    <i class="fas fa-chart-line me-1"></i>
                                    <?php echo round(($submission_stats['on_track_programs'] / $submission_stats['total_programs']) * 100); ?>% of total
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Programs Delayed Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card danger">
                        <div class="card-body">
                            <div class="icon-container">
                                <i class="fas fa-exclamation-triangle stat-icon"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-title">Programs Delayed</div>
                                <div class="stat-value">
                                    <?php echo $submission_stats['delayed_programs'] ?? 0; ?>
                                </div>
                                <?php if (isset($submission_stats['total_programs']) && $submission_stats['total_programs'] > 0): ?>
                                <div class="stat-subtitle">
                                    <i class="fas fa-chart-line me-1"></i>
                                    <?php echo round(($submission_stats['delayed_programs'] / $submission_stats['total_programs']) * 100); ?>% of total
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Completion Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card success">
                        <div class="card-body">
                            <div class="icon-container">
                                <i class="fas fa-clipboard-list stat-icon"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-title">Overall Completion</div>
                                <div class="stat-value">
                                    <?php echo $submission_stats['completion_percentage'] ?? 0; ?>%
                                </div>
                                <div class="stat-subtitle progress mt-2" style="height: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: <?php echo $submission_stats['completion_percentage'] ?? 0; ?>%"
                                         aria-valuenow="<?php echo $submission_stats['completion_percentage'] ?? 0; ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programs Overview Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">Programs Overview</h5>                        <div>
                            <a href="<?php echo view_url('admin', 'programs.php', ['program_type' => 'assigned']); ?>" class="btn btn-sm btn-success me-2">
                                <i class="fas fa-tasks me-1"></i> View Assigned Programs
                            </a>
                            <a href="<?php echo view_url('admin', 'programs.php', ['program_type' => 'agency']); ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-list me-1"></i> View Agency Programs
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row gx-4">
                            <!-- Assigned Programs -->
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-tasks text-success me-2"></i>Assigned Programs
                                    <span class="badge bg-success ms-2"><?php echo $assigned_count; ?></span>
                                </h6>
                                  <?php if (empty($assigned_programs)): ?>
                                    <div class="alert alert-light">
                                        <i class="fas fa-info-circle me-2"></i>No assigned programs found.
                                        <a href="<?php echo view_url('admin', 'programs/assign_programs.php'); ?>" class="alert-link">Assign programs to agencies</a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Program</th>
                                                    <th>Agency</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $count = 0; ?>                                                <?php foreach ($assigned_programs as $program): ?>
                                                    <?php if ($count < 5): ?>
                                                        <tr>
                                                            <td>
                                                                <a href="<?php echo view_url('admin', 'programs/view_program.php', ['id' => $program['program_id']]); ?>" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                                                </a>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                                                        </tr>
                                                        <?php $count++; ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                      <?php if ($assigned_count > 5): ?>
                                        <div class="text-center mt-2">
                                            <a href="<?php echo view_url('admin', 'programs.php', ['program_type' => 'assigned']); ?>" class="btn btn-sm btn-outline-success">
                                                View All Assigned Programs <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Agency Created Programs -->
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-list text-info me-2"></i>Agency Created Programs
                                    <span class="badge bg-info ms-2"><?php echo $agency_count; ?></span>
                                </h6>
                                
                                <?php if (empty($agency_programs)): ?>
                                    <div class="alert alert-light">
                                        <i class="fas fa-info-circle me-2"></i>No agency-created programs found.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Program</th>
                                                    <th>Agency</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $count = 0; ?>                                                <?php foreach ($agency_programs as $program): ?>
                                                    <?php if ($count < 5): ?>
                                                        <tr>
                                                            <td>
                                                                <a href="<?php echo view_url('admin', 'programs/view_program.php', ['id' => $program['program_id']]); ?>" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                                                </a>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($program['agency_name']); ?></td>
                                                        </tr>
                                                        <?php $count++; ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <?php if ($agency_count > 5): ?>
                                        <div class="text-center mt-2">
                                            <a href="<?php echo view_url('admin', 'programs.php', ['program_type' => 'agency']); ?>" class="btn btn-sm btn-outline-info">
                                                View All Agency Programs <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sector Overview section - conditionally displayed based on MULTI_SECTOR_ENABLED -->
        <?php if (MULTI_SECTOR_ENABLED): ?>
        <div class="row">
            <!-- Sector Overview -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">Sector Overview</h5>
                        <a href="<?php echo view_url('admin', 'sector_details.php'); ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                    </div>
                    <div class="card-body" data-period-content="sectors_section">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Sector</th>
                                        <th>Agencies</th>
                                        <th>Programs</th>
                                        <th>Submissions</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sector_data as $sector): ?>
                                        <tr>
                                            <td><?php echo $sector['sector_name']; ?></td>
                                            <td><?php echo $sector['agency_count']; ?></td>
                                            <td><?php echo $sector['program_count']; ?></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-<?php echo $sector['submission_pct'] >= 100 ? 'success' : 'primary'; ?>" 
                                                         style="width: <?php echo $sector['submission_pct']; ?>%">
                                                        <?php echo $sector['submission_pct']; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($sector['submission_pct'] >= 100): ?>
                                                    <span class="badge bg-success">Complete</span>
                                                <?php elseif ($sector['submission_pct'] >= 75): ?>
                                                    <span class="badge bg-info">Almost Complete</span>
                                                <?php elseif ($sector['submission_pct'] >= 25): ?>
                                                    <span class="badge bg-warning">In Progress</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Early Stage</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
        <div class="row">
            <!-- Forestry Sector Overview -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">Forestry Sector Overview</h5>
                        <a href="<?php echo view_url('admin', 'sector_details.php', ['sector_id' => FORESTRY_SECTOR_ID]); ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                    </div>
                    <div class="card-body" data-period-content="sectors_section">
                        <?php 
                        // Filter sector data to just show Forestry
                        $forestry_data = null;
                        foreach ($sector_data as $sector) {
                            if ($sector['sector_id'] == FORESTRY_SECTOR_ID) {
                                $forestry_data = $sector;
                                break;
                            }
                        }
                        
                        if ($forestry_data): 
                        ?>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Agencies</h5>
                                        <div class="display-6"><?php echo $forestry_data['agency_count']; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Programs</h5>
                                        <div class="display-6"><?php echo $forestry_data['program_count']; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Submission Progress</h5>
                                        <div class="progress mb-3" style="height: 25px;">
                                            <div class="progress-bar bg-<?php echo $forestry_data['submission_pct'] >= 100 ? 'success' : 'primary'; ?>" 
                                                 role="progressbar" style="width: <?php echo $forestry_data['submission_pct']; ?>%" 
                                                 aria-valuenow="<?php echo $forestry_data['submission_pct']; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $forestry_data['submission_pct']; ?>%
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($forestry_data['submission_pct'] >= 100): ?>
                                                <span class="badge bg-success">Complete</span>
                                            <?php elseif ($forestry_data['submission_pct'] >= 75): ?>
                                                <span class="badge bg-info">Almost Complete</span>
                                            <?php elseif ($forestry_data['submission_pct'] >= 25): ?>
                                                <span class="badge bg-warning">In Progress</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Early Stage</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> 
                            No data available for the Forestry sector.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

            <!-- Recent Submissions -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title m-0">Recent Submissions</h5>
                    </div>
                    <div class="card-body" data-period-content="submissions_section">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Agency</th>
                                        <th>Program</th>
                                        <th>Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_submissions)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-3">No recent submissions for this period</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_submissions as $submission): ?>
                                            <tr>
                                                <td><?php echo $submission['agency_name']; ?></td>
                                                <td><?php echo $submission['program_name']; ?></td>
                                                <td><?php echo date('M j, g:i a', strtotime($submission['submission_date'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>        </div>
    </section>
</main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>

