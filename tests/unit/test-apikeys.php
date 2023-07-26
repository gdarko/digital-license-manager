<?php

class DLM_ApiKeys_TestCase extends WP_UnitTestCase {

	private function getData() {

		return [
			[
				'user_id'         => '',
				'description'     => 'Example 1',
				'permissions'     => 'read_write',
				'endpoints'       => array(
					'010' => '1',
					'011' => '1',
					'012' => '1',
					'013' => '1',
					'014' => '1',
					'015' => '1',
					'016' => '1',
					'017' => '1',
					'022' => '1',
					'023' => '1',
					'024' => '1',
					'025' => '1',
					'026' => '1',
					'027' => '1',
				),
				'consumer_key'    => 'dbb7cba60c5ba31c2c75e61b6d9c811c98853c1358c803e3bd3c701966a1cf93',
				'consumer_secret' => 'cs_f124f5f9afcf807058adf0561c5a24adf19d1475',
				'truncated_key'   => 'a51113b',
				'last_access'     => '2023-07-25 09:45:10',
				'created_at'      => '2023-07-25 07:41:18',
			],
		];
	}

	private function importData( $data ) {
		$succeeded = [];
		foreach ( $data as $object ) {
			$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->insert( $object );
			if ( $result && method_exists( $result, 'getId' ) ) {
				$succeeded[] = $result;
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

		$row    = $this->getApiKey( 'Example 1' );
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->find( isset( $row['id'] ) ? $row['id'] : 0 );
		$this->assertIsObject( $object );
		$this->assertIsArray( $object->getEndpoints() );

	}

	public function testApiKeysModel() {

		$this->importAllData();
		$row    = $this->getApiKey( 'Example 1' );
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->find( isset( $row['id'] ) ? $row['id'] : 0 );

		$properties = [
			'getId',
			'getUserId',
			'getDescription',
			'getPermissions',
			'getEndpoints',
			'getConsumerKey',
			'getConsumerSecret',
			'getNonces',
			'getTruncatedKey',
			'getLastAccess',
		];

		$allExist = true;
		foreach ( $properties as $property ) {
			if ( ! method_exists( $object, $property ) ) {
				$allExist = false;
				break;
			}
		}
		$this->assertTrue( $allExist );

	}

	public function testFindBy() {

		$this->importAllData();

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->findBy( [
			'description' => 'Example 1',
		] );
		$this->assertIsObject( $object );
	}

	public function testFindAll() {

		$this->importAllData();

		$objects = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->findAll();
		$this->assertCount( count( $this->getData() ), is_countable( $objects ) ? $objects : [] );
	}

	public function testFindAllBy() {

		$this->importAllData();

		$objects = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->findAllBy( [
			'permissions' => 'read_write',
		] );
		$this->assertCount( 1, is_countable( $objects ) ? $objects : [] );
	}

	public function testUpdate() {

		$this->importAllData();

		$model = $this->getApiKey( 'Example 1' );

		/* @var $model \IdeoLogix\DigitalLicenseManager\Database\Models\Resources\ApiKey */
		$model = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->update( $model['id'], [
			'permissions' => 'read',
		] );

		$this->assertIsObject( $model );
		$this->assertEquals( 'read', ! empty( $model->getPermissions() ) ? $model->getPermissions() : null );
	}

	public function testUpdateBy() {

		$this->importAllData();

		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->updateBy( [ 'permissions' => 'read_write' ], [
			'permissions' => 'read'
		] );

		$this->assertIsNotBool( $result );
		$this->assertIsInt( $result );
	}

	public function testDelete() {

		$this->importAllData();

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->findBy( [
			'description' => 'Example 1',
		] );

		$foundId = $object->getId();

		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->delete( [ $foundId ] );
		$this->assertIsNotBool( $result );
		$this->assertEquals( 1, $result );

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->find( $foundId );
		$this->assertFalse( $object );

	}

	public function testDeleteBy() {

		$this->importAllData();

		global $wpdb;
		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->deleteBy( [ 'description' => 'Example 1' ] );
		$this->assertIsNotBool( $result );
		$this->assertEquals( 1, $result );
		$this->assertEquals( $wpdb->prefix . 'dlm_api_keys', \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\ApiKey::instance()->getTable() );

	}

	private function getApiKey( $key ) {

		global $wpdb;
		$table = $wpdb->prefix . 'dlm_api_keys';

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE description=%s", $key ), ARRAY_A );
	}

}