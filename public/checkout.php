<?php
include 'header.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(); 
?>
<div class="container">
    <h1>Checkout</h1>
    <form method="post" action="payment.php">
        <label>Full Name: </label>
        <?= htmlspecialchars($user['name']) ?> <br>
        <label>Email: </label>
        <?= htmlspecialchars($user['email']) ?> <br>    
        <label>Phone Number (M-Pesa): </label>
        <?= htmlspecialchars($user['phone']) ?><br>    
     
        <label>Shipping Address</label>
        <input type="text" name="address" required><br>
        <button type="submit">Pay with M-Pesa</button>
    </form>
</div>
<?php include 'footer.php'; ?>
