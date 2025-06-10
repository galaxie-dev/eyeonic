<head>
<style>
    /* Mobile Bottom Navigation */
    /* Mobile Bottom Navigation */
    :root {
        --primary: #2563eb;
        --primary-light: #3b82f6;
        --primary-dark: #1d4ed8;
        --secondary: #e0f2fe;
        --dark: #1e293b;
        --light: #f8fafc;
        --accent: #f43f5e;
        --success: #10b981;
    }
    
    /* Hide mobile nav on desktop screens */
    @media (min-width: 769px) {
        .mobile-nav {
            display: none !important;
        }
    }
    
    /* Show mobile nav on mobile screens by default */
    .mobile-nav {
        display: block;
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
        align-items: center;
    }

    .mobile-nav-item {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #64748b;
        font-size: 0.7rem;
        padding: 5px;
    }

    .mobile-nav-item svg {
        width: 20px;
        height: 20px;
        margin-bottom: 4px;
    }

    .mobile-nav-item.active {
        color: var(--primary);
    }

    /* Count badge styles */
    .count-badge {
        position: absolute;
        top: -2px;
        right: 8px;
        background-color: var(--accent);
        color: white;
        border-radius: 50%;
        width: 16px;
        height: 16px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    /* Wishlist icon specific styling */
    .wishlist-icon {
        position: relative;
        display: inline-block;
    }
</style>
</head>

<body>
<!-- Mobile Bottom Navigation -->
<div class="mobile-nav">
    <div class="mobile-nav-items">
        <a href="index.php" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Home
        </a>
        <a href="products.php" class="mobile-nav-item active">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Shop
        </a>
        <a href="cart.php" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span class="cart-count count-badge" style="display: none;">0</span>
            Cart
        </a>
        <a href="wishlist.php" class="mobile-nav-item">
            <div class="wishlist-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <span class="wishlist-count count-badge" style="display: none;">0</span>
            </div>
            Wishlist
        </a>
        <a href="dashboard.php" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Account
        </a>
    </div>
</div>

<script>
// Update cart and wishlist counts on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = data.count;
                el.style.display = data.count > 0 ? 'flex' : 'none';
            });
        });

    <?php if(isset($_SESSION['user_id'])): ?>
    // Update wishlist count only if user is logged in
    fetch('get_wishlist_count.php')
        .then(response => response.json())
        .then(data => {
            if(data.count !== undefined) {
                document.querySelectorAll('.wishlist-count').forEach(el => {
                    el.textContent = data.count;
                    el.style.display = data.count > 0 ? 'flex' : 'none';
                });
            }
        });
    <?php endif; ?>
    
    // Highlight current page in mobile nav
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.mobile-nav-item').forEach(item => {
        item.classList.remove('active');
        if (item.getAttribute('href') === currentPage) {
            item.classList.add('active');
        }
    });
});

// You can keep these separate functions if you need to call them from other parts of your code
function updateCartCount() {
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = data.count;
                el.style.display = data.count > 0 ? 'flex' : 'none';
            });
        });
}

function updateWishlistCount() {
    <?php if(isset($_SESSION['user_id'])): ?>
    fetch('get_wishlist_count.php')
        .then(response => response.json())
        .then(data => {
            if(data.count !== undefined) {
                document.querySelectorAll('.wishlist-count').forEach(el => {
                    el.textContent = data.count;
                    el.style.display = data.count > 0 ? 'flex' : 'none';
                });
            }
        });
    <?php endif; ?>
}
</script>
</body>