<?php

namespace IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractResourceRepository;
use IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces\ResourceRepositoryInterface;
use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\ProductDownload as ProductDownloadResourceModel;
use IdeoLogix\DigitalLicenseManager\Enums\ColumnType;
use IdeoLogix\DigitalLicenseManager\Enums\DatabaseTable;

defined( 'ABSPATH' ) || exit;

class ProductDownload extends AbstractResourceRepository implements ResourceRepositoryInterface {

	/**
	 * @var string
	 */
	const TABLE = DatabaseTable::PRODUCT_DOWNLOADS;

	/**
	 * Country constructor.
	 */
	public function __construct() {

		global $wpdb;

		$this->table        = $wpdb->prefix . self::TABLE;
		$this->primaryKey   = 'id';
		$this->model        = ProductDownloadResourceModel::class;
		$this->mapping      = array(
			'license_id'    => ColumnType::BIGINT,
			'activation_id' => ColumnType::BIGINT,
			'source'        => ColumnType::TINYINT,
			'ip_address'    => ColumnType::VARCHAR,
			'user_agent'    => ColumnType::TEXT,
			'meta_data'     => ColumnType::SERIALIZED,
			'created_at'    => ColumnType::DATETIME,
			'updated_at'    => ColumnType::DATETIME,
		);
		$this->useCreatedBy = false;
		$this->useUpdatedBy = false;
	}
}
