# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WordPress/WooCommerce e-commerce site for a Vietnamese cosmetics brand ("Nang Tho Cosmetics"). Custom theme with two payment gateways tailored for the Vietnamese market: SePay (VietQR/card) and Vietnam Bank Transfer.

## Development Environment

### Starting the dev stack

```bash
docker compose up -d
```

- WordPress: http://localhost:8080
- phpMyAdmin: http://localhost:8081

### Production deployment

```bash
cp env.example .env
# Edit .env with real credentials
docker compose -f docker-compose.prod.yml up -d
```

### PHP dependencies (SePay SDK)

```bash
composer install
```

The vendor directory is mounted into the WordPress container at `/var/www/html/vendor`.

## Architecture

### Theme

All custom code lives in `wp-content/themes/nang-tho-cosmetics/`. The theme is a **standalone custom theme** (not a child theme) with full WooCommerce template overrides.

- `functions.php` — Theme bootstrap: registers nav menus, theme supports, enqueues assets, loads payment gateways, and defines custom nav walker
- `includes/` — Custom payment gateway classes loaded by functions.php
- `woocommerce/` — WooCommerce template overrides (shop archive, single product, checkout, reviews)
- `template-parts/home/` — Homepage section partials (hero, categories, flash-sale, best-sellers, brands)
- `template-parts/shop/` — Shop page partials (sidebar filters, grid header)
- `assets/js/` — Custom JS for shop filters, checkout (Vietnam-specific address logic), and product detail interactions

### Payment Gateways

Both gateways are registered as WooCommerce payment methods from within the theme (not as separate plugins):

1. **SePay** (`class-wc-gateway-sepay.php`) — Integrates `SePay\SePayClient` from `vendor/autoload.php`. The class tries three autoload paths to handle both local dev and Docker environments.

2. **Vietnam Bank Transfer** (`class-wc-gateway-vietnam-bank-transfer.php`) — Manual bank transfer with configurable bank account fields. Shows payment instructions on thank-you page and in order emails.

### Composer / PSR-4

`composer.json` maps the `NangTho\` namespace to `wp-content/themes/nang-tho-cosmetics/includes/`. The SePay SDK (`sepay/sepay-pg`) and Guzzle are the primary dependencies.

### Docker volumes (dev)

| Host path | Container path |
|---|---|
| `wp-content/themes/nang-tho-cosmetics/` | `/var/www/html/wp-content/themes/nang-tho-cosmetics/` |
| `vendor/` | `/var/www/html/vendor/` |
| `composer.json` | `/var/www/html/composer.json` |

Database credentials in dev: DB `nangtho_cosmetics`, user `user`/`password`.

## Key Conventions

- Templates follow WooCommerce override conventions — any file under `woocommerce/` mirrors the plugin's template path.
- CSS uses Tailwind directives in `style.css`; compiled output goes to `dist/`.
- Vietnamese-language strings appear inline in PHP templates (not in a `.pot` file). No i18n/l10n pipeline is set up.
- PHP minimum: 7.4 (8.0+ recommended). WordPress 5.0+, WooCommerce 5.0+.
