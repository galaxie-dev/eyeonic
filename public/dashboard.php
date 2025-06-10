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
            padding-bottom: 70px; /* Space for mobile nav */
        }
        
        .dashboard-container {
            max-width: 100%;
            margin: 0;
            padding: 1rem;
        }
        
        .profile-header {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .profile-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: bold;
            border: 3px solid var(--primary-light);
        }
        
        .profile-info {
            flex: 1;
        }
        
        .profile-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: var(--dark);
        }
        
        .profile-email {
            color: var(--primary);
            font-size: 0.8rem;
        }
        
        .edit-btn {
            background: none;
            border: none;
            color: var(--primary);
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .profile-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .section-title i {
            font-size: 1.1rem;
        }
        
        .info-item {
            margin-bottom: 1.2rem;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.3rem;
        }
        
        .info-value {
            font-size: 1rem;
            font-weight: 500;
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
            width: 100%;
            justify-content: center;
            margin-top: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        .btn-secondary {
            background-color: #e2e8f0;
            color: var(--dark);
        }
        
        .btn-secondary:hover {
            background-color: #cbd5e1;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
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
        
        /* Edit Popup */
        .edit-popup {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            z-index: 1000;
            padding: 1.5rem;
            overflow-y: auto;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .edit-popup.active {
            transform: translateY(0);
        }
        
        .popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .popup-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-dark);
        }
        
        .close-popup {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.8rem;
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
        
        /* Password toggle */
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
        
        /* Mobile Navigation */
        .mobile-nav {
            display: flex;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            padding: 8px 0;
        }
        
        .mobile-nav-items {
            display: flex;
            justify-content: space-around;
            width: 100%;
        }
        
        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #64748b;
            font-size: 0.7rem;
            padding: 5px;
        }
        
        .mobile-nav-item i {
            font-size: 1.2rem;
            margin-bottom: 4px;
        }
        
        .mobile-nav-item.active {
            color: var(--primary);
        }
        
        @media (min-width: 768px) {
            body {
                padding-bottom: 0;
            }
            
            .dashboard-container {
                max-width: 600px;
                margin: 0 auto;
                padding: 2rem 1rem;
            }
            
            .mobile-nav {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
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
        
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div class="profile-info">
                <h2 class="profile-name"><?= htmlspecialchars($user['name']) ?></h2>
                <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <button class="edit-btn" id="openEditProfile">
                <i class="fas fa-edit"></i>
            </button>
        </div>
        
        <div class="profile-section">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i> Personal Information
            </h3>
            
            <div class="info-item">
                <div class="info-label">Phone Number</div>
                <div class="info-value"><?= htmlspecialchars($user['phone'] ?: 'Not provided') ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Location</div>
                <div class="info-value">
                    <?php 
                    $location = [];
                    if (!empty($user['city'])) $location[] = htmlspecialchars($user['city']);
                    if (!empty($user['country'])) $location[] = htmlspecialchars($user['country']);
                    echo $location ? implode(', ', $location) : 'Not provided';
                    ?>
                </div>
            </div>
        </div>
        
        <div class="profile-section">
            <h3 class="section-title">
                <i class="fas fa-key"></i> Account Security
            </h3>
            
            <button class="btn btn-secondary" id="openChangePassword">
                <i class="fas fa-key"></i> Change Password
            </button>
        </div>
        
        <button class="btn btn-secondary" onclick="window.location.href='products.php'">
            <i class="fas fa-arrow-left"></i> Continue Shopping
        </button>
        
        <!-- Edit Profile Popup -->
        <div class="edit-popup" id="editProfilePopup">
            <div class="popup-header">
                <h3 class="popup-title">Edit Profile</h3>
                <button class="close-popup" id="closeEditProfile">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
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
                    <small style="color: #64748b; font-size: 0.8rem;">Contact support to change your email</small>
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
        
        <!-- Change Password Popup -->
        <div class="edit-popup" id="changePasswordPopup">
            <div class="popup-header">
                <h3 class="popup-title">Change Password</h3>
                <button class="close-popup" id="closeChangePassword">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
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
    </div>
    
    
    <script>
        // Edit Profile Popup
        const openEditProfile = document.getElementById('openEditProfile');
        const closeEditProfile = document.getElementById('closeEditProfile');
        const editProfilePopup = document.getElementById('editProfilePopup');
        
        openEditProfile.addEventListener('click', () => {
            editProfilePopup.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        closeEditProfile.addEventListener('click', () => {
            editProfilePopup.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        // Change Password Popup
        const openChangePassword = document.getElementById('openChangePassword');
        const closeChangePassword = document.getElementById('closeChangePassword');
        const changePasswordPopup = document.getElementById('changePasswordPopup');
        
        openChangePassword.addEventListener('click', () => {
            changePasswordPopup.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        closeChangePassword.addEventListener('click', () => {
            changePasswordPopup.classList.remove('active');
            document.body.style.overflow = '';
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
        
        // Close popups when clicking outside content
        document.querySelectorAll('.edit-popup').forEach(popup => {
            popup.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
</body>
</html>
<?php include 'mobile-menu.php'; ?>