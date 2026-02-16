<?php
/**
 * SePay Payment Gateway
 *
 * Provides SePay Payment Gateway integration for WooCommerce.
 * Supports card payments, bank transfers, and VietQR.
 *
 * @class       WC_Gateway_SePay
 * @extends     WC_Payment_Gateway
 * @package     Nang_Tho_Cosmetics
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Load SePay SDK
// Try multiple paths to find vendor/autoload.php
if (!class_exists('SePay\SePayClient')) {
    $autoload_paths = array(
        __DIR__ . '/../../../../vendor/autoload.php', // From theme directory
        ABSPATH . 'vendor/autoload.php', // WordPress root (Docker container)
        dirname(ABSPATH) . '/vendor/autoload.php', // One level up from WordPress root
    );

    $autoload_loaded = false;
    foreach ($autoload_paths as $path) {
        if (file_exists($path)) {
            try {
                require_once $path;
                $autoload_loaded = true;
                break;
            } catch (Exception $e) {
                // Continue to next path
                continue;
            }
        }
    }
    
    // If autoload failed, log error but don't prevent gateway from showing
    if (!$autoload_loaded && function_exists('error_log')) {
        error_log('SePay: Could not load vendor/autoload.php. Checked paths: ' . implode(', ', $autoload_paths));
    }
}

/**
 * SePay Payment Gateway
 */
class WC_Gateway_SePay extends WC_Payment_Gateway
{
    /**
     * SePay client instance
     *
     * @var SePayClient|null
     */
    private $sepay_client = null;

    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {
        $this->id = 'sepay';
        $this->icon = ''; // URL of the icon that will be displayed on checkout page
        $this->has_fields = false;
        $this->method_title = __('SePay', 'nang-tho-cosmetics');
        $this->method_description = __('Thanh toán qua SePay - Hỗ trợ thẻ tín dụng, chuyển khoản ngân hàng và VietQR.', 'nang-tho-cosmetics');
        $this->supports = array(
            'products'
        );

        // Load the settings
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->merchant_id = $this->get_option('merchant_id');
        $this->secret_key = $this->get_option('secret_key');
        $this->environment = $this->get_option('environment', 'sandbox');

        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_sepay_callback', array($this, 'handle_callback'));
        add_action('woocommerce_api_sepay_return', array($this, 'handle_return'));
    }

    /**
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Bật/Tắt', 'nang-tho-cosmetics'),
                'type' => 'checkbox',
                'label' => __('Bật phương thức thanh toán SePay', 'nang-tho-cosmetics'),
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Tiêu đề', 'nang-tho-cosmetics'),
                'type' => 'text',
                'description' => __('Tiêu đề hiển thị cho khách hàng khi thanh toán.', 'nang-tho-cosmetics'),
                'default' => __('SePay', 'nang-tho-cosmetics'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Mô tả', 'nang-tho-cosmetics'),
                'type' => 'textarea',
                'description' => __('Mô tả phương thức thanh toán hiển thị cho khách hàng.', 'nang-tho-cosmetics'),
                'default' => __('Thanh toán an toàn qua SePay - Hỗ trợ thẻ tín dụng, chuyển khoản ngân hàng và VietQR.', 'nang-tho-cosmetics'),
                'desc_tip' => true,
            ),
            'api_settings' => array(
                'title' => __('Cài đặt API SePay', 'nang-tho-cosmetics'),
                'type' => 'title',
                'description' => __('Nhập thông tin API từ tài khoản SePay của bạn.', 'nang-tho-cosmetics'),
            ),
            'merchant_id' => array(
                'title' => __('Merchant ID', 'nang-tho-cosmetics'),
                'type' => 'text',
                'description' => __('Merchant ID từ tài khoản SePay (VD: SP-LIVE-XXXXXXX hoặc SP-TEST-XXXXXXX).', 'nang-tho-cosmetics'),
                'default' => '',
                'desc_tip' => true,
                'required' => true,
            ),
            'secret_key' => array(
                'title' => __('Secret Key', 'nang-tho-cosmetics'),
                'type' => 'password',
                'description' => __('Secret Key từ tài khoản SePay (VD: spsk_live_xxxxxxxxxxx hoặc spsk_test_xxxxxxxxxxx).', 'nang-tho-cosmetics'),
                'default' => '',
                'desc_tip' => true,
                'required' => true,
            ),
            'environment' => array(
                'title' => __('Môi trường', 'nang-tho-cosmetics'),
                'type' => 'select',
                'description' => __('Chọn môi trường hoạt động. Sandbox để test, Production để sử dụng thực tế.', 'nang-tho-cosmetics'),
                'default' => 'sandbox',
                'options' => array(
                    'sandbox' => __('Sandbox (Test)', 'nang-tho-cosmetics'),
                    'production' => __('Production (Live)', 'nang-tho-cosmetics'),
                ),
                'desc_tip' => true,
            ),
        );
    }

    /**
     * Get SePay client instance
     *
     * @return SePayClient|null
     */
    private function get_sepay_client()
    {
        if (!class_exists('SePay\SePayClient')) {
            return null;
        }

        if ($this->sepay_client === null) {
            $environment = $this->environment === 'production' 
                ? \SePay\SePayClient::ENVIRONMENT_PRODUCTION 
                : \SePay\SePayClient::ENVIRONMENT_SANDBOX;

            $this->sepay_client = new \SePay\SePayClient(
                $this->merchant_id,
                $this->secret_key,
                $environment
            );
        }

        return $this->sepay_client;
    }

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            wc_add_notice(__('Đơn hàng không hợp lệ.', 'nang-tho-cosmetics'), 'error');
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        }

        try {
            // Get order data
            $order_total = $order->get_total();
            $order_number = $order->get_order_number();
            $order_description = sprintf(
                __('Thanh toán đơn hàng #%s', 'nang-tho-cosmetics'),
                $order_number
            );

            // Check if SePay SDK is loaded
            if (!class_exists('SePay\SePayClient') || !class_exists('SePay\Builders\CheckoutBuilder')) {
                wc_add_notice(__('SePay SDK chưa được cài đặt. Vui lòng liên hệ quản trị viên.', 'nang-tho-cosmetics'), 'error');
                return array(
                    'result' => 'fail',
                    'redirect' => ''
                );
            }

            // Build checkout data
            $checkout_data = \SePay\Builders\CheckoutBuilder::make()
                ->currency('VND')
                ->orderAmount((int) round($order_total)) // SePay expects amount in VND (integer)
                ->operation('PURCHASE')
                ->orderDescription($order_description)
                ->orderInvoiceNumber($order_number)
                ->customerId((string) ($order->get_customer_id() ?: $order->get_billing_email()))
                ->successUrl(add_query_arg(
                    array(
                        'wc-api' => 'sepay_return',
                        'order_id' => $order_id,
                        'status' => 'success'
                    ),
                    home_url('/', is_ssl() ? 'https' : 'http')
                ))
                ->errorUrl(add_query_arg(
                    array(
                        'wc-api' => 'sepay_return',
                        'order_id' => $order_id,
                        'status' => 'error'
                    ),
                    home_url('/', is_ssl() ? 'https' : 'http')
                ))
                ->cancelUrl(add_query_arg(
                    array(
                        'wc-api' => 'sepay_return',
                        'order_id' => $order_id,
                        'status' => 'cancel'
                    ),
                    home_url('/', is_ssl() ? 'https' : 'http')
                ))
                ->build();

            // Get SePay client
            $sepay = $this->get_sepay_client();

            // Generate form fields
            $form_fields = $sepay->checkout()->generateFormFields($checkout_data);

            // Store order ID and form fields in session for callback verification
            WC()->session->set('sepay_order_id', $order_id);
            WC()->session->set('sepay_form_fields', $form_fields);

            // Mark order as pending payment
            $order->update_status('pending', __('Đang chờ thanh toán qua SePay', 'nang-tho-cosmetics'));

            // Return redirect URL for form submission
            return array(
                'result' => 'success',
                'redirect' => $this->get_sepay_checkout_url()
            );

        } catch (\SePay\Exceptions\ValidationException $e) {
            wc_add_notice(__('Lỗi xác thực: ', 'nang-tho-cosmetics') . $e->getMessage(), 'error');
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        } catch (\SePay\Exceptions\AuthenticationException $e) {
            wc_add_notice(__('Lỗi xác thực API: ', 'nang-tho-cosmetics') . $e->getMessage(), 'error');
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        } catch (\Exception $e) {
            wc_add_notice(__('Lỗi khi xử lý thanh toán: ', 'nang-tho-cosmetics') . $e->getMessage(), 'error');
            return array(
                'result' => 'fail',
                'redirect' => ''
            );
        }
    }

    /**
     * Get SePay checkout URL for form submission
     *
     * @return string
     */
    private function get_sepay_checkout_url()
    {
        // Create a temporary page to submit form
        return add_query_arg(
            array(
                'wc-api' => 'sepay_checkout',
            ),
            home_url('/')
        );
    }

    /**
     * Handle SePay callback (IPN)
     */
    public function handle_callback()
    {
        // Get order ID from POST data (SePay sends it in callback)
        $order_invoice = isset($_POST['order_invoice_number']) ? sanitize_text_field($_POST['order_invoice_number']) : '';
        
        if (empty($order_invoice)) {
            status_header(400);
            exit;
        }

        // Find order by invoice number
        $orders = wc_get_orders(array(
            'limit' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_key' => '_order_number',
            'meta_value' => $order_invoice,
        ));

        if (empty($orders)) {
            // Try to find by order number directly
            $order = wc_get_order($order_invoice);
        } else {
            $order = $orders[0];
        }

        if (!$order) {
            status_header(404);
            exit;
        }

        // Verify callback signature
        // Note: SePay will send callback data via POST
        // You should verify the signature here based on SePay documentation
        // For now, we'll trust the callback (in production, always verify signature)

        // Update order status based on payment result
        $status = isset($_POST['order_status']) ? sanitize_text_field($_POST['order_status']) : '';
        
        if ($status === 'CAPTURED' || $status === 'COMPLETED') {
            $order->payment_complete();
            $order->add_order_note(__('Thanh toán SePay thành công (Callback)', 'nang-tho-cosmetics'));
            
            // Clear session
            WC()->session->__unset('sepay_order_id');
            WC()->session->__unset('sepay_form_fields');
        } elseif ($status === 'FAILED' || $status === 'CANCELLED') {
            $order->update_status('failed', __('Thanh toán SePay thất bại (Callback)', 'nang-tho-cosmetics'));
        }

        status_header(200);
        echo 'OK';
        exit;
    }

    /**
     * Handle SePay return (customer redirect)
     */
    public function handle_return()
    {
        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        
        $order = wc_get_order($order_id);

        if (!$order) {
            wc_add_notice(__('Đơn hàng không tồn tại.', 'nang-tho-cosmetics'), 'error');
            wp_safe_redirect(wc_get_page_permalink('shop'));
            exit;
        }

        // Check payment status
        if ($status === 'success') {
            // Payment successful - redirect to thank you page
            wp_safe_redirect($this->get_return_url($order));
        } else {
            // Payment failed or cancelled
            if ($status === 'cancel') {
                wc_add_notice(__('Bạn đã hủy thanh toán.', 'nang-tho-cosmetics'), 'notice');
            } else {
                wc_add_notice(__('Thanh toán không thành công. Vui lòng thử lại.', 'nang-tho-cosmetics'), 'error');
            }
            wp_safe_redirect($order->get_cancel_order_url_raw());
        }
        exit;
    }

    /**
     * Check if this gateway is available
     *
     * @return bool
     */
    public function is_available()
    {
        // Always show in admin, even if not configured
        if (is_admin()) {
            return true;
        }

        if (!parent::is_available()) {
            return false;
        }

        // Check if merchant ID and secret key are set
        if (empty($this->merchant_id) || empty($this->secret_key)) {
            return false;
        }

        // Check if SePay SDK is loaded
        if (!class_exists('SePay\SePayClient')) {
            return false;
        }

        return true;
    }
}

/**
 * Handle SePay checkout form submission
 */
add_action('woocommerce_api_sepay_checkout', 'nang_tho_sepay_checkout_handler');
function nang_tho_sepay_checkout_handler()
{
    $form_fields = WC()->session->get('sepay_form_fields');

    if (!$form_fields) {
        wp_die(__('Lỗi: Không tìm thấy thông tin thanh toán.', 'nang-tho-cosmetics'));
    }

    // Get SePay checkout URL from form fields or use default
    // SePay SDK should provide checkout_url in form_fields
    $checkout_url = 'https://checkout.sepay.vn'; // Default SePay checkout URL
    
    // Try to get from form fields if available
    if (isset($form_fields['checkout_url'])) {
        $checkout_url = $form_fields['checkout_url'];
    } elseif (isset($form_fields['action'])) {
        $checkout_url = $form_fields['action'];
    }

    // Output auto-submit form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php esc_html_e('Đang chuyển đến SePay...', 'nang-tho-cosmetics'); ?></title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                text-align: center;
                padding: 50px 20px;
                background: #f5f5f5;
            }
            .container {
                max-width: 500px;
                margin: 0 auto;
                background: white;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h2 {
                color: #333;
                margin-bottom: 20px;
            }
            .loading {
                margin: 20px 0;
                color: #666;
            }
            .spinner {
                border: 3px solid #f3f3f3;
                border-top: 3px solid #3498db;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: spin 1s linear infinite;
                margin: 20px auto;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2><?php esc_html_e('Đang chuyển đến trang thanh toán SePay...', 'nang-tho-cosmetics'); ?></h2>
            <div class="spinner"></div>
            <div class="loading"><?php esc_html_e('Vui lòng đợi...', 'nang-tho-cosmetics'); ?></div>
        </div>
        <form id="sepay-form" method="POST" action="<?php echo esc_url($checkout_url); ?>">
            <?php foreach ($form_fields as $key => $value): ?>
                <?php if ($key !== 'checkout_url' && $key !== 'action' && is_scalar($value)): ?>
                    <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
                <?php endif; ?>
            <?php endforeach; ?>
        </form>
        <script>
            // Auto-submit form after page load
            window.onload = function() {
                document.getElementById('sepay-form').submit();
            };
        </script>
    </body>
    </html>
    <?php
    exit;
}
