<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'host') {
    header('Location: index.php?page=login');
    exit();
}

$host_id = $_SESSION['user_id'];

// Get statistics
$stats_sql = "
    SELECT 
        COUNT(DISTINCT b.id) as total_bookings,
        COALESCE(SUM(b.total_price), 0) as total_revenue,
        SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
        COUNT(DISTINCT h.id) as total_listings
    FROM homestays h
    LEFT JOIN bookings b ON h.id = b.homestay_id
    WHERE h.host_id = ?
";
$stmt = $conn->prepare($stats_sql);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get pending bookings
$pending_sql = "
    SELECT 
        b.*, 
        h.title, 
        h.location,
        u.name as guest_name,
        u.email as guest_email
    FROM bookings b 
    JOIN homestays h ON b.homestay_id = h.id 
    JOIN users u ON b.guest_id = u.id 
    WHERE h.host_id = ? AND b.status = 'pending'
    ORDER BY b.booking_date DESC
";
$stmt = $conn->prepare($pending_sql);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$pending_bookings = $stmt->get_result();

// Get all bookings history
$history_sql = "
    SELECT 
        b.*, 
        h.title, 
        h.location,
        u.name as guest_name,
        u.email as guest_email
    FROM bookings b 
    JOIN homestays h ON b.homestay_id = h.id 
    JOIN users u ON b.guest_id = u.id 
    WHERE h.host_id = ?
    ORDER BY b.booking_date DESC
";
$stmt = $conn->prepare($history_sql);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$all_bookings = $stmt->get_result();

// Get host's listings
$listings_sql = "SELECT * FROM homestays WHERE host_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($listings_sql);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$listings = $stmt->get_result();
?>

<!-- NO HEADER INCLUDE - Router handles it -->

<div class="container">
    <h1>Welcome, <?php echo $_SESSION['name']; ?>! (Host Dashboard)</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="success-message" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 20px 0;">
            <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 20px 0;">
            <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
            <h3>Total Bookings</h3>
            <p style="font-size: 2.5em; color: #3498db; margin: 10px 0;"><?php echo $stats['total_bookings'] ?? 0; ?></p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
            <h3>Pending Requests</h3>
            <p style="font-size: 2.5em; color: #f39c12; margin: 10px 0;"><?php echo $stats['pending_requests'] ?? 0; ?></p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
            <h3>Total Revenue</h3>
            <p style="font-size: 2.5em; color: #27ae60; margin: 10px 0;">$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></p>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
            <h3>Your Listings</h3>
            <p style="font-size: 2.5em; color: #9b59b6; margin: 10px 0;"><?php echo $stats['total_listings'] ?? 0; ?></p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="margin: 30px 0; display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="index.php?page=add-listing" style="background: #27ae60; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold;">➕ Add New Listing</a>
        <a href="index.php?page=dashboard" style="background: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold;">📋 View My Listings</a>
    </div>

    <!-- My Listings Preview -->
    <h2>🏠 My Recent Listings</h2>
    <?php if ($listings && $listings->num_rows > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
            <?php 
            $count = 0;
            while($listing = $listings->fetch_assoc()): 
                if($count >= 3) break; // Show only first 3
                $count++;
            ?>
                <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0;"><?php echo $listing['title']; ?></h3>
                    <p><strong>Price:</strong> $<?php echo $listing['price']; ?>/night</p>
                    <p><strong>Location:</strong> <?php echo $listing['location']; ?></p>
                    <p>
                        <strong>Status:</strong> 
                        <?php if($listing['verified']): ?>
                            <span style="background: #27ae60; color: white; padding: 3px 10px; border-radius: 3px;">Verified</span>
                        <?php else: ?>
                            <span style="background: #f39c12; color: white; padding: 3px 10px; border-radius: 3px;">Pending Verification</span>
                        <?php endif; ?>
                    </p>
                    <a href="index.php?page=edit-listing&id=<?php echo $listing['id']; ?>" style="background: #3498db; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;">Edit</a>
                </div>
            <?php endwhile; ?>
        </div>
        <?php if($listings->num_rows > 3): ?>
            <p><a href="index.php?page=dashboard">View all <?php echo $listings->num_rows; ?> listings →</a></p>
        <?php endif; ?>
    <?php else: ?>
        <p style="background: #f8f9fa; padding: 20px; border-radius: 4px;">You haven't created any listings yet. <a href="index.php?page=add-listing">Add your first listing!</a></p>
    <?php endif; ?>

    <!-- Pending Bookings Section -->
    <h2 style="margin-top: 40px;">⏳ Pending Booking Requests (<?php echo $stats['pending_requests'] ?? 0; ?>)</h2>
    <?php if ($pending_bookings && $pending_bookings->num_rows > 0): ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <thead style="background: #34495e; color: white;">
                    <tr>
                        <th style="padding: 12px;">Guest</th>
                        <th style="padding: 12px;">Home-Stay</th>
                        <th style="padding: 12px;">Location</th>
                        <th style="padding: 12px;">Nights</th>
                        <th style="padding: 12px;">Total</th>
                        <th style="padding: 12px;">Booked On</th>
                        <th style="padding: 12px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = $pending_bookings->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px;">
                                <strong><?php echo $booking['guest_name']; ?></strong><br>
                                <small style="color: #666;"><?php echo $booking['guest_email']; ?></small>
                            </td>
                            <td style="padding: 12px;"><?php echo $booking['title']; ?></td>
                            <td style="padding: 12px;"><?php echo $booking['location']; ?></td>
                            <td style="padding: 12px;"><?php echo $booking['nights']; ?></td>
                            <td style="padding: 12px;"><strong>$<?php echo $booking['total_price']; ?></strong></td>
                            <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                            <td style="padding: 12px;">
                                <a href="update-booking-status.php?id=<?php echo $booking['id']; ?>&action=confirm" 
                                   style="background: #27ae60; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-right: 5px;"
                                   onclick="return confirm('Confirm this booking?')">✓ Confirm</a>
                                <a href="update-booking-status.php?id=<?php echo $booking['id']; ?>&action=cancel" 
                                   style="background: #e74c3c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block;"
                                   onclick="return confirm('Cancel this booking?')">✗ Decline</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="background: #f8f9fa; padding: 20px; border-radius: 4px;">No pending booking requests.</p>
    <?php endif; ?>

    <!-- Booking History Section -->
    <h2 style="margin-top: 40px;">📊 All Booking History</h2>
    <?php if ($all_bookings && $all_bookings->num_rows > 0): ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <thead style="background: #34495e; color: white;">
                    <tr>
                        <th style="padding: 12px;">Guest</th>
                        <th style="padding: 12px;">Home-Stay</th>
                        <th style="padding: 12px;">Nights</th>
                        <th style="padding: 12px;">Total</th>
                        <th style="padding: 12px;">Status</th>
                        <th style="padding: 12px;">Booking Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = $all_bookings->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px;">
                                <strong><?php echo $booking['guest_name']; ?></strong><br>
                                <small style="color: #666;"><?php echo $booking['guest_email']; ?></small>
                            </td>
                            <td style="padding: 12px;"><?php echo $booking['title']; ?></td>
                            <td style="padding: 12px;"><?php echo $booking['nights']; ?></td>
                            <td style="padding: 12px;"><strong>$<?php echo $booking['total_price']; ?></strong></td>
                            <td style="padding: 12px;">
                                <span style="
                                    display: inline-block;
                                    padding: 5px 12px;
                                    border-radius: 20px;
                                    background: <?php 
                                        echo $booking['status'] == 'confirmed' ? '#d4edda' : 
                                            ($booking['status'] == 'pending' ? '#fff3cd' : '#f8d7da'); 
                                    ?>;
                                    color: <?php 
                                        echo $booking['status'] == 'confirmed' ? '#155724' : 
                                            ($booking['status'] == 'pending' ? '#856404' : '#721c24'); 
                                    ?>;
                                    font-weight: bold;
                                ">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="background: #f8f9fa; padding: 20px; border-radius: 4px;">No booking history yet.</p>
    <?php endif; ?>
</div>

<!-- NO FOOTER INCLUDE - Router handles it -->