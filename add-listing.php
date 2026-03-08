<?php
if ($_SESSION['role'] != 'host') {
    header('Location: index.php?page=dashboard');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $cultural_tag = $_POST['cultural_tag'];
    $host_id = $_SESSION['user_id'];

    $image = 'placeholder.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $image);
    }
    
    // Use prepared statement
    $stmt = $conn->prepare("INSERT INTO homestays (host_id, title, description, price, location, cultural_tag, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdsss", $host_id, $title, $description, $price, $location, $cultural_tag, $image);
    
    if ($stmt->execute()) {
        header('Location: index.php?page=dashboard&success=listing_added');
        exit();
    } else {
        $error = "Error adding listing: " . $conn->error;
    }
    $stmt->close();
}
?>

<h1>Add New Home-Stay</h1>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

<form method="POST" enctype="multipart/form-data" class="form">
    <div class="form-group">
        <label>Title:</label>
        <input type="text" name="title" required>
    </div>
    
    <div class="form-group">
        <label>Description:</label>
        <textarea name="description" rows="4" required></textarea>
    </div>
    
    <div class="form-group">
        <label>Price per night ($):</label>
        <input type="number" step="0.01" name="price" required>
    </div>
    
    <div class="form-group">
        <label>Location:</label>
        <input type="text" name="location" required>
    </div>
    
    <div class="form-group">
        <label>Cultural Element:</label>
        <select name="cultural_tag" required>
            <option value="none">None</option>
            <option value="Imigongo">Imigongo</option>
            <option value="volcanic stone">Volcanic Stone</option>
            <option value="traditional">Traditional</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Image (optional):</label>
        <input type="file" name="image" accept="image/*">
    </div>
    
    <button type="submit" class="btn">Add Listing</button>
    <a href="index.php?page=dashboard" class="btn">Cancel</a>
</form>