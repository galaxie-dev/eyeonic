<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);
    $password = cleanInput($_POST['password']);

    $token = bin2hex(random_bytes(16));
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, user_role) VALUES (?, ?, ?, ?, 'customer')");
    $success = $stmt->execute([$name, $email, $phone, $password_hash]);

    // if ($success) {
    //     $verificationLink = "http://yourdomain.com/public/verify_email.php?token=$token";
    //     $subject = "Verify Your Email";
    //     $message = "Click the link to verify your email: $verificationLink";
    //     mail($email, $subject, $message); // Replace with PHPMailer for real usage

    //     redirect('login.php', 'Registration successful! Check your email to verify your account.');
    // } else {
    //     $error = "Registration failed.";
    // }
}
?>

<form method="post">
    <input type="text" name="name" required placeholder="Name">
    <input type="email" name="email" required placeholder="Email">
    <input type="text" name="phone" required placeholder="Phone (e.g. 2547...)">
    <input type="password" name="password" required placeholder="Password">
    <button type="submit">Register</button>
</form>
<?php if (isset($error)) echo "<p>$error</p>"; ?>
