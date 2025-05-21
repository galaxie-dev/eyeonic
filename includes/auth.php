# Login, register, session handlers<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Require login to access certain pages
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /public/login.php');
        exit;
    }
}

// Require admin role to access admin pages
function requireAdmin() {
    if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
        header('Location: /public/index.php');
        exit;
    }
}

// Login user
function loginUser($email, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['user_role'];
        return true;
    }

    return false;
}

// Register user
function registerUser($name, $email, $phone, $password, $pdo) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, user_role) VALUES (?, ?, ?, ?, 'customer')");
    return $stmt->execute([$name, $email, $phone, $password_hash]);
}

// Logout user
function logoutUser() {
    session_unset();
    session_destroy();
    header('Location: /public/login.php');
    exit;
}
