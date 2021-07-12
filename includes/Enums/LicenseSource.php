<?php

namespace IdeoLogix\DigitalLicenseManager\Enums;

defined( 'ABSPATH' ) || exit;

/**
 * Class LicenseSource
 * @package IdeoLogix\DigitalLicenseManager\Enums
 */
abstract class LicenseSource {
	/**
	 * The default enumerator value.
	 *
	 * @var int
	 */
	const __default = - 1;

	/**
	 * Enumerator value used for generators.
	 *
	 * @var int
	 */
	const GENERATOR = 1;

	/**
	 * Enumerator value used for imports.
	 *
	 * @var int
	 */
	const IMPORT = 2;

	/**
	 * Enumerator value used for the API.
	 *
	 * @var int
	 */
	const API = 3;

	/**
	 * Available enumerator values.
	 *
	 * @var array
	 */
	public static $sources = array(
		self::GENERATOR,
		self::IMPORT,
		self::API
	);

	/**
	 * Returns the string representation of a specific enumerator value.
	 *
	 * @param int $source Source enumerator value
	 *
	 * @return string
	 */
	public static function getExportLabel( $source ) {
		$labels = array(
			self::GENERATOR => 'GENERATOR',
			self::IMPORT    => 'IMPORT',
			self::API       => 'API'
		);

		return $labels[ $source ];
	}
}
