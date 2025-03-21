<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-present  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-present  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
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

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use IdeoLogix\DigitalLicenseManager\Database\Models\License;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Licenses;
use IdeoLogix\DigitalLicenseManager\Enums\LicensePrivateStatus;
use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Utils\ArrayFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\HttpHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Class MyAccount
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce
 */
class MyAccount {

	protected $endpoints = [];

	/**
	 * MyAccount constructor.
	 */
	public function __construct() {

		$this->rewriteEndpoints();
		add_action( 'init', [ $this, 'rewriteEndpoints' ], 9 );
		add_filter( 'the_title', array( $this, 'accountItemTitles' ) );
		add_action( 'template_redirect', array( $this, 'handleAccountActions' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'accountMenuItems' ), 10, 1 );
		add_action( 'woocommerce_account_digital-licenses_endpoint', array( $this, 'digitalLicenses' ) );
		add_filter( 'dlm_myaccount_licenses_row_actions', array( $this, 'licensesRowActions' ), 10, 3 );
		add_filter( 'dlm_myaccount_order_licenses_row_actions', array( $this, 'licensesRowActions' ), 10, 3 );
		add_action( 'dlm_myaccount_licenses_single_page_content', array( $this, 'addSingleLicenseContent' ), 10, 1 );
		add_action( 'dlm_myaccount_licenses_single_page_end', array( $this, 'addSingleLicenseActivationsTable' ), 10, 5 );
		add_action( 'dlm_register_scripts', array( $this, 'registerScripts' ), 10, 1 );
		add_action( 'dlm_enqueue_scripts', array( $this, 'enqueueScripts' ), 10, 1 );
		add_filter( 'dlm_is_order_page', array( $this, 'isOrderPage' ), 10, 2 );
		add_filter( 'dlm_is_product_page', array( $this, 'isProductPage' ), 10, 2 );
	}

	/**
	 * Register the rewrite endpoints
	 * @return void
	 * @since 1.6.0
	 */
	public function rewriteEndpoints() {

		$this->endpoints = apply_filters( 'dlm_myaccount_rewrite_endpoints', [
			'digital-licenses' => EP_ROOT | EP_PAGES,
		] );

		foreach ( $this->endpoints as $slug => $places ) {
			add_rewrite_endpoint( $slug, $places );
		}
	}

	/**
	 * Handle account actions
	 * @return void
	 */
	public function handleAccountActions() {

		$action = isset( $_REQUEST['dlm_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['dlm_action'] ) ) : '';

		$whitelisted_actions = apply_filters( 'dlm_myaccount_whitelisted_actions', array() );

		$action_method = isset( $whitelisted_actions[ $action ]['method'] ) ? $whitelisted_actions[ $action ]['method'] : 'POST';

		if ( empty( $whitelisted_actions ) || ! array_key_exists( $action, $whitelisted_actions ) ) {
			return;
		}

		if ( $action_method != HttpHelper::requestMethod() ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			$login_url = wc_get_page_permalink( 'myaccount' );
			if ( 'GET' == strtoupper( $action_method ) ) {
				$redirect_to = add_query_arg( $_GET, self::getProcessingEndpointUrl() );
				$login_url   = add_query_arg( [ 'redirect_to' => urlencode( $redirect_to ) ], $login_url );
			}
			wp_redirect( esc_url( $login_url ) );
			exit;
		}

		$check_nonce = isset( $whitelisted_actions[ $action ]['nonce'] ) && $whitelisted_actions[ $action ]['nonce'];
		if ( $check_nonce ) {
			$nonce = isset( $_REQUEST['dlm_nonce'] ) ? sanitize_key( $_REQUEST['dlm_nonce'] ) : '';
			if ( ! wp_verify_nonce( $nonce, 'dlm_account' ) ) {
				wp_die( 'Link has expired. Please try again later.', 'digital-license-manager' );
			}
		}

		do_action( 'dlm_myaccount_handle_action_' . $action );
		do_action( 'dlm_myaccount_handle_action', $action );
		exit;
	}

	/**
	 * Registers the scripts
	 * @return void
	 */
	public function registerScripts( $version ) {
		wp_register_style( 'dlm_myaccount', DLM_CSS_URL . 'public/account.css', array( 'dlm_global', 'dlm_iconfont', 'dlm_micromodal' ), $version );
		wp_register_script( 'dlm_myaccount', DLM_JS_URL . 'public/account.js', array( 'dlm_micromodal' ), $version );
	}

	/**
	 * Enqueues the scripts
	 * @return void
	 */
	public function enqueueScripts( $version ) {
		if ( ! is_account_page() && ! is_order_received_page() ) {
			return;
		}
		wp_enqueue_script( 'dlm_myaccount' );
		wp_enqueue_style( 'dlm_myaccount' );
		wp_localize_script( 'dlm_myaccount', 'DLM_MyAccount', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_wpnonce' => wp_create_nonce( Activations::NONCE ),
			'i18n'     => [
				'copiedToClipboard' => __( 'Key copied to clipboard.', 'digital-license-manager' )
			]
		] );
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

			$licenses = self::getLicenses( $user->ID );

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

			$licenseService = new LicensesService();
			$license        = $licenseService->findById( $licenseID );

			if ( is_wp_error( $license ) || $license->getUserId() !== $user->ID ) {
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
	 * @param License $license
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
	 * @param License $license
	 */
	public function addSingleLicenseContent( $license ) {

		do_action( 'dlm_myaccount_single_page', $license, $license );

		if ( get_current_user_id() !== (int) $license->getUserId() ) {
			_e( 'Permission denied', 'digital-license-manager' );
		} else {
			echo wc_get_template_html(
				'dlm/my-account/licenses/single.php',
				array(
					'license'          => $license,
					'license_key'      => $license->getDecryptedLicenseKey(),
					'license_key_html' => wc_get_template_html( 'dlm/my-account/licenses/partials/license-key.php', array(
						'license' => $license,
					), '', Controller::getTemplatePath() ),
					'product'          => ! empty( $license->getProductId() ) ? wc_get_product( $license->getProductId() ) : null,
					'order'            => ! empty( $license->getOrderId() ) ? wc_get_order( $license->getOrderId() ) : null,
					'date_format'      => get_option( 'date_format' ),
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

		echo self::getLicenseActivationsTable( $license, $order, $product, $dateFormat, $licenseKey );

	}

	/**
	 * Is the single order page
	 *
	 * @param $enabled
	 * @param $hook
	 *
	 * @return bool
	 */
	public function isOrderPage( $enabled, $hook ) {
		global $post_type;
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return 'woocommerce_page_wc-orders' === $hook && isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'];
		} else {
			return in_array( $hook, array( 'post.php', 'post-new.php' ) ) && 'shop_order' === $post_type;
		}

	}

	/**
	 * Is the single product page
	 *
	 * @param $enabled
	 * @param $hook
	 *
	 * @return bool
	 */
	public function isProductPage( $enabled, $hook ) {
		global $post_type;

		return in_array( $hook, array( 'post.php', 'post-new.php' ) ) && 'product' === $post_type;
	}

	/**
	 * Prints out the licenses activation table
	 *
	 * @param License $license
	 * @param $order
	 * @param $product
	 * @param $dateFormat
	 * @param $licenseKey
	 *
	 * @return string
	 */
	public static function getLicenseActivationsTable( $license, $order = null, $product = null, $dateFormat = null, $licenseKey = null ) {

		if ( is_null( $order ) ) {
			$order = wc_get_order( $license->getOrderId() );
		}

		if ( is_null( $product ) ) {
			$product = wc_get_order( $license->getProductId() );
		}

		if ( is_null( $dateFormat ) ) {
			$dateFormat = get_option( 'date_format' );
		}

		if ( is_null( $licenseKey ) ) {
			$licenseKey = $license->getDecryptedLicenseKey();
		}

		$manual_activations_enabled = (int) Settings::get( 'enable_manual_activations', Settings::SECTION_WOOCOMMERCE );

		$row_actions = apply_filters( 'dlm_myaccount_license_activation_row_actions', array(), $license, $order, $product );

		usort( $row_actions, [ ArrayFormatter::class, 'prioritySort' ] );

		if ( in_array( $license->getStatus(), [ LicensePrivateStatus::DISABLED, LicensePrivateStatus::INACTIVE ] ) ) {
			$row_actions = [];
			$manual_activations_enabled = false;
		}

		return wc_get_template_html(
			'dlm/my-account/licenses/partials/single-table-activations.php',
			array(
				'license'                    => $license,
				'license_key'                => $licenseKey,
				'product'                    => $product,
				'order'                      => $order,
				'date_format'                => $dateFormat,
				'manual_activations_enabled' => $manual_activations_enabled,
				'can_activate'               => $can_activate,
				'rowActions'                 => $row_actions,
				'activations'                => $license->getActivations(),
				'nonce'                      => wp_create_nonce( 'dlm_nonce' ),
			),
			'',
			Controller::getTemplatePath()
		);
	}

	/**
	 * Get licenses for a customer
	 *
	 * @param $userId
	 *
	 * @return array
	 */
	public static function getLicenses( $userId ) {

		if ( ! function_exists( 'wc_get_product' ) ) {
			return array();
		}

		/**
		 * Filter the licenses queried in the MyAccount.
		 */
		$query = apply_filters( 'dlm_myaccount_licenses_query', array() );


		/** @var License[] $licenses */
		$licenses = Licenses::instance()->findAllBy( array_merge( $query, array( 'user_id' => get_current_user_id() ) ) );

		$result = array();

		foreach ( $licenses as $license ) {

			$product_id = $license->getProductId();
			$product    = wc_get_product( $product_id );
			if ( ! isset( $result[ $product_id ] ) ) {
				$result[ $product_id ] = array();
			}
			if ( ! $product ) {
				$result[ $product_id ]['name'] = '#' . $license->getProductId();
			} else {
				$result[ $product_id ]['name'] = $product->get_formatted_name();
			}
			$result[ $product_id ]['licenses'][] = $license;
		}

		return $result;
	}

	/**
	 * Returns the processing endpoint url
	 *
	 * @return string
	 * @since 1.5.6
	 *
	 */
	public static function getProcessingEndpointUrl() {
		return apply_filters( 'dlm_myaccount_processing_endpoint_url', add_query_arg( [ 'dlm_action_handler' => 1 ], trailingslashit( home_url() ) ) );
	}

}