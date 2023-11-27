<?php

class DLM_Helpers_TestCase extends WP_UnitTestCase {

	public function test_license_helpers() {

		// Create licenses
		$result = dlm_create_license( 'BBBB-CCCC-FFFF-EEEE', [
			'product_id'        => 1,
			'order_id'          => 1,
			'user_id'           => 1,
			'expires_at'        => '2024-10-15 11:00:00',
			'source'            => 1,
			'activations_limit' => 2,
			'status'            => 2,
		] );

		$result = dlm_create_license( 'AAAA-BBBB-CCCC-DDDD', [
			'product_id'        => 1,
			'order_id'          => 1,
			'user_id'           => 1,
			'expires_at'        => '2024-10-15 11:00:00',
			'source'            => 1,
			'activations_limit' => 2,
			'status'            => 2,
		] );

		$this->assertEquals( $result->getDecryptedLicenseKey(), 'AAAA-BBBB-CCCC-DDDD' );
		$this->assertEquals(2, $result->getActivationsLimit());

		// Update license
		$result = dlm_update_license( 'AAAA-BBBB-CCCC-DDDD', [
			'product_id'        => 2
		] );
		$this->assertEquals(2, $result->getProductId());

		// Activate licenses
		$result = dlm_activate_license('AAAA-BBBB-CCCC-DDDD', ['label' => 'test']);
		$this->assertInstanceOf(\IdeoLogix\DigitalLicenseManager\Database\Models\LicenseActivation::class, $result);
		$this->assertEquals('test', $result->getLabel());

		$result2 = dlm_activate_license('AAAA-BBBB-CCCC-DDDD', []);
		$this->assertNull($result2->getLabel());

		$result = dlm_activate_license('AAAA-BBBB-CCCC-DDDD', []);
		$this->assertInstanceOf(\WP_Error::class, $result);

		// Deactivate licenses
		$result = dlm_deactivate_license('AAAA-BBBB-CCCC-DDDD', $result2->getToken());
		$this->assertInstanceOf(\IdeoLogix\DigitalLicenseManager\Database\Models\License::class, $result);

		$result = dlm_deactivate_license('AAAA-BBBB-CCCC-DDDD');
		$this->assertInstanceOf(\IdeoLogix\DigitalLicenseManager\Database\Models\License::class, $result);

		$result = dlm_deactivate_license('AAAA-BBBB-CCCC-DDDD');
		$this->assertInstanceOf(\WP_Error::class, $result);

		// Get licenses
		$result = dlm_get_licenses([]);
		$this->assertCount(2, $result);

		$result = dlm_get_license('AAAA-BBBB-CCCC-DDDD');
		$this->assertEquals('AAAA-BBBB-CCCC-DDDD', $result->getDecryptedLicenseKey());

		// License meta
		$result = dlm_add_license_meta('AAAA-BBBB-CCCC-DDDD', '');

		// Delete license
		$result = dlm_delete_license( 'AAAA-BBBB-CCCC-DDDD' );
		$this->assertTrue( $result );

		$result = dlm_get_licenses([]);
		$this->assertCount(1, $result);

		// Delete activation
		$result = dlm_delete_activation($result2->getToken());
		$this->assertTrue( $result );




	}
}