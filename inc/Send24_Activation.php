<?php
namespace inc;
use inc\Send24_Logger;

class Send24_Activation{

	public static function activate(){
		add_option('send_private_key','');
		add_option('send_public_key','');

		wp_enqueue_script( 'send24settings', plugins_url( '/assets/send24settings.js', __FILE__ ) );
	}
}
