<?php
/**
 * The template for displaying all WooCommerce pages
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

if (is_product()) {
    // Single product page uses its own template
    wc_get_template('single-product.php');
} elseif (is_checkout() && !is_wc_endpoint_url('order-pay') && !is_wc_endpoint_url('order-received')) {
    // Checkout page uses its own template
    get_header();
    ?>
    <div class="container mx-auto px-4 lg:px-8 py-6 flex-grow">
        <div class="mb-6 text-sm font-medium text-gray-500 overflow-x-auto whitespace-nowrap pb-2">
            <?php woocommerce_breadcrumb(); ?>
        </div>
        <div class="w-full">
            <?php echo do_shortcode('[woocommerce_checkout]'); ?>
        </div>
    </div>
    <?php
    get_footer();
} else {
    // Shop and other WooCommerce pages use archive-product template
    wc_get_template('archive-product.php');
}
