<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class ArrayFormatter
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class ArrayFormatter {

	/**
	 * Return only specific parts of the data
	 *
	 * @param $data
	 * @param $keys
	 *
	 * @return array
	 */
	public static function only( $data, $keys ) {

		$valid = array();
		foreach ( $keys as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$valid[ $key ] = $data[ $key ];
			}
		}
		return $valid;
	}

}
