# Program Attachments Upload Directory

This directory stores uploaded program attachments.

## Structure
- `/programs/attachments/{program_id}/` - Files organized by program
- Files are stored with secure naming convention: `{timestamp}_{hash}_{sanitized_filename}`

## Security
- Direct access blocked by .htaccess
- Files accessed only through PHP download handler
- All uploads validated and logged

## Maintenance
- Orphaned files cleaned up by scheduled maintenance
- File operations logged in audit_logs table
