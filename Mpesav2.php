<?php
/**
 * Plugin Name: Mpesa for WooCommerce
 * Plugin URI: https://dev-nourisha.pantheonsite.io/mpesa_for_woo
 * Author Name: Nourisha
 * Author URI: https://dev-nourisha.pantheonsite.io
 * Description: This plugin allows for Mpesa payment systems in WooCommerce.
 * Version: 1.0.0
 * text-domain: mpesa-pay-woo
 */

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

add_action('plugins_loaded', 'mpesa_payment_init', 11);

function mpesa_payment_init() {
    if (class_exists('WC_Payment_Gateway')) {
        class WC_mpesa_pay_Gateway extends WC_Payment_Gateway {
            public function __construct() {
                $this->id = 'mpesa_payment';
                $this->icon = apply_filters('woocommerce_mpesa_icon', plugins_url('/icon.png', __FILE__));
                $this->has_fields = false;
                $this->method_title = __('Mpesa Payment', 'mpesa-pay-woo');
                $this->method_description = __('Mpesa payment systems.', 'mpesa-pay-woo');

                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');
                $this->instructions = $this->get_option('instructions', $this->description);

                $this->init_form_fields();
                $this->init_settings();

                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            }

            public function init_form_fields() {
                $this->form_fields = apply_filters('woo_mpesa_pay_fields', array(
                    'enabled' => array(
                        'title' => __('Enable/Disable', 'mpesa-pay-woo'),
                        'type' => 'checkbox',
                        'label' => __('Enable or Disable Mpesa Payments', 'mpesa-pay-woo'),
                        'default' => 'no'
                    ),
                    'title' => array(
                        'title' => __('Mpesa Payment Gateway', 'mpesa-pay-woo'),
                        'type' => 'text',
                        'default' => __('Mpesa Payment Gateway', 'mpesa-pay-woo'),
                        'desc_tip' => true,
                        'description' => __('Add a new title for the Mpesa Payment Gateway that customers will see when they are on the checkout page.', 'mpesa-pay-woo')
                    ),
                    'description' => array(
                        'title' => __('Mpesa Payment Gateway Description', 'mpesa-pay-woo'),
                        'type' => 'textarea',
                        'default' => __('Please remit your payment to the shop to allow for the delivery to be made', 'mpesa-pay-woo'),
                        'desc_tip' => true,
                        'description' => __('Add a new description for the Mpesa Payment Gateway that customers will see when they are on the checkout page.', 'mpesa-pay-woo')
                    ),
                    'instructions' => array(
                        'title' => __('Instructions', 'mpesa-pay-woo'),
                        'type' => 'textarea',
                        'default' => __('Default instructions', 'mpesa-pay-woo'),
                        'desc_tip' => true,
                        'description' => __('Instructions that will be added to the thank you page and order email', 'mpesa-pay-woo')
                    ),
                    'auth_key' => array( // Add the authKey field.
                        'title' => __('Auth Key', 'mpesa-pay-woo'),
                        'type' => 'text',
                        'default' => '',
                        'desc_tip' => true,
                        'description' => __('Enter the authentication key for clearing payments with the API.', 'mpesa-pay-woo'),
                    ),
                ));
            }

            public function process_payments($order_id) {
                $order = wc_get_order($order_id);

                $auth_key = $this->get_option('auth_key'); // Get the authKey value entered by the user.

                $order_total = $order->get_total();

                // Fetch user's Mpesa phone number if not available, you can use a popup or form.

                // Create the JavaScript popup here or refer to the previous code for the popup/modal.

                // Send a request to a URL with the fetched data including authKey.
                $request_url = 'http://192.168.228.33:8000/api/get-data'; // Replace with the actual URL you want to send the data to.

                $user_mpesa_phone = ''; // Fetch the user's Mpesa phone number.

                $request_data = array(
                    'order_total' => $order_total,
                    'mpesa_phone' => $user_mpesa_phone,
                    'authKey' => $auth_key, // Include the authKey in the request data.
                );

                // Send the request using wp_safe_remote_post.
                $response = wp_safe_remote_post($request_url, array(
                    'body' => json_encode($request_data),
                    'headers' => array('Content-Type' => 'application/json'),
                ));

                if (is_wp_error($response)) {
                    // Handle the error.
                } else {
                    // Process the API response if needed.
                    $api_response = wp_remote_retrieve_body($response);
                }

                $order->update_status('on-hold', __('Awaiting Mpesa Payment', 'mpesa-pay-woo'));

                $order->reduce_order_stock();

                WC()->cart->empty_cart();

                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order),
                );
            }

            public function thank_you_page() {
                if ($this->instructions) {
                    echo wpautop($this->instructions);
                }
            }
        }
    }
}

add_filter('woocommerce_payment_gateways', 'add_to_woo_mpesa_payment_gateway');

function add_to_woo_mpesa_payment_gateway($gateways) {
    $gateways[] = 'WC_mpesa_pay_Gateway';
    return $gateways;
}

// Enqueue the JavaScript and CSS files.
function mpesa_enqueue_scripts() {
    // Enqueue the JavaScript file.
    wp_enqueue_script('mpesa-popup', plugins_url('popup.js', __FILE__), array('jquery'), '1.0.0', true);

    // Enqueue the CSS file.
    wp_enqueue_style('mpesa-popup-css', plugins_url('popup.css', __FILE__), array(), '1.0.0');
}

// Hook the enqueue function to the 'wp_enqueue_scripts' action.
add_action('wp_enqueue_scripts', 'mpesa_enqueue_scripts');
