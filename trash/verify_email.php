<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, email_verification_token = NULL WHERE email_verification_token = ?");
    $stmt->execute([$token]);

    if ($stmt->rowCount()) {
        redirect('login.php', 'Email verified successfully!');
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "Missing token.";
}
