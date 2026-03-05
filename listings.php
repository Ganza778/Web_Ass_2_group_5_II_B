<?php
// Build query with prepared statements
$sql = "SELECT h.*, u.name as host_name FROM homestays h JOIN users u ON h.host_id = u.id WHERE h.verified = 1";
$params = [];
$types = "";

// Add filters
if (isset($_GET['min_price']) && $_GET['min_price'] != '') {
    $sql .= " AND h.price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
}
if (isset($_GET['max_price']) && $_GET['max_price'] != '') {
    $sql .= " AND h.price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
}
if (isset($_GET['tag']) && $_GET['tag'] != '') {
    $sql .= " AND h.cultural_tag = ?";
    $params[] = $_GET['tag'];
    $types .= "s";
}

$sql .= " ORDER BY h.created_at DESC";

// Prepare and execute
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$listings = $stmt->get_result();
?>

<h1>Available Home-Stays</h1>

<!-- Filter Form -->
<div class="filter-section">
    <h3>Filter Stays</h3>
    <form method="GET" class="filter-form">
        <input type="hidden" name="page" value="listings">
        <div class="form-group">
            <label>Min Price ($):</label>
            <input type="number" name="min_price" value="<?php echo $_GET['min_price'] ?? ''; ?>">
        </div>
        <div class="form-group">
            <label>Max Price ($):</label>
            <input type="number" name="max_price" value="<?php echo $_GET['max_price'] ?? ''; ?>">
        </div>
        <div class="form-group">
            <label>Cultural Tag:</label>
            <select name="tag">
                <option value="">All</option>
                <option value="Imigongo" <?php echo (isset($_GET['tag']) && $_GET['tag'] == 'Imigongo') ? 'selected' : ''; ?>>Imigongo</option>
                <option value="volcanic stone" <?php echo (isset($_GET['tag']) && $_GET['tag'] == 'volcanic stone') ? 'selected' : ''; ?>>Volcanic Stone</option>
                <option value="traditional" <?php echo (isset($_GET['tag']) && $_GET['tag'] == 'traditional') ? 'selected' : ''; ?>>Traditional</option>
            </select>
        </div>
        <button type="submit" class="btn">Apply Filters</button>
        <a href="index.php?page=listings" class="btn">Clear</a>
    </form>
</div>

<!-- Listings Grid -->
<div class="listings-grid">
    <?php if ($listings->num_rows > 0): ?>
        <?php while($stay = $listings->fetch_assoc()): ?>
            <div class="stay-card">
                <img src="images/<?php echo $stay['image']; ?>" alt="<?php echo $stay['title']; ?>" class="stay-image">
                <div class="stay-details">
                    <h3><?php echo $stay['title']; ?></h3>
                    <p class="location">📍 <?php echo $stay['location']; ?></p>
                    <p class="price">$<?php echo $stay['price']; ?> <span>/ night</span></p>
                    <?php if ($stay['cultural_tag'] != 'none'): ?>
                        <span class="tag"><?php echo $stay['cultural_tag']; ?></span>
                    <?php endif; ?>
                    <p class="host">Hosted by: <?php echo $stay['host_name']; ?></p>
                    
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'guest'): ?>
                        <a href="index.php?page=book&id=<?php echo $stay['id']; ?>" class="btn">Book Now</a>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                        <a href="index.php?page=login" class="btn">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No home-stays found matching your criteria.</p>
    <?php endif; ?>
</div>