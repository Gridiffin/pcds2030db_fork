<?php
/**
 * Admin Edit Program
 * 
 * Allows admin users to edit program details.
 */

require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_functions.php'; // Assuming admin helper functions here

// Verify user is admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Get program ID from query parameter
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($program_id <= 0) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_programs.php');
    exit;
}

// Initialize variables
$message = '';
$messageType = 'info';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $program_name = trim($_POST['program_name'] ?? '');
    $owner_agency_id = intval($_POST['owner_agency_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if (empty($program_name)) {
        $message = 'Program name is required.';
        $messageType = 'danger';
    } elseif ($owner_agency_id <= 0) {
        $message = 'Valid owner agency is required.';
        $messageType = 'danger';
    } else {
        // Update program in database
        $query = "UPDATE programs SET program_name = ?, owner_agency_id = ?, description = ? WHERE program_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sisi', $program_name, $owner_agency_id, $description, $program_id);
        if ($stmt->execute()) {
            $message = 'Program updated successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to update program: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
}

// Fetch program data for form
$query = "SELECT * FROM programs WHERE program_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $program_id);
$stmt->execute();
$result = $stmt->get_result();
$program = $result->fetch_assoc();
$stmt->close();

if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Fetch list of agencies for owner selection
$agencies = [];
$agencyQuery = "SELECT agency_id, agency_name FROM agencies ORDER BY agency_name";
$agencyResult = $conn->query($agencyQuery);
if ($agencyResult) {
    while ($row = $agencyResult->fetch_assoc()) {
        $agencies[] = $row;
    }
}

// Set page title
$pageTitle = 'Edit Program';

require_once '../layouts/header.php';
require_once '../layouts/admin_nav.php';

// Set up header variables
$title = "Edit Program";
$subtitle = "Modify program details";
$headerStyle = 'light';
$actions = [
    [
        'url' => APP_URL . '/views/admin/programs.php',
        'text' => 'Back to Programs',
        'icon' => 'fas fa-arrow-left',
        'class' => 'btn-secondary'
    ]
];

require_once '../../includes/dashboard_header.php';
?>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <div><?php echo htmlspecialchars($message); ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<form method="post" action="edit_program.php?id=<?php echo $program_id; ?>" class="mb-4">
    <div class="mb-3">
        <label for="program_name" class="form-label">Program Name</label>
        <input type="text" class="form-control" id="program_name" name="program_name" value="<?php echo htmlspecialchars($program['program_name']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="owner_agency_id" class="form-label">Owner Agency</label>
        <select class="form-select" id="owner_agency_id" name="owner_agency_id" required>
            <option value="">Select an agency</option>
            <?php foreach ($agencies as $agency): ?>
                <option value="<?php echo $agency['agency_id']; ?>" <?php echo ($agency['agency_id'] == $program['owner_agency_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($agency['agency_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($program['description']); ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Update Program</button>
</form>

<?php
require_once '../layouts/footer.php';
?>
