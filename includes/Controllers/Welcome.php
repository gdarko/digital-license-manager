<?php


namespace IdeoLogix\DigitalLicenseManager\Controllers;

use IdeoLogix\DigitalLicenseManager\Utils\NoticeManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Welcome
 * @package IdeoLogix\DigitalLicenseManager\Controllers
 */
class Welcome {

	/**
	 * Welcome constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initializes the notice.
	 */
	public function init() {
		$key  = apply_filters( 'dlm_welcome_notice_key', 'dlm_welcome' );
		$path = apply_filters( 'dlm_welcome_notice_path', DLM_ABSPATH . 'templates/admin/welcome.php' );
		if ( file_exists( $path ) ) {
			$path = trim( $path );
			NoticeManager::instance()->add_custom( $key, "file://{$path}", "never" );
		}
	}
}
