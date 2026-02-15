#!/bin/bash

echo "=== 🔍 Kiểm tra SePay Gateway ==="
echo ""

echo "1. ✅ Kiểm tra file syntax:"
docker exec nangtho_wp php -l /var/www/html/wp-content/themes/nang-tho-cosmetics/includes/class-wc-gateway-sepay.php 2>&1 | grep -E "(No syntax errors|Parse error|Fatal error)" || echo "   ⚠️ Có lỗi syntax"
echo ""

echo "2. ✅ Kiểm tra SePay SDK:"
if docker exec nangtho_wp test -f /var/www/html/vendor/autoload.php; then
    echo "   ✅ SDK đã được cài đặt"
    docker exec nangtho_wp test -d /var/www/html/vendor/sepay && echo "   ✅ SePay package có trong vendor" || echo "   ❌ SePay package không có trong vendor"
else
    echo "   ❌ SDK chưa được cài đặt"
fi
echo ""

echo "3. ✅ Kiểm tra Gateway class:"
if docker exec nangtho_wp grep -q "class WC_Gateway_SePay" /var/www/html/wp-content/themes/nang-tho-cosmetics/includes/class-wc-gateway-sepay.php; then
    echo "   ✅ Class WC_Gateway_SePay tồn tại"
else
    echo "   ❌ Class không tồn tại"
fi
echo ""

echo "4. ✅ Kiểm tra đăng ký gateway:"
if docker exec nangtho_wp grep -q "WC_Gateway_SePay" /var/www/html/wp-content/themes/nang-tho-cosmetics/functions.php; then
    echo "   ✅ Gateway đã được đăng ký trong functions.php"
else
    echo "   ❌ Gateway chưa được đăng ký"
fi
echo ""

echo "5. ✅ Kiểm tra file include:"
if docker exec nangtho_wp grep -q "class-wc-gateway-sepay.php" /var/www/html/wp-content/themes/nang-tho-cosmetics/functions.php; then
    echo "   ✅ File được include trong functions.php"
else
    echo "   ❌ File chưa được include"
fi
echo ""

echo "6. ✅ Kiểm tra SePay classes có thể load:"
docker exec nangtho_wp php -r "require '/var/www/html/vendor/autoload.php'; echo class_exists('SePay\SePayClient') ? '   ✅ SePay\SePayClient có thể load' : '   ❌ SePay\SePayClient không thể load'; echo PHP_EOL;" 2>&1 | grep -v "Warning\|Notice" || echo "   ❌ Không thể load autoload"
echo ""

echo "=== 📋 Tóm tắt ==="
echo ""
echo "Nếu tất cả đều ✅, hãy thử:"
echo "1. Refresh trang WooCommerce → Settings → Payments"
echo "2. Clear cache (nếu có)"
echo "3. Đăng xuất và đăng nhập lại WordPress"
echo ""
echo "Nếu có ❌, xem file SEPAY_TROUBLESHOOTING.md để khắc phục"
