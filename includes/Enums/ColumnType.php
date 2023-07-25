<?php
/**
 * This file comes from the "Digital License Manager" WordPress plugin.
 * https://darkog.com/p/digital-license-manager/
 *
 * Copyright (C) 2020-2023  Darko Gjorgjijoski. All Rights Reserved.
 *
 * Digital License Manager is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Digital License Manager program is distributed in the hope that it
 * will be useful,but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License v3
 * along with this program;
 *
 * If not, see: https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Code written, maintained by Darko Gjorgjijoski (https://darkog.com)
 */

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