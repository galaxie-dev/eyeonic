<head>
<style>
    :root {
        --primary: #2563eb;
        --primary-light: #3b82f6;
        --primary-dark: #1d4ed8;
        --secondary: #e0f2fe;
        --dark: #1e293b;
        --light: #f8fafc;
        --accent: #f43f5e;
        --success: #10b981;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.2);
    }
    
    /* Hide mobile nav on desktop screens */
    @media (min-width: 769px) {
        .mobile-nav {
            display: none !important;
        }
    }
    
.mobile-nav {
    display: block;
    position: fixed;
    bottom: 1rem;
    left: 50%;
    transform: translateX(-50%);
    width: calc(100% - 2rem);
    max-width: 400px;
    background: white; /* Changed from glass-bg to solid white */
    border-radius: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    padding: 12px;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
}


    .mobile-nav-items {
        display: flex;
        justify-content: space-around;
        align-items: center;
        position: relative;
    }

    .mobile-nav-item {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: var(--dark);
        font-size: 0.65rem;
        font-weight: 500;
        padding: 8px 12px;
        border-radius: 16px;
        transition: all 0.3s ease;
        z-index: 1;
    }

    .mobile-nav-item svg {
        width: 22px;
        height: 22px;
        margin-bottom: 4px;
        stroke-width: 2;
        transition: all 0.3s ease;
    }

    .mobile-nav-item.active {
        color: var(--primary);
    }

    .mobile-nav-item.active svg {
        stroke: var(--primary);
        transform: translateY(-4px);
    }

    /* Active item highlight */
    /* .mobile-nav-highlight {
        position: absolute;
        bottom: 8px;
        left: 0;
        width: 20%;
        height: calc(100% - 16px);
        background: rgba(37, 99, 235, 0.1);
        border-radius: 14px;
        border: 1px solid rgba(37, 99, 235, 0.15);
        backdrop-filter: blur(4px);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        z-index: 0;
    } */

    /* Count badge styles */
    .count-badge {
        position: absolute;
        top: -2px;
        right: 0;
        background-color: var(--accent);
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(244, 63, 94, 0.3);
        transform: scale(1);
        transition: transform 0.2s ease;
    }

    .mobile-nav-item:hover .count-badge {
        transform: scale(1.15);
    }

    /* Wishlist icon specific styling */
    .wishlist-icon {
        position: relative;
        display: inline-block;
    }

    /* Pulse animation for active item */
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.2); }
        70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }

    .mobile-nav-item.active::after {
        content: '';
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 6px;
        height: 6px;
        background: var(--primary);
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }

    /* Hover effects */
    .mobile-nav-item:hover {
        color: var(--primary);
    }

    .mobile-nav-item:hover svg {
        stroke: var(--primary);
        transform: translateY(-2px);
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
        .mobile-nav {
            bottom: 0.5rem;
            width: calc(100% - 1rem);
            border-radius: 20px;
        }
        
        .mobile-nav-item {
            padding: 6px 8px;
            font-size: 0.6rem;
        }
        
        .mobile-nav-item svg {
            width: 20px;
            height: 20px;
        }
    }
</style>
</head>

<body>
<!-- Mobile Navigation -->
<div class="mobile-nav">
    <div class="mobile-nav-highlight"></div>
    <div class="mobile-nav-items">
        <a href="index.php" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Home
        </a>
        <a href="products.php" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Shop
        </a>
        <a href="<?php echo isset($_SESSION['user_id']) ? 'cart.php' : 'login.php?redirect=cart.php'; ?>" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span class="cart-count count-badge" style="display: none;">0</span>
            Cart
        </a>
        <a href="<?php echo isset($_SESSION['user_id']) ? 'orders.php' : 'login.php?redirect=orders.php'; ?>" class="mobile-nav-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span class="order-count count-badge" style="display: none;">0</span>
            My Orders
        </a>
        <a href="<?php echo isset($_SESSION['user_id']) ? 'wishlist.php' : 'login.php?redirect=wishlist.php'; ?>" class="mobile-nav-item">
            <div class="wishlist-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <span class="wishlist-count count-badge" style="display: none;">0</span>
            </div>
            Wishlist
        </a>
        <a href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'login.php?redirect=dashboard.php'; ?>" class="mobile-nav-item">
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
        
    // Update orders count only if user is logged in
    fetch('get_orders_count.php')
        .then(response => response.json())
        .then(data => {
            if(data.count !== undefined) {
                document.querySelectorAll('.order-count').forEach(el => {
                    el.textContent = data.count;
                    el.style.display = data.count > 0 ? 'flex' : 'none';
                });
            }
        });
    <?php endif; ?>
    
    // Highlight current page in mobile nav
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const navItems = document.querySelectorAll('.mobile-nav-item');
    const highlight = document.querySelector('.mobile-nav-highlight');
    
    navItems.forEach((item, index) => {
        // Remove active class from all items
        item.classList.remove('active');
        
        // Check if current page matches href (without query params)
        const href = item.getAttribute('href').split('?')[0];
        if (href === currentPage) {
            item.classList.add('active');
            
            // Position the highlight
            const itemWidth = 100 / navItems.length;
            highlight.style.left = `${index * itemWidth}%`;
            highlight.style.width = `${itemWidth}%`;
        }
    });
    
    // Add click animation to all nav items
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // For protected pages when not logged in
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?> && 
                ['cart.php', 'orders.php', 'wishlist.php', 'dashboard.php'].some(page => this.href.includes(page))) {
                // Show login notification
                showNotification('Please login to access this page');
                return;
            }
            
            // Remove active class from all items
            navItems.forEach(navItem => navItem.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Move highlight
            const index = Array.from(navItems).indexOf(this);
            const itemWidth = 100 / navItems.length;
            highlight.style.left = `${index * itemWidth}%`;
            
            // Add ripple effect
            const ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.backgroundColor = 'rgba(37, 99, 235, 0.2)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.pointerEvents = 'none';
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${e.clientX - rect.left - size/2}px`;
            ripple.style.top = `${e.clientY - rect.top - size/2}px`;
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});

// Notification function (make sure this exists in your code)
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'mobile-notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add ripple animation style
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(2.5);
            opacity: 0;
        }
    }
    
    .mobile-notification {
        position: fixed;
        bottom: 80px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    }
    
    .mobile-notification.fade-out {
        animation: fadeOut 0.3s ease-in;
    }
    
    @keyframes slideIn {
        from { bottom: 50px; opacity: 0; }
        to { bottom: 80px; opacity: 1; }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
`;
document.head.appendChild(style);

// Update functions that can be called from other scripts
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

function updateOrdersCount() {
    <?php if(isset($_SESSION['user_id'])): ?>
    fetch('get_orders_count.php')
        .then(response => response.json())
        .then(data => {
            if(data.count !== undefined) {
                document.querySelectorAll('.order-count').forEach(el => {
                    el.textContent = data.count;
                    el.style.display = data.count > 0 ? 'flex' : 'none';
                });
            }
        });
    <?php endif; ?>
}
</script>
</body>