<?php
/**
 * The main template file
 *
 * @package Nang_Tho_Cosmetics
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container mx-auto px-4 py-8">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header mb-4">
                        <?php the_title( '<h1 class="entry-title text-3xl font-bold">', '</h1>' ); ?>
                    </header>

                    <div class="entry-content prose max-w-none">
                        <?php the_content(); ?>
                    </div>
                </article>
                <?php
            endwhile;

            the_posts_navigation();

        else :
            ?>
            <div class="no-results">
                <h2 class="text-2xl font-bold"><?php esc_html_e( 'Nothing Found', 'nang-tho-cosmetics' ); ?></h2>
                <p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for.', 'nang-tho-cosmetics' ); ?></p>
            </div>
            <?php
        endif;
        ?>
    </div>
</main>

<?php
get_footer();
