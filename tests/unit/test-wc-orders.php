<?php
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
