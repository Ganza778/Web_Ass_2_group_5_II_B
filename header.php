<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TourStack - Home-Stay Finder</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php?page=home" class="logo">TourStack</a>
            <ul class="nav-menu">
                <li><a href="index.php?page=listings">Browse Stays</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="index.php?page=dashboard">Dashboard</a></li>
                    <?php if ($_SESSION['role'] == 'host'): ?>
                        <li><a href="index.php?page=host-dashboard">Host Panel</a></li>
                        <li><a href="index.php?page=add-listing">Add Listing</a></li>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li><a href="index.php?page=admin-dashboard">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?page=logout">Logout (<?php echo $_SESSION['name']; ?>)</a></li>
                <?php else: ?>
                    <li><a href="index.php?page=login">Login</a></li>
                    <li><a href="index.php?page=register">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="container">