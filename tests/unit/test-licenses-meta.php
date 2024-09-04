<?php

class DLM_LicenseMeta_TestCase extends WP_UnitTestCase {

	private $meta_keys = [
		'key1',
		'key2',
		'key3',
		'key4',
	];
	private $meta_values = [
		'snake',
		'cat',
		'wolf',
		'cat'
	];

	private function getData() {

		return [
			[
				'valid_for'   => 365,
				'license_key' => 'XXXX-XXXX-XXXX-1111',
				'status'      => \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::DELIVERED,
				'source'      => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::IMPORT,
				'created_at'  => gmdate( 'Y-m-d H:i:s', time() - wp_rand( 2630000, 2630000 * 3 ) ),
				'meta' => [
					[
						'license_id'     => '',
						'meta_key'     => $this->meta_keys[0],
						'meta_value'  => $this->meta_values[0],
					],
					[
						'license_id'     => '',
						'meta_key'     => $this->meta_keys[1],
						'meta_value'  => $this->meta_values[1],
					],
				]
			],
			[
				'valid_for'   => 120,
				'license_key' => 'XXXX-XXXX-XXXX-XXX1',
				'status'      => \IdeoLogix\DigitalLicenseManager\Enums\LicenseStatus::DELIVERED,
				'source'      => \IdeoLogix\DigitalLicenseManager\Enums\LicenseSource::API,
				'created_at'  => gmdate( 'Y-m-d H:i:s', time() - wp_rand( 2630000, 2630000 * 3 ) ),
				'meta' => [
					[
						'license_id'     => '',
						'meta_key'     => $this->meta_keys[2],
						'meta_value'  => $this->meta_values[2],
					],
					[
						'license_id'     => '',
						'meta_key'     => $this->meta_keys[3],
						'meta_value'  => $this->meta_values[3],
					],
				]
			],
		];
	}

	private function getDataCount() {
		$total = 0;
		foreach($this->getData() as $item) {
			$total += count($item['meta']);
		}
		return $total;
	}

	private function importData( $data ) {
		$succeeded = [];
		foreach ( $data as $object ) {
			$license_key = $object['license_key'];
			$activations = $object['meta'];
			unset( $object['meta'] );
			$object['license_key'] = \IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper::encrypt( $license_key );
			$object['hash']        = \IdeoLogix\DigitalLicenseManager\Utils\StringHasher::license( $license_key );
			$result                = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License::instance()->insert( $object );
			if ( $result && method_exists( $result, 'getId' ) ) {
				foreach ( $activations as $activation ) {
					$activation['license_id'] = $result->getId();
					$result2                  = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->insert( $activation );
					if ( $result2 && method_exists( $result2, 'getMetaId' ) ) {
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

		$row    = $this->getMeta( $this->meta_keys[0] );
		/* @var $object \IdeoLogix\DigitalLicenseManager\Database\Models\Resources\LicenseMeta */
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->find( isset( $row['meta_id'] ) ? $row['meta_id'] : 0 );
		$this->assertIsObject( $object );
		$this->assertEquals($this->meta_values[0], $object->getMetaValue());

	}

	public function testLicenseModel() {

		$this->importAllData();
		$row    = $this->getMeta( $this->meta_keys[0] );

		/* @var $object \IdeoLogix\DigitalLicenseManager\Database\Models\Resources\LicenseMeta */
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->find( isset( $row['meta_id'] ) ? $row['meta_id'] : 0 );

		$properties = [
			'getMetaId',
			'getMetaValue',
			'getMetaKey',
			'getLicenseId',
		];

		$allExist = true;
		foreach ( $properties as $property ) {
			if ( ! method_exists( $object, $property ) ) {
				$allExist = false;
				break;
			}
		}
		$this->assertTrue( $allExist );
		$this->assertEquals( $this->meta_values[0], $object->getMetaValue() );

	}

	public function testFindBy() {

		$this->importAllData();

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->findBy( [
			'meta_key' => $this->meta_keys[1]
		] );
		$this->assertIsObject( $object );
	}

	public function testFindAll() {

		$this->importAllData();

		$objects = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->findAll();
		$this->assertCount($this->getDataCount(), is_countable( $objects ) ? $objects : [] );
	}

	public function testFindAllBy() {

		$this->importAllData();

		$objects = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->findAllBy( [
			'meta_value' => 'cat',
		] );

		$this->assertCount( 2, is_countable( $objects ) ? $objects : [] );
	}

	public function testUpdate() {

		$this->importAllData();

		$model = $this->getMeta( $this->meta_keys[3] );

		/* @var $model \IdeoLogix\DigitalLicenseManager\Database\Models\Resources\LicenseMeta */
		$model = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->update( $model['meta_id'], [
			'meta_value' => 'dinosaur'
		] );

		$this->assertIsObject( $model );
		$this->assertEquals( 'dinosaur', ! empty( $model->getMetaValue() ) ? $model->getMetaValue() : null );
	}

	public function testUpdateBy() {

		$this->importAllData();

		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->updateBy( [ 'meta_key' => $this->meta_keys[1] ], [
			'meta_value' => 'tiger'
		] );

		$this->assertIsNotBool( $result );
		$this->assertIsInt( $result );
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->findBy( [
			'meta_key' => $this->meta_keys[1]
		] );

		$this->assertIsObject($object);
		$this->assertEquals('tiger', $object->getMetaValue());


	}

	public function testDelete() {

		$this->importAllData();

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->findBy( [
			'meta_value' => 'cat'
		] );

		$foundId = $object->getMetaId();

		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->delete( [ $foundId ] );
		$this->assertIsNotBool( $result );
		$this->assertEquals( 1, $result );

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->find( $foundId );
		$this->assertFalse( $object );

	}

	public function testDeleteBy() {

		$this->importAllData();

		global $wpdb;
		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->deleteBy( [ 'meta_key' => $this->meta_keys[3] ] );
		$this->assertIsNotBool( $result );
		$this->assertEquals( 1, $result );
		$this->assertEquals( $wpdb->prefix . 'dlm_license_meta', \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\LicenseMeta::instance()->getTable() );

	}

	private function getMeta( $key ) {

		global $wpdb;
		$table = $wpdb->prefix . 'dlm_license_meta';

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE meta_key=%s", $key ), ARRAY_A );
	}

}