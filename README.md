# Eyeonic

**Eyeonic** is a modern, mobile-friendly e-commerce web application for eyewear products. Built with PHP, MySQL and CSS, it offers a seamless shopping experience complete with user authentication, shopping cart, wishlist, responsive UI, and admin management features.

## Features

- **Product Catalog**: Browse eyewear products by category.
- **Mobile-First UI**: Responsive navigation, touch-friendly design, and mobile bottom navigation.
- **User Accounts**: Customer login, registration, and session management.
- **Shopping Cart**: Add, remove, and update products in your cart.
- **Wishlist**: Save products to your wishlist for later.
- **Search**: Quickly find products with a sticky, stylish search bar.
- **Customer Reviews & Testimonials**: Showcase customer feedback.
- **Admin Panel**: (in `/admin/`) for managing products, categories, and orders.
- **Fast Checkout**: Streamlined purchase process.
- **Commitment Section**: Highlights quality, shipping, and satisfaction guarantees.

## Technology Stack

- **Backend**: PHP (session management, routing, server-side logic)
- **Frontend**: HTML, CSS, and inline SVG icons
- **Database**: MySQLr MariaDB with PHP)
- **Other**: Custom CSS for responsive layouts and effects

## Repository Structure

```
index.php                   # Redirects to public/index.php
/public/
    index.php               # Main homepage, dynamic content
    cart.php                # Shopping cart
    wishlist.php            # Wishlist page
    categories.php          # Product categories
    header.php              # Header/navigation bar
    search-bar.php          # Sticky search bar
    mobile-menu.php         # Mobile navigation
/admin/
    includes/auth.php       # Admin authentication
    logout.php              # Session logout
...
```

_Note: Only partial files are shown above for brevity. [See the full file list in the GitHub repository.](https://github.com/osumba404/eyeonic)_

## Quick Start

1. **Clone the Repository**
   ```bash
   git clone https://github.com/osumba404/eyeonic.git
   ```

2. **Set Up Your Environment**
   - Make sure you have PHP installed (version 7.4+ recommended).
   - Deploy the code to your web server (e.g., Apache).
   - Configure your database (see `config.php`).
   - Update file permissions as needed.

3. **Browse the Application**
   - Open `public/index.php` in your browser to view the storefront.
   - Access `/admin/` for admin management (authentication required).

## Customization

- **Styling**: Modify the CSS in each PHP file or extract to separate `.css` files for maintainability.
- **Products & Categories**: Update/add products and categories through the admin panel or directly in the database.

## Credits

- Developed by [osumba404](https://github.com/osumba404)
- Customer icons and SVGs are open source.

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.

---

> For questions, bugs, or feature requests, please use the [Issues](https://github.com/osumba404/eyeonic/issues) section on GitHub.
