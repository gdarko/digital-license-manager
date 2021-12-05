<?php


namespace IdeoLogix\DigitalLicenseManager\Utils;


use IdeoLogix\DigitalLicenseManager\Abstracts\Singleton;
use IgniteKit\WP\Notices\NoticesManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class NoticeManager
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class NoticeManager extends Singleton {

	/**
	 * The manager
	 * @var NoticesManager
	 */
	protected $manager;

	/**
	 * NoticeManager constructor.
	 */
	public function __construct() {
		$this->manager = new NoticesManager( 'dlm' );
	}

	/**
	 * Add success notice
	 *
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return \IgniteKit\WP\Notices\Notice
	 */
	public function add_success( $key, $message, $expiry ) {
		return $this->manager->add_success( $key, $message, $expiry );
	}

	/**
	 * Add warning notice
	 *
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return \IgniteKit\WP\Notices\Notice
	 */
	public function add_warning( $key, $message, $expiry ) {
		return $this->manager->add_warning( $key, $message, $expiry );
	}

	/**
	 * Add error notice
	 *
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return \IgniteKit\WP\Notices\Notice
	 */
	public function add_error( $key, $message, $expiry ) {
		return $this->manager->add_error( $key, $message, $expiry );
	}

	/**
	 * Add info notice
	 *
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return \IgniteKit\WP\Notices\Notice
	 */
	public function add_info( $key, $message, $expiry ) {
		return $this->manager->add_info( $key, $message, $expiry );
	}

	/**
	 * Add custom notice
	 *
	 * @param $key
	 * @param $message
	 * @param $expiry
	 *
	 * @return \IgniteKit\WP\Notices\Notice
	 */
	public function add_custom( $key, $message, $expiry ) {
		return $this->manager->add_custom( $key, $message, $expiry );
	}

	/**
	 * Returns notice
	 *
	 * @param $key
	 * @param $type
	 *
	 * @return \IgniteKit\WP\Notices\Notice|null
	 */
	public function get_notice( $key, $type ) {
		return $this->manager->get_notice( $key, $type );
	}

}
