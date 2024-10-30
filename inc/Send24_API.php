<?php

namespace inc;

use WP_Error;

class Send24_API {

	public function test() {
		return $this->api_request('https://webhook.site/d6b9bb9c-2b01-4693-8810-bae7a71601d6');
	}

	public function calculate_price($params) {
		return $this->api_request('https://business-api.dilivva.com.ng/api/v1/orders/pricing', $params, 'POST');
	}

	public function get_size_and_fragility($params) {
		return $this->api_request('https://apps.dilivva.com.ng/api/get-size', $params, 'POST');
	}

	public function create_order($params) {
		return $this->api_request('https://business-api.dilivva.com.ng/api/v1/orders', $params, 'POST');
	}

	private function api_request($endpoint, $args = array(), $method = 'POST') {
		$requestBody = json_encode($args);
		$headers = $this->get_headers($requestBody);

		$arg = array(
			'method'      => $method,
			'timeout'     => 260,
			'sslverify'   => false,
			'headers'     => $headers,
			'body'        => $requestBody,
		);

		$getApiResponse = wp_remote_request($endpoint, $arg);

		if (is_wp_error($getApiResponse)) {
			$bodyApiResponse = $getApiResponse->get_error_message();
		} else {
			$bodyApiResponse = json_decode(wp_remote_retrieve_body($getApiResponse));
		}

		return $bodyApiResponse;
	}

	private function get_headers($requestBody) {
		$settings = get_option('woocommerce_send24_logistics_settings');
		$mode = $settings['mode'];
	
		if ($mode === 'test') {
			$public_key = $settings['test_api_key'];  
			$secret_key = $settings['test_secret_key'];
		} else {
			$public_key = $settings['live_api_key'];  
			$secret_key = $settings['live_secret_key'];
		}
	
		if (!$public_key || !$secret_key) {
			return new WP_Error('send24_missing_keys', 'Public API key or Secret key is missing.');
		}
	
		$signature = hash_hmac('sha256', $requestBody, $secret_key);  
		Send24_Logger::write_log('API Mode: ' . $mode);
		Send24_Logger::write_log('Public Key: ' . $public_key);
		Send24_Logger::write_log('Signature: ' . $signature);
	
		return [
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
			'X-Public-Key'  => $public_key, 
			'X-Signature'   => $signature,  
		];
	}
	
}
