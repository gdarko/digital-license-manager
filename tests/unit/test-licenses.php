<?php

class DLM_Licenses_TestCase extends WP_UnitTestCase {

	private $tokens = [
		'77963b7a931377ad4ab5ad6a9cd718aa',
		'5fa5ef503967d1c4774a82c8b339838c',
		'xfa5ef503967d1c4774a82c8b3398321',
		'9fa5ef503967d1c4774a82c8b3398113',
	];

	private function getData() {

		return [
			[
				'valid_for'   => 365,
				'license_key' => 'XXXX-XXXX-XXXX-XXX1',
				'status'      => \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::ACTIVE,
				'source'      => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::API,
				'created_at'  => date( 'Y-m-d H:i:s', time() - wp_rand( 2630000, 2630000 * 3 ) ),
			],
			[
				'valid_for'         => 120,
				'license_key'       => 'XXXX-XXXX-XXXX-XX11',
				'expires_at'        => date( 'Y-m-d H:i:s', time() + wp_rand( 2630000, 2630000 * 2 ) ),
				'status'            => \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::ACTIVE,
				'source'            => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::API,
				'activations_limit' => wp_rand( 1, 3 ),
				'created_at'        => date( 'Y-m-d H:i:s', time() - wp_rand( 2630000, 2630000 * 3 ) ),
			],
			[
				'valid_for'         => 90,
				'license_key'       => 'XXXX-XXXX-XXXX-X111',
				'status'            => \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::ACTIVE,
				'source'            => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::API,
				'activations_limit' => 5,
				'created_at'        => date( 'Y-m-d H:i:s', time() - wp_rand( 2630000, 2630000 * 3 ) ),
				'expires_at'        => date( 'Y-m-d H:i:s', time() - 16000 ),
			],
			[
				'license_key'       => 'XXXX-XXXX-XXXX-1111',
				'expires_at'        => date( 'Y-m-d H:i:s', time() + wp_rand( 2630000, 2630000 * 2 ) ),
				'status'            => \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::DELIVERED,
				'source'            => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::IMPORT,
				'activations_limit' => 1,
				'created_at'        => date( 'Y-m-d H:i:s', time() - wp_rand( 2630000, 2630000 * 3 ) ),
				'activations'       => [
					[
						'token'          => $this->tokens[0],
						'license_id'     => '',
						'label'          => 'Random Activation',
						'source'         => \IdeoLogix\DigitalLicenseManager\Enums\ActivationSource::WEB,
						'ip_address'     => '10.10.10.1',
						'user_agent'     => 'Chrome user agent',
						'created_at'     => date( 'Y-m-d H:i:s', time() - 150 ),
						'deactivated_at' => date( 'Y-m-d H:i:s', time() )
					],
					[
						'token'      => $this->tokens[1],
						'license_id' => '',
						'label'      => 'Different Activation',
						'source'     => \IdeoLogix\DigitalLicenseManager\Enums\ActivationSource::WEB,
						'ip_address' => '10.10.10.2',
						'user_agent' => 'Mozila user agent',
						'created_at' => date( 'Y-m-d H:i:s', time() - 110 ),
					],
				]
			]
		];
	}

	private function importData( $data ) {
		$succeeded = [];
		foreach ( $data as $object ) {
			$license_key = $object['license_key'];
			$activations = [];
			if ( isset( $object['activations'] ) ) {
				$activations = $object['activations'];
				unset( $object['activations'] );
			}
			$object['license_key'] = \IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper::encrypt( $license_key );
			$object['hash']        = \IdeoLogix\DigitalLicenseManager\Utils\StringHasher::license( $license_key );
			$result                = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->insert( $object );
			if ( $result && method_exists( $result, 'getId' ) ) {
				$succeeded[] = $result;
				if ( ! empty( $activations ) ) {
					foreach ( $activations as $activation ) {
						$activation['license_id'] = $result->getId();
						\IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->insert( $activation );
					}
				}

			}
		}

		return $succeeded;
	}

	private function importAllData() {
		$data = $this->getData();
		$this->importData( $data );
	}

	public function testInsert() {

		$data     = $this->getData();
		$imported = $this->importData( $data );

		$this->assertCount( count( $data ), $imported );
	}

	public function testFind() {

		$this->importAllData();

		$row    = $this->getLicense( 'XXXX-XXXX-XXXX-X111' );
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->find( isset( $row['id'] ) ? $row['id'] : 0 );
		$this->assertIsObject( $object );

	}

	public function testLicenseModel() {

		$this->importAllData();
		$row    = $this->getLicense( 'XXXX-XXXX-XXXX-X111' );
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->find( isset( $row['id'] ) ? $row['id'] : 0 );

		$properties = [
			'getId',
			'getOrderId',
			'getProductId',
			'getUserId',
			'getLicenseKey',
			'getDecryptedLicenseKey',
			'getHash',
			'getExpiresAt',
			'getSource',
			'getStatus',
			'getTimesActivated',
			'getActivationsLimit',
			'getCreatedAt',
			'getCreatedBy',
			'getUpdatedAt',
			'getUpdatedBy',
			'getValidFor',
			'isExpired',
			'getActivations',
			'getActivationsCount'
		];

		$allExist = true;
		foreach ( $properties as $property ) {
			if ( ! method_exists( $object, $property ) ) {
				$allExist = false;
				break;
			}
		}
		$this->assertTrue( $allExist );
		$this->assertEquals( 'XXXX-XXXX-XXXX-X111', $object->getDecryptedLicenseKey() );
		$this->assertEquals( \IdeoLogix\DigitalLicenseManager\Utils\StringHasher::license( 'XXXX-XXXX-XXXX-X111' ), $object->getHash() );
		$this->assertEquals( \IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper::hash( 'XXXX-XXXX-XXXX-X111' ), $object->getHash() );

	}

	public function testFindBy() {

		$this->importAllData();

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->findBy( [
			'hash' => \IdeoLogix\DigitalLicenseManager\Utils\StringHasher::license( 'XXXX-XXXX-XXXX-1111' )
		] );
		$this->assertIsObject( $object );
	}

	public function testFindAll() {

		$this->importAllData();

		$objects = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->findAll();
		$this->assertCount( count( $this->getData() ), is_countable( $objects ) ? $objects : [] );
	}

	public function testFindAllBy() {

		$this->importAllData();

		$objects = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->findAllBy( [
			'source' => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::API,
		] );
		$this->assertCount( 3, is_countable( $objects ) ? $objects : [] );
	}

	public function testUpdate() {

		$this->importAllData();

		$model = $this->getLicense( 'XXXX-XXXX-XXXX-XXX1' );

		/* @var $model \IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License */
		$model = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->update( $model['id'], [
			'source' => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::MIGRATION
		] );

		$this->assertIsObject( $model );
		$this->assertEquals( ! empty( $model->getSource() ) ? $model->getSource() : null, \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::MIGRATION );
	}

	public function testUpdateBy() {

		$this->importAllData();

		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->updateBy( [ 'source' => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::MIGRATION ], [
			'source' => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::IMPORT
		] );

		$this->assertIsNotBool( $result );
		$this->assertIsInt( $result );
	}

	public function testDelete() {

		$this->importAllData();

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->findBy( [
			'hash' => \IdeoLogix\DigitalLicenseManager\Utils\StringHasher::license( 'XXXX-XXXX-XXXX-1111' )
		] );

		$foundId = $object->getId();

		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->delete( [ $foundId ] );
		$this->assertIsNotBool( $result );
		$this->assertEquals( 1, $result );

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->find( $foundId );
		$this->assertFalse( $object );

	}

	public function testDeleteBy() {

		$this->importAllData();

		global $wpdb;
		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->deleteBy( [ 'status' => \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::DELIVERED ] );
		$this->assertIsNotBool( $result );
		$this->assertEquals( 1, $result );
		$this->assertEquals( $wpdb->prefix . 'dlm_licenses', \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->getTable() );

	}

	public function testRelations() {
		$this->importAllData();

		/* @var $license \IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License */
		$license = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->findBy( [ 'hash' => \IdeoLogix\DigitalLicenseManager\Utils\StringHasher::license( 'XXXX-XXXX-XXXX-1111' ) ] );
		$this->assertIsObject( $license );
		$this->assertEquals( 1, $license->getTimesActivated() );
		$activations = $license->getActivations();
		$this->assertCount( 2, $activations );
		$activations = $license->getActivations( [ 'active' => 1 ] );
		$this->assertCount( 1, $activations );
	}

	public function testExpiration() {
		$this->importAllData();

		/* @var $license \IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License */
		$license = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->findBy( [ 'hash' => \IdeoLogix\DigitalLicenseManager\Utils\StringHasher::license( 'XXXX-XXXX-XXXX-X111' ) ] );
		$this->assertIsObject( $license );
		$this->assertTrue( $license->isExpired() );
	}

	private function getLicense( $licenseKey ) {

		global $wpdb;
		$table = $wpdb->prefix . 'dlm_licenses';

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE hash=%s", \IdeoLogix\DigitalLicenseManager\Utils\StringHasher::license( $licenseKey ) ), ARRAY_A );
	}

}