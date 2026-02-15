# Khắc phục sự cố SePay Gateway không hiển thị

## Vấn đề: SePay không xuất hiện trong danh sách Payment Methods

### Giải pháp 1: Clear cache và reload

1. **Clear WordPress cache** (nếu dùng cache plugin):
   - Vào Settings → Cache → Clear Cache
   - Hoặc deactivate và reactivate cache plugin

2. **Reload trang Payments**:
   - Refresh trang WooCommerce → Settings → Payments
   - Hoặc đăng xuất và đăng nhập lại

### Giải pháp 2: Kiểm tra file có được load không

Chạy lệnh sau trong Docker container để kiểm tra:

```bash
docker exec nangtho_wp php -l /var/www/html/wp-content/themes/nang-tho-cosmetics/includes/class-wc-gateway-sepay.php
```

Nếu có lỗi, sẽ hiển thị. Nếu không có lỗi, sẽ hiển thị "No syntax errors detected".

### Giải pháp 3: Kiểm tra SePay SDK

```bash
docker exec nangtho_wp test -f /var/www/html/vendor/autoload.php && echo "✅ SDK đã cài" || echo "❌ SDK chưa cài"
```

Nếu chưa cài, chạy lại:
```bash
docker exec nangtho_wp bash -c "cd /tmp && rm -rf sepay-install && mkdir -p sepay-install && cd sepay-install && echo '{\"require\":{\"sepay/sepay-pg\":\"^1.0.0\"}}' > composer.json && php /usr/local/bin/composer install --no-interaction && mkdir -p /var/www/html/vendor && rm -rf /var/www/html/vendor/* && cp -r /tmp/sepay-install/vendor/* /var/www/html/vendor/ && chown -R www-data:www-data /var/www/html/vendor"
```

### Giải pháp 4: Kiểm tra WordPress Debug Log

Bật WordPress debug mode trong `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Sau đó kiểm tra log:
```bash
docker exec nangtho_wp tail -50 /var/www/html/wp-content/debug.log | grep -i sepay
```

### Giải pháp 5: Kiểm tra filter registration

Đảm bảo gateway được đăng ký trong `functions.php`:

```php
function nang_tho_add_payment_gateway($gateways)
{
    $gateways[] = 'WC_Gateway_Vietnam_Bank_Transfer';
    $gateways[] = 'WC_Gateway_SePay';  // ← Phải có dòng này
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'nang_tho_add_payment_gateway');
```

### Giải pháp 6: Force reload gateway

Thêm code tạm thời vào `functions.php` để force load:

```php
add_action('init', function() {
    if (class_exists('WC_Payment_Gateway')) {
        require_once get_template_directory() . '/includes/class-wc-gateway-sepay.php';
    }
}, 20);
```

### Giải pháp 7: Kiểm tra theme activation

Đảm bảo theme "Nang Tho Cosmetics" đang được kích hoạt:
- Vào Appearance → Themes
- Đảm bảo theme đang active

### Giải pháp 8: Kiểm tra WooCommerce version

SePay gateway yêu cầu WooCommerce >= 3.0. Kiểm tra:
- Vào WooCommerce → Status
- Xem version WooCommerce

## Kiểm tra nhanh

Chạy script sau để kiểm tra tất cả:

```bash
echo "=== Kiểm tra SePay Gateway ===" && \
echo "1. File syntax:" && \
docker exec nangtho_wp php -l /var/www/html/wp-content/themes/nang-tho-cosmetics/includes/class-wc-gateway-sepay.php && \
echo "" && \
echo "2. SePay SDK:" && \
docker exec nangtho_wp test -f /var/www/html/vendor/autoload.php && echo "✅ SDK OK" || echo "❌ SDK missing" && \
echo "" && \
echo "3. Gateway class:" && \
docker exec nangtho_wp grep -q "class WC_Gateway_SePay" /var/www/html/wp-content/themes/nang-tho-cosmetics/includes/class-wc-gateway-sepay.php && echo "✅ Class exists" || echo "❌ Class missing" && \
echo "" && \
echo "4. Registration:" && \
docker exec nangtho_wp grep -q "WC_Gateway_SePay" /var/www/html/wp-content/themes/nang-tho-cosmetics/functions.php && echo "✅ Registered" || echo "❌ Not registered"
```

## Nếu vẫn không hiển thị

1. Kiểm tra WordPress error log
2. Kiểm tra PHP error log trong container
3. Thử deactivate và reactivate theme
4. Thử deactivate và reactivate WooCommerce
5. Kiểm tra xem có plugin nào conflict không
