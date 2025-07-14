<?php
/**
 * Agency Session Fix Verification Test
 * Tests if agency_id session handling is working correctly
 */

// Include necessary files
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/lib/db_connect.php';
require_once __DIR__ . '/app/lib/session.php';
require_once __DIR__ . '/app/lib/functions.php';
require_once __DIR__ . '/app/lib/agencies/index.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$agency_id = get_agency_id(); // Using the fixed function
$session_agency_id = $_SESSION['agency_id'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Agency Session Fix Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Agency Session Fix Verification Test</h2>
        
        <div class="alert alert-info">
            <strong>Session Information:</strong><br>
            - User ID: <?php echo $_SESSION['user_id'] ?? 'N/A'; ?><br>
            - Session Agency ID: <?php echo $session_agency_id ?? 'N/A'; ?><br>
            - get_agency_id() result: <?php echo $agency_id ?? 'N/A'; ?><br>
            - Role: <?php echo $_SESSION['role'] ?? 'N/A'; ?><br>
        </div>
        
        <?php if ($agency_id === $session_agency_id && $agency_id): ?>
            <div class="alert alert-success">
                ✅ <strong>Session Fix Working!</strong> get_agency_id() matches $_SESSION['agency_id']
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                ❌ <strong>Session Fix Failed!</strong> Mismatch between get_agency_id() and $_SESSION['agency_id']
            </div>
        <?php endif; ?>
        
        <h3>Database Query Test</h3>
        <?php if ($agency_id): ?>
            <?php
            // Test the database query that was failing
            $query = "SELECT COUNT(*) as program_count 
                     FROM program_agency_assignments paa 
                     JOIN programs p ON paa.program_id = p.program_id 
                     WHERE paa.agency_id = ? AND paa.is_active = 1 AND p.is_deleted = 0";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $agency_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = $result->fetch_assoc()['program_count'];
            ?>
            
            <div class="alert alert-info">
                Found <?php echo $count; ?> programs for agency ID <?php echo $agency_id; ?>
            </div>
            
            <?php if ($count > 0): ?>
                <div class="alert alert-success">
                    ✅ Database queries should now work correctly
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    ⚠️ No programs found - this might be expected if agency has no assigned programs
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-danger">
                ❌ Cannot test database queries - agency_id is null
            </div>
        <?php endif; ?>
        
        <h3>Test Links</h3>
        <div class="row">
            <div class="col-md-6">
                <a href="app/views/agency/programs/view_programs.php" class="btn btn-primary mb-2 w-100">Test Full Programs Page</a>
                <a href="app/views/agency/programs/create_program.php" class="btn btn-success mb-2 w-100">Test Create Program</a>
                <a href="app/views/agency/dashboard/dashboard.php" class="btn btn-info mb-2 w-100">Test Dashboard</a>
            </div>
            <div class="col-md-6">
                <a href="test_session.php" class="btn btn-secondary mb-2 w-100">Session Test</a>
                <a href="simple_programs_test.php" class="btn btn-warning mb-2 w-100">Simple Programs Test</a>
                <a href="login.php" class="btn btn-danger mb-2 w-100">Logout</a>
            </div>
        </div>
        
        <hr>
        <div class="mt-3">
            <h4>Expected Behavior After Fix:</h4>
            <ul>
                <li>✅ get_agency_id() should return the same value as $_SESSION['agency_id']</li>
                <li>✅ Database queries should find programs for the correct agency</li>
                <li>✅ Create New Program button should not infinitely load</li>
                <li>✅ View Details buttons should not infinitely load</li>
                <li>✅ Dashboard should load agency-specific data</li>
            </ul>
        </div>
    </div>
    
    <script>
        console.log('Agency Session Fix Verification Page Loaded');
        console.log('Session data:', {
            user_id: '<?php echo $_SESSION['user_id'] ?? 'N/A'; ?>',
            session_agency_id: '<?php echo $session_agency_id ?? 'N/A'; ?>',
            get_agency_id: '<?php echo $agency_id ?? 'N/A'; ?>',
            role: '<?php echo $_SESSION['role'] ?? 'N/A'; ?>'
        });
    </script>
</body>
</html>
