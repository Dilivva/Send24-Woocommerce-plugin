<?php

namespace inc;

class Send24_API {

	public function calculate_price($params, $access_token){
		return $this->api_request('https://dev.dilivva.com.ng/api/v1/pricing', $params, 'POST', $access_token);
	}

	public function create_order($params, $access_token) {
        return $this->api_request('https://dev.dilivva.com.ng/api/v1/corporates/orders', $params, 'POST', $access_token);
    }
	public function get_size($params, $access_token) {
		return $this->api_request('https://apps.dilivva.com.ng/api/get-size', $params);
	}
//	public function test($params) {
//		return $this->api_request('https://webhook.site/d6b9bb9c-2b01-4693-8810-bae7a71601d6', $params);
//	}


	private function api_request(
		$endpoint,
		$args = array(),
		$method = 'POST', $token = NULL
	) {
		$arg = array(
			'method'      => $method,
			'timeout'     => 260,
			'sslverify'   => false,
			'headers'     => $this->get_headers($token),
			'body'        => json_encode($args),

		);
		if($method == 'GET'){
			$arg = array(
				'timeout'     => 260,
				'sslverify'   => false,
				'headers'     => $this->get_headers($token),
			);

			$getApiResponse = wp_remote_get( $endpoint, $arg );
		}else{
			$getApiResponse = wp_remote_request( $endpoint, $arg );
		}
		if (is_wp_error($getApiResponse)){
			$bodyApiResponse = $getApiResponse->get_error_message();
		}else{
			$bodyApiResponse = json_decode(wp_remote_retrieve_body($getApiResponse));
		}

		return $bodyApiResponse;
	}

	/**
	 * Generates the headers to pass to API request.
	 */
	private function get_headers($token)
	{
		if(!empty($token)){
			$getHead = array(
				'Authorization' => "Bearer {$token}",
				'Content-Type'  => 'application/json',
			);
		}else{
			$getHead = array('Content-Type'  => 'application/json',);
		}

		return $getHead;

	}

}