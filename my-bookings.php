<?php
if ($_SESSION['role'] != 'guest') {
    header('Location: index.php?page=dashboard');
    exit();
}

$guest_id = $_SESSION['user_id'];

// Get bookings with prepared statement
$stmt = $conn->prepare("
    SELECT b.*, h.title, h.location, h.price, h.image 
    FROM bookings b 
    JOIN homestays h ON b.homestay_id = h.id 
    WHERE b.guest_id = ? 
    ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<h1>My Bookings</h1>

<?php if (isset($_GET['success'])): ?>
    <p class="success">Booking confirmed successfully!</p>
<?php endif; ?>

<?php if ($bookings->num_rows > 0): ?>
    <div class="bookings-list">
        <?php while($booking = $bookings->fetch_assoc()): ?>
            <div class="booking-item">
                <img src="images/<?php echo $booking['image']; ?>" alt="<?php echo $booking['title']; ?>" class="booking-image">
                <div class="booking-info">
                    <h3><?php echo $booking['title']; ?></h3>
                    <p><?php echo $booking['location']; ?></p>
                    <p><?php echo $booking['nights']; ?> nights - $<?php echo $booking['total_price']; ?></p>
                    <p>Status: <span class="status <?php echo $booking['status']; ?>"><?php echo $booking['status']; ?></span></p>
                    <p>Booked on: <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>You haven't made any bookings yet. <a href="index.php?page=listings">Browse available stays</a></p>
<?php endif; ?>