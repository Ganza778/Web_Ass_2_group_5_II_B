<?php
require_once 'config.php';

// Build query with prepared statements - USE DISTINCT to avoid duplicates
$sql = "SELECT DISTINCT h.*, u.name as host_name 
        FROM homestays h 
        JOIN users u ON h.host_id = u.id 
        WHERE h.verified = 1";
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

// Get unique count
$total_listings = $listings->num_rows;
?>

<!-- NO HEADER INCLUDE - Router handles it -->

<div class="container">
    <h1>Available Home-Stays (<?php echo $total_listings; ?>)</h1>

    <!-- Filter Form -->
    <div class="filter-section" style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3>Filter Stays</h3>
        <form method="GET" class="filter-form" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <input type="hidden" name="page" value="listings">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Min Price ($):</label>
                <input type="number" name="min_price" value="<?php echo $_GET['min_price'] ?? ''; ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Max Price ($):</label>
                <input type="number" name="max_price" value="<?php echo $_GET['max_price'] ?? ''; ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Cultural Tag:</label>
                <select name="tag" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">All</option>
                    <option value="Imigongo" <?php echo (isset($_GET['tag']) && $_GET['tag'] == 'Imigongo') ? 'selected' : ''; ?>>Imigongo</option>
                    <option value="volcanic stone" <?php echo (isset($_GET['tag']) && $_GET['tag'] == 'volcanic stone') ? 'selected' : ''; ?>>Volcanic Stone</option>
                    <option value="traditional" <?php echo (isset($_GET['tag']) && $_GET['tag'] == 'traditional') ? 'selected' : ''; ?>>Traditional</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; align-items: flex-end;">
                <button type="submit" style="background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Apply Filters</button>
                <a href="index.php?page=listings" style="background: #95a5a6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">Clear</a>
            </div>
        </form>
    </div>

    <!-- Listings Grid -->
    <?php if ($listings && $listings->num_rows > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; margin: 30px 0;">
            <?php 
            // Use an array to track displayed IDs (extra safety)
            $displayed_ids = [];
            while($stay = $listings->fetch_assoc()): 
                // Skip if already displayed (double-check)
                if (in_array($stay['id'], $displayed_ids)) continue;
                $displayed_ids[] = $stay['id'];
            ?>
                <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Image -->
                    <img src="images/<?php echo $stay['image'] ?? 'placeholder.jpg'; ?>" 
                         alt="<?php echo $stay['title']; ?>" 
                         style="width: 100%; height: 220px; object-fit: cover;">
                    
                    <!-- Details -->
                    <div style="padding: 20px;">
                        <h2 style="margin-top: 0; margin-bottom: 10px; color: #2c3e50;"><?php echo $stay['title']; ?></h2>
                        
                        <div style="display: flex; align-items: center; gap: 5px; margin-bottom: 10px; color: #7f8c8d;">
                            <span>📍</span>
                            <span><?php echo $stay['location']; ?></span>
                        </div>
                        
                        <div style="display: flex; align-items: baseline; gap: 5px; margin-bottom: 15px;">
                            <span style="font-size: 24px; font-weight: bold; color: #2c3e50;">$<?php echo $stay['price']; ?></span>
                            <span style="color: #7f8c8d;">/ night</span>
                        </div>
                        
                        <?php if ($stay['cultural_tag'] != 'none'): ?>
                            <div style="margin-bottom: 15px;">
                                <span style="background: #f1c40f; color: #2c3e50; padding: 5px 12px; border-radius: 20px; font-size: 14px; display: inline-block;">
                                    🎨 <?php echo $stay['cultural_tag']; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <p style="color: #34495e; margin-bottom: 20px; line-height: 1.6;">
                            <?php echo substr($stay['description'], 0, 100) . '...'; ?>
                        </p>
                        
                        <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 20px;">
                            Hosted by: <strong><?php echo $stay['host_name']; ?></strong>
                        </p>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'guest'): ?>
                            <a href="index.php?page=book&id=<?php echo $stay['id']; ?>" 
                               style="background: #27ae60; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold; width: 100%; text-align: center; box-sizing: border-box;">
                                📅 Book Now
                            </a>
                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                            <a href="index.php?page=login" 
                               style="background: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold; width: 100%; text-align: center; box-sizing: border-box;">
                                🔑 Login to Book
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; background: white; border-radius: 8px; margin: 30px 0;">
            <h2>No Home-Stays Found</h2>
            <p>Try adjusting your filters or check back later for new listings.</p>
            <a href="index.php?page=listings" style="background: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 20px;">Clear Filters</a>
        </div>
    <?php endif; ?>
</div>
