# Search UX Enhancement — Design Spec

**Date:** 2026-06-30  
**Status:** Approved

## Overview

Cải thiện trải nghiệm tìm kiếm trên website Nàng Thơ Cosmetics theo 2 hướng:
1. Live search autocomplete trên header search box
2. Search results page — tiêu đề rõ ràng + empty state thông minh

---

## Architecture (Phương án B — Dedicated Search Layer)

### New files

| File | Mục đích |
|------|----------|
| `assets/js/search-autocomplete.js` | JS cho live search dropdown |
| `template-parts/search/search-results-header.php` | Header riêng khi `is_search()` |
| `template-parts/search/search-empty-state.php` | Empty state khi 0 kết quả |

### Modified files

| File | Thay đổi |
|------|----------|
| `functions.php` | Đăng ký AJAX handler + enqueue JS |
| `woocommerce/archive-product.php` | Dùng `search-results-header` khi `is_search()` |
| `template-parts/shop/product-grid-header.php` | Hiện query đang tìm khi có `?s=` |
| `header.php` | Gắn JS autocomplete vào search box |

### Data flow

```
User gõ vào search box (header)
    → debounce 300ms (min 2 ký tự)
    → POST admin-ajax.php?action=nang_tho_live_search
    → PHP: query products + popular terms + categories
    → JSON response
    → JS render dropdown

User submit (Enter / click Tìm kiếm)
    → Redirect /cua-hang/?s=<query>
    → archive-product.php
    → is_search() = true → dùng search-results-header
    → 0 kết quả → render search-empty-state
```

---

## Feature 1: Live Search Autocomplete

### UI

Dropdown hiện dưới search box, gồm 3 section:

```
┌─────────────────────────────────────────────┐
│  Từ khoá phổ biến                           │
│  🔥 son môi lì        🔥 son dưỡng          │
├─────────────────────────────────────────────┤
│  Danh mục liên quan                         │
│  📂 Trang điểm › Son môi (23)              │
├─────────────────────────────────────────────┤
│  Sản phẩm gợi ý                            │
│  [img] Son Tint Peripera...   180.000 đ    │
│  [img] Son Kem Black Rouge... 220.000 đ    │
│  [img] Son dưỡng Laneige...   350.000 đ    │
└─────────────────────────────────────────────┘
```

### Limits

- Tối đa 2 từ khoá phổ biến
- Tối đa 1 danh mục
- Tối đa 3 sản phẩm

### Behaviour

- Debounce 300ms sau khi user ngừng gõ
- Kích hoạt từ 2 ký tự trở lên
- Click sản phẩm → vào trang sản phẩm
- Click từ khoá / danh mục → `/cua-hang/?s=<term>`
- Escape hoặc click ngoài → đóng dropdown
- ↑↓ keyboard navigation, Enter để chọn

### AJAX Handler

- Action: `nang_tho_live_search`
- Method: `wp_ajax_nopriv_` + `wp_ajax_` (cả logged in và guest)
- Input: `$_POST['query']` (sanitized)
- Output JSON:

```json
{
  "popular_terms": ["son môi lì", "son dưỡng"],
  "categories": [
    { "name": "Son môi", "url": "/danh-muc/son-moi/", "count": 23 }
  ],
  "products": [
    { "id": 1, "name": "Son Tint Peripera", "price": "180.000 ₫", "image": "...", "url": "..." }
  ]
}
```

Popular terms: hardcoded array trong PHP (có thể mở rộng sau bằng search log). Categories: `get_terms()` với `search` param. Products: `WC_Product_Query` với `s` param, limit 3.

---

## Feature 2: Search Results Page Header

Khi `is_search()` = true trong `product-grid-header.php`:

- Tiêu đề: `Kết quả tìm kiếm cho "<strong class="text-primary">query</strong>"`
- Subtitle: `Tìm thấy X sản phẩm`
- Search box vẫn giữ giá trị query hiện tại

Khi `is_search()` = false: giữ nguyên logic hiện tại.

---

## Feature 3: Empty State

Khi `woocommerce_product_loop()` = false và `is_search()` = true, render `search-empty-state.php`:

```
┌──────────────────────────────────────────────┐
│           🔍                                 │
│   Không tìm thấy "<query>"                  │
│   Thử tìm với từ khoá ngắn hơn hoặc         │
│   kiểm tra lại chính tả.                    │
│                                              │
│   ── Có thể bạn quan tâm ──                 │
│   [SP1]  [SP2]  [SP3]  [SP4]               │
│                                              │
│      [← Xem tất cả sản phẩm]               │
└──────────────────────────────────────────────┘
```

- 4 sản phẩm bán chạy: `WC_Product_Query(orderby=popularity, limit=4)`
- Không hiển thị sidebar filter (không có gì để lọc khi 0 kết quả)
- Nút "Xem tất cả sản phẩm" → `/cua-hang/`

---

## Out of scope

- Autocomplete skeleton loading (có thể thêm sau)
- Search analytics / logging
- "Xem tất cả X kết quả" ở cuối dropdown
- Lưu lịch sử tìm kiếm của user
