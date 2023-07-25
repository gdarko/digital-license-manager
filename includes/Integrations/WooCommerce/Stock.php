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

use IdeoLogix\DigitalLicenseManager\Settings;
use IdeoLogix\DigitalLicenseManager\Core\Services\LicensesService;
use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Class Stock
 * @package IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce
 */
class Stock {
	/**
	 * Stock constructor.
	 */
	public function __construct() {

		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array(
			$this,
			'handleCustomQueryVars'
		), 10, 2 );
	}

	/**
	 * Class internal function used to modify the stock amount.
	 *
	 * @param int|WC_Product $product
	 * @param string $action
	 * @param int $amount
	 *
	 * @return bool|WC_Product
	 */
	private static function modify( $product, $action, $amount = 1 ) {
		// Check if the setting is enabled
		if ( ! Settings::get( 'stock_management', Settings::SECTION_WOOCOMMERCE ) ) {
			return false;
		}

		// Retrieve the WooCommerce Product if we're given an ID
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		// No need to modify if WooCommerce is not managing the stock
		if ( ! $product instanceof WC_Product || ! $product->managing_stock() ) {
			return false;
		}

		// Retrieve the current stock
		$stock = $product->get_stock_quantity();

		// Normalize
		if ( $stock === null ) {
			$stock = 0;
		}

		// Add or subtract the given amount to the stock
		if ( $action === 'increase' ) {
			$stock += $amount;
		} elseif ( $action === 'decrease' ) {
			$stock -= $amount;
		}

		// Set and save
		$product->set_stock_quantity( $stock );
		$product->save();

		return $product;
	}

	/**
	 * Increases the available stock of a WooCommerce Product by $amount.
	 *
	 * @param int|WC_Product $product WooCommerce Product object
	 * @param int $amount Increment amount
	 *
	 * @return bool|WC_Product
	 */
	public static function increase( $product, $amount = 1 ) {
		return self::modify( $product, 'increase', $amount );
	}

	/**
	 * Decreases the available stock of a WooCommerce Product by $amount.
	 *
	 * @param int|WC_Product $product WooCommerce Product object
	 * @param int $amount Decrement amount
	 *
	 * @return bool|WC_Product
	 */
	public static function decrease( $product, $amount = 1 ) {
		return self::modify( $product, 'decrease', $amount );
	}

	/**
	 * Synchronizes the license stock with the WooCommerce products stock.
	 * Returns the number of synchronized WooCommerce products.
	 *
	 * @return int
	 */
	public static function synchronize() {
		// For the query to return any results, the following WooCommerce Product settings need to be enabled:
		// 1. Inventory       -> Manage stock?
		// 2. License Manager -> Sell Licenses
		// 3. License Manager -> Licenses source -> Provide licenses from stock
		$args = [
			'limit'                                => - 1,
			'type'                                 => [ 'simple', 'subscription', 'variation' ],
			'orderBy'                              => 'id',
			'order'                                => 'ASC',
			'manage_stock'                         => true,
			'dlm_licensed_product'                 => '1',
			'dlm_licensed_product_licenses_source' => 'stock',
		];


		$products     = wc_get_products( $args );
		$synchronized = 0;

		// No such products, nothing to do
		if ( count( $products ) === 0 ) {
			return $synchronized;
		}

		/** @var WC_Product $product */
		foreach ( $products as $product ) {

			self::syncrhonizeProductStock( $product );
			$synchronized ++;
		}

		return $synchronized;
	}

	/**
	 * Syncrhonize a single product stock and the number of licenses assigned to it.
	 * Basically, copy the number of licenses assigned to the product and are marked as "ACTIVE" to the stock quantity number.
	 *
	 * @param $product
	 *
	 * @return bool|void
	 */
	public static function syncrhonizeProductStock( $product ) {

		$licenseService = new LicensesService();

		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! $product ) {
			return false;
		}

		if ( ! $product->get_manage_stock() ) {
			return false;
		}

		$woocommerceStock = (int) $product->get_stock_quantity();
		$licenseStock     = $licenseService->getLicensesStockCount( $product->get_id() );

		// Nothing to do in this case
		if ( $woocommerceStock === $licenseStock ) {
			return true;
		}

		// Update the stock
		if ( $licenseStock > 0 ) {
			$product->set_stock_status( 'instock' );
		} else {
			$product->set_stock_status( 'outofstock' );
		}
		$product->set_stock_quantity( $licenseStock );
		$product->save();

		return true;
	}

	/**
	 * @param array $query
	 * @param array $query_vars
	 *
	 * @return mixed
	 */
	public function handleCustomQueryVars( $query, $query_vars ) {
		if ( ! empty( $query_vars['dlm_licensed_product'] ) ) {
			$query['meta_query'][] = array(
				'key'   => 'dlm_licensed_product',
				'value' => esc_attr( $query_vars['dlm_licensed_product'] )
			);
		}

		if ( ! empty( $query_vars['dlm_licensed_product_licenses_source'] ) ) {
			$query['meta_query'][] = array(
				'key'   => 'dlm_licensed_product_licenses_source',
				'value' => esc_attr( $query_vars['dlm_licensed_product_licenses_source'] )
			);
		}

		return $query;
	}
}
