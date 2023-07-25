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

use DateTime;
use IdeoLogix\DigitalLicenseManager\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Class DateFormatter
 * - Date and time related helpers.
 *
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class DateFormatter {

	/**
	 * The expiration date and time format.
	 *
	 * @see https://www.php.net/manual/datetime.format.php
	 *
	 * @var string
	 */
	protected static $expiration_format;

	/**
	 * Converts valid_for into expires_at.
	 *
	 * @param string $validFor
	 * @param string $format
	 *
	 * @return null|string
	 */
	public static function addDaysInFuture( $validFor, $addTo = 'now', $format = 'Y-m-d H:i:s' ) {
		if ( ! empty( $validFor ) ) {
			try {
				$date         = new \DateTime( $addTo, new \DateTimeZone( 'GMT' ) );
				$dateInterval = new \DateInterval( 'P' . $validFor . 'D' );
			} catch ( \Exception $e ) {
				return null;
			}

			return $date->add( $dateInterval )->format( $format );
		}

		return null;
	}


	/**
	 * Convert date form one format to another format.
	 *
	 * @param $value
	 * @param $srcFormat
	 * @param string $targetFormat
	 *
	 * @return string
	 */
	public static function convert( $value, $srcFormat, $targetFormat = 'system' ) {
		$dt = \DateTime::createFromFormat( $srcFormat, $value );

		return 'system' === $targetFormat ? $dt->format( get_option( 'date_format' ) ) : $dt->format( $targetFormat );
	}

	/**
	 * Returns a format string for expiration dates.
	 *
	 * @return string
	 */
	public static function getExpirationFormat() {

		if ( empty( self::$expiration_format ) ) {

			$expiration_format = Settings::get( 'expiration_format' );
			if ( false === $expiration_format ) {
				$expiration_format = '{{DATE_FORMAT}}, {{TIME_FORMAT}} T';
			}

			if ( strpos( $expiration_format, '{{DATE_FORMAT}}' ) !== false ) {
				$date_format       = get_option( 'date_format', 'F j, Y' );
				$expiration_format = str_replace( '{{DATE_FORMAT}}', $date_format, $expiration_format );
			}

			if ( strpos( $expiration_format, '{{TIME_FORMAT}}' ) !== false ) {
				$time_format       = get_option( 'time_format', 'g:i a' );
				$expiration_format = str_replace( '{{TIME_FORMAT}}', $time_format, $expiration_format );
			}

			self::$expiration_format = $expiration_format;
		}

		return self::$expiration_format;
	}

	/**
	 * Convert date to html
	 *
	 * @param $date_str
	 * @param false $expires
	 * @param bool $br
	 *
	 * @return string
	 */
	public static function toHtml( $date_str, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'br'      => false,
			'expires' => false,
			'never'   => '',
		) );


		if ( empty( $args['never'] ) ) {
			$args['never'] = __( 'Never', 'digital-license-manager' );
		}


		if ( $args['expires'] ) {

			if ( empty( $date_str ) || '0000-00-00 00:00:00' === $date_str ) {
				return sprintf(
					'<span class="dlm-date dlm-date-valid" title="%s">%s</span>%s',
					$args['never'],
					$args['never'],
					$args['br'] ? '<br/>' : ''
				);

			} else {
				$timestampInput = strtotime( $date_str );
				$timestampNow   = strtotime( 'now' );
				if ( $timestampNow >= $timestampInput ) {
					return sprintf(
						'<span class="dlm-date dlm-date-expired" title="%s">%s</span>%s',
						__( 'Expired' ),
						wp_date( DateFormatter::getExpirationFormat(), $timestampInput ),
						$args['br'] ? '<br/>' : ''
					);
				} else {

					$diffSeconds = $timestampInput - $timestampNow;
					$statusClass = $diffSeconds > MONTH_IN_SECONDS ? 'dlm-date-valid' : 'dlm-date-expires-soon';

					return sprintf(
						'<span class="dlm-date %s" title="%s">%s</span>%s',
						$statusClass,
						__( 'Active' ),
						wp_date( DateFormatter::getExpirationFormat(), $timestampInput ),
						$args['br'] ? '<br/>' : ''
					);
				}
			}
		}

		return sprintf(
			'<span class="dlm-date dlm-date-valid" title="%s">%s</span>%s',
			$args['never'],
			$args['never'],
			$args['br'] ? '<br/>' : ''
		);
	}


	/**
	 * Converts PHP time format tokens to Flatpickr time format tokens
	 *
	 * @param $format
	 *
	 * @url https://www.php.net/manual/en/datetime.format.php (Time)
	 * @url https://flatpickr.js.org/formatting/#time-formatting-tokens
	 *
	 * @since 1.4.0
	 *
	 * @return array|string|string[]
	 */
	public static function convertTimeFormatForFlatpickr( $format ) {
		$phpfpmap = [
			'A' => 'K',
			'g' => 'h',
			'G' => 'H',
			'h' => 'G',
			'H' => 'H',
			'a' => 'K',
			'i' => 'i',
			's' => 'S'
		];

		foreach ( $phpfpmap as $phpF => $fpF ) {
			$format = str_replace( $phpF, $fpF, $format );
		}

		return $format;
	}


	/**
	 * Validates date/time string based on format
	 *
	 * @param $format
	 * @param $date
	 *
	 * @since 1.4.0
	 *
	 * @return bool
	 */
	public static function validate( $format, $date ) {
		return ( DateTime::createFromFormat( $format, $date ) !== false );
	}
}
