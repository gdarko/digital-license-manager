<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceRepository;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ResourceRepositoryInterface;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;
use IdeoLogix\DigitalLicenseManager\Enums\ColumnType as ColumnTypeEnum;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

defined( 'ABSPATH' ) || exit;

class Generator extends AbstractResourceRepository implements ResourceRepositoryInterface {
	/**
	 * @var string
	 */
	const TABLE = DatabaseTable::GENERATORS;

	/**
	 * Country constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->table      = $wpdb->prefix . self::TABLE;
		$this->primaryKey = 'id';
		$this->model      = GeneratorResourceModel::class;
		$this->mapping    = array(
			'name'              => ColumnTypeEnum::VARCHAR,
			'charset'           => ColumnTypeEnum::VARCHAR,
			'chunks'            => ColumnTypeEnum::INT,
			'chunk_length'      => ColumnTypeEnum::INT,
			'activations_limit' => ColumnTypeEnum::INT,
			'separator'         => ColumnTypeEnum::VARCHAR,
			'prefix'            => ColumnTypeEnum::VARCHAR,
			'suffix'            => ColumnTypeEnum::VARCHAR,
			'expires_in'        => ColumnTypeEnum::INT,
		);
	}
}
