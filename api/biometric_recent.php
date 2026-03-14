<?php
session_start();

header('Content-Type: application/json');

include '../config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Owner') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if ($limit < 1) {
    $limit = 10;
} elseif ($limit > 50) {
    $limit = 50;
}

$stmt = $conn->prepare("SELECT bh.id, bh.user_id, u.name, bh.action, bh.status, bh.timestamp
    FROM biometric_history bh
    JOIN users u ON u.id = bh.user_id
    ORDER BY bh.timestamp DESC
    LIMIT ?");

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
    exit;
}

$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

$stmt->close();

echo json_encode([
    'success' => true,
    'data' => $rows
]);
