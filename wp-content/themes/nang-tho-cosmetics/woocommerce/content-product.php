<?php
/**
 * The template for displaying product content within loops
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

global $product;

if (!wc_get_loop_prop('is_paginated', true) && wc_get_loop_prop('is_shortcode')) {
    return;
}
?>

<div class="group bg-white dark:bg-[#2c1621] rounded-lg border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl hover:border-primary/30 transition-all duration-300 flex flex-col overflow-hidden relative">
    <div class="relative aspect-[4/5] bg-white p-4 overflow-hidden">
        <?php
        // Discount badge
        if ($product->is_on_sale()) {
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            if ($regular_price && $sale_price) {
                $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
                echo '<div class="absolute top-0 left-0 bg-primary text-white text-xs font-bold px-2 py-1 z-10 rounded-br-lg shadow-sm">-' . esc_html($discount) . '%</div>';
            }
        }

        // Best seller badge
        if ($product->get_total_sales() > 50) {
            echo '<div class="absolute top-0 left-0 bg-gray-800 text-white text-xs font-bold px-2 py-1 z-10 rounded-br-lg shadow-sm">Bán chạy</div>';
        }

        // Out of stock badge
        if (!$product->is_in_stock()) {
            echo '<div class="absolute top-0 left-0 bg-gray-500 text-white text-xs font-bold px-2 py-1 z-10 rounded-br-lg shadow-sm">Hết hàng</div>';
        }
        ?>

        <a href="<?php echo esc_url($product->get_permalink()); ?>" class="block w-full h-full">
            <?php
            $image_id = $product->get_image_id();
            if ($image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'woocommerce_single');
                echo '<img class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500' . (!$product->is_in_stock() ? ' grayscale opacity-60' : '') . '" src="' . esc_url($image_url) . '" alt="' . esc_attr($product->get_name()) . '" />';
            } else {
                echo '<div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-white/5">';
                echo '<span class="material-symbols-outlined text-4xl text-gray-300">image</span>';
                echo '</div>';
            }
            ?>
        </a>

        <?php if ($product->is_in_stock()): ?>
            <!-- Quick Actions (Hover) -->
            <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 bg-gradient-to-t from-white/90 to-transparent pt-10 flex justify-center gap-2">
                <button class="bg-white text-gray-800 shadow-md p-2 rounded-full hover:bg-primary hover:text-white transition-colors" title="Xem nhanh" onclick="event.preventDefault(); window.location.href='<?php echo esc_url($product->get_permalink()); ?>'">
                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                </button>
                <button class="bg-white text-gray-800 shadow-md p-2 rounded-full hover:bg-primary hover:text-white transition-colors" title="Yêu thích" onclick="event.preventDefault();">
                    <span class="material-symbols-outlined text-[20px]">favorite</span>
                </button>
            </div>
        <?php else: ?>
            <!-- Out of stock overlay -->
            <div class="absolute inset-0 bg-white/50 z-[1] pointer-events-none"></div>
        <?php endif; ?>
    </div>

    <div class="p-4 flex flex-col gap-1.5 flex-1">
        <?php
        // Brand/Origin (if available as attribute)
        $brand = $product->get_attribute('pa_thuong-hieu') ?: $product->get_attribute('thuong-hieu');
        if ($brand) {
            echo '<div class="flex items-center gap-1">';
            echo '<span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">' . esc_html($brand) . '</span>';
            echo '</div>';
        }
        ?>

        <h3 class="font-bold text-[#1b0d14] dark:text-gray-100 text-sm leading-snug line-clamp-2 group-hover:text-primary transition-colors min-h-[2.5em]">
            <a href="<?php echo esc_url($product->get_permalink()); ?>">
                <?php echo esc_html($product->get_name()); ?>
            </a>
        </h3>

        <?php
        // Rating
        $rating = $product->get_average_rating();
        $review_count = $product->get_review_count();
        if ($rating > 0) {
            echo '<div class="flex items-center gap-1 mt-1">';
            echo '<div class="flex text-yellow-400 text-[12px]">';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= floor($rating)) {
                    echo '<span class="material-symbols-outlined text-[14px] fill-current">star</span>';
                } elseif ($i - 0.5 <= $rating) {
                    echo '<span class="material-symbols-outlined text-[14px] fill-current">star_half</span>';
                } else {
                    echo '<span class="material-symbols-outlined text-[14px] text-gray-300">star</span>';
                }
            }
            echo '</div>';
            $review_text = $review_count >= 1000 ? number_format($review_count / 1000, 1) . 'k' : $review_count;
            echo '<span class="text-[10px] text-gray-400">(' . esc_html($review_text) . ')</span>';
            echo '</div>';
        }
        ?>

        <div class="mt-auto pt-2">
            <div class="flex flex-wrap items-baseline gap-2">
                <?php
                // Price
                if ($product->is_on_sale()) {
                    echo '<span class="text-lg font-bold text-primary">' . $product->get_price_html() . '</span>';
                    echo '<span class="text-xs text-gray-400 line-through">' . wc_price($product->get_regular_price()) . '</span>';
                } else {
                    echo '<span class="text-lg font-bold text-primary">' . $product->get_price_html() . '</span>';
                }
                ?>
            </div>

            <?php if ($product->is_in_stock()): ?>
                <div class="mt-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 absolute bottom-4 left-4 right-4 bg-white dark:bg-[#2c1621] md:relative md:opacity-100 md:bottom-0 md:left-0 md:right-0 md:bg-transparent">
                    <?php
                    woocommerce_template_loop_add_to_cart(array(
                        'class' => 'w-full bg-primary hover:bg-pink-700 text-white font-bold py-2 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm shadow-lg md:shadow-none shadow-pink-200'
                    ));
                    ?>
                </div>
            <?php else: ?>
                <button class="mt-3 w-full bg-gray-100 text-gray-400 font-bold py-2 px-4 rounded-lg flex items-center justify-center gap-2 text-sm cursor-not-allowed" disabled>
                    <span class="material-symbols-outlined text-[18px]">notifications</span>
                    Nhận thông báo
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>