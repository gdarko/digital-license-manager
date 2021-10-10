<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;

/**
 * Class Formatter
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class Formatter {

	/**
	 *
	 * @param $size
	 *
	 * @return string
	 */
	public static function formatBytes( $size ) {
		$base   = log( $size ) / log( 1024 );
		$suffix = array( "", "KB", "MB", "GB", "TB" );
		$f_base = floor( $base );

		return round( pow( 1024, $base - floor( $base ) ), 1 ) . $suffix[ $f_base ];
	}

}
