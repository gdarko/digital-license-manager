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

namespace IdeoLogix\DigitalLicenseManager\Integrations\WCPIPS;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractIntegrationController;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\IntegrationControllerInterface;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Orders;

/**
 * Integration: "PDF Invoices & Packing Slips for WooCommerce"
 * @url https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/
 * @since 1.5.1
 */
class Controller extends AbstractIntegrationController implements IntegrationControllerInterface {

	/**
	 * Constructor
	 *
	 * @since 1.5.1
	 */
	public function __construct() {
		add_action( 'wpo_wcpdf_before_item_meta', [ $this, 'showLicenses' ], 10, 3 );
	}


	/**
	 * Show all the licenses
	 *
	 * @param string $type
	 * @param \WC_Order_Item_Product $item
	 * @param \WC_Order $order
	 *
	 * @return void
	 * @since 1.5.1
	 *
	 */
	public function showLicenses( $type, $item, $order ) {

		if ( ! apply_filters( 'dlm_wcpips_show_in_line_item', true ) ) {
			return;
		}

		if ( ! in_array( $type, apply_filters( 'dlm_wcpips_allowed_types', [ 'invoice' ] ) ) ) {
			return;
		}

		$html = apply_filters( 'dlm_wcpips_line_item', null, $item, $order );

		if ( ! empty( $html ) ) {
			echo $html;

			return;
		}

		$data = Orders::getLicenses( [ 'order' => $order ] );

		if ( ! empty( $data['data'] ) ) {
			foreach ( $data['data'] as $item ) {

				if ( empty( $item['keys'] ) ) {
					continue;
				}

				echo '<p style="margin-bottom: 0; margin-top:5px; font-weight:bold; padding-left:5px; font-size:7pt;">' . __( 'Licenses:', 'digital-license-manager' ) . '</p>';

				$licenses = array_map( function ( $item ) {
					return sprintf( '<span style="background-color: green; color: #fff; padding: 1px 8px; border-radius: 15px; font-weight:bold;">%s</span>', $item->getDecryptedLicenseKey() );
				}, $item['keys'] );


				echo '<p style="margin-top:0;">' . implode( ', ', $licenses ) . '</p>';

			}
		}


	}

}