<?php
/* @var int $migrationMode */

use IdeoLogix\DigitalLicenseManager\Database\Migrator;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
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
			if ( ! empty( $license['valid_for'] ) && empty( $license['expires_at'] ) && ! empty( $license['created_at'] ) ) {
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
		Migrator::maybe_drop_column( $tbl_licenses, 'valid_for');
	}
}
