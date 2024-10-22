<?php
// Send24 Order Creation
use inc\Send24_API;
use inc\Send24_Logger;

class Send24_Order_Creation {

    private Send24_API $api;
	private array $settings;

	private static $instance;


	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;

    }

    public function __construct() {
		Send24_Logger::write_log("Send24_Order_Creation");
        $this->api = new Send24_API();
        $this->settings = get_option('woocommerce_send24_logistics_settings');
		//Block checkout
        add_action('woocommerce_checkout_order_created', array($this, 'create_send24_order'));
		//For folks still using normal checkout
	    add_action('woocommerce_store_api_checkout_order_processed', array($this, 'create_send24_order'));
    }



    public function create_send24_order( $order ) {

        $mode = $this->settings['mode'];

        // Fetch the appropriate API key based on the 
        if ($mode === 'test') {
            $api_key = $this->settings['test_api_key'];
        } else {
            $api_key = $this->settings['live_api_key'];
        }
        Send24_Logger::write_log("Send24 API Key: " . $api_key);
	    //$order = wc_get_order($order_id);
	    Send24_Logger::write_log("Starting Send24 order creation for order ID: " .json_encode($order));

	    // Retrieve the origin_hub_id from the WooCommerce session
        $destination_hub_id = WC()->session->get('send24_selected_hub_id');
        if (!$destination_hub_id) {
	        $destination_hub_id = null; // Fallback value if the session is not set
        }

		Send24_Logger::write_log("Destination Hub ID: " . $destination_hub_id);


        $size = WC()->session->get('send24_size');
        if (!$size) {
	        $size = 'small';
        }
        Send24_Logger::write_log("Size: " . $size);

        $is_fragile = WC()->session->get('send24_is_fragile');
        if ($is_fragile === null) {
	        $is_fragile = 1;
        }
        Send24_Logger::write_log("Fragility: " . $is_fragile);


        $destination_address = $order->get_shipping_address_1();

        $name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
        $phone = $order->get_billing_phone();
        $email = $order->get_billing_email();


        // Loop through items for package data
	    $product_names = '';
		$product_images = [];
        foreach ($order->get_items() as $item_key => $item) {
	        $label = $item->get_name();
			$product_names = $product_names .'-' . $label;
	        $product = $item->get_product();

	        $product_image_id = $product->get_image_id();
	        $product_image_url = wp_get_attachment_url($product_image_id);

	        if ($product_image_url) {
		        $product_images[] = $product_image_url;
	        }

        }
	        $data = [
		        'destination_address' => $destination_address,
		        'size' => $size,
		        'label' => $product_names,
		        'is_fragile' => $is_fragile,
		        'name' => $name,
		        'phone' => $phone,
		        'email' => $email,
		        'destination_hub_id' => $destination_hub_id,
		        'images' => $product_images,
	        ];

		Send24_Logger::write_log("Data: ".json_encode($data));

		$response = $this->api->create_order($data);
		Send24_Logger::write_log("Order Creation Response: " . json_encode($response));

    }
}
