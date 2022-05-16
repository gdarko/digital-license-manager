<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceRepository;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ResourceRepositoryInterface;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\LicenseActivation as LicenseActivationResourceModel;
use IdeoLogix\DigitalLicenseManager\Enums\ColumnType;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

defined( 'ABSPATH' ) || exit;

/**
 * Class LicenseActivation
 * @package IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources
 */
class LicenseActivation extends AbstractResourceRepository implements ResourceRepositoryInterface {
	/**
	 * @var string
	 */
	const TABLE = DatabaseTable::LICENSE_ACTIVATIONS;

	/**
	 * Country constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->table        = $wpdb->prefix . self::TABLE;
		$this->primaryKey   = 'id';
		$this->model        = LicenseActivationResourceModel::class;
		$this->mapping      = array(
			'token'          => ColumnType::LONGTEXT,
			'license_id'     => ColumnType::BIGINT,
			'label'          => ColumnType::VARCHAR,
			'source'         => ColumnType::TINYINT,
			'ip_address'     => ColumnType::VARCHAR,
			'user_agent'     => ColumnType::TEXT,
			'meta_data'      => ColumnType::SERIALIZED,
			'created_at'     => ColumnType::DATETIME,
			'updated_at'     => ColumnType::DATETIME,
			'deactivated_at' => ColumnType::DATETIME,
		);
		$this->useCreatedBy = false;
		$this->useUpdatedBy = false;
	}
}
