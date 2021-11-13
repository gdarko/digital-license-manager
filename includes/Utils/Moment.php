<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;

use DateTime;

/**
 * Class Moment
 * - Date and time related helpers.
 *
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class Moment {

	/**
	 * Converts valid_for into expires_at.
	 *
	 * @param string $validFor
	 * @param string $format
	 *
	 * @return null|string
	 */
	public static function addDaysInFuture( $validFor, $format = 'Y-m-d H:i:s' ) {
		if ( ! empty( $validFor ) ) {
			try {
				$date         = new \DateTime( 'now', new \DateTimeZone( 'GMT' ) );
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
	 * Convert date to html
	 *
	 * @param $date_str
	 * @param false $expires
	 * @param bool $br
	 *
	 * @return string
	 */
	public static function toHtml( $date_str, $expires = false, $br = false ) {

		static $dateFormat = null;
		static $timeFormat = null;
		static $gmtOffset = null;

		if ( ! $dateFormat ) {
			$dateFormat = get_option( 'date_format' );
		}
		if ( ! $timeFormat ) {
			$timeFormat = get_option( 'time_format' );
		}

		if ( ! $gmtOffset ) {
			$gmtOffset = get_option( 'gmt_offset' );
		}

		try {

			$offsetSeconds  = floatval( $gmtOffset ) * 60 * 60;
			$timestampInput = strtotime( $date_str ) + $offsetSeconds;
			$datetimeString = date( 'Y-m-d H:i:s', $timestampInput );
			$dateInput      = new DateTime( $datetimeString );

			if ( $expires ) {

				if ( empty( $date_str ) || '0000-00-00 00:00:00' === $date_str ) {
					return sprintf(
						'<span class="dlm-date dlm-date-valid" title="%s">%s</span>%s',
						__( 'Never'. 'digital-license-manager' ),
						__( 'Never', 'digital-license-manager' ),
						$br ? '<br/>' : ''
					);

				} else {
					$timestampNow = strtotime( 'now' ) + $offsetSeconds;
					if ( $timestampNow >= $timestampInput ) {
						return sprintf(
							'<span class="dlm-date dlm-date-expired" title="%s">%s, %s</span>%s',
							__( 'Expired' ),
							$dateInput->format( $dateFormat ),
							$dateInput->format( $timeFormat ),
							$br ? '<br/>' : ''
						);
					} else {

						$diffSeconds = $timestampInput - $timestampNow;
						$statusClass = $diffSeconds > MONTH_IN_SECONDS ? 'dlm-date-valid' : 'dlm-date-expires-soon';

						return sprintf(
							'<span class="dlm-date %s" title="%s">%s, %s</span>%s',
							$statusClass,
							__( 'Active' ),
							$dateInput->format( $dateFormat ),
							$dateInput->format( $timeFormat ),
							$br ? '<br/>' : ''
						);
					}
				}
			}

			return sprintf(
				'<span class="dlm-date dlm-status">%s, %s</span>',
				$dateInput->format( $dateFormat ),
				$dateInput->format( $timeFormat )
			);
		} catch ( \Exception $e ) {
			return '';
		}
	}

}
