<?php
/**
 * @package  Send24 Logistics
 */
/*
Plugin Name: Send24 Logistics
Plugin URI: https://send24.co
Description: 24 hour nation-wide delivery.
Version: 1.0.0
Requires PHP: 7.4
Requires at least: 6.3
Author: Send24
Author URI: https://github.com/dilivva
License: GPLv2 or later
Text Domain: send24
Copyright: © 2024 Send24 Logistics
Icon: assets/logo.png
*/


// If this file is called firectly, abort!!!
use inc\Send24_Activation;
use inc\Send24_Deactivation;
use inc\settings\Send24_WC_Admin_Settings;

defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );

require_once (dirname(__FILE__) . '/inc/Send24_Activation.php');
require_once (dirname(__FILE__) . '/inc/Send24_Deactivation.php');
require_once (dirname(__FILE__) . '/inc/Send24_Logger.php');
require_once (dirname(__FILE__) . '/inc/Send24_Modal.php');
require_once (dirname(__FILE__) . '/inc/settings/Send24_WC_Admin_Settings.php');

require_once (dirname(__FILE__) . '/inc/Send24_API.php');

require_once (dirname(__FILE__) . '/inc/Send24_Order_Creation.php');
// require_once (dirname(__FILE__) . '/inc/Send24UI.php');



function activation(){
	Send24_Activation::activate();
}
function deactivation(){
	Send24_Deactivation::deactivate();
}

add_action( 'woocommerce_init', 'notify_wc_init' );
function notify_wc_init(){
	$isloaded = Send24_WC_Admin_Settings::is_wc_loaded();
	if (!$isloaded){
		Send24_WC_Admin_Settings::wc_loaded();
		Send24_Order_Creation::getInstance();
	}
}

add_action( 'woocommerce_shipping_init', 'send24_shipping_settings' );
function send24_shipping_settings(){
	$isloaded = Send24_WC_Admin_Settings::is_wc_loaded();

	if ($isloaded){
		require_once (dirname(__FILE__) . '/inc/Send24_Shipping_Method.php');
		//$send24_shipping = new Send24_Shipping_Method();
	}
}

add_filter( 'woocommerce_shipping_methods', 'add_send24_shipping_method' );
function add_send24_shipping_method( $methods ) {
	$methods['send24_logistics'] = 'Send24_Shipping_Method';
	return $methods;
}
// add_action('woocommerce_review_order_before_order_total', 'display_send24_shipping_widget');

register_activation_hook (__FILE__, 'activation');
register_deactivation_hook(__FILE__,'deactivation');







add_action( 'admin_enqueue_scripts', 'enqueue' );
function enqueue() {
	wp_enqueue_script( 'send24settings', plugins_url( '/assets/send24settings.js', __FILE__ ) );
}





//Get variant of send24 and close modal

add_action('wp_ajax_send24_get_selected_variant', 'send24_get_selected_variant');
add_action('wp_ajax_nopriv_send24_get_selected_variant', 'send24_get_selected_variant');
function send24_get_selected_variant(){
	if (isset($_POST['shipping_price']) && isset($_POST['selected_variant'])) {
		$shipping_price = $_POST['shipping_price'];
		$selected_variant = $_POST['selected_variant'];
		$selected_hub_id = $_POST['selected_hub'];


		// Set the new shipping price in WooCommerce session
		WC()->session->set('send24_shipping_rate', $shipping_price);
		WC()->session->set('send24_selected_hub_id', $selected_hub_id);
		WC()->session->set('send24_selected_variant', $selected_variant);

		wp_send_json_success();
	} else {
		wp_send_json_error('No shipping price set');
	}

	wp_die();
}

add_action('wp_ajax_send24_show_send24_modal', 'send24_show_send24_modal');
add_action('wp_ajax_nopriv_send24_show_send24_modal', 'send24_show_send24_modal');
function send24_show_send24_modal(){
	Send24_Modal::show_send24_modal();
	wp_die();
}


add_filter( 'allowed_http_origins', 'add_allowed_origins' );
function add_allowed_origins( $origins ) {
	$site_url = get_site_url();
    $origins[] = $site_url;
    return $origins;
}



//Force update the new rate
function wc_shipping_rate_cache_invalidation( $packages ) {
	foreach ( $packages as &$package ) {
		$package['rate_cache'] = wp_rand();
	}

	return $packages;
}
add_filter( 'woocommerce_cart_shipping_packages', 'wc_shipping_rate_cache_invalidation', 100 );

add_action('woocommerce_store_api_cart_update_customer_from_request', 'address_changed', 100, 2);

//Reset cache after user updates checkout details
function address_changed($customer, $request){
	\inc\Send24_Logger::write_log("Address changed!!! $customer");
	WC()->session->set('send24_shipping_rate', null);
	WC()->session->set('send24_user_cart_response', null);
	WC()->session->set('send24_selected_variant', null);
	WC()->session->set('send24_size', null);
	WC()->session->set('send24_is_fragile', null);
	WC()->session->set('send24_selected_hub_id', null);
}