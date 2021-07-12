<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;

/**
 * Class Arr
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class Arr {

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