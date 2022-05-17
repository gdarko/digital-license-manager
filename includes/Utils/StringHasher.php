<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class StringHasher
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class StringHasher {

	/**
	 * Return random hash
	 * @return mixed|string|void
	 */
	public static function random() {
		if ( $hash = apply_filters( 'dlm_rand_hash', null ) ) {
			return $hash;
		}
		if ( function_exists( 'wc_rand_hash' ) ) {
			return wc_rand_hash();
		}
		if ( ! function_exists( 'openssl_random_pseudo_bytes' ) ) {
			return sha1( wp_rand() );
		}

		return bin2hex( openssl_random_pseudo_bytes( 20 ) );
	}

	/**
	 * Returns the activation hash
	 *
	 * @param $license_key
	 *
	 * @return string
	 */
	public static function activation( $license_key ) {
		return sha1( sprintf( '%s%s%s%s', $license_key, self::random(), mt_rand( 10000, 1000000 ), HttpHelper::clientIp() ) );
	}

	/**
	 * Hash license key
	 *
	 * @param $license_key
	 *
	 * @return mixed|void
	 */
	public static function license( $license_key ) {
		return CryptoHelper::hash( $license_key );
	}

	/**
	 * Generate a keyed hash value using the HMAC method
	 * @param $data
	 *
	 * @return false|string
	 */
	public static function make($data) {
		return hash_hmac( 'sha256', $data, 'wc-api' );
	}

}
