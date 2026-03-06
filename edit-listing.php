<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'host') {
    header('Location: index.php?page=login');
    exit();
}

$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$host_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM homestays WHERE id = ? AND host_id = ?");
$stmt->bind_param("ii", $listing_id, $host_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    $_SESSION['error'] = "Listing not found";
    header('Location: index.php?page=host-dashboard');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $cultural_tag = $_POST['cultural_tag'];
    
    $update_sql = "UPDATE homestays SET title=?, description=?, price=?, location=?, cultural_tag=? WHERE id=? AND host_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssdssii", $title, $description, $price, $location, $cultural_tag, $listing_id, $host_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Listing updated successfully!";
        header('Location: index.php?page=host-dashboard');
        exit();
    } else {
        $error = "Error updating listing";
    }
}
?>

<div class="container">
    <h1>Edit Listing: <?php echo $listing['title']; ?></h1>
    
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    
    <form method="POST" style="max-width: 600px; margin: 30px 0; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Title:</label>
            <input type="text" name="title" value="<?php echo $listing['title']; ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Description:</label>
            <textarea name="description" rows="4" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"><?php echo $listing['description']; ?></textarea>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Price per night ($):</label>
            <input type="number" step="0.01" name="price" value="<?php echo $listing['price']; ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Location:</label>
            <input type="text" name="location" value="<?php echo $listing['location']; ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Cultural Element:</label>
            <select name="cultural_tag" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="none" <?php echo $listing['cultural_tag'] == 'none' ? 'selected' : ''; ?>>None</option>
                <option value="Imigongo" <?php echo $listing['cultural_tag'] == 'Imigongo' ? 'selected' : ''; ?>>Imigongo</option>
                <option value="volcanic stone" <?php echo $listing['cultural_tag'] == 'volcanic stone' ? 'selected' : ''; ?>>Volcanic Stone</option>
                <option value="traditional" <?php echo $listing['cultural_tag'] == 'traditional' ? 'selected' : ''; ?>>Traditional</option>
            </select>
        </div>
        
        <button type="submit" style="background: #27ae60; color: white; padding: 12px 25px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">Update Listing</button>
        <a href="index.php?page=host-dashboard" style="background: #e74c3c; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; display: inline-block; margin-left: 10px;">Cancel</a>
    </form>
</div>