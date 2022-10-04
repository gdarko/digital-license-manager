<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceRepository;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ResourceRepositoryInterface;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Enums\ColumnType as ColumnTypeEnum;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

defined( 'ABSPATH' ) || exit;

class License extends AbstractResourceRepository implements ResourceRepositoryInterface {
	/**
	 * @var string
	 */
	const TABLE = DatabaseTable::LICENSES;

	/**
	 * Country constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->table      = $wpdb->prefix . self::TABLE;
		$this->primaryKey = 'id';
		$this->model      = LicenseResourceModel::class;
		$this->mapping    = array(
			'order_id'          => ColumnTypeEnum::BIGINT,
			'product_id'        => ColumnTypeEnum::BIGINT,
			'user_id'           => ColumnTypeEnum::BIGINT,
			'license_key'       => ColumnTypeEnum::LONGTEXT,
			'hash'              => ColumnTypeEnum::LONGTEXT,
			'expires_at'        => ColumnTypeEnum::DATETIME,
			'source'            => ColumnTypeEnum::TINYINT,
			'status'            => ColumnTypeEnum::TINYINT,
			'valid_for'         => ColumnTypeEnum::INT,
			'times_activated'   => ColumnTypeEnum::INT,
			'activations_limit' => ColumnTypeEnum::INT,
		);
	}
}
