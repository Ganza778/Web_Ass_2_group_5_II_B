<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    // Use prepared statement
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    
    if ($stmt->execute()) {
        header('Location: index.php?page=login&registered=1');
        exit();
    } else {
        $error = "Registration failed: " . $conn->error;
    }
    $stmt->close();
}
?>

<h1>Register</h1>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
<form method="POST" class="form">
    <div class="form-group">
        <label>Full Name:</label>
        <input type="text" name="name" required>
    </div>
    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" required>
    </div>
    <div class="form-group">
        <label>Password:</label>
        <input type="password" name="password" required>
    </div>
    <div class="form-group">
        <label>I want to:</label>
        <select name="role" required>
            <option value="guest">Find a Stay (Guest)</option>
            <option value="host">List My Place (Host)</option>
        </select>
    </div>
    <button type="submit" class="btn">Register</button>
</form>
<p>Already have an account? <a href="index.php?page=login">Login here</a></p>