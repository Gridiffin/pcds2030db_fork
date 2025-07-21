<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the program: <strong id="program-name-display"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="delete_program.php" method="post" id="delete-program-form">
                    <input type="hidden" name="program_id" id="program-id-input">
                    <input type="hidden" name="confirm_delete" value="1">
                    <button type="submit" class="btn btn-danger">Delete Program</button>
                </form>
            </div>
        </div>
    </div>
</div> 