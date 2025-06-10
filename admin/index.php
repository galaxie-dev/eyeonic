<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database connection
// $host = 'sql307.infinityfree.com';
// $db   = 'if0_39115861_eyeonic';
// $user = 'if0_39115861';
// $pass = 'QPDY35CzNmhsUMy';
// $charset = 'utf8mb4'; 

$host = 'localhost';
$db   = 'eyeonic';
$user = 'root';
$pass = '';
$charset = 'utf8mb4'; 

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, $options);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = cleanInput($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<h2>Admin Login</h2>
<form method="post">
    <input type="email" name="email" required placeholder="Email">
    <input type="password" name="password" required placeholder="Password">
    <button type="submit">Login</button>
</form>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>







<!-- email: admin@eyeonic.com -->
<!-- password: admin123 -->

