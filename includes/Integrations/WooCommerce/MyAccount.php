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
		add_action( 'template_redirect', array( $this, 'handleAccountActions' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'accountMenuItems' ), 10, 1 );
		add_action( 'woocommerce_account_digital-licenses_endpoint', array( $this, 'digitalLicenses' ) );
		add_filter( 'dlm_myaccount_licenses_row_actions', array( $this, 'licensesRowActions' ), 10, 3 );
		add_filter( 'dlm_myaccount_licenses_keys_row_actions', array( $this, 'licensesRowActions' ), 10, 3 );
		add_action( 'dlm_myaccount_licenses_single_page_content', array( $this, 'addSingleLicenseContent' ), 10, 1 );
		add_action( 'dlm_myaccount_licenses_single_page_end', array( $this, 'addSingleLicenseActivationsTable' ), 10, 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ), 10, 1 );
	}

	/**
	 * Handle account actions
	 * @return void
	 */
	public function handleAccountActions() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			return;
		}

		$action              = isset( $_POST['dlm_action'] ) ? sanitize_text_field( $_POST['dlm_action'] ) : '';
		$whitelisted_actions = apply_filters( 'dlm_myaccount_whitelisted_actions', array() );
		if ( empty( $whitelisted_actions ) || ! in_array( $action, $whitelisted_actions ) ) {
			return;
		}

		$nonce = isset( $_POST['dlm_nonce'] ) ? sanitize_text_field( $_POST['dlm_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'dlm_account' ) ) {
			wp_die( 'Link has expired. Please try again later.', 'digital-license-manager' );
		}


		do_action( 'dlm_myaccount_handle_action_' . $action );
		do_action( 'dlm_myaccount_handle_action', $action );
		exit;
	}

	/**
	 * Enqueue scripts
	 * @return void
	 */
	public function enqueueScripts() {

		if ( ! is_account_page() ) {
			return;
		}
		wp_enqueue_style( 'dlm_global' );
		wp_enqueue_style( 'dlm_public' );
	}

	/**
	 * Change page titles
	 */
	public function accountItemTitles( $title ) {
		global $wp_query;

		$in_the_loop = in_the_loop();

		if ( isset( $wp_query->query_vars['digital-licenses'] ) && $in_the_loop ) {
			return __( 'Licenses', 'digital-license-manager' );
		}

		return apply_filters( 'dlm_myaccount_endpoint_title', $title, $in_the_loop );
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

		$customItems = apply_filters( 'dlm_myaccount_menu_items', $customItems );

		$customItems = array_slice( $items, 0, 2, true ) + $customItems + array_slice( $items, 2, count( $items ), true );

		return $customItems;
	}

	/**
	 * Creates an overview of all purchased license keys.
	 */
	public function digitalLicenses() {

		global $wp_query;

		$user             = wp_get_current_user();
		$message          = new \stdClass();
		$message->type    = '';
		$message->message = '';

		if ( ! $user ) {
			return;
		}

		$licenseID = null;
		$paged     = 1;

		// Parse query parameters.
		if ( $wp_query->query['digital-licenses'] ) {
			$page = $wp_query->query['digital-licenses'];
			if ( ! empty( $page ) ) {
				$parts = explode( '/', $page );
				if ( count( $parts ) === 2 && $parts[0] === 'page' ) {
					$paged = (int) $parts[1];
				} else {
					$licenseID = sanitize_text_field( $parts[0] );
				}
			}
		}

		if ( ! $licenseID ) {

			$licenses = Customer::getLicenses( $user->ID );

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

			$license = LicenseUtil::findById( $licenseID );

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

			do_action( 'dlm_myaccount_licenses_single_page_content', $license );

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
				'href'  => esc_url( Controller::getAccountLicenseUrl( $license->getId() ) ),
				'class' => 'button',
				'text'  => __( 'View', 'digital-license-manager' ),
				'title' => __( 'View more details about this license.', 'digital-license-manager' ),
			);
		}

		return $actions;
	}

	/**
	 * Single license page
	 *
	 * @param LicenseResourceModel $license
	 */
	public function addSingleLicenseContent( $license ) {

		do_action( 'dlm_myaccount_single_page', $license, $license );

		if ( get_current_user_id() !== (int) $license->getUserId() ) {
			_e( 'Permission denied', 'digital-license-manager' );
		} else {
			echo wc_get_template_html(
				'dlm/my-account/licenses/single.php',
				array(
					'license'     => $license,
					'license_key' => $license->getDecryptedLicenseKey(),
					'product'     => ! empty( $license->getProductId() ) ? wc_get_product( $license->getProductId() ) : null,
					'order'       => ! empty( $license->getOrderId() ) ? wc_get_order( $license->getOrderId() ) : null,
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
	public function addSingleLicenseActivationsTable( $license, $order, $product, $dateFormat, $licenseKey ) {

		$is_enabled = (int) Settings::get( 'enable_activations_table', Settings::SECTION_WOOCOMMERCE );

		if ( ! $is_enabled ) {
			return;
		}

		echo wc_get_template_html(
			'dlm/my-account/licenses/partials/single-table-activations.php',
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
