<?php

namespace IdeoLogix\DigitalLicenseManager\Tools\Migration\Migrators;

use Defuse\Crypto\Key;
use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractToolMigrator;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey as ApiKeyResourceRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator as GeneratorResourceRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation as LicenseActivationResourceRepository;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta as LicenseMetaResourceRepository;
use IdeoLogix\DigitalLicenseManager\Enums\ActivationSource;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseSource;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;
use IdeoLogix\DigitalLicenseManager\Utils\Data\License as LicenseUtil;
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

		$default_per_page = 25;

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
		global $wpdb;

		return [
			'licenses'     => $wpdb->prefix . 'lmfwc_licenses',
			'generators'   => $wpdb->prefix . 'lmfwc_generators',
			'api_keys'     => $wpdb->prefix . 'lmfwc_api_keys',
			'license_meta' => $wpdb->prefix . 'lmfwc_licenses_meta',
		];
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
			'meta_key'     => 'dlm_licensed_product',
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
	protected function getRecordsCount( $table ) {
		global $wpdb;

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM %s", $table ) );
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
		$per_page = 25;


		$preserve_ids = isset( $_POST['preserve_ids'] ) ? intval( $_POST['preserve_ids'] ) : 0;

		$tables = $this->getTables();

		try {
			switch ( $step ) {

				/**
				 * Licenses
				 */
				case 1:
					$old_rows = $this->getRecords( $tables['licenses'], $page, $per_page );
					if ( $preserve_ids ) {
						LicenseResourceRepository::instance()->deleteBy( [
							'id' => [
								'compare' => '>',
								'value'   => 0,
							]
						] );
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

						$new_row = LicenseResourceRepository::instance()->insert( $new_row_data );

						if ( ! empty( $new_row ) ) {

							if ( ! empty( $row['times_activated'] ) ) {
								for ( $i = 0; $i < $row['times_activated']; $i ++ ) {
									LicenseActivationResourceRepository::instance()->insert( array(
										'token'      => LicenseUtil::generateActivationToken( $license_key ),
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
									LicenseMetaResourceRepository::instance()->insert( $old_meta_row );
								}
							}

							if ( ! $preserve_ids ) {
								LicenseMetaResourceRepository::instance()->insert( array(
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
					if ( $preserve_ids ) {
						GeneratorResourceRepository::instance()->deleteBy( [
							'id' => [
								'compare' => '>',
								'value'   => 0,
							]
						] );
					}

					delete_transient( 'dlm_generator_map' );

					$generator_map = [];

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

						$new_row = GeneratorResourceRepository::instance()->insert( $new_row_data );
						if ( ! empty( $new_row ) ) {
							$generator_map[ $row['id'] ] = $new_row->getId();
						}
					}

					set_transient( 'dlm_generator_map', $generator_map, HOUR_IN_SECONDS * 24 );

					break;
				case 3:

					$old_rows = $this->getRecords( $tables['api_keys'], $page, $per_page );
					if ( $preserve_ids ) {
						ApiKeyResourceRepository::instance()->deleteBy( [
							'id' => [
								'compare' => '>',
								'value'   => 0,
							]
						] );
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
								$new_endpoints[ $dlm_id ] = $value;
							}
							if ( ! empty( $new_endpoints ) ) {
								$new_row_data['endpoints'] = $new_endpoints;
							}
						}

						ApiKeyResourceRepository::instance()->insert( $new_row_data );
					}


					break;
				case 4:

					$query = $this->getProducts( $page, $per_page );
					if ( ! empty( $query['products'] ) ) {
						$generator_map = get_transient( 'dlm_generator_map' );
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

					break;

			}
		} catch ( \Exception $e ) {
			return ( new \WP_Error( 'conversio_error', sprintf( __( 'Error: %s', 'digital-license-manager' ), $e->getMessage() ) ) );
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

		$offset = $page <= 1 ? 0 : ( $page - 1 ) * $per_page;
		$query  = $wpdb->prepare( "SELECT * FROM {$table} LIMIT %d, %d", $offset, $per_page );

		return $wpdb->get_results( $query, ARRAY_A );
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

	public function test() {
		$data2 = $this->getProducts( 1, 3 );
		var_dump( $data2 );
		echo 'done';
	}
}
