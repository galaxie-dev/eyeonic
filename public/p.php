<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Eyeonic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --accent: #facc15;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-dark: #0f172a;
            --text-light: #f8fafc;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background-color: #f8fafc;
        }
        
        /* Premium Header Styles */
        .premium-header {
            position: relative;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            padding: 0.5rem 0;
        }
        
        .header-scrolled {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 0.25rem 0;
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Logo with Animation */
        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .header-logo:hover {
            transform: scale(1.05);
        }
        
        .logo-icon {
            width: 36px;
            height: 36px;
            color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .header-logo:hover .logo-icon {
            transform: rotate(15deg);
        }
        
        .header-logo-text {
            font-weight: 800;
            font-size: 1.75rem;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
            position: relative;
        }
        
        .header-logo-text::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        }
        
        .header-logo:hover .header-logo-text::after {
            transform: scaleX(1);
            transform-origin: left;
        }
        
        /* Navigation */
        .header-nav {
            display: none;
            gap: 1.5rem;
            align-items: center;
        }
        
        .header-nav.active {
            display: flex;
            flex-direction: column;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid var(--glass-border);
            animation: slideDown 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .nav-link {
            position: relative;
            font-weight: 600;
            font-size: 1.05rem;
            color: var(--text-dark);
            text-decoration: none;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transition: width 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        }
        
        .nav-link:hover {
            color: var(--primary);
        }
        
        .nav-link:hover::before {
            width: 100%;
        }
        
        /* Icons and Badges */
        .nav-icons {
            display: flex;
            gap: 1.25rem;
            align-items: center;
        }
        
        .icon-btn {
            position: relative;
            background: none;
            border: none;
            color: var(--text-dark);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .icon-btn:hover {
            color: var(--primary);
            transform: translateY(-2px);
        }
        
        .count-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
            transition: all 0.3s ease;
        }
        
        .icon-btn:hover .count-badge {
            transform: scale(1.1);
            background: var(--accent);
        }
        
        /* Menu Button with Morphing Animation */
        .menu-button {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 44px;
            height: 44px;
            background: rgba(37, 99, 235, 0.1);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            padding: 0;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1001;
        }
        
        .menu-button:hover {
            background: rgba(37, 99, 235, 0.2);
            transform: rotate(90deg);
        }
        
        .menu-line {
            width: 20px;
            height: 2px;
            background: var(--primary);
            margin: 3px 0;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            transform-origin: center;
        }
        
        .menu-button.active .menu-line:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }
        
        .menu-button.active .menu-line:nth-child(2) {
            opacity: 0;
        }
        
        .menu-button.active .menu-line:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }
        
        /* Auth Buttons */
        .auth-buttons {
            display: flex;
            gap: 0.75rem;
            margin-left: 1rem;
        }
        
        .btn-login, .btn-register {
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .btn-login {
            background: transparent;
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-login:hover {
            background: rgba(37, 99, 235, 0.1);
            transform: translateY(-2px);
        }
        
        .btn-register {
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }
        
        /* User Dropdown */
        .user-dropdown {
            position: relative;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--glass-border);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 120%;
            right: 0;
            background: var(--glass-bg);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 0.5rem 0;
            min-width: 180px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1000;
        }
        
        .dropdown-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        
        .dropdown-item:hover {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            padding-left: 1.75rem;
        }
        
        .dropdown-divider {
            height: 1px;
            background: var(--glass-border);
            margin: 0.25rem 0;
        }
        
        /* Search Bar */
        .search-container {
            position: relative;
            margin-right: 1rem;
        }
        
        .search-input {
            width: 0;
            padding: 0;
            border: none;
            border-radius: 50px;
            background: rgba(37, 99, 235, 0.1);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            font-size: 0.95rem;
            color: var(--text-dark);
            outline: none;
        }
        
        .search-input.active {
            width: 200px;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
        }
        
        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            pointer-events: none;
        }
        
        /* Responsive Styles */
        @media (min-width: 992px) {
            .header-nav {
                display: flex;
                flex-direction: row;
            }
            
            .menu-button {
                display: none;
            }
            
            .search-input.active {
                width: 250px;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                padding: 0 1.5rem;
            }
            
            .auth-buttons {
                display: none;
            }
            
            .header-nav.active .auth-buttons {
                display: flex;
                flex-direction: column;
                width: 100%;
                margin-top: 1rem;
            }
            
            .btn-login, .btn-register {
                width: 100%;
                text-align: center;
                margin: 0.25rem 0;
            }
        }
    </style>
</head>
<body>
    <header class="premium-header">
        <div class="header-container">
            <div class="header-logo" onclick="window.location.href='index.php'">
                <svg class="logo-icon" aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
                <span class="header-logo-text">Eyeonic</span>
            </div>
            
            <nav class="header-nav">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="products.php" class="nav-link">
                    <i class="fas fa-glasses"></i>
                    <span>Shop</span>
                </a>
                <a href="wishlist.php" class="nav-link">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                    <span class="wishlist-count count-badge" style="display: none;">0</span>
                </a>
                <a href="cart.php" class="nav-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Cart</span>
                    <span class="cart-count count-badge" style="display: none;">0</span>
                </a>

                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="user-dropdown">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="user-avatar" onclick="toggleDropdown()">
                        <div class="dropdown-menu">
                            <a href="dashboard.php" class="dropdown-item">
                                <i class="fas fa-user-circle mr-2"></i>Dashboard
                            </a>
                            <a href="orders.php" class="dropdown-item">
                                <i class="fas fa-shopping-bag mr-2"></i>My Orders
                            </a>                         
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="login.php" class="btn-login">Login</a>
                        <a href="register.php" class="btn-register">Register</a>
                    </div>
                <?php endif; ?>
            </nav>
            
        </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Header scroll effect
        const header = document.querySelector('.premium-header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        });
        
        // Toggle mobile menu
        function toggleMenu() {
            const menuButton = document.querySelector('.menu-button');
            const nav = document.querySelector('.header-nav');
            menuButton.classList.toggle('active');
            nav.classList.toggle('active');
            
            // Toggle body scroll when menu is open
            if (nav.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        // Toggle search bar
        const searchToggle = document.querySelector('.search-toggle');
        const searchInput = document.querySelector('.search-input');
        
        searchToggle.addEventListener('click', () => {
            searchInput.classList.toggle('active');
            if (searchInput.classList.contains('active')) {
                searchInput.focus();
            }
        });
        
        // Close search when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchToggle.contains(e.target) && !searchInput.contains(e.target)) {
                searchInput.classList.remove('active');
            }
        });
        
        // Toggle user dropdown
        function toggleDropdown() {
            const dropdown = document.querySelector('.dropdown-menu');
            dropdown.classList.toggle('active');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.querySelector('.dropdown-menu');
            const avatar = document.querySelector('.user-avatar');
            
            if (dropdown.classList.contains('active') && !avatar.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
        
        // Update counts when page loads
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll('.cart-count').forEach(el => {
                    el.textContent = data.count;
                    el.style.display = data.count > 0 ? 'flex' : 'none';
                });
            });

        <?php if(isset($_SESSION['user_id'])): ?>
        fetch('get_wishlist_count.php')
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll('.wishlist-count').forEach(el => {
                    el.textContent = data.count;
                    el.style.display = data.count > 0 ? 'flex' : 'none';
                });
            });
        <?php endif; ?>
        
        // Add parallax effect to logo on mousemove
        const logo = document.querySelector('.header-logo');
        document.addEventListener('mousemove', (e) => {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            logo.style.transform = `translate(${x * 10 - 5}px, ${y * 10 - 5}px)`;
        });
    });
    </script>
</body>
</html>