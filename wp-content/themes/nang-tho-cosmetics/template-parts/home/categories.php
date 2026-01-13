<?php
/**
 * Categories component converted to PHP
 */

// Fetch top-level product categories
$args = array(
    'taxonomy' => 'product_cat',
    'hide_empty' => true,
    'parent' => 0,
    'number' => 12,
);

$product_categories = get_terms($args);

// Map icons to category slugs/names (Material Symbols names)
$icon_mapping = [
    'cham-soc-da-mat' => 'face',
    'trang-diem' => 'brush',
    'co-the' => 'spa',
    'cham-soc-toc' => 'content_cut',
    'thuc-pham-cn' => 'health_and_safety',
    'nuoc-hoa' => 'fragrance',
    'cham-soc-body' => 'spa',
    'cham-soc-toc' => 'hail',
];

$categories = [];

if (!is_wp_error($product_categories) && is_array($product_categories)) {
    foreach ($product_categories as $cat) {
        if ($cat->name === 'Uncategorized' || $cat->slug === 'uncategorized')
            continue;

        $categories[] = [
            'name' => $cat->name,
            'slug' => $cat->slug,
            'icon' => isset($icon_mapping[$cat->slug]) ? $icon_mapping[$cat->slug] : 'category',
            'link' => get_term_link($cat),
        ];
    }
}

// Limit to 6 to match original design grid if needed, or allow more
$categories = array_slice($categories, 0, 6);
?>
<section class="py-10 max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-text-dark dark:text-white">Danh Mục Nổi Bật</h2>
        <a href="<?php echo home_url('/shop'); ?>" class="text-primary font-medium hover:underline text-sm">Xem tất
            cả</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
        <?php foreach ($categories as $cat): ?>
            <a href="<?php echo esc_url($cat['link']); ?>"
                class="flex flex-col items-center gap-3 p-4 rounded-xl bg-white dark:bg-white/5 border border-transparent hover:border-primary/30 hover:shadow-lg transition-all group">
                <div
                    class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-3xl">
                        <?php echo esc_html($cat['icon']); ?>
                    </span>
                </div>
                <h3 class="text-sm font-bold text-center group-hover:text-primary transition-colors">
                    <?php echo esc_html($cat['name']); ?>
                </h3>
            </a>
        <?php endforeach; ?>
    </div>
</section>