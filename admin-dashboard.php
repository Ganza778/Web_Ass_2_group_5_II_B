<?php
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php?page=dashboard');
    exit();
}

// Get unverified homestays
$stmt = $conn->prepare("
    SELECT h.*, u.name as host_name 
    FROM homestays h 
    JOIN users u ON h.host_id = u.id 
    WHERE h.verified = 0
");
$stmt->execute();
$unverified = $stmt->get_result();

// Get all users
$users = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
?>

<h1>Admin Dashboard</h1>

<h2>Unverified Home-Stays</h2>
<?php if ($unverified->num_rows > 0): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Host</th>
                <th>Location</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($stay = $unverified->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $stay['title']; ?></td>
                    <td><?php echo $stay['host_name']; ?></td>
                    <td><?php echo $stay['location']; ?></td>
                    <td>$<?php echo $stay['price']; ?></td>
                    <td>
                        <a href="verify-stay.php?id=<?php echo $stay['id']; ?>&action=verify" class="btn-small">Verify</a>
                        <a href="verify-stay.php?id=<?php echo $stay['id']; ?>&action=reject" class="btn-small btn-danger">Reject</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>All home-stays are verified.</p>
<?php endif; ?>

<h2>All Users</h2>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
        </tr>
    </thead>
    <tbody>
        <?php while($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo $user['name']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td><?php echo $user['role']; ?></td>
                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>