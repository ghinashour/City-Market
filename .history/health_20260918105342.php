<?php
header('Content-Type: application/json');

require_once __DIR__ . '/includes/db.php';

echo json_encode([
    'status' => 'ok',
    'service' => 'city-market',
    'time' => gmdate('c')
]);
?>