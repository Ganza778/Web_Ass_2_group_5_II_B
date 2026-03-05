<?php
if ($_SESSION['role'] != 'host') {
    header('Location: index.php?page=dashboard');
    exit();
}

$host_id = $_SESSION['user_id'];

// Get statistics with prepared statements

// Total bookings for host's listings
$stmt = $conn->prepare("
    SELECT COUNT(*) as total_bookings, 
           SUM(b.total_price) as total_revenue,
           SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_requests
    FROM bookings b 
    JOIN homestays h ON b.homestay_id = h.id 
    WHERE h.host_id = ?
");
$stmt->bind_param("i", $host_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get pending bookings
$stmt = $conn->prepare("
    SELECT b.*, h.title, u.name as guest_name 
    FROM bookings b 
    JOIN homestays h ON b.homestay_id = h.id 
    JOIN users u ON b.guest_id = u.id 
    WHERE h.host_id = ? AND b.status = 'pending'
    ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $host_id);
$stmt->execute();
$pending_bookings = $stmt->get_result();
?>

<h1>Host Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Bookings</h3>
        <p class="stat-number"><?php echo $stats['total_bookings'] ?? 0; ?></p>
    </div>
    <div class="stat-card">
        <h3>Pending Requests</h3>
        <p class="stat-number"><?php echo $stats['pending_requests'] ?? 0; ?></p>
    </div>
    <div class="stat-card">
        <h3>Estimated Revenue</h3>
        <p class="stat-number">$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></p>
    </div>
</div>

<h2>Pending Booking Requests</h2>
<?php if ($pending_bookings->num_rows > 0): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Guest</th>
                <th>Home-Stay</th>
                <th>Nights</th>
                <th>Total</th>
                <th>Booked On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($booking = $pending_bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $booking['guest_name']; ?></td>
                    <td><?php echo $booking['title']; ?></td>
                    <td><?php echo $booking['nights']; ?></td>
                    <td>$<?php echo $booking['total_price']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                    <td>
                        <a href="update-booking.php?id=<?php echo $booking['id']; ?>&status=confirmed" class="btn-small">Confirm</a>
                        <a href="update-booking.php?id=<?php echo $booking['id']; ?>&status=cancelled" class="btn-small btn-danger">Cancel</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No pending booking requests.</p>
<?php endif; ?>