<?php
/**
 * Single Product Tabs
 *
 * @package Nang_Tho_Cosmetics
 */

defined('ABSPATH') || exit;

global $product;

$product_tabs = apply_filters('woocommerce_product_tabs', array());

if (empty($product_tabs)) {
    return;
}
?>

<div class="border-b border-gray-200 dark:border-gray-700 mb-6">
    <nav aria-label="Tabs" class="flex gap-8 overflow-x-auto pb-px product-tabs-nav">
        <?php
        $first_tab = true;
        foreach ($product_tabs as $key => $tab):
            $is_active = $first_tab ? 'border-b-2 border-primary text-primary font-bold' : 'border-b-2 border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 font-medium';
        ?>
            <button class="border-b-2 <?php echo esc_attr($is_active); ?> py-3 text-base whitespace-nowrap transition-colors product-tab-btn" 
                    data-tab="<?php echo esc_attr($key); ?>"
                    id="tab-title-<?php echo esc_attr($key); ?>"
                    role="tab"
                    aria-selected="<?php echo $first_tab ? 'true' : 'false'; ?>"
                    aria-controls="tab-<?php echo esc_attr($key); ?>">
                <?php echo esc_html(apply_filters('woocommerce_product_' . $key . '_tab_title', $tab['title'], $key)); ?>
            </button>
        <?php
            $first_tab = false;
        endforeach;
        ?>
    </nav>
</div>

<div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed product-tabs-content">
    <?php
    $first_tab = true;
    foreach ($product_tabs as $key => $tab):
        $is_active = $first_tab ? '' : 'hidden';
    ?>
        <div class="product-tab-panel <?php echo esc_attr($is_active); ?>" id="tab-<?php echo esc_attr($key); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr($key); ?>">
            <?php
            if (isset($tab['callback'])) {
                call_user_func($tab['callback'], $key, $tab);
            }
            ?>
        </div>
    <?php
        $first_tab = false;
    endforeach;
    ?>
</div>