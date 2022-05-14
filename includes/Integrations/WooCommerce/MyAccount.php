<?php

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Utils\Data\Customer;
use IdeoLogix\DigitalLicenseManager\Utils\Data\License as LicenseUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class MyAccount
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce
 */
class MyAccount {
	/**
	 * MyAccount constructor.
	 */
	public function __construct() {
		add_rewrite_endpoint( 'digital-licenses', EP_ROOT | EP_PAGES );

		add_filter( 'the_title', array( $this, 'accountItemTitles' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'accountMenuItems' ), 10, 1 );
		add_action( 'woocommerce_account_digital-licenses_endpoint', array( $this, 'digitalLicenses' ) );
		add_filter( 'dlm_myaccount_licenses_row_actions', array( $this, 'licensesRowActions' ), 10, 3 );
		add_filter( 'dlm_myaccount_licenses_keys_row_actions', array( $this, 'licensesRowActions' ), 10, 3 );
		add_action( 'dlm_myaccount_licenses_single_page_content', array( $this, 'addSingleLicenseContent' ), 10, 2 );
		add_action( 'dlm_myaccount_licenses_single_page_end', array( $this, 'addSingleLicenseActivationsTable' ), 10, 5 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ), 10, 1 );
	}

	/**
	 * Enqueue scripts
	 * @return void
	 */
	public function enqueueScripts() {
		wp_enqueue_style( 'dlm-public', DLM_PLUGIN_URL . 'assets/css/public.css', array(), filemtime( DLM_ABSPATH . 'assets/css/public.css' ), 'all' );
	}

	/**
	 * Change page titles
	 */
	public function accountItemTitles( $title ) {
		global $wp_query;

		if ( isset( $wp_query->query_vars['digital-licenses'] ) && in_the_loop() ) {
			return __( 'Licenses', 'digital-license-manager' );
		}

		return $title;
	}

	/**
	 * Adds the plugin pages to the "My account" section.
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function accountMenuItems( $items ) {
		$customItems = array(
			'digital-licenses' => __( 'Licenses', 'digital-license-manager' )
		);

		$customItems = array_slice( $items, 0, 2, true ) + $customItems + array_slice( $items, 2, count( $items ), true );

		return $customItems;
	}

	/**
	 * Creates an overview of all purchased license keys.
	 */
	public function digitalLicenses() {

		global $wp_query;

		wp_enqueue_style( 'dlm_main' );

		$user             = wp_get_current_user();
		$message          = new \stdClass();
		$message->type    = '';
		$message->message = '';

		if ( ! $user ) {
			return;
		}

		$licenseKey = null;
		$paged      = 1;

		// Parse query parameters.
		if ( $wp_query->query['digital-licenses'] ) {
			$page = $wp_query->query['digital-licenses'];
			if ( ! empty( $page ) ) {
				$parts = explode( '/', $page );
				if ( count( $parts ) === 2 && $parts[0] === 'page' ) {
					$paged = (int) $parts[1];
				} else {
					$licenseKey = sanitize_text_field( $parts[0] );
				}
			}
		}

		if ( ! $licenseKey ) {

			$licenses = Customer::getLicenseKeys( $user->ID );

			echo wc_get_template_html(
				'dlm/my-account/licenses/index.php',
				array(
					'licenses'    => $licenses,
					'message'     => $message,
					'page'        => $paged,
					'date_format' => get_option( 'date_format' ),
				),
				'',
				Controller::getTemplatePath()
			);

		} else {

			$license = LicenseUtil::find( $licenseKey );

			if ( is_wp_error( $license ) || $license->getUserId() != $user->ID ) {
				echo sprintf( '<h3>%s</h3>', __( 'Not found', 'digital-license-manager' ) );
				echo sprintf( '<p>%s</p>', __( 'The license you are looking for is not found.', 'digital-license-manager' ) );

				return;

			}

			$decrypted = $license->getDecryptedLicenseKey();
			if ( is_wp_error( $decrypted ) ) {
				echo sprintf( '<p>%s</p>', $decrypted->get_error_message() );

				return;
			}

			do_action( 'dlm_myaccount_licenses_single_page_content', $license, $licenseKey );

		}

	}

	/**
	 * License actions
	 *
	 * @param array $actions
	 * @param LicenseResourceModel $license
	 * @param string $licenseKey
	 *
	 * @return array
	 */
	public function licensesRowActions( $actions, $license, $licenseKey ) {

		if ( Settings::get( 'myaccount_endpoint', Settings::SECTION_WOOCOMMERCE ) ) {
			$actions[5] = array(
				'href'  => esc_url( wc_get_account_endpoint_url( 'digital-licenses/' . $licenseKey ) ),
				'class' => 'button',
				'text'  => __( 'View', 'digital-license-manager-pro' ),
				'title' => __( 'View more details about this license.', 'digital-license-manager-pro' ),
			);
		}

		return $actions;
	}

	/**
	 * Single license page
	 *
	 * @param LicenseResourceModel $license
	 */
	public function addSingleLicenseContent( $license, $licenseKey ) {

		do_action( 'dlm_myaccount_single_page', $license, $licenseKey );

		if ( get_current_user_id() !== (int) $license->getUserId() ) {
			_e( 'Permission denied', 'digital-license-manager-pro' );
		} else {
			echo wc_get_template_html(
				'dlm/my-account/licenses/single.php',
				array(
					'license'    => $license,
					'license_key' => $licenseKey,
					'product'    => ! empty( $license->getProductId() ) ? wc_get_product( $license->getProductId() ) : null,
					'order'      => ! empty( $license->getOrderId() ) ? wc_get_order( $license->getOrderId() ) : null,
					'date_format' => get_option( 'date_format' ),
				),
				'',
				Controller::getTemplatePath()
			);
		}
	}

	/**
	 * Add the software table to the single license page.
	 *
	 * @param $license
	 * @param $order
	 * @param $product
	 * @param $dateFormat
	 * @param $licenseKey
	 *
	 * @return void
	 */
	public function addSingleLicenseActivationsTable($license, $order, $product, $dateFormat, $licenseKey) {
		echo wc_get_template_html(
			'dlm/my-account/licenses/single-table-activations.php',
			array(
				'license'     => $license,
				'license_key' => $licenseKey,
				'product'     => $product,
				'order'       => $order,
				'date_format' => $dateFormat,
				'nonce'       => wp_create_nonce( 'dlm_nonce' ),
			),
			'',
			Controller::getTemplatePath()
		);
	}
}
