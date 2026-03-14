<?php
session_start();

header('Content-Type: application/json');

include '../config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$name = $_SESSION['name'] ?? 'User';

$latest = null;
$history_stmt = $conn->prepare("SELECT action, status, timestamp FROM biometric_history
    WHERE user_id = ?
    ORDER BY timestamp DESC
    LIMIT 1");
if ($history_stmt) {
    $history_stmt->bind_param("i", $user_id);
    $history_stmt->execute();
    $history_result = $history_stmt->get_result();
    if ($history_result->num_rows === 1) {
        $latest = $history_result->fetch_assoc();
    }
    $history_stmt->close();
}

$today = date('Y-m-d');
$attendance = null;
$attendance_stmt = $conn->prepare("SELECT time_in, time_out FROM attendance
    WHERE user_id = ? AND date = ?
    LIMIT 1");
if ($attendance_stmt) {
    $attendance_stmt->bind_param("is", $user_id, $today);
    $attendance_stmt->execute();
    $attendance_result = $attendance_stmt->get_result();
    if ($attendance_result->num_rows === 1) {
        $attendance = $attendance_result->fetch_assoc();
    }
    $attendance_stmt->close();
}

$welcome_message = null;
if ($latest && ($latest['status'] ?? '') === 'success') {
    $last_ts = strtotime($latest['timestamp']);
    if ($last_ts && (time() - $last_ts) <= 120) {
        $welcome_message = "Welcome {$name}!";
    }
}

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user_id,
        'name' => $name
    ],
    'last_scan' => $latest,
    'attendance_today' => $attendance,
    'welcome_message' => $welcome_message
]);
