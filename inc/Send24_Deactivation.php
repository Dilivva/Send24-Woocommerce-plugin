<?php

namespace inc;

class Send24_Deactivation{

	public static function deactivate(){
		delete_option('send_private_key');
		delete_option('send_public_key');
	}
}
