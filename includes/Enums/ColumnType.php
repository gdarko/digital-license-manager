<?php

namespace IdeoLogix\DigitalLicenseManager\Enums;

defined( 'ABSPATH' ) || exit;

/**
 * Class ColumnType
 * @package IdeoLogix\DigitalLicenseManager\Enums
 */
abstract class ColumnType {
	/**
	 * @var string
	 */
	const INT = 'INT';

	/**
	 * @var string
	 */
	const TINYINT = 'TINYINT';

	/**
	 * @var string
	 */
	const BIGINT = 'BIGINT';

	/**
	 * @var string
	 */
	const CHAR = 'CHAR';

	/**
	 * @var string
	 */
	const VARCHAR = 'VARCHAR';

	/**
	 * @var string
	 */
	const LONGTEXT = 'LONGTEXT';

	/**
	 * @var string
	 */
	const TEXT = 'TEXT';

	/**
	 * @var string
	 */
	const DATETIME = 'DATETIME';

	/**
	 * @var string
	 */
	const SERIALIZED = 'serialized';

	/**
	 * @var string
	 */
	const HTML_TEXT = 'html_text';
}