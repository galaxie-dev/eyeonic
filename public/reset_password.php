<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = cleanInput($_POST['password']);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, password_reset_token = NULL WHERE password_reset_token = ?");
        $stmt->execute([$password_hash, $token]);

        if ($stmt->rowCount()) {
            redirect('login.php', 'Password reset successfully.');
        } else {
            $error = "Invalid or expired token.";
        }
    }
} else {
    die("Invalid access.");
}
?>

<form method="post">
    <input type="password" name="password" required placeholder="New Password">
    <button type="submit">Reset Password</button>
</form>
<?php if (isset($error)) echo "<p>$error</p>"; ?>
