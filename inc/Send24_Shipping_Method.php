<?php

use inc\Send24_Logger;
use inc\Send24_API;

class Send24_Shipping_Method extends \WC_Shipping_Method {

    public $form_fields = array();
    private $api;

    public function __construct($instance_id = 0) {
        $this->api = new Send24_API();
        Send24_Logger::write_log("Shipping method called");

        $this->id                 = 'send24_logistics';
        $this->instance_id        = absint($instance_id);
        $this->title              = __( 'Send24 Logistics' );
        $this->method_title       = __('Send24 Logistics');
        $this->method_description = __( '24 hour shipping solution' );

        $this->enabled            = "yes"; 

        $this->supports           = array(
            'shipping-zones',
            'settings',
        );

        $this->init();

        add_action('woocommerce_review_order_before_payment', array($this, 'display_send24_shipping_widget'));

        add_action('wp_ajax_send24_update_shipping_price', array($this, 'send24_update_shipping_price'));
        add_action('wp_ajax_nopriv_send24_update_shipping_price', array($this, 'send24_update_shipping_price'));

        add_action('woocommerce_checkout_order_processed', function ($order_id) {
            WC()->session->__unset('send24_selected_shipping');
        });
    }

    function init(){
        $this->init_form_fields();
        $this->init_settings();

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

        $mode = $this->settings['mode'];
        $connected = false;
        if ($mode === 'test'){
            $public_key = $this->settings['test_api_key'];
            $secret_key = $this->settings['test_secret_key'];
            $connected = !empty($public_key) && !empty($secret_key);
        } else {
            $public_key = $this->settings['live_api_key'];
            $secret_key = $this->settings['live_secret_key'];
            $connected = !empty($public_key) && !empty($secret_key);
        }
        $message = '';
        if ($connected){
            $message = "<span style='color:#1c4a05;font-weight: 800;'>Connected</span>";
        } else {
            $message = "<span style='color:#cb0847;font-weight: 800;'>Not Connected</span>";
        }

        $this->method_description = __( '24 hour shipping solution' .'<br><br><br><span><b>Status</b>: '. $message.'</span>' );
    }

    // Fetch shipping options when on the checkout page
    public function calculate_shipping( $package = array() ){
        static $dropdown_rendered = false;

        if ($dropdown_rendered) {
            return;
        }

        $dropdown_rendered = true;

        $delivery_country_code = $package['destination']['country'];
        $delivery_state_code = $package['destination']['state'];

        $destination_state = WC()->countries->get_states($delivery_country_code)[$delivery_state_code];

        $destination_address = $package['destination']['address'];

        $full_destination_address = $destination_address . ', ' . $destination_state;
        Send24_Logger::write_log("State: $full_destination_address");

        $product_names = [];

        // Prepare data for get_size_and_fragility
        $delivery_base_contents = $package['contents'];
        foreach ($delivery_base_contents as $item_id => $item) {
            $product_id = $item["product_id"];
            $product = wc_get_product($product_id);
        
            $product_names[] = $product->get_name();
        }
                
        $size_fragility_response = $this->api->get_size_and_fragility($product_names, '');
        $size_resp = json_encode($size_fragility_response);
        Send24_Logger::write_log("Response: $size_resp");

        $size_fragility = json_decode($size_resp, true);
        
        // Check if the response contains size and is_fragile information
        if ($size_fragility && isset($size_fragility['name'], $size_fragility['is_fragile'])) {
            $size = $size_fragility['name'];
            $is_fragile = $size_fragility['is_fragile'] ? 1 : 0;
        } else {
            // Handle error case here
            $size = '';
            $is_fragile = 0;
        }
        
        // Prepare data for calculating price
        $calculate_price_data = [
            'size' => $size,
            'destination_address' => $full_destination_address,
            'is_fragile' => $is_fragile
        ];

        $response = $this->api->calculate_price($calculate_price_data);
        $resp = json_encode($response);
        Send24_Logger::write_log("Response: $resp");

        WC()->session->set('send24_shipping_options', $response);

        if (is_checkout()) {
            $response_data = json_decode($resp, true);
            if (isset($response_data['status']) && $response_data['status'] === 'success' && !empty($response_data['data'])) {

                // Enqueue CSS and JS files
                add_action('wp_enqueue_scripts', function() {
                    wp_enqueue_style('send24-widget-style', plugin_dir_url(__FILE__) . 'send24-shipping-widget.css');
                    wp_enqueue_script('send24-widget-script', plugin_dir_url(__FILE__) . 'send24-shipping-widget.js', array('jquery'), null, true);

                    // Localize script to pass data to JS
                    wp_localize_script('send24-widget-script', 'send24_ajax_object', array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce'    => wp_create_nonce('send24_nonce')
                    ));
                });

                // Make $response_data available to the template
                $send24_response_data = $response_data['data'];

                // Include the HTML template
                include plugin_dir_path(__FILE__) . 'send24-shipping-widget.php';

            } else {
                echo '<p>Unable to fetch Send24 shipping options at this time. Please try again later.</p>';
            }
        }
    }

    // Handle the AJAX request to update shipping price
    public function send24_update_shipping_price() {
        check_ajax_referer('send24_nonce', 'nonce'); // Security check

        if (!isset($_POST['shipping_option'])) {
            wp_send_json_error('Shipping option not set');
        }

        $shipping_option = sanitize_text_field($_POST['shipping_option']);
        $price = floatval($_POST['price']);
        $hub_uuid = isset($_POST['hub_uuid']) ? sanitize_text_field($_POST['hub_uuid']) : '';

        // Update session with chosen shipping price
        WC()->session->set('send24_selected_shipping', [
            'option' => $shipping_option,
            'price' => $price,
            'hub_uuid' => $hub_uuid
        ]);

        wp_send_json_success('Shipping option updated successfully');
    }


	public function init_form_fields() {
		$this->form_fields = array(
			'mode' => array(
				'title'       => 	__('Environment'),
				'type'        => 	'select',
				'description' => 	__('All order type created are determined by this mode.'),
				'default'     => 	'test',
				'options'     => 	array('test' => 'Test', 'live' => 'Live'),
				'class'		  =>	'send24_mode'
			),
			'test_api_key' => array(
				'title'       => 	__('Test API Key'),
				'type'        => 	'text',
				'description' => 	__('Your test API key as provided on your Send24 dashboard'),
				'class'		  =>	'send24_test_api_key send24_test',
				'default'     => 	__('')
			),
			'test_secret_key' => array(
				'title'       => 	__('Test Secret Key'),
				'type'        => 	'password',
				'description' => 	__('Your test secret key as provided on your Send24 dashboard'),
				'class'		  =>	' send24_test_secret_key send24_test',
				'default'     => 	__('')
			),
			'live_api_key' => array(
				'title'       => 	__('Live API Key'),
				'type'        => 	'text',
				'description' => 	__('Your live API key as provided on your Send24 dashboard'),
				'id'		  =>	'send24_live_api_key send24_live',
				'default'     => 	__('')
			),
			'live_secret_key' => array(
				'title'       => 	__('Live Secret Key'),
				'type'        => 	'password',
				'description' => 	__('Your live secret key as provided on your Send24 dashboard'),
				'id'		  =>	'send24_live_secret_key send24_live',
				'default'     => 	__('')
			)
		);
	}
}
