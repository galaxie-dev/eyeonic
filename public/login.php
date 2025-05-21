<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

session_start(); // Ensure session is started

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = cleanInput($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['user_role'];
        $_SESSION['user_name'] = $user['full_name'] ?? 'User';

        // Redirect to homepage after login
        redirect('index.php');
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!-- Simple login form -->
<form method="post">
    <input type="email" name="email" required placeholder="Email">
    <input type="password" name="password" required placeholder="Password">
    <button type="submit">Login</button>
</form>

<a href="forgot_password.php">Forgot password?</a>

<?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
