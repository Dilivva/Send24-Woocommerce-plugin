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
	\inc\Send24_Activation::activate();
}
function deactivation(){
	\inc\Send24_Deactivation::deactivate();
}

function notify_wc_init(){
	$isloaded = \inc\settings\Send24_WC_Admin_Settings::is_wc_loaded();
	if (!$isloaded){
		\inc\settings\Send24_WC_Admin_Settings::wc_loaded();
	}
}

function send24_shipping_settings(){
	$isloaded = \inc\settings\Send24_WC_Admin_Settings::is_wc_loaded();

	if ($isloaded){
		require_once (dirname(__FILE__) . '/inc/Send24_Shipping_Method.php');
		//$send24_shipping = new Send24_Shipping_Method();
	}
}
function add_send24_shipping_method( $methods ) {
	$methods['send24_logistics'] = 'Send24_Shipping_Method';
	return $methods;
}
// add_action('woocommerce_review_order_before_order_total', 'display_send24_shipping_widget');

register_activation_hook (__FILE__, 'activation');
register_deactivation_hook(__FILE__,'deactivation');

add_action( 'woocommerce_init', 'notify_wc_init' );
add_action( 'woocommerce_shipping_init', 'send24_shipping_settings' );



add_filter( 'woocommerce_shipping_methods', 'add_send24_shipping_method' );


function send24_initialize_order_creation() {
    new Send24_Order_Creation();
}
add_action('woocommerce_init', 'send24_initialize_order_creation');


function enqueue() {
	wp_enqueue_script( 'send24settings', plugins_url( '/assets/send24settings.js', __FILE__ ) );
}

add_action( 'admin_enqueue_scripts', 'enqueue' );



//Get variant of send24 and close modal

add_action('wp_ajax_send24_get_selected_variant', 'send24_get_selected_variant');
add_action('wp_ajax_nopriv_send24_get_selected_variant', 'send24_get_selected_variant');
function send24_get_selected_variant(){
	if (isset($_POST['shipping_price']) && isset($_POST['selected_variant'])) {
		$shipping_price = $_POST['shipping_price'];
		$selected_variant = $_POST['selected_variant'];
		$selected_hub_id = $_POST['selected_hub'];

		\inc\Send24_Logger::write_log("Result: $selected_variant, $selected_hub_id");


		// Set the new shipping price in WooCommerce session
		WC()->session->set('send24_shipping_rate', $shipping_price);
		WC()->session->set('send24_selected_hub_id', $selected_hub_id);


		$rate = array(
			'label' => 'Send24 Shipping',
			'cost' => $shipping_price,
			'calc_tax' => 'per_order'
		);

		$method = WC()->shipping()->get_shipping_methods()['send24_logistics'];
		$method->add_rate($rate);
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
	\inc\Send24_Logger::write_log("Url found: $site_url");
    return $origins;
}

add_filter( 'woocommerce_calculated_total', 'my_custom_shipping_calculation', 10, 2 );
function my_custom_shipping_calculation( $total, $cart ) {
	// Get the cart total
	$shipping_total = $cart->get_shipping_total();

	\inc\Send24_Logger::write_log("Ships: $shipping_total, $total");

	return $total;
}




//function filter_need_shipping($val) {
//	// Check if we need to recalculate shipping
//	if (!is_admin()) {
//		$prevent_after_add = WC()->session->get('prevent_recalc_on_add_to_cart');
//		return $val && !$prevent_after_add;
//	}
//	return $val;
//}
//add_filter('woocommerce_cart_needs_shipping', 'filter_need_shipping');
//
//function mark_cart_not_to_recalc() {
//	// Mark the cart not to recalculate when adding items
//	if (!is_admin()) {
//		WC()->session->__unset('prevent_recalc_on_add_to_cart');
//	}
//}
//add_action('woocommerce_before_calculate_totals', 'mark_cart_not_to_recalc');
//
//
//// Unset the flag when proceeding to checkout
//add_action('woocommerce_checkout_init', 'unset_prevent_recalc_flag');
//function unset_prevent_recalc_flag() {
//	if (!is_admin()) {
//		WC()->session->__unset('prevent_recalc_on_add_to_cart');
//	}
//}