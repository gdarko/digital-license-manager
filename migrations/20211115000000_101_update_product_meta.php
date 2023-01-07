<?php
/* @var int $migrationMode */

use IdeoLogix\DigitalLicenseManager\Database\Migrator;
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;

defined( 'ABSPATH' ) || exit;

/**
 * Upgrade script
 */
if ( $migrationMode === Migrator::MODE_UP ) {

	global $wpdb;

	$licenseService = new LicensesService();

	$results = $wpdb->get_results( "SELECT PM.post_id FROM {$wpdb->postmeta} PM WHERE PM.meta_key='dlm_licensed_product' AND PM.meta_value='1'", ARRAY_A );

	if ( empty( $results ) ) {
		return false;
	}

	$total = 0;

	foreach ( $results as $result ) {

		$use_stock     = get_post_meta( $result['post_id'], 'dlm_licensed_product_use_stock', true );
		$use_generator = get_post_meta( $result['post_id'], 'dlm_licensed_product_use_generator', true );

		if ( $use_stock || $use_generator ) {

			// What if both are checked?
			if ( $use_generator && $use_stock ) {
				$current_stock     = $licenseService->getLicensesStockCount( $result['post_id'] );
				$current_generator = get_post_meta( $result['post_id'], 'dlm_licensed_product_assigned_generator', true );
				if ( is_numeric( $current_generator ) && $current_generator > 0 ) {
					$use_stock = 0;
				} else if ( is_numeric( $current_stock ) && $current_stock > 0 ) {
					$use_generator = 0;
				}
			}

			// Transfer the values
			if ( $use_stock ) {
				update_post_meta( $result['post_id'], 'dlm_licensed_product_licenses_source', 'stock' );
			} else if ( $use_generator ) {
				update_post_meta( $result['post_id'], 'dlm_licensed_product_licenses_source', 'generators' );
			}

			// Remove the metadata
			delete_post_meta( $result['post_id'], 'dlm_licensed_product_use_stock' );
			delete_post_meta( $result['post_id'], 'dlm_licensed_product_use_generator' );

			$total ++;
		}
	}

	return $total > 0;
}

return false;