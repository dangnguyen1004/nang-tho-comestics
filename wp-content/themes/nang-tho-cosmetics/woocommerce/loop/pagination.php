<?php
/**
 * Pagination for product loops
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

if (!wc_get_loop_prop('is_paginated', true) || !woocommerce_products_will_display()) {
    return;
}

$total = wc_get_loop_prop('total_pages');
$current = wc_get_loop_prop('current_page');
$base = esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
$format = '?paged=%#%';

if ($total <= 1) {
    return;
}
?>
<div class="flex justify-center mt-10">
    <nav class="flex gap-2" aria-label="Product pagination">
        <?php
        echo paginate_links(apply_filters('woocommerce_pagination_args', array(
            'base' => $base,
            'format' => $format,
            'add_args' => false,
            'current' => max(1, $current),
            'total' => $total,
            'prev_text' => '<span class="material-symbols-outlined text-sm">chevron_left</span>',
            'next_text' => '<span class="material-symbols-outlined text-sm">chevron_right</span>',
            'type' => 'list',
            'end_size' => 3,
            'mid_size' => 3,
        )));
        ?>
    </nav>
</div>