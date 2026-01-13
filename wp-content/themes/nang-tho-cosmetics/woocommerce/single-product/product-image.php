<?php
/**
 * Single Product Image
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

global $product;

$attachment_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();
$all_images = array();

if ($main_image_id) {
    $all_images[] = $main_image_id;
}

if ($attachment_ids) {
    $all_images = array_merge($all_images, $attachment_ids);
}

$main_image_url = $main_image_id ? wp_get_attachment_image_url($main_image_id, 'woocommerce_single') : wc_placeholder_img_src();
?>

<!-- Main Image -->
<div class="relative aspect-square md:aspect-[4/3] w-full rounded-xl overflow-hidden bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 group">
    <?php
    // Discount badge
    if ($product->is_on_sale()) {
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
        if ($regular_price && $sale_price) {
            $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
            echo '<div class="absolute top-4 left-4 z-10 flex flex-col gap-2">';
            echo '<span class="bg-primary text-white text-xs font-bold px-3 py-1 rounded-full">-' . esc_html($discount) . '%</span>';
            echo '</div>';
        }
    }
    ?>
    
    <div class="w-full h-full bg-center bg-contain bg-no-repeat transition-transform duration-500 group-hover:scale-105 product-main-image" 
         id="product-main-image"
         style='background-image: url("<?php echo esc_url($main_image_url); ?>")'></div>
    
    <button class="absolute bottom-4 right-4 bg-white/80 dark:bg-black/50 backdrop-blur rounded-full p-2 cursor-pointer hover:bg-white dark:hover:bg-black transition-colors product-zoom-btn">
        <span class="material-symbols-outlined text-gray-600 dark:text-gray-300">zoom_in</span>
    </button>
</div>

<!-- Thumbnails -->
<?php if (count($all_images) > 1): ?>
<div class="flex gap-4 overflow-x-auto pb-2 no-scrollbar product-thumbnails">
    <?php foreach ($all_images as $index => $image_id): ?>
        <?php
        $thumb_url = wp_get_attachment_image_url($image_id, 'woocommerce_gallery_thumbnail');
        $full_url = wp_get_attachment_image_url($image_id, 'woocommerce_single');
        $is_active = $index === 0 ? 'border-2 border-primary' : 'border border-transparent hover:border-gray-300 dark:hover:border-gray-600';
        ?>
        <button class="relative w-20 h-20 flex-shrink-0 rounded-lg <?php echo esc_attr($is_active); ?> overflow-hidden bg-white dark:bg-gray-800 product-thumbnail transition-colors" 
                data-image-id="<?php echo esc_attr($image_id); ?>"
                data-full-url="<?php echo esc_url($full_url); ?>">
            <div class="w-full h-full bg-center bg-cover" style='background-image: url("<?php echo esc_url($thumb_url); ?>")'></div>
        </button>
    <?php endforeach; ?>
    
    <!-- Video thumbnail placeholder (optional) -->
    <button class="relative w-20 h-20 flex-shrink-0 rounded-lg border border-transparent hover:border-gray-300 dark:hover:border-gray-600 overflow-hidden bg-white dark:bg-gray-800 flex items-center justify-center product-video-thumb" style="display: none;">
        <span class="material-symbols-outlined text-gray-400">play_circle</span>
    </button>
</div>
<?php endif; ?>