<?php
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'guest') {
    // Get guest's bookings
    $stmt = $conn->prepare("
        SELECT b.*, h.title, h.location, h.price 
        FROM bookings b 
        JOIN homestays h ON b.homestay_id = h.id 
        WHERE b.guest_id = ?
        ORDER BY b.booking_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $bookings = $stmt->get_result();
} elseif ($role == 'host') {
    // Get host's listings
    $stmt = $conn->prepare("SELECT * FROM homestays WHERE host_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $listings = $stmt->get_result();
}
?>

<h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>

<?php if ($role == 'guest'): ?>
    <h2>My Bookings</h2>
    <?php if ($bookings->num_rows > 0): ?>
        <div class="booking-grid">
            <?php while($booking = $bookings->fetch_assoc()): ?>
                <div class="booking-card">
                    <h3><?php echo $booking['title']; ?></h3>
                    <p>Location: <?php echo $booking['location']; ?></p>
                    <p>Nights: <?php echo $booking['nights']; ?></p>
                    <p>Total: $<?php echo $booking['total_price']; ?></p>
                    <p>Status: <span class="status <?php echo $booking['status']; ?>"><?php echo $booking['status']; ?></span></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>You have no bookings yet. <a href="index.php?page=listings">Browse stays</a></p>
    <?php endif; ?>

<?php elseif ($role == 'host'): ?>
    <h2>My Listings</h2>
    <a href="index.php?page=add-listing" class="btn">Add New Listing</a>
    <?php if ($listings->num_rows > 0): ?>
        <div class="listing-grid">
            <?php while($listing = $listings->fetch_assoc()): ?>
                <div class="listing-card">
                    <h3><?php echo $listing['title']; ?></h3>
                    <p>Price: $<?php echo $listing['price']; ?>/night</p>
                    <p>Location: <?php echo $listing['location']; ?></p>
                    <p>Verified: <?php echo $listing['verified'] ? 'Yes' : 'No'; ?></p>
                    <a href="index.php?page=edit-listing&id=<?php echo $listing['id']; ?>" class="btn-small">Edit</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>You have no listings yet.</p>
    <?php endif; ?>

<?php elseif ($role == 'admin'): ?>
    <h2>Admin Dashboard</h2>
    <a href="index.php?page=admin-dashboard" class="btn">Go to Admin Panel</a>
<?php endif; ?>