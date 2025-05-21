<?php
require_once '../config/database.php'; // Make sure this points to your DB config

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // Basic validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Email already registered as admin.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (full_name, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$full_name, $email, $hashedPassword]);
            $success = "Admin account created successfully.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Admin</title>
    <style>
        form { width: 300px; margin: 40px auto; }
        input, button { width: 100%; padding: 8px; margin: 10px 0; }
        .message { text-align: center; }
    </style>
</head>
<body>
    <form method="post">
        <h2>Create Admin Account</h2>
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm" placeholder="Confirm Password" required>
        <button type="submit">Create Admin</button>

        <div class="message">
            <?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
            <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
        </div>
    </form>
</body>
</html>
