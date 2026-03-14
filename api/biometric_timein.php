<?php
/**
 * Biometric Time-In/Time-Out Endpoint
 * W.I.Y Laundry Shop - Payroll Management System
 * 
 * Receives fingerprint scan data from ESP8266
 * Records time-in or time-out in attendance table
 * Updates biometric history for audit trail
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

include '../config.php';

// Log request for debugging
$log_file = '../logs/biometric.log';
if (!is_dir('../logs')) {
    mkdir('../logs', 0755, true);
}

$request_log = date('Y-m-d H:i:s') . " - " . json_encode($_POST ?? json_decode(file_get_contents('php://input'), true)) . "\n";
file_put_contents($log_file, $request_log, FILE_APPEND);

// Main logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $fingerprint_id = intval($data['fingerprint_id'] ?? 0);
    $action = $data['action'] ?? 'time_in';
    $timestamp = $data['timestamp'] ?? date('Y-m-d H:i:s');
    $device_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    if ($fingerprint_id === 0) {
        echo json_encode([
            'success' => false,
            'message' => '✗ Invalid fingerprint ID',
            'code' => 'INVALID_FINGERPRINT'
        ]);
        exit;
    }
    
    // Find user by fingerprint_id
    $stmt = $conn->prepare("
        SELECT u.id, u.name, f.user_id, f.is_enrolled
        FROM fingerprints f 
        JOIN users u ON f.user_id = u.id 
        WHERE f.fingerprint_id = ? AND f.is_enrolled = TRUE
        LIMIT 1
    ");
    
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => '✗ Database error: ' . $conn->error,
            'code' => 'DB_ERROR'
        ]);
        exit;
    }
    
    $stmt->bind_param("i", $fingerprint_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];
        $name = $user['name'];
        
        $today = date('Y-m-d');
        $current_time = date('H:i:s');
        
        if ($action === 'time_in') {
            // Check if already timed in today
            $check = $conn->prepare("
                SELECT id FROM attendance 
                WHERE user_id = ? AND date = ? AND time_in IS NOT NULL
            ");
            $check->bind_param("is", $user_id, $today);
            $check->execute();
            
            if ($check->get_result()->num_rows === 0) {
                // Record time in
                $insert = $conn->prepare("
                    INSERT INTO attendance (user_id, date, time_in) 
                    VALUES (?, ?, ?)
                ");
                $insert->bind_param("iss", $user_id, $today, $current_time);
                
                if ($insert->execute()) {
                    // Record biometric history
                    $history = $conn->prepare("
                        INSERT INTO biometric_history 
                        (user_id, fingerprint_id, action, status, ip_address) 
                        VALUES (?, ?, ?, 'success', ?)
                    ");
                    $history->bind_param("iiss", $user_id, $fingerprint_id, $action, $device_ip);
                    $history->execute();
                    
                    // Update last biometric checkin
                    $update = $conn->prepare("
                        UPDATE users 
                        SET last_biometric_checkin = NOW() 
                        WHERE id = ?
                    ");
                    $update->bind_param("i", $user_id);
                    $update->execute();
                    
                    echo json_encode([
                        'success' => true,
                        'message' => "✓ Time-in recorded for {$name}",
                        'user_id' => $user_id,
                        'name' => $name,
                        'time' => $current_time,
                        'action' => 'time_in',
                        'timestamp' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => '✗ Failed to record time-in',
                        'code' => 'INSERT_FAILED'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "{$name} already timed in today",
                    'user_id' => $user_id,
                    'name' => $name,
                    'code' => 'ALREADY_TIMED_IN'
                ]);
            }
        } elseif ($action === 'time_out') {
            // Record time out
            $update = $conn->prepare("
                UPDATE attendance 
                SET time_out = ? 
                WHERE user_id = ? AND date = ?
            ");
            $update->bind_param("sis", $current_time, $user_id, $today);
            
            if ($update->execute()) {
                // Record biometric history
                $history = $conn->prepare("
                    INSERT INTO biometric_history 
                    (user_id, fingerprint_id, action, status, ip_address) 
                    VALUES (?, ?, ?, 'success', ?)
                ");
                $history->bind_param("iiss", $user_id, $fingerprint_id, $action, $device_ip);
                $history->execute();
                
                // Update last biometric checkin
                $update2 = $conn->prepare("
                    UPDATE users 
                    SET last_biometric_checkin = NOW() 
                    WHERE id = ?
                ");
                $update2->bind_param("i", $user_id);
                $update2->execute();
                
                echo json_encode([
                    'success' => true,
                    'message' => "✓ Time-out recorded for {$name}",
                    'user_id' => $user_id,
                    'name' => $name,
                    'time' => $current_time,
                    'action' => 'time_out',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '✗ Failed to record time-out',
                    'code' => 'UPDATE_FAILED'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => '✗ Invalid action. Use "time_in" or "time_out"',
                'code' => 'INVALID_ACTION'
            ]);
        }
    } else {
        // Fingerprint not found or not enrolled
        echo json_encode([
            'success' => false,
            'message' => '✗ Fingerprint not registered or not enrolled',
            'code' => 'FINGERPRINT_NOT_REGISTERED'
        ]);
        
        // Log failed attempt
        $fail_log = $log_file;
        file_put_contents($fail_log, date('Y-m-d H:i:s') . " - FAILED: Fingerprint $fingerprint_id not found\n", FILE_APPEND);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => '✗ Invalid request method. Use POST only.',
        'code' => 'INVALID_METHOD'
    ]);
}
?>
