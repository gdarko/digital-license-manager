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

/* @var int $migrationMode */

use IdeoLogix\DigitalLicenseManager\Database\Migrator;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;

defined( 'ABSPATH' ) || exit;

/**
 * Upgrade script
 */
if ( $migrationMode === Migrator::MODE_UP ) {
	global $wpdb;
	$tbl_licenses = $wpdb->prefix . DatabaseTable::LICENSES;
	$licenses     = $wpdb->get_results( "SELECT * FROM {$tbl_licenses}", ARRAY_A );
	if ( ! empty( $licenses ) ) {
		foreach ( $licenses as $license ) {
			if ( ! empty( $license['valid_for'] ) && empty( $license['expires_at'] ) && ! empty( $license['created_at'] ) && intval( $license['status'] ) !== LicenseStatus::ACTIVE ) {
				$expiresAt = DateFormatter::addDaysInFuture( $license['valid_for'], $license['created_at'], 'Y-m-d H:i:s' );
				$wpdb->update(
					$tbl_licenses,
					array(
						'expires_at' => $expiresAt
					),
					array(
						'id' => $license['id']
					),
					array(
						'%s'
					),
					array(
						'%d',
					)
				);
			}
		}
	}

	return Migrator::maybe_drop_column( $tbl_licenses, 'valid_for' );
}

return false;

