<?php
// Send24 Order Creation
use inc\Send24_API;
use inc\Send24_Logger;

class Send24_Order_Creation {

    private $api;

    public function __construct() {
        $this->api = new Send24_API();
        $this->settings = get_option('woocommerce_send24_logistics_settings');
        add_action('woocommerce_thankyou', array($this, 'create_send24_order'), 10, 1);
    }

    public function create_send24_order( $order_id ) {
    
        Send24_Logger::write_log("Starting Send24 order creation for order ID: " . $order_id);
    
        // Fetch the shipping method instance to get the API key and mode
        $mode = $this->settings['mode'];
        Send24_Logger::write_log("Send24 Mode: " . $mode);
    
        // Fetch the appropriate API key based on the mode
        if ($mode === 'test') {
            $api_key = $this->settings['test_api_key'];
        } else {
            $api_key = $this->settings['live_api_key'];
        }
        Send24_Logger::write_log("Send24 API Key: " . $api_key);
    
        $order = wc_get_order($order_id);
        
        // Retrieve the origin_hub_id from the WooCommerce session
        $destination_hub_id = WC()->session->get('send24_selected_hub_id');
        if (!$destination_hub_id) {
            $destination_hub_id = ''; // Fallback value if the session is not set
        }
        Send24_Logger::write_log("Destination Hub ID: " . $origin_hub_id);
    
        // $size = '68882080-9cb3-11ed-a1e0-1b525c297de0'; // Example size ID
        // $is_fragile = 0; 
        // $variant = 'HUB_TO_DOOR';

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


    
        // Store information
        $store_raw_country = get_option('woocommerce_default_country');
        $split_country = explode(':', $store_raw_country);
        $store_country = isset($split_country[0]) ? $split_country[0] : '';
        $store_state = isset($split_country[1]) ? $split_country[1] : '';
        $pickup_state = WC()->countries->get_states($store_country)[$store_state];
    
        // Destination information
        $destination_address = $order->get_shipping_address_1();
        // $destination_coordinates = '6.5156, 3.3862'; // Placeholder
        // $des_country = $order->get_shipping_country();
        // $des_state = $order->get_shipping_state();
        // $destination_state = WC()->countries->get_states($des_country)[$des_state];
    
        // $destination_local_government = 'Lagelu'; // Example LGA
        $name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
        $phone = $order->get_billing_phone();
        $email = $order->get_billing_email();
        // $order_type = 'delivery';
    
        // Loop through items for package data
        foreach ($order->get_items() as $item_key => $item) {
            $label = $item->get_name();
            $product = $item->get_product();
            $package_note = $product->get_short_description();
            $recipient_note = 'Delivery Note';

            // Retrieve the product image URL
            $product_image_id = $product->get_image_id();
            $product_image_url = wp_get_attachment_url($product_image_id); // Convert ID to URL

            // Check if the product image URL is valid
            if (!$product_image_url) {
                $product_image_url = ''; // Fallback if no image URL is available
            }

            // Prepare API data
            $data = [
                'destination_address' => $destination_address,
                'size' => $size,
                'label' => $label,
                'is_fragile' => $is_fragile,
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'destination_hub_id' => $destination_hub_id,
                'images' => [$product_image_url],
            ];

            $test = json_encode( $data );

            // die('You hit the right hook!-------'. $test . '.....');
    
            // Send to API
            $response = $this->api->create_order($data);
            Send24_Logger::write_log("Order Creation Response: " . json_encode($response));
        }
    }
    
}
