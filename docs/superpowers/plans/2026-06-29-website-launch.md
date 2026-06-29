# Kế hoạch thực hiện: Hoàn thiện Nàng Thơ Cosmetics

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Website nhận đơn hàng được, toàn bộ giao diện tiếng Việt, không còn lỗi PHP, luồng đặt hàng hoạt động end-to-end.

**Architecture:** Sửa trực tiếp trên theme custom `nang-tho-cosmetics` tại `wp-content/themes/nang-tho-cosmetics/`. Mỗi task là một nhóm thay đổi liên quan, deploy lên production qua SSH sau khi xong.

**Tech Stack:** WordPress 6.x, WooCommerce 9.x, PHP 8.x, Tailwind CSS (đã compile), JavaScript vanilla

## Global Constraints

- Mọi chuỗi hiển thị ra ngoài phải là tiếng Việt — không để lại bất kỳ tiếng Anh nào
- Production SSH: `ssh nangtho` (alias cho 134.122.17.201), deploy bằng `git pull` trên server
- WordPress theme: `wp-content/themes/nang-tho-cosmetics/`
- Không thêm plugin mới, không đổi cấu trúc theme
- WooCommerce shop page slug trên production là `/cua-hang/` — dùng `wc_get_page_permalink('shop')` thay vì hardcode

---

## Task 1: Bật phương thức thanh toán chuyển khoản ngân hàng

**Files:**
- Không có file — thao tác qua WP Admin

**Ghi chú:** Task này là bước thủ công trên WP Admin. Không cần code. Phải làm trước tất cả task còn lại vì đây là blocker.

- [ ] **Bước 1: Vào WP Admin → WooCommerce → Cài đặt → Thanh toán**

  Truy cập: `https://nangthocomestic.io.vn/wp-admin/admin.php?page=wc-settings&tab=checkout`

- [ ] **Bước 2: Bật "Chuyển khoản ngân hàng Việt Nam"**

  Tìm gateway "Chuyển khoản ngân hàng Việt Nam" → click "Quản lý" → bật toggle "Bật/Tắt" → điền thông tin:
  - Tiêu đề: `Chuyển khoản ngân hàng`
  - Mô tả: `Vui lòng chuyển khoản vào tài khoản bên dưới. Đơn hàng sẽ được xử lý sau khi nhận được thanh toán.`
  - Tên ngân hàng: *(điền tên ngân hàng thật)*
  - Số tài khoản: *(điền số tài khoản thật)*
  - Tên chủ tài khoản: *(điền tên chủ tài khoản)*
  - Click "Lưu thay đổi"

- [ ] **Bước 3: Kiểm tra trên trang thanh toán**

  Vào `https://nangthocomestic.io.vn/cua-hang/` → thêm 1 sản phẩm → vào `/thanh-toan/`
  
  Kết quả mong đợi: Phần "Tùy chọn thanh toán" hiện "Chuyển khoản ngân hàng" thay vì thông báo lỗi đỏ.

- [ ] **Bước 4: Test đặt hàng thật**

  Điền đầy đủ form, chọn "Chuyển khoản ngân hàng", click "Đặt hàng"
  
  Kết quả mong đợi: Chuyển đến trang xác nhận đơn hàng, email xác nhận gửi đến địa chỉ email đã nhập.

---

## Task 2: Sửa URL trang cửa hàng trong fallback navigation

**Files:**
- Modify: `wp-content/themes/nang-tho-cosmetics/header.php` — dòng 101-117 (desktop fallback) và 148-158 (mobile fallback)

**Vấn đề:** Khi WordPress admin chưa cấu hình menu `primary`, header fallback về các link hardcode `/shop` — URL sai (shop page ở `/cua-hang/`).

- [ ] **Bước 1: Tìm và sửa fallback desktop nav**

  Tại `header.php` dòng ~101, thay toàn bộ `home_url('/shop')` bằng `wc_get_page_permalink('shop')`:

  Trước:
  ```php
  <a href="<?php echo esc_url(home_url('/shop')); ?>"
      class="...">Chăm sóc da</a>
  <a href="<?php echo esc_url(home_url('/shop')); ?>?cat=makeup"
      class="...">Trang điểm</a>
  <a href="<?php echo esc_url(home_url('/shop')); ?>?cat=body"
      class="...">Cơ thể & Tóc</a>
  ```

  Sau:
  ```php
  <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"
      class="...">Chăm sóc da</a>
  <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>?cat=makeup"
      class="...">Trang điểm</a>
  <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>?cat=body"
      class="...">Cơ thể & Tóc</a>
  ```

- [ ] **Bước 2: Sửa fallback mobile nav** (dòng ~148-158, cùng pattern)

  Thay `home_url('/shop')` → `wc_get_page_permalink('shop')` tương tự bước 1 cho mobile nav.

- [ ] **Bước 3: Kiểm tra**

  Vào trang chủ → click "Chăm sóc da" trong menu.
  
  Kết quả mong đợi: Chuyển đến `https://nangthocomestic.io.vn/cua-hang/` và hiện trang cửa hàng đầy đủ sản phẩm (không phải "Nothing Found").

- [ ] **Bước 4: Commit**

  ```bash
  git add wp-content/themes/nang-tho-cosmetics/header.php
  git commit -m "fix: use wc_get_page_permalink for shop links in fallback nav"
  ```

---

## Task 3: Sửa PHP Warning trong sidebar trang cửa hàng

**Files:**
- Modify: `wp-content/themes/nang-tho-cosmetics/template-parts/shop/sidebar-filters.php` — dòng 50

**Vấn đề:** `get_queried_object()` trả về `WP_Post_Type` khi ở trang shop, không phải `WP_Term`. Truy cập `->term_id` trên `WP_Post_Type` sinh ra Warning.

- [ ] **Bước 1: Sửa dòng 50 trong sidebar-filters.php**

  Trước (dòng 50):
  ```php
  $is_active = $current_category && $current_category->term_id === $category->term_id;
  ```

  Sau:
  ```php
  $is_active = ($current_category instanceof WP_Term) && $current_category->term_id === $category->term_id;
  ```

- [ ] **Bước 2: Tương tự sửa dòng 75 (subcategory check)**

  Trước (dòng 75):
  ```php
  $is_sub_active = $current_category && $current_category->term_id === $subcat->term_id;
  ```

  Sau:
  ```php
  $is_sub_active = ($current_category instanceof WP_Term) && $current_category->term_id === $subcat->term_id;
  ```

- [ ] **Bước 3: Kiểm tra**

  Vào `https://nangthocomestic.io.vn/cua-hang/` — sidebar hiện không còn thông báo Warning PHP màu vàng nữa.

- [ ] **Bước 4: Commit**

  ```bash
  git add wp-content/themes/nang-tho-cosmetics/template-parts/shop/sidebar-filters.php
  git commit -m "fix: check instanceof WP_Term before accessing term_id in sidebar"
  ```

---

## Task 4: Sửa lọc danh mục trang chủ

**Files:**
- Modify: `wp-content/themes/nang-tho-cosmetics/template-parts/home/categories.php` — dòng 32-33

**Vấn đề:** Code chỉ lọc `Uncategorized` (slug tiếng Anh). Trên production danh mục mặc định có slug `chua-phan-loai` và tên `Chưa phân loại` — bị bỏ sót.

- [ ] **Bước 1: Sửa điều kiện lọc trong categories.php**

  Trước (dòng 32-33):
  ```php
  if ($cat->name === 'Uncategorized' || $cat->slug === 'uncategorized')
      continue;
  ```

  Sau:
  ```php
  if (in_array($cat->slug, ['uncategorized', 'chua-phan-loai']) ||
      in_array($cat->name, ['Uncategorized', 'Chưa phân loại']))
      continue;
  ```

- [ ] **Bước 2: Kiểm tra**

  Vào trang chủ — phần "Danh Mục Nổi Bật" không còn hiện ô "Chưa phân loại".
  
  **Lưu ý:** Nếu phần này vẫn trống (không hiện danh mục nào), nguyên nhân là tất cả sản phẩm chưa được gán vào danh mục đúng. Xem Task 5 để xử lý.

- [ ] **Bước 3: Commit**

  ```bash
  git add wp-content/themes/nang-tho-cosmetics/template-parts/home/categories.php
  git commit -m "fix: filter Vietnamese uncategorized slug from homepage categories"
  ```

---

## Task 5: Phân loại sản phẩm (thao tác thủ công WP Admin)

**Files:** Không có file — thao tác qua WP Admin

**Vấn đề:** 439 sản phẩm đang ở "Chưa phân loại". Menu điều hướng có 5 danh mục: Chăm sóc da, Trang điểm, Cơ thể & Tóc, Thương hiệu, Khuyến mãi — nhưng hiện không có sản phẩm nào trong đó.

- [ ] **Bước 1: Tạo các danh mục sản phẩm trong WP Admin**

  Vào `WP Admin → Sản phẩm → Danh mục` và tạo các danh mục sau nếu chưa có:
  - `Chăm sóc da` (slug: `cham-soc-da`)
  - `Trang điểm` (slug: `trang-diem`)
  - `Cơ thể & Tóc` (slug: `co-the-toc`)
  - `Nước hoa` (slug: `nuoc-hoa`)
  - `Thực phẩm chức năng` (slug: `thuc-pham-chuc-nang`)

- [ ] **Bước 2: Bulk-assign sản phẩm vào danh mục**

  Vào `WP Admin → Sản phẩm → Tất cả sản phẩm`:
  1. Lọc theo "Chưa phân loại"
  2. Chọn các sản phẩm thuộc cùng nhóm (ví dụ: serum, kem dưỡng → Chăm sóc da)
  3. Hành động hàng loạt → Chỉnh sửa → Danh mục → chọn đúng danh mục → Cập nhật
  4. Lặp lại cho từng nhóm sản phẩm

- [ ] **Bước 3: Kiểm tra**

  Vào trang chủ → phần "Danh Mục Nổi Bật" hiện các danh mục có sản phẩm với icon đúng.

---

## Task 6: Việt hoá toàn bộ chuỗi tiếng Anh

**Files:**
- Modify: `wp-content/themes/nang-tho-cosmetics/index.php` — dòng 35-36
- Modify: `wp-content/themes/nang-tho-cosmetics/footer.php` — dòng 63
- Modify: `wp-content/themes/nang-tho-cosmetics/functions.php` — thêm filter ở cuối file

- [ ] **Bước 1: Sửa "Nothing Found" trong index.php**

  Trước (dòng 35-36):
  ```php
  <h2 class="text-2xl font-bold"><?php esc_html_e( 'Nothing Found', 'nang-tho-cosmetics' ); ?></h2>
  <p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for.', 'nang-tho-cosmetics' ); ?></p>
  ```

  Sau:
  ```php
  <h2 class="text-2xl font-bold">Không tìm thấy nội dung</h2>
  <p>Xin lỗi, chúng tôi không tìm thấy trang bạn cần. Hãy thử tìm kiếm hoặc quay lại trang chủ.</p>
  ```

- [ ] **Bước 2: Sửa copyright footer**

  Tại `footer.php` dòng 63:

  Trước:
  ```php
  <p class="text-sm text-gray-400">© 2023 Nàng Thơ Cosmetics. All rights reserved.</p>
  ```

  Sau:
  ```php
  <p class="text-sm text-gray-400">© 2025 Nàng Thơ Cosmetics. Bảo lưu mọi quyền.</p>
  ```

- [ ] **Bước 3: Thêm gettext filter cho WooCommerce strings trong functions.php**

  Thêm vào cuối `functions.php` (trước dấu `?>` nếu có, hoặc ở cuối file):

  ```php
  /**
   * Việt hoá các chuỗi WooCommerce còn sót tiếng Anh
   */
  add_filter('gettext', 'nang_tho_viet_hoa_woocommerce', 20, 3);
  function nang_tho_viet_hoa_woocommerce($translated, $text, $domain) {
      $strings = [
          'Free shipping'          => 'Miễn phí vận chuyển',
          'Coupon:'                => 'Mã giảm giá:',
          'Apply coupon'           => 'Áp dụng mã',
          'Remove'                 => 'Xóa',
          'Cart'                   => 'Giỏ hàng',
          'Checkout'               => 'Thanh toán',
          'Order total:'           => 'Tổng đơn hàng:',
          'Subtotal:'              => 'Tạm tính:',
          'Shipping:'              => 'Vận chuyển:',
          'Payment method:'        => 'Phương thức thanh toán:',
          'Place order'            => 'Đặt hàng',
          'Your order'             => 'Đơn hàng của bạn',
          'Product'                => 'Sản phẩm',
          'Total'                  => 'Tổng',
          'Continue shopping'      => 'Tiếp tục mua hàng',
          'Return to shop'         => 'Quay lại cửa hàng',
          'No products in the cart.' => 'Chưa có sản phẩm trong giỏ hàng.',
          'Update cart'            => 'Cập nhật giỏ hàng',
          'Proceed to checkout'    => 'Tiến hành thanh toán',
      ];
      if (isset($strings[$text])) {
          return $strings[$text];
      }
      return $translated;
  }

  /**
   * Việt hoá breadcrumb WooCommerce
   */
  add_filter('woocommerce_breadcrumb_defaults', 'nang_tho_breadcrumb_viet');
  function nang_tho_breadcrumb_viet($args) {
      $args['home'] = 'Trang chủ';
      return $args;
  }
  ```

- [ ] **Bước 4: Kiểm tra**

  - Vào `/thanh-toan/` → "Free shipping" phải hiện là "Miễn phí vận chuyển"
  - Vào `/gio-hang/` → các nút "Update cart", "Proceed to checkout" phải là tiếng Việt
  - Vào trang cửa hàng → breadcrumb "Home" phải là "Trang chủ"
  - Footer hiện "© 2025 Nàng Thơ Cosmetics. Bảo lưu mọi quyền."

- [ ] **Bước 5: Commit**

  ```bash
  git add wp-content/themes/nang-tho-cosmetics/index.php \
          wp-content/themes/nang-tho-cosmetics/footer.php \
          wp-content/themes/nang-tho-cosmetics/functions.php
  git commit -m "feat: viet hoa toan bo chuoi tieng Anh con sot"
  ```

---

## Task 7: Flash sale countdown động

**Files:**
- Create: `wp-content/themes/nang-tho-cosmetics/assets/js/flash-sale-countdown.js`
- Modify: `wp-content/themes/nang-tho-cosmetics/template-parts/home/flash-sale.php` — phần HTML timer
- Modify: `wp-content/themes/nang-tho-cosmetics/functions.php` — enqueue script mới

**Logic:** Countdown đến 23:59:59 hôm nay. Khi hết, reset sang ngày hôm sau.

- [ ] **Bước 1: Tạo file JS countdown**

  Tạo `wp-content/themes/nang-tho-cosmetics/assets/js/flash-sale-countdown.js`:

  ```javascript
  (function () {
    function getEndOfDay() {
      var now = new Date();
      var end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
      return end;
    }

    function pad(n) {
      return n < 10 ? '0' + n : String(n);
    }

    function tick() {
      var now = new Date();
      var end = getEndOfDay();
      var diff = Math.max(0, Math.floor((end - now) / 1000));

      var hours = Math.floor(diff / 3600);
      var minutes = Math.floor((diff % 3600) / 60);
      var seconds = diff % 60;

      var hEl = document.getElementById('flash-sale-hours');
      var mEl = document.getElementById('flash-sale-minutes');
      var sEl = document.getElementById('flash-sale-seconds');

      if (hEl) hEl.textContent = pad(hours);
      if (mEl) mEl.textContent = pad(minutes);
      if (sEl) sEl.textContent = pad(seconds);
    }

    document.addEventListener('DOMContentLoaded', function () {
      tick();
      setInterval(tick, 1000);
    });
  })();
  ```

- [ ] **Bước 2: Cập nhật HTML timer trong flash-sale.php**

  Tìm đoạn timer hardcode (dòng ~54-61):

  Trước:
  ```php
  <div class="flex gap-1 text-white text-sm font-bold">
      <div class="bg-black dark:bg-primary rounded px-2 py-1">02</div>
      <div class="text-black dark:text-white py-1">:</div>
      <div class="bg-black dark:bg-primary rounded px-2 py-1">15</div>
      <div class="text-black dark:text-white py-1">:</div>
      <div class="bg-black dark:bg-primary rounded px-2 py-1">45</div>
  </div>
  ```

  Sau:
  ```php
  <div class="flex gap-1 text-white text-sm font-bold">
      <div class="bg-black dark:bg-primary rounded px-2 py-1" id="flash-sale-hours">00</div>
      <div class="text-black dark:text-white py-1">:</div>
      <div class="bg-black dark:bg-primary rounded px-2 py-1" id="flash-sale-minutes">00</div>
      <div class="text-black dark:text-white py-1">:</div>
      <div class="bg-black dark:bg-primary rounded px-2 py-1" id="flash-sale-seconds">00</div>
  </div>
  ```

- [ ] **Bước 3: Enqueue script trong functions.php**

  Tìm function `nang_tho_cosmetics_scripts` (hoặc hàm enqueue scripts), thêm vào:

  ```php
  wp_enqueue_script(
      'nang-tho-flash-sale-countdown',
      get_template_directory_uri() . '/assets/js/flash-sale-countdown.js',
      array(),
      _S_VERSION,
      true
  );
  ```

  Nếu chưa có hàm enqueue, tạo mới trong functions.php:

  ```php
  add_action('wp_enqueue_scripts', 'nang_tho_cosmetics_scripts');
  function nang_tho_cosmetics_scripts() {
      wp_enqueue_script(
          'nang-tho-flash-sale-countdown',
          get_template_directory_uri() . '/assets/js/flash-sale-countdown.js',
          array(),
          _S_VERSION,
          true
      );
  }
  ```

- [ ] **Bước 4: Kiểm tra**

  Vào trang chủ — phần "Deal Sốc Hôm Nay":
  - Timer hiển thị giờ/phút/giây đếm ngược thật
  - Sau 1 phút, số giây giảm đi 60

- [ ] **Bước 5: Commit**

  ```bash
  git add wp-content/themes/nang-tho-cosmetics/assets/js/flash-sale-countdown.js \
          wp-content/themes/nang-tho-cosmetics/template-parts/home/flash-sale.php \
          wp-content/themes/nang-tho-cosmetics/functions.php
  git commit -m "feat: them flash sale countdown dong thay timer cung"
  ```

---

## Task 8: Deploy lên production và kiểm tra toàn bộ luồng

**Files:** Không có file mới — deploy code đã có

- [ ] **Bước 1: Push code lên GitHub/remote**

  ```bash
  git push origin main
  ```

- [ ] **Bước 2: Pull trên production server**

  ```bash
  ssh nangtho
  cd /var/www/html   # hoặc path WordPress trên server
  git pull origin main
  ```

  Nếu không dùng git trên server, dùng rsync hoặc script deploy đã có:
  ```bash
  # Từ máy local:
  bash deploy.sh
  ```

- [ ] **Bước 3: Kiểm tra luồng đặt hàng end-to-end**

  Thực hiện theo thứ tự:
  1. Vào `https://nangthocomestic.io.vn/cua-hang/` → trang cửa hàng hiện đúng, không lỗi
  2. Click vào 1 sản phẩm → trang sản phẩm đẹp, có ảnh, giá, biến thể
  3. Chọn biến thể → click "Thêm vào giỏ hàng" → icon giỏ hàng tăng số
  4. Vào `/thanh-toan/` → điền thông tin → chọn "Chuyển khoản ngân hàng" → click "Đặt hàng"
  5. Trang xác nhận hiện ra, email xác nhận đến hộp thư

- [ ] **Bước 4: Kiểm tra checklist UI**

  - [ ] Không còn PHP Warning trên trang cửa hàng
  - [ ] Footer hiện "© 2025 Nàng Thơ Cosmetics. Bảo lưu mọi quyền."
  - [ ] Trang chủ: Flash sale countdown đang đếm ngược
  - [ ] Trang chủ: Danh mục không còn "Chưa phân loại" (nếu đã assign sản phẩm)
  - [ ] Checkout: "Free shipping" → "Miễn phí vận chuyển"
  - [ ] Menu → "Chăm sóc da" → dẫn đến trang cửa hàng đúng

---

## Task 9 (Tùy chọn): Cấu hình SePay sau khi có API key

**Files:**
- Modify: WP Admin UI — không cần code

**Ghi chú:** Chỉ thực hiện sau khi đã đăng ký và được duyệt tài khoản SePay merchant tại sepay.vn.

- [ ] **Bước 1: Đăng ký tài khoản SePay**

  Truy cập `https://sepay.vn` → Đăng ký tài khoản merchant → Xác minh danh tính → Liên kết tài khoản ngân hàng.

- [ ] **Bước 2: Lấy thông tin API**

  Từ dashboard SePay, lấy:
  - API key
  - Merchant ID  
  - Secret key (webhook signature)
  - Số tài khoản ngân hàng đã liên kết

- [ ] **Bước 3: Cấu hình trong WP Admin**

  Vào `WP Admin → WooCommerce → Cài đặt → Thanh toán → SePay → Quản lý`:
  - Bật gateway
  - Điền API key, Merchant ID, Secret key
  - Webhook URL (copy từ trang cấu hình, paste vào SePay dashboard)
  - Lưu thay đổi

- [ ] **Bước 4: Test với giao dịch thử**

  Đặt đơn hàng test → chọn SePay → quét QR bằng app ngân hàng → đơn hàng tự động chuyển trạng thái "Đã thanh toán".

---

---

## Ghi chú: Hero banner ảnh thật (tùy chọn)

Spec 4.1 yêu cầu thay ảnh hero từ Google CDN sang ảnh thật của shop. Các ảnh hiện tại đang load được nên không blocking. Khi bạn có ảnh thật:
1. Upload lên `WP Admin → Media → Thêm mới`
2. Lấy URL ảnh đã upload
3. Sửa `template-parts/home/hero.php` — thay URL trong `style="background-image: url('...')"`
   - Main banner (dòng 12): 1200×500px
   - Side banner trên (dòng 33): 580×220px  
   - Side banner dưới (dòng 43): 580×220px

---

## Thứ tự ưu tiên thực hiện

1. **Task 1** — Bật chuyển khoản ngân hàng (không cần code, 5 phút, unblocks tất cả)
2. **Task 2** — Sửa URL shop
3. **Task 3** — Sửa PHP Warning
4. **Task 4** — Sửa danh mục
5. **Task 6** — Việt hoá strings
6. **Task 7** — Flash sale countdown
7. **Task 5** — Phân loại sản phẩm (thủ công)
8. **Task 8** — Deploy và test
9. **Task 9** — SePay (sau khi có API key)
