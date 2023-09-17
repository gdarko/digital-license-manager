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

namespace IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractIntegrationController;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\IntegrationControllerInterface;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Tools\GeneratePastOrderLicenses\GeneratePastOrderLicenses;
use IdeoLogix\DigitalLicenseManager\Settings as SettingsData;

defined( 'ABSPATH' ) || exit;

/**
 * Class Controller
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce
 */
class Controller extends AbstractIntegrationController implements IntegrationControllerInterface {

	/**
	 * @var Emails
	 */
	public $emails;

	/**
	 * @var Orders
	 */
	public $orders;

	/**
	 * @var Stock
	 */
	public $stock;

	/**
	 * @var Products
	 */
	public $products;

	/**
	 * @var Settings
	 */
	public $settings;

	/**
	 * @var Certificates
	 */
	public $certificates;

	/**
	 * @var MyAccount
	 */
	public $myaccount;

	/**
	 * @var Activations
	 */
	public $activations;

	/**
	 * Controller constructor.
	 */
	public function __construct() {
		$this->bootstrap();

		add_filter( 'dlm_default_settings', array( $this, 'defaultWooCommerceSettings' ), 10, 1 );
		add_filter( 'dlm_dropdown_searchable_post_types', array( $this, 'dropdownSearchablePostTypes' ), 10, 1 );
		add_filter( 'dlm_dropdown_search_query_default_status', array( $this, 'dropdownSearchQDefaultStatus' ), 10, 2 );
		add_filter( 'dlm_dropdown_search_post_type', array( $this, 'dropdownSearchPostTypeResults' ), 10, 5 );
		add_filter( 'dlm_tools', array( $this, 'registerTools' ), 10, 1 );
	}

	/**
	 * Initializes the integration component
	 */
	private function bootstrap() {
		$this->stock    = new Stock();
		$this->orders   = new Orders();
		$this->emails   = new Emails();
		$this->products = new Products();
		$this->settings = new Settings();

		if ( Certificates::isLicenseCertificationEnabled() ) {
			$this->certificates = new Certificates();
		}

		if ( SettingsData::get( 'myaccount_endpoint', SettingsData::SECTION_WOOCOMMERCE ) ) {
			$this->myaccount   = new MyAccount();
			$this->activations = new Activations();
		}
	}

	/**
	 * Enable searchable post types to be products and orders
	 *
	 * @param $types
	 *
	 * @return array|string[]
	 */
	public function dropdownSearchablePostTypes( $types ) {

		if ( ! is_array( $types ) ) {
			$types = array();
		}

		return array_merge( $types, array(
			'product',
			'shop_order'
		) );
	}

	/**
	 * Search post type results
	 *
	 * @param $results
	 * @param $type
	 * @param $term
	 * @param $page
	 * @param $limit
	 *
	 * @return void
	 */
	public function dropdownSearchPostTypeResults( $results, $type, $term, $page, $limit ) {

		if ( 'shop_order' === $type ) {
			if ( is_numeric( $term ) ) {
				$order  = wc_get_order( (int) $term );
				$orders = $order ? [ $order ] : [];
				$more   = false;
			} else {
				$orders = [];
				$query  = wc_get_orders( [
					'customer' => $term,
					'paginate' => true,
					'paged'    => $page,
					'limit'    => $limit
				] );
				if ( ! empty( $query->orders ) ) {
					$orders = $query->orders;
				}
				$more   = $page < $query->max_num_pages;
			}
			$records = [];
			foreach ( $orders as $order ) {
				/* @var \WC_Order $order */
				$records[] = [
					'id'   => $order->get_id(),
					'text' => sprintf('#%d - %s %s', $order->get_id(), $order->get_billing_first_name(), $order->get_billing_last_name()),
					'meta' => $term
				];
			}
			$results = [ 'records' => $records, 'more' => $more ];

		}

		return $results;

	}


	/**
	 * Format order for search
	 *
	 * @param  \WC_Order  $order
	 *
	 * @return array
	 */
	protected function formatOrderForSearch( $order ) {
		return [
			'id'   => $order->get_id(),
			'text' => sprintf( '#%d - %s%s', $id, $title, $type )
		];
	}

	/**
	 * Default search query status for shop order.
	 *
	 * @param $status
	 * @param $type
	 *
	 * @return array|string
	 */
	public function dropdownSearchQDefaultStatus( $status, $type ) {
		if ( 'shop_order' === $type ) {
			$status = array_keys( wc_get_order_statuses() );
		}

		return $status;
	}

	/**
	 * Default settings
	 *
	 * @param $settings
	 *
	 * @return array
	 */
	public function defaultWooCommerceSettings( $settings ) {

		if ( ! isset( $settings[ SettingsData::SECTION_WOOCOMMERCE ] ) ) {

			$default_settings = array(
				'myaccount_endpoint'        => 1,
				'auto_delivery'             => 1,
				'enable_activations_table'  => 1,
				'enable_manual_activations' => 0,
				'enable_certificates'       => 1,
				'order_delivery_statuses'   => array(
					'wc-completed'  => array(
						'send' => '1'
					),
					'wc-processing' => array(
						'send' => '1',
					)
				)
			);

			$settings[ SettingsData::SECTION_WOOCOMMERCE ] = apply_filters( 'dlm_default_woocommerce_settings', $default_settings );
		}

		return $settings;
	}

	/**
	 * Add additional tools
	 *
	 * @param $tools
	 *
	 * @return array
	 */
	public function registerTools( $tools ) {
		if ( ! is_array( $tools ) || empty( $tools ) ) {
			$tools = [];
		}

		if ( ! isset( $tools['generate_past_order_licenses'] ) ) {
			$tools['generate_past_order_licenses'] = GeneratePastOrderLicenses::class;
		}

		return $tools;
	}

	/**
	 * Return the WooCommerce template path
	 * @return string
	 */
	public static function getTemplatePath() {
		return trailingslashit( DLM_TEMPLATES_DIR ) . 'woocommerce' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Return license url
	 *
	 * @param $license
	 *
	 * @return string|null
	 */
	public static function getAccountLicenseUrl( $license_id ) {
		return esc_url( wc_get_account_endpoint_url( 'digital-licenses/' . $license_id ) );
	}
}
