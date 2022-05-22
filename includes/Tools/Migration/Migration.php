<?php

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
	public function __construct() {

		$this->id          = 'migration';
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
	public function getSteps( $identifier = null ) {
		$plugin = $this->getPlugin( $identifier );
		if ( is_wp_error( $plugin ) ) {
			return $plugin;
		}

		return $plugin->getSteps();
	}

	/**
	 * Initializes the process
	 * @return bool|\WP_Error
	 */
	public function initProcess( $identifier = null ) {
		$plugin = $this->getPlugin( $identifier );
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
	public function getPlugin( $identifier ) {
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
	public function doStep( $step, $page, $identifier = null ) {

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
	public function checkAvailability( $identifier = null ) {

		$plugin = $this->getPlugin( $identifier );
		if ( is_wp_error( $plugin ) ) {
			return $plugin;
		}

		return $plugin->checkAvailability();

	}
}
