<?php

namespace send24_inc\settings;

class Send24_WC_Admin_Settings {

	private static $is_wc_loaded = false;

	public static function wc_loaded(){
		self::$is_wc_loaded = true;
	}

	public static function is_wc_loaded(){
		return self::$is_wc_loaded;
	}

}