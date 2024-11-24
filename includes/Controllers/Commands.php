<?php

namespace IdeoLogix\DigitalLicenseManager\Controllers;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractCommand;

class Commands {

	/**
	 * List of commands
	 * @var AbstractCommand[]
	 */
	protected $commands = [];

	/**
	 * The Constructor
	 */
	public function __construct() {

		if ( ! defined( '\WP_CLI' ) ) {
			return;
		}

		$this->commands = apply_filters( 'dlm_commands', $this->commands );

		foreach ( $this->commands as $command ) {
			\WP_CLI::add_command( sprintf( 'dlm:%s', $command->get_name() ), [ $command, 'handle' ] );
		}

	}

}