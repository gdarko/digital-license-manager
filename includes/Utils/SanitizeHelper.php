<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2023  Darko Gjorgjijoski. All Rights Reserved.
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

class SanitizeHelper {

	/**
	 * Object sanitization
	 *
	 * @param $data
	 * @param null $text_callback
	 *
	 * @return array|mixed
	 */
	public static function sanitizeComplex( $data, $text_callback = null ) {

		if ( ! is_array( $data ) && ! is_object( $data ) ) {
			return $data;
		}

		$is_array = is_array( $data );

		foreach ( $data as $dataKey => $value ) {
			if ( is_numeric( $value ) ) {
				$value = self::sanitizeNumeric( $value );
			} else {

				if ( is_array( $value ) || is_object( $value ) ) {
					$value = self::sanitizeComplex( $value, $text_callback );
				} else {
					$value = $text_callback && is_callable( $text_callback ) ? call_user_func_array( $text_callback, [ $dataKey, $value ] ) : sanitize_text_field( $value );
				}
			}

			if ( $is_array && isset( $data[ $dataKey ] ) ) {
				$data[ $dataKey ] = $value;
			} else if ( isset( $data->$dataKey ) ) {
				$data->$dataKey = $value;
			}
		}

		return $data;
	}


	/**
	 * Sanitizes numeric value
	 *
	 * @param int|string $value
	 *
	 * @return int|string
	 */
	public static function sanitizeNumeric( $value ) {

		if ( ! is_string( $value ) ) {
			return $value;
		}

		$dot_count = substr_count( $value, '.' );
		if ( $dot_count === 1 ) {
			$value = doubleval( $value );
		} else if ( $dot_count === 0 ) {
			$value = intval( $value );
		}

		return $value;
	}

	/**
	 * Return's the allowed html tags (same list as WP default, but added li,ul,ol,p,and a[_target] support.
	 * @return array
	 */
	public static function ksesAllowedHtmlTags() {
		return apply_filters( 'dlm_kses_allowed_html_tags', array(
			'a'          => array( 'href' => true, 'title' => true, 'target' => true, ),
			'abbr'       => array( 'title' => true, ),
			'acronym'    => array( 'title' => true, ),
			'b'          => array(),
			'blockquote' => array( 'cite' => true, ),
			'cite'       => array(),
			'code'       => array(),
			'del'        => array( 'datetime' => true, ),
			'em'         => array(),
			'i'          => array(),
			'q'          => array( 'cite' => true, ),
			's'          => array(),
			'strike'     => array(),
			'strong'     => array(),
			'p'          => array(),
			'ul'         => array(),
			'ol'         => array(),
			'li'         => array(),
		) );
	}

}