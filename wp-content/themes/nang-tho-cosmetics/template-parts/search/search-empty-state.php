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
