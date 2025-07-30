// Unsubmit finalized submission (focal users only)
function unsubmitSubmission(submissionId, btn) {
    if (!confirm('Are you sure you want to return this finalized submission to draft status?')) return;
    btn.disabled = true;
    fetch(APP_URL + '/app/views/agency/ajax/unsubmit_submission.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'submission_id=' + encodeURIComponent(submissionId)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Submission returned to draft status.', 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast('Error', data.error || 'Failed to unsubmit.', 'danger');
            btn.disabled = false;
        }
    })
    .catch(() => {
        showToast('Error', 'Network error.', 'danger');
        btn.disabled = false;
    });
}
