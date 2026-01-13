<?php
/**
 * The Template for displaying all single products
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

get_header();
?>

<main class="flex-grow w-full max-w-[1280px] mx-auto px-4 md:px-10 py-6">
    <!-- Breadcrumbs -->
    <nav class="flex mb-6 text-sm font-medium text-text-secondary overflow-x-auto whitespace-nowrap pb-2">
        <?php woocommerce_breadcrumb(); ?>
    </nav>

    <?php
    while (have_posts()) {
        the_post();
        wc_get_template_part('content', 'single-product');
    }
    ?>
</main>

<?php
get_footer();