<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


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



//MySQL DB Name	        MySQL User Name	 MySQL Password	        MySQL Host Name	
//if0_39115861_eyeonic	if0_39115861	(Your vPanel Password)	sql307.infinityfree.com



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
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, user_role) VALUES (?, ?, ?, ?, 'customer')");
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

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        
<style>
   /* Font stack resembling Aptos (Segoe UI) */
    body {
        font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .register-section {
        max-width: 1200px;
        margin: 1.5rem auto 0 auto;
        padding: 0 1rem;
        display: flex;
        justify-content: center;
    }
    
    .register-container {
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        padding: 1.5rem;
        max-width: 400px;
        width: 100%;
    }
    
    .register-title {
        font-weight: 600;
        font-size: 1.25rem;
        margin-bottom: 1.25rem;
        color: #111827;
        text-align: center;
    }
    
    .register-form {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
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
    
    .error-message, .success-message {
        font-size: 0.875rem;
        text-align: center;
        margin-top: 1rem;
    }
    
    .error-message {
        color: #dc2626;
    }
    
    .success-message {
        color: #059669;
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

    /* Mobile optimizations */
    @media (max-width: 480px) {
        .register-section {
            margin: 1rem auto 0 auto;
            padding: 0 0.75rem;
        }
        
        .register-container {
            padding: 1.25rem;
            box-shadow: none;
            border: 1px solid #e5e7eb;
        }
        
        .register-title {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .register-form {
            gap: 0.5rem;
        }
        
        input, button {
            font-size: 0.8125rem !important;
        }
        
        .error-message, 
        .success-message,
        .login-link a {
            font-size: 0.8125rem;
        }
    }
    /* ===== Mobile App-like Styling ===== */
@media (max-width: 600px) {
    body {
        background: #f8f9fa;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    main {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .login-section, .register-section {
        margin: 0;
        padding: 0;
        width: 100%;
        max-width: 100%;
    }

    .login-container, .register-container {
        max-width: 100%;
        width: 100%;
        border-radius: 0;
        box-shadow: none;
        border: none;
        padding: 2rem 1.5rem;
        background: transparent;
    }

    .login-title, .register-title {
        font-size: 1.5rem;
        margin-bottom: 2rem;
        color: #1a1a1a;
    }

    .login-form, .register-form {
        gap: 1.25rem;
    }

    .login-form input, .register-form input {
        padding: 0.75rem;
        font-size: 1rem;
        border: 1px solid #e0e0e0;
        background: white;
    }

    .btn-login, .btn-register {
        padding: 0.85rem;
        font-size: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
        background: #2563eb;
        width: 100%;
    }

    .error-message, .success-message {
        font-size: 0.9rem;
    }

    .forgot-password, .register-link, .login-link {
        margin-top: 1.5rem;
    }

    .forgot-password a, .register-link a, .login-link a {
        color: #2563eb;
        font-weight: 500;
    }
}

/* ===== Extra Small Devices (e.g., iPhone SE) ===== */
@media (max-width: 375px) {
    .login-title, .register-title {
        font-size: 1.3rem;
    }

    .login-form, .register-form {
        gap: 1rem;
    }

    .btn-login, .btn-register {
        padding: 0.75rem;
    }
}
</style>
</head>


<body>

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
</body>
</html>

