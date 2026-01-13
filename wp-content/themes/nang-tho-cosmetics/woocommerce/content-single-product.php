<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

global $product;

if (!is_a($product, 'WC_Product')) {
    return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>
    <!-- Product Hero Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 mb-12">
        <!-- Left Column: Product Gallery -->
        <div class="lg:col-span-7 flex flex-col gap-4">
            <?php wc_get_template('single-product/product-image.php'); ?>
        </div>

        <!-- Right Column: Product Info -->
        <div class="lg:col-span-5 flex flex-col h-full">
            <?php wc_get_template('single-product/product-summary.php'); ?>
        </div>
    </div>

    <!-- Details Tabs -->
    <div class="mb-12">
        <?php wc_get_template('single-product/tabs/tabs.php'); ?>
    </div>

    <!-- Rating & Reviews -->
    <div class="mb-16 scroll-mt-24" id="reviews">
        <?php wc_get_template('single-product/reviews.php'); ?>
    </div>

    <!-- Related Products -->
    <div class="mb-12">
        <?php wc_get_template('single-product/related.php'); ?>
    </div>
</div>