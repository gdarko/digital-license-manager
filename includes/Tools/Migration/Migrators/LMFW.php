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

namespace IdeoLogix\DigitalLicenseManager\Tools\Migration\Migrators;

use Defuse\Crypto\Key;
use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractToolMigrator;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\ApiKeys;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Generators;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseActivations;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\LicenseMeta;
use IdeoLogix\DigitalLicenseManager\Enums\ActivationSource;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseSource;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;

class LMFW extends AbstractToolMigrator {

	private $upload_dir;

	public function __construct() {
		$this->id   = 'lmfw';
		$this->name = 'License Manager for WooCommerce';
	}

	/**
	 * Returns the migrator steps
	 * @return array|\WP_Error
	 */
	public function getSteps() {

		$default_per_page = (int) apply_filters( 'dlm_migrator_lmfw_batch_size', 25 );

		$tables = $this->getTables();

		$query_prod = $this->getProducts( 1, $default_per_page, 'ids' );
		$query_ord  = $this->getOrders( 1, $default_per_page, 'ids' );


		return [
			1 => array(
				'name'  => 'Licenses',
				'pages' => $this->getPageCount( $this->getRecordsCount( $tables['licenses'] ), $default_per_page )
			),
			2 => array(
				'name'  => 'Generators',
				'pages' => $this->getPageCount( $this->getRecordsCount( $tables['generators'] ), $default_per_page )
			),
			3 => array(
				'name'  => 'API Keys',
				'pages' => $this->getPageCount( $this->getRecordsCount( $tables['api_keys'] ), $default_per_page )
			),
			4 => array(
				'name'  => 'Products',
				'pages' => $this->getPageCount( $query_prod['total'], $default_per_page )
			),
			5 => array(
				'name'  => 'Orders',
				'pages' => $this->getPageCount( $query_ord['total'], $default_per_page )
			),
			/*6 => array(
				'name'  => 'Settings',
				'pages' => 1
			)*/
		];
	}

	/**
	 * Return the tables
	 * @return string[]
	 */
	protected function getTables() {
		return self::_getTables();
	}

	/**
	 * Return the orders
	 *
	 * @param $page
	 * @param $per_page
	 * @param string $return
	 *
	 * @return array|\WP_Error
	 */
	public function getProducts( $page, $per_page, $return = 'objects' ) {

		if ( ! function_exists( '\wc_get_products' ) ) {
			return new \WP_Error( 'WooCommerce is not active.' );
		}

		$args = array(
			'meta_key'     => 'lmfwc_licensed_product',
			'meta_value'   => 1,
			'meta_compare' => '=',
			'type'         => array( 'simple', 'variation' ),
			'paginate'     => true,
			'limit'        => $per_page,
			'page'         => $page,
			'return'       => $return,
		);

		$query = (array) \wc_get_products( $args );

		return $query;
	}

	/**
	 * Return the orders
	 *
	 * @param $page
	 * @param $per_page
	 * @param string $return
	 *
	 * @return array|\WP_Error
	 */
	public function getOrders( $page, $per_page, $return = 'objects' ) {

		if ( ! function_exists( '\wc_get_orders' ) ) {
			return new \WP_Error( 'WooCommerce is not active.' );
		}

		$args = array(
			'status'       => 'any',
			'meta_key'     => 'lmfwc_order_complete',
			'meta_value'   => 1,
			'meta_compare' => '=',
			'paginate'     => true,
			'limit'        => $per_page,
			'page'         => $page,
			'return'       => $return
		);

		$query = (array) \wc_get_orders( $args );

		return $query;
	}

	/**
	 * Get page count
	 * @return float
	 */
	public function getPageCount( $total, $per_page = 20 ) {
		if ( $total <= $per_page ) {
			return 1;
		} else {
			return (int) ceil( $total / $per_page );
		}
	}

	/**
	 * Return the database results
	 *
	 * @param $table
	 * @param $page
	 * @param $per_page
	 *
	 * @return int
	 */
	public function getRecordsCount( $table ) {
		return self::getRowCount( $table );
	}

	/**
	 * Initializes the process
	 *
	 * @param array $data
	 *
	 * @return bool|\WP_Error
	 */
	public function init( $data = array() ) {
		return true;
	}

	/**
	 * Initializes the process
	 *
	 * @param $step
	 * @param $page
	 * @param array $data
	 *
	 * @return bool|\WP_Error
	 */
	public function doStep( $step, $page, $data = array() ) {
		$step     = (int) $step;
		$page     = (int) $page;
		$per_page = (int) apply_filters( 'dlm_migrator_lmfw_batch_size', 25 );

		$licenseService = new LicensesService();

		$preserve_ids = isset( $_POST['preserve_ids'] ) ? intval( $_POST['preserve_ids'] ) : 0;

		$tables = $this->getTables();

		try {
			switch ( $step ) {

				/**
				 * Licenses
				 */
				case 1:
					$old_rows = $this->getRecords( $tables['licenses'], $page, $per_page );
					if ( $preserve_ids && $page === 1 ) {
						Licenses::instance()->truncate();
					}

					foreach ( $old_rows as $row ) {

						$license_key = $this->decrypt( $row['license_key'] );
						$expires_at  = ( empty( $row['expires_at'] )
						                 && ! empty( $row['valid_for'] )
						                 && ! empty( $row['created_at'] ) ) ? DateFormatter::addDaysInFuture( $row['valid_for'], $row['created_at'], 'Y-m-d H:i:s' ) : $row['expires_at'];


						$new_row_data = array(
							'order_id'          => $row['order_id'],
							'product_id'        => $row['product_id'],
							'user_id'           => $row['user_id'],
							'license_key'       => CryptoHelper::encrypt( $license_key ),
							'hash'              => CryptoHelper::hash( $license_key ),
							'expires_at'        => $expires_at,
							'source'            => LicenseSource::MIGRATION,
							'status'            => $row['status'],
							'activations_limit' => $row['times_activated_max'],
							'created_at'        => $row['created_at'],
							'created_by'        => $row['created_by'],
							'updated_at'        => $row['updated_at'],
							'updated_by'        => $row['updated_by'],
						);

						if ( $preserve_ids ) {
							$new_row_data['id'] = $row['id'];
						}

						$new_row = Licenses::instance()->insert( $new_row_data );

						if ( ! empty( $new_row ) ) {

							if ( ! empty( $row['times_activated'] ) ) {
								for ( $i = 0; $i < $row['times_activated']; $i ++ ) {
									LicenseActivations::instance()->insert( array(
										'token'      => $licenseService->generateActivationToken( $license_key ),
										'license_id' => $new_row->getId(),
										'label'      => __( 'Untitled' ),
										'source'     => ActivationSource::MIGRATION,
									) );
								}
							}

							$old_meta_rows = $this->getLicenseMeta( $row['id'] );
							if ( ! empty( $old_meta_rows ) ) {
								foreach ( $old_meta_rows as $old_meta_row ) {
									unset( $old_meta_row['meta_id'] );
									if ( ! $preserve_ids ) {
										$old_meta_row['license_id'] = $new_row->getId();
									}
									LicenseMeta::instance()->insert( $old_meta_row );
								}
							}

							if ( ! $preserve_ids ) {
								LicenseMeta::instance()->insert( array(
									'license_id' => $new_row->getId(),
									'meta_key'   => 'migrated_from',
									'meta_value' => $row['id'],
								) );
							}

						}
					}

					break;

				/**
				 * Generators
				 */
				case 2:
					$old_rows = $this->getRecords( $tables['generators'], $page, $per_page );
					if ( $preserve_ids && $page === 1 ) {
						Generators::instance()->truncate();
					};

					$generator_map = $this->getGeneratorMap();

					foreach ( $old_rows as $row ) {

						$new_row_data = array(
							'name'              => $row['name'],
							'charset'           => $row['charset'],
							'chunks'            => $row['chunks'],
							'chunk_length'      => $row['chunk_length'],
							'activations_limit' => $row['times_activated_max'],
							'separator'         => $row['separator'],
							'prefix'            => $row['prefix'],
							'suffix'            => $row['suffix'],
							'expires_in'        => $row['expires_in'],
							'created_at'        => $row['created_at'],
							'created_by'        => $row['created_by'],
							'updated_at'        => $row['updated_at'],
							'updated_by'        => $row['updated_by'],


						);

						if ( $preserve_ids ) {
							$new_row_data['id'] = $row['id'];
						}

						$new_row = Generators::instance()->insert( $new_row_data );
						if ( ! empty( $new_row ) ) {
							$generator_map[ $row['id'] ] = $new_row->getId();
						}
					}

					$this->updateGeneratorMap($generator_map);

					break;
				case 3:

					$old_rows = $this->getRecords( $tables['api_keys'], $page, $per_page );
					if ( $preserve_ids && $page === 1 ) {
						ApiKeys::instance()->truncate();
					}

					foreach ( $old_rows as $row ) {

						$new_row_data = array(
							'user_id'         => $row['user_id'],
							'description'     => $row['description'],
							'permissions'     => $row['permissions'],
							'consumer_key'    => $row['consumer_key'],
							'consumer_secret' => $row['consumer_secret'],
							'nonces'          => $row['nonces'],
							'truncated_key'   => $row['truncated_key'],
							'last_access'     => $row['last_access'],
							'created_at'      => $row['created_at'],
							'created_by'      => $row['created_by'],
							'updated_at'      => $row['updated_at'],
							'updated_by'      => $row['updated_by'],
						);


						if ( $preserve_ids ) {
							$new_row_data['id'] = $row['id'];
						}

						$new_endpoints    = array();
						$settings_general = get_option( 'lmfwc_settings_general', array() );
						if ( ! empty( $settings_general['lmfwc_enabled_api_routes'] ) ) {
							$map = [
								'010' => '010',
								'011' => '011',
								'012' => '012',
								'013' => '013',
								'015' => '014',
								'016' => '015',
								'017' => '016',
								'022' => '017',
								'023' => '018',
								'024' => '019',
								'025' => '020',
							];
							foreach ( $map as $dlm_id => $lmfwc_id ) {
								$value                    = isset( $settings_general['lmfwc_enabled_api_routes'][ $lmfwc_id ] ) ? $settings_general['lmfwc_enabled_api_routes'][ $lmfwc_id ] : 0;
								$new_endpoints[ $dlm_id ] = strval( $value );
							}
							if ( ! empty( $new_endpoints ) ) {
								$new_row_data['endpoints'] = $new_endpoints;
							}
						}

						ApiKeys::instance()->delete(['consumer_key' => $row['consumer_key'], 'consumer_secret' => $row['consumer_secret']]); // Delete all existing matchin before inserting.
						ApiKeys::instance()->insert( $new_row_data );
					}


					break;
				case 4:

					$query = $this->getProducts( $page, $per_page );
					if ( ! empty( $query['products'] ) ) {
						$generator_map = $this->getGeneratorMap();
						foreach ( $query['products'] as $product ) {
							/* @var \WC_Product $product */
							$quantity      = (int) get_post_meta( $product->get_id(), 'lmfwc_licensed_product_delivered_quantity', true );
							$useGenerator  = (int) get_post_meta( $product->get_id(), 'lmfwc_licensed_product_use_generator', true );
							$generator     = (int) get_post_meta( $product->get_id(), 'lmfwc_licensed_product_assigned_generator', true );
							$generator_new = isset( $generator_map[ $generator ] ) ? (int) $generator_map[ $generator ] : $generator;
							$product->update_meta_data( 'dlm_licensed_product', 1 );
							$product->update_meta_data( 'dlm_licensed_product_delivered_quantity', $quantity );
							if ( $useGenerator && $generator_new ) {
								$product->update_meta_data( 'dlm_licensed_product_licenses_source', 'generators' );
								$product->update_meta_data( 'dlm_licensed_product_assigned_generator', $generator_new );
							} else {
								$product->update_meta_data( 'dlm_licensed_product_licenses_source', 'stock' );
							}
							$product->save_meta_data();
						}
					}
					break;

				case 5:

					$query = $this->getOrders( $page, $per_page );
					if ( ! empty( $query['orders'] ) ) {
						foreach ( $query['orders'] as $order ) {
							/* @var \WC_Order $order */
							$order->update_meta_data( 'dlm_order_complete', 1 );
							$order->save_meta_data();
						}
					}

					break;
				case 6:

					$settings_general      = (array) get_option( 'lmfwc_settings_general', array() );
					$settings_order_status = (array) get_option( 'lmfwc_settings_order_status', array() );
					// TODO: Implement.

					break;

			}
		} catch ( \Exception $e ) {
			return ( new \WP_Error( 'step_error', sprintf( __( 'Error: %s', 'digital-license-manager' ), $e->getMessage() ) ) );
		}

		return true;


	}

	/**
	 * Return the database results
	 *
	 * @param $table
	 * @param $page
	 * @param $per_page
	 *
	 * @return array[]
	 */
	protected function getRecords( $table, $page, $per_page ) {
		global $wpdb;

		$per_page = (int) $per_page;

		$offset = $page <= 1 ? 0 : ( $page - 1 ) * $per_page;

		return $wpdb->get_results( "SELECT * FROM {$table} LIMIT {$offset}, {$per_page}", ARRAY_A );
	}

	/**
	 * Decrypt license
	 *
	 * @param $license_key
	 *
	 * @return string
	 * @throws \Defuse\Crypto\Exception\BadFormatException
	 * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
	 * @throws \Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException
	 */
	protected function decrypt( $license_key ) {
		return \Defuse\Crypto\Crypto::decrypt( $license_key, Key::loadFromAsciiSafeString( $this->find3rdPartyDefuse() ) );
	}

	/**
	 * Get encryption defuse key.
	 * @return string|null
	 */
	protected function find3rdPartyDefuse() {

		if ( defined( 'LMFWC_PLUGIN_DEFUSE' ) ) {
			return LMFWC_PLUGIN_DEFUSE;
		}

		if ( is_null( $this->upload_dir ) ) {
			$this->upload_dir = wp_upload_dir()['basedir'] . '/lmfwc-files/';
		}

		if ( file_exists( $this->upload_dir . 'defuse.txt' ) ) {
			return (string) file_get_contents( $this->upload_dir . 'defuse.txt' );
		}

		return null;
	}

	/**
	 * Return the database results
	 *
	 * @param $table
	 * @param $page
	 * @param $per_page
	 *
	 * @return array[]
	 */
	protected function getLicenseMeta( $license_id ) {
		global $wpdb;

		$tables = $this->getTables();
		$table  = $tables['license_meta'];
		$query  = $wpdb->prepare( "SELECT * FROM {$table} WHERE license_id=%d", $license_id );

		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Undo migration
	 *
	 * @return bool
	 */
	public function undo() {
		// Delete imported licenses
		$per_page = apply_filters( 'dlm_migrator_lmfw_undo_batch_size', 50 );
		$total    = LicenseMeta::instance()->count( [ 'meta_key' => 'migrated_from' ] );
		$pages    = $total > $per_page ? ceil( $total / $per_page ) : 1;
		$all      = [];
		for ( $i = 1; $i <= $pages; $i ++ ) {
			$offset = ( $i - 1 ) * $per_page;
			$rows   = LicenseMeta::instance()->get( [ 'meta_key' => 'migrated_from' ], $sortBy = null, $sortDir = 'DESC', $offset, $per_page );
			foreach ( $rows as $row ) {
				$all[] = [ 'licenseId' => $row->getLicenseId(), 'metaId' => $row->getMetaId() ];
			}
		}
		foreach($all as $item) {
			Licenses::instance()->delete( [ 'id' => (int) $item['licenseId'] ] );
			LicenseMeta::instance()->delete( [ 'meta_id' => (int) $item['metaId'] ] );
			LicenseActivations::instance()->delete( [ 'license_id' => (int) $item['licenseId'] ] );
		}
		// Delete imported generators
		$map = $this->getGeneratorMap();
		if(!empty($map) && is_array($map)) {
			foreach($map as $old_id => $new_id) {
				Generators::instance()->delete(['id' => (int) $new_id]);
			}
		}
		// House keeping
		delete_option('dlm_database_migration_'.$this->getId());
		$this->deleteGeneratorMap();

		return true;
	}

	/**
	 * Check if the it is possible to faciliate migration
	 * @return bool|\WP_Error
	 */
	public function checkAvailability() {

		if ( ! function_exists( '\WC' ) ) {
			return new \WP_Error( __( 'Please activate WooCommerce before starting with migration', 'digital-license-manager' ) );
		}

		global $wpdb;

		/**
		 * Check the tables.
		 */
		$tables = $this->getTables();
		foreach ( $tables as $table ) {
			$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) );
			if ( $wpdb->get_var( $query ) != $table ) {
				return new \WP_Error( __( 'No data found from the plugin "License Manager for WooCommerce" to migrate.', 'digital-license-manager' ) );
			}
		}

		/**
		 * Check the crypto secrets
		 */
		$key_defuse = $this->find3rdPartyDefuse();
		$key_secret = $this->find3rdPartySecret();
		if ( is_null( $key_defuse ) || is_null( $key_secret ) ) {
			return new \WP_Error( __( 'Plugin data found, but no encryption secrets found. Unable to decrypt license keys.', 'digital-license-manager' ) );
		}

		return true;


	}

	/**
	 * Get encryption secret key
	 */
	protected function find3rdPartySecret() {

		if ( defined( 'LMFWC_PLUGIN_SECRET' ) ) {
			return LMFWC_PLUGIN_SECRET;
		}

		if ( is_null( $this->upload_dir ) ) {
			$this->upload_dir = wp_upload_dir()['basedir'] . '/lmfwc-files/';
		}

		if ( file_exists( $this->upload_dir . 'secret.txt' ) ) {
			return (string) file_get_contents( $this->upload_dir . 'secret.txt' );
		}

		return null;
	}

	/**
	 * Get the rows count in specific table
	 * @param $table
	 *
	 * @return int
	 */
	public static function getRowCount($table) {
		global $wpdb;

		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}

	/**
	 * Return the tables
	 * @return string[]
	 */
	public static function _getTables() {
		global $wpdb;

		return [
			'licenses'     => $wpdb->prefix . 'lmfwc_licenses',
			'generators'   => $wpdb->prefix . 'lmfwc_generators',
			'api_keys'     => $wpdb->prefix . 'lmfwc_api_keys',
			'license_meta' => $wpdb->prefix . 'lmfwc_licenses_meta',
		];
	}

	/**
	 * Retrieves the generator map
	 * @return false|mixed|null
	 */
	protected function getGeneratorMap() {
		$value = get_option('dlm_lmfw_migration_generator_map');
		return empty($value) || !is_array($value) ? [] : $value;
	}

	/**
	 * Set generator map
	 *
	 * @param array $new_map
	 *
	 * @return void
	 */
	protected function updateGeneratorMap( $new_map ) {
		update_option( 'dlm_lmfw_migration_generator_map', $new_map );
	}

	/**
	 * Deletes the generator map
	 * @return void
	 */
	protected function deleteGeneratorMap() {
		delete_option('dlm_lmfw_migration_generator_map');
	}

	/**
	 * Is the legacy "LMFWC" plugin used?
	 * @return bool|null
	 */
	public static function isUsed() {
		static $state = null;
		if ( is_null( $state ) ) {
			$settings = get_option( 'lmfwc_settings_general' );
			$state    = ! empty( $settings );
		}

		return $state;
	}

	/**
	 * Data exists
	 * @return mixed|string|null
	 */
	public static function dataFound() {
		global $wpdb;
		$tables = self::_getTables();
		$licenses = $tables['licenses'];
		return self::getRowCount($licenses) ;
	}

	/**
	 * Already migrated?
	 * @return bool
	 */
	public static function alreadyMigrated() {
		$info = get_option('dlm_database_migration_lmfw');
		return !empty($info['completed_at']);
	}

}
