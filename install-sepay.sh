#!/bin/bash

# Script để cài đặt SePay SDK trong Docker container WordPress

echo "🚀 Đang cài đặt SePay SDK..."

# Kiểm tra xem container có đang chạy không
if ! docker ps | grep -q nangtho_wp; then
    echo "❌ Container WordPress chưa chạy. Đang khởi động..."
    docker-compose up -d wordpress
    sleep 5
fi

# Cài đặt Composer trong container nếu chưa có
echo "📦 Đang kiểm tra Composer..."
docker exec nangtho_wp bash -c "command -v composer >/dev/null 2>&1 || { \
    echo 'Cài đặt Composer...'; \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
}"

# Chạy composer install trong container
echo "📥 Đang tải SePay SDK..."
docker exec -w /var/www/html nangtho_wp composer install --no-interaction

if [ $? -eq 0 ]; then
    echo "✅ Cài đặt SePay SDK thành công!"
    echo ""
    echo "📝 Bước tiếp theo:"
    echo "1. Vào WordPress Admin > WooCommerce > Settings > Payments"
    echo "2. Kích hoạt và cấu hình SePay gateway"
    echo "3. Nhập Merchant ID và Secret Key từ tài khoản SePay"
else
    echo "❌ Có lỗi xảy ra khi cài đặt. Vui lòng kiểm tra lại."
    exit 1
fi
