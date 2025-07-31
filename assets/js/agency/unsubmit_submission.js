// Unsubmit finalized submission (focal users only)
function unsubmitSubmission(submissionId, btn) {
    if (!confirm('Are you sure you want to return this finalized submission to draft status?')) return;
    
    // Debug: Check if APP_URL is defined
    console.log('APP_URL:', window.APP_URL);
    
    // Get the base URL for AJAX requests
    let appUrl = window.APP_URL;
    if (!appUrl) {
        // Fallback: Try to get from document location
        const currentPath = window.location.pathname;
        // Remove the current page path to get the base URL
        const basePath = currentPath.replace(/\/app\/views\/.*$/, '');
        appUrl = window.location.origin + basePath;
        console.log('Fallback APP_URL:', appUrl);
    }
    
    if (!appUrl) {
        showToast('Error', 'Configuration error: APP_URL not defined', 'danger');
        return;
    }
    
    btn.disabled = true;
    fetch(appUrl + '/app/ajax/unsubmit_submission.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'submission_id=' + encodeURIComponent(submissionId)
    })
    .then(res => {
        console.log('Response status:', res.status);
        console.log('Response headers:', res.headers);
        return res.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showToast('Success', 'Submission returned to draft status.', 'success');
            console.log('Unsubmit successful, reloading page in 1.2 seconds...');
            setTimeout(() => {
                console.log('Reloading page now...');
                window.location.reload();
            }, 1200);
        } else {
            let errorMsg = data.error || 'Failed to unsubmit.';
            if (data.debug) {
                console.log('Debug info:', data.debug);
                errorMsg += ' (Check console for details)';
            }
            showToast('Error', errorMsg, 'danger');
            btn.disabled = false;
        }
    })
    .catch((error) => {
        console.error('Fetch error:', error);
        showToast('Error', 'Network error: ' + error.message, 'danger');
        btn.disabled = false;
    });
}

// Ensure the function is available globally
window.unsubmitSubmission = unsubmitSubmission;

// Debug: Log when script is loaded
console.log('Unsubmit submission script loaded');
console.log('Current APP_URL:', window.APP_URL);
