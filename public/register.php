<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);
    $password = cleanInput($_POST['password']);

    // Check for duplicate email
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Email already registered.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, user_role) VALUES (?, ?, ?, ?, 'customer')");
            $success = $stmt->execute([$name, $email, $phone, $password_hash]);

            if ($success) {
                $success_message = "Registration successful! <a href='login.php'>Login here</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}

?>

<style>
    .register-section {
        max-width: 1200px;
        margin: 2.5rem auto 0 auto;
        padding: 0 1rem;
        display: flex;
        justify-content: center;
    }
    .register-container {
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        padding: 2rem;
        max-width: 400px;
        width: 100%;
    }
    .register-title {
        font-weight: 600;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: #111827;
        text-align: center;
    }
    .register-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .register-form label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #111827;
    }
    .register-form input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }
    .register-form input:focus {
        border-color: #2563eb;
        outline: none;
    }
    .btn-register {
        background-color: #2563eb;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.5rem 1.25rem;
        border-radius: 0.375rem;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
        margin-top: 0.5rem;
    }
    .btn-register:hover {
        background-color: #1d4ed8;
    }
    .error-message {
        color: #dc2626;
        font-size: 0.875rem;
        text-align: center;
        margin-top: 1rem;
    }
    .success-message {
        color: #059669;
        font-size: 0.875rem;
        text-align: center;
        margin-top: 1rem;
    }
    .success-message a {
        color: #2563eb;
        text-decoration: none;
        transition: color 0.2s;
    }
    .success-message a:hover {
        color: #1d4ed8;
    }
    .login-link {
        text-align: center;
        margin-top: 1rem;
    }
    .login-link a {
        font-size: 0.875rem;
        color: #2563eb;
        text-decoration: none;
        transition: color 0.2s;
    }
    .login-link a:hover {
        color: #1d4ed8;
    }
</style>

<main>
    <section class="register-section">
        <div class="register-container">
            <h2 class="register-title">Register</h2>
            <?php if (isset($success_message)): ?>
                <p class="success-message"><?= $success_message ?></p>
            <?php else: ?>
                <form method="post" class="register-form">
                    <div>
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required placeholder="Enter your full name">
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="Enter your email">
                    </div>
                    <div>
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" required placeholder="e.g., 2547...">
                    </div>
                    <div>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                    </div>
                    <button type="submit" class="btn-register">Register</button>
                </form>
                <?php if (isset($error)): ?>
                    <p class="error-message"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <div class="login-link">
                    <a href="login.php">Already have an account? Login</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
