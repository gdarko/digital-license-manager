<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-present  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-present  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

namespace IdeoLogix\DigitalLicenseManager\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class HttpHelper
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class HttpHelper {

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

		/**
		 * When the site is behind CloudFlare it returns comma separated IP addresses,
		 * In this case, we only need the first ip address to be returned.
		 */
		if ( ! is_null( $addr ) && strpos( $addr, ',' ) !== false ) {
			$addr = trim( current( preg_split( '/,/', sanitize_text_field( wp_unslash( $addr ) ) ) ) );
		}

		return $addr;

	}

	/**
	 * Return the client user agent
	 * @return string|null
	 */
	public static function userAgent() {
		return isset( $_SERVER["HTTP_USER_AGENT"] ) ? sanitize_text_field( wp_unslash( $_SERVER["HTTP_USER_AGENT"] ) ) : null;
	}

	/**
	 * Return's the request method
	 * @return string|null
	 */
	public static function requestMethod() {
		return isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : null;
	}

	/**
	 * Returns the request uri
	 * @return string|null
	 */
	public static function requestUri() {
		return isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : null;
	}

	/**
	 * Redirects to specific url
	 * @return void
	 */
	public static function redirect( $url ) {

		if ( ! $url ) {
			return;
		}

		wp_redirect( $url );
		exit;

	}

}
