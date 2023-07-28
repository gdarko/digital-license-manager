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

namespace IdeoLogix\DigitalLicenseManager\Enums;

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use ReflectionClass;

/**
 * Class LicenseStatus
 * @package IdeoLogix\DigitalLicenseManager\Enums
 *
 * Code inspired by "License Manager for WooCommerce" plugin
 * @copyright  2019-2022 Drazen Bebic
 * @copyright  2022-2023 WPExperts.io
 * @copyright  2020-2023 Darko Gjorgjijoski
 *
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
	public static function getLabel( $status ) {
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
	public static function toHtml( $license, $args = [] ) {

		$status = ! empty( $license ) ? $license->getStatus() : 'unknown';

		return self::statusToHtml( $status, $args );
	}

	/**
	 * Creates "Expired" status
	 * @return string
	 */
	public static function toHtmlExpired( $license, $args = [] ) {

		$args     = wp_parse_args( $args, [ 'style' => 'normal', 'text' => '' ] );
		$cssClass = $args['style'] === 'normal' ? 'dlm-status' : 'dlm-status-' . $args['style'];

		return sprintf(
			'<div class="%s dlm-status-inactive"><span class="dashicons dashicons-marker"></span> %s</div>',
			$cssClass,
			! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Expired', 'digital-license-manager' )
		);
	}

	/**
	 * Returns the license status
	 *
	 * @param $status
	 * @param array $args
	 *
	 * @return string
	 */
	public static function statusToHtml( $status, $args = [] ) {

		$args     = wp_parse_args( $args, [ 'style' => 'normal', 'text' => '' ] );
		$cssClass = $args['style'] === 'normal' ? 'dlm-status' : 'dlm-status-' . $args['style'];

		switch ( $status ) {
			case 'sold':
			case LicenseStatus::SOLD:
				$markup = sprintf(
					'<div class="%s dlm-status-sold"><span class="dashicons dashicons-saved"></span> %s</div>',
					$cssClass,
					! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Sold&nbsp;&nbsp;&nbsp;', 'digital-license-manager' )
				);
				break;
			case 'delivered':
			case LicenseStatus::DELIVERED:
				$markup = sprintf(
					'<div class="%s dlm-status-delivered"><span class="dashicons dashicons-saved"></span> %s</div>',
					$cssClass,
					! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Delivered', 'digital-license-manager' )
				);
				break;
			case 'active':
			case LicenseStatus::ACTIVE:
				$markup = sprintf(
					'<div class="%s dlm-status-active"><span class="dashicons dashicons-marker"></span> %s</div>',
					$cssClass,
					! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Active', 'digital-license-manager' )
				);
				break;
			case 'inactive':
			case LicenseStatus::INACTIVE:
				$markup = sprintf(
					'<div class="%s dlm-status-inactive"><span class="dashicons dashicons-marker"></span> %s</div>',
					$cssClass,
					! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Inactive', 'digital-license-manager' )
				);
				break;
			case 'disabled':
			case LicenseStatus::DISABLED:
				$markup = sprintf(
					'<div class="%s dlm-status-disabled"><span class="dashicons dashicons-warning"></span> %s</div>',
					$cssClass,
					! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Disabled', 'digital-license-manager' )
				);
				break;
			default:
				$markup = sprintf(
					'<div class="%s dlm-status-unknown">%s</div>',
					$cssClass,
					__( 'Unknown', 'digital-license-manager' )
				);
				break;
		}

		return $markup;
	}


	/**
	 * Returns the status based on input
	 *
	 * @param $input
	 *
	 * @return int|mixed
	 */
	public static function inputToStatus( $input ) {
		$input  = strtolower( $input );
		$status = self::INACTIVE;

		return isset( self::$values[ $input ] ) ? self::$values[ $input ] : $status;
	}

}
