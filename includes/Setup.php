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

use Exception;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key as DefuseCryptoKey;
use IdeoLogix\DigitalLicenseManager\Controllers\Settings as SettingsController;
use IdeoLogix\DigitalLicenseManager\Database\Migrator;
use IdeoLogix\DigitalLicenseManager\Database\Schema;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;
use IdeoLogix\DigitalLicenseManager\RestAPI\Setup as RestAPISetup;
use IdeoLogix\DigitalLicenseManager\Utils\CompatibilityHelper;
use WP_Roles;

defined( 'ABSPATH' ) || exit;

/**
 * Class Setup
 * @package IdeoLogix\DigitalLicenseManager
 */
class Setup {

	/**
	 * The database version
	 * @var int
	 */
	const DB_VERSION = 103;

	/**
	 * The minimum PHP version
	 */
	const MIN_PHP_VERSION = '5.6.0';

	/**
	 * Executed when plugin is installed.
	 *
	 * @param $network_wide
	 *
	 * @throws EnvironmentIsBrokenException
	 */
	public static function install( $network_wide ) {

		if ( defined( 'DLM_INSTALLED' ) ) {
			return;
		}

		self::checkEnv();

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

		Schema::drop();

		self::removeRoles();
		foreach ( SettingsController::instance()->all() as $tab ) {
			if ( empty( $tab['slug'] ) ) {
				continue;
			}
			delete_option( 'dlm_settings_' . $tab['slug'] );
		}

		delete_option( 'nc_info_dlm_lmfwc' );
		delete_option( 'dlm_lmfw_migration_generator_map' );
		delete_option( 'dlm_db_version' );
		delete_option( 'dlm_needs_permalinks_flush' );

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
	 * Check requirements.
	 *
	 * @throws Exception
	 */
	public static function checkEnv() {
		if ( self::isEnvCompatible() ) {
			return; // All fine.
		}
		throw new Exception( sprintf( 'PHP %s or lower detected. Digital License Manager requires PHP 5.6 or greater.', self::MIN_PHP_VERSION ) );
	}

	/**
	 * Creates the application tables
	 */
	public static function createTables() {
		Schema::create();
	}

	/**
	 * Sets up the default folder structure and creates the default files, if needed.
	 *
	 * @return string[]
	 * @throws EnvironmentIsBrokenException
	 *
	 * Code inspired by "License Manager for WooCommerce" plugin
	 * @copyright  2019-2022 Drazen Bebic
	 * @copyright  2022-2023 WPExperts.io
	 * @copyright  2020-2023 Darko Gjorgjijoski
	 *
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
	 * Sets the default plugin options.
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
	 * Creates the default Digital License Manager roles.
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
	 * Removes the default Digital License Manager roles and capabilities
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
	 * @deprecated 1.5.0
	 */
	public static function restEndpoints() {
		return RestAPISetup::getEndpoints();
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

	/**
	 * Is the environment compatible?
	 * @return bool
	 */
	public static function isEnvCompatible() {
		return ! version_compare( phpversion(), self::MIN_PHP_VERSION, '<=' );
	}
}
