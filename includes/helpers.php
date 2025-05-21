 <!-- Reusable functions (e.g. sanitize, redirect) -->
<?php

// Sanitize input
function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Redirect with optional message
function redirect($url, $message = null) {
    if ($message) {
        $_SESSION['flash'] = $message;
    }
    header("Location: $url");
    exit;
}

// Flash message handler
function flash() {
    if (!empty($_SESSION['flash'])) {
        echo "<div class='flash-message'>" . $_SESSION['flash'] . "</div>";
        unset($_SESSION['flash']);
    }
}

// Format currency
function formatCurrency($amount) {
    return 'KES ' . number_format($amount, 2);
}

// Generate random string (for order ref or tokens)
function generateRandomString($length = 10) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

