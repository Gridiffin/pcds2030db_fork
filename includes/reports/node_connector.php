<?php
/**
 * PHP connector for Node.js report generation service
 *
 * This file handles communication between the PHP backend and the Node.js microservice.
 */

/**
 * Send report generation request to Node.js service
 * 
 * @param int $period_id The reporting period ID
 * @param array $period Period information
 * @param array $sectors Sectors with program data
 * @return array Response from the Node.js service
 */
function send_report_request($period_id, $period, $sectors) {
    $service_url = 'http://localhost:3000/generate-report';
    
    // Prepare data for request
    $post_data = json_encode([
        'periodId' => $period_id,
        'period' => $period,
        'sectors' => $sectors
    ]);
    
    // Set up cURL request
    $ch = curl_init($service_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($post_data)
    ]);
    
    // Execute request and get response
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for errors
    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return [
            'success' => false, 
            'error' => 'Connection to report service failed: ' . curl_error($ch)
        ];
    }
    
    curl_close($ch);
    
    // Decode JSON response
    $result = json_decode($response, true);
    
    // If status code is not 200 or JSON couldn't be decoded
    if ($status_code != 200 || !$result) {
        error_log('Report service error. Status: ' . $status_code . ', Response: ' . $response);
        return [
            'success' => false,
            'error' => 'Report service returned an error. Status: ' . $status_code
        ];
    }
    
    return $result;
}

/**
 * Copy generated report from Node.js service to local storage
 * 
 * @param string $filename Filename of the generated report
 * @param string $destination Local destination path
 * @return bool True if successfully copied, false otherwise
 */
function retrieve_generated_report($filename, $destination) {
    $service_url = 'http://localhost:3000/download/' . $filename;
    
    // Create directory if it doesn't exist
    $dir = dirname($destination);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    // Download the file
    $fp = fopen($destination, 'w+');
    
    $ch = curl_init($service_url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $success = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        fclose($fp);
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    fclose($fp);
    
    // Check if file was successfully downloaded
    if ($status_code != 200 || !file_exists($destination) || filesize($destination) == 0) {
        error_log('Failed to download report file. Status: ' . $status_code);
        return false;
    }
    
    return true;
}