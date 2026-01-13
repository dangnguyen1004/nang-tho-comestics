<?php
/**
 * Shop Sidebar Filters
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

$current_category = get_queried_object();
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';
$selected_brands = isset($_GET['filter_brand']) ? (array) $_GET['filter_brand'] : array();
$stock_status = isset($_GET['stock_status']) ? sanitize_text_field($_GET['stock_status']) : '';
?>

<aside class="w-full lg:w-1/4 flex-shrink-0 space-y-8">
    <!-- Categories -->
    <div class="bg-white dark:bg-[#2c1621] p-5 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <h3 class="font-bold text-lg mb-4 flex items-center gap-2 text-gray-900 dark:text-white">
            <span class="material-symbols-outlined text-primary">category</span>
            Danh mục
        </h3>
        <ul class="space-y-2">
            <?php
            // Add "Toàn bộ" (All) option at the top
            $is_shop_page = is_shop() && !is_product_category();
            $shop_url = wc_get_page_permalink('shop');
            ?>
            <li>
                <a class="flex items-center justify-between group p-2 rounded-lg <?php echo $is_shop_page ? 'bg-pink-50/50 dark:bg-primary/10 text-primary font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800'; ?> transition-colors" 
                   href="<?php echo esc_url($shop_url); ?>">
                    <span>Toàn bộ</span>
                    <?php
                    $all_products_count = wp_count_posts('product');
                    $total_count = isset($all_products_count->publish) ? $all_products_count->publish : 0;
                    ?>
                    <span class="text-gray-400 text-xs"><?php echo esc_html($total_count); ?></span>
                </a>
            </li>
            <?php
            $product_categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'parent' => 0,
            ));

            if (!is_wp_error($product_categories) && !empty($product_categories)):
                foreach ($product_categories as $category) {
                $is_active = $current_category && $current_category->term_id === $category->term_id;
                $subcategories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                    'parent' => $category->term_id,
                ));

                $category_url = get_term_link($category);
                $product_count = $category->count;
                ?>
                <li>
                    <a class="flex items-center justify-between group p-2 rounded-lg <?php echo $is_active ? 'bg-pink-50/50 dark:bg-primary/10 text-primary font-bold' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800'; ?> transition-colors" 
                       href="<?php echo esc_url($category_url); ?>">
                        <span><?php echo esc_html($category->name); ?></span>
                        <?php if (!empty($subcategories)): ?>
                            <span class="material-symbols-outlined text-sm category-toggle">expand_more</span>
                        <?php else: ?>
                            <span class="text-gray-400 text-xs"><?php echo esc_html($product_count); ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (!empty($subcategories)): ?>
                        <ul class="ml-4 mt-2 space-y-2 border-l-2 border-pink-100 dark:border-gray-700 pl-3 category-submenu hidden">
                            <?php foreach ($subcategories as $subcat): ?>
                                <?php
                                $subcat_url = get_term_link($subcat);
                                $is_sub_active = $current_category && $current_category->term_id === $subcat->term_id;
                                ?>
                                <li>
                                    <a class="text-sm py-1 block <?php echo $is_sub_active ? 'text-primary font-medium' : 'text-gray-600 dark:text-gray-300 hover:text-primary'; ?>" 
                                       href="<?php echo esc_url($subcat_url); ?>">
                                        <?php echo esc_html($subcat->name); ?> (<?php echo esc_html($subcat->count); ?>)
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php 
                }
            endif; 
            ?>
        </ul>
    </div>

    <!-- Price Filter -->
    <div class="bg-white dark:bg-[#2c1621] p-5 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <h3 class="font-bold text-lg mb-4 text-gray-900 dark:text-white">Mức giá</h3>
        <div class="space-y-4">
            <!-- Visual slider placeholder -->
            <div class="h-1.5 w-full bg-gray-200 dark:bg-gray-700 rounded-full relative mt-2" id="price-slider-track">
                <div class="absolute left-0 right-0 top-0 bottom-0 bg-primary rounded-full" id="price-slider-range" style="left: 0%; right: 0%;"></div>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-white border-2 border-primary rounded-full shadow cursor-pointer" id="price-slider-min"></div>
                <div class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-white border-2 border-primary rounded-full shadow cursor-pointer" id="price-slider-max"></div>
            </div>
            <form method="get" class="price-filter-form">
                <?php
                // Preserve other query parameters
                foreach ($_GET as $key => $value) {
                    if ($key !== 'min_price' && $key !== 'max_price' && $key !== 'paged') {
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
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <div class="w-1/2">
                        <label class="text-xs mb-1 block">Từ (₫)</label>
                        <input type="number" name="min_price" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-sm focus:border-primary focus:ring-primary" 
                               value="<?php echo esc_attr($min_price); ?>" placeholder="0" min="0" step="1000">
                    </div>
                    <div class="pt-5">-</div>
                    <div class="w-1/2">
                        <label class="text-xs mb-1 block">Đến (₫)</label>
                        <input type="number" name="max_price" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-sm focus:border-primary focus:ring-primary" 
                               value="<?php echo esc_attr($max_price); ?>" placeholder="1000000" min="0" step="1000">
                    </div>
                </div>
                <button type="submit" class="w-full py-2 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg text-sm font-bold transition-colors mt-4">
                    Áp dụng
                </button>
            </form>
        </div>
    </div>

    <!-- Brands Filter -->
    <div class="bg-white dark:bg-[#2c1621] p-5 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <h3 class="font-bold text-lg mb-3 text-gray-900 dark:text-white">Thương hiệu</h3>
        <div class="relative mb-3">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-gray-400 text-sm">search</span>
            <input type="text" id="brand-search" class="w-full pl-9 pr-3 py-2 text-sm border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 focus:border-primary focus:ring-primary" placeholder="Tìm thương hiệu...">
        </div>
        <form method="get" class="brand-filter-form">
            <?php
            // Preserve other query parameters
            foreach ($_GET as $key => $value) {
                if ($key !== 'filter_brand' && $key !== 'paged') {
                    if (is_array($value)) {
                        foreach ($value as $v) {
                            echo '<input type="hidden" name="' . esc_attr($key) . '[]" value="' . esc_attr($v) . '">';
                        }
                    } else {
                        echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                    }
                }
            }

            // Get brands (product attributes or custom taxonomy)
            $brands = get_terms(array(
                'taxonomy' => 'pa_thuong-hieu',
                'hide_empty' => true,
            ));

            if (empty($brands) || is_wp_error($brands)) {
                // Try alternative attribute name
                $brands = get_terms(array(
                    'taxonomy' => 'product_brand',
                    'hide_empty' => true,
                ));
            }

            if (!empty($brands) && !is_wp_error($brands)):
            ?>
            <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar pr-2" id="brand-list">
                <?php foreach ($brands as $brand): ?>
                    <?php
                    $brand_slug = $brand->slug;
                    $is_selected = in_array($brand_slug, $selected_brands);
                    ?>
                    <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer hover:text-primary brand-item" data-brand-name="<?php echo esc_attr(strtolower($brand->name)); ?>">
                        <input type="checkbox" name="filter_brand[]" value="<?php echo esc_attr($brand_slug); ?>" 
                               class="rounded border-gray-300 text-primary focus:ring-primary brand-checkbox" 
                               <?php checked($is_selected); ?>>
                        <span><?php echo esc_html($brand->name); ?></span>
                        <span class="ml-auto text-xs text-gray-400">(<?php echo esc_html($brand->count); ?>)</span>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="w-full py-2 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-lg text-sm font-bold transition-colors mt-3 hidden" id="apply-brand-filter">
                Áp dụng
            </button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Stock Filter -->
    <div class="bg-white dark:bg-[#2c1621] p-5 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm flex items-center justify-between">
        <span class="font-medium text-gray-900 dark:text-white">Chỉ hiện hàng có sẵn</span>
        <form method="get" class="stock-filter-form inline-block">
            <?php
            // Preserve other query parameters
            foreach ($_GET as $key => $value) {
                if ($key !== 'stock_status' && $key !== 'paged') {
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
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="stock_status" value="instock" 
                       class="sr-only peer stock-toggle" 
                       <?php checked($stock_status === 'instock'); ?>>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
            </label>
        </form>
    </div>
</aside>