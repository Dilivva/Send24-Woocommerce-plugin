<?php

use send24_inc\Send24_API;
use send24_inc\Send24_Logger;

$count = 0;
class Send24_Shipping_Method extends \WC_Shipping_Method {

	public $form_fields = array();
	private Send24_API $api;


	public function __construct($instance_id = 0) {
		$this->api = new Send24_API();

		$this->id                 = 'send24_logistics';
		$this->instance_id 		  = absint($instance_id);
		$this->title = esc_html__('Send24 Logistics', 'send24-logistics');
		$this->method_title = esc_html__('Send24 Logistics', 'send24-logistics');
		$this->method_description = esc_html__('24 hour shipping solution', 'send24-logistics');

		$this->enabled            = "yes";

		$this->supports              = array(
			'shipping-zones',
			'settings',
		);

		$this->init();
		$this->add_a_sleeper_div();
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

		$this->method_description = sprintf(
			esc_html__('24 hour shipping solution<br><br><br><span><b>Status</b>: %s</span>', 'send24-logistics'),
			$message
		);
		
	}

	public function calculate_shipping( $package = array() ){

		if (is_cart()){
			WC()->session->set('send24_shipping_rate', null);
			WC()->session->set('send24_user_cart_response', null);
			WC()->session->set('send24_selected_variant', null);
			WC()->session->set('send24_size', null);
			WC()->session->set('send24_is_fragile', null);
			WC()->session->set('send24_selected_hub_id', null);

			$rate = array(
				'label'    => "Send24 Shipping (Calculated at checkout)",
				'cost'     => '0',
				'calc_tax' => 'per_order'
			);
			$this->add_rate( $rate );
			return;
		}


		$variant = WC()->session->get('send24_selected_variant');
		$fee = WC()->session->get('send24_shipping_rate');


		if ($variant != null){

			$size_fragility_session = WC()->session->get('send24_user_cart_response');
			$response = json_decode($size_fragility_session);

			if ($variant === 'HUB_TO_HUB'){
				$selected_hub_id = WC()->session->get('send24_selected_hub_id');
				$hub = $this->getRecommendedHubById($response, $selected_hub_id );
				if ($hub == null) return;

				$hub_name = $hub->name;
				$hub_distance = $hub->distance;
				$hub_uuid = $hub->uuid;

				WC()->session->set('send24_selected_hub_id', $hub_uuid);

				$display = "Pick up at $hub_name, $hub_distance km from you. Tap to change";
			}else{
				$display = "Deliver to your house. Tap to change";
			}

			$rate = array(
				'label' => $display,
				'cost' => $fee,
				'calc_tax' => 'per_order'
			);
			$this->add_rate( $rate );
			return;
		}


		Send24_Logger::write_log("Recalculating....");


		$delivery_country_code = $package['destination']['country'];
        $delivery_state_code = $package['destination']['state'];
		$destination_state = WC()->countries->get_states($delivery_country_code)[$delivery_state_code];
        $destination_address = $package['destination']['address'];
		$full_destination_address = $destination_address . ', ' . $destination_state;

        $product_names = [];

        $delivery_base_contents = $package['contents'];
        foreach ($delivery_base_contents as $item_id => $item) {
            $product_id = $item["product_id"];
            $product = wc_get_product($product_id);
        
            $product_names[] = $product->get_name();
        }

		$size_fragility_session = WC()->session->get('send24_user_cart_response');

		if ($size_fragility_session != null){
			$response = json_decode($size_fragility_session);
			$this->set_shipping_rate($response);
			return;
		}

		$size_fragility = $this->api->get_size_and_fragility($product_names, '');

		Send24_Logger::write_log("Size: ".json_encode($size_fragility));

		$size = $size_fragility->name;
		$is_fragile = $size_fragility->is_fragile;


		if ($size == null && $is_fragile == null) return;


		WC()->session->set('send24_size', $size);
		WC()->session->set('send24_is_fragile', $is_fragile);
        
        $calculate_price_data = [
            'size' => $size,
            'destination_address' => $full_destination_address,
            'is_fragile' => $is_fragile
        ];

        $response = $this->api->calculate_price($calculate_price_data);

		Send24_Logger::write_log("Price: ".json_encode($response));

		$this->set_shipping_rate($response);

		Send24_Logger::write_log("Recalculating last....".json_encode($calculate_price_data));

    }

	private function set_shipping_rate($response){
		if (isset($response->status) && $response->status === 'success' && $response->data !== NULL) {
			foreach ($response->data as $option) {
				foreach ( $option as $shipping_type => $details ) {
					if ( $shipping_type === 'HUB_TO_HUB' ) {
						$price           = $details->price;
						$first_hub = $details->recommended_hubs[0];
						$hub_name = $first_hub->name;
						$hub_distance = $first_hub->distance;
						$hub_uuid = $first_hub->uuid;

						$display = "Pick up at $hub_name, $hub_distance km from you. Tap to change";

						WC()->session->set('send24_shipping_rate', $price);
						WC()->session->set('send24_user_cart_response', json_encode($response));
						WC()->session->set('send24_selected_hub_id', $hub_uuid);
						WC()->session->set('send24_selected_variant', $shipping_type);

						$rate = array(
							'label'    => $display,
							'cost'     => $price,
							'calc_tax' => 'per_order'
						);

						$this->add_rate( $rate );


					}
				}
			}
		}
	}

	private function getRecommendedHubById($response, $hubId) {
		foreach ($response->data as $option) {
			foreach ( $option as $shipping_type => $details ) {
				if ( $shipping_type === 'HUB_TO_HUB' ) {
					if (empty($hubId)){
						return $details->recommended_hubs[0];
					}
					foreach ($details->recommended_hubs as $hub) {
						if ($hub->uuid === $hubId) {
							return $hub;
						}
					}
				}
			}

		}

		return null;
	}

	private function add_a_sleeper_div(){
		if (is_checkout()){
			echo '<div id="send24Modal_hidden" class="send24-modal-hidden" style="display: none;"> </div>';
		}

	}
	

	public function init_form_fields() {
		$this->form_fields = array(
			'mode' => array(
				'title' => esc_html__('Environment', 'send24-logistics'),
				'type'        => 	'select',
				'description' => esc_html__('All order type created are determined by this mode.', 'send24-logistics'),
				'default'     => 	'test',
				'options'     => 	array('test' => 'Test', 'live' => 'Live'),
				'class'		  =>	'send24_mode'
			),
			'test_api_key' => array(
				'title' => esc_html__('Test API Key', 'send24-logistics'),
				'type'        => 	'password',
				'description' => esc_html__('Your test API key as provided on your Send24 dashboard', 'send24-logistics'),
				'class'		  =>	'send24_test_api_key send24_test',
				'default'     => 	null,
			),
			'test_secret_key' => array(
				'title' => esc_html__('Test Secret Key', 'send24-logistics'),
				'type'        => 	'password',
				'description' => esc_html__('Your test secret key as provided on your Send24 dashboard', 'send24-logistics'),
				'class'		  =>	' send24_test_secret_key send24_test',
				'default'     => 	null,
			),
			'live_api_key' => array(
				'title' => esc_html__('Live API Key', 'send24-logistics'),
				'type'        => 	'password',
				'description' => esc_html__('Your live API key as provided on your Send24 dashboard', 'send24-logistics'),
				'id'		  =>	'send24_live_api_key send24_live',
				'default'     => 	null,
			),
			'live_secret_key' => array(
				'title' => esc_html__('Live Secret Key', 'send24-logistics'),
				'type'        => 	'password',
				'description' => esc_html__('Your live secret key as provided on your Send24 dashboard', 'send24-logistics'),
				'id'		  =>	'send24_live_secret_key send24_live',
				'default'     => 	null,
			)
		);
	}

	}

add_filter( 'woocommerce_cart_calculate_fees', 'send24_load_checkout_script', 10, 1 );


