<?php

class DLM_Generators_TestCase extends WP_UnitTestCase {

	private $generators = [ 'Unlimited Generator', 'Number Generator', 'Big Generator' ];

	private function getData() {

		return [
			[
				'name'         => 'Unlimited Generator',
				'charset'      => 'abcdefghjkl123456789',
				'chunks'       => 4,
				'chunk_length' => 4,
				'separator'    => '-',
				'expires_in'   => 365
			],
			[
				'name'         => 'Number Generator',
				'charset'      => '123456789',
				'chunks'       => 1,
				'chunk_length' => 15,
				'separator'    => '',
				'expires_in'   => 60,
			],
			[
				'name'         => 'Big Generator',
				'charset'      => 'qwertyuioipasdfghjklzxcvbnm1234567890',
				'chunks'       => 6,
				'chunk_length' => 4,
				'separator'    => '-',
				'prefix'       => 'company_',
				'expires_in'   => 120,
				'created_at'   => date( 'Y-m-d H:i:s', time() - mt_rand( 2630000, 2630000 * 3 ) ),
			],
		];
	}

	private function importData( $data ) {
		$succeeded = [];
		foreach ( $data as $object ) {
			$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->insert( $object );
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

		$row    = $this->getGenerator( $this->generators[0] );
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->find( isset( $row['id'] ) ? $row['id'] : 0 );
		$this->assertIsObject( $object );

	}

	public function testLicenseModel() {

		$this->importAllData();
		$row    = $this->getGenerator( $this->generators[1] );
		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->find( isset( $row['id'] ) ? $row['id'] : 0 );

		$properties = [
			'getId',
			'getName',
			'getCharset',
			'getChunks',
			'getChunkLength',
			'getActivationsLimit',
			'getSeparator',
			'getPrefix',
			'getSuffix',
			'getExpiresIn',
			'getCreatedAt',
			'getUpdatedAt',
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

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->findBy( [
			'name' => $this->generators[2]
		] );
		$this->assertIsObject( $object );
	}

	public function testFindAll() {

		$this->importAllData();

		$objects = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->findAll();
		$this->assertCount( count( $this->getData() ), is_countable( $objects ) ? $objects : [] );
	}

	public function testFindAllBy() {

		$this->importAllData();

		$objects = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->findAllBy( [
			'chunk_length' => 4,
		] );
		$this->assertCount( 2, is_countable( $objects ) ? $objects : [] );
	}

	public function testUpdate() {

		$this->importAllData();

		$model = $this->getGenerator( $this->generators[1] );

		/* @var $model \IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator */
		$model = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->update( $model['id'], [
			'chunk_length' => 15
		] );

		$this->assertIsObject( $model );
		$this->assertEquals( 15, ! empty( $model->getChunkLength() ) ? $model->getChunkLength() : null );
	}

	public function testUpdateBy() {

		$this->importAllData();

		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->updateBy( [ 'name' => $this->generators[0] ], [
			'chunk_length' => 3
		] );

		$this->assertIsNotBool( $result );
		$this->assertIsInt( $result );
	}

	public function testDelete() {

		$this->importAllData();

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->findBy( [
			'name' => $this->generators[2]
		] );

		$foundId = $object->getId();

		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->delete( [ $foundId ] );
		$this->assertIsNotBool( $result );
		$this->assertEquals( 1, $result );

		$object = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->find( $foundId );
		$this->assertFalse( $object );

	}

	public function testDeleteBy() {

		$this->importAllData();

		global $wpdb;
		$result = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->deleteBy( [ 'name' => $this->generators[1] ] );
		$this->assertIsNotBool( $result );
		$this->assertEquals( 1, $result );
		$this->assertEquals( $wpdb->prefix . 'dlm_generators', \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->getTable() );

	}

	public function testGeneration() {

		$this->importAllData();

		$model = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->findBy( [ 'name' => 'Unlimited Generator' ] );
		$this->assertIsNotBool( $model );
		$generator = new \IdeoLogix\DigitalLicenseManager\Core\Generators\StandardGenerator( $model );
		$licenses  = $generator->generate( 10 );
		$this->assertCount( 10, $licenses );
		$chunks = explode( $model->getSeparator(), $licenses[0] );
		$this->assertCount( $model->getChunks(), $chunks );
		$this->assertEquals( $model->getChunkLength(), strlen( $chunks[0] ) );

		$model = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->findBy( [ 'name' => 'Number Generator' ] );
		$this->assertIsNotBool( $model );
		$generator = new \IdeoLogix\DigitalLicenseManager\Core\Generators\StandardGenerator( $model );
		$licenses  = $generator->generate( 5 );
		$this->assertCount( 5, $licenses );
		$this->assertIsNumeric($licenses[0]);

		$model = \IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator::instance()->findBy( [ 'name' => 'Big Generator' ] );
		$this->assertIsNotBool( $model );
		$generator = new \IdeoLogix\DigitalLicenseManager\Core\Generators\StandardGenerator( $model );
		$licenses  = $generator->generate( 10 );
		$this->assertCount( 10, $licenses );
		$this->assertStringStartsWith($model->getPrefix(), $licenses[0]);

	}

	private function getGenerator( $name ) {

		global $wpdb;
		$table = $wpdb->prefix . 'dlm_generators';

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE name=%s", $name ), ARRAY_A );
	}
}
