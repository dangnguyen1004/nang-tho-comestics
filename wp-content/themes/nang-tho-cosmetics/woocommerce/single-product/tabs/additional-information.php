<?php
/**
 * Single Product Additional Information Tab (Thành phần)
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

global $product;

$heading = apply_filters('woocommerce_product_additional_information_heading', __('Thành phần', 'nang-tho-cosmetics'));
?>

<?php if ($heading): ?>
    <h3 class="text-lg font-bold text-text-main dark:text-white mt-6 mb-3"><?php echo esc_html($heading); ?></h3>
<?php endif; ?>

<div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">
    <?php
    // Display product attributes (Thành phần)
    $attributes = $product->get_attributes();
    if (!empty($attributes)):
        foreach ($attributes as $attribute):
            if ($attribute->get_variation()) {
                continue;
            }
            
            $name = $attribute->get_name();
            $values = wc_get_product_terms($product->get_id(), $name, array('fields' => 'names'));
            
            if (!empty($values)):
                ?>
                <div class="mb-4">
                    <strong><?php echo esc_html(wc_attribute_label($name)); ?>:</strong>
                    <p class="mt-1"><?php echo esc_html(implode(', ', $values)); ?></p>
                </div>
                <?php
            endif;
        endforeach;
    else:
        // Try to get ingredients from product description or meta
        $ingredients = get_post_meta($product->get_id(), '_product_ingredients', true);
        if ($ingredients) {
            echo wpautop(wp_kses_post($ingredients));
        } else {
            do_action('woocommerce_product_additional_information', $product);
        }
    endif;
    ?>
</div>