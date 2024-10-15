<?php
// Send24 Order Creation
use inc\Send24_Logger;
use inc\Send24_API;

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
        // $mode = get_option('woocommerce_send24_logistics_environment'); // Assuming 'environment' is the key used in the settings
        $mode = $this->settings['mode'];
        Send24_Logger::write_log("Send24 Mode: " . $mode);

        // Fetch the appropriate API key based on the 
        if ($mode === 'test') {
            $api_key = $this->settings['test_api_key'];
        } else {
            $api_key = $this->settings['live_api_key'];
        }
        Send24_Logger::write_log("Send24 API Key: " . $api_key);

        $order = wc_get_order($order_id);
        
        // Define hardcoded values and WooCommerce data
        $pickup_address = 'UNILAG Senate Building, Lagos, Nigeria';
        $pickup_coordinates = '6.5194683, 3.3987129';
        $size_id = '68882080-9cb3-11ed-a1e0-1b525c297de0'; // Example size ID
        $origin_hub_id = '32b51d10-8eaf-11ee-8032-37c06d0259ca'; // Example hub ID
        $is_fragile = 0; 
        $variant = 'HUB_TO_DOOR';

        // Store information
        $store_raw_country = get_option('woocommerce_default_country');
        $split_country = explode(':', $store_raw_country);
        $store_country = isset($split_country[0]) ? $split_country[0] : '';
        $store_state = isset($split_country[1]) ? $split_country[1] : '';
        $pickup_state = WC()->countries->get_states($store_country)[$store_state];

        // Destination information
        $destination_address = $order->get_shipping_address_1();
        $destination_coordinates = '6.5156, 3.3862'; // Placeholder
        $des_country = $order->get_shipping_country();
        $des_state = $order->get_shipping_state();
        $destination_state = WC()->countries->get_states($des_country)[$des_state];

        $destination_local_government = 'Lagelu'; // Example LGA
        $name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
        $phone = '07033128603';
        $email = $order->get_billing_email();
        $order_type = 'delivery';

        // Loop through items for package data
        foreach ($order->get_items() as $item_key => $item) {
            $label = $item->get_name();
            $product = $item->get_product();
            $package_note = $product->get_short_description();
            $recipient_note = 'Delivery Note';
            $product_image = $product->get_image_id();

            // Prepare API data
            $data = [
                'pickup_address' => $pickup_address,
                'pickup_coordinates' => $pickup_coordinates,
                'destination_address' => $destination_address,
                'destination_coordinates' => $destination_coordinates,
                'size_id' => $size_id,
                'label' => $label,
                'package_note' => $package_note,
                'is_fragile' => $is_fragile,
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'origin_hub_id' => $origin_hub_id,
                'recipient_note' => $recipient_note,
                'destination_state' => $destination_state,
                'destination_local_government' => $destination_local_government,
                'pickup_state' => $pickup_state,
                'variant' => $variant,
                'images' => [$product_image],
                'order_type' => $order_type
            ];

            $test = json_encode( $data );

            // die('You hit the right hook!-------'. $test . '.....');

            // Send to API
            $response = $this->api->create_order($data, $api_key);
            Send24_Logger::write_log("Order Creation Response: " . json_encode($response));
        }
    }
}
