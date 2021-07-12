<?php

namespace IdeoLogix\DigitalLicenseManager\Enums;

use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License;
use ReflectionClass;

defined( 'ABSPATH' ) || exit;

/**
 * Class LicenseStatus
 * @package IdeoLogix\DigitalLicenseManager\Enums
 */
abstract class LicenseStatus {
	/**
	 * Enumerator value used for sold licenses.
	 *
	 * @var int
	 */
	const SOLD = 1;

	/**
	 * Enumerator value used for delivered licenses.
	 *
	 * @var int
	 */
	const DELIVERED = 2;

	/**
	 * Enumerator value used for active licenses.
	 *
	 * @var int
	 */
	const ACTIVE = 3;

	/**
	 * Enumerator value used for inactive licenses.
	 *
	 * @var int
	 */
	const INACTIVE = 4;

	/**
	 * Enumerator value used for disabled licenses.
	 *
	 * @var int
	 */
	const DISABLED = 5;

	/**
	 * Available enumerator values.
	 *
	 * @var array
	 */
	public static $status = array(
		self::SOLD,
		self::DELIVERED,
		self::ACTIVE,
		self::INACTIVE,
		self::DISABLED
	);

	/**
	 * Available text representations of the enumerator
	 *
	 * @var array
	 */
	public static $enumArray = array(
		'sold',
		'delivered',
		'active',
		'inactive',
		'disabled'
	);

	/**
	 * Key/value pairs of text representations and actual enumerator values.
	 *
	 * @var array
	 */
	public static $values = array(
		'sold'      => self::SOLD,
		'delivered' => self::DELIVERED,
		'active'    => self::ACTIVE,
		'inactive'  => self::INACTIVE,
		'disabled'  => self::DISABLED
	);

	/**
	 * Returns the string representation of a specific enumerator value.
	 *
	 * @param int $status Status enumerator value
	 *
	 * @return string
	 */
	public static function getExportLabel( $status ) {
		$labels = array(
			self::SOLD      => 'SOLD',
			self::DELIVERED => 'DELIVERED',
			self::ACTIVE    => 'ACTIVE',
			self::INACTIVE  => 'INACTIVE',
			self::DISABLED  => 'DISABLED'
		);

		return $labels[ $status ];
	}

	/**
	 * Returns an array of enumerators to be used as a dropdown.
	 *
	 * @return array
	 */
	public static function dropdown() {
		return array(
			array(
				'value' => self::ACTIVE,
				'name'  => __( 'Active', 'digital-license-manager' )
			),
			array(
				'value' => self::INACTIVE,
				'name'  => __( 'Inactive', 'digital-license-manager' )
			),
			array(
				'value' => self::SOLD,
				'name'  => __( 'Sold', 'digital-license-manager' )
			),
			array(
				'value' => self::DELIVERED,
				'name'  => __( 'Delivered', 'digital-license-manager' )
			),
			array(
				'value' => self::DISABLED,
				'name'  => __( 'Disabled', 'digital-license-manager' )
			)
		);
	}

	/**
	 * Returns the class constants as an array.
	 *
	 * @return array
	 */
	public static function getConstants() {
		$oClass = new ReflectionClass( __CLASS__ );

		return $oClass->getConstants();
	}

	/**
	 * Show the license status
	 *
	 * @param License $license
	 */
	public static function toHtml( $license ) {

		if ( empty( $license ) ) {
			return sprintf(
				'<div class="dlm-status unknown">%s</div>',
				__( 'Unknown', 'digital-license-manager' )
			);
		}

		$status = $license->getStatus();

		return self::statusToHtml( $status );
	}

	/**
	 * Returns the license status
	 *
	 * @param $status
	 *
	 * @return string
	 */
	public static function statusToHtml( $status ) {
		switch ( $status ) {
			case LicenseStatus::SOLD:
				$markup = sprintf(
					'<div class="dlm-status sold"><span class="dashicons dashicons-saved"></span> %s</div>',
					__( 'Sold&nbsp;&nbsp;&nbsp;', 'digital-license-manager' )
				);
				break;
			case LicenseStatus::DELIVERED:
				$markup = sprintf(
					'<div class="dlm-status delivered"><span class="dashicons dashicons-saved"></span> %s</div>',
					__( 'Delivered', 'digital-license-manager' )
				);
				break;
			case LicenseStatus::ACTIVE:
				$markup = sprintf(
					'<div class="dlm-status active"><span class="dashicons dashicons-marker"></span> %s</div>',
					__( 'Active', 'digital-license-manager' )
				);
				break;
			case LicenseStatus::INACTIVE:
				$markup = sprintf(
					'<div class="dlm-status inactive"><span class="dashicons dashicons-marker"></span> %s</div>',
					__( 'Inactive', 'digital-license-manager' )
				);
				break;
			case LicenseStatus::DISABLED:
				$markup = sprintf(
					'<div class="dlm-status disabled"><span class="dashicons dashicons-warning"></span> %s</div>',
					__( 'Disabled', 'digital-license-manager' )
				);
				break;
			default:
				$markup = sprintf(
					'<div class="dlm-status unknown">%s</div>',
					__( 'Unknown', 'digital-license-manager' )
				);
				break;
		}

		return $markup;
	}
}
