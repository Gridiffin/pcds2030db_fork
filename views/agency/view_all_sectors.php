<?php
/**
 * View All Sectors Programs
 * 
 * Page for agency users to view programs from all sectors (read-only).
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'View All Sectors Programs';

// Get current reporting period
$current_period = get_current_reporting_period();

// Add period_id handling for historical views
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : ($current_period['period_id'] ?? null);
$viewing_period = $period_id ? get_reporting_period($period_id) : $current_period;

// Get all sectors programs
$all_programs = get_all_sectors_programs($period_id);

// Get current agency's sector
$current_sector_id = $_SESSION['sector_id'];

// Additional styles
$additionalStyles = [
    APP_URL . '/assets/css/custom/agency.css'
];

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/agency/view_programs.js',
    APP_URL . '/assets/js/period_selector.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">All Sectors Programs</h1>
        <p class="text-muted">View programs across all sectors (read-only)</p>
    </div>
</div>

<!-- Period Selector Component -->
<?php require_once '../../includes/period_selector.php'; ?>

<div data-period-content="programs_content">
    <?php if (empty($all_programs) || isset($all_programs['error'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <?php echo isset($all_programs['error']) ? $all_programs['error'] : 'No programs found across sectors.'; ?>
        </div>
    <?php else: ?>
        <!-- Sector Tabs -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" id="sectorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">
                            All Sectors
                        </button>
                    </li>
                    
                    <?php 
                    // Get unique sectors
                    $sectors = [];
                    foreach ($all_programs as $program) {
                        if (!isset($sectors[$program['sector_id']])) {
                            $sectors[$program['sector_id']] = $program['sector_name'];
                        }
                    }
                    
                    // Display sector tabs
                    foreach ($sectors as $id => $name): 
                        $isCurrentSector = ($id == $current_sector_id);
                    ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $isCurrentSector ? 'current-sector' : ''; ?>" 
                                    id="sector-<?php echo $id; ?>-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#sector-<?php echo $id; ?>" 
                                    type="button" role="tab">
                                <?php echo $name; ?>
                                <?php if ($isCurrentSector): ?>
                                    <span class="badge bg-primary ms-1">Your Sector</span>
                                <?php endif; ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="card-body">
                <div class="tab-content" id="sectorTabsContent">
                    <!-- All Sectors Tab -->
                    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Program Name</th>
                                        <th>Sector</th>
                                        <th>Agency</th>
                                        <th>Status</th>
                                        <th>Achievement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_programs as $program): ?>
                                        <tr class="<?php echo ($program['sector_id'] == $current_sector_id) ? 'current-sector-row' : ''; ?>">
                                            <td>
                                                <strong><?php echo $program['program_name']; ?></strong>
                                                <?php if (!empty($program['description'])): ?>
                                                    <div class="small text-muted"><?php echo substr($program['description'], 0, 100); ?><?php echo strlen($program['description']) > 100 ? '...' : ''; ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $program['sector_name']; ?></td>
                                            <td><?php echo $program['agency_name']; ?></td>
                                            <td>
                                                <?php if (isset($program['status']) && $program['status']): ?>
                                                    <?php
                                                        $status_class = 'secondary';
                                                        switch ($program['status']) {
                                                            case 'on-track': $status_class = 'success'; break;
                                                            case 'delayed': $status_class = 'warning'; break;
                                                            case 'completed': $status_class = 'primary'; break;
                                                            case 'not-started': $status_class = 'secondary'; break;
                                                        }
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo ucfirst(str_replace('-', ' ', $program['status'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">Not Reported</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($program['achievement']) && $program['achievement']): ?>
                                                    <?php echo $program['achievement']; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Not reported</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Individual Sector Tabs -->
                    <?php foreach ($sectors as $id => $name): ?>
                        <div class="tab-pane fade" id="sector-<?php echo $id; ?>" role="tabpanel">
                            <h5 class="mb-3"><?php echo $name; ?> Sector Programs</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Program Name</th>
                                            <th>Agency</th>
                                            <th>Status</th>
                                            <th>Target</th>
                                            <th>Achievement</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $sectorPrograms = array_filter($all_programs, function($p) use ($id) {
                                            return $p['sector_id'] == $id;
                                        });
                                        
                                        foreach ($sectorPrograms as $program): 
                                        ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo $program['program_name']; ?></strong>
                                                    <?php if (!empty($program['description'])): ?>
                                                        <div class="small text-muted"><?php echo $program['description']; ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $program['agency_name']; ?></td>
                                                <td>
                                                    <?php if (isset($program['status']) && $program['status']): ?>
                                                        <?php
                                                            $status_class = 'secondary';
                                                            switch ($program['status']) {
                                                                case 'on-track': $status_class = 'success'; break;
                                                                case 'delayed': $status_class = 'warning'; break;
                                                                case 'completed': $status_class = 'primary'; break;
                                                                case 'not-started': $status_class = 'secondary'; break;
                                                            }
                                                        ?>
                                                        <span class="badge bg-<?php echo $status_class; ?>">
                                                            <?php echo ucfirst(str_replace('-', ' ', $program['status'])); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark">Not Reported</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($program['target']) && $program['target']): ?>
                                                        <?php echo $program['target']; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not reported</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($program['achievement']) && $program['achievement']): ?>
                                                        <?php echo $program['achievement']; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not reported</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
