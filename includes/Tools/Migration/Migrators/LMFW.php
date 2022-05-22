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
		return [
			1 => array( 'name' => 'Licenses', 'pages' => 3 ),
			2 => array( 'name' => 'Generators', 'pages' => 4 ),
			3 => array( 'name' => 'API Keys', 'pages' => 5 ),
			4 => array( 'name' => 'Products', 'pages' => 6 ),
			5 => array( 'name' => 'Orders', 'pages' => 7 )
		];
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

					$license_key = $this->decrypt( $row['license_ley'] );
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
							for ( $i = 0; $i < $row['times_activated_max']; $i ++ ) {
								LicenseActivationResourceRepository::instance()->insert( array(
									'token'      => LicenseUtil::generateActivationToken( $license_key ),
									'license_id' => $new_row->id,
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
									$old_meta_row['license_id'] = $new_row->id;
								}
								LicenseMetaResourceRepository::instance()->insert( $old_meta_row );
							}
						}

						if ( ! $preserve_ids ) {
							LicenseMetaResourceRepository::instance()->insert( array(
								'license_id' => $new_row->id,
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

					GeneratorResourceRepository::instance()->insert( $new_row_data );
				}

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

				break;

		}

		return true;


	}

	/**
	 * Check if the it is possible to faciliate migration
	 * @return bool|\WP_Error
	 */
	public function checkAvailability() {

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

		$offset = ( $page - 1 ) * $per_page;
		$query  = $wpdb->prepare( "SELECT * FROM {$table} LIMIT %d, %d", $offset, $per_page );

		return $wpdb->get_results( $query, ARRAY_A );
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

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table}" ) );
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


	public function test() {
		$license = $this->decrypt( 'def50200bc45ee1a86c42673a0f7b7d835d4e00ae186b6fc02dc8659f20a9aa1896f987be981000e158e6a08c16f048d34f1f79361d633ed6946e15d843fa08c675f3588fa1eb6edbfe94a89b4cc8dee51158834c997018e6b624cece7340724f306319138e5b3' );
		var_dump( $license );
		var_dump( CryptoHelper::hash( $license ) );
		var_dump( CryptoHelper::encrypt( $license ) );
		var_dump( CryptoHelper::decrypt( CryptoHelper::encrypt( $license ) ) );


	}
}
