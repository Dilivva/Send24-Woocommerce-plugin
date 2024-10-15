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
require_once (dirname(__FILE__) . '/inc/settings/Send24_WC_Admin_Settings.php');

require_once (dirname(__FILE__) . '/inc/Send24_API.php');

require_once (dirname(__FILE__) . '/inc/Send24_Order_Creation.php');


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

