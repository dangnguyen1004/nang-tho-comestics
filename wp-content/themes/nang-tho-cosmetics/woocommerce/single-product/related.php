<?php
/**
 * Single Product Related Products
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

global $product;

if (!$product || !wc_get_related_products($product->get_id())) {
    return;
}

$related_products = array_filter(array_map('wc_get_product', wc_get_related_products($product->get_id(), 4)), 'wc_products_array_filter_visible');

if (empty($related_products)) {
    return;
}

$heading = apply_filters('woocommerce_product_related_products_heading', __('Sản phẩm liên quan', 'woocommerce'));
?>

<div class="flex justify-between items-end mb-6">
    <h2 class="text-2xl font-bold text-text-main dark:text-white"><?php echo esc_html($heading); ?></h2>
    <a class="text-primary font-semibold text-sm hover:underline" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Xem thêm</a>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
    <?php foreach ($related_products as $related_product): ?>
        <?php
        $post_object = get_post($related_product->get_id());
        setup_postdata($GLOBALS['post'] = &$post_object);
        
        $image_id = $related_product->get_image_id();
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : wc_placeholder_img_src();
        $rating = $related_product->get_average_rating();
        $review_count = $related_product->get_review_count();
        ?>
        <div class="flex flex-col gap-3 group bg-white dark:bg-gray-800 rounded-lg p-3 border border-transparent hover:border-primary/20 hover:shadow-lg transition-all">
            <div class="relative w-full aspect-[3/4] rounded-lg overflow-hidden bg-gray-100">
                <?php if ($related_product->is_on_sale()): ?>
                    <?php
                    $regular_price = $related_product->get_regular_price();
                    $sale_price = $related_product->get_sale_price();
                    if ($regular_price && $sale_price):
                        $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
                    ?>
                        <div class="absolute top-2 left-2 z-10 bg-primary text-white text-[10px] font-bold px-2 py-0.5 rounded">-<?php echo esc_html($discount); ?>%</div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <a href="<?php echo esc_url($related_product->get_permalink()); ?>" class="block w-full h-full">
                    <div class="w-full h-full bg-center bg-cover group-hover:scale-105 transition-transform duration-500" 
                         style='background-image: url("<?php echo esc_url($image_url); ?>")'></div>
                </a>
                
                <button class="absolute bottom-2 right-2 size-8 bg-white dark:bg-gray-900 rounded-full flex items-center justify-center shadow opacity-0 group-hover:opacity-100 transition-opacity text-primary hover:bg-primary hover:text-white add-to-cart-related" 
                        data-product-id="<?php echo esc_attr($related_product->get_id()); ?>">
                    <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                </button>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-text-main dark:text-white line-clamp-2 leading-snug group-hover:text-primary transition-colors">
                    <a href="<?php echo esc_url($related_product->get_permalink()); ?>">
                        <?php echo esc_html($related_product->get_name()); ?>
                    </a>
                </h3>
                
                <div class="flex items-center gap-2 mt-2">
                    <p class="text-primary font-bold"><?php echo $related_product->get_price_html(); ?></p>
                    <?php if ($related_product->is_on_sale()): ?>
                        <p class="text-xs text-gray-400 line-through"><?php echo wc_price($related_product->get_regular_price()); ?></p>
                    <?php endif; ?>
                </div>
                
                <?php if ($rating > 0): ?>
                    <div class="flex items-center gap-1 mt-1">
                        <span class="material-symbols-outlined text-[12px] text-yellow-400 fill-current">star</span>
                        <span class="text-xs text-gray-500"><?php echo esc_html(number_format($rating, 1)); ?> (<?php echo esc_html($review_count); ?>)</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
wp_reset_postdata();
?>