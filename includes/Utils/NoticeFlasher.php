<?php

namespace IdeoLogix\DigitalLicenseManager\Utils;

use IdeoLogix\DigitalLicenseManager\Abstracts\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Class NoticeFlasher
 * @package IdeoLogix\DigitalLicenseManager\Utils
 */
class NoticeFlasher extends Singleton {

	const MESSAGE_DISMISSIBLE = '<div class="notice %s is-dismissible"><p><strong>Digital License Manager</strong>: %s</p></div>';
	const NOTICE_ERROR = 'notice-error';
	const NOTICE_SUCCESS = 'notice-success';
	const NOTICE_WARNING = 'notice-warning';
	const NOTICE_INFO = 'notice-info';

	/**
	 * @var array
	 */
	protected $types;

	/**
	 * Notice constructor.
	 */
	public function __construct() {
		$this->types = array(
			'error'   => self::NOTICE_ERROR,
			'success' => self::NOTICE_SUCCESS,
			'warning' => self::NOTICE_WARNING,
			'info'    => self::NOTICE_INFO
		);

		add_action( 'admin_notices', array( $this, 'init' ) );
	}

	/**
	 * Retrieves the notice message from the transients, displays it and finally deletes the transient itself.
	 */
	public function init() {
		foreach ( $this->types as $type => $class ) {
			$messages = get_transient( 'dlm_notice_' . $type );

			if ( $messages && is_array( $messages ) ) {
				foreach ( $messages as $message ) {
					echo sprintf(
						self::MESSAGE_DISMISSIBLE,
						$class,
						$message
					);
				}

				delete_transient( 'dlm_notice_' . $type );
			}
		}
	}

	/**
	 * Adds a dashboard notice to be displayed on the next page reload.
	 *
	 * @param string $level
	 * @param string $message
	 * @param int $duration
	 */
	public static function add( $level, $message, $duration = 60 ) {
		$messages = get_transient( 'dlm_notice_' . $level );

		if ( $messages && is_array( $messages ) ) {
			$messages[] = $message;
		} else {
			$messages = array( $message );
		}

		set_transient( 'dlm_notice_' . $level, $messages, $duration );
	}

	/**
	 * Log and display exception.
	 *
	 * @param string $message The error message
	 */
	public static function error( $message ) {
		self::add( 'error', $message );
	}

	/**
	 * Display a success message.
	 *
	 * @param string $message The success message to be display
	 */
	public static function success( $message ) {
		self::add( 'success', $message );
	}

	/**
	 * Display a warning message.
	 *
	 * @param string $message The warning message to be display
	 */
	public static function warning( $message ) {
		self::add( 'warning', $message );
	}

	/**
	 * Display a info message.
	 *
	 * @param string $message The info message to be display
	 */
	public static function info( $message ) {
		self::add( 'info', $message );
	}
}
