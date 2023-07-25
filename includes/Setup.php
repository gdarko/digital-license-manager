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

namespace IdeoLogix\DigitalLicenseManager;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key as DefuseCryptoKey;
use Exception;
use IdeoLogix\DigitalLicenseManager\Controllers\Settings as SettingsController;
use IdeoLogix\DigitalLicenseManager\Database\Migrator;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\Utils\CompatibilityHelper;
use WP_Roles;
use function dbDelta;

defined( 'ABSPATH' ) || exit;

/**
 * Class Setup
 * @package IdeoLogix\DigitalLicenseManager
 */
class Setup {

	/**
	 * @var int
	 */
	const DB_VERSION = 103;

	/**
	 * Installation script.
	 *
	 * @param $network_wide
	 *
	 * @return void
	 * @throws EnvironmentIsBrokenException
	 */
	public static function install( $network_wide ) {

		if ( defined( 'DLM_INSTALLED' ) ) {
			return;
		}

		self::checkRequirements();

		if ( $network_wide ) {
			foreach ( CompatibilityHelper::get_site_ids() as $blog_id ) {
				switch_to_blog( $blog_id );
				self::installDefaults();
				restore_current_blog();
			}
		} else {
			self::installDefaults();
		}

		define( 'DLM_INSTALLED', true );

		update_option( 'dlm_needs_permalinks_flush', 1 );
	}

	/**
	 * Install defaults.
	 * @return void
	 * @throws EnvironmentIsBrokenException
	 */
	public static function installDefaults() {
		self::createTables();
		self::setDefaultFilesAndFolders();
		self::setDefaultSettings();
		self::createRoles();
	}

	/**
	 * Deactivation script.
	 */
	public static function deactivate() {

		if ( defined( 'DLM_DEACTIVATED' ) ) {
			return;
		}
		// Nothing for now...
		define( 'DLM_DEACTIVATED', true );
	}

	/**
	 * Uninstall script.
	 */
	public static function uninstall() {

		if ( defined( 'DLM_UNINSTALLED' ) ) {
			return;
		}

		if ( defined( 'DLM_PRO_VERSION' ) ) {
			return; // SKip if a future PRO version is active.
		}

		$safeGuard = (bool) Settings::get( 'safeguard_data', Settings::SECTION_GENERAL );
		if ( $safeGuard ) {
			return;
		}

		global $wpdb;
		$tables = array(
			$wpdb->prefix . DatabaseTable::LICENSES,
			$wpdb->prefix . DatabaseTable::GENERATORS,
			$wpdb->prefix . DatabaseTable::API_KEYS,
			$wpdb->prefix . DatabaseTable::LICENSE_META,
			$wpdb->prefix . DatabaseTable::LICENSE_ACTIVATIONS,
			$wpdb->prefix . DatabaseTable::PRODUCT_DOWNLOADS,
		);

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		}

		self::removeRoles();
		foreach ( SettingsController::instance()->all() as $tab ) {
			if ( empty( $tab['slug'] ) ) {
				continue;
			}
			delete_option( 'dlm_settings_' . $tab['slug'] );
		}

		define( 'DLM_UNINSTALLED', true );
	}

	/**
	 * Migration script.
	 */
	public static function migrate() {
		$migrator = new Migrator( DLM_MIGRATIONS_DIR . '*.php', 'dlm_db_version', self::DB_VERSION );
		$migrator->run();
	}

	/**
	 * Checks if all required plugin components are present.
	 *
	 * @throws Exception
	 */
	public static function checkRequirements() {
		if ( version_compare( phpversion(), '5.3.29', '<=' ) ) {
			throw new Exception( 'PHP 5.3 or lower detected. Digital License Manager requires PHP 5.6 or greater.' );
		}
	}

	/**
	 * Create the necessary database tables.
	 */
	public static function createTables() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table1 = $wpdb->prefix . DatabaseTable::LICENSES;
		$table2 = $wpdb->prefix . DatabaseTable::GENERATORS;
		$table3 = $wpdb->prefix . DatabaseTable::API_KEYS;
		$table4 = $wpdb->prefix . DatabaseTable::LICENSE_META;
		$table5 = $wpdb->prefix . DatabaseTable::LICENSE_ACTIVATIONS;
		$table6 = $wpdb->prefix . DatabaseTable::PRODUCT_DOWNLOADS;

		dbDelta( "
            CREATE TABLE IF NOT EXISTS $table1 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `product_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `license_key` LONGTEXT NOT NULL COMMENT 'Encrypted License Key',
                `hash` LONGTEXT NOT NULL COMMENT 'Hashed License Key ID	',
                `valid_for` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Valid for X time (when ordered from stock)',
                `expires_at` DATETIME NULL DEFAULT NULL COMMENT 'Expiration Date',
                `source` VARCHAR(255) NOT NULL,
                `status` TINYINT(1) UNSIGNED NOT NULL,
                `activations_limit` INT(10) UNSIGNED NULL DEFAULT NULL,
                `created_at` DATETIME NULL COMMENT 'Creation Date',
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Update Date',
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        " );

		dbDelta( "
            CREATE TABLE IF NOT EXISTS $table2 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `charset` VARCHAR(255) NOT NULL,
                `chunks` INT(10) UNSIGNED NOT NULL,
                `chunk_length` INT(10) UNSIGNED NOT NULL,
                `activations_limit` INT(10) UNSIGNED NULL DEFAULT NULL,
                `separator` VARCHAR(255) NULL DEFAULT NULL,
                `prefix` VARCHAR(255) NULL DEFAULT NULL,
                `suffix` VARCHAR(255) NULL DEFAULT NULL,
                `expires_in` INT(10) UNSIGNED NULL DEFAULT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        " );

		dbDelta( "
            CREATE TABLE IF NOT EXISTS $table3 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `description` VARCHAR(200) NULL DEFAULT NULL,
                `permissions` VARCHAR(10) NOT NULL,
                `endpoints` LONGTEXT NULL DEFAULT NULL,
                `consumer_key` CHAR(64) NOT NULL,
                `consumer_secret` CHAR(43) NOT NULL,
                `nonces` LONGTEXT NULL,
                `truncated_key` CHAR(7) NOT NULL,
                `last_access` DATETIME NULL DEFAULT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `consumer_key` (`consumer_key`),
                INDEX `consumer_secret` (`consumer_secret`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        " );

		dbDelta( "
            CREATE TABLE IF NOT EXISTS $table4 (
                `meta_id` BIGINT(20) UNSIGNED AUTO_INCREMENT,
                `license_id` BIGINT(20) UNSIGNED DEFAULT 0 NOT NULL,
                `meta_key` VARCHAR(255) NULL,
                `meta_value` LONGTEXT NULL,
                `created_at` DATETIME NULL,
                `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`meta_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        " );

		dbDelta( "
            CREATE TABLE IF NOT EXISTS $table5 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `token` LONGTEXT NOT NULL COMMENT 'Public identifier',
                `license_id` BIGINT(20) UNSIGNED NOT NULL,
                `label` VARCHAR(255) NULL DEFAULT NULL,
                `source` VARCHAR(255) NOT NULL,
                `ip_address` VARCHAR(255) NULL DEFAULT NULL,
                `user_agent` TEXT NULL DEFAULT NULL,
                `meta_data` LONGTEXT NULL DEFAULT NULL,
                `created_at` DATETIME NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `deactivated_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        " );

		dbDelta( "
            CREATE TABLE IF NOT EXISTS $table6 (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `license_id` BIGINT(20) UNSIGNED NOT NULL,
                `activation_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                `source` VARCHAR(255) NOT NULL,
                `ip_address` VARCHAR(255) NULL DEFAULT NULL,
                `user_agent` TEXT NULL DEFAULT NULL, 
                `meta_data` LONGTEXT NULL DEFAULT NULL,
                `created_at` DATETIME NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        " );
	}

	/**
	 * Sets up the default folder structure and creates the default files, if needed.
	 * @return string[]
	 * @throws EnvironmentIsBrokenException
	 */
	public static function setDefaultFilesAndFolders() {
		/**
		 * When the cryptographic secrets are loaded into these constants,
		 * no crypto credential files should be created.
		 *
		 * @see https://github.com/gdarko/digital-license-manager/wiki/security
		 */
		$cryptoConst = defined( 'DLM_PLUGIN_SECRET' ) && defined( 'DLM_PLUGIN_DEFUSE' );

		$uploads      = wp_upload_dir( null, false );
		$mainDir      = trailingslashit( $uploads['basedir'] ) . 'dlm-files';
		$fileHtaccess = $mainDir . '/.htaccess';
		$fileDefuse   = $mainDir . '/defuse.txt';
		$fileSecret   = $mainDir . '/secret.txt';
		$fileLog      = $mainDir . '/debug.log';
		$fileStatus   = array( 'htaccess' => null, 'defuse' => null, 'secret' => null, 'log' => null );

		$oldUmask = umask( 0 );

		// wp-contents/dlm-files/
		if ( ! file_exists( $mainDir ) ) {
			@mkdir( $mainDir, 0775, true );
		} else {
			$mainDirPerms = substr( sprintf( '%o', fileperms( $mainDir ) ), - 4 );

			if ( $mainDirPerms != '0775' ) {
				@chmod( $mainDirPerms, 0775 );
			}
		}

		// wp-contents/dlm-files/.htaccess
		if ( ! file_exists( $fileHtaccess ) ) {
			$fileHandle = @fopen( $fileHtaccess, 'w' );

			if ( $fileHandle ) {
				fwrite( $fileHandle, 'deny from all' );
				fclose( $fileHandle );
				$fileStatus['htaccess'] = $fileHtaccess;
			}

			@chmod( $fileHtaccess, 0664 );
		} else {
			$permsFileHtaccess = substr( sprintf( '%o', fileperms( $fileHtaccess ) ), - 4 );

			if ( $permsFileHtaccess != '0664' ) {
				@chmod( $permsFileHtaccess, 0664 );
			}
			$fileStatus['htaccess'] = $fileHtaccess;
		}

		if ( ! $cryptoConst ) {
			// wp-contents/dlm-files/defuse.txt
			if ( ! file_exists( $fileDefuse ) ) {
				$defuse     = DefuseCryptoKey::createNewRandomKey();
				$fileHandle = @fopen( $fileDefuse, 'w' );

				if ( $fileHandle ) {
					fwrite( $fileHandle, $defuse->saveToAsciiSafeString() );
					fclose( $fileHandle );
					$fileStatus['defuse'] = $fileDefuse;
				}

				@chmod( $fileDefuse, 0664 );
			} else {
				$permsFileDefuse = substr( sprintf( '%o', fileperms( $fileDefuse ) ), - 4 );

				if ( $permsFileDefuse != '0664' ) {
					@chmod( $permsFileDefuse, 0664 );
				}
				$fileStatus['defuse'] = $fileDefuse;
			}

			// wp-contents/dlm-files/secret.txt
			if ( ! file_exists( $fileSecret ) ) {
				$fileHandle = @fopen( $fileSecret, 'w' );

				if ( $fileHandle ) {
					fwrite( $fileHandle, bin2hex( openssl_random_pseudo_bytes( 32 ) ) );
					fclose( $fileHandle );
					$fileStatus['secret'] = $fileSecret;
				}

				@chmod( $fileSecret, 0664 );
			} else {
				$permsFileSecret = substr( sprintf( '%o', fileperms( $fileSecret ) ), - 4 );

				if ( $permsFileSecret != '0664' ) {
					@chmod( $permsFileSecret, 0664 );
				}
				$fileStatus['secret'] = $fileSecret;
			}
		}

		// wp-contents/dlm-files/debug.log
		if ( ! file_exists( $fileLog ) ) {
			$fileHandle = @fopen( $fileLog, 'w+' );

			if ( $fileHandle ) {
				fclose( $fileHandle );
				$fileStatus['log'] = $fileLog;
			}

			@chmod( $fileLog, 0664 );
		} else {
			$permsFileSecret = substr( sprintf( '%o', fileperms( $fileLog ) ), - 4 );

			if ( $permsFileSecret != '0664' ) {
				@chmod( $permsFileSecret, 0664 );
			}

			$fileStatus['log'] = $fileLog;
		}


		umask( $oldUmask );

		return $fileStatus;
	}

	/**
	 * Set the default plugin options.
	 */
	public static function setDefaultSettings() {

		$default_settings = array(
			'dlm_settings_general' => array(
				'hide_license_keys' => 0,
				'disable_api_ssl'   => 0,
				'safeguard_data'    => 1,
			)
		);

		$default_settings = apply_filters( 'dlm_default_settings', $default_settings );

		foreach ( $default_settings as $section => $settings ) {
			if ( ! get_option( $section ) ) {
				update_option( $section, apply_filters( $section . '_defaults', $settings ) );
			}
		}

		update_option( 'dlm_db_version', self::DB_VERSION );
	}

	/**
	 * Add Digital License Manager roles.
	 */
	public static function createRoles() {
		global $wp_roles;

		if ( ! class_exists( '\WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		// Licensing agent role.
		add_role(
			'dlm_agent',
			_x( 'Licensing agent', 'User role', 'digital-license-manager' ),
			array()
		);

		// Shop manager role.
		add_role(
			'dlm_manager',
			_x( 'License manager', 'User role', 'digital-license-manager' ),
			array(
				'level_9'                => true,
				'level_8'                => true,
				'level_7'                => true,
				'level_6'                => true,
				'level_5'                => true,
				'level_4'                => true,
				'level_3'                => true,
				'level_2'                => true,
				'level_1'                => true,
				'level_0'                => true,
				'read'                   => true,
				'read_private_pages'     => true,
				'read_private_posts'     => true,
				'edit_posts'             => true,
				'edit_pages'             => true,
				'edit_published_posts'   => true,
				'edit_published_pages'   => true,
				'edit_private_pages'     => true,
				'edit_private_posts'     => true,
				'edit_others_posts'      => true,
				'edit_others_pages'      => true,
				'publish_posts'          => true,
				'publish_pages'          => true,
				'delete_posts'           => true,
				'delete_pages'           => true,
				'delete_private_pages'   => true,
				'delete_private_posts'   => true,
				'delete_published_pages' => true,
				'delete_published_posts' => true,
				'delete_others_posts'    => true,
				'delete_others_pages'    => true,
				'manage_categories'      => true,
				'manage_links'           => true,
				'moderate_comments'      => true,
				'upload_files'           => true,
				'export'                 => true,
				'import'                 => true,
				'list_users'             => true,
				'edit_theme_options'     => true,
			)
		);

		foreach ( self::getCoreCapabilities() as $capGroup ) {
			foreach ( $capGroup as $cap ) {
				$wp_roles->add_cap( 'dlm_manager', $cap );
				$wp_roles->add_cap( 'administrator', $cap );
			}
		}

		foreach ( self::getAgentCapabilities() as $capGroup ) {
			foreach ( $capGroup as $cap ) {
				$wp_roles->add_cap( 'dlm_agent', $cap );
				$wp_roles->add_cap( 'administrator', $cap );
				$wp_roles->add_cap( 'shop_manager', $cap );
			}
		}
	}

	/**
	 * Remove Digital License Manager roles
	 */
	public static function removeRoles() {
		global $wp_roles;

		if ( ! class_exists( '\WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		foreach ( self::getCoreCapabilities() as $capGroup ) {
			foreach ( $capGroup as $cap ) {
				$wp_roles->remove_cap( 'dlm_manager', $cap );
				$wp_roles->remove_cap( 'administrator', $cap );
			}
		}

		foreach ( self::getAgentCapabilities() as $capGroup ) {
			foreach ( $capGroup as $cap ) {
				$wp_roles->remove_cap( 'dlm_agent', $cap );
				$wp_roles->remove_cap( 'administrator', $cap );
				$wp_roles->remove_cap( 'shop_manager', $cap );
			}
		}

		remove_role( 'dlm_manager' );
		remove_role( 'dlm_agent' );
	}

	/**
	 * Returns the plugin's core capabilities.
	 *
	 * @return array
	 */
	public static function getCoreCapabilities() {

		$capabilities = array();

		$capabilities['core'] = array(
			'dlm_manage_settings',
		);

		$capabilityTypes = array(
			'licenses',
			'generators',
			'activations',
			'api_keys',
			'downloads',
			'software',
		);

		foreach ( $capabilityTypes as $capType ) {
			$capabilities[ $capType ] = array(
				"dlm_create_{$capType}",
				"dlm_edit_{$capType}",
				"dlm_read_{$capType}",
				"dlm_delete_{$capType}",
				"dlm_export_{$capType}",
			);
		}

		$perCapType = array(
			'licenses' => array(
				'dlm_activate_licenses',
				'dlm_deactivate_licenses',
				'dlm_validate_licenses',
			),
			'software' => array(
				'dlm_download_software',
			)
		);

		foreach ( $perCapType as $capType => $caps ) {
			foreach ( $caps as $cap ) {
				$capabilities[ $capType ][] = $cap;
			}
		}

		return apply_filters( 'dlm_core_caps', $capabilities );
	}

	/**
	 * Return's the plugin's REST API capabilities.
	 *
	 * @return array
	 */
	public static function getAgentCapabilities() {
		$capabilities = self::getCoreCapabilities();
		if ( isset( $capabilities['core'] ) ) {
			unset( $capabilities['core'] );
		}

		return $capabilities;
	}

	/**
	 * List of rest api endpoints.
	 * @return array[]
	 */
	public static function restEndpoints() {
		return apply_filters( 'dlm_rest_endpoints', array(
			array(
				'id'         => '010',
				'name'       => 'v1/licenses',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '011',
				'name'       => 'v1/licenses/{license_key}',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '012',
				'name'       => 'v1/licenses',
				'method'     => 'POST',
				'deprecated' => false,
			),
			array(
				'id'         => '013',
				'name'       => 'v1/licenses/{license_key}',
				'method'     => 'PUT',
				'deprecated' => false,
			),
			array(
				'id'         => '014',
				'name'       => 'v1/licenses/{license_key}',
				'method'     => 'DELETE',
				'deprecated' => false,
			),
			array(
				'id'         => '015',
				'name'       => 'v1/licenses/activate/{license_key}',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '016',
				'name'       => 'v1/licenses/deactivate/{activation_token}',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '017',
				'name'       => 'v1/licenses/validate/{activation_token}',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '022',
				'name'       => 'v1/generators',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '023',
				'name'       => 'v1/generators/{id}',
				'method'     => 'GET',
				'deprecated' => false,
			),
			array(
				'id'         => '024',
				'name'       => 'v1/generators',
				'method'     => 'POST',
				'deprecated' => false,
			),
			array(
				'id'         => '025',
				'name'       => 'v1/generators/{id}',
				'method'     => 'PUT',
				'deprecated' => false,
			),
			array(
				'id'         => '026',
				'name'       => 'v1/generators/{id}',
				'method'     => 'DELETE',
				'deprecated' => false,
			),
			array(
				'id'         => '027',
				'name'       => 'v1/generators/{id}/generate',
				'method'     => 'POST',
				'deprecated' => false,
			),
		) );
	}

	/**
	 * This should only be called if you want to load the plugin text domain manually and in the init hook.
	 * The use case of this function is only if you sue this plugin as a composer package, then it will need to be loaded manually.
	 * @return void
	 */
	public static function loadTextdomain() {
		if ( defined( 'DLM_ABSPATH' ) ) {
			load_plugin_textdomain( 'digital-license-manager', false, DLM_ABSPATH . 'i18n/languages' );
		}
	}
}
