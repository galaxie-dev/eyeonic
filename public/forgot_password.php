<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $token = bin2hex(random_bytes(16));

    $stmt = $pdo->prepare("UPDATE users SET password_reset_token = ? WHERE email = ?");
    if ($stmt->execute([$token, $email])) {
        $link = "http://yourdomain.com/public/reset_password.php?token=$token";
        mail($email, "Reset Password", "Click to reset password: $link");
        $message = "Reset link sent to your email.";
    } else {
        $error = "Email not found.";
    }
}
?>

<form method="post">
    <input type="email" name="email" required placeholder="Enter your email">
    <button type="submit">Send Reset Link</button>
</form>
<?php if (isset($message)) echo "<p>$message</p>"; ?>
<?php if (isset($error)) echo "<p>$error</p>"; ?>
