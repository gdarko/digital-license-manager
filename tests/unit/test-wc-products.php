<?php
/**
 * Class DLMPRO_SubscriptionsTestCase
 *
 * @package Digital_License_Manager_Pro
 */

/**
 * Sample test case.
 */
class DLM_Products_TestCase extends WP_UnitTestCase {

	public function test_simple_products_with_generators() {

		$args = [
			'license_source'    => 'generators',
			'generator'         => 'new',
			'expires_in'        => 365,
			'activations_limit' => 1,
		];

		$product = DLM_Helper_Licensed_Products::create_simple_product( $args );

		$this->assertEquals( $product->get_meta( 'dlm_licensed_product_licenses_source' ), $args['license_source'] );
		$generator = DLM_Helper_Generator::find( (int) $product->get_meta( 'dlm_licensed_product_assigned_generator' ) );

		$this->assertNotInstanceOf( \WP_Error::class, $generator );
		$this->assertEquals( $generator->getExpiresIn(), $args['expires_in'] );
		$this->assertEquals( $generator->getActivationsLimit(), $args['activations_limit'] );

	}

	public function test_simple_products_with_stock() {

		$args    = [
			'license_source'    => 'stock',
			'max_products'      => 10,
			'expires_in'        => 365,
			'activations_limit' => 1,
		];
		$product = DLM_Helper_Licensed_Products::create_simple_product( $args );

		$this->assertEquals( $product->get_meta( 'dlm_licensed_product_licenses_source' ), 'stock' );
		$licenses = DLM_Helper_License::get( [ 'product_id' => $product->get_id() ] );
		$this->assertGreaterThanOrEqual( is_array( $licenses ) ? count( $licenses ) : [], (int) $args['max_products'] );

	}

	public function test_variable_products_with_generators() {

		$product = DLM_Helper_Licensed_Products::create_variable_product();
		$this->assertInstanceOf( WC_Product_Variable::class, $product );
		$variations = $product->get_children();
		$variation  = array_rand( $variations );
		$this->assertInstanceOf( WC_Product_Variation::class, wc_get_product( $variations[ $variation ] ) );

	}
}
