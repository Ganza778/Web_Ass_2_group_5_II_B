<?php
require_once 'config.php';

// Check if user is logged in and is host
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'host') {
    $_SESSION['error'] = "Unauthorized access";
    header('Location: index.php?page=login');
    exit();
}

// Get parameters
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (!$booking_id || !in_array($action, ['confirm', 'cancel'])) {
    $_SESSION['error'] = "Invalid request";
    header('Location: index.php?page=host-dashboard');
    exit();
}

// Set status based on action
$status = ($action == 'confirm') ? 'confirmed' : 'cancelled';

// First verify this booking belongs to the host's listings
$host_id = $_SESSION['user_id'];

// Use transaction to ensure data integrity
$conn->begin_transaction();

try {
    // Check if booking exists and belongs to host's listing
    $check_sql = "
        SELECT b.id 
        FROM bookings b 
        JOIN homestays h ON b.homestay_id = h.id 
        WHERE b.id = ? AND h.host_id = ?
    ";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $booking_id, $host_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Booking not found or you don't have permission");
    }
    
    // Update booking status
    $update_sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $status, $booking_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update booking");
    }
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['message'] = "Booking " . ($action == 'confirm' ? 'confirmed' : 'cancelled') . " successfully!";
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect back
header('Location: index.php?page=host-dashboard');
exit();
?>