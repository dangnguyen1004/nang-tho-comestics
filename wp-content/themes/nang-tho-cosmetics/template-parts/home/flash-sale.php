<?php
/**
 * Flash Sale component converted to PHP
 */

// Fetch products on sale
$include_ids = wc_get_product_ids_on_sale();

$flashSaleProducts = [];

if ( ! empty( $include_ids ) ) {
    $args = array(
        'limit'   => 8,
        'status'  => 'publish',
        'include' => $include_ids,
        'orderby' => 'rand',
    );

    $products_on_sale = wc_get_products($args);

    if ( ! is_wp_error( $products_on_sale ) && is_array( $products_on_sale ) ) {
        foreach ($products_on_sale as $product) {
            $regular_price = (float) $product->get_regular_price();
            $sale_price = (float) $product->get_sale_price();
            $discount = 0;
            if ($regular_price > 0) {
                $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
            }

            $flashSaleProducts[] = [
                'id'            => $product->get_id(),
                'name'          => $product->get_name(),
                'price'         => wc_price($sale_price),
                'originalPrice' => wc_price($regular_price),
                'discount'      => '-' . $discount . '%',
                'sold'          => (int) $product->get_meta('total_sales'),
                'image'         => wp_get_attachment_image_url($product->get_image_id(), 'large'),
                'link'          => get_permalink($product->get_id()),
            ];
        }
    }
}
?>
<section class="bg-gradient-to-r from-pink-50 to-white dark:from-[#2a1b24] dark:to-background-dark py-10">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3 text-primary">
                    <span class="material-symbols-outlined animate-pulse">bolt</span>
                    <h2 class="text-3xl font-black uppercase italic tracking-tighter">Deal Sốc Hôm Nay</h2>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Kết thúc trong:</span>
                    <div class="flex gap-1 text-white text-sm font-bold">
                        <div class="bg-black dark:bg-primary rounded px-2 py-1">02</div>
                        <div class="text-black dark:text-white py-1">:</div>
                        <div class="bg-black dark:bg-primary rounded px-2 py-1">15</div>
                        <div class="text-black dark:text-white py-1">:</div>
                        <div class="bg-black dark:bg-primary rounded px-2 py-1">45</div>
                    </div>
                </div>
            </div>
            <a href="<?php echo home_url('/sale'); ?>"
                class="text-primary font-bold hover:underline flex items-center gap-1">
                Xem tất cả deal <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>

        <!-- Horizontal Scroll Products -->
        <div class="flex overflow-x-auto gap-4 pb-6 hide-scrollbar snap-x snap-mandatory -mx-4 px-4 md:mx-0 md:px-0">
            <?php foreach ($flashSaleProducts as $product): ?>
                <a href="<?php echo esc_url($product['link']); ?>"
                    class="min-w-[240px] md:min-w-[260px] w-[240px] md:w-[260px] snap-center bg-white dark:bg-[#2a1b24] rounded-lg border border-gray-100 dark:border-white/5 p-3 flex flex-col gap-3 group hover:shadow-xl transition-shadow relative">
                    <div class="absolute top-3 left-3 bg-primary text-white text-xs font-bold px-2 py-1 rounded z-10">
                        <?php echo esc_html($product['discount']); ?>
                    </div>
                    <div class="w-full aspect-square bg-gray-50 dark:bg-white/5 rounded-lg overflow-hidden flex-shrink-0">
                        <?php if ($product['image']): ?>
                            <div class="w-full h-full bg-cover bg-center group-hover:scale-105 transition-transform duration-500"
                                style="background-image: url('<?php echo esc_url($product['image']); ?>')"></div>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-white/5">
                                <span class="material-symbols-outlined text-4xl text-gray-300">image</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col gap-1">
                        <h3 class="font-bold text-text-dark dark:text-white line-clamp-2 min-h-[48px]">
                            <?php echo esc_html($product['name']); ?>
                        </h3>
                        <div class="flex items-baseline gap-2">
                            <span class="text-primary font-bold text-lg">
                                <?php echo $product['price']; ?>
                            </span>
                            <span class="text-gray-400 text-sm line-through decoration-1">
                                <?php echo $product['originalPrice']; ?>
                            </span>
                        </div>
                        <?php 
                        // Simulate progress bar based on sold vs stock if available, 
                        // or just use 70-90% for flash sale look if real stock isn't managed
                        $progress = min(95, max(40, ($product['sold'] % 50) + 40)); 
                        ?>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mt-2 overflow-hidden flex-shrink-0">
                            <div class="bg-primary h-2.5 rounded-full"
                                style="width: <?php echo esc_attr($progress); ?>%"></div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Đã bán
                            <?php echo esc_html($product['sold'] > 0 ? $product['sold'] : rand(5, 20)); ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>