<?php
/**
 * Product Grid Header with Search and Sort
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

$current_category = get_queried_object();
global $wp_query;
$product_count = $wp_query->found_posts ?: wc_get_loop_prop('total', 0);
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';

// Get active filters for tags
$active_filters = array();
if (isset($_GET['min_price']) && $_GET['min_price']) {
    $active_filters[] = array('type' => 'price', 'label' => 'Giá: ' . number_format(floatval($_GET['min_price'])) . '₫ - ' . (isset($_GET['max_price']) ? number_format(floatval($_GET['max_price'])) : '∞') . '₫', 'key' => 'price');
}
if (isset($_GET['filter_brand']) && !empty($_GET['filter_brand'])) {
    foreach ((array) $_GET['filter_brand'] as $brand_slug) {
        $brand = get_term_by('slug', $brand_slug, 'pa_thuong-hieu');
        if (!$brand) {
            $brand = get_term_by('slug', $brand_slug, 'product_brand');
        }
        if ($brand) {
            $active_filters[] = array('type' => 'brand', 'label' => $brand->name, 'key' => 'brand_' . $brand_slug, 'value' => $brand_slug);
        }
    }
}
if (isset($_GET['stock_status']) && $_GET['stock_status'] === 'instock') {
    $active_filters[] = array('type' => 'stock', 'label' => 'Có hàng', 'key' => 'stock');
}
?>

<div class="bg-white dark:bg-[#2c1621] rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm p-4 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
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
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Inner Search -->
            <form method="get" class="relative min-w-[200px]">
                <?php
                // Preserve category and other filters
                foreach ($_GET as $key => $value) {
                    if ($key !== 's' && $key !== 'paged') {
                        if (is_array($value)) {
                            foreach ($value as $v) {
                                echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($v) . '">';
                            }
                        } else {
                            echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                        }
                    }
                }
                ?>
                <input type="text" name="s" 
                       class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm focus:ring-primary focus:border-primary" 
                       placeholder="Tìm trong danh mục..." 
                       value="<?php echo esc_attr($search_query); ?>">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
            </form>
            <!-- Sort Dropdown -->
            <form method="get" class="relative min-w-[180px]">
                <?php
                // Preserve all other parameters
                foreach ($_GET as $key => $value) {
                    if ($key !== 'orderby' && $key !== 'paged') {
                        if (is_array($value)) {
                            foreach ($value as $v) {
                                echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($v) . '">';
                            }
                        } else {
                            echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                        }
                    }
                }
                ?>
                <select name="orderby" class="w-full pl-3 pr-10 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-primary focus:border-primary appearance-none cursor-pointer sort-select">
                    <option value="" <?php selected($orderby, ''); ?>>Phổ biến nhất</option>
                    <option value="popularity" <?php selected($orderby, 'popularity'); ?>>Bán chạy nhất</option>
                    <option value="date" <?php selected($orderby, 'date'); ?>>Hàng mới về</option>
                    <option value="price" <?php selected($orderby, 'price'); ?>>Giá: Thấp đến Cao</option>
                    <option value="price-desc" <?php selected($orderby, 'price-desc'); ?>>Giá: Cao đến Thấp</option>
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-[20px]">sort</span>
            </form>
        </div>
    </div>
    <!-- Active Filters Tags -->
    <?php if (!empty($active_filters)): ?>
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <?php foreach ($active_filters as $filter): ?>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-pink-50 dark:bg-pink-900/30 text-primary text-xs font-bold border border-pink-100 dark:border-pink-800">
                    <?php echo esc_html($filter['label']); ?>
                    <a href="<?php
                        $url = remove_query_arg(array($filter['key'], 'filter_brand', 'min_price', 'max_price', 'stock_status', 'paged'));
                        if (isset($filter['value'])) {
                            $current_brands = isset($_GET['filter_brand']) ? (array) $_GET['filter_brand'] : array();
                            $new_brands = array_diff($current_brands, array($filter['value']));
                            if (!empty($new_brands)) {
                                $url = add_query_arg('filter_brand', $new_brands, $url);
                            }
                        }
                        echo esc_url($url);
                    ?>" class="hover:text-red-500">
                        <span class="material-symbols-outlined text-[14px] pt-1">close</span>
                    </a>
                </span>
            <?php endforeach; ?>
            <a href="<?php echo esc_url(remove_query_arg(array('min_price', 'max_price', 'filter_brand', 'stock_status', 'paged'))); ?>" 
               class="text-xs text-gray-500 underline hover:text-primary">Xóa tất cả</a>
        </div>
    <?php endif; ?>
</div>