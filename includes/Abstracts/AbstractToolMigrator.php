<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

abstract class AbstractToolMigrator {

	/**
	 * The unique identifier of the migrator
	 * @var mixed
	 */
	protected $id;

	/**
	 * The name of the migrator
	 * @var string
	 */
	protected $name;

	/**
	 * Returns the unique identifier of the migrator
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Returns the name of the migrator
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the migrator steps
	 * @return array|\WP_Error
	 */
	abstract public function getSteps();


	/**
	 * Check if the it is possible to faciliate migration
	 * @return bool|\WP_Error
	 */
	abstract public function checkAvailability();

	/**
	 * Initializes the process
	 *
	 * @param array $data
	 *
	 * @return bool|\WP_Error
	 */
	abstract public function init( $data = array() );

	/**
	 * Initializes the process
	 *
	 * @param $step
	 * @param array $data
	 *
	 * @return bool|\WP_Error
	 */
	abstract public function doStep( $step, $page, $data = array() );

}
