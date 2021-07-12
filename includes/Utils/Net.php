<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;


class Net {

	/**
	 * Return the client ip address
	 * @return array|false|string|null
	 */
	public static function clientIp() {

		$addr = null;
		if ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$addr = getenv( 'HTTP_CLIENT_IP' );
		} else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$addr = getenv( 'HTTP_X_FORWARDED_FOR' );
		} else if ( getenv( 'HTTP_X_FORWARDED' ) ) {
			$addr = getenv( 'HTTP_X_FORWARDED' );
		} else if ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			$addr = getenv( 'HTTP_FORWARDED_FOR' );
		} else if ( getenv( 'HTTP_FORWARDED' ) ) {
			$addr = getenv( 'HTTP_FORWARDED' );
		} else if ( getenv( 'REMOTE_ADDR' ) ) {
			$addr = getenv( 'REMOTE_ADDR' );
		}

		return $addr;

	}

	/**
	 * Return the client user agent
	 */
	public static function userAgent() {
		return isset( $_SERVER["HTTP_USER_AGENT"] ) ? $_SERVER["HTTP_USER_AGENT"] : null;
	}

}