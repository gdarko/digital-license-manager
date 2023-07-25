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

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\Generator as GeneratorResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\Generator as GeneratorResourceRepository;
use WP_Error;

abstract class AbstractGenerator {

	/**
	 * The generator database instance
	 * @var GeneratorResourceModel
	 */
	protected $generator;

	/**
	 * Generate list of licenses needed.
	 *
	 * @param GeneratorResourceModel $generator
	 *
	 */
	public function __construct( $generator ) {

		$this->generator = $generator;

		if ( is_numeric( $this->generator ) ) {
			$this->generator = GeneratorResourceRepository::instance()->find( $this->generator );
		}

	}

	/**
	 * Generate list of licenses needed.
	 *
	 * @param int $amount - Needed amount of licenses
	 * @param array $licenses - List of existing licenses
	 *
	 * @return WP_Error|string[]
	 */
	abstract public function generate( $amount, $licenses = array() );

}