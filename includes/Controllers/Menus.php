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

namespace IdeoLogix\DigitalLicenseManager\Controllers;

use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Generators;
use IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus;
use IdeoLogix\DigitalLicenseManager\Enums\PageSlug;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Products;
use IdeoLogix\DigitalLicenseManager\ListTables\Activations;
use IdeoLogix\DigitalLicenseManager\ListTables\Generators as GeneratorsListTable;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
use IdeoLogix\DigitalLicenseManager\ListTables\Licenses as LicensesListTable;

defined( 'ABSPATH' ) || exit;

/**
 * Class Menus
 * @package IdeoLogix\DigitalLicenseManager\Controllers
 */
class Menus {

	/**
	 * @var Licenses
	 */
	private $licenses;

	/**
	 * @var Activations
	 */
	private $activations;

	/**
	 * @var Generators
	 */
	private $generators;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Plugin pages.
		add_action( 'admin_menu', array( $this, 'createPluginPages' ), 9 );

		// Screen options
		add_filter( 'set-screen-option', array( $this, 'setScreenOption' ), 10, 3 );

		// Footer text
		add_filter( 'admin_footer_text', array( $this, 'adminFooterText' ), 1 );

	}

	/**
	 * Return the submenu pages
	 * @return mixed|void
	 */
	private function getSubpages() {

		$pages = array(
			10 => array(
				'parent'         => PageSlug::LICENSES,
				'page_title'     => __( 'License Manager', 'digital-license-manager' ),
				'menu_title'     => __( 'Licenses', 'digital-license-manager' ),
				'capability'     => 'dlm_read_licenses',
				'menu_slug'      => PageSlug::LICENSES,
				'function'       => array( $this, 'licensesPage' ),
				'screen_options' => array( $this, 'licensesPageScreenOptions' ),
			),
			20 => array(
				'parent'         => PageSlug::LICENSES,
				'page_title'     => __( 'License Manager - Generators', 'digital-license-manager' ),
				'menu_title'     => __( 'Generators', 'digital-license-manager' ),
				'capability'     => 'dlm_read_generators',
				'menu_slug'      => PageSlug::GENERATORS,
				'function'       => array( $this, 'generatorsPage' ),
				'screen_options' => array( $this, 'generatorsPageScreenOptions' ),
			),
			30 => array(
				'parent'         => PageSlug::LICENSES,
				'page_title'     => __( 'License Manager - Activations', 'digital-license-manager' ),
				'menu_title'     => __( 'Activations', 'digital-license-manager' ),
				'capability'     => 'dlm_read_activations',
				'menu_slug'      => PageSlug::ACTIVATIONS,
				'function'       => array( $this, 'activationsPage' ),
				'screen_options' => array( $this, 'activationsPageScreenOptions' ),
			),
			40 => array(
				'parent'     => PageSlug::LICENSES,
				'page_title' => __( 'License Manager - Settings', 'digital-license-manager' ),
				'menu_title' => __( 'Settings', 'digital-license-manager' ),
				'capability' => 'dlm_manage_settings',
				'menu_slug'  => PageSlug::SETTINGS,
				'function'   => array( $this, 'settingsPage' ),
			)
		);

		$pages = apply_filters( 'dlm_admin_submenu_pages', $pages );
		ksort( $pages );

		return $pages;
	}

	/**
	 * Returns an array of all plugin pages.
	 *
	 * @return array
	 */
	public function getPluginPageIDs() {
		$pages = array(
			'toplevel_page_' . PageSlug::LICENSES,
		);
		foreach ( $this->getSubpages() as $subpage ) {
			array_push( $pages, 'license-manager_page_' . $subpage['menu_slug'] );
		}

		return $pages;
	}

	/**
	 * Sets up all necessary plugin pages.
	 */
	public function createPluginPages() {

		// Licenses List Page
		add_menu_page(
			__( 'License Manager', 'digital-license-manager' ),
			__( 'License Manager', 'digital-license-manager' ),
			'dlm_read_licenses',
			PageSlug::LICENSES,
			array( $this, 'licensesPage' ),
			'dashicons-admin-network',
			58
		);

		$required = array( 'parent', 'page_title', 'menu_title', 'capability', 'menu_slug', 'function' );

		foreach ( $this->getSubpages() as $subpage ) {
			$isOk = true;
			foreach ( $required as $key ) {
				if ( ! isset( $subpage[ $key ] ) ) {
					$isOk = false;
				}
			}
			if ( ! $isOk ) {
				continue;
			}
			$licensesHook = add_submenu_page(
				$subpage['parent'],
				$subpage['page_title'],
				$subpage['menu_title'],
				$subpage['capability'],
				$subpage['menu_slug'],
				$subpage['function']
			);
			if ( $licensesHook && isset( $subpage['screen_options'] ) ) {
				add_action( 'load-' . $licensesHook, $subpage['screen_options'] );
			}
		}
	}

	/**
	 * Adds the supported screen options for the licenses list.
	 */
	public function licensesPageScreenOptions() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Licenses per page', 'digital-license-manager' ),
			'default' => 10,
			'option'  => 'dlm_licenses_per_page'
		);

		add_screen_option( $option, $args );

		$this->licenses = new LicensesListTable();
	}

	/**
	 * Adds the supported screen options for the activations list
	 */
	public function activationsPageScreenOptions() {

		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Activations per page', 'digital-license-manager' ),
			'default' => 10,
			'option'  => 'dlm_activations_per_page'
		);

		add_screen_option( $option, $args );

		$this->activations = new Activations;
	}

	/**
	 * Adds the supported screen options for the generators list.
	 */
	public function generatorsPageScreenOptions() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Generators per page', 'digital-license-manager' ),
			'default' => 10,
			'option'  => 'generators_per_page'
		);

		add_screen_option( $option, $args );

		$this->generators = new GeneratorsListTable();
	}

	/**
	 * Sets up the licenses page.
	 */
	public function licensesPage() {
		$action           = $this->getCurrentAction( $default = 'list' );
		$licenses         = $this->licenses;
		$addLicenseUrl    = admin_url(
			sprintf(
				'admin.php?page=%s&action=add&_wpnonce=%s',
				PageSlug::LICENSES,
				wp_create_nonce( 'add' )
			)
		);
		$importLicenseUrl = admin_url(
			sprintf(
				'admin.php?page=%s&action=import&_wpnonce=%s',
				PageSlug::LICENSES,
				wp_create_nonce( 'import' )
			)
		);

		// Edit license keys
		if ( $action === 'edit' ) {

			/** @var License $license */
			$license   = Licenses::instance()->find( absint( $_GET['id'] ) );
			$expiresAt = null;

			if ( $license->getExpiresAt() ) {
				try {
					$expiresAtDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $license->getExpiresAt(), new \DateTimeZone('UTC'));
					$expiresAt         = $expiresAtDateTime->format( 'Y-m-d H:i:s' );
				} catch ( \Exception $e ) {
					$expiresAt = null;
				}
			}

			if ( ! $license ) {
				wp_die( __( 'Invalid license key ID', 'digital-license-manager' ) );
			}

			$licenseKey = $license->getDecryptedLicenseKey();
			if ( is_wp_error( $licenseKey ) ) {
				wp_die( $licenseKey->get_error_message() );
			}
		}

		// Edit, add or import license keys
		if ( $action === 'edit' || $action === 'add' || $action === 'import' ) {
			$statusOptions = LicenseStatus::dropdown();
		}

		include trailingslashit( DLM_TEMPLATES_DIR ) . 'admin/page-licenses.php';
	}

	/**
	 * Set up the activations page
	 */
	public function activationsPage() {

		$activations = $this->activations;
		$action      = $this->getCurrentAction( $default = 'list' );

		include trailingslashit( DLM_TEMPLATES_DIR ) . 'admin/page-activations.php';
	}

	/**
	 * Sets up the generators page.
	 */
	public function generatorsPage() {
		$generators = $this->generators;
		$action     = $this->getCurrentAction( $default = 'list' );

		// List generators
		if ( $action === 'list' || $action === 'delete' ) {
			$addGeneratorUrl = wp_nonce_url(
				sprintf(
					admin_url( 'admin.php?page=%s&action=add' ),
					PageSlug::GENERATORS
				),
				'dlm_add_generator'
			);
			$generateKeysUrl = wp_nonce_url(
				sprintf(
					admin_url( 'admin.php?page=%s&action=generate' ),
					PageSlug::GENERATORS
				),
				'dlm_generate_keys'
			);
		}

		// Edit generators
		if ( $action === 'edit' ) {

			if ( ! array_key_exists( 'edit', $_GET ) && ! array_key_exists( 'id', $_GET ) ) {
				return;
			}

			$generatorId = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : '';

			if ( ! $generator = Generators::instance()->find( $generatorId ) ) {
				return;
			}

			$products = Products::getByGenerator( $generatorId );
		}

		// Generate license keys
		if ( $action === 'generate' ) {
			$generatorsDropdown = Generators::instance()->findAll();
			$statusOptions      = LicenseStatus::dropdown();

			if ( ! $generatorsDropdown ) {
				$generatorsDropdown = array();
			}
		}

		include trailingslashit( DLM_TEMPLATES_DIR ) . 'admin/page-generators.php';
	}

	/**
	 * Sets up the settings page.
	 */
	public function settingsPage() {
		include trailingslashit( DLM_TEMPLATES_DIR ) . 'admin/page-settings.php';
	}


	/**
	 * Displays the new screen options.
	 *
	 * @param bool $keep
	 * @param string $option
	 * @param int $value
	 *
	 * @return int
	 */
	public function setScreenOption( $keep, $option, $value ) {
		return $value;
	}

	/**
	 * Sets the custom footer text for the plugin pages.
	 *
	 * @param string $footerText
	 *
	 * @return string
	 */
	public function adminFooterText( $footerText ) {
		if ( ! current_user_can( 'dlm_manage_settings' ) ) {
			return $footerText;
		}

		$currentScreen = get_current_screen();

		/**
		 * Check if the user is on any of the plugin's pages
		 */
		if ( isset( $currentScreen->id ) && in_array( $currentScreen->id, $this->getPluginPageIDs() ) ) {
			$footerText = sprintf(
				__( 'If you like %1$s please leave us a %2$s rating. A massive thanks in advance!', 'digital-license-manager' ),
				sprintf( '<strong>%s</strong>', esc_html__( 'Digital License Manager', 'digital-license-manager' ) ),
				'<a href="https://wordpress.org/support/plugin/digital-license-manager/reviews/?rate=5#new-post" target="_blank" class="wc-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'digital-license-manager' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
			if(!defined('DLM_PRO_VERSION')) {
			    $footerText .= '<br/>Need more functionality? Buy <a style="font-weight:bold;color:#3eb03e;" target="_blank" href="'.DLM_PURCHASE_URL.'"><strong>'.__('Digital License Manager PRO', 'wp-vimeo-videos').'</strong></a>';
            }
		}

		return $footerText;
	}

	/**
	 * Returns the string value of the "action" GET parameter.
	 *
	 * @param string $default
	 *
	 * @return string
	 */
	protected function getCurrentAction( $default ) {
		$action = $default;

		if ( ! isset( $_GET['action'] ) || ! is_string( $_GET['action'] ) ) {
			return $action;
		}

		return sanitize_text_field( $_GET['action'] );
	}

}
