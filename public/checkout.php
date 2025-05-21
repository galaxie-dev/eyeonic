<?php
include 'header.php';
session_start();
?>
<div class="container">
    <h1>Checkout</h1>
    <form method="post" action="payment.php">
        <label>Full Name</label>
        <input type="text" name="name" required>
        <label>Phone Number (M-Pesa)</label>
        <input type="text" name="phone" required>
        <label>Shipping Address</label>
        <input type="text" name="address" required>
        <button type="submit">Pay with M-Pesa</button>
    </form>
</div>
<?php include 'footer.php'; ?>
