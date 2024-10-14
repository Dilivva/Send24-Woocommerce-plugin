<?php

namespace inc;

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


// namespace inc;

// class Send24_Logger {

//     public static function write_log($data) {
//         if (true === WP_DEBUG) {
//             $log_file = plugin_dir_path(__FILE__) . 'send24_log.txt'; // Specify the path to your log file
//             if (is_array($data) || is_object($data)) {
//                 $data = print_r($data, true);
//             }
//             $data = date('Y-m-d H:i:s') . " - " . $data . "\n"; // Prepend timestamp
//             file_put_contents($log_file, $data, FILE_APPEND); // Append to log file
//         }
//     }
// }
