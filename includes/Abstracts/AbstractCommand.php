<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts;

abstract class AbstractCommand {

	/**
	 * Handles the command execution
	 * @return mixed
	 */
	abstract public function handle();

	/**
	 * Returns the name
	 * @return string
	 */
	abstract public function get_name();
}