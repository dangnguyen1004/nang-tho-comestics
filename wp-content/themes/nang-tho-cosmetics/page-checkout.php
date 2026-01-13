<?php
/**
 * Template Name: Checkout Page
 *
 * This template overrides the content of the checkout page to force
 * the Classic WooCommerce Checkout Shortcode. This allows us to use
 * traditional hooks and filters for field customization, which are not
 * fully supported by the Block Checkout.
 *
 * @package Nang_Tho_Cosmetics
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container mx-auto px-4 lg:px-8 py-6 flex-grow">

        <!-- Breadcrumbs -->
        <div class="mb-6 text-sm font-medium text-gray-500 overflow-x-auto whitespace-nowrap pb-2">
            <?php woocommerce_breadcrumb(); ?>
        </div>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header mb-8">
                <?php the_title('<h1 class="entry-title text-3xl font-bold">', '</h1>'); ?>
            </header>

            <div class="entry-content">
                <?php
                // Force the Classic Checkout Shortcode
                // This bypasses the Block Checkout content in the database
                echo do_shortcode('[woocommerce_checkout]');
                ?>
            </div>
        </article>

    </div>
</main>

<?php
get_footer();
