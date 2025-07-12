<?php
// This endpoint is deprecated due to sector functionality removal.
http_response_code(410);
echo json_encode(['error' => 'Sectors functionality has been removed from the system.']);
exit;
