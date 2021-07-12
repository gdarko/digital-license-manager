<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;

/**
 * Class Str
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class Str {

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