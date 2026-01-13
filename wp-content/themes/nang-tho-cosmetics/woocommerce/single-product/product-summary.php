<?php
/**
 * Single Product Summary
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

global $product;
?>

<!-- Title & Rating -->
<div class="mb-4">
    <h1 class="text-2xl md:text-3xl font-bold text-text-main dark:text-white leading-tight mb-2"><?php the_title(); ?></h1>
    <div class="flex items-center gap-4 text-sm">
        <?php
        $rating = $product->get_average_rating();
        $review_count = $product->get_review_count();
        if ($rating > 0):
        ?>
        <div class="flex items-center gap-1 text-primary">
            <span class="font-bold"><?php echo esc_html(number_format($rating, 1)); ?></span>
            <div class="flex">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php if ($i <= floor($rating)): ?>
                        <span class="material-symbols-outlined text-base fill-current">star</span>
                    <?php elseif ($i - 0.5 <= $rating): ?>
                        <span class="material-symbols-outlined text-base fill-current">star_half</span>
                    <?php else: ?>
                        <span class="material-symbols-outlined text-base text-gray-300">star</span>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>
        <span class="w-px h-4 bg-gray-300"></span>
        <a class="text-text-secondary underline hover:text-primary" href="#reviews">
            <?php printf(_n('%s Đánh giá', '%s Đánh giá', $review_count, 'nang-tho-cosmetics'), number_format_i18n($review_count)); ?>
        </a>
        <?php endif; ?>
        <span class="w-px h-4 bg-gray-300"></span>
        <span class="text-text-secondary">Đã bán <?php echo esc_html($product->get_total_sales() > 0 ? $product->get_total_sales() : '0'); ?></span>
    </div>
</div>

<!-- Price -->
<div class="bg-[#fcf0f5] dark:bg-white/5 p-4 rounded-lg mb-6 flex items-end gap-3">
    <?php
    if ($product->is_on_sale()) {
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
        $discount = $regular_price && $sale_price ? round((($regular_price - $sale_price) / $regular_price) * 100) : 0;
        ?>
        <span class="text-3xl font-bold text-primary"><?php echo $product->get_price_html(); ?></span>
        <span class="text-lg text-gray-400 line-through mb-1"><?php echo wc_price($regular_price); ?></span>
        <?php if ($discount > 0): ?>
            <span class="text-sm font-semibold text-primary bg-white dark:bg-gray-800 px-2 py-0.5 rounded border border-primary/20 mb-1.5 ml-auto md:ml-2">
                Tiết kiệm <?php echo esc_html($discount); ?>%
            </span>
        <?php endif; ?>
    <?php } else { ?>
        <span class="text-3xl font-bold text-primary"><?php echo $product->get_price_html(); ?></span>
    <?php } ?>
</div>

<!-- Short Description -->
<?php
$short_description = apply_filters('woocommerce_short_description', $product->get_short_description());
if ($short_description):
?>
<div class="mb-6 space-y-2">
    <?php
    // Extract bullet points or create from short description
    $lines = explode("\n", strip_tags($short_description));
    foreach (array_filter($lines) as $line):
        $line = trim($line);
        if (!empty($line)):
    ?>
        <div class="flex items-start gap-2">
            <span class="material-symbols-outlined text-green-500 text-xl">check_circle</span>
            <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo esc_html($line); ?></p>
        </div>
    <?php
        endif;
    endforeach;
    ?>
</div>
<?php endif; ?>

<!-- Variants -->
<?php
if ($product->is_type('variable')):
    // Let WooCommerce handle variable product form
    do_action('woocommerce_before_add_to_cart_form');
    ?>
    <div class="mb-6">
        <?php woocommerce_template_single_add_to_cart(); ?>
    </div>
    <?php
    do_action('woocommerce_after_add_to_cart_form');
endif;
?>

<!-- Quantity & Actions (for simple products) -->
<?php if (!$product->is_type('variable')): ?>
<div class="mt-auto pt-6 border-t border-gray-100 dark:border-gray-800">
    <?php do_action('woocommerce_before_add_to_cart_form'); ?>
    
    <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
        <?php do_action('woocommerce_before_add_to_cart_button'); ?>
        
        <div class="flex flex-col gap-4 mb-4">
            <!-- Quantity Stepper -->
            <div class="flex items-center justify-center border border-gray-300 dark:border-gray-600 rounded-lg h-14 w-fit max-w-[200px] mx-auto sm:mx-0 bg-white dark:bg-gray-800">
                <button type="button" class="px-4 h-14 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-300 quantity-decrease">
                    <span class="material-symbols-outlined text-xl">remove</span>
                </button>
                <?php
                woocommerce_quantity_input(
                    array(
                        'min_value' => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                        'max_value' => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                        'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(),
                        'input_class' => 'w-16 h-full text-center border-none bg-transparent focus:ring-0 text-text-main dark:text-white font-bold text-lg',
                    ),
                    $product
                );
                ?>
                <button type="button" class="px-4 h-14 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-300 quantity-increase">
                    <span class="material-symbols-outlined text-xl">add</span>
                </button>
            </div>
            
            <!-- Main Buttons -->
            <?php if ($product->is_in_stock()): ?>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" 
                            class="flex-1 h-16 flex items-center justify-center gap-2 rounded-lg border-2 border-primary text-primary font-bold hover:bg-primary/5 transition-colors add-to-cart-btn text-base py-3">
                        <span class="material-symbols-outlined text-xl">shopping_cart</span>
                        Thêm vào giỏ
                    </button>
                    <button type="button" 
                            class="flex-1 h-16 flex items-center justify-center gap-2 rounded-lg bg-primary hover:bg-primary-dark text-white font-bold shadow-lg shadow-primary/30 transition-all buy-now-btn text-base py-3" 
                            data-checkout-url="<?php echo esc_url(wc_get_checkout_url()); ?>"
                            data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                        Mua ngay
                    </button>
                </div>
            <?php else: ?>
                <button type="button" class="w-full h-16 flex items-center justify-center gap-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-400 font-bold cursor-not-allowed text-base py-3" disabled>
                    <span class="material-symbols-outlined text-xl">notifications</span>
                    Hết hàng
                </button>
            <?php endif; ?>
        </div>
        
        <?php do_action('woocommerce_after_add_to_cart_button'); ?>
    </form>
    
    <?php do_action('woocommerce_after_add_to_cart_form'); ?>

    <!-- Trust Signals -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs text-gray-500 dark:text-gray-400">
        <div class="flex flex-col items-center justify-center text-center gap-1 p-2 bg-gray-50 dark:bg-gray-800 rounded">
            <span class="material-symbols-outlined text-primary">verified_user</span>
            <span>100% Chính hãng</span>
        </div>
        <div class="flex flex-col items-center justify-center text-center gap-1 p-2 bg-gray-50 dark:bg-gray-800 rounded">
            <span class="material-symbols-outlined text-primary">local_shipping</span>
            <span>Miễn phí vận chuyển</span>
        </div>
        <div class="flex flex-col items-center justify-center text-center gap-1 p-2 bg-gray-50 dark:bg-gray-800 rounded">
            <span class="material-symbols-outlined text-primary">assignment_return</span>
            <span>Đổi trả 14 ngày</span>
        </div>
        <div class="flex flex-col items-center justify-center text-center gap-1 p-2 bg-gray-50 dark:bg-gray-800 rounded">
            <span class="material-symbols-outlined text-primary">support_agent</span>
            <span>Hỗ trợ 24/7</span>
        </div>
    </div>
</div>
<?php endif; ?>