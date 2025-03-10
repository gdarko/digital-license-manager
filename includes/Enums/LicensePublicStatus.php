<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2025  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-2025  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
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

class LicensePublicStatus {

	/**
	 * Licenses that are either "DELIVERED" or "SOLD" with expiration date set and are currently EXPIRED.
	 */
	const VALID = 'valid';

	/**
	 * Licenses that are either "DISABLED" or "INACTIVE"
	 */
	const INVALID = 'invalid';

	/**
	 * Licenses that are either "DELIVERED" or "SOLD" with expiration date set or permanent expiration.
	 */
	const EXPIRED = 'expired';

	/**
	 * Licenses that are other status
	 */
	const UNKNOWN = 'unknown';

	/**
	 * Icon mapping
	 * @var string[]
	 */
	public static $icons = [
		self::VALID   => 'ok-circled',
		self::INVALID => 'info-circled',
		self::EXPIRED => 'attention-circled',
		self::UNKNOWN => 'help-circled',
	];

	/**
	 * Return the label
	 *
	 * @param $key
	 *
	 * @return string
	 */
	public static function getLabel( $key ) {
		switch ( $key ) {
			case 'valid':
				return esc_html__( 'Valid', 'digital-license-manager' );
			case 'invalid':
				return esc_html__( 'Invalid', 'digital-license-manager' );
			case 'expired':
				return esc_html__( 'Expired', 'digital-license-manager' );
			default:
				return esc_html__( 'Unknown', 'digital-license-manager' );
		}
	}

	/**
	 * Show the license status
	 *
	 * @param License $license
	 * @param array $args
	 *
	 * @return string
	 */
	public static function toHtml( License $license, array $args = [] ) {

		$wrapper = isset( $args['inline'] ) && $args['inline'] ? 'inline' : 'full';

		if ( in_array( $license->getStatus(), [ LicensePrivateStatus::SOLD, LicensePrivateStatus::DELIVERED ] ) ) {
			if ( $license->isExpired() ) {
				$status = self::EXPIRED;
			} else {
				$status = self::VALID;
			}
		} else if ( in_array( $license->getStatus(), [ LicensePrivateStatus::INACTIVE, LicensePrivateStatus::DISABLED ] ) ) {
			$status = self::INVALID;
		} else {
			$status = self::UNKNOWN;
		}

		$label = self::getLabel( $status );
		$icon  = self::$icons[ $status ];

		return sprintf( '<div class="dlm-license-status dlm-license-status--%s dlm-license-status--%s">
        <span class="dlm-license-status-icon dlm-icon-%s"></span>
        <span class="dlm-license-status-word">%s</span>
    </div>', $status, $wrapper, $icon, $label );

	}


}