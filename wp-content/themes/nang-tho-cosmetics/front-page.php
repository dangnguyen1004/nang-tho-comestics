<?php
/**
 * The template for displaying the front page
 *
 * @package Nang_Tho_Cosmetics
 */

get_header();
?>

<main id="primary" class="site-main">
    <?php
    get_template_part('template-parts/home/hero');
    get_template_part('template-parts/home/categories');
    get_template_part('template-parts/home/flash-sale');
    get_template_part('template-parts/home/best-sellers');
    get_template_part('template-parts/home/brands');
    ?>
</main>

<?php
get_footer();
