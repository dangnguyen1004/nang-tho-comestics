<?php
/**
 * Nang Tho Cosmetics functions and definitions
 *
 * @package Nang_Tho_Cosmetics
 */

if (!defined('_S_VERSION')) {
    // Replace the version number of the theme on each release.
    define('_S_VERSION', '1.0.1');
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function nang_tho_cosmetics_setup()
{
    // Add default posts and comments RSS feed links to head.
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title.
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support('post-thumbnails');

    // This theme uses wp_nav_menu() in one location.
    register_nav_menus(
        array(
            'primary' => esc_html__('Primary', 'nang-tho-cosmetics'),
        )
    );

    // Switch default core markup for search form, comment form, and comments to output valid HTML5.
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );

    // Add support for WooCommerce
    add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'nang_tho_cosmetics_setup');

/**
 * Custom Navigation Walker for Primary Menu
 * Outputs menu items with Tailwind CSS classes matching the original design
 */
class Nang_Tho_Nav_Walker extends Walker_Nav_Menu
{
    /**
     * Start the list before the elements are added.
     */
    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu\">\n";
    }

    /**
     * End the list of after the elements are added.
     */
    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    /**
     * Start the element output.
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        // Determine if this is the current menu item
        $is_current = in_array('current-menu-item', $classes) || in_array('current-page-ancestor', $classes);

        // Base classes for all menu items
        $link_classes = 'hover:text-primary transition-colors border-b-2';

        // Active state: border-primary, inactive: border-transparent hover:border-primary/50
        if ($is_current) {
            $link_classes .= ' border-primary';
        } else {
            $link_classes .= ' border-transparent hover:border-primary/50';
        }

        // Check for custom CSS classes added in admin (for special styling like "Khuyến mãi")
        // WordPress stores custom classes in $item->classes array
        $custom_classes = array();
        foreach ($classes as $class) {
            // Skip WordPress default classes, keep only custom ones
            if (
                $class !== 'menu-item' &&
                $class !== 'menu-item-type-post_type' &&
                $class !== 'menu-item-object-page' &&
                $class !== 'menu-item-home' &&
                strpos($class, 'menu-item-') !== 0 &&
                strpos($class, 'current') === false &&
                (!isset($item->post_name) || $class !== $item->post_name)
            ) {
                $custom_classes[] = $class;
            }
        }

        // Add custom classes to link if they exist
        if (!empty($custom_classes)) {
            $link_classes .= ' ' . implode(' ', array_map('esc_attr', $custom_classes));
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names . '>';

        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        $attributes .= ' class="' . esc_attr(trim($link_classes)) . '"';

        $item_output = isset($args->before) ? $args->before : '';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= (isset($args->link_before) ? $args->link_before : '') . apply_filters('the_title', $item->title, $item->ID) . (isset($args->link_after) ? $args->link_after : '');
        $item_output .= '</a>';
        $item_output .= isset($args->after) ? $args->after : '';

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    /**
     * End the element output.
     */
    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= "</li>\n";
    }
}

/**
 * Custom Navigation Walker for Mobile Menu
 * Outputs menu items with mobile-friendly styling
 */
class Nang_Tho_Nav_Walker_Mobile extends Walker_Nav_Menu
{
    /**
     * Start the list before the elements are added.
     */
    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"pl-4 mt-2 space-y-1\">\n";
    }

    /**
     * End the list of after the elements are added.
     */
    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    /**
     * Start the element output.
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        // Determine if this is the current menu item
        $is_current = in_array('current-menu-item', $classes) || in_array('current-page-ancestor', $classes);

        // Base classes for mobile menu items
        $link_classes = 'px-4 py-2 text-sm font-medium text-text-dark dark:text-gray-200 hover:text-primary hover:bg-background-light dark:hover:bg-white/5 transition-colors block';

        // Active state styling
        if ($is_current) {
            $link_classes .= ' text-primary font-bold';
        }

        // Check for custom CSS classes added in admin
        $custom_classes = array();
        foreach ($classes as $class) {
            if (
                $class !== 'menu-item' &&
                $class !== 'menu-item-type-post_type' &&
                $class !== 'menu-item-object-page' &&
                $class !== 'menu-item-home' &&
                strpos($class, 'menu-item-') !== 0 &&
                strpos($class, 'current') === false &&
                (!isset($item->post_name) || $class !== $item->post_name)
            ) {
                $custom_classes[] = $class;
            }
        }

        // Add custom classes to link if they exist
        if (!empty($custom_classes)) {
            $link_classes .= ' ' . implode(' ', array_map('esc_attr', $custom_classes));
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names . '>';

        $attributes = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';
        $attributes .= ' class="' . esc_attr(trim($link_classes)) . '"';

        $item_output = isset($args->before) ? $args->before : '';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= (isset($args->link_before) ? $args->link_before : '') . apply_filters('the_title', $item->title, $item->ID) . (isset($args->link_after) ? $args->link_after : '');
        $item_output .= '</a>';
        $item_output .= isset($args->after) ? $args->after : '';

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    /**
     * End the element output.
     */
    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= "</li>\n";
    }
}

/**
 * Include Custom Payment Gateway
 */
function nang_tho_include_payment_gateway()
{
    if (class_exists('WC_Payment_Gateway')) {
        require_once get_template_directory() . '/includes/class-wc-gateway-vietnam-bank-transfer.php';
        require_once get_template_directory() . '/includes/class-wc-gateway-sepay.php';
    }
}
add_action('plugins_loaded', 'nang_tho_include_payment_gateway');

/**
 * Register widget area.
 */
function nang_tho_cosmetics_widgets_init()
{
    register_sidebar(
        array(
            'name' => esc_html__('Shop Sidebar', 'nang-tho-cosmetics'),
            'id' => 'shop-sidebar',
            'description' => esc_html__('Add widgets here.', 'nang-tho-cosmetics'),
            'before_widget' => '<section id="%1$s" class="widget %2$s mb-8">',
            'after_widget' => '</section>',
            'before_title' => '<h2 class="widget-title font-bold text-text-dark dark:text-white mb-4">',
            'after_title' => '</h2>',
        )
    );
}
add_action('widgets_init', 'nang_tho_cosmetics_widgets_init');

/**
 * Customize WooCommerce Checkout Fields
 */
add_filter('woocommerce_checkout_fields', 'nang_tho_custom_checkout_fields', 9999);
function nang_tho_custom_checkout_fields($fields)
{
    // 1. Remove unwanted fields
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_postcode']);
    // unset($fields['billing']['billing_country']); // Keep country for WC logic, hide with CSS if needed

    // 2. Customize standard fields

    // Country - Hide it but keep it for logic
    $fields['billing']['billing_country']['class'] = array('hidden');
    $fields['billing']['billing_country']['label_class'] = array('hidden');

    // Full Name
    $fields['billing']['billing_first_name']['label'] = 'Họ và tên';
    $fields['billing']['billing_first_name']['placeholder'] = 'Nhập họ tên của bạn';
    $fields['billing']['billing_first_name']['class'] = array('form-row-first', 'nangtho-one-half');
    $fields['billing']['billing_first_name']['priority'] = 10;

    // Phone
    $fields['billing']['billing_phone']['label'] = 'Số điện thoại';
    $fields['billing']['billing_phone']['placeholder'] = 'Ví dụ: 0912345678';
    $fields['billing']['billing_phone']['class'] = array('form-row-last', 'nangtho-one-half');
    $fields['billing']['billing_phone']['priority'] = 20;

    // Email
    $fields['billing']['billing_email']['label'] = 'Email';
    $fields['billing']['billing_email']['placeholder'] = 'example@email.com';
    $fields['billing']['billing_email']['class'] = array('form-row-wide', 'clear');
    $fields['billing']['billing_email']['priority'] = 30;

    // Address 1 - Moved below location fields
    $fields['billing']['billing_address_1']['label'] = 'Địa chỉ cụ thể';
    $fields['billing']['billing_address_1']['placeholder'] = 'Càng chi tiết càng tốt';
    $fields['billing']['billing_address_1']['class'] = array('form-row-wide', 'address-field', 'clear');
    $fields['billing']['billing_address_1']['priority'] = 75;

    // State, City, Address 2 - Configured via Locale Filter naturally!
    // We just set labels and priority here for cleanup.
    $fields['billing']['billing_state']['label'] = 'Tỉnh / Thành phố';
    $fields['billing']['billing_state']['required'] = true;
    $fields['billing']['billing_state']['class'] = array('nangtho-one-third', 'nangtho-first', 'clear');
    $fields['billing']['billing_state']['priority'] = 50;

    $fields['billing']['billing_city']['label'] = 'Quận / Huyện';
    $fields['billing']['billing_city']['type'] = 'select'; // Explicitly force Select
    $fields['billing']['billing_city']['class'] = array('nangtho-one-third', 'nangtho-center');
    $fields['billing']['billing_city']['placeholder'] = 'Chọn Quận/Huyện';
    $fields['billing']['billing_city']['options'] = array('' => 'Chọn Quận/Huyện'); // Default options to ensure render
    $fields['billing']['billing_city']['priority'] = 60;

    $fields['billing']['billing_address_2']['label'] = 'Phường / Xã';
    $fields['billing']['billing_address_2']['label_class'] = array(''); // Prevent screen-reader-text class
    $fields['billing']['billing_address_2']['required'] = true;
    $fields['billing']['billing_address_2']['type'] = 'select'; // Explicitly force Select
    $fields['billing']['billing_address_2']['class'] = array('nangtho-one-third', 'nangtho-last');
    $fields['billing']['billing_address_2']['placeholder'] = 'Chọn Phường/Xã';
    $fields['billing']['billing_address_2']['options'] = array('' => 'Chọn Phường/Xã'); // Default options to ensure render
    $fields['billing']['billing_address_2']['priority'] = 70;

    // Order notes
    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['label'] = 'Ghi chú thêm';
    }

    return $fields;
}

/**
 * Disable "Ship to different address" option
 */
add_filter('woocommerce_cart_needs_shipping_address', '__return_false');

/**
 * Valid Vietnam Provinces (Native WC Support)
 */
add_filter('woocommerce_states', 'nang_tho_custom_woocommerce_states');
function nang_tho_custom_woocommerce_states($states)
{
    $states['VN'] = array(
        'Hồ Chí Minh' => 'Hồ Chí Minh',
        'Hà Nội' => 'Hà Nội',
        'Đà Nẵng' => 'Đà Nẵng',
        // Add more provinces here
    );
    return $states;
}

/**
 * Force City and Address 2 to be Select Fields for Vietnam (Native WC Support)
 */
add_filter('woocommerce_get_country_locale', 'nang_tho_custom_country_locale');
function nang_tho_custom_country_locale($locale)
{
    $locale['VN']['city'] = array(
        'type' => 'select',
        'required' => true, // Ensure required
        'label' => 'Quận / Huyện',
        'priority' => 60,
        'class' => array('nangtho-one-third', 'nangtho-center'), // Add class here too just in case
        'options' => array('' => 'Chọn Quận/Huyện')
    );

    $locale['VN']['address_2'] = array(
        'type' => 'select',
        'required' => true,
        'label' => 'Phường / Xã',
        'priority' => 70,
        'class' => array('nangtho-one-third', 'nangtho-last'), // Add class here too
        'options' => array('' => 'Chọn Phường/Xã')
    );

    // Address 1 - Ensure it comes after location fields
    $locale['VN']['address_1'] = array(
        'label' => 'Địa chỉ cụ thể',
        'placeholder' => 'Càng chi tiết càng tốt',
        'priority' => 75,
        'class' => array('form-row-wide', 'address-field', 'clear')
    );

    $locale['VN']['state']['label'] = 'Tỉnh / Thành phố'; // Ensure label is correct
    $locale['VN']['state']['required'] = true;
    $locale['VN']['state']['priority'] = 50;
    $locale['VN']['state']['class'] = array('nangtho-one-third', 'nangtho-first', 'clear');

    return $locale;
}


/**
 * Enqueue scripts and styles.
 */
function nang_tho_cosmetics_scripts()
{
    wp_enqueue_style('nang-tho-cosmetics-style', get_stylesheet_uri(), array(), _S_VERSION);

    // Enqueue Vietnam Address Management Script
    wp_enqueue_script('nang-tho-vietnam-checkout', get_template_directory_uri() . '/assets/js/checkout-vietnam.js', array('jquery'), _S_VERSION, true);

    // Address Data - Passed from PHP to JS
    $vietnam_data = array(
        "Hồ Chí Minh" => array(
            "Quận 1" => array("Phường Bến Nghé", "Phường Bến Thành", "Phường Cô Giang", "Phường Cầu Kho", "Phường Cầu Ông Lãnh", "Phường Đa Kao", "Phường Nguyễn Cư Trinh", "Phường Nguyễn Thái Bình", "Phường Phạm Ngũ Lão", "Phường Tân Định"),
            "Quận 3" => array("Phường 01", "Phường 02", "Phường 03", "Phường 04", "Phường 05", "Phường 09", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường Võ Thị Sáu"),
            "Quận 7" => array("Phường Tân Thuận Đông", "Phường Tân Thuận Tây", "Phường Tân Kiểng", "Phường Tân Hưng", "Phường Bình Thuận", "Phường Tân Quy", "Phường Phú Thuận", "Phường Tân Phú", "Phường Tân Phong", "Phường Phú Mỹ"),
            "Thành phố Thủ Đức" => array("Phường An Khánh", "Phường An Lợi Đông", "Phường An Phú", "Phường Bình Chiểu", "Phường Bình Thọ", "Phường Bình Trưng Đông", "Phường Bình Trưng Tây", "Phường Cát Lái", "Phường Hiệp Bình Chánh", "Phường Hiệp Bình Phước")
        ),
        "Hà Nội" => array(
            "Quận Ba Đình" => array("Phường Phúc Xá", "Phường Trúc Bạch", "Phường Vĩnh Phúc", "Phường Cống Vị", "Phường Liễu Giai", "Phường Nguyễn Trung Trực", "Phường Quán Thánh", "Phường Ngọc Hà", "Phường Điện Biên", "Phường Đội Cấn", "Phường Ngọc Khánh", "Phường Kim Mã", "Phường Giảng Võ", "Phường Thành Công"),
            "Quận Hoàn Kiếm" => array("Phường Phúc Tân", "Phường Đồng Xuân", "Phường Hàng Mã", "Phường Hàng Buồm", "Phường Hàng Đào", "Phường Hàng Bồ", "Phường Cửa Đông", "Phường Lý Thái Tổ", "Phường Hàng Bạc", "Phường Hàng Gai", "Phường Chương Dương", "Phường Hàng Trống", "Phường Cửa Nam", "Phường Hàng Bông", "Phường Tràng Tiền", "Phường Trần Hưng Đạo", "Phường Phan Chu Trinh", "Phường Hàng Bài"),
            "Quận Đống Đa" => array("Phường Văn Miếu", "Phường Quốc Tử Giám", "Phường Hàng Bột", "Phường Nam Đồng", "Phường Trung Liệt", "Phường Khâm Thiên", "Phường Thổ Quan", "Phường Phương Liên", "Phường Quang Trung", "Phường Trung Phụng", "Phường Trung Tự", "Phường Kim Liên", "Phường Phương Mai", "Phường Khương Thượng", "Phường Ngã Tư Sở", "Phường Láng Thượng", "Phường Cát Linh", "Phường Văn Chương", "Phường Ô Chợ Dừa", "Phường Hàng Bột", "Phường Láng Hạ")
        ),
        "Đà Nẵng" => array(
            "Quận Hải Châu" => array("Phường Hải Châu I", "Phường Hải Châu II", "Phường Thạch Thang", "Phường Thanh Bình", "Phường Thuận Phước", "Phường Hòa Thuận Đông", "Phường Hòa Thuận Tây", "Phường Nam Dương", "Phường Phước Ninh", "Phường Bình Thuận", "Phường Bình Hiên", "Phường Hòa Cường Bắc", "Phường Hòa Cường Nam"),
            "Quận Thanh Khê" => array("Phường Tam Thuận", "Phường Thanh Khê Tây", "Phường Thanh Khê Đông", "Phường Xuân Hà", "Phường Tân Chính", "Phường Chính Gián", "Phường Vĩnh Trung", "Phường Thạc Gián", "Phường An Khê", "Phường Hòa Khê")
        )
    );

    wp_localize_script('nang-tho-vietnam-checkout', 'nang_tho_data', $vietnam_data);

    // WooCommerce-specific scripts (only load when WooCommerce is active)
    if (class_exists('WooCommerce')) {
        // Enqueue Payment Gateway CSS
        if (is_checkout() || is_order_received_page()) {
            wp_enqueue_style('nang-tho-payment-gateway', get_template_directory_uri() . '/assets/css/payment-gateway.css', array(), _S_VERSION);
        }

        // Enqueue Shop Filters JavaScript
        if (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()) {
            wp_enqueue_script('nang-tho-shop-filters', get_template_directory_uri() . '/assets/js/shop-filters.js', array('jquery'), _S_VERSION, true);

            // Localize script with shop URL
            wp_localize_script('nang-tho-shop-filters', 'wc_shop_params', array(
                'shop_url' => wc_get_page_permalink('shop'),
            ));
        }

        // Enqueue Product Detail JavaScript
        if (is_product()) {
            wp_enqueue_script('nang-tho-product-detail', get_template_directory_uri() . '/assets/js/product-detail.js', array('jquery'), _S_VERSION, true);

            // Get WooCommerce AJAX endpoint
            $ajax_url = '';
            if (class_exists('WC_AJAX')) {
                $ajax_url = WC_AJAX::get_endpoint('add_to_cart');
            } else {
                $ajax_url = home_url('/?wc-ajax=add_to_cart');
            }

            // Localize script with WooCommerce params
            wp_localize_script('nang-tho-product-detail', 'wc_add_to_cart_params', array(
                'ajax_url' => $ajax_url,
                'wc_ajax_url' => home_url('/?wc-ajax=%%endpoint%%'),
                'checkout_url' => esc_url_raw(wc_get_checkout_url()),
                'cart_url' => esc_url_raw(wc_get_cart_url()),
                'i18n_view_cart' => esc_attr__('View cart', 'woocommerce'),
            ));
        }
    }
}
add_action('wp_enqueue_scripts', 'nang_tho_cosmetics_scripts');

/**
 * Add custom payment gateway to WooCommerce
 */
function nang_tho_add_payment_gateway($gateways)
{
    $gateways[] = 'WC_Gateway_Vietnam_Bank_Transfer';
    $gateways[] = 'WC_Gateway_SePay';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'nang_tho_add_payment_gateway');

/**
 * Translate WooCommerce Strings
 */
add_filter('gettext', 'nang_tho_translate_woocommerce_strings', 999, 3);
function nang_tho_translate_woocommerce_strings($translated, $text, $domain)
{
    // Translate "Search results for" regardless of domain
    if (strpos($text, 'Search results for') !== false) {
        $translated = str_replace('Search results for', 'Kết quả tìm kiếm cho', $translated);
    }
    
    // Translate comment form strings regardless of domain
    $comment_strings = array(
        'Leave a Reply' => 'Để lại bình luận',
        'Logged in as' => 'Đăng nhập với tên',
        'Edit your profile' => 'Chỉnh sửa hồ sơ',
        'Log out?' => 'Đăng xuất?',
        'Log out' => 'Đăng xuất',
        'Required fields are marked *' => 'Các trường bắt buộc được đánh dấu *',
        'Required fields are marked' => 'Các trường bắt buộc được đánh dấu',
    );
    
    if (isset($comment_strings[$text])) {
        $translated = $comment_strings[$text];
    }
    
    // Handle combined strings with placeholders
    if (strpos($text, 'Logged in as') !== false && strpos($text, 'Edit your profile') !== false) {
        // Pattern: "Logged in as %1$s. %2$s" where %2$s contains "Edit your profile. Log out?"
        $translated = preg_replace('/Logged in as (.+?)\. (.+?)\. Log out\?/', 'Đăng nhập với tên $1. $2. Đăng xuất?', $text);
        if ($translated === $text) {
            // Fallback if regex doesn't match
            $translated = str_replace('Logged in as', 'Đăng nhập với tên', $text);
            $translated = str_replace('Edit your profile', 'Chỉnh sửa hồ sơ', $translated);
            $translated = str_replace('Log out?', 'Đăng xuất?', $translated);
        }
    }
    
    if ($domain === 'woocommerce') {
        switch ($text) {
            case 'Billing details':
                $translated = 'Thông tin thanh toán';
                break;
            case 'Your order':
                $translated = 'Đơn hàng của bạn';
                break;
            case 'Product':
                $translated = 'Sản phẩm';
                break;
            case 'Subtotal':
                $translated = 'Tạm tính';
                break;
            case 'Total':
                $translated = 'Tổng cộng';
                break;
            case 'Place order':
                $translated = 'Đặt hàng';
                break;
            case 'Have a coupon?':
                $translated = 'Bạn có mã ưu đãi?';
                break;
            case 'Click here to enter your code':
                $translated = 'Ấn vào đây để nhập mã';
                break;
            case 'If you have a coupon code, please apply it below.':
                $translated = 'Nếu bạn có mã giảm giá, vui lòng điền vào bên dưới.';
                break;
            case 'Apply coupon':
                $translated = 'Áp dụng';
                break;
            case 'Coupon code':
                $translated = 'Mã ưu đãi';
                break;
            case 'Payment method':
                $translated = 'Phương thức thanh toán';
                break;
            case 'Cash on delivery':
                $translated = 'Thanh toán khi nhận hàng';
                break;
            case 'Direct bank transfer':
                $translated = 'Chuyển khoản ngân hàng';
                break;
            case 'Thank you. Your order has been received.':
                $translated = 'Cảm ơn bạn. Đơn hàng của bạn đã được tiếp nhận.';
                break;
            case 'Order number:':
                $translated = 'Mã đơn hàng:';
                break;
            case 'Date:':
                $translated = 'Ngày:';
                break;
            case 'Email:':
                $translated = 'Email:';
                break;
            case 'Payment method:':
                $translated = 'Phương thức thanh toán:';
                break;
            case 'Search results for':
                $translated = 'Kết quả tìm kiếm cho';
                break;
            case 'Search results for:':
                $translated = 'Kết quả tìm kiếm cho:';
                break;
            case 'Username or email address':
                $translated = 'Tên đăng nhập hoặc email';
                break;
            case 'Password':
                $translated = 'Mật khẩu';
                break;
            case 'Remember me':
                $translated = 'Ghi nhớ đăng nhập';
                break;
            case 'Log in':
                $translated = 'Đăng nhập';
                break;
            case 'Lost your password?':
                $translated = 'Quên mật khẩu?';
                break;
            case 'Required':
                $translated = 'Bắt buộc';
                break;
            // Add more translations as needed
        }
    }
    
    // Translate login form strings regardless of domain
    $login_strings = array(
        'Username or email address' => 'Tên đăng nhập hoặc email',
        'Password' => 'Mật khẩu',
        'Remember me' => 'Ghi nhớ đăng nhập',
        'Log in' => 'Đăng nhập',
        'Lost your password?' => 'Quên mật khẩu?',
        'Required' => 'Bắt buộc',
    );
    
    if (isset($login_strings[$text])) {
        $translated = $login_strings[$text];
    }
    
    return $translated;
}

/**
 * Customize comment form text
 */
add_filter('comment_form_defaults', 'nang_tho_custom_comment_form_text');
function nang_tho_custom_comment_form_text($defaults)
{
    $user = wp_get_current_user();
    if ($user->exists()) {
        $user_identity = $user->display_name ? $user->display_name : $user->user_login;
        $logout_url = wp_logout_url(apply_filters('the_permalink', get_permalink()));
        $profile_url = get_edit_user_link($user->ID);
        
        // Compact logged in message - just show user info with links
        $defaults['logged_in_as'] = sprintf(
            '<p class="logged-in-info text-sm text-gray-600 dark:text-gray-400 mb-4">Đăng nhập với tên <a href="%1$s" class="text-primary hover:underline font-medium">%2$s</a> • <a href="%3$s" class="text-primary hover:underline">Chỉnh sửa</a> • <a href="%4$s" class="text-primary hover:underline">Đăng xuất</a></p>',
            esc_url($profile_url),
            esc_html($user_identity),
            esc_url($profile_url),
            esc_url($logout_url)
        );
    }
    
    $defaults['title_reply'] = 'Để lại bình luận';
    $defaults['title_reply_to'] = 'Trả lời %s';
    $defaults['cancel_reply_link'] = 'Hủy trả lời';
    $defaults['label_submit'] = 'Gửi bình luận';
    $defaults['comment_notes_before'] = '';
    $defaults['comment_notes_after'] = '<p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Các trường bắt buộc được đánh dấu *</p>';
    return $defaults;
}

/**
 * Define States for Vietnam explicitly to match our Data Structure
 * This ensures the 'value' of the option is 'Hồ Chí Minh' not 'VN:SG'
 */
add_filter('woocommerce_states', 'nang_tho_custom_vietnam_states');
function nang_tho_custom_vietnam_states($states)
{
    $states['VN'] = array(
        'Hồ Chí Minh' => 'Hồ Chí Minh',
        'Hà Nội' => 'Hà Nội',
        'Đà Nẵng' => 'Đà Nẵng',
        // Add more standard provinces here if needed to support full list, 
        // matching the keys in vietnam_data
    );
    return $states;
}

/**
 * Customize WooCommerce Product Query for Filters
 */
add_action('woocommerce_product_query', 'nang_tho_custom_product_query');
function nang_tho_custom_product_query($q)
{
    $meta_query = $q->get('meta_query') ?: array();
    $tax_query = $q->get('tax_query') ?: array();

    // Price filter
    if (isset($_GET['min_price']) && $_GET['min_price']) {
        $meta_query[] = array(
            'key' => '_price',
            'value' => floatval($_GET['min_price']),
            'compare' => '>=',
            'type' => 'NUMERIC',
        );
    }

    if (isset($_GET['max_price']) && $_GET['max_price']) {
        $meta_query[] = array(
            'key' => '_price',
            'value' => floatval($_GET['max_price']),
            'compare' => '<=',
            'type' => 'NUMERIC',
        );
    }

    // Stock filter
    if (isset($_GET['stock_status']) && $_GET['stock_status'] === 'instock') {
        $meta_query[] = array(
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '=',
        );
    }

    if (!empty($meta_query)) {
        $q->set('meta_query', $meta_query);
    }

    // Brand filter
    if (isset($_GET['filter_brand']) && !empty($_GET['filter_brand'])) {
        $brands = (array) $_GET['filter_brand'];
        
        // Try product attribute first (pa_thuong-hieu)
        $brand_taxonomy = 'pa_thuong-hieu';
        if (!taxonomy_exists($brand_taxonomy)) {
            // Try alternative taxonomy
            $brand_taxonomy = 'product_brand';
        }
        
        if (taxonomy_exists($brand_taxonomy)) {
            $tax_query[] = array(
                'taxonomy' => $brand_taxonomy,
                'field' => 'slug',
                'terms' => $brands,
                'operator' => 'IN',
            );
        }
    }

    if (!empty($tax_query)) {
        $q->set('tax_query', $tax_query);
    }
}

/**
 * Handle WooCommerce Search on Shop Page
 */
add_filter('woocommerce_product_query', 'nang_tho_shop_search');
function nang_tho_shop_search($q)
{
    if (isset($_GET['s']) && !empty($_GET['s']) && (is_shop() || is_product_category() || is_product_taxonomy())) {
        $q->set('s', sanitize_text_field($_GET['s']));
    }
}

/**
 * Customize WooCommerce Ordering Options
 */
add_filter('woocommerce_catalog_orderby', 'nang_tho_custom_ordering_options');
function nang_tho_custom_ordering_options($options)
{
    $options = array(
        'menu_order' => 'Phổ biến nhất',
        'popularity' => 'Bán chạy nhất',
        'date' => 'Hàng mới về',
        'price' => 'Giá: Thấp đến Cao',
        'price-desc' => 'Giá: Cao đến Thấp',
    );
    return $options;
}

/**
 * Remove default WooCommerce content wrapper
 */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

/**
 * Remove "Showing all X results" message on shop page
 */
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);

/**
 * Filter to return empty string for result count
 */
add_filter('woocommerce_get_result_count', '__return_empty_string', 999);

/**
 * Handle "Buy Now" (Mua ngay) - Redirect to checkout after adding to cart
 */
add_filter('woocommerce_add_to_cart_redirect', 'nang_tho_buy_now_redirect', 10, 1);
function nang_tho_buy_now_redirect($url)
{
    // Check if buy_now parameter is set
    if (isset($_REQUEST['buy_now']) && $_REQUEST['buy_now'] == '1') {
        // Redirect to checkout instead of cart/product page
        return wc_get_checkout_url();
    }
    return $url;
}

/**
 * Also handle redirect when using AJAX add to cart
 */
add_action('woocommerce_ajax_added_to_cart', 'nang_tho_ajax_buy_now_redirect', 10, 1);
function nang_tho_ajax_buy_now_redirect($product_id)
{
    if (isset($_REQUEST['buy_now']) && $_REQUEST['buy_now'] == '1') {
        wp_send_json(array(
            'error' => false,
            'product_url' => '',
            'redirect' => wc_get_checkout_url()
        ));
    }
}

/**
 * Customize WooCommerce Product Tabs
 */
add_filter('woocommerce_product_tabs', 'nang_tho_custom_product_tabs');
function nang_tho_custom_product_tabs($tabs)
{
    // Rename tabs
    if (isset($tabs['description'])) {
        $tabs['description']['title'] = 'Mô tả sản phẩm';
        $tabs['description']['callback'] = 'nang_tho_product_description_tab';
    }
    
    if (isset($tabs['additional_information'])) {
        $tabs['additional_information']['title'] = 'Thành phần';
        $tabs['additional_information']['callback'] = 'nang_tho_product_additional_information_tab';
    }
    
    // Remove reviews tab since we're displaying it separately in reviews.php
    if (isset($tabs['reviews'])) {
        unset($tabs['reviews']);
    }
    
    // Add usage instructions tab if needed (can be added via custom field)
    $usage_content = get_post_meta(get_the_ID(), '_product_usage', true);
    if ($usage_content) {
        $tabs['usage'] = array(
            'title' => 'Hướng dẫn sử dụng',
            'priority' => 30,
            'callback' => 'nang_tho_product_usage_tab',
        );
    }
    
    return $tabs;
}

/**
 * Custom Product Description Tab Content
 */
function nang_tho_product_description_tab()
{
    wc_get_template('single-product/tabs/description.php');
}

/**
 * Custom Additional Information Tab Content
 */
function nang_tho_product_additional_information_tab()
{
    wc_get_template('single-product/tabs/additional-information.php');
}

/**
 * Custom Usage Instructions Tab Content
 */
function nang_tho_product_usage_tab()
{
    $usage = get_post_meta(get_the_ID(), '_product_usage', true);
    if ($usage) {
        echo '<div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed">';
        echo wpautop($usage);
        echo '</div>';
    }
}
