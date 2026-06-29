# Thiết kế: Hoàn thiện website Nàng Thơ Cosmetics

**Ngày:** 2026-06-29  
**Mục tiêu:** Website nhận đơn hàng được, giao diện tiếng Việt hoàn toàn, hoạt động tương tự Hasaki.vn phiên bản đơn giản.

---

## Bối cảnh

Website WordPress/WooCommerce cho thương hiệu mỹ phẩm Nàng Thơ. Sản phẩm đã import (439 sản phẩm), giao diện cơ bản đã có, nhưng chưa nhận được đơn hàng vì không có phương thức thanh toán nào được bật. Ngoài ra còn nhiều lỗi giao diện và chức năng cần sửa.

## Phạm vi

### Sẽ làm
- Bật và cấu hình thanh toán (chuyển khoản ngân hàng + SePay VietQR)
- Sửa luồng đặt hàng end-to-end hoạt động ổn định
- Việt hoá toàn bộ chuỗi tiếng Anh còn sót
- Sửa các lỗi chức năng trên trang chủ và trang cửa hàng
- Nâng cấp UI trang chủ (countdown thật, danh mục đúng)

### Không làm
- Đặt lịch clinic/spa
- Hệ thống điểm thưởng/loyalty
- Live chat
- Trang quản lý đơn hàng phức tạp cho khách hàng

---

## Nhóm 1 — Thanh toán (Blocking — ưu tiên số 1)

### 1.1 Vietnam Bank Transfer (bật ngay)
- Vào WP Admin → WooCommerce → Cài đặt → Thanh toán
- Bật gateway "Chuyển khoản ngân hàng Việt Nam"
- Điền: tên ngân hàng, số tài khoản, tên chủ tài khoản, chi nhánh
- Kết quả: khách đặt hàng được ngay, admin xác nhận tay

### 1.2 SePay (sau khi có API key)
- Đăng ký tài khoản merchant tại sepay.vn
- Lấy API key, Merchant ID, Secret key
- Cấu hình trong WP Admin → WooCommerce → Thanh toán → SePay
- Điền: API key, Merchant ID, Secret key, số tài khoản ngân hàng liên kết
- Kết quả: tự động xác nhận đơn khi khách quét VietQR hoặc thanh toán thẻ

### 1.3 Kiểm tra luồng thanh toán
- Test đặt hàng với chuyển khoản ngân hàng
- Test đặt hàng với SePay (khi có API key)
- Xác nhận email xác nhận đơn gửi đúng

---

## Nhóm 2 — Sửa lỗi chức năng

### 2.1 Sửa URL trang cửa hàng
- **Vấn đề:** Menu hardcode link `/shop` nhưng WooCommerce shop page ở `/cua-hang/`
- **Sửa:** Thay tất cả hardcode `/shop` bằng `wc_get_page_permalink('shop')` trong `header.php`
- **File:** `wp-content/themes/nang-tho-cosmetics/header.php`

### 2.2 Sửa lọc danh mục trang chủ
- **Vấn đề:** `categories.php` chỉ filter `slug === 'uncategorized'` (tiếng Anh), bỏ sót `chua-phan-loai` (tiếng Việt)
- **Sửa:** Thêm điều kiện lọc `chua-phan-loai` và `Chưa phân loại`
- **File:** `wp-content/themes/nang-tho-cosmetics/template-parts/home/categories.php`

### 2.3 Sửa PHP Warning trên trang cửa hàng
- **Vấn đề:** `sidebar-filters.php` dòng 50 truy cập `$term->term_id` nhưng `$term` là `WP_Post_Type` object, không phải taxonomy term
- **Sửa:** Kiểm tra đúng object type trước khi access property
- **File:** `wp-content/themes/nang-tho-cosmetics/template-parts/shop/sidebar-filters.php`

### 2.4 Flash sale countdown động
- **Vấn đề:** Timer `02:15:45` hardcode trong PHP, không đếm ngược
- **Sửa:** Thêm JavaScript countdown; end time lấy từ WP custom option hoặc cố định mỗi ngày đến 23:59
- **File:** `template-parts/home/flash-sale.php` + `assets/js/` (file mới)

### 2.5 Hướng dẫn phân loại sản phẩm
- 439 sản phẩm đang ở "Chưa phân loại" — cần bulk-assign vào đúng danh mục
- Thực hiện qua WP Admin → Sản phẩm → chọn nhiều → Hành động hàng loạt → Chỉnh sửa → Danh mục
- Các danh mục mục tiêu: Chăm sóc da, Trang điểm, Cơ thể & Tóc, Nước hoa, Thực phẩm chức năng

---

## Nhóm 3 — Việt hoá toàn bộ

### Chuỗi cần đổi

| Vị trí | Tiếng Anh | Tiếng Việt |
|---|---|---|
| Trang 404/shop rỗng | "Nothing Found" | "Không tìm thấy sản phẩm" |
| Trang 404/shop rỗng | "It seems we can't find what you're looking for." | "Xin lỗi, chúng tôi không tìm thấy trang bạn cần." |
| Checkout | "Free shipping" | "Miễn phí vận chuyển" |
| Checkout | "Coupon:" | "Mã giảm giá:" |
| PHP warnings | Ẩn tất cả warning ngoài production | Thêm `WP_DEBUG = false` trong wp-config |
| Footer copyright | "© 2023" | "© 2025" |
| Breadcrumb | "Home" | "Trang chủ" |

### Cách thực hiện
- Các chuỗi WooCommerce: thêm filter `gettext` trong `functions.php` để override
- Các chuỗi trong template: sửa trực tiếp trong file PHP
- WP_DEBUG: đảm bảo `WP_DEBUG = false` và `WP_DEBUG_DISPLAY = false` trên production

---

## Nhóm 4 — Nâng cấp UI

### 4.1 Hero banner
- **Hiện tại:** Dùng ảnh từ Google CDN (lh3.googleusercontent.com) — không ổn định
- **Thay bằng:** Upload ảnh thật lên WP Media Library, dùng `get_theme_mod()` hoặc hardcode URL nội bộ
- Kích thước main banner: 1200×500px, side banners: 580×220px

### 4.2 Flash sale style
- Countdown style giống Hasaki: nền tối (`bg-black`), chữ trắng đậm, số nổi bật
- Hiện tại đã gần đúng — chỉ cần làm timer hoạt động

### 4.3 Footer
- Cập nhật năm từ 2023 → 2025
- Thêm link mạng xã hội (Facebook, Instagram) nếu có
- **File:** `wp-content/themes/nang-tho-cosmetics/footer.php`

---

## Kiến trúc không thay đổi

- Vẫn giữ theme custom `nang-tho-cosmetics`
- Vẫn dùng WooCommerce template overrides
- Vẫn dùng Tailwind CSS (đã compile trên prod)
- Không thêm plugin mới ngoài SePay (đã có trong `sepay-gateway/`)

---

## Thứ tự thực hiện

1. **Nhóm 1.1** — Bật chuyển khoản ngân hàng ngay (5 phút, không cần code)
2. **Nhóm 2.1** — Sửa URL shop (10 phút)
3. **Nhóm 2.3** — Sửa PHP warning (15 phút)
4. **Nhóm 3** — Việt hoá strings (30 phút)
5. **Nhóm 2.2** — Sửa danh mục homepage (20 phút)
6. **Nhóm 2.4** — Flash sale countdown (30 phút)
7. **Nhóm 2.5** — Assign sản phẩm vào danh mục (thủ công, WP admin)
8. **Nhóm 1.2** — SePay (sau khi đăng ký)
9. **Nhóm 4** — UI nâng cấp (1-2 tiếng)
10. Deploy lên production, test toàn bộ luồng

---

## Kiểm thử trước khi launch

- [ ] Đặt hàng test với chuyển khoản ngân hàng → nhận email xác nhận
- [ ] Đặt hàng test với SePay → xác nhận tự động
- [ ] Kiểm tra trang cửa hàng `/cua-hang/` hiển thị đúng
- [ ] Kiểm tra danh mục trên trang chủ có sản phẩm
- [ ] Không còn PHP warning hiển thị
- [ ] Toàn bộ text hiển thị bằng tiếng Việt
- [ ] Mobile responsive hoạt động tốt
