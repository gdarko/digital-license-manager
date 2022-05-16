<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceRepository;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ResourceRepositoryInterface;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\ApiKey as ApiKeyResourceModel;
use IdeoLogix\DigitalLicenseManager\Enums\ColumnType as ColumnTypeEnum;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

defined( 'ABSPATH' ) || exit;

class ApiKey extends AbstractResourceRepository implements ResourceRepositoryInterface {
	/**
	 * @var string
	 */
	const TABLE = DatabaseTable::API_KEYS;

	/**
	 * Country constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->table      = $wpdb->prefix . self::TABLE;
		$this->primaryKey = 'id';
		$this->model      = ApiKeyResourceModel::class;
		$this->mapping    = array(
			'user_id'         => ColumnTypeEnum::BIGINT,
			'description'     => ColumnTypeEnum::VARCHAR,
			'permissions'     => ColumnTypeEnum::VARCHAR,
			'endpoints'       => ColumnTypeEnum::SERIALIZED,
			'consumer_key'    => ColumnTypeEnum::CHAR,
			'consumer_secret' => ColumnTypeEnum::CHAR,
			'nonces'          => ColumnTypeEnum::LONGTEXT,
			'truncated_key'   => ColumnTypeEnum::CHAR,
			'last_access'     => ColumnTypeEnum::DATETIME
		);
	}
}
