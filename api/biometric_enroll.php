<?php
/**
 * Biometric Enrollment Endpoint
 * W.I.Y Laundry Shop - Payroll Management System
 * 
 * Receives enrollment requests from ESP8266
 * Stores fingerprint template ID and marks user as enrolled
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $fingerprint_id = intval($data['fingerprint_id'] ?? 0);
    $staff_id = intval($data['staff_id'] ?? 0);
    $action = $data['action'] ?? 'enroll';
    
    if (empty($fingerprint_id) || empty($staff_id)) {
        echo json_encode([
            'success' => false,
            'message' => '✗ Missing fingerprint_id or staff_id',
            'code' => 'MISSING_PARAMS'
        ]);
        exit;
    }
    
    if ($action === 'enroll') {
        // Check if user exists
        $check = $conn->prepare("SELECT id, name FROM users WHERE id = ?");
        $check->bind_param("i", $staff_id);
        $check->execute();
        $user_result = $check->get_result();
        
        if ($user_result->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => '✗ Staff member not found',
                'code' => 'STAFF_NOT_FOUND'
            ]);
            exit;
        }
        
        $user_data = $user_result->fetch_assoc();
        $staff_name = $user_data['name'];
        
        // Check if already enrolled
        $enrolled_check = $conn->prepare("
            SELECT id FROM fingerprints 
            WHERE user_id = ? AND is_enrolled = TRUE
        ");
        $enrolled_check->bind_param("i", $staff_id);
        $enrolled_check->execute();
        
        if ($enrolled_check->get_result()->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => "✗ {$staff_name} is already enrolled",
                'code' => 'ALREADY_ENROLLED'
            ]);
            exit;
        }
        
        // Insert or update fingerprint record
        $stmt = $conn->prepare("
            INSERT INTO fingerprints 
            (user_id, fingerprint_id, is_enrolled, enrollment_status)
            VALUES (?, ?, TRUE, 'completed')
            ON DUPLICATE KEY UPDATE 
            is_enrolled = TRUE, 
            enrollment_status = 'completed',
            enrollment_date = NOW()
        ");
        
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'message' => '✗ Database error: ' . $conn->error,
                'code' => 'DB_ERROR'
            ]);
            exit;
        }
        
        $stmt->bind_param("ii", $staff_id, $fingerprint_id);
        
        if ($stmt->execute()) {
            // Update user biometric status
            $update = $conn->prepare("
                UPDATE users 
                SET biometric_enabled = TRUE, 
                    biometric_status = 'enrolled'
                WHERE id = ?
            ");
            $update->bind_param("i", $staff_id);
            $update->execute();
            
            // Record in biometric history
            $history = $conn->prepare("
                INSERT INTO biometric_history 
                (user_id, fingerprint_id, action, status) 
                VALUES (?, ?, 'enroll', 'success')
            ");
            $history->bind_param("ii", $staff_id, $fingerprint_id);
            $history->execute();
            
            echo json_encode([
                'success' => true,
                'message' => "✓ Fingerprint enrolled successfully for {$staff_name}",
                'staff_id' => $staff_id,
                'staff_name' => $staff_name,
                'fingerprint_id' => $fingerprint_id,
                'code' => 'ENROLLMENT_SUCCESS'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '✗ Failed to enroll fingerprint',
                'code' => 'ENROLLMENT_FAILED'
            ]);
        }
        
        $check->close();
        $stmt->close();
        
    } else if ($action === 'unenroll') {
        // Remove fingerprint enrollment
        $delete = $conn->prepare("
            DELETE FROM fingerprints 
            WHERE user_id = ?
        ");
        $delete->bind_param("i", $staff_id);
        
        if ($delete->execute()) {
            // Update user biometric status
            $update = $conn->prepare("
                UPDATE users 
                SET biometric_enabled = FALSE, 
                    biometric_status = 'not_enrolled'
                WHERE id = ?
            ");
            $update->bind_param("i", $staff_id);
            $update->execute();
            
            echo json_encode([
                'success' => true,
                'message' => '✓ Fingerprint unenrolled successfully',
                'code' => 'UNENROLLMENT_SUCCESS'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '✗ Failed to unenroll fingerprint',
                'code' => 'UNENROLLMENT_FAILED'
            ]);
        }
        
        $delete->close();
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => '✗ Invalid action. Use "enroll" or "unenroll"',
            'code' => 'INVALID_ACTION'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => '✗ Invalid request method. Use POST only.',
        'code' => 'INVALID_METHOD'
    ]);
}
?>
