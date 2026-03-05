<?php
if ($_SESSION['role'] != 'guest') {
    header('Location: index.php?page=listings');
    exit();
}

$homestay_id = $_GET['id'] ?? 0;

// Get homestay details
$stmt = $conn->prepare("SELECT * FROM homestays WHERE id = ? AND verified = 1");
$stmt->bind_param("i", $homestay_id);
$stmt->execute();
$homestay = $stmt->get_result()->fetch_assoc();

if (!$homestay) {
    header('Location: index.php?page=listings');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nights = $_POST['nights'];
    $total_price = $homestay['price'] * $nights;
    $guest_id = $_SESSION['user_id'];
    
    // Use prepared statement
    $stmt = $conn->prepare("INSERT INTO bookings (homestay_id, guest_id, nights, total_price, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiid", $homestay_id, $guest_id, $nights, $total_price);
    
    if ($stmt->execute()) {
        header('Location: index.php?page=my-bookings&success=booked');
        exit();
    } else {
        $error = "Error creating booking: " . $conn->error;
    }
    $stmt->close();
}
?>

<h1>Book: <?php echo $homestay['title']; ?></h1>

<div class="booking-details">
    <p>Location: <?php echo $homestay['location']; ?></p>
    <p>Price per night: $<span id="pricePerNight"><?php echo $homestay['price']; ?></span></p>
    
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    
    <form method="POST" class="form" id="bookingForm">
        <div class="form-group">
            <label>Number of nights:</label>
            <input type="number" name="nights" id="nights" min="1" value="1" required>
        </div>
        
        <div class="form-group">
            <label>Total Price:</label>
            <h2>$<span id="totalPrice"><?php echo $homestay['price']; ?></span></h2>
        </div>
        
        <button type="submit" class="btn">Confirm Booking</button>
        <a href="index.php?page=listings" class="btn">Cancel</a>
    </form>
</div>

<script>
// Live total estimator
document.getElementById('nights').addEventListener('input', function() {
    const pricePerNight = <?php echo $homestay['price']; ?>;
    const nights = this.value;
    const total = pricePerNight * nights;
    document.getElementById('totalPrice').textContent = total.toFixed(2);
});
</script>