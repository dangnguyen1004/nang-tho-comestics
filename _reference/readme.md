# Nang Tho Cosmetics - WordPress Theme

A custom WordPress/WooCommerce theme for a cosmetics e-commerce website, optimized for the Vietnamese market with local payment methods and user-friendly interface.

## Features

- **Homepage**: Hero carousel, product categories, new arrivals, best sellers, flash sale sections
- **Shop Page**: Product grid with sidebar filters (category, price, brand, stock status), search, and sorting
- **Product Detail**: Large product images, detailed information, reviews, related products
- **Cart**: Product list with quantity controls and order summary
- **Checkout**: Customer information form, shipping options, payment methods (COD, bank transfer, e-wallets)

## Requirements

- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.4+ (PHP 8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.3+

## Installation

### Manual Installation

1. Copy the `wp-content/themes/nang-tho-cosmetics` folder to your WordPress `wp-content/themes/` directory
2. Activate the theme in WordPress Admin → Appearance → Themes
3. Install and activate WooCommerce plugin
4. Run WooCommerce Setup Wizard

### Docker Development

```bash
# Start containers
docker-compose up -d

# Access WordPress
# http://localhost:8080

# Access phpMyAdmin
# http://localhost:8081
```

## Project Structure

```
wp-content/themes/nang-tho-cosmetics/
├── assets/
│   ├── css/          # Stylesheets
│   └── js/           # JavaScript files
├── includes/         # Custom classes (payment gateway)
├── template-parts/   # Reusable template parts
│   ├── home/        # Homepage sections
│   └── shop/        # Shop page components
├── woocommerce/      # WooCommerce template overrides
├── functions.php     # Theme functions and hooks
├── style.css        # Main stylesheet
└── ...
```

## Customization

- **Colors**: Edit `style.css` or use WordPress Customizer
- **Templates**: Modify files in `woocommerce/` directory
- **Template Parts**: Add new parts in `template-parts/` and call with `get_template_part()`

## Development

### Docker Services

- **WordPress**: Port 8080
- **MySQL**: Database
- **phpMyAdmin**: Port 8081

### Best Practices

- Follow WordPress and WooCommerce coding standards
- Use hooks (actions/filters) instead of modifying core files
- Use `wp_enqueue_script()` and `wp_enqueue_style()` for assets
- Validate and sanitize all user input
- Use nonces for form submissions

## Author

**Antigravity**

## License

GNU General Public License v2 or later

---

**Version**: 1.0.1
