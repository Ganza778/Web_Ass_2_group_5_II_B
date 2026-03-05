<?php
require_once 'config.php';

// Simple router
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Public pages
$allowed_pages = ['home', 'listings', 'login', 'register', 'logout'];
$protected_pages = ['dashboard', 'add-listing', 'edit-listing', 'book', 'my-bookings', 'host-dashboard', 'admin-dashboard'];

// Check if page is protected
if (in_array($page, $protected_pages) && !isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

// Role-based access
if (isset($_SESSION['role'])) {
    if ($page == 'host-dashboard' && $_SESSION['role'] != 'host') {
        header('Location: index.php?page=dashboard');
        exit();
    }
    if ($page == 'admin-dashboard' && $_SESSION['role'] != 'admin') {
        header('Location: index.php?page=dashboard');
        exit();
    }
}

// Include header
include 'header.php';

// Route to page
switch($page) {
    case 'home':
        include 'home.php';
        break;
    case 'listings':
        include 'listings.php';
        break;
    case 'login':
        include 'login.php';
        break;
    case 'register':
        include 'register.php';
        break;
    case 'logout':
        include 'logout.php';
        break;
    case 'dashboard':
        include 'dashboard.php';
        break;
    case 'add-listing':
        include 'add-listing.php';
        break;
    case 'edit-listing':
        include 'edit-listing.php';
        break;
    case 'book':
        include 'book.php';
        break;
    case 'my-bookings':
        include 'my-bookings.php';
        break;
    case 'host-dashboard':
        include 'host-dashboard.php';
        break;
    case 'admin-dashboard':
        include 'admin-dashboard.php';
        break;
    default:
        include '404.php';
}

// Include footer
include 'footer.php';
?>