<?php
/**
 * Test page for dhtmlxGantt integration
 * This page tests the Gantt chart functionality
 */

// Mock initiative data for testing
$test_initiative_id = 1;
$test_initiative = [
    'id' => 1,
    'name' => 'Test Initiative',
    'start_date' => '2024-01-01',
    'end_date' => '2025-12-31'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dhtmlxGantt Test - PCDS2030 Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/components/dhtmlxgantt.css">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">dhtmlxGantt Test Page</h1>
                
                <!-- dhtmlxGantt Chart Section -->
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">
                            <i class="fas fa-chart-gantt me-2"></i>Initiative Timeline Test
                        </h5>
                        <div class="gantt-legend">
                            <div class="gantt-legend-item">
                                <span class="gantt-legend-color" style="background-color: #17a2b8;"></span>
                                <span>Completed</span>
                            </div>
                            <div class="gantt-legend-item">
                                <span class="gantt-legend-color" style="background-color: #28a745;"></span>
                                <span>On Target</span>
                            </div>
                            <div class="gantt-legend-item">
                                <span class="gantt-legend-color" style="background-color: #ffc107;"></span>
                                <span>At Risk</span>
                            </div>
                            <div class="gantt-legend-item">
                                <span class="gantt-legend-color" style="background-color: #dc3545;"></span>
                                <span>Off Target</span>
                            </div>
                            <div class="gantt-legend-item">
                                <span class="gantt-legend-color" style="background-color: #6c757d;"></span>
                                <span>Not Started</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="gantt_here" class="gantt_container">
                            <div class="gantt-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="ms-2">Loading timeline...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h5>Test Information</h5>
                    <ul>
                        <li>Initiative ID: <?php echo $test_initiative_id; ?></li>
                        <li>API URL: <code>app/api/simple_gantt_data.php?initiative_id=<?php echo $test_initiative_id; ?></code></li>
                        <li>dhtmlxGantt Version: Edge (from CDN)</li>
                        <li>Columns: Number, Item (swapped order)</li>
                        <li>Data: Program names and target names displayed</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Load dhtmlxGantt -->
    <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
    <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
    <script src="assets/js/components/dhtmlxgantt.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const initiativeId = <?php echo $test_initiative_id; ?>;
        const apiUrl = "app/api/simple_gantt_data.php?initiative_id=" + initiativeId;
        
        console.log('Page loaded, initializing dhtmlxGantt...');
        console.log('Initiative ID:', initiativeId);
        console.log('API URL:', apiUrl);
        
        // Check if dhtmlxGantt is loaded
        if (typeof gantt === 'undefined') {
            console.error('dhtmlxGantt library not loaded!');
            document.getElementById('gantt_here').innerHTML = 
                '<div class="alert alert-danger m-3">dhtmlxGantt library failed to load from CDN</div>';
            return;
        } else {
            console.log('dhtmlxGantt library loaded successfully');
        }
        
        // Check if our custom class is loaded
        if (typeof PCDSGanttChart === 'undefined') {
            console.error('PCDSGanttChart class not loaded!');
            document.getElementById('gantt_here').innerHTML = 
                '<div class="alert alert-danger m-3">PCDSGanttChart class not found. Check dhtmlxgantt.js file.</div>';
            return;
        } else {
            console.log('PCDSGanttChart class loaded successfully');
        }
        
        // Test API endpoint first
        console.log('Testing API endpoint...');
        fetch(apiUrl)
            .then(response => {
                console.log('API Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('API Response data:', data);
                
                // Now initialize gantt chart
                try {
                    console.log('Initializing gantt chart...');
                    const ganttChart = new PCDSGanttChart('gantt_here', apiUrl);
                    console.log('dhtmlxGantt initialized successfully');
                } catch (error) {
                    console.error('Error initializing dhtmlxGantt:', error);
                    document.getElementById('gantt_here').innerHTML = 
                        '<div class="alert alert-danger m-3">Error initializing Gantt chart: ' + error.message + '</div>';
                }
            })
            .catch(error => {
                console.error('API Test failed:', error);
                document.getElementById('gantt_here').innerHTML = 
                    '<div class="alert alert-danger m-3">API Test failed: ' + error.message + 
                    '<br><br>Check console for details. Make sure the API endpoint exists and is accessible.</div>';
            });
    });
    </script>
</body>
</html>
