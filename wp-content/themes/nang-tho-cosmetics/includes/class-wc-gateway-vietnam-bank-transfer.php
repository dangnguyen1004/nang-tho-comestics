<?php
/**
 * Vietnamese Bank Transfer Payment Gateway
 *
 * Provides a Bank Transfer Payment Gateway for Vietnamese customers.
 *
 * @class       WC_Gateway_Vietnam_Bank_Transfer
 * @extends     WC_Payment_Gateway
 * @package     Nang_Tho_Cosmetics
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Vietnamese Bank Transfer Payment Gateway
 */
class WC_Gateway_Vietnam_Bank_Transfer extends WC_Payment_Gateway
{
    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {
        $this->id = 'vietnam_bank_transfer';
        $this->icon = ''; // URL of the icon that will be displayed on checkout page
        $this->has_fields = false;
        $this->method_title = __('Chuyển khoản ngân hàng', 'nang-tho-cosmetics');
        $this->method_description = __('Thanh toán bằng cách chuyển khoản trực tiếp vào tài khoản ngân hàng của chúng tôi.', 'nang-tho-cosmetics');

        // Load the settings
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->instructions = $this->get_option('instructions');
        $this->bank_name = $this->get_option('bank_name');
        $this->account_name = $this->get_option('account_name');
        $this->account_number = $this->get_option('account_number');
        $this->bank_branch = $this->get_option('bank_branch');

        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));

        // Customer Emails
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
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
                'label' => __('Bật phương thức thanh toán chuyển khoản ngân hàng', 'nang-tho-cosmetics'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Tiêu đề', 'nang-tho-cosmetics'),
                'type' => 'text',
                'description' => __('Tiêu đề hiển thị cho khách hàng khi thanh toán.', 'nang-tho-cosmetics'),
                'default' => __('Chuyển khoản ngân hàng', 'nang-tho-cosmetics'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Mô tả', 'nang-tho-cosmetics'),
                'type' => 'textarea',
                'description' => __('Mô tả phương thức thanh toán hiển thị cho khách hàng.', 'nang-tho-cosmetics'),
                'default' => __('Vui lòng chuyển khoản trực tiếp vào tài khoản ngân hàng của chúng tôi. Đơn hàng sẽ được xử lý sau khi chúng tôi nhận được thanh toán.', 'nang-tho-cosmetics'),
                'desc_tip' => true,
            ),
            'instructions' => array(
                'title' => __('Hướng dẫn', 'nang-tho-cosmetics'),
                'type' => 'textarea',
                'description' => __('Hướng dẫn sẽ được thêm vào trang cảm ơn và email.', 'nang-tho-cosmetics'),
                'default' => __('Vui lòng chuyển khoản theo thông tin bên dưới. Đơn hàng của bạn sẽ được xử lý sau khi chúng tôi xác nhận thanh toán.', 'nang-tho-cosmetics'),
                'desc_tip' => true,
            ),
            'bank_details' => array(
                'title' => __('Thông tin tài khoản ngân hàng', 'nang-tho-cosmetics'),
                'type' => 'title',
                'description' => __('Nhập thông tin tài khoản ngân hàng của bạn bên dưới.', 'nang-tho-cosmetics'),
            ),
            'bank_name' => array(
                'title' => __('Tên ngân hàng', 'nang-tho-cosmetics'),
                'type' => 'text',
                'description' => __('Tên ngân hàng nhận tiền (VD: Vietcombank, Techcombank, VPBank).', 'nang-tho-cosmetics'),
                'default' => 'Ngân hàng TMCP Ngoại thương Việt Nam (Vietcombank)',
                'desc_tip' => true,
            ),
            'account_name' => array(
                'title' => __('Tên chủ tài khoản', 'nang-tho-cosmetics'),
                'type' => 'text',
                'description' => __('Tên chủ tài khoản ngân hàng.', 'nang-tho-cosmetics'),
                'default' => 'CÔNG TY TNHH NÀNG THƠ COSMETICS',
                'desc_tip' => true,
            ),
            'account_number' => array(
                'title' => __('Số tài khoản', 'nang-tho-cosmetics'),
                'type' => 'text',
                'description' => __('Số tài khoản ngân hàng.', 'nang-tho-cosmetics'),
                'default' => '1234567890',
                'desc_tip' => true,
            ),
            'bank_branch' => array(
                'title' => __('Chi nhánh', 'nang-tho-cosmetics'),
                'type' => 'text',
                'description' => __('Chi nhánh ngân hàng (tùy chọn).', 'nang-tho-cosmetics'),
                'default' => 'Chi nhánh Thành phố Hồ Chí Minh',
                'desc_tip' => true,
            ),
        );
    }

    /**
     * Output for the order received page.
     */
    public function thankyou_page($order_id)
    {
        if ($this->instructions) {
            echo '<div class="bank-transfer-instructions">';
            echo '<h2>' . esc_html__('Thông tin chuyển khoản', 'nang-tho-cosmetics') . '</h2>';
            echo wpautop(wptexturize($this->instructions));
            echo $this->get_bank_details_html($order_id);
            echo '</div>';
        }
    }

    /**
     * Add content to the WC emails.
     *
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($this->instructions && !$sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status('on-hold')) {
            echo wpautop(wptexturize($this->instructions)) . PHP_EOL;
            echo $this->get_bank_details_html($order->get_id(), $plain_text);
        }
    }

    /**
     * Get bank details HTML
     *
     * @param int $order_id
     * @param bool $plain_text
     * @return string
     */
    private function get_bank_details_html($order_id, $plain_text = false)
    {
        $order = wc_get_order($order_id);
        $order_total = $order ? $order->get_total() : '';
        $order_number = $order ? $order->get_order_number() : '';

        if ($plain_text) {
            $html = "\n" . str_repeat('-', 50) . "\n";
            $html .= __('THÔNG TIN CHUYỂN KHOẢN', 'nang-tho-cosmetics') . "\n";
            $html .= str_repeat('-', 50) . "\n\n";
            $html .= __('Ngân hàng:', 'nang-tho-cosmetics') . ' ' . $this->bank_name . "\n";
            $html .= __('Chủ tài khoản:', 'nang-tho-cosmetics') . ' ' . $this->account_name . "\n";
            $html .= __('Số tài khoản:', 'nang-tho-cosmetics') . ' ' . $this->account_number . "\n";
            if ($this->bank_branch) {
                $html .= __('Chi nhánh:', 'nang-tho-cosmetics') . ' ' . $this->bank_branch . "\n";
            }
            $html .= __('Số tiền:', 'nang-tho-cosmetics') . ' ' . wc_price($order_total) . "\n";
            $html .= __('Nội dung chuyển khoản:', 'nang-tho-cosmetics') . ' ' . sprintf(__('DH %s', 'nang-tho-cosmetics'), $order_number) . "\n";
            $html .= str_repeat('-', 50) . "\n";
            return $html;
        }

        ob_start();
        ?>
        <div class="bank-transfer-details">
            <table class="bank-info-table">
                <tbody>
                    <tr>
                        <th>
                            <?php esc_html_e('Ngân hàng:', 'nang-tho-cosmetics'); ?>
                        </th>
                        <td><strong>
                                <?php echo esc_html($this->bank_name); ?>
                            </strong></td>
                    </tr>
                    <tr>
                        <th>
                            <?php esc_html_e('Chủ tài khoản:', 'nang-tho-cosmetics'); ?>
                        </th>
                        <td><strong>
                                <?php echo esc_html($this->account_name); ?>
                            </strong></td>
                    </tr>
                    <tr>
                        <th>
                            <?php esc_html_e('Số tài khoản:', 'nang-tho-cosmetics'); ?>
                        </th>
                        <td><strong class="account-number">
                                <?php echo esc_html($this->account_number); ?>
                            </strong></td>
                    </tr>
                    <?php if ($this->bank_branch): ?>
                        <tr>
                            <th>
                                <?php esc_html_e('Chi nhánh:', 'nang-tho-cosmetics'); ?>
                            </th>
                            <td>
                                <?php echo esc_html($this->bank_branch); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th>
                            <?php esc_html_e('Số tiền:', 'nang-tho-cosmetics'); ?>
                        </th>
                        <td><strong class="amount">
                                <?php echo wc_price($order_total); ?>
                            </strong></td>
                    </tr>
                    <tr>
                        <th>
                            <?php esc_html_e('Nội dung chuyển khoản:', 'nang-tho-cosmetics'); ?>
                        </th>
                        <td><strong class="transfer-content">
                                <?php echo sprintf(esc_html__('DH %s', 'nang-tho-cosmetics'), $order_number); ?>
                            </strong></td>
                    </tr>
                </tbody>
            </table>
            <p class="bank-transfer-note">
                <em>
                    <?php esc_html_e('* Vui lòng nhập chính xác nội dung chuyển khoản để chúng tôi xác nhận thanh toán nhanh hơn.', 'nang-tho-cosmetics'); ?>
                </em>
            </p>
        </div>
        <?php
        return ob_get_clean();
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

        // Mark as on-hold (we're awaiting the payment)
        $order->update_status('on-hold', __('Đang chờ thanh toán chuyển khoản ngân hàng', 'nang-tho-cosmetics'));

        // Reduce stock levels
        wc_reduce_stock_levels($order_id);

        // Remove cart
        WC()->cart->empty_cart();

        // Return thankyou redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }
}
