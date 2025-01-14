<?php
/**
 * @package  Send24 Logistics
 */
/*
Plugin Name: Send24 Logistics
Plugin URI: https://send24.co
Description: 24 hour nation-wide delivery at your finger tips.
Version: 1.0.0
Requires PHP: 7.4
Requires at least: 6.3
Author: send24app
Author URI: https://github.com/dilivva
License: GPLv2 or later
Text Domain: send24
Copyright: © 2024 Send24 Logistics
Icon: assets/logo.png
*/


// If this file is called firectly, abort!!!
use send24_inc\Send24_Activation;
use send24_inc\Send24_Deactivation;
use send24_inc\settings\Send24_WC_Admin_Settings;

defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );

require_once (dirname(__FILE__) . '/send24_inc/Send24_Activation.php');
require_once (dirname(__FILE__) . '/send24_inc/Send24_Deactivation.php');
require_once (dirname(__FILE__) . '/send24_inc/Send24_Logger.php');
require_once (dirname(__FILE__) . '/send24_inc/Send24_Modal.php');
require_once (dirname(__FILE__) . '/send24_inc/settings/Send24_WC_Admin_Settings.php');

require_once (dirname(__FILE__) . '/send24_inc/Send24_API.php');

require_once (dirname(__FILE__) . '/send24_inc/Send24_Order_Creation.php');



function send24_activation() {
	Send24_Activation::activate();
}

function send24_deactivation() {
	Send24_Deactivation::deactivate();
}

add_action('woocommerce_init', 'send24_notify_wc_init');
function send24_notify_wc_init() {
	$isloaded = Send24_WC_Admin_Settings::is_wc_loaded();
	if (!$isloaded) {
		Send24_WC_Admin_Settings::wc_loaded();
		Send24_Order_Creation::getInstance();
	}
}

add_action( 'woocommerce_shipping_init', 'send24_shipping_settings' );
function send24_shipping_settings(){
	$isloaded = Send24_WC_Admin_Settings::is_wc_loaded();

	if ($isloaded){
		require_once (dirname(__FILE__) . '/send24_inc/Send24_Shipping_Method.php');
		//$send24_shipping = new Send24_Shipping_Method();
	}
}

add_filter( 'woocommerce_shipping_methods', 'send24_add_send24_shipping_method' );
function send24_add_send24_shipping_method( $methods ) {
	$methods['send24_logistics'] = 'Send24_Shipping_Method';
	return $methods;
}
// add_action('woocommerce_review_order_before_order_total', 'display_send24_shipping_widget');

register_activation_hook(__FILE__, 'send24_activation');
register_deactivation_hook(__FILE__, 'send24_deactivation');







add_action( 'admin_enqueue_scripts', 'send24_enqueue' );
function send24_enqueue() {
	wp_enqueue_script( 'send24settings', plugins_url( '/assets/js/send24settings.js', __FILE__ ) );

	wp_localize_script('send24settings', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('send24_nonce_action') // Creates nonce for AJAX security
    ));
}

add_action('wp_enqueue_scripts', 'send24_load_checkout_script');

function send24_load_checkout_script() {
	if (is_checkout()) { // Enqueue only on the WooCommerce checkout page
		wp_enqueue_style(
			'modal',
			plugins_url('/assets/css/modal.css', __FILE__)
		);

		wp_enqueue_script('jquery');

		wp_enqueue_script('send24script', plugins_url('/assets/js/send24script.js', __FILE__), array('jquery'), null, true);
		wp_enqueue_script('send24checkout', plugins_url('/assets/js/send24checkout.js', __FILE__), array('jquery'), null, true);

		$localize_data = [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce'    => wp_create_nonce('send24_nonce'),
		];

		wp_localize_script('send24script', 'ajax_object', $localize_data);
		wp_localize_script('send24checkout', 'ajax_object', $localize_data);
	}
}



add_action('wp_ajax_send24_get_selected_variant', 'send24_get_selected_variant');
add_action('wp_ajax_nopriv_send24_get_selected_variant', 'send24_get_selected_variant');
function send24_get_selected_variant() {
    if (isset($_POST['shipping_price'], $_POST['selected_variant'], $_POST['selected_hub'])) {

		$nonce = $_REQUEST['security']; 
	    if ( ! wp_verify_nonce( $nonce, 'send24_nonce' ) ) { 
    	    wp_send_json_error( 'Invalid nonce.' );
        	wp_die(); 
    	}

        $shipping_price = sanitize_text_field($_POST['shipping_price']);
        $selected_variant = sanitize_text_field($_POST['selected_variant']);
        $selected_hub_id = sanitize_text_field($_POST['selected_hub']);

        if (!is_numeric($shipping_price) || $shipping_price <= 0) {
            wp_send_json_error('Invalid shipping price.');
            wp_die();
        }

        if (empty($selected_variant) || !is_string($selected_variant)) {
            wp_send_json_error('Invalid selected variant.');
            wp_die();
        }

        if (empty($selected_hub_id) || !is_string($selected_hub_id)) {
            wp_send_json_error('Invalid selected hub.');
            wp_die();
        }

        WC()->session->set('send24_shipping_rate', $shipping_price);
        WC()->session->set('send24_selected_hub_id', $selected_hub_id);
        WC()->session->set('send24_selected_variant', $selected_variant);

        wp_send_json_success();
    } else {
        wp_send_json_error('Required fields are missing.');
    }

    wp_die();
}


add_action('wp_ajax_send24_show_send24_modal', 'send24_show_send24_modal');
add_action('wp_ajax_nopriv_send24_show_send24_modal', 'send24_show_send24_modal');
function send24_show_send24_modal(){
	Send24_Modal::show_send24_modal();
	wp_die();
}


add_filter( 'allowed_http_origins', 'send24_add_allowed_origins' );
function send24_add_allowed_origins( $origins ) {
	$site_url = get_site_url();
    $origins[] = $site_url;
    return $origins;
}



//Force update the new rate
function send24_wc_shipping_rate_cache_invalidation( $packages ) {
	foreach ( $packages as &$package ) {
		$package['rate_cache'] = wp_rand();
	}

	return $packages;
}
add_filter( 'woocommerce_cart_shipping_packages', 'send24_wc_shipping_rate_cache_invalidation', 100 );

add_action('woocommerce_store_api_cart_update_customer_from_request', 'address_changed', 100, 2);

//Reset cache after user updates checkout details
function send24_address_changed($customer, $request){
	\send24_inc\Send24_Logger::write_log("Address changed!!! $customer");
	WC()->session->set('send24_shipping_rate', null);
	WC()->session->set('send24_user_cart_response', null);
	WC()->session->set('send24_selected_variant', null);
	WC()->session->set('send24_size', null);
	WC()->session->set('send24_is_fragile', null);
	WC()->session->set('send24_selected_hub_id', null);
}