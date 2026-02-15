# Hướng dẫn cài đặt SePay SDK trong Docker

## Cách 1: Sử dụng script tự động (Khuyến nghị)

Chạy script sau để tự động cài đặt:

```bash
./install-sepay.sh
```

Script này sẽ:
- Kiểm tra và khởi động Docker container nếu cần
- Cài đặt Composer trong container
- Tải và cài đặt SePay SDK

## Cách 2: Chạy thủ công trong Docker container

### Bước 1: Khởi động Docker container

```bash
docker-compose up -d
```

### Bước 2: Cài đặt Composer trong container (nếu chưa có)

```bash
docker exec nangtho_wp bash -c "curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer"
```

### Bước 3: Chạy composer install trong container

```bash
docker exec -w /var/www/html nangtho_wp composer install
```

## Cách 3: Sử dụng Composer từ máy local (nếu đã cài)

Nếu bạn đã cài Composer trên máy local, có thể chạy trực tiếp:

```bash
composer install
```

**Lưu ý**: Với cách này, thư mục `vendor` sẽ được tạo trên máy local và cần được mount vào container.

## Kiểm tra cài đặt

Sau khi cài đặt, kiểm tra xem SePay SDK đã được cài đặt chưa:

```bash
docker exec nangtho_wp ls -la /var/www/html/vendor/sepay
```

Hoặc kiểm tra file autoload:

```bash
docker exec nangtho_wp test -f /var/www/html/vendor/autoload.php && echo "✅ Đã cài đặt thành công" || echo "❌ Chưa cài đặt"
```

## Xử lý lỗi

### Lỗi: "Container không chạy"

```bash
docker-compose up -d
```

### Lỗi: "Composer không tìm thấy"

Cài đặt Composer trong container:

```bash
docker exec nangtho_wp bash -c "curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer"
```

### Lỗi: "Permission denied"

Đảm bảo thư mục vendor có quyền ghi:

```bash
docker exec nangtho_wp chown -R www-data:www-data /var/www/html/vendor
```

## Cấu trúc sau khi cài đặt

```
nang-tho-cosmetics/
├── vendor/                    # Thư mục chứa SePay SDK (được mount vào container)
│   ├── sepay/
│   │   └── sepay-pg/
│   └── autoload.php
├── composer.json
├── composer.lock
└── wp-content/
    └── themes/
        └── nang-tho-cosmetics/
            └── includes/
                └── class-wc-gateway-sepay.php
```

## Lưu ý quan trọng

1. **Thư mục vendor**: Đã được mount vào container qua `docker-compose.yml`, nên bạn có thể cài đặt từ máy local hoặc trong container đều được.

2. **Autoload path**: File `class-wc-gateway-sepay.php` đã được cấu hình để tự động load `vendor/autoload.php` từ thư mục gốc WordPress.

3. **Production**: Khi deploy lên production, đảm bảo chạy `composer install --no-dev` để không cài các package development.
