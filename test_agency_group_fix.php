<?php
/**
 * Test Agency Group Filtering Fix
 * 
 * This script tests the agency group filtering functionality without authentication.
 */

// Include necessary files
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/admins/users.php';

// Get agency groups to test the fix
$agency_groups = get_all_agency_groups($conn);

// Get all sectors for testing
$sectors_query = "SELECT sector_id, sector_name FROM sectors ORDER BY sector_name";
$sectors_result = $conn->query($sectors_query);
$sectors = [];
while ($row = $sectors_result->fetch_assoc()) {
    $sectors[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Agency Group Filtering</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Test Agency Group Filtering Fix</h2>
        <p class="text-muted">Test the sector-to-agency group filtering functionality</p>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="sector_id" class="form-label">Sector *</label>
                    <select class="form-select" id="sector_id" name="sector_id">
                        <option value="">Select Sector</option>
                        <?php foreach($sectors as $sector): ?>
                            <option value="<?php echo $sector['sector_id']; ?>"><?php echo htmlspecialchars($sector['sector_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="agency_group_id" class="form-label">Agency Group</label>
                    <select class="form-select" id="agency_group_id" name="agency_group_id">
                        <option value="">Select Agency Group (Optional)</option>
                        <?php foreach($agency_groups as $group): ?>
                            <option value="<?php echo $group['agency_group_id']; ?>" data-sector-id="<?php echo $group['sector_id']; ?>">
                                <?php echo htmlspecialchars($group['group_name']); ?> (Sector ID: <?php echo $group['sector_id']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info">
            <h5>Test Instructions:</h5>
            <ol>
                <li>Initially, you should see all agency groups in the dropdown</li>
                <li>Select "Forestry" from the Sector dropdown</li>
                <li>The Agency Group dropdown should show only groups that belong to the Forestry sector (STIDC, SFC, FDS)</li>
                <li>Clear the sector selection (select "Select Sector")</li>
                <li>All agency groups should be visible again</li>
            </ol>
        </div>
        
        <div class="alert alert-success">
            <h5>Debug Information:</h5>
            <p><strong>Available Agency Groups:</strong></p>
            <pre><?php echo json_encode($agency_groups, JSON_PRETTY_PRINT); ?></pre>
            <p><strong>Available Sectors:</strong></p>
            <pre><?php echo json_encode($sectors, JSON_PRETTY_PRINT); ?></pre>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sectorId = document.getElementById('sector_id');
            const agencyGroupId = document.getElementById('agency_group_id');
            
            // Function to update agency group options based on sector
            function updateAgencyGroupOptions() {
                const selectedSectorId = sectorId.value;
                const agencyGroups = <?php echo json_encode($agency_groups); ?>;
                
                console.log('Selected Sector ID:', selectedSectorId);
                console.log('Available Agency Groups:', agencyGroups);
                
                // Clear current options except first one
                while (agencyGroupId.options.length > 1) {
                    agencyGroupId.remove(1);
                }
                
                // Add filtered options
                agencyGroups.forEach(group => {
                    console.log(`Checking group ${group.group_name}: sector_id=${group.sector_id}, selected=${selectedSectorId}`);
                    
                    // If no sector is selected, show all groups
                    // If sector is selected, only show groups that belong to that sector
                    if (!selectedSectorId || parseInt(group.sector_id) === parseInt(selectedSectorId)) {
                        console.log(`Adding group: ${group.group_name}`);
                        const option = new Option(group.group_name, group.agency_group_id);
                        agencyGroupId.add(option);
                    } else {
                        console.log(`Filtering out group: ${group.group_name}`);
                    }
                });
                
                // Enable the dropdown
                agencyGroupId.disabled = false;
                
                console.log('Updated agency group options. Total options:', agencyGroupId.options.length);
            }
            
            // Listen for sector changes
            sectorId.addEventListener('change', function() {
                console.log('Sector changed to:', this.value);
                updateAgencyGroupOptions();
            });
            
            // Initial setup
            updateAgencyGroupOptions();
        });
    </script>
</body>
</html>
