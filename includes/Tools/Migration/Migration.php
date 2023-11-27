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

namespace IdeoLogix\DigitalLicenseManager\Tools\Migration;

use IdeoLogix\DigitalLicenseManager\Abstracts\AbstractTool;
use IdeoLogix\DigitalLicenseManager\Tools\Migration\Migrators\LMFW;


/**
 * Migration tool
 */
class Migration extends AbstractTool {

	/**
	 * The plugin classes
	 * @var array
	 */
	protected $plugins;

	/**
	 * Constructor
	 */
	public function __construct( $id ) {

		parent::__construct( $id );

		$this->slug        = 'migration';
		$this->description = __( 'Migration tool that makes it possible to easily move from other plugins', 'digital-license-manager' );

		$this->plugins = [
			new LMFW(),
		];
	}

	/**
	 * Returns the view
	 * @return string
	 */
	public function getView() {
		ob_start();

		$tool    = $this;
		$plugins = $this->plugins;
		include_once DLM_ABSPATH . 'templates/admin/settings/tools/migration.php';

		return ob_get_clean();
	}


	/**
	 * Returns the migrator steps
	 * @return array|\WP_Error
	 */
	public function getSteps() {
		$plugin = $this->getPlugin();
		if ( is_wp_error( $plugin ) ) {
			return $plugin;
		}

		return $plugin->getSteps();
	}

	/**
	 * Initializes the process
	 * @return bool|\WP_Error
	 */
	public function initProcess() {

		$canInit = $this->checkAvailability();
		if ( is_wp_error( $canInit ) ) {
			return $canInit;
		}
		$plugin = $this->getPlugin();
		if ( is_wp_error( $plugin ) ) {
			return $plugin;
		}

		return $plugin->init();
	}


	/**
	 * Returns a plugin
	 *
	 * @param $identifier
	 *
	 * @return LMFW|mixed|null
	 */
	public function getPlugin() {

		$identifier = $this->getIdentifier();

		foreach ( $this->plugins as $plugin ) {
			if ( $plugin->getId() == $identifier ) {
				return $plugin;
			}
		}

		return new \WP_Error( '404', 'Plugin migrator not found.' );
	}

	/**
	 * Initializes the process
	 *
	 * @param $step
	 * @param $page
	 * @param null $identifier
	 *
	 * @return bool|\WP_Error
	 */
	public function doStep( $step, $page ) {

		$identifier = $this->getIdentifier();

		$plugin = $this->getPlugin( $identifier );
		if ( is_wp_error( $plugin ) ) {
			return $plugin;
		}

		return $plugin->doStep( $step, $page );
	}

	/**
	 * Check availability
	 *
	 * @param null $identifier
	 *
	 * @return bool|\WP_Error
	 */
	public function checkAvailability() {

		$identifier = $this->getIdentifier();

		$plugin = $this->getPlugin( $identifier );
		if ( is_wp_error( $plugin ) ) {
			return $plugin;
		}

		return $plugin->checkAvailability();

	}

	/**
	 * Return the identifier
	 * @return string
	 */
	public function getIdentifier() {
		return isset( $_REQUEST['identifier'] ) ? sanitize_text_field( $_REQUEST['identifier'] ) : '';
	}

	/**
	 * Set the tool status
	 * @return void
	 */
	public function setStatus($args) {
		$data = wp_parse_args($args, [
			'completed_at' => null,
		]);
		update_option($this->getStatusKey(), $data);
	}

	/**
	 * Get the tool status
	 * @return mixed
	 */
	public function getStatus() {
		return get_option( $this->getStatusKey() );
	}

	/**
	 * Reset the tool status
	 * @return void
	 */
	public function resetStatus() {
		delete_option($this->getStatusKey());
	}

	/**
	 * Marks as complete
	 * @param $args
	 *
	 * @return void
	 */
	public function markAsComplete() {
		$this->setStatus([
			'completed_at' => date('Y-m-d H:i:s')
		]);
	}

	/**
	 * The status key
	 * @return string
	 */
	private function getStatusKey() {
		return sprintf( 'dlm_database_migration_%s', $this->getIdentifier());
	}
}
