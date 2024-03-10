<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2024  Darko Gjorgjijoski. All Rights Reserved.
 * Copyright (C) 2020-2024  IDEOLOGIX MEDIA DOOEL. All Rights Reserved.
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

/**
 * Class DLM_Orders_TestCase
 *
 * @package Digital_License_Manager_Pro
 */
class DLM_Orders_TestCase extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	public function test_order_process() {

		$product = DLM_Helper_Licensed_Products::create_simple_product( [
			'license_source'    => 'generators',
			'generator'         => 'new',
			'expires_in'        => 365,
			'activations_limit' => 1,
		] );

		$this->assertIsObject( $product );
		$this->assertInstanceOf( WC_Product_Simple::class, $product );
		$this->assertEquals(  'generators', $product->get_meta( 'dlm_licensed_product_licenses_source' ) );

		$unique_id = mt_rand( 100000, 200000 );
		$user_id   = wp_insert_user( [
			'user_login' => 'tester_' . $unique_id,
			'user_email' => 'tester_' . $unique_id . '@woo.test',
			'user_pass'  => wp_generate_password(),
		] );

		$this->assertIsInt( $user_id );
		$order = DLM_WC_Helper_Order::create_order( $user_id, $product );
		$this->assertEquals( $order->get_status(), 'pending' );

		$order->set_status( 'completed' );
		$order->save();

		sleep(3); // Allow some time for hooks to trigger.

		$licenses = DLM_Helper_License::get( [ 'order_id' => $order->get_id() ] );

		$this->assertIsArray( $licenses );
		$this->assertGreaterThanOrEqual( 1, count( $licenses ) );
	}
}
