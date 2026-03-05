<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Use prepared statement
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php?page=dashboard');
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
    $stmt->close();
}
?>

<h1>Login</h1>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
<form method="POST" class="form">
    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" required>
    </div>
    <div class="form-group">
        <label>Password:</label>
        <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn">Login</button>
</form>
<p>Don't have an account? <a href="index.php?page=register">Register here</a></p>