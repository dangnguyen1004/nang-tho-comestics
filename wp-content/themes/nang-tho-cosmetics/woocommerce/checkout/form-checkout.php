<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

// If checkout registration is disabled and not logged in, the form isn't shown.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout"
    action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

    <?php if ($checkout->get_checkout_fields()): ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 relative">

            <!-- Left Column: Billing & Shipping & Payment -->
            <div class="lg:col-span-8 flex flex-col gap-8">

                <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                <div class="" id="customer_details">
                    <div class="mb-8">
                        <?php do_action('woocommerce_checkout_billing'); ?>
                    </div>

                    <div class="mb-8">
                        <?php do_action('woocommerce_checkout_shipping'); ?>
                    </div>
                </div>

                <?php do_action('woocommerce_checkout_after_customer_details'); ?>

                <!-- Moved Payment Section Here -->
                <div id="payment-section-custom"
                    class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-100 dark:border-gray-700">
                    <h3 id="payment_heading" class="text-xl font-bold mb-4">
                        <?php esc_html_e('Phương thức thanh toán', 'nang-tho-cosmetics'); ?>
                    </h3>
                    <?php woocommerce_checkout_payment(); ?>
                </div>

            </div>

            <!-- Right Column: Order Review (Sticky) -->
            <div class="lg:col-span-4">
                <div class="sticky top-24">
                    <div
                        class="bg-white dark:bg-[#2f1b25] rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <h3 id="order_review_heading" class="font-bold text-lg">
                                <?php esc_html_e('Đơn hàng', 'nang-tho-cosmetics'); ?>
                            </h3>
                        </div>

                        <div class="p-4">
                            <?php do_action('woocommerce_checkout_before_order_review'); ?>

                            <div id="order_review" class="woocommerce-checkout-review-order">
                                <?php
                                // Remove the default payment action from the order review
                                remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
                                woocommerce_order_review();
                                ?>
                            </div>

                            <?php do_action('woocommerce_checkout_after_order_review'); ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    <?php endif; ?>

</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>