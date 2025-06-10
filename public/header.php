<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <title>Eyeonic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
        }
        header {
            background-color: transparent;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            width: 100%;
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .header-logo svg {
            width: 28px;
            height: 28px;
            color: #2563eb;
        }
        .header-logo-text {
            font-weight: 800;
            font-size: 1.5rem;
            color: #2563eb;
            user-select: none;
        }
        .header-nav {
            display: none;
            gap: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .header-nav.active {
            display: flex;
            flex-direction: column;
            width: 100%;
            margin-top: 1rem;
        }
        .header-nav a {
            font-weight: 600;
            font-size: 1rem;
            color: #2563eb;
            text-decoration: none;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            position: relative;
        }
        .header-nav a:hover {
            color: #facc15;
        }
        .menu-button {
            display: block;
            background: none;
            border: none;
            color: #2563eb;
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
        }
        .menu-button:hover {
            background-color: #dbeafe;
        }
        .nav-icons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .count-badge {
            position: absolute;
            top: -20px;
            right: -28px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (min-width: 768px) {
            .header-nav {
                display: flex;
                flex-direction: row;
            }
            .menu-button {
                display: none;
            }
        }
    </style>
    <script>
        function toggleMenu() {
            const nav = document.querySelector('.header-nav');
            nav.classList.toggle('active');
        }
    </script>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="header-logo">
                <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
                <span class="header-logo-text">Eyeonic</span>
            </div>
            <nav class="header-nav">
                <a href="index.php">Home</a>
                <a href="products.php">Shop</a>
                <a href="wishlist.php" class="nav-link">
                    Wishlist
                    <span class="wishlist-count count-badge" style="display: none;">0</span>
                </a>
                <a href="cart.php" class="nav-link">
                    Cart
                    <span class="cart-count count-badge" style="display: none;">0</span>
                </a>              
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
            <button class="menu-button" aria-label="Toggle Menu" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update counts when page loads
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll('.cart-count').forEach(el => {
                    el.textContent = data.count;
                    el.style.display = data.count > 0 ? 'inline-block' : 'none';
                });
            });

        <?php if(isset($_SESSION['user_id'])): ?>
        fetch('get_wishlist_count.php')
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll('.wishlist-count').forEach(el => {
                    el.textContent = data.count;
                    el.style.display = data.count > 0 ? 'inline-block' : 'none';
                });
            });
        <?php endif; ?>
    });
    </script>