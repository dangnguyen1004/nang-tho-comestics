<?php
/**
 * Best Sellers component converted to PHP
 */

// Fetch top selling products
$args = array(
    'limit' => 10,
    'status' => 'publish',
    'orderby' => 'popularity',
    'order' => 'DESC',
);

$products_best_selling = wc_get_products($args);

    if ( ! is_wp_error( $products_best_selling ) && is_array( $products_best_selling ) ) {
        foreach ($products_best_selling as $product) {
            $bestSellers[] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'price' => $product->get_price_html(),
                'rating' => $product->get_average_rating() > 0 ? $product->get_average_rating() . ' (' . $product->get_review_count() . ')' : '5.0 (0)',
                'image' => wp_get_attachment_image_url($product->get_image_id(), 'large'),
                'link' => get_permalink($product->get_id()),
            ];
        }
    }
?>
<section class="py-12 max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold text-text-dark dark:text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-yellow-500">hotel_class</span>
            Top Bán Chạy
        </h2>
        <div class="flex gap-2">
            <button class="px-4 py-1 rounded-full text-sm font-medium bg-primary text-white">Chăm sóc da</button>
            <button
                class="px-4 py-1 rounded-full text-sm font-medium bg-white dark:bg-white/10 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/20">Trang
                điểm</button>
        </div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <?php foreach ($bestSellers as $product): ?>
            <a href="<?php echo esc_url($product['link']); ?>"
                class="bg-white dark:bg-[#2a1b24] rounded-lg p-3 flex flex-col gap-3 group hover:shadow-xl hover:-translate-y-1 transition-all duration-300 border border-gray-100 dark:border-white/5">
                <div class="w-full aspect-square bg-gray-50 dark:bg-white/5 rounded-lg overflow-hidden relative">
                    <?php if ($product['image']): ?>
                        <div class="w-full h-full bg-cover bg-center"
                            style="background-image: url('<?php echo esc_url($product['image']); ?>')"></div>
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-white/5">
                            <span class="material-symbols-outlined text-4xl text-gray-300">image</span>
                        </div>
                    <?php endif; ?>
                    <button
                        class="absolute bottom-2 right-2 size-8 bg-white dark:bg-gray-800 rounded-full shadow-md flex items-center justify-center text-primary opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-lg">shopping_bag</span>
                    </button>
                </div>
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-1 text-yellow-400 text-xs">
                        <span class="material-symbols-outlined text-[14px] fill-current">star</span>
                        <span class="text-gray-500 dark:text-gray-400 font-medium ml-1">
                            <?php echo esc_html($product['rating']); ?>
                        </span>
                    </div>
                    <h3
                        class="text-sm font-semibold text-text-dark dark:text-white line-clamp-2 min-h-[40px] group-hover:text-primary transition-colors">
                        <?php echo esc_html($product['name']); ?>
                    </h3>
                    <div class="mt-2 font-bold text-primary">
                        <?php echo $product['price']; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>