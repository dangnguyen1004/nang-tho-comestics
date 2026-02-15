# Hướng dẫn tích hợp SePay Payment Gateway

Tài liệu này hướng dẫn cách cài đặt và cấu hình SePay Payment Gateway cho website Nang Tho Cosmetics.

## Yêu cầu

- PHP >= 7.4
- WordPress với WooCommerce đã được cài đặt
- Composer (để cài đặt SePay SDK)
- Tài khoản SePay (Merchant ID và Secret Key)

## Cài đặt

### Bước 1: Cài đặt SePay SDK

**Nếu bạn dùng Docker (khuyến nghị):**

Chạy script tự động:
```bash
./install-sepay.sh
```

Hoặc chạy thủ công trong Docker container:
```bash
# Khởi động container
docker-compose up -d

# Cài đặt Composer trong container (nếu chưa có)
docker exec nangtho_wp bash -c "curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer"

# Cài đặt SePay SDK
docker exec -w /var/www/html nangtho_wp composer install
```

**Nếu bạn không dùng Docker:**

Chạy lệnh sau trong thư mục gốc của dự án (nơi có file `composer.json`):

```bash
composer install
```

Xem thêm chi tiết trong file `SEPAY_INSTALL.md`.

### Bước 2: Cấu hình Payment Gateway

1. Đăng nhập vào WordPress Admin
2. Vào **WooCommerce** > **Settings** > **Payments**
3. Tìm và kích hoạt **SePay** payment gateway
4. Click vào **SePay** để cấu hình:

   - **Bật/Tắt**: Chọn "Bật phương thức thanh toán SePay"
   - **Tiêu đề**: Tiêu đề hiển thị cho khách hàng (mặc định: "SePay")
   - **Mô tả**: Mô tả phương thức thanh toán
   - **Merchant ID**: Nhập Merchant ID từ tài khoản SePay của bạn
     - Format: `SP-LIVE-XXXXXXX` (production) hoặc `SP-TEST-XXXXXXX` (sandbox)
   - **Secret Key**: Nhập Secret Key từ tài khoản SePay
     - Format: `spsk_live_xxxxxxxxxxx` (production) hoặc `spsk_test_xxxxxxxxxxx` (sandbox)
   - **Môi trường**: 
     - **Sandbox**: Dùng để test thanh toán
     - **Production**: Dùng cho thanh toán thực tế

5. Click **Save changes**

### Bước 3: Cấu hình Callback URLs (Quan trọng)

Trong tài khoản SePay của bạn, cần cấu hình các URL sau:

**Success URL:**
```
https://yourdomain.com/checkout/order-received/
```

**Error/Cancel URL:**
```
https://yourdomain.com/checkout/
```

**Callback URL (IPN):**
```
https://yourdomain.com/?wc-api=sepay_callback
```

Lưu ý: Thay `yourdomain.com` bằng domain thực tế của bạn.

## Kiểm thử

### Test với Sandbox

1. Đảm bảo **Môi trường** được đặt là **Sandbox**
2. Sử dụng thông tin test từ SePay để thực hiện thanh toán
3. Kiểm tra xem đơn hàng có được cập nhật trạng thái đúng không

### Test với Production

1. Đảm bảo **Môi trường** được đặt là **Production**
2. Sử dụng thông tin thực từ SePay
3. Thực hiện một giao dịch test nhỏ để xác nhận mọi thứ hoạt động

## Xử lý sự cố

### Lỗi: "Class 'SePay\SePayClient' not found"

**Nguyên nhân**: SePay SDK chưa được cài đặt hoặc autoload chưa được load.

**Giải pháp**:
1. Chạy `composer install` hoặc `composer require sepay/sepay-pg`
2. Đảm bảo file `vendor/autoload.php` tồn tại
3. Kiểm tra đường dẫn trong file `class-wc-gateway-sepay.php` có đúng không

### Lỗi: "Lỗi xác thực API"

**Nguyên nhân**: Merchant ID hoặc Secret Key không đúng.

**Giải pháp**:
1. Kiểm tra lại Merchant ID và Secret Key trong cài đặt
2. Đảm bảo môi trường (Sandbox/Production) khớp với thông tin đăng nhập
3. Kiểm tra Secret Key có bị copy thừa khoảng trắng không

### Payment Gateway không hiển thị

**Nguyên nhân**: Gateway chưa được kích hoạt hoặc thiếu thông tin cấu hình.

**Giải pháp**:
1. Vào **WooCommerce** > **Settings** > **Payments**
2. Đảm bảo SePay đã được bật
3. Kiểm tra Merchant ID và Secret Key đã được nhập chưa

### Callback không hoạt động

**Nguyên nhân**: URL callback chưa được cấu hình đúng trong SePay.

**Giải pháp**:
1. Kiểm tra URL callback trong tài khoản SePay
2. Đảm bảo URL có thể truy cập được từ internet (không phải localhost)
3. Kiểm tra WordPress permalink settings

## Tính năng

- ✅ Hỗ trợ thanh toán bằng thẻ tín dụng
- ✅ Hỗ trợ chuyển khoản ngân hàng
- ✅ Hỗ trợ VietQR
- ✅ Tự động cập nhật trạng thái đơn hàng
- ✅ Hỗ trợ Sandbox và Production
- ✅ Xử lý callback (IPN) tự động
- ✅ Xử lý redirect sau thanh toán

## Tài liệu tham khảo

- [SePay PHP SDK Documentation](https://packagist.org/packages/sepay/sepay-pg)
- [SePay Developer Portal](https://developer.sepay.vn)
- [SePay Support](mailto:info@sepay.vn)

## Hỗ trợ

Nếu gặp vấn đề, vui lòng liên hệ:
- Email: info@sepay.vn
- GitHub Issues: [sepayvn/sepay-pg-php](https://github.com/sepayvn/sepay-pg-php/issues)
