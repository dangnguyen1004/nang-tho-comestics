<?php
/**
 * The Template for displaying product archives, including the main shop page
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

get_header();
?>

<main class="container mx-auto px-4 lg:px-8 py-6 flex-grow">
    <!-- Breadcrumbs -->
    <nav class="flex text-sm font-medium text-gray-500 mb-6 overflow-x-auto whitespace-nowrap pb-2">
        <?php woocommerce_breadcrumb(); ?>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <?php get_template_part('template-parts/shop/sidebar-filters'); ?>

        <!-- Product Grid Area -->
        <div class="flex-1 min-w-0">
            <?php get_template_part('template-parts/shop/product-grid-header'); ?>

            <?php
            if (woocommerce_product_loop()) {
                do_action('woocommerce_before_shop_loop');
                
                woocommerce_product_loop_start();
                
                if (wc_get_loop_prop('is_shortcode')) {
                    $columns = absint(wc_get_loop_prop('columns'));
                    wc_set_loop_prop('columns', $columns);
                }

                while (have_posts()) {
                    the_post();
                    wc_get_template_part('content', 'product');
                }

                woocommerce_product_loop_end();
                do_action('woocommerce_after_shop_loop');
            } else {
                ?>
                <div class="bg-white dark:bg-[#2c1621] rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm p-8 text-center">
                    <p class="text-gray-600 dark:text-gray-400">Không tìm thấy sản phẩm nào.</p>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="mt-4 inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-pink-700 transition-colors">
                        Xem tất cả sản phẩm
                    </a>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</main>

<?php
get_footer();