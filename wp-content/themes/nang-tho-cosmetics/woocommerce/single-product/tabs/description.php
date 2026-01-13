<?php
/**
 * Single Product Description Tab
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

global $product;

$heading = apply_filters('woocommerce_product_description_heading', __('Mô tả sản phẩm', 'woocommerce'));

// Get product description
$description = get_the_content();
?>

<?php if ($description): ?>
    <p class="mb-4">
        <strong><?php echo esc_html($product->get_name()); ?></strong> 
        <?php echo wpautop(wp_kses_post($description)); ?>
    </p>

    <?php
    // Check for featured list items or custom highlights
    $highlighted_features = get_post_meta($product->get_id(), '_product_highlights', true);
    if ($highlighted_features):
        $features = explode("\n", $highlighted_features);
    ?>
        <h3 class="text-lg font-bold text-text-main dark:text-white mt-6 mb-3">Công dụng nổi bật:</h3>
        <ul class="list-disc pl-5 space-y-2">
            <?php foreach (array_filter($features) as $feature): ?>
                <li><?php echo esc_html(trim($feature)); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-100 dark:border-yellow-900/30">
        <p class="text-sm text-yellow-800 dark:text-yellow-200">
            <span class="font-bold">Lưu ý:</span> 
            Hiệu quả sản phẩm có thể khác nhau tùy thuộc vào cơ địa của mỗi người. Nên thử sản phẩm ở vùng da nhỏ trước khi sử dụng toàn mặt.
        </p>
    </div>
<?php else: ?>
    <p class="text-gray-500 dark:text-gray-400">Không có mô tả sản phẩm.</p>
<?php endif; ?>