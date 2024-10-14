<?php

use inc\Send24_API;
use inc\Send24_Logger;

$count = 0;
class Send24_Shipping_Method extends \WC_Shipping_Method {

	public $form_fields = array();
	private $api;




	public function __construct($instance_id = 0) {
		$this->api = new Send24_API();
		//Send24_Logger::write_log("Shipping method called");

		$this->id                 = 'send24_logistics';
		$this->instance_id 		  = absint($instance_id);
		$this->title       = __( 'Send24 Logistics' );
		$this->method_title    = __('Send24 Logistics');
		$this->method_description = __( '24 hour shipping solution' );

		$this->enabled            = "yes"; // This can be added as a setting but for this example, it's forced enabled.

		$this->supports              = array(
			'shipping-zones',
			'settings',
		);

		$this->init();
		$this->add_a_sleeper_div();
		add_action('woocommerce_review_order_before_payment ', array($this, 'display_send24_shipping_widget'));

	}




	function init(){
		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action('woocommerce_review_order_before_payment', array($this, 'display_send24_shipping_widget'));


		$mode = $this->settings['mode'];
		$connected = false;
		if ($mode === 'test'){
			$public_key = $this->settings['test_api_key'];
			$secret_key = $this->settings['test_secret_key'];
			$connected = !empty($public_key) && !empty($secret_key);
		}else{
			$public_key = $this->settings['live_api_key'];
			$secret_key = $this->settings['live_secret_key'];
			$connected = !empty($public_key) && !empty($secret_key);
		}

		if ($connected){
			$message = "<span style='color:#1c4a05;font-weight: 800;'>Connected</span>";
		}else{
			$message = "<span style='color:#cb0847;font-weight: 800;'>Not Connected</span>";
		}

		$this->method_description = __( '24 hour shipping solution' .'<br><br><br><span><b>Status</b>: '. $message.'</span>' );
	}

	// Fetch shipping options when on the checkout page
	public function calculate_shipping( $package = array() ){
		if (is_cart()){
			Send24_Logger::write_log("CART");
			WC()->session->set('send24_shipping_rate', null);
			WC()->session->set('send24_user_cart_response', null);
			$rate = array(
				'label'    => "Send24 Shipping (Calculated at checkout)",
				'cost'     => '0',
				'calc_tax' => 'per_order'
			);
			$this->add_rate( $rate );
			return;
		}
//		if (!is_checkout()) {
//			Send24_Logger::write_log("Not checkout");
//			return;
//		}

		//Check if there's a calculated
		$pricing_rate = WC()->session->get( 'send24_shipping_rate' );


		if ($pricing_rate != null){
			Send24_Logger::write_log("Shipping $pricing_rate");
			$rate = array(
				'label'    => "Send24 Shipping",
				'cost'     => $pricing_rate,
				'calc_tax' => 'per_order'
			);

			$this->add_rate( $rate );
			return;
		}


		$delivery_base_contents = $package['contents'];

		// Destination details
		$delivery_country_code = $package['destination']['country'];
		$delivery_state_code = $package['destination']['state'];
		$destination_state = WC()->countries->get_states($delivery_country_code)[$delivery_state_code];
		$delivery_base = $package['destination']['address'];

		$pickup_state 		= 'Lagos';
		$pickup_address = $this->get_option('woocommerce_store_address');

		$pickup_coordinates = '6.4310185, 3.4212225';
		$destination_coordinates = '6.5894212, 3.3357455';
		$products = array();




		// Example of iterating through cart items
		foreach( $delivery_base_contents as $item_id => $item ){
			$product_id = $item["product_id"];
			$product = wc_get_product( $product_id );

			$name       = $product->get_name();
			$products[] = $name;
			$price = $product->get_price();
			$quantity = $item['quantity'];
			$weight = $product->get_weight();
			$dimension = $product->get_dimensions();
		}



		$size_response = $this->api->get_size($products, '');
		Send24_Logger::write_log($size_response);
		$size_id = $size_response->size_id;
		$size_fragility = $size_response->is_fragile;

		Send24_Logger::write_log("Size: $size_id");

		if ($size_id == null){
			echo '<script> window.alert("Large items present in the cart.") </script>';
			return;
		}


		$data = [
			'size_id' => $size_id,
			'pickup_coordinates' => $pickup_coordinates,
			'destination_coordinates' => $destination_coordinates,
			'pickup_state' => $pickup_state,
			'destination_state' => $destination_state,
			'is_fragile' => $size_fragility,
		];


		$response = $this->api->calculate_price($data, '');


		if (isset($response->status) && $response->status === 'success' && $response->data !== NULL) {
			foreach ($response->data as $option) {
				foreach ( $option as $shipping_type => $details ) {
					if ( $shipping_type === 'HUB_TO_HUB' ) {
						$formatted_price = $details->formatted_price;
						$price           = $details->price;
						Send24_Logger::write_log("Price: $price");
						WC()->session->set('send24_shipping_rate', $price);
						WC()->session->set('send24_user_cart_response', json_encode($response));
						$rate = array(
							'label'    => "Send24 Shipping",
							'cost'     => $price,
							'calc_tax' => 'per_order'
						);

						$this->add_rate( $rate );

					}
				}
			}
		}

    }

	private function add_a_sleeper_div(){
		if (is_checkout()){
			echo '<div id="send24Modal_hidden" class="send24-modal-hidden" style="display: none;"> </div>';
		}

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

add_action( 'woocommerce_cart_updated', 'clear_shipping_session_data' );
add_filter( 'woocommerce_cart_calculate_fees', 'load_checkout_script', 10, 1 );
function clear_shipping_session_data() {
	//WC()->session->set('send24_shipping_rate', null);
}

function load_checkout_script(){
	wp_enqueue_style( 'modal', plugins_url( 'modal.css', __FILE__ ) );
	wp_enqueue_script( 'send24checkout', plugins_url( 'send24checkout.js', __FILE__ ) );
	wp_localize_script( 'send24checkout', 'ajax_object',
		array( 'ajax_url' => admin_url( 'admin-ajax.php' )
		));
}