<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;

/**
 * Class Json
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class Json {

	/**
	 * Decodes object. If data is not valid JSON, returns data.
	 *
	 * @param $data
	 * @param bool $associative
	 *
	 * @return mixed
	 */
	public static function decode( $data, $associative = false ) {
		$result = json_decode( $data, $associative );

		return json_last_error() === JSON_ERROR_NONE ? $result : $data;
	}

	/**
	 * Encodes object.
	 * @param $data
	 *
	 * @return bool|float|int|string
	 */
	public static function encode( $data ) {
		if ( is_scalar( $data ) ) {
			return $data;
		} else {
			return json_encode( $data );
		}
	}

}