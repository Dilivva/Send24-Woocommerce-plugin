<?php
namespace send24_inc;

class Send24_Activation {

	public static function activate() {
		add_option('send24_private_key', '');
		add_option('send24_public_key', '');

		wp_enqueue_script(
			'send24settings',
			plugins_url('/assets/send24settings.js', __FILE__)
		);
	}
}
