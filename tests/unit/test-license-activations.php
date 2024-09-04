<?php

class DLM_LicenseActivations_TestCase extends WP_UnitTestCase {

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
				'license_key' => 'XXXX-XXXX-XXXX-1111',
				'status'      => \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::DELIVERED,
				'source'      => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::IMPORT,
				'created_at'  => date( 'Y-m-d H:i:s', time() - wp_rand( 2630000, 2630000 * 3 ) ),
				'activations' => [
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
			],
			[
				'valid_for'   => 120,
				'license_key' => 'XXXX-XXXX-XXXX-XXX1',
				'status'      => \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::DELIVERED,
				'source'      => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::API,
				'created_at'  => date( 'Y-m-d H:i:s', time() - wp_rand( 2630000, 2630000 * 3 ) ),
				'activations' => [
					[
						'token'          => $this->tokens[2],
						'license_id'     => '',
						'label'          => 'Random Activation',
						'source'         => \IdeoLogix\DigitalLicenseManager\Enums\ActivationSource::API,
						'ip_address'     => '10.10.10.1',
						'user_agent'     => 'Chrome user agent',
						'created_at'     => date( 'Y-m-d H:i:s', time() - 120 ),
						'deactivated_at' => date( 'Y-m-d H:i:s', time() - 45 )
					],
					[
						'token'      => $this->tokens[3],
						'license_id' => '',
						'label'      => 'Different Activation',
						'source'     => \IdeoLogix\DigitalLicenseManager\Enums\ActivationSource::API,
						'ip_address' => '10.10.10.2',
						'user_agent' => 'Mozila user agent',
						'created_at'     => date( 'Y-m-d H:i:s', time() - 120 ),
						'deactivated_at' => date( 'Y-m-d H:i:s', time() - 30 ),
					],
				]
			],
		];
	}

	private function getDataCount() {
		$total = 0;
		foreach($this->getData() as $item) {
			$total += count($item['activations']);
		}
		return $total;
	}

	private function importData( $data ) {
		$succeeded = [];
		foreach ( $data as $object ) {
			$license_key = $object['license_key'];
			$activations = $object['activations'];
			unset( $object['activations'] );
			$object['license_key'] = \IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper::encrypt( $license_key );
			$object['hash']        = \IdeoLogix\DigitalLicenseManager\Utils\StringHasher::license( $license_key );
			$result                = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->insert( $object );
			if ( $result && method_exists( $result, 'getId' ) ) {
				foreach ( $activations as $activation ) {
					$activation['license_id'] = $result->getId();
					$result2                  = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->insert( $activation );
					if ( $result2 && method_exists( $result2, 'getId' ) ) {
						$succeeded[] = $result2;
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

		$this->assertCount( $this->getDataCount(), $imported );
	}

	public function testFind() {

		$this->importAllData();

		$row    = $this->getActivation( $this->tokens[3] );
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->find( isset( $row['id'] ) ? $row['id'] : 0 );
		$this->assertIsObject( $object );
		$this->assertEquals($this->tokens[3], $object->getToken());

	}

	public function testLicenseModel() {

		$this->importAllData();
		$row    = $this->getActivation( $this->tokens[0] );
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->find( isset( $row['id'] ) ? $row['id'] : 0 );

		$properties = [
			'getId',
			'getLicenseId',
			'getLabel',
			'getSource',
			'getToken',
			'getIpAddress',
			'getUserAgent',
			'getMetaData',
			'getCreatedAt',
			'getUpdatedAt',
			'getDeactivatedAt',
			'getLicense',
		];

		$allExist = true;
		foreach ( $properties as $property ) {
			if ( ! method_exists( $object, $property ) ) {
				$allExist = false;
				break;
			}
		}
		$this->assertTrue( $allExist );
		$this->assertEquals( $this->tokens[0], $object->getToken() );

	}

	public function testFindBy() {

		$this->importAllData();

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->findBy( [
			'token' => $this->tokens[1]
		] );
		$this->assertIsObject( $object );
	}

	public function testFindAll() {

		$this->importAllData();

		$objects = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->findAll();
		$this->assertCount($this->getDataCount(), is_countable( $objects ) ? $objects : [] );
	}

	public function testFindAllBy() {

		$this->importAllData();

		$objects = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->findAllBy( [
			'source' => \IdeoLogix\DigitalLicenseManager\Enums\ActivationSource::API,
		] );
		$this->assertCount( 2, is_countable( $objects ) ? $objects : [] );
	}

	public function testUpdate() {

		$this->importAllData();

		$model = $this->getActivation( $this->tokens[2] );

		/* @var $model \IdeoLogix\DigitalLicenseManager\Database\Models\Resources\LicenseActivation */
		$model = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->update( $model['id'], [
			'source' => \IdeoLogix\DigitalLicenseManager\Enums\ActivationSource::WEB
		] );

		$this->assertIsObject( $model );
		$this->assertEquals( ! empty( $model->getSource() ) ? $model->getSource() : null, \IdeoLogix\DigitalLicenseManager\Enums\ActivationSource::WEB );
	}

	public function testUpdateBy() {

		$this->importAllData();

		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->updateBy( [ 'token' => $this->tokens[1] ], [
			'label' => 'New Label Here'
		] );

		$this->assertIsNotBool( $result );
		$this->assertIsInt( $result );
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->findBy( [
			'token' => $this->tokens[1]
		] );

		$this->assertIsObject($object);
		$this->assertEquals('New Label Here', $object->getLabel());


	}

	public function testDelete() {

		$this->importAllData();

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->findBy( [
			'label' => 'Random Activation'
		] );

		$foundId = $object->getId();

		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->delete( [ $foundId ] );
		$this->assertIsNotBool( $result );
		$this->assertEquals( 1, $result );

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->find( $foundId );
		$this->assertFalse( $object );

	}

	public function testDeleteBy() {

		$this->importAllData();

		global $wpdb;
		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->deleteBy( [ 'token' => $this->tokens[3] ] );
		$this->assertIsNotBool( $result );
		$this->assertEquals( 1, $result );
		$this->assertEquals( $wpdb->prefix . 'dlm_license_activations', \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseActivation::instance()->getTable() );

	}

	private function getActivation( $token ) {

		global $wpdb;
		$table = $wpdb->prefix . 'dlm_license_activations';

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE token=%s", $token ), ARRAY_A );
	}

}