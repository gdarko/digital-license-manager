<?php


namespace IdeoLogix\DigitalLicenseManager\Enums;

/**
 * Class ActivationSource
 * @package IdeoLogix\DigitalLicenseManager\Enums
 */
abstract class ActivationSource {

	/**
	 * Enumerator value used for generators.
	 *
	 * @var int
	 */
	const WEB = 1;

	/**
	 * Enumerator value used for generators.
	 *
	 * @var int
	 */
	const API = 2;


	/**
	 * Format source
	 *
	 * @param int $src
	 *
	 * @return string
	 */
	public static function format( $src ) {
		$src = (int) $src;
		if ( $src === self::WEB ) {
			$str = __( 'Web', 'digital-license-manager' );
		} else if ( $src === self::API ) {
			$str = __( 'API', 'digital-license-manager' );
		} else {
			$str = __( 'Other', 'digital-license-manager' );
		}

		return $str;
	}

	/**
	 * Returns all sources formatted
	 * @return array
	 */
	public static function all() {
		$sources = array();
		foreach ( array( self::WEB, self::API ) as $source ) {
			$sources[ $source ] = self::format( $source );
		}

		return $sources;
	}

}