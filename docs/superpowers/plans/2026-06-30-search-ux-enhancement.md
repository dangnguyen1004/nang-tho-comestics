# Search UX Enhancement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Thêm live search autocomplete trên header và cải thiện trang kết quả tìm kiếm (tiêu đề rõ ràng + empty state thông minh).

**Architecture:** WordPress AJAX handler trả JSON gồm popular terms + categories + products. JS vanilla với debounce 300ms render dropdown dưới search box. Search results page dùng conditional `is_search()` để hiển thị header và empty state riêng.

**Tech Stack:** PHP 7.4+, WordPress AJAX (`wp_ajax_nopriv_`), WooCommerce `WC_Product_Query`, Vanilla JS (no framework), Tailwind CSS (via existing CDN), Material Symbols icons.

## Global Constraints

- Theme path: `wp-content/themes/nang-tho-cosmetics/`
- PHP minimum: 7.4. WordPress 5.0+, WooCommerce 5.0+.
- Không dùng jQuery cho JS mới — chỉ dùng Fetch API và Vanilla JS.
- Màu primary: `#ee2b8c` (class Tailwind: `text-primary`, `bg-primary`).
- Icon dùng Material Symbols Outlined (đã được load sẵn).
- Mọi string tiếng Việt viết inline, không qua `__()`.
- Deploy: `scp <file> nangtho:/opt/nang-tho-cosmetics/wp-content/themes/nang-tho-cosmetics/<path>`

---

## File Map

| File | Action | Mục đích |
|------|--------|----------|
| `functions.php` | Modify | Thêm AJAX handler + enqueue JS + localize |
| `header.php` | Modify | Thêm `id` vào search input để JS gắn vào |
| `assets/js/search-autocomplete.js` | Create | Toàn bộ logic dropdown autocomplete |
| `template-parts/shop/product-grid-header.php` | Modify | Hiện "Kết quả tìm kiếm cho X" khi `is_search()` |
| `template-parts/search/search-empty-state.php` | Create | UI khi tìm kiếm không có kết quả |
| `woocommerce/archive-product.php` | Modify | Route sang empty state khi search + 0 kết quả |

---

## Task 1: PHP AJAX Search Handler

**Files:**
- Modify: `functions.php` (append sau block "Handle WooCommerce Search")

**Interfaces:**
- Produces: `POST /wp-admin/admin-ajax.php?action=nang_tho_live_search` với body `{query, nonce}`
- Response: `{"success":true,"data":{"popular_terms":[...],"categories":[...],"products":[...]}}`

- [ ] **Step 1: Thêm AJAX handler vào functions.php**

Tìm comment `/** Handle WooCommerce Search on Shop Page */` (khoảng dòng 770) và thêm block sau NGAY SAU hàm `nang_tho_shop_search`:

```php
/**
 * Live Search AJAX Handler
 */
add_action('wp_ajax_nang_tho_live_search', 'nang_tho_live_search_handler');
add_action('wp_ajax_nopriv_nang_tho_live_search', 'nang_tho_live_search_handler');

function nang_tho_live_search_handler()
{
    check_ajax_referer('nang_tho_search_nonce', 'nonce');

    $query = isset($_POST['query']) ? sanitize_text_field(wp_unslash($_POST['query'])) : '';

    if (strlen($query) < 2) {
        wp_send_json_error('Query too short');
        return;
    }

    // Popular terms — hardcoded, filtered by query substring
    $all_popular = [
        'son môi', 'son môi lì', 'son dưỡng', 'son kem',
        'kem dưỡng da', 'kem chống nắng', 'serum vitamin c',
        'tẩy trang', 'mặt nạ', 'phấn nền', 'mascara',
        'phấn má hồng', 'toner', 'nước tẩy trang',
    ];
    $popular_terms = array_values(array_slice(
        array_filter($all_popular, fn($t) => mb_stripos($t, $query) !== false),
        0, 2
    ));

    // Categories
    $raw_cats = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => true,
        'search'     => $query,
        'number'     => 1,
        'exclude'    => [(int) get_option('default_product_cat')],
    ]);
    $categories = [];
    if (!is_wp_error($raw_cats) && !empty($raw_cats)) {
        foreach ($raw_cats as $cat) {
            $categories[] = [
                'name'  => $cat->name,
                'url'   => get_term_link($cat),
                'count' => (int) $cat->count,
            ];
        }
    }

    // Products
    $product_query = new WC_Product_Query([
        's'       => $query,
        'limit'   => 3,
        'status'  => 'publish',
        'orderby' => 'relevance',
    ]);
    $products = [];
    foreach ($product_query->get_products() as $product) {
        $image_id  = $product->get_image_id();
        $image_url = $image_id
            ? wp_get_attachment_image_url($image_id, 'thumbnail')
            : wc_placeholder_img_src('thumbnail');
        $products[] = [
            'id'    => $product->get_id(),
            'name'  => $product->get_name(),
            'price' => wp_strip_all_tags($product->get_price_html()),
            'image' => esc_url($image_url),
            'url'   => esc_url($product->get_permalink()),
        ];
    }

    wp_send_json_success([
        'popular_terms' => $popular_terms,
        'categories'    => $categories,
        'products'      => $products,
    ]);
}
```

- [ ] **Step 2: Test handler thủ công**

Mở DevTools → Network tab, vào trang shop, chạy trong Console:

```javascript
const fd = new FormData();
fd.append('action', 'nang_tho_live_search');
fd.append('nonce', '');   // nonce chưa có, sẽ fail với 403 — expected
fd.append('query', 'son');
fetch('/wp-admin/admin-ajax.php', {method:'POST', body:fd})
  .then(r=>r.json()).then(console.log);
```

Expected: `{success: false, data: ""}` (403 vì chưa có nonce). Điều này xác nhận handler đã được đăng ký.

- [ ] **Step 3: Deploy và commit**

```bash
scp wp-content/themes/nang-tho-cosmetics/functions.php \
    nangtho:/opt/nang-tho-cosmetics/wp-content/themes/nang-tho-cosmetics/functions.php

git add wp-content/themes/nang-tho-cosmetics/functions.php
git commit -m "feat: add live search AJAX handler (nang_tho_live_search)"
```

---

## Task 2: Enqueue JS + Thêm ID vào Search Input

**Files:**
- Modify: `functions.php` (enqueue + wp_localize_script)
- Modify: `header.php` line ~40 (thêm `id` vào `<input type="search">`)

**Interfaces:**
- Produces: `window.nangThoSearch = { ajaxUrl, nonce, shopUrl }` — consumed bởi Task 3
- Produces: `<input id="search-input-desktop">` — consumed bởi Task 3

- [ ] **Step 1: Thêm enqueue vào functions.php**

Tìm block enqueue shop filters (khoảng dòng 467):
```php
if (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()) {
    wp_enqueue_script('nang-tho-shop-filters', ...);
```

Thêm block enqueue mới NGAY TRƯỚC `if (is_shop() || ...` đó (để chạy trên mọi trang):

```php
        // Live Search Autocomplete
        wp_enqueue_script(
            'nang-tho-search-autocomplete',
            get_template_directory_uri() . '/assets/js/search-autocomplete.js',
            [],
            _S_VERSION,
            true
        );
        wp_localize_script('nang-tho-search-autocomplete', 'nangThoSearch', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('nang_tho_search_nonce'),
            'shopUrl' => wc_get_page_permalink('shop'),
        ]);
```

- [ ] **Step 2: Thêm id vào search input trong header.php**

Tìm dòng (khoảng 40):
```php
<input type="search" name="s"
    class="block w-full pl-10 pr-3 ...
```

Thêm `id="search-input-desktop"` vào thẻ input:
```php
<input type="search" name="s"
    id="search-input-desktop"
    class="block w-full pl-10 pr-3 py-2.5 border border-transparent rounded-lg leading-5 bg-background-light dark:bg-white/5 text-text-dark dark:text-white placeholder-gray-400 focus:outline-none focus:bg-white dark:focus:bg-white/10 focus:ring-2 focus:ring-primary focus:border-primary sm:text-sm transition duration-150 ease-in-out"
    placeholder="<?php echo esc_attr_x('Tìm kiếm sản phẩm, thương hiệu...', 'placeholder', 'nang-tho-cosmetics'); ?>"
    value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>"
    autocomplete="off" />
```

(Thêm `autocomplete="off"` để tránh browser suggestion đè lên dropdown tùy chỉnh.)

- [ ] **Step 3: Tạo file JS rỗng để không bị 404**

```bash
touch wp-content/themes/nang-tho-cosmetics/assets/js/search-autocomplete.js
```

- [ ] **Step 4: Deploy và verify**

```bash
scp wp-content/themes/nang-tho-cosmetics/functions.php \
    nangtho:/opt/nang-tho-cosmetics/wp-content/themes/nang-tho-cosmetics/functions.php
scp wp-content/themes/nang-tho-cosmetics/header.php \
    nangtho:/opt/nang-tho-cosmetics/wp-content/themes/nang-tho-cosmetics/header.php
scp wp-content/themes/nang-tho-cosmetics/assets/js/search-autocomplete.js \
    nangtho:/opt/nang-tho-cosmetics/wp-content/themes/nang-tho-cosmetics/assets/js/search-autocomplete.js
```

Mở trang shop, DevTools → Console:
```javascript
console.log(typeof nangThoSearch, nangThoSearch);
// Expected: "object" { ajaxUrl: "...admin-ajax.php", nonce: "...", shopUrl: "...cua-hang..." }
document.getElementById('search-input-desktop');
// Expected: <input id="search-input-desktop" ...>
```

- [ ] **Step 5: Commit**

```bash
git add wp-content/themes/nang-tho-cosmetics/functions.php \
        wp-content/themes/nang-tho-cosmetics/header.php \
        wp-content/themes/nang-tho-cosmetics/assets/js/search-autocomplete.js
git commit -m "feat: enqueue search autocomplete JS + add search input id"
```

---

## Task 3: Live Search Autocomplete JS

**Files:**
- Create: `assets/js/search-autocomplete.js`

**Interfaces:**
- Consumes: `window.nangThoSearch.ajaxUrl`, `window.nangThoSearch.nonce`, `window.nangThoSearch.shopUrl` (từ Task 2)
- Consumes: `#search-input-desktop` (từ Task 2)
- Produces: Dropdown `#search-autocomplete-dropdown` được inject vào `.relative` wrapper của search input

- [ ] **Step 1: Viết toàn bộ search-autocomplete.js**

```javascript
/* global nangThoSearch */
(function () {
    var input = document.getElementById('search-input-desktop');
    if (!input || typeof nangThoSearch === 'undefined') return;

    var debounceTimer = null;
    var dropdown = null;
    var activeIndex = -1;
    var wrapper = input.closest('.relative');

    // ── Dropdown lifecycle ────────────────────────────────────────────
    function ensureDropdown() {
        if (!dropdown || !wrapper.contains(dropdown)) {
            dropdown = document.createElement('div');
            dropdown.id = 'search-autocomplete-dropdown';
            dropdown.className = [
                'absolute top-full left-0 right-0 mt-1 z-50',
                'bg-white dark:bg-[#2c1621]',
                'rounded-xl border border-gray-100 dark:border-gray-700',
                'shadow-xl overflow-hidden',
            ].join(' ');
            wrapper.appendChild(dropdown);
        }
        return dropdown;
    }

    function hideDropdown() {
        if (dropdown && wrapper.contains(dropdown)) {
            wrapper.removeChild(dropdown);
        }
        dropdown = null;
        activeIndex = -1;
    }

    // ── HTML helpers ─────────────────────────────────────────────────
    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function sectionTitle(label) {
        return '<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">' + label + '</div>';
    }

    // ── Render ────────────────────────────────────────────────────────
    function render(data) {
        var terms = data.popular_terms || [];
        var cats  = data.categories   || [];
        var prods = data.products     || [];

        if (!terms.length && !cats.length && !prods.length) {
            hideDropdown();
            return;
        }

        var html = '';

        if (terms.length) {
            html += '<div class="p-3 border-b border-gray-100 dark:border-gray-800">';
            html += sectionTitle('Từ khoá phổ biến');
            html += '<div class="flex flex-wrap gap-2">';
            terms.forEach(function (term) {
                var url = nangThoSearch.shopUrl + '?s=' + encodeURIComponent(term);
                html += '<a href="' + esc(url) + '" class="search-ac-item flex items-center gap-1 px-3 py-1 rounded-full bg-pink-50 dark:bg-pink-900/20 text-primary text-sm hover:bg-pink-100 dark:hover:bg-pink-900/40 transition-colors">'
                    + '<span class="material-symbols-outlined text-[14px]">local_fire_department</span>'
                    + esc(term)
                    + '</a>';
            });
            html += '</div></div>';
        }

        if (cats.length) {
            html += '<div class="p-3 border-b border-gray-100 dark:border-gray-800">';
            html += sectionTitle('Danh mục');
            cats.forEach(function (cat) {
                html += '<a href="' + esc(cat.url) + '" class="search-ac-item flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">'
                    + '<span class="material-symbols-outlined text-[16px] text-gray-400">folder</span>'
                    + '<span>' + esc(cat.name) + '</span>'
                    + '<span class="ml-auto text-xs text-gray-400">(' + esc(cat.count) + ')</span>'
                    + '</a>';
            });
            html += '</div>';
        }

        if (prods.length) {
            html += '<div class="p-3">';
            html += sectionTitle('Sản phẩm gợi ý');
            html += '<div class="space-y-0.5">';
            prods.forEach(function (prod) {
                html += '<a href="' + esc(prod.url) + '" class="search-ac-item flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">'
                    + '<img src="' + esc(prod.image) + '" alt="" class="w-10 h-10 object-cover rounded-lg flex-shrink-0 bg-gray-100">'
                    + '<div class="flex-1 min-w-0">'
                    + '<div class="text-sm font-medium text-gray-800 dark:text-white truncate">' + esc(prod.name) + '</div>'
                    + '<div class="text-xs text-primary font-bold">' + esc(prod.price) + '</div>'
                    + '</div>'
                    + '</a>';
            });
            html += '</div></div>';
        }

        ensureDropdown().innerHTML = html;
    }

    // ── AJAX ──────────────────────────────────────────────────────────
    function fetchSuggestions(query) {
        var fd = new FormData();
        fd.append('action', 'nang_tho_live_search');
        fd.append('nonce',  nangThoSearch.nonce);
        fd.append('query',  query);

        fetch(nangThoSearch.ajaxUrl, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) render(data.data);
                else hideDropdown();
            })
            .catch(hideDropdown);
    }

    // ── Keyboard navigation ───────────────────────────────────────────
    function getItems() {
        return dropdown ? Array.from(dropdown.querySelectorAll('.search-ac-item')) : [];
    }

    function setActive(index) {
        var items = getItems();
        items.forEach(function (el, i) {
            el.classList.toggle('bg-gray-50', i === index);
            el.classList.toggle('dark:bg-white/5', i === index);
        });
        activeIndex = index;
        if (index >= 0 && items[index]) items[index].focus();
    }

    // ── Events ────────────────────────────────────────────────────────
    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var q = this.value.trim();
        if (q.length < 2) { hideDropdown(); return; }
        debounceTimer = setTimeout(function () { fetchSuggestions(q); }, 300);
    });

    input.addEventListener('keydown', function (e) {
        var items = getItems();
        if (e.key === 'Escape') { hideDropdown(); input.blur(); return; }
        if (!items.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActive(Math.min(activeIndex + 1, items.length - 1));
        }
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeIndex <= 0) { activeIndex = -1; input.focus(); return; }
            setActive(activeIndex - 1);
        }
    });

    document.addEventListener('click', function (e) {
        if (!wrapper.contains(e.target)) hideDropdown();
    });

    input.addEventListener('focus', function () {
        var q = this.value.trim();
        if (q.length >= 2) fetchSuggestions(q);
    });
})();
```

- [ ] **Step 2: Deploy và test thủ công**

```bash
scp wp-content/themes/nang-tho-cosmetics/assets/js/search-autocomplete.js \
    nangtho:/opt/nang-tho-cosmetics/wp-content/themes/nang-tho-cosmetics/assets/js/search-autocomplete.js
```

Mở `https://nangthocomestic.io.vn`, gõ "son" vào ô tìm kiếm.

Expected:
- Sau 300ms: dropdown xuất hiện với từ khoá phổ biến / danh mục / sản phẩm
- Nhấn ↓: focus chuyển vào item đầu tiên
- Nhấn Escape: dropdown đóng
- Click ngoài dropdown: dropdown đóng
- Click sản phẩm: điều hướng đến trang sản phẩm đó
- Gõ < 2 ký tự: dropdown không hiện

- [ ] **Step 3: Commit**

```bash
git add wp-content/themes/nang-tho-cosmetics/assets/js/search-autocomplete.js
git commit -m "feat: live search autocomplete dropdown with debounce + keyboard nav"
```

---

## Task 4: Search Results Page Header

**Files:**
- Modify: `template-parts/shop/product-grid-header.php`

**Interfaces:**
- Consumes: `is_search()`, `get_search_query()`, `$wp_query->found_posts`
- Produces: Tiêu đề "Kết quả tìm kiếm cho X" khi `is_search() === true`

- [ ] **Step 1: Sửa product-grid-header.php**

Tìm block hiển thị tiêu đề (khoảng dòng 39-52):

```php
        <div>
            <h2 class="text-2xl font-bold text-[#1b0d14] dark:text-white">
                <?php
                if ($current_category && is_a($current_category, 'WP_Term')) {
                    echo esc_html($current_category->name);
                } else {
                    echo 'Sản phẩm';
                }
                ?>
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Tìm thấy <span class="text-primary font-bold"><?php echo esc_html($product_count); ?></span> sản phẩm
            </p>
        </div>
```

Thay bằng:

```php
        <div>
            <h2 class="text-2xl font-bold text-[#1b0d14] dark:text-white">
                <?php
                if (is_search()) {
                    $search_query = esc_html(get_search_query());
                    echo 'Kết quả tìm kiếm cho <span class="text-primary">"' . $search_query . '"</span>';
                } elseif ($current_category && is_a($current_category, 'WP_Term')) {
                    echo esc_html($current_category->name);
                } else {
                    echo 'Sản phẩm';
                }
                ?>
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Tìm thấy <span class="text-primary font-bold"><?php echo esc_html($product_count); ?></span> sản phẩm
            </p>
        </div>
```

- [ ] **Step 2: Deploy và kiểm tra**

```bash
scp wp-content/themes/nang-tho-cosmetics/template-parts/shop/product-grid-header.php \
    nangtho:/opt/nang-tho-cosmetics/wp-content/themes/nang-tho-cosmetics/template-parts/shop/product-grid-header.php
```

Vào `https://nangthocomestic.io.vn/cua-hang/?s=son+moi`.

Expected: Tiêu đề hiện `Kết quả tìm kiếm cho "son moi"` với "son moi" màu primary (hồng).

- [ ] **Step 3: Commit**

```bash
git add wp-content/themes/nang-tho-cosmetics/template-parts/shop/product-grid-header.php
git commit -m "feat: show search query in results page header"
```

---

## Task 5: Search Empty State

**Files:**
- Create: `template-parts/search/search-empty-state.php`
- Modify: `woocommerce/archive-product.php`

**Interfaces:**
- Consumes: `get_search_query()`, `WC_Product_Query(orderby=popularity, limit=4)`
- Produces: UI empty state với 4 sản phẩm bán chạy khi `is_search() && 0 results`

- [ ] **Step 1: Tạo thư mục và file search-empty-state.php**

```bash
mkdir -p wp-content/themes/nang-tho-cosmetics/template-parts/search
```

Tạo file `template-parts/search/search-empty-state.php`:

```php
<?php
/**
 * Search Empty State
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

$search_query = esc_html(get_search_query());
?>

<div class="bg-white dark:bg-[#2c1621] rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm p-10 text-center">
    <span class="material-symbols-outlined text-5xl text-gray-300 mb-4 block">search_off</span>
    <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">
        Không tìm thấy kết quả cho <span class="text-primary">"<?php echo $search_query; ?>"</span>
    </h3>
    <p class="text-sm text-gray-500 mb-8">
        Thử tìm với từ khoá ngắn hơn hoặc kiểm tra lại chính tả.
    </p>

    <?php
    $popular_products = new WC_Product_Query([
        'limit'   => 4,
        'status'  => 'publish',
        'orderby' => 'popularity',
        'order'   => 'DESC',
    ]);
    $products = $popular_products->get_products();

    if (!empty($products)) :
    ?>
    <div class="border-t border-gray-100 dark:border-gray-800 pt-8">
        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-5">Có thể bạn quan tâm</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($products as $product) :
                $image_id  = $product->get_image_id();
                $image_url = $image_id
                    ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail')
                    : wc_placeholder_img_src('woocommerce_thumbnail');
            ?>
            <a href="<?php echo esc_url($product->get_permalink()); ?>"
               class="group flex flex-col items-center text-center gap-2 p-3 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-primary/30 hover:shadow-md transition-all">
                <img src="<?php echo esc_url($image_url); ?>"
                     alt="<?php echo esc_attr($product->get_name()); ?>"
                     class="w-full aspect-square object-contain rounded-lg bg-gray-50 group-hover:scale-105 transition-transform duration-300">
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200 line-clamp-2 leading-snug">
                    <?php echo esc_html($product->get_name()); ?>
                </span>
                <span class="text-sm font-bold text-primary">
                    <?php echo wp_strip_all_tags($product->get_price_html()); ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-8">
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm font-medium hover:border-primary hover:text-primary transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Xem tất cả sản phẩm
        </a>
    </div>
</div>
```

- [ ] **Step 2: Sửa archive-product.php — route sang empty state khi search + 0 kết quả**

Tìm block else (khoảng dòng 47-54):

```php
            } else {
                ?>
                <div class="bg-white dark:bg-[#2c1621] rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm p-8 text-center">
                    <p class="text-gray-600 dark:text-gray-400">Không tìm thấy sản phẩm nào.</p>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="mt-4 inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition-colors">
                        Xem tất cả sản phẩm
                    </a>
                </div>
                <?php
            }
```

Thay bằng:

```php
            } else {
                if (is_search()) {
                    get_template_part('template-parts/search/search-empty-state');
                } else {
                    ?>
                    <div class="bg-white dark:bg-[#2c1621] rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm p-8 text-center">
                        <p class="text-gray-600 dark:text-gray-400">Không tìm thấy sản phẩm nào.</p>
                        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="mt-4 inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition-colors">
                            Xem tất cả sản phẩm
                        </a>
                    </div>
                    <?php
                }
            }
```

- [ ] **Step 3: Deploy và kiểm tra**

```bash
scp wp-content/themes/nang-tho-cosmetics/template-parts/search/search-empty-state.php \
    nangtho:/opt/nang-tho-cosmetics/wp-content/themes/nang-tho-cosmetics/template-parts/search/search-empty-state.php

scp wp-content/themes/nang-tho-cosmetics/woocommerce/archive-product.php \
    nangtho:/opt/nang-tho-cosmetics/wp-content/themes/nang-tho-cosmetics/woocommerce/archive-product.php
```

Vào `https://nangthocomestic.io.vn/cua-hang/?s=xyzkhongcosanpham`.

Expected:
- Icon search_off + "Không tìm thấy kết quả cho 'xyzkhongcosanpham'"
- Grid 4 sản phẩm bán chạy bên dưới
- Nút "← Xem tất cả sản phẩm"

Vào `https://nangthocomestic.io.vn/cua-hang/?s=son` (có kết quả).

Expected: Trang hiện sản phẩm bình thường, KHÔNG hiện empty state.

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/nang-tho-cosmetics/template-parts/search/search-empty-state.php \
        wp-content/themes/nang-tho-cosmetics/woocommerce/archive-product.php
git commit -m "feat: search empty state with popular products suggestion"
```
