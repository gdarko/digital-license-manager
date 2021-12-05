<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class StringFormatter
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class StringFormatter {

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

	/**
	 *  Converts dashes to camel case with first capital letter.
	 *
	 * @param $input
	 * @param string $separator
	 *
	 * @return array|string|string[]
	 */
	public static function camelize( $input, $separator = '_' ) {
		return str_replace( $separator, '', ucwords( $input, $separator ) );
	}

}
