<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php?page=login');
    exit();
}

// Get unverified homestays
$unverified_sql = "
    SELECT h.*, u.name as host_name, u.email as host_email 
    FROM homestays h 
    JOIN users u ON h.host_id = u.id 
    WHERE h.verified = 0
    ORDER BY h.created_at DESC
";
$unverified = $conn->query($unverified_sql);

// Get ALL users (no limit)
$users_sql = "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC";
$users = $conn->query($users_sql);

// Get statistics
$stats_sql = "
    SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM homestays) as total_listings,
        (SELECT COUNT(*) FROM homestays WHERE verified = 0) as pending_verification,
        (SELECT COUNT(*) FROM bookings) as total_bookings
";
$stats = $conn->query($stats_sql)->fetch_assoc();

// Count users by role
$role_counts_sql = "
    SELECT 
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN role = 'host' THEN 1 ELSE 0 END) as host_count,
        SUM(CASE WHEN role = 'guest' THEN 1 ELSE 0 END) as guest_count
    FROM users
";
$role_counts = $conn->query($role_counts_sql)->fetch_assoc();
?>

<!-- NO HEADER INCLUDE HERE - Router handles it -->

<div class="container">
    <h1>Admin Dashboard</h1>
    
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
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
            <h3>Total Users</h3>
            <p style="font-size: 2.5em; color: #3498db;"><?php echo $stats['total_users']; ?></p>
            <div style="font-size: 0.9em; margin-top: 10px;">
                <span style="color: #e74c3c;">Admin: <?php echo $role_counts['admin_count']; ?></span> | 
                <span style="color: #3498db;">Host: <?php echo $role_counts['host_count']; ?></span> | 
                <span style="color: #27ae60;">Guest: <?php echo $role_counts['guest_count']; ?></span>
            </div>
        </div>
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
            <h3>Total Listings</h3>
            <p style="font-size: 2.5em; color: #27ae60;"><?php echo $stats['total_listings']; ?></p>
        </div>
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
            <h3>Pending Verification</h3>
            <p style="font-size: 2.5em; color: #f39c12;"><?php echo $stats['pending_verification']; ?></p>
        </div>
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
            <h3>Total Bookings</h3>
            <p style="font-size: 2.5em; color: #9b59b6;"><?php echo $stats['total_bookings']; ?></p>
        </div>
    </div>

    <!-- Unverified Home-Stays Section -->
    <h2>⏳ Pending Verification (<?php echo $stats['pending_verification']; ?>)</h2>
    <?php if ($unverified && $unverified->num_rows > 0): ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <thead style="background: #34495e; color: white;">
                    <tr>
                        <th style="padding: 12px;">Title</th>
                        <th style="padding: 12px;">Host</th>
                        <th style="padding: 12px;">Location</th>
                        <th style="padding: 12px;">Price</th>
                        <th style="padding: 12px;">Cultural Tag</th>
                        <th style="padding: 12px;">Submitted</th>
                        <th style="padding: 12px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($stay = $unverified->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px;"><strong><?php echo $stay['title']; ?></strong></td>
                            <td style="padding: 12px;">
                                <?php echo $stay['host_name']; ?><br>
                                <small style="color: #666;"><?php echo $stay['host_email']; ?></small>
                            </td>
                            <td style="padding: 12px;"><?php echo $stay['location']; ?></td>
                            <td style="padding: 12px;"><strong>$<?php echo $stay['price']; ?></strong></td>
                            <td style="padding: 12px;">
                                <span style="background: #f1c40f; padding: 3px 8px; border-radius: 3px; display: inline-block;">
                                    <?php echo $stay['cultural_tag']; ?>
                                </span>
                            </td>
                            <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($stay['created_at'])); ?></td>
                            <td style="padding: 12px;">
                                <a href="verify-homestay.php?id=<?php echo $stay['id']; ?>&action=verify" 
                                   style="background: #27ae60; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-right: 5px;"
                                   onclick="return confirm('Verify this home-stay? It will be visible to guests.')">✓ Verify</a>
                                <a href="verify-homestay.php?id=<?php echo $stay['id']; ?>&action=reject" 
                                   style="background: #e74c3c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block;"
                                   onclick="return confirm('Reject this home-stay? It will be deleted.')">✗ Reject</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="background: #f8f9fa; padding: 20px; border-radius: 4px;">No pending verifications. All home-stays are verified!</p>
    <?php endif; ?>

    <!-- ALL USERS TABLE -->
    <h2 style="margin-top: 40px;">👥 All Users (<?php echo $stats['total_users']; ?> total)</h2>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <thead style="background: #34495e; color: white;">
                <tr>
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Name</th>
                    <th style="padding: 12px;">Email</th>
                    <th style="padding: 12px;">Role</th>
                    <th style="padding: 12px;">Joined Date</th>
                    <th style="padding: 12px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users && $users->num_rows > 0): ?>
                    <?php while($user = $users->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px;">#<?php echo $user['id']; ?></td>
                            <td style="padding: 12px;">
                                <strong><?php echo $user['name']; ?></strong>
                            </td>
                            <td style="padding: 12px;"><?php echo $user['email']; ?></td>
                            <td style="padding: 12px;">
                                <span style="
                                    background: <?php 
                                        echo $user['role'] == 'admin' ? '#e74c3c' : 
                                            ($user['role'] == 'host' ? '#3498db' : '#27ae60'); 
                                    ?>;
                                    color: white;
                                    padding: 5px 12px;
                                    border-radius: 20px;
                                    display: inline-block;
                                    font-size: 0.9em;
                                ">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td style="padding: 12px;"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></td>
                            <td style="padding: 12px;">
                                <span style="
                                    background: #27ae60;
                                    color: white;
                                    padding: 3px 8px;
                                    border-radius: 3px;
                                    font-size: 0.8em;
                                ">Active</span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center;">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Quick Stats Summary -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 30px 0;">
        <h3>📊 Quick Summary</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px;">
            <div>
                <strong style="color: #e74c3c;">Admins:</strong> <?php echo $role_counts['admin_count']; ?>
            </div>
            <div>
                <strong style="color: #3498db;">Hosts:</strong> <?php echo $role_counts['host_count']; ?>
            </div>
            <div>
                <strong style="color: #27ae60;">Guests:</strong> <?php echo $role_counts['guest_count']; ?>
            </div>
        </div>
    </div>
</div>
