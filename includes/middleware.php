Access control (admin/user checks)
<?php
require_once __DIR__ . '/auth.php';

// Middleware for customer routes
function onlyCustomers() {
    if (!isLoggedIn() || $_SESSION['user_role'] !== 'customer') {
        redirect('/public/login.php', 'Access denied. Customers only.');
    }
}

// Middleware for admin routes
function onlyAdmins() {
    if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
        redirect('/public/index.php', 'Access denied. Admins only.');
    }
}
