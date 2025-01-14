<?php

namespace send24_inc;

class Send24_Logger {

	public static function write_log( $data ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $data ) || is_object( $data ) ) {
				error_log( print_r( $data, true ) );
			} else {
				error_log( $data );
			}
		}
	}
}


