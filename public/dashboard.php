<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

// Fetch user data
$stmt = $pdo->prepare("SELECT name, email, phone, city, country FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Process profile update
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $city = $_POST['city'];
        $country = $_POST['country'];
        
        $updateStmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, city = ?, country = ? WHERE id = ?");
        $updateStmt->execute([$name, $phone, $city, $country, $_SESSION['user_id']]);
        
        // Refresh user data
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $success = "Profile updated successfully!";
    }
    
    if (isset($_POST['change_password'])) {
        // Process password change
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password (you would need to implement this)
        // Then update password if verification passes
        // This is just a placeholder - implement proper password hashing
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $pwdStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $pwdStmt->execute([$hashed_password, $_SESSION['user_id']]);
            $pwd_success = "Password changed successfully!";
        } else {
            $pwd_error = "New passwords don't match!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #e0f2fe;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }
        
        .profile-sidebar {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            height: fit-content;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--primary);
            font-size: 3rem;
            font-weight: bold;
            border: 4px solid var(--primary-light);
        }
        
        .profile-name {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .profile-email {
            text-align: center;
            color: var(--primary);
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.2s ease;
            gap: 0.8rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: var(--secondary);
            color: var(--primary-dark);
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .section-title i {
            font-size: 1.4rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--dark);
            opacity: 0.6;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="profile-sidebar">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <h2 class="profile-name"><?= htmlspecialchars($user['name']) ?></h2>
            <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#profile" class="nav-link active" data-tab="profile">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#password" class="nav-link" data-tab="password">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($pwd_success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $pwd_success ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($pwd_error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= $pwd_error ?>
                </div>
            <?php endif; ?>
            
            <!-- Profile Tab -->
            <div id="profile" class="tab-content active">
                <h2 class="section-title">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" class="form-control" 
                               value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        <small class="text-muted">Contact support to change your email</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?= htmlspecialchars($user['phone']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="city" class="form-label">City</label>
                        <input type="text" id="city" name="city" class="form-control" 
                               value="<?= htmlspecialchars($user['city']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" id="country" name="country" class="form-control" 
                               value="<?= htmlspecialchars($user['country']) ?>">
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
            
            <!-- Password Tab -->
            <div id="password" class="tab-content">
                <h2 class="section-title">
                    <i class="fas fa-key"></i> Change Password
                </h2>
                
                <form method="POST">
                    <div class="form-group password-toggle">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                    
                    <div class="form-group password-toggle">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                    
                    <div class="form-group password-toggle">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Update Password
                    </button>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        // Tab switching functionality
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    
                    // Remove active class from all links and tabs
                    document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
                    
                    // Add active class to clicked link and corresponding tab
                    this.classList.add('active');
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                }
            });
        });
        
        // Password toggle functionality
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    this.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
    </script>
</body>
</html>