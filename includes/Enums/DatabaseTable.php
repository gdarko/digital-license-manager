<?php


namespace IdeoLogix\DigitalLicenseManager\Enums;

/**
 * Class DatabaseTable
 * @package IdeoLogix\DigitalLicenseManager\Enums
 */
abstract class DatabaseTable {

	/**
	 * @var string
	 */
	const LICENSES = 'dlm_licenses';

	/**
	 * @var string
	 */
	const GENERATORS = 'dlm_generators';

	/**
	 * @var string
	 */
	const API_KEYS = 'dlm_api_keys';

	/**
	 * @var string
	 */
	const LICENSE_META = 'dlm_license_meta';

	/**
	 * @var string
	 */
	const LICENSE_ACTIVATIONS = 'dlm_license_activations';

	/**
	 * @var string
	 */
	const PRODUCT_DOWNLOADS = 'dlm_product_downloads';

}
