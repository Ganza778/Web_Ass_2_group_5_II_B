<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = "Unauthorized access";
    header('Location: index.php?page=login');
    exit();
}

// Get parameters
$homestay_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (!$homestay_id || !in_array($action, ['verify', 'reject'])) {
    $_SESSION['error'] = "Invalid request";
    header('Location: index.php?page=admin-dashboard');
    exit();
}

// Use transaction to ensure data integrity
$conn->begin_transaction();

try {
    if ($action == 'verify') {
        // Verify the homestay
        $sql = "UPDATE homestays SET verified = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $homestay_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to verify homestay");
        }
        
        $_SESSION['message'] = "Home-stay verified successfully!";
        
    } elseif ($action == 'reject') {
        // First check if homestay exists
        $check_sql = "SELECT id, title FROM homestays WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $homestay_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Home-stay not found");
        }
        
        $homestay = $result->fetch_assoc();
        
        // Delete the homestay (bookings will cascade delete due to FK)
        $delete_sql = "DELETE FROM homestays WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $homestay_id);
        
        if (!$delete_stmt->execute()) {
            throw new Exception("Failed to delete homestay");
        }
        
        $_SESSION['message'] = "Home-stay '" . $homestay['title'] . "' has been rejected and removed.";
    }
    
    // Commit transaction
    $conn->commit();
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect back
header('Location: index.php?page=admin-dashboard');
exit();
?>