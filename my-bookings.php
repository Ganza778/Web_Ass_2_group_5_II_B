<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'guest') {
    header('Location: index.php?page=login');
    exit();
}

$guest_id = $_SESSION['user_id'];

// Get all bookings for this guest
$stmt = $conn->prepare("
    SELECT 
        b.*, 
        h.title, 
        h.location, 
        h.price,
        h.image,
        u.name as host_name
    FROM bookings b 
    JOIN homestays h ON b.homestay_id = h.id 
    JOIN users u ON h.host_id = u.id
    WHERE b.guest_id = ? 
    ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!-- NO HEADER INCLUDE -->

<div class="container">
    <h1>My Bookings</h1>
    
    <!-- Rest of your bookings content -->
    <!-- ... -->
</div>

<!-- NO FOOTER INCLUDE -->