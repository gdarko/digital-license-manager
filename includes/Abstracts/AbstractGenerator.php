<?php

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
	abstract public function generate( $amount, $licenses );

}