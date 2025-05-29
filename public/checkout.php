<?php
require_once '../config/database.php';
session_start();
include 'header.php';

// Verify order is approved
if (!isset($_GET['order_id'])) {
    header('Location: cart.php');
    exit;
}

$orderId = $_GET['order_id'];
$stmt = $pdo->prepare("
    SELECT o.*, u.name, u.email, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.status = 'approved' AND o.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: cart.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT p.name, oi.quantity, oi.price 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();
?>

<h2>Complete Payment</h2>
<div>
    <p><strong>Order ID:</strong> <?= $order['id'] ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($order['name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
    <p><strong>Delivery Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
    
    <h3>Order Summary</h3>
    <ul>
        <?php foreach ($items as $item): ?>
            <li>
                <?= htmlspecialchars($item['name']) ?> - 
                <?= $item['quantity'] ?> x KES <?= number_format($item['price'], 2) ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <p><strong>Subtotal:</strong> KES <?= number_format($order['total_amount'] - $order['delivery_fee'], 2) ?></p>
    <p><strong>Delivery Fee:</strong> KES <?= number_format($order['delivery_fee'], 2) ?></p>
    <p><strong>Total:</strong> KES <?= number_format($order['total_amount'], 2) ?></p>
    
    <h3>Payment Methods</h3>
    <form method="post" action="process_payment.php">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        
        <div>
            <input type="radio" name="payment_method" value="mpesa" id="mpesa" checked>
            <label for="mpesa">M-Pesa</label>
        </div>
        <div>
            <input type="radio" name="payment_method" value="card" id="card">
            <label for="card">Credit/Debit Card</label>
        </div>
        
        <button type="submit">Proceed to Payment</button>
    </form>
</div>

<?php include 'footer.php'; ?>





