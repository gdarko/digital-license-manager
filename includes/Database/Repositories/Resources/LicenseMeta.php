<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceRepository;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ResourceRepositoryInterface;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\LicenseMeta as LicenseMetaResourceModel;
use IdeoLogix\DigitalLicenseManager\Enums\ColumnType;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

defined( 'ABSPATH' ) || exit;

class LicenseMeta extends AbstractResourceRepository implements ResourceRepositoryInterface {
	/**
	 * @var string
	 */
	const TABLE = DatabaseTable::LICENSE_META;

	/**
	 * Country constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->table      = $wpdb->prefix . self::TABLE;
		$this->primaryKey = 'meta_id';
		$this->model      = LicenseMetaResourceModel::class;
		$this->mapping    = array(
			'license_id' => ColumnType::BIGINT,
			'meta_key'   => ColumnType::VARCHAR,
			'meta_value' => ColumnType::LONGTEXT,
		);
	}
}
